<?php
/**
 * Leave Management API - Sistem Cuti dengan Approval Workflow
 * Mengadopsi pattern dari overtime_api.php untuk approval system
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';
require_once __DIR__ . '/../core/NotificationService.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

// CSRF protection for POST (skip read-only GET actions)
$readOnlyActions = [
    'get_leave_requests','get_personil_leave','get_leave_balance',
    'get_pending_approvals','get_leave_statistics','get_leave_calendar'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Leave requests with filters
    if ($action === 'get_leave_requests') {
        $personilId = $_GET['personil_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+90 days'));
        $bagianId = $_GET['bagian_id'] ?? '';
        $leaveType = $_GET['leave_type'] ?? '';
        
        $whereClause = "WHERE lr.start_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($personilId) {
            $whereClause .= " AND lr.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($status) {
            $whereClause .= " AND lr.approval_status = ?";
            $params[] = $status;
        }
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        if ($leaveType) {
            $whereClause .= " AND lr.leave_type = ?";
            $params[] = $leaveType;
        }
        
        $stmt = $pdo->prepare("
            SELECT lr.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   ap.nama as approved_by_name,
                   ap.nama_pangkat as approved_by_pangkat,
                   DATEDIFF(lr.end_date, lr.start_date) + 1 as total_days,
                   CASE lr.approval_status
                       WHEN 'pending' THEN 'Menunggu Persetujuan'
                       WHEN 'approved' THEN 'Disetujui'
                       WHEN 'rejected' THEN 'Ditolak'
                       WHEN 'cancelled' THEN 'Dibatalkan'
                       ELSE lr.approval_status
                   END as status_display
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN personil ap ON ap.nrp = lr.approved_by
            $whereClause
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$requests]);
        exit;
    }

    // GET: Personil leave balance
    if ($action === 'get_leave_balance') {
        $personilId = trim($_GET['personil_id'] ?? '');
        $year = (int)($_GET['year'] ?? date('Y'));
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        // Get leave balance from database
        $stmt = $pdo->prepare("
            SELECT * FROM leave_balance 
            WHERE personil_id = ? AND year = ?
        ");
        $stmt->execute([$personilId, $year]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$balance) {
            // Initialize default balance
            $defaultBalance = [
                'annual_balance' => 12,
                'annual_used' => 0,
                'sick_balance' => 90,
                'sick_used' => 0,
                'personal_balance' => 6,
                'personal_used' => 0,
                'maternity_balance' => 90,
                'maternity_used' => 0,
                'year' => $year,
                'personil_id' => $personilId
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO leave_balance 
                (personil_id, year, annual_balance, annual_used, sick_balance, sick_used,
                 personal_balance, personal_used, maternity_balance, maternity_used)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $personilId, $year, 12, 0, 90, 0, 6, 0, 90, 0
            ]);
            
            $balance = $defaultBalance;
        }
        
        // Calculate remaining
        $balance['annual_remaining'] = $balance['annual_balance'] - $balance['annual_used'];
        $balance['sick_remaining'] = $balance['sick_balance'] - $balance['sick_used'];
        $balance['personal_remaining'] = $balance['personal_balance'] - $balance['personal_used'];
        $balance['maternity_remaining'] = $balance['maternity_balance'] - $balance['maternity_used'];
        
        echo json_encode(['success'=>true,'data'=>$balance]);
        exit;
    }

    // GET: Pending approvals for current user
    if ($action === 'get_pending_approvals') {
        $currentUser = $_SESSION['username'] ?? '';
        $userRole = $_SESSION['role'] ?? '';
        
        if (!$currentUser) {
            echo json_encode(['success'=>false,'error'=>'User not authenticated']); exit;
        }
        
        // Get user's personil data
        $stmt = $pdo->prepare("
            SELECT p.id_bagian, p.id_jabatan, p.nrp 
            FROM personil p 
            WHERE p.username = ?
        ");
        $stmt->execute([$currentUser]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            echo json_encode(['success'=>false,'error'=>'User data not found']); exit;
        }
        
        $whereClause = "WHERE lr.approval_status = 'pending'";
        $params = [];
        
        // Filter based on user role and hierarchy
        if ($userRole === 'admin' || $userRole === 'kapolres') {
            // Can see all pending requests
            $whereClause .= "";
        } elseif ($userRole === 'kadis' || $userRole === 'kabag') {
            // Can see requests from their bagian
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $userData['id_bagian'];
        } else {
            // Can see requests where they are direct supervisor
            $whereClause .= " AND p.id_jabatan IN (SELECT id FROM jabatan WHERE parent_id = ?)";
            $params[] = $userData['id_jabatan'];
        }
        
        $stmt = $pdo->prepare("
            SELECT lr.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   DATEDIFF(lr.end_date, lr.start_date) + 1 as total_days
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY lr.created_at ASC
        ");
        $stmt->execute($params);
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$pending]);
        exit;
    }

    // GET: Leave statistics
    if ($action === 'get_leave_statistics') {
        $year = (int)($_GET['year'] ?? date('Y'));
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE YEAR(lr.start_date) = ?";
        $params = [$year];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stats = [
            'total_requests' => 0,
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'leave_types' => [],
            'monthly_trends' => []
        ];
        
        // Get basic statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_requests'] = (int)$result['total'];
        $stats['approved'] = (int)$result['approved'];
        $stats['rejected'] = (int)$result['rejected'];
        $stats['pending'] = (int)$result['pending'];
        $stats['cancelled'] = (int)$result['cancelled'];
        
        // Get leave type distribution
        $stmt = $pdo->prepare("
            SELECT 
                leave_type,
                COUNT(*) as count,
                SUM(DATEDIFF(end_date, start_date) + 1) as total_days
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            $whereClause
            GROUP BY leave_type
        ");
        $stmt->execute($params);
        $stats['leave_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get monthly trends
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(lr.start_date) as month,
                COUNT(*) as count,
                SUM(DATEDIFF(end_date, start_date) + 1) as total_days
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            $whereClause
            GROUP BY MONTH(lr.start_date)
            ORDER BY month
        ");
        $stmt->execute($params);
        $stats['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$stats]);
        exit;
    }

    // POST: Create leave request
    if ($action === 'create_leave_request') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $leaveType = in_array($_POST['leave_type'] ?? '', ['annual','sick','personal','maternity','unpaid']) ? $_POST['leave_type'] : 'annual';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        $contactInfo = trim($_POST['contact_info'] ?? '');
        $emergencyContact = trim($_POST['emergency_contact'] ?? '');
        
        if (!$personilId || !$startDate || !$endDate) {
            echo json_encode(['success'=>false,'error'=>'Personil ID, start date, and end date are required']); exit;
        }
        
        if ($startDate > $endDate) {
            echo json_encode(['success'=>false,'error'=>'End date must be after start date']); exit;
        }
        
        // Calculate total days
        $totalDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
        
        // Check leave balance
        $stmt = $pdo->prepare("
            SELECT * FROM leave_balance 
            WHERE personil_id = ? AND year = ?
        ");
        $stmt->execute([$personilId, date('Y')]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$balance) {
            echo json_encode(['success'=>false,'error'=>'Leave balance not found']); exit;
        }
        
        $balanceField = $leaveType . '_balance';
        $usedField = $leaveType . '_used';
        
        if ($balance[$balanceField] - $balance[$usedField] < $totalDays) {
            echo json_encode(['success'=>false,'error'=>'Insufficient leave balance']); exit;
        }
        
        // Check for overlapping requests
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM leave_requests 
            WHERE personil_id = ? AND approval_status IN ('pending', 'approved')
            AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (start_date <= ? AND end_date >= ?))
        ");
        $stmt->execute([$personilId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        $overlap = $stmt->fetchColumn();
        
        if ($overlap > 0) {
            echo json_encode(['success'=>false,'error'=>'Leave dates overlap with existing request']); exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Create leave request
            $stmt = $pdo->prepare("
                INSERT INTO leave_requests 
                (personil_id, leave_type, start_date, end_date, reason, contact_info, 
                 emergency_contact, total_days, approval_status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([
                $personilId, $leaveType, $startDate, $endDate, $reason, 
                $contactInfo, $emergencyContact, $totalDays, $_SESSION['username']
            ]);
            
            $leaveId = $pdo->lastInsertId();
            
            // Create notification for approval
            $notificationService = new NotificationService($pdo);
            $notificationService->createNotification([
                'type' => 'leave_approval',
                'title' => 'Pengajuan Cuti Menunggu Persetujuan',
                'message' => "Pengajuan cuti $leaveType untuk $totalDays hari menunggu persetujuan",
                'target_group' => ['supervisors'],
                'priority' => 'medium',
                'action_required' => true,
                'action_url' => "leave_management.php?action=review&id=$leaveId",
                'action_deadline' => date('Y-m-d H:i:s', strtotime('+3 days')),
                'created_by' => 'system'
            ]);
            
            $pdo->commit();
            
            echo json_encode([
                'success'=>true, 
                'message'=>'Leave request created successfully',
                'leave_id' => $leaveId
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    // POST: Approve/Reject leave request
    if ($action === 'process_approval') {
        $leaveId = (int)($_POST['leave_id'] ?? 0);
        $action = $_POST['approval_action'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        $currentUser = $_SESSION['username'] ?? '';
        
        if (!$leaveId || !in_array($action, ['approve', 'reject'])) {
            echo json_encode(['success'=>false,'error'=>'Invalid leave ID or action']); exit;
        }
        
        if (!$currentUser) {
            echo json_encode(['success'=>false,'error'=>'User not authenticated']); exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Get leave request details
            $stmt = $pdo->prepare("
                SELECT lr.*, p.nama as personil_name 
                FROM leave_requests lr
                JOIN personil p ON p.nrp = lr.personil_id
                WHERE lr.id = ?
            ");
            $stmt->execute([$leaveId]);
            $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$leaveRequest) {
                throw new Exception('Leave request not found');
            }
            
            if ($leaveRequest['approval_status'] !== 'pending') {
                throw new Exception('Leave request already processed');
            }
            
            // Update leave request
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("
                UPDATE leave_requests 
                SET approval_status = ?, approved_by = ?, approved_at = NOW(), 
                    approval_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$status, $currentUser, $reason, $leaveId]);
            
            // If approved, update leave balance
            if ($action === 'approve') {
                $stmt = $pdo->prepare("
                    UPDATE leave_balance 
                    SET {$leaveRequest['leave_type']}_used = {$leaveRequest['leave_type']}_used + ?
                    WHERE personil_id = ? AND year = ?
                ");
                $stmt->execute([$leaveRequest['total_days'], $leaveRequest['personil_id'], date('Y')]);
            }
            
            // Create notification for personil
            $notificationService = new NotificationService($pdo);
            $notificationService->createNotification([
                'type' => 'leave_status',
                'title' => "Pengajuan Cuti $status",
                'message' => "Pengajuan cuti Anda telah " . ($action === 'approve' ? 'disetujui' : 'ditolak'),
                'target_personil' => $leaveRequest['personil_id'],
                'priority' => $action === 'approve' ? 'low' : 'high',
                'action_required' => false,
                'created_by' => $currentUser
            ]);
            
            $pdo->commit();
            
            echo json_encode([
                'success'=>true, 
                'message'=>"Leave request $action successfully"
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    // POST: Cancel leave request
    if ($action === 'cancel_leave_request') {
        $leaveId = (int)($_POST['leave_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $currentUser = $_SESSION['username'] ?? '';
        
        if (!$leaveId) {
            echo json_encode(['success'=>false,'error'=>'Leave ID required']); exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Get leave request details
            $stmt = $pdo->prepare("
                SELECT lr.*, p.nama as personil_name 
                FROM leave_requests lr
                JOIN personil p ON p.nrp = lr.personil_id
                WHERE lr.id = ?
            ");
            $stmt->execute([$leaveId]);
            $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$leaveRequest) {
                throw new Exception('Leave request not found');
            }
            
            if ($leaveRequest['approval_status'] === 'cancelled') {
                throw new Exception('Leave request already cancelled');
            }
            
            // If approved, restore leave balance
            if ($leaveRequest['approval_status'] === 'approved') {
                $stmt = $pdo->prepare("
                    UPDATE leave_balance 
                    SET {$leaveRequest['leave_type']}_used = {$leaveRequest['leave_type']}_used - ?
                    WHERE personil_id = ? AND year = ?
                ");
                $stmt->execute([$leaveRequest['total_days'], $leaveRequest['personil_id'], date('Y')]);
            }
            
            // Update leave request
            $stmt = $pdo->prepare("
                UPDATE leave_requests 
                SET approval_status = 'cancelled', cancelled_by = ?, 
                    cancelled_at = NOW(), cancellation_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$currentUser, $reason, $leaveId]);
            
            $pdo->commit();
            
            echo json_encode([
                'success'=>true, 
                'message'=>'Leave request cancelled successfully'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    // GET: Leave calendar
    if ($action === 'get_leave_calendar') {
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        
        $whereClause = "WHERE lr.approval_status = 'approved' AND lr.start_date <= ? AND lr.end_date >= ?";
        $params = [$endDate, $startDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT lr.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur
            FROM leave_requests lr
            JOIN personil p ON p.nrp = lr.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY lr.start_date
        ");
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format for calendar
        $calendarEvents = [];
        foreach ($events as $event) {
            $currentDate = strtotime($event['start_date']);
            $endDate = strtotime($event['end_date']);
            
            while ($currentDate <= $endDate) {
                $calendarEvents[] = [
                    'date' => date('Y-m-d', $currentDate),
                    'personil_name' => $event['personil_name'],
                    'pangkat' => $event['nama_pangkat'],
                    'bagian' => $event['nama_bagian'],
                    'leave_type' => $event['leave_type'],
                    'title' => $event['personil_name'] . ' - ' . ucfirst($event['leave_type'])
                ];
                $currentDate = strtotime('+1 day', $currentDate);
            }
        }
        
        echo json_encode(['success'=>true,'data'=>$calendarEvents]);
        exit;
    }

} catch (Exception $e) {
    error_log('[leave_management_api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
