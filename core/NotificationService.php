<?php
/**
 * Notification Service - Centralized Notification Management
 * Supports in-app, email, SMS, and push notifications
 */

class NotificationService {
    private $pdo;
    private $pushService;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->pushService = new PushNotificationService($pdo);
    }
    
    /**
     * Create and send notification
     */
    public function createNotification($data) {
        $required = ['type', 'title', 'message'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        $this->pdo->beginTransaction();
        
        try {
            // Create notification record
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications 
                (notification_type, title, message, target_personil, target_group, priority_level, 
                 delivery_methods, action_required, action_url, action_deadline, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['type'],
                $data['title'],
                $data['message'],
                $data['target_personil'] ?? null,
                json_encode($data['target_group'] ?? []),
                $data['priority'] ?? 'medium',
                json_encode($data['delivery_methods'] ?? ['in_app']),
                $data['action_required'] ?? false,
                $data['action_url'] ?? null,
                $data['action_deadline'] ?? null,
                $data['created_by'] ?? 'system'
            ]);
            
            $notificationId = $this->pdo->lastInsertId();
            
            // Get target recipients
            $recipients = $this->getNotificationRecipients($notificationId);
            
            // Send notifications
            $deliveryResults = $this->sendNotifications($notificationId, $recipients, $data);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'notification_id' => $notificationId,
                'recipients_count' => count($recipients),
                'delivery_results' => $deliveryResults
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    /**
     * Get notification recipients
     */
    private function getNotificationRecipients($notificationId) {
        $stmt = $this->pdo->prepare("
            SELECT n.target_personil, n.target_group, n.delivery_methods
            FROM notifications n
            WHERE n.id = ?
        ");
        $stmt->execute([$notificationId]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$notification) {
            return [];
        }
        
        $recipients = [];
        
        // Direct target
        if ($notification['target_personil']) {
            $recipients[] = [
                'personil_id' => $notification['target_personil'],
                'delivery_methods' => json_decode($notification['delivery_methods'])
            ];
        }
        
        // Group targets
        $targetGroups = json_decode($notification['target_group'] ?: '[]', true);
        if (!empty($targetGroups)) {
            $groupRecipients = $this->getGroupRecipients($targetGroups);
            $recipients = array_merge($recipients, $groupRecipients);
        }
        
        return array_unique($recipients, SORT_REGULAR);
    }
    
    /**
     * Get recipients from target groups
     */
    private function getGroupRecipients($targetGroups) {
        $recipients = [];
        
        foreach ($targetGroups as $group) {
            switch ($group['type']) {
                case 'bagian':
                    $stmt = $this->pdo->prepare("
                        SELECT nrp FROM personil 
                        WHERE id_bagian = ? AND is_active = 1 AND is_deleted = 0
                    ");
                    $stmt->execute([$group['id']]);
                    break;
                    
                case 'unsur':
                    $stmt = $this->pdo->prepare("
                        SELECT p.nrp FROM personil p
                        JOIN bagian b ON b.id = p.id_bagian
                        WHERE b.id_unsur = ? AND p.is_active = 1 AND p.is_deleted = 0
                    ");
                    $stmt->execute([$group['id']]);
                    break;
                    
                case 'pangkat':
                    $stmt = $this->pdo->prepare("
                        SELECT nrp FROM personil 
                        WHERE id_pangkat = ? AND is_active = 1 AND is_deleted = 0
                    ");
                    $stmt->execute([$group['id']]);
                    break;
                    
                case 'all':
                    $stmt = $this->pdo->prepare("
                        SELECT nrp FROM personil 
                        WHERE is_active = 1 AND is_deleted = 0
                    ");
                    $stmt->execute();
                    break;
            }
            
            $personnel = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($personnel as $nrp) {
                $recipients[] = [
                    'personil_id' => $nrp,
                    'delivery_methods' => ['in_app', 'push']
                ];
            }
        }
        
        return $recipients;
    }
    
    /**
     * Send notifications to recipients
     */
    private function sendNotifications($notificationId, $recipients, $data) {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $deliveryMethods = $recipient['delivery_methods'];
            
            foreach ($deliveryMethods as $method) {
                $result = $this->sendNotificationByMethod($notificationId, $recipient['personil_id'], $method, $data);
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Send notification by specific method
     */
    private function sendNotificationByMethod($notificationId, $personilId, $method, $data) {
        $result = [
            'notification_id' => $notificationId,
            'personil_id' => $personilId,
            'method' => $method,
            'status' => 'pending',
            'sent_at' => null,
            'error' => null
        ];
        
        try {
            switch ($method) {
                case 'in_app':
                    $result = $this->sendInAppNotification($notificationId, $personilId, $data);
                    break;
                    
                case 'push':
                    $result = $this->pushService->sendPushNotification($notificationId, $personilId, $data);
                    break;
                    
                case 'email':
                    $result = $this->sendEmailNotification($notificationId, $personilId, $data);
                    break;
                    
                case 'sms':
                    $result = $this->sendSMSNotification($notificationId, $personilId, $data);
                    break;
            }
        } catch (Exception $e) {
            $result['status'] = 'failed';
            $result['error'] = $e->getMessage();
        }
        
        // Log delivery attempt
        $this->logNotificationDelivery($notificationId, $personilId, $method, $result);
        
        return $result;
    }
    
    /**
     * Send in-app notification
     */
    private function sendInAppNotification($notificationId, $personilId, $data) {
        // In-app notifications are already stored in the database
        // Just update the status
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET status = 'sent', sent_time = NOW()
            WHERE id = ? AND target_personil = ?
        ");
        $stmt->execute([$notificationId, $personilId]);
        
        return [
            'notification_id' => $notificationId,
            'personil_id' => $personilId,
            'method' => 'in_app',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'error' => null
        ];
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($notificationId, $personilId, $data) {
        // Get personil email
        $stmt = $this->pdo->prepare("
            SELECT email, nama FROM personil WHERE nrp = ?
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil || empty($personil['email'])) {
            throw new Exception('Email address not found');
        }
        
        $subject = $data['title'];
        $message = $this->formatEmailMessage($data, $personil);
        
        // Send email (using PHP mail function for demo)
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@sprin.polri.go.id\r\n";
        
        $sent = mail($personil['email'], $subject, $message, $headers);
        
        if (!$sent) {
            throw new Exception('Failed to send email');
        }
        
        return [
            'notification_id' => $notificationId,
            'personil_id' => $personilId,
            'method' => 'email',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'error' => null
        ];
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($notificationId, $personilId, $data) {
        // Get personil phone number
        $stmt = $this->pdo->prepare("
            SELECT no_hp, nama FROM personil WHERE nrp = ?
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil || empty($personil['no_hp'])) {
            throw new Exception('Phone number not found');
        }
        
        $message = $this->formatSMSMessage($data);
        
        // Send SMS (using SMS gateway API - placeholder implementation)
        $smsSent = $this->sendSMS($personil['no_hp'], $message);
        
        if (!$smsSent) {
            throw new Exception('Failed to send SMS');
        }
        
        return [
            'notification_id' => $notificationId,
            'personil_id' => $personilId,
            'method' => 'sms',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'error' => null
        ];
    }
    
    /**
     * Format email message
     */
    private function formatEmailMessage($data, $personil) {
        $template = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background: #007bff; color: white; padding: 20px; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>SPRIN - Sistem Personil Polri</h2>
            </div>
            <div class='content'>
                <h3>{$data['title']}</h3>
                <p>Dear {$personil['nama']},</p>
                <p>{$data['message']}</p>
                " . ($data['action_url'] ? "<p><a href='{$data['action_url']}'>Click here to take action</a></p>" : "") . "
                " . ($data['action_deadline'] ? "<p><strong>Action required by:</strong> {$data['action_deadline']}</p>" : "") . "
            </div>
            <div class='footer'>
                <p>This is an automated message from SPRIN System</p>
                <p>© 2026 Kepolisian Negara Republik Indonesia</p>
            </div>
        </body>
        </html>";
        
        return $template;
    }
    
    /**
     * Format SMS message
     */
    private function formatSMSMessage($data) {
        $message = "[SPRIN] {$data['title']}\n";
        $message .= $data['message'];
        
        if ($data['action_url']) {
            $message .= "\nAction: {$data['action_url']}";
        }
        
        return $message;
    }
    
    /**
     * Send SMS using gateway (placeholder)
     */
    private function sendSMS($phoneNumber, $message) {
        // In production, integrate with SMS gateway like Twilio, Vonage, etc.
        // For demo, we'll just log the SMS
        error_log("SMS to $phoneNumber: $message");
        return true;
    }
    
    /**
     * Log notification delivery
     */
    private function logNotificationDelivery($notificationId, $personilId, $method, $result) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notification_delivery_log 
            (notification_id, delivery_method, recipient, delivery_status, error_message, sent_at, delivered_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $notificationId,
            $method,
            $personilId,
            $result['status'],
            $result['error'],
            $result['sent_at'],
            $result['status'] === 'sent' ? date('Y-m-d H:i:s') : null
        ]);
    }
    
    /**
     * Get notifications for personil
     */
    public function getPersonilNotifications($personilId, $status = null, $limit = 50) {
        $sql = "
            SELECT n.*, 
                   CASE WHEN n.read_time IS NOT NULL THEN 1 ELSE 0 END as is_read
            FROM notifications n
            WHERE n.target_personil = ? OR n.target_group LIKE ?
        ";
        $params = [$personilId, "%\"personil_id\":\"$personilId\"%"];
        
        if ($status) {
            $sql .= " AND n.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $personilId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET status = 'read', read_time = NOW()
            WHERE id = ? AND target_personil = ?
        ");
        $stmt->execute([$notificationId, $personilId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Create automatic notifications based on triggers
     */
    public function createAutomaticNotifications() {
        $notifications = [];
        
        // Fatigue warnings
        $notifications = array_merge($notifications, $this->createFatigueWarnings());
        
        // Certification expiry warnings
        $notifications = array_merge($notifications, $this->createCertificationWarnings());
        
        // Emergency task assignments
        $notifications = array_merge($notifications, $this->createEmergencyTaskNotifications());
        
        // Recall campaign notifications
        $notifications = array_merge($notifications, $this->createRecallNotifications());
        
        // Equipment maintenance reminders
        $notifications = array_merge($notifications, $this->createEquipmentMaintenanceReminders());
        
        // Send all notifications
        $results = [];
        foreach ($notifications as $notification) {
            try {
                $result = $this->createNotification($notification);
                $results[] = $result;
            } catch (Exception $e) {
                error_log('Failed to create automatic notification: ' . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Create fatigue warnings
     */
    private function createFatigueWarnings() {
        $notifications = [];
        
        // Get personnel with critical fatigue
        $stmt = $this->pdo->prepare("
            SELECT p.nrp, p.nama, p.fatigue_level, p.wellness_score
            FROM personil p
            WHERE p.fatigue_level IN ('critical', 'high') 
            AND p.is_active = 1 AND p.is_deleted = 0
            AND (p.last_fatigue_check IS NULL OR p.last_fatigue_check < CURDATE())
        ");
        $stmt->execute();
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($personnel as $person) {
            $notifications[] = [
                'type' => 'fatigue_warning',
                'title' => 'Fatigue Warning',
                'message' => "Your wellness score is {$person['wellness_score']} and fatigue level is {$person['fatigue_level']}. Please consider taking rest.",
                'target_personil' => $person['nrp'],
                'priority' => $person['fatigue_level'] === 'critical' ? 'critical' : 'high',
                'delivery_methods' => ['in_app', 'push'],
                'action_required' => true,
                'action_url' => '/pages/fatigue_management.php',
                'created_by' => 'system'
            ];
        }
        
        return $notifications;
    }
    
    /**
     * Create certification expiry warnings
     */
    private function createCertificationWarnings() {
        $notifications = [];
        
        // Get certifications expiring in 30 days
        $stmt = $this->pdo->prepare("
            SELECT c.personil_id, p.nama, c.certification_name, c.expiry_date, DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND c.status = 'valid'
            AND c.reminder_sent = FALSE
        ");
        $stmt->execute();
        $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($certifications as $cert) {
            $notifications[] = [
                'type' => 'certification_expiry',
                'title' => 'Certification Expiry Warning',
                'message' => "Your certification '{$cert['certification_name']}' expires in {$cert['days_to_expiry']} days on " . date('M d, Y', strtotime($cert['expiry_date'])),
                'target_personil' => $cert['personil_id'],
                'priority' => $cert['days_to_expiry'] <= 7 ? 'high' : 'medium',
                'delivery_methods' => ['in_app', 'push', 'email'],
                'action_required' => true,
                'action_url' => '/pages/certification_compliance.php',
                'created_by' => 'system'
            ];
            
            // Mark reminder as sent
            $stmt = $this->pdo->prepare("UPDATE certifications SET reminder_sent = TRUE WHERE id = ?");
            $stmt->execute([$cert['id']]);
        }
        
        return $notifications;
    }
    
    /**
     * Create emergency task notifications
     */
    private function createEmergencyTaskNotifications() {
        $notifications = [];
        
        // Get newly assigned emergency tasks
        $stmt = $this->pdo->prepare("
            SELECT et.*, p.nama
            FROM emergency_tasks et
            JOIN personil p ON p.nrp = et.assigned_to
            WHERE et.status = 'assigned'
            AND et.created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($tasks as $task) {
            $notifications[] = [
                'type' => 'emergency_task',
                'title' => 'Emergency Task Assignment',
                'message' => "You have been assigned to emergency task: {$task['task_name']}. Priority: {$task['priority_level']}",
                'target_personil' => $task['assigned_to'],
                'priority' => $task['priority_level'],
                'delivery_methods' => ['in_app', 'push', 'sms'],
                'action_required' => true,
                'action_url' => '/pages/emergency_tasks.php',
                'created_by' => 'system'
            ];
        }
        
        return $notifications;
    }
    
    /**
     * Create recall notifications
     */
    private function createRecallNotifications() {
        $notifications = [];
        
        // Get active recall campaigns
        $stmt = $this->pdo->prepare("
            SELECT rc.*, target_groups
            FROM recall_campaigns rc
            WHERE rc.status = 'active'
            AND rc.start_time <= NOW()
            AND (rc.end_time IS NULL OR rc.end_time > NOW())
        ");
        $stmt->execute();
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($campaigns as $campaign) {
            $targetGroups = json_decode($campaign['target_groups'] ?: '[]', true);
            
            foreach ($targetGroups as $group) {
                $notifications[] = [
                    'type' => 'recall_alert',
                    'title' => 'Recall Campaign: ' . $campaign['campaign_name'],
                    'message' => $campaign['message_template'],
                    'target_group' => [$group],
                    'priority' => $campaign['priority_level'],
                    'delivery_methods' => ['in_app', 'push', 'sms'],
                    'action_required' => true,
                    'action_url' => '/pages/recall_response.php?campaign=' . $campaign['id'],
                    'created_by' => 'system'
                ];
            }
        }
        
        return $notifications;
    }
    
    /**
     * Create equipment maintenance reminders
     */
    private function createEquipmentMaintenanceReminders() {
        $notifications = [];
        
        // Get equipment due for maintenance
        $stmt = $this->pdo->prepare("
            SELECT e.*, p.nama
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            WHERE e.next_maintenance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND e.current_status = 'assigned'
        ");
        $stmt->execute();
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($equipment as $eq) {
            if ($eq['current_assignment']) {
                $daysToMaintenance = (new DateTime($eq['next_maintenance']))->diff(new DateTime())->days;
                
                $notifications[] = [
                    'type' => 'equipment_due',
                    'title' => 'Equipment Maintenance Due',
                    'message' => "Equipment '{$eq['equipment_name']}' is due for maintenance in $daysToMaintenance days",
                    'target_personil' => $eq['current_assignment'],
                    'priority' => 'medium',
                    'delivery_methods' => ['in_app', 'push'],
                    'action_required' => true,
                    'action_url' => '/pages/equipment_management.php',
                    'created_by' => 'system'
                ];
            }
        }
        
        return $notifications;
    }
}

/**
 * Push Notification Service
 */
class PushNotificationService {
    private $pdo;
    private $fcmServerKey;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        // FCM server key should be stored securely
        $this->fcmServerKey = 'YOUR_FCM_SERVER_KEY'; // Replace with actual FCM server key
    }
    
    /**
     * Send push notification
     */
    public function sendPushNotification($notificationId, $personilId, $data) {
        // Get device tokens for personil
        $stmt = $this->pdo->prepare("
            SELECT device_token, platform 
            FROM mobile_sessions 
            WHERE user_id = (SELECT id FROM users WHERE nrp = ?)
            AND is_active = TRUE 
            AND expires_at > NOW()
        ");
        $stmt->execute([$personilId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($devices)) {
            throw new Exception('No active devices found');
        }
        
        $results = [];
        
        foreach ($devices as $device) {
            $result = $this->sendPushToDevice($device, $data, $notificationId);
            $results[] = $result;
        }
        
        return $results[0]; // Return first result for compatibility
    }
    
    /**
     * Send push to specific device
     */
    private function sendPushToDevice($device, $data, $notificationId) {
        $payload = [
            'notification' => [
                'title' => $data['title'],
                'body' => $data['message'],
                'sound' => 'default',
                'badge' => '1',
                'click_action' => $data['action_url'] ?? ''
            ],
            'data' => [
                'notification_id' => $notificationId,
                'type' => $data['type'],
                'action_required' => $data['action_required'] ?? false,
                'action_url' => $data['action_url'] ?? ''
            ],
            'to' => $device['device_token']
        ];
        
        $headers = [
            'Authorization: key=' . $this->fcmServerKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('FCM request failed: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if ($result['success'] !== 1) {
            throw new Exception('Push notification failed: ' . json_encode($result));
        }
        
        return [
            'notification_id' => $notificationId,
            'personil_id' => $personilId,
            'method' => 'push',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'error' => null
        ];
    }
}
?>
