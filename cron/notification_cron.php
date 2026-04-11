<?php
/**
 * Notification Cron Job
 * Runs automatic notification creation and delivery
 * Should be scheduled to run every 5-10 minutes
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/NotificationService.php';
require_once __DIR__ . '/../core/ActivityLog.php';

// Set execution time limit
set_time_limit(300); // 5 minutes

// Log cron start
error_log('[notification_cron] Starting notification cron job at ' . date('Y-m-d H:i:s'));

try {
    // Database connection
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Initialize notification service
    $notificationService = new NotificationService($pdo);

    // Create automatic notifications
    $results = $notificationService->createAutomaticNotifications();

    // Log results
    $totalNotifications = count($results);
    $successCount = array_filter($results, function($r) { return $r['success']; });
    $successCount = count($successCount);

    error_log("[notification_cron] Processed $totalNotifications notifications, $successCount successful");

    // Clean up old notifications (older than 30 days and read)
    $cleanupResult = cleanupOldNotifications($pdo);

    // Clean up expired mobile sessions
    $sessionCleanupResult = cleanupExpiredSessions($pdo);

    // Update notification statistics
    updateNotificationStats($pdo);

    error_log('[notification_cron] Completed successfully');
    error_log('[notification_cron] Cleanup: ' . json_encode($cleanupResult));
    error_log('[notification_cron] Session cleanup: ' . json_encode($sessionCleanupResult));

    echo json_encode([
        'success' => true,
        'processed' => $totalNotifications,
        'successful' => $successCount,
        'cleanup' => $cleanupResult,
        'session_cleanup' => $sessionCleanupResult,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('[notification_cron] Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Clean up old notifications
 */
function cleanupOldNotifications($pdo) {
    // Delete notifications older than 30 days and already read
    $stmt = $pdo->prepare("
        DELETE FROM notifications 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) 
        AND status = 'read'
    ");
    $stmt->execute();
    $notificationsDeleted = $stmt->rowCount();

    // Delete delivery logs for deleted notifications
    $stmt = $pdo->prepare("
        DELETE FROM notification_delivery_log 
        WHERE notification_id NOT IN (SELECT id FROM notifications)
    ");
    $stmt->execute();
    $logsDeleted = $stmt->rowCount();

    // Delete mobile notification logs older than 7 days
    $stmt = $pdo->prepare("
        DELETE FROM mobile_notification_logs 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $mobileLogsDeleted = $stmt->rowCount();

    return [
        'notifications_deleted' => $notificationsDeleted,
        'logs_deleted' => $logsDeleted,
        'mobile_logs_deleted' => $mobileLogsDeleted
    ];
}

/**
 * Clean up expired mobile sessions
 */
function cleanupExpiredSessions($pdo) {
    // Delete expired sessions
    $stmt = $pdo->prepare("
        DELETE FROM mobile_sessions 
        WHERE expires_at < NOW() 
        OR last_active < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute();
    $sessionsDeleted = $stmt->rowCount();

    // Clean up old mobile analytics (older than 90 days)
    $stmt = $pdo->prepare("
        DELETE FROM mobile_app_analytics 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    ");
    $stmt->execute();
    $analyticsDeleted = $stmt->rowCount();

    return [
        'sessions_deleted' => $sessionsDeleted,
        'analytics_deleted' => $analyticsDeleted
    ];
}

/**
 * Update notification statistics
 */
function updateNotificationStats($pdo) {
    $today = date('Y-m-d');
    
    // Update daily notification stats
    $stmt = $pdo->prepare("
        INSERT INTO notification_stats (date, total_sent, total_delivered, total_read, total_failed)
        SELECT 
            CURDATE() as date,
            COUNT(*) as total_sent,
            COUNT(CASE WHEN status = 'delivered' THEN 1 END) as total_delivered,
            COUNT(CASE WHEN status = 'read' THEN 1 END) as total_read,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as total_failed
        FROM notifications 
        WHERE DATE(created_at) = CURDATE()
        ON DUPLICATE KEY UPDATE
        total_sent = VALUES(total_sent),
        total_delivered = VALUES(total_delivered),
        total_read = VALUES(total_read),
        total_failed = VALUES(total_failed)
    ");
    $stmt->execute();

    // Update notification queue status
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as pending,
            COUNT(CASE WHEN priority_level = 'critical' THEN 1 END) as critical_pending
        FROM notifications 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $queueStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log queue statistics
    error_log("[notification_cron] Queue status: " . json_encode($queueStats));

    return true;
}

/**
 * Send emergency notifications for critical events
 */
function sendEmergencyNotifications($pdo) {
    $emergencyNotifications = [];

    // Check for critical fatigue levels
    $stmt = $pdo->prepare("
        SELECT p.nrp, p.nama, p.fatigue_level, p.wellness_score
        FROM personil p
        WHERE p.fatigue_level = 'critical' 
        AND p.is_active = 1 AND p.is_deleted = 0
        AND (p.last_fatigue_check IS NULL OR p.last_fatigue_check < DATE_SUB(NOW(), INTERVAL 1 HOUR))
    ");
    $stmt->execute();
    $criticalPersonnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($criticalPersonnel as $person) {
        $emergencyNotifications[] = [
            'type' => 'fatigue_emergency',
            'title' => 'CRITICAL: Fatigue Alert',
            'message' => "CRITICAL: {$person['nama']} has critical fatigue level. Immediate action required!",
            'target_group' => [
                ['type' => 'bagian', 'id' => getPersonilBagianId($pdo, $person['nrp'])]
            ],
            'priority' => 'critical',
            'delivery_methods' => ['in_app', 'push', 'sms'],
            'action_required' => true,
            'action_url' => '/pages/fatigue_management.php',
            'created_by' => 'system'
        ];
    }

    // Check for unresponded emergency tasks older than 30 minutes
    $stmt = $pdo->prepare("
        SELECT et.*, p.nama
        FROM emergency_tasks et
        JOIN personil p ON p.nrp = et.assigned_to
        WHERE et.status = 'assigned'
        AND et.start_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        AND et.priority_level = 'critical'
    ");
    $stmt->execute();
    $unrespondedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($unrespondedTasks as $task) {
        $emergencyNotifications[] = [
            'type' => 'emergency_task_unresponded',
            'title' => 'CRITICAL: Unresponded Emergency Task',
            'message' => "CRITICAL: Emergency task '{$task['task_name']}' assigned to {$task['nama']} has not been responded to for 30+ minutes!",
            'target_group' => [
                ['type' => 'bagian', 'id' => getPersonilBagianId($pdo, $task['assigned_to'])]
            ],
            'priority' => 'critical',
            'delivery_methods' => ['in_app', 'push', 'sms'],
            'action_required' => true,
            'action_url' => '/pages/emergency_tasks.php',
            'created_by' => 'system'
        ];
    }

    return $emergencyNotifications;
}

/**
 * Get personil bagian ID
 */
function getPersonilBagianId($pdo, $nrp) {
    $stmt = $pdo->prepare("SELECT id_bagian FROM personil WHERE nrp = ?");
    $stmt->execute([$nrp]);
    return $stmt->fetchColumn();
}

/**
 * Check system health for notifications
 */
function checkNotificationSystemHealth($pdo) {
    $health = [
        'database_connection' => true,
        'notification_queue_size' => 0,
        'failed_notifications_1h' => 0,
        'mobile_sessions_active' => 0,
        'last_notification_sent' => null
    ];

    // Check notification queue size
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE status = 'pending'");
    $stmt->execute();
    $health['notification_queue_size'] = $stmt->fetchColumn();

    // Check failed notifications in last hour
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notification_delivery_log 
        WHERE delivery_status = 'failed' 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute();
    $health['failed_notifications_1h'] = $stmt->fetchColumn();

    // Check active mobile sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM mobile_sessions 
        WHERE is_active = TRUE AND expires_at > NOW()
    ");
    $stmt->execute();
    $health['mobile_sessions_active'] = $stmt->fetchColumn();

    // Get last notification sent time
    $stmt = $pdo->prepare("
        SELECT MAX(sent_time) as last_sent 
        FROM notifications 
        WHERE status = 'sent'
    ");
    $stmt->execute();
    $health['last_notification_sent'] = $stmt->fetchColumn();

    return $health;
}
?>
