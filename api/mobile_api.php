<?php
/**
 * Mobile App API - Optimized for Mobile Applications
 * Provides lightweight, mobile-friendly endpoints for SPRIN mobile app
 */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key");
header("Cache-Control: no-cache, must-revalidate");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Mobile API Key Authentication (simplified for demo)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validApiKey = 'SPRIN_MOBILE_2026'; // In production, use proper key management

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Invalid API key']); exit;
}

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';
    $version = $_GET['v'] ?? '1.0'; // API versioning

    // Rate limiting for mobile (100 requests per minute)
    $mobileRateLimit = checkMobileRateLimit($apiKey);
    if (!$mobileRateLimit) {
        http_response_code(429);
        echo json_encode(['success'=>false,'error'=>'Rate limit exceeded']); exit;
    }

    // ============================================
    // AUTHENTICATION ENDPOINTS
    // ============================================

    // POST: Mobile login
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $deviceToken = trim($_POST['device_token'] ?? '');
        $deviceInfo = $_POST['device_info'] ?? '';
        
        if (!$username || !$password) {
            echo json_encode(['success'=>false,'error'=>'Username and password required']); exit;
        }
        
        // Authenticate user
        $stmt = $pdo->prepare("
            SELECT u.*, p.nrp, p.nama, p.nama_bagian, pk.nama_pangkat
            FROM users u
            JOIN personil p ON p.nrp = u.nrp
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['success'=>false,'error'=>'Invalid credentials']); exit;
        }
        
        // Generate mobile session token
        $sessionToken = generateMobileToken($user['id'], $user['nrp']);
        
        // Update device info
        if ($deviceToken) {
            $stmt = $pdo->prepare("
                INSERT INTO mobile_sessions (user_id, session_token, device_token, device_info, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                session_token = VALUES(session_token),
                device_info = VALUES(device_info),
                last_active = NOW()
            ");
            $stmt->execute([$user['id'], $sessionToken, $deviceToken, $deviceInfo]);
        }
        
        ActivityLog::logCreate('mobile_login', $user['id'], "Mobile login from device: $deviceInfo");
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'session_token' => $sessionToken,
                'user' => [
                    'nrp' => $user['nrp'],
                    'nama' => $user['nama'],
                    'bagian' => $user['nama_bagian'],
                    'pangkat' => $user['nama_pangkat']
                ],
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]
        ]);
        exit;
    }

    // POST: Mobile logout
    if ($action === 'logout') {
        $sessionToken = $_POST['session_token'] ?? '';
        
        if (!$sessionToken) {
            echo json_encode(['success'=>false,'error'=>'Session token required']); exit;
        }
        
        $stmt = $pdo->prepare("
            DELETE FROM mobile_sessions WHERE session_token = ?
        ");
        $stmt->execute([$sessionToken]);
        
        echo json_encode(['success'=>true,'message'=>'Logged out successfully']);
        exit;
    }

    // Validate mobile session
    $sessionToken = $_SERVER['HTTP_X_SESSION_TOKEN'] ?? $_POST['session_token'] ?? $_GET['session_token'] ?? '';
    $userId = validateMobileSession($sessionToken);
    
    if (!$userId && $action !== 'login') {
        http_response_code(401);
        echo json_encode(['success'=>false,'error'=>'Invalid or expired session']); exit;
    }

    // ============================================
    // DASHBOARD ENDPOINTS
    // ============================================

    // GET: Mobile dashboard
    if ($action === 'dashboard') {
        $nrp = $_GET['nrp'] ?? '';
        
        // Get user's basic info
        $stmt = $pdo->prepare("
            SELECT p.*, pk.nama_pangkat, b.nama_bagian, u.nama_unsur
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE p.nrp = ? AND p.is_active = 1
        ");
        $stmt->execute([$nrp ?: $userId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil) {
            echo json_encode(['success'=>false,'error'=>'Personil not found']); exit;
        }
        
        // Get today's schedule
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT s.*, t.nama_tim, t.shift_type as tim_shift_type
            FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id
            WHERE s.personil_id = ? AND s.shift_date = ?
        ");
        $stmt->execute([$personil['nrp'], $today]);
        $todaySchedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get fatigue status
        $stmt = $pdo->prepare("
            SELECT * FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date = ?
        ");
        $stmt->execute([$personil['nrp'], $today]);
        $fatigueToday = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get pending emergency tasks
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM emergency_tasks 
            WHERE assigned_to = ? AND status IN ('pending', 'assigned')
        ");
        $stmt->execute([$personil['nrp']]);
        $pendingTasks = $stmt->fetchColumn();
        
        // Get unread notifications
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM notifications 
            WHERE target_personil = ? AND status = 'pending'
        ");
        $stmt->execute([$personil['nrp']]);
        $unreadNotifications = $stmt->fetchColumn();
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'today_schedule' => $todaySchedule,
                'fatigue_status' => $fatigueToday,
                'pending_tasks' => (int)$pendingTasks,
                'unread_notifications' => (int)$unreadNotifications,
                'wellness_score' => $personil['wellness_score'] ?? 100,
                'fatigue_level' => $personil['fatigue_level'] ?? 'low'
            ]
        ]);
        exit;
    }

    // ============================================
    // SCHEDULE ENDPOINTS
    // ============================================

    // GET: My schedules
    if ($action === 'my_schedules') {
        $nrp = $_GET['nrp'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
        
        $stmt = $pdo->prepare("
            SELECT s.*, t.nama_tim, t.shift_type as tim_shift_type,
                   pa.status as attendance_status, pa.tanggal as attendance_date
            FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
            WHERE s.personil_id = ? AND s.shift_date BETWEEN ? AND ?
            ORDER BY s.shift_date ASC
        ");
        $stmt->execute([$nrp ?: $userId, $startDate, $endDate]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$schedules]);
        exit;
    }

    // POST: Update attendance
    if ($action === 'update_attendance') {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $photo = $_FILES['photo'] ?? null;
        
        if (!$scheduleId || !$status) {
            echo json_encode(['success'=>false,'error'=>'Schedule ID and status required']); exit;
        }
        
        $validStatuses = ['hadir', 'sakit', 'ijin', 'tidak_hadir'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid attendance status']); exit;
        }
        
        // Check if attendance already exists
        $stmt = $pdo->prepare("SELECT id FROM piket_absensi WHERE schedule_id = ?");
        $stmt->execute([$scheduleId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing attendance
            $stmt = $pdo->prepare("
                UPDATE piket_absensi 
                SET status = ?, lokasi = ?, keterangan = ?, updated_at = NOW()
                WHERE schedule_id = ?
            ");
            $stmt->execute([$status, $location, $notes, $scheduleId]);
        } else {
            // Create new attendance record
            $stmt = $pdo->prepare("
                INSERT INTO piket_absensi (schedule_id, status, lokasi, keterangan, tanggal)
                VALUES (?, ?, ?, ?, CURDATE())
            ");
            $stmt->execute([$scheduleId, $status, $location, $notes]);
        }
        
        // Handle photo upload if provided
        if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../file/attendance/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = 'attendance_' . $scheduleId . '_' . time() . '.jpg';
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($photo['tmp_name'], $filePath)) {
                $stmt = $pdo->prepare("
                    UPDATE piket_absensi SET photo_path = ? WHERE schedule_id = ?
                ");
                $stmt->execute([$fileName, $scheduleId]);
            }
        }
        
        ActivityLog::logUpdate('attendance', $scheduleId, "Updated attendance: $status");
        
        echo json_encode(['success'=>true,'message'=>'Attendance updated successfully']);
        exit;
    }

    // ============================================
    // FATIGUE ENDPOINTS
    // ============================================

    // GET: My fatigue status
    if ($action === 'my_fatigue') {
        $nrp = $_GET['nrp'] ?? '';
        $days = (int)($_GET['days'] ?? 7);
        
        $startDate = date('Y-m-d', strtotime("-$days days"));
        $endDate = date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT * FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date BETWEEN ? AND ?
            ORDER BY tracking_date DESC
        ");
        $stmt->execute([$nrp ?: $userId, $startDate, $endDate]);
        $fatigueData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get current wellness score
        $stmt = $pdo->prepare("
            SELECT wellness_score, fatigue_level, last_fatigue_check 
            FROM personil WHERE nrp = ?
        ");
        $stmt->execute([$nrp ?: $userId]);
        $currentStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'current_status' => $currentStatus,
                'tracking_history' => $fatigueData
            ]
        ]);
        exit;
    }

    // POST: Update fatigue tracking
    if ($action === 'update_fatigue') {
        $nrp = trim($_POST['nrp'] ?? '');
        $trackingDate = trim($_POST['tracking_date'] ?? date('Y-m-d'));
        $hoursWorked = (float)($_POST['hours_worked'] ?? 0);
        $restHours = (float)($_POST['rest_hours'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$nrp) {
            echo json_encode(['success'=>false,'error'=>'NRP required']); exit;
        }
        
        // Calculate fatigue score
        $fatigueScore = calculateFatigueScore($hoursWorked, $restHours);
        $fatigueLevel = getFatigueLevel($fatigueScore);
        
        // Check for violations
        $violations = checkFatigueViolations($nrp, $hoursWorked, $restHours, $pdo);
        
        $pdo->beginTransaction();
        
        // Update or insert fatigue tracking
        $stmt = $pdo->prepare("
            INSERT INTO fatigue_tracking 
            (personil_id, tracking_date, hours_worked, rest_hours, fatigue_score, fatigue_level, violations)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            hours_worked = VALUES(hours_worked),
            rest_hours = VALUES(rest_hours),
            fatigue_score = VALUES(fatigue_score),
            fatigue_level = VALUES(fatigue_level),
            violations = VALUES(violations),
            updated_at = NOW()
        ");
        $stmt->execute([$nrp, $trackingDate, $hoursWorked, $restHours, $fatigueScore, $fatigueLevel, json_encode($violations)]);
        
        // Update personil current status
        $stmt = $pdo->prepare("
            UPDATE personil 
            SET wellness_score = ?, fatigue_level = ?, last_fatigue_check = ?
            WHERE nrp = ?
        ");
        $stmt->execute([$fatigueScore, $fatigueLevel, $trackingDate, $nrp]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('fatigue_tracking', 0, "Updated fatigue tracking for $nrp: Score $fatigueScore");
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'fatigue_score' => $fatigueScore,
                'fatigue_level' => $fatigueLevel,
                'violations' => $violations
            ]
        ]);
        exit;
    }

    // ============================================
    // EMERGENCY TASKS ENDPOINTS
    // ============================================

    // GET: My emergency tasks
    if ($action === 'my_emergency_tasks') {
        $nrp = $_GET['nrp'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $whereClause = "WHERE et.assigned_to = ?";
        $params = [$nrp ?: $userId];
        
        if ($status) {
            $whereClause .= " AND et.status = ?";
            $params[] = $status;
        }
        
        $stmt = $pdo->prepare("
            SELECT et.*, p.nama as creator_name
            FROM emergency_tasks et
            LEFT JOIN personil p ON p.nrp = et.created_by
            $whereClause
            ORDER BY et.start_time DESC
        ");
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$tasks]);
        exit;
    }

    // POST: Respond to emergency task
    if ($action === 'respond_emergency_task') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $response = $_POST['response'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $eta = trim($_POST['eta'] ?? '');
        
        if (!$taskId || !$response) {
            echo json_encode(['success'=>false,'error'=>'Task ID and response required']); exit;
        }
        
        $validResponses = ['acknowledged', 'confirmed', 'declined', 'unable'];
        if (!in_array($response, $validResponses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid response']); exit;
        }
        
        // Update task status
        $stmt = $pdo->prepare("
            UPDATE emergency_tasks 
            SET status = CASE 
                WHEN ? = 'confirmed' THEN 'in_progress'
                WHEN ? = 'declined' OR ? = 'unable' THEN 'cancelled'
                ELSE 'assigned'
            END,
            updated_at = NOW()
            WHERE id = ? AND assigned_to = ?
        ");
        $stmt->execute([$response, $response, $response, $taskId, $userId]);
        
        // Add response note
        if ($notes) {
            $stmt = $pdo->prepare("
                INSERT INTO emergency_task_responses 
                (task_id, personil_id, response, notes, eta, response_time)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$taskId, $userId, $response, $notes, $eta]);
        }
        
        ActivityLog::logUpdate('emergency_task', $taskId, "Responded to emergency task: $response");
        
        echo json_encode(['success'=>true,'message'=>'Task response recorded']);
        exit;
    }

    // ============================================
    // NOTIFICATIONS ENDPOINTS
    // ============================================

    // GET: My notifications
    if ($action === 'my_notifications') {
        $nrp = $_GET['nrp'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = (int)($_GET['limit'] ?? 50);
        
        $whereClause = "WHERE target_personil = ?";
        $params = [$nrp ?: $userId];
        
        if ($status) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            $whereClause
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([...$params, $limit]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$notifications]);
        exit;
    }

    // POST: Mark notification as read
    if ($action === 'mark_notification_read') {
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if (!$notificationId) {
            echo json_encode(['success'=>false,'error'=>'Notification ID required']); exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET status = 'read', read_time = NOW()
            WHERE id = ? AND target_personil = ?
        ");
        $stmt->execute([$notificationId, $userId]);
        
        echo json_encode(['success'=>true,'message'=>'Notification marked as read']);
        exit;
    }

    // ============================================
    // RECALL ENDPOINTS
    // ============================================

    // GET: My recall campaigns
    if ($action === 'my_recall_campaigns') {
        $nrp = $_GET['nrp'] ?? '';
        
        $stmt = $pdo->prepare("
            SELECT rc.*, rr.response_status, rr.response_time, rr.response_note, rr.eta_time
            FROM recall_campaigns rc
            LEFT JOIN recall_responses rr ON rr.campaign_id = rc.id AND rr.personil_id = ?
            WHERE rc.status = 'active'
            ORDER BY rc.start_time DESC
        ");
        $stmt->execute([$nrp ?: $userId]);
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$campaigns]);
        exit;
    }

    // POST: Respond to recall campaign
    if ($action === 'respond_recall') {
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        $response = $_POST['response'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $eta = trim($_POST['eta'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (!$campaignId || !$response) {
            echo json_encode(['success'=>false,'error'=>'Campaign ID and response required']); exit;
        }
        
        $validResponses = ['acknowledged', 'confirmed', 'declined', 'unable'];
        if (!in_array($response, $validResponses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid response']); exit;
        }
        
        // Update or insert response
        $stmt = $pdo->prepare("
            INSERT INTO recall_responses 
            (campaign_id, personil_id, response_status, response_time, response_note, eta_time, location)
            VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            response_status = VALUES(response_status),
            response_time = VALUES(response_time),
            response_note = VALUES(response_note),
            eta_time = VALUES(eta_time),
            location = VALUES(location),
            updated_at = NOW()
        ");
        $stmt->execute([$campaignId, $userId, $response, $notes, $eta, $location]);
        
        // Update campaign totals
        $stmt = $pdo->prepare("
            UPDATE recall_campaigns rc
            SET total_responded = (
                SELECT COUNT(*) FROM recall_responses 
                WHERE campaign_id = rc.id AND response_status != 'pending'
            )
            WHERE rc.id = ?
        ");
        $stmt->execute([$campaignId]);
        
        ActivityLog::logUpdate('recall_response', $campaignId, "Responded to recall: $response");
        
        echo json_encode(['success'=>true,'message'=>'Recall response recorded']);
        exit;
    }

    // ============================================
    // EQUIPMENT ENDPOINTS
    // ============================================

    // GET: My equipment
    if ($action === 'my_equipment') {
        $nrp = $_GET['nrp'] ?? '';
        
        $stmt = $pdo->prepare("
            SELECT e.*, ea.assignment_date, ea.assignment_purpose, ea.condition_assigned
            FROM equipment e
            LEFT JOIN equipment_assignments ea ON ea.equipment_id = e.id AND ea.status = 'active'
            WHERE ea.personil_id = ? OR e.current_assignment = ?
            ORDER BY e.equipment_type, e.equipment_name
        ");
        $stmt->execute([$nrp ?: $userId, $nrp ?: $userId]);
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$equipment]);
        exit;
    }

    // POST: Update equipment condition
    if ($action === 'update_equipment_condition') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $condition = trim($_POST['condition'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$equipmentId || !$condition) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID and condition required']); exit;
        }
        
        // Update assignment record
        $stmt = $pdo->prepare("
            UPDATE equipment_assignments 
            SET condition_returned = ?, notes = ?
            WHERE equipment_id = ? AND personil_id = ? AND status = 'active'
        ");
        $stmt->execute([$condition, $notes, $equipmentId, $userId]);
        
        ActivityLog::logUpdate('equipment_condition', $equipmentId, "Updated equipment condition: $condition");
        
        echo json_encode(['success'=>true,'message'=>'Equipment condition updated']);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[mobile_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

// Helper Functions

function generateMobileToken($userId, $nrp) {
    $payload = [
        'user_id' => $userId,
        'nrp' => $nrp,
        'exp' => time() + (24 * 60 * 60), // 24 hours
        'iat' => time()
    ];
    
    // Simple token generation (in production, use JWT)
    return base64_encode(json_encode($payload)) . '.' . md5(uniqid($nrp, true));
}

function validateMobileSession($sessionToken) {
    if (!$sessionToken) return null;
    
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT ms.user_id, ms.last_active 
            FROM mobile_sessions ms 
            WHERE ms.session_token = ? AND ms.last_active > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$sessionToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session) {
            // Update last active
            $stmt = $pdo->prepare("
                UPDATE mobile_sessions 
                SET last_active = NOW() 
                WHERE session_token = ?
            ");
            $stmt->execute([$sessionToken]);
            
            return $session['user_id'];
        }
    } catch (Exception $e) {
        error_log('Session validation error: ' . $e->getMessage());
    }
    
    return null;
}

function checkMobileRateLimit($apiKey) {
    $rateLimitFile = sys_get_temp_dir() . '/mobile_rate_' . md5($apiKey) . '.json';
    $windowSeconds = 60;
    $maxRequests = 100;
    
    if (!file_exists($rateLimitFile)) {
        file_put_contents($rateLimitFile, json_encode(['count' => 0, 'start' => time()]));
    }
    
    $data = json_decode(file_get_contents($rateLimitFile), true);
    $now = time();
    
    if (($now - $data['start']) >= $windowSeconds) {
        $data = ['count' => 1, 'start' => $now];
    } else {
        $data['count']++;
    }
    
    file_put_contents($rateLimitFile, json_encode($data));
    
    return $data['count'] <= $maxRequests;
}

function calculateFatigueScore($hoursWorked, $restHours) {
    $baseScore = 100;
    
    // Deduct points for excessive work hours
    if ($hoursWorked > 12) {
        $baseScore -= 30;
    } elseif ($hoursWorked > 10) {
        $baseScore -= 20;
    } elseif ($hoursWorked > 8) {
        $baseScore -= 10;
    }
    
    // Add points for adequate rest
    if ($restHours >= 12) {
        $baseScore += 10;
    } elseif ($restHours >= 8) {
        $baseScore += 5;
    } elseif ($restHours < 6) {
        $baseScore -= 20;
    }
    
    return max(0, min(100, $baseScore));
}

function getFatigueLevel($score) {
    if ($score >= 85) return 'low';
    if ($score >= 70) return 'medium';
    if ($score >= 50) return 'high';
    return 'critical';
}

function checkFatigueViolations($nrp, $hoursWorked, $restHours, $pdo) {
    $violations = [];
    
    // Check daily work hours violation
    if ($hoursWorked > 12) {
        $violations[] = [
            'type' => 'excessive_work_hours',
            'description' => 'Work hours exceed 12 hours limit',
            'severity' => 'high'
        ];
    }
    
    // Check rest hours violation
    if ($restHours < 6) {
        $violations[] = [
            'type' => 'insufficient_rest',
            'description' => 'Rest hours below minimum 6 hours',
            'severity' => 'medium'
        ];
    }
    
    // Check consecutive days (simplified check)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as consecutive_days 
        FROM fatigue_tracking 
        WHERE personil_id = ? AND tracking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        AND hours_worked > 8
    ");
    $stmt->execute([$nrp]);
    $consecutiveDays = $stmt->fetchColumn();
    
    if ($consecutiveDays >= 7) {
        $violations[] = [
            'type' => 'consecutive_work_days',
            'description' => 'Working more than 7 consecutive days',
            'severity' => 'high'
        ];
    }
    
    return $violations;
}
?>
