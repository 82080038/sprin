<?php
/**
 * Overtime & Compensation Management API
 * Track overtime hours, calculate compensation, and manage approvals
 */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';

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
    'get_overtime_records','get_personil_overtime','get_overtime_statistics',
    'get_pending_approvals','get_overtime_summary'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Overtime records with filters
    if ($action === 'get_overtime_records') {
        $personilId = $_GET['personil_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE or.overtime_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($personilId) {
            $whereClause .= " AND or.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($status) {
            $whereClause .= " AND or.approval_status = ?";
            $params[] = $status;
        }
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT or.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   s.shift_type,
                   s.start_time as schedule_start,
                   s.end_time as schedule_end,
                   CASE or.overtime_rate
                       WHEN 'regular' THEN 1.5
                       WHEN 'holiday' THEN 2.0
                       WHEN 'weekend' THEN 1.75
                       WHEN 'emergency' THEN 2.5
                       ELSE 1.5
                   END as rate_multiplier
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN schedules s ON s.id = or.schedule_id
            $whereClause
            ORDER BY or.overtime_date DESC, or.created_at DESC
        ");
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$records]);
        exit;
    }

    // GET: Personil overtime summary
    if ($action === 'get_personil_overtime') {
        $personilId = trim($_GET['personil_id'] ?? '');
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        // Get personil info
        $stmt = $pdo->prepare("
            SELECT p.*, pk.nama_pangkat, b.nama_bagian, u.nama_unsur
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE p.nrp = ?
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil) {
            echo json_encode(['success'=>false,'error'=>'Personil not found']); exit;
        }
        
        // Get overtime records for the month
        $stmt = $pdo->prepare("
            SELECT or.*, 
                   s.shift_type,
                   CASE or.overtime_rate
                       WHEN 'regular' THEN 1.5
                       WHEN 'holiday' THEN 2.0
                       WHEN 'weekend' THEN 1.75
                       WHEN 'emergency' THEN 2.5
                       ELSE 1.5
                   END as rate_multiplier
            FROM overtime_records or
            LEFT JOIN schedules s ON s.id = or.schedule_id
            WHERE or.personil_id = ? AND or.overtime_date BETWEEN ? AND ?
            ORDER BY or.overtime_date DESC
        ");
        $stmt->execute([$personilId, $startDate, $endDate]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate monthly summary
        $summary = [
            'total_hours' => 0,
            'regular_hours' => 0,
            'holiday_hours' => 0,
            'weekend_hours' => 0,
            'emergency_hours' => 0,
            'total_compensation' => 0,
            'pending_approval' => 0,
            'approved_hours' => 0,
            'rejected_hours' => 0
        ];
        
        foreach ($records as $record) {
            $summary['total_hours'] += $record['overtime_hours'];
            $summary[$record['overtime_rate'] . '_hours'] += $record['overtime_hours'];
            $summary['total_compensation'] += $record['total_compensation'] ?? 0;
            
            if ($record['approval_status'] === 'pending') {
                $summary['pending_approval'] += $record['overtime_hours'];
            } elseif ($record['approval_status'] === 'approved') {
                $summary['approved_hours'] += $record['overtime_hours'];
            } elseif ($record['approval_status'] === 'rejected') {
                $summary['rejected_hours'] += $record['overtime_hours'];
            }
        }
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'records' => $records,
                'summary' => $summary,
                'period' => ['start' => $startDate, 'end' => $endDate, 'month' => $month, 'year' => $year]
            ]
        ]);
        exit;
    }

    // GET: Overtime statistics
    if ($action === 'get_overtime_statistics') {
        $period = $_GET['period'] ?? '30'; // days
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        $whereClause = "WHERE or.overtime_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get overall statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT or.personil_id) as unique_personnel,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(CASE WHEN or.approval_status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN or.approval_status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN or.approval_status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN or.approval_status = 'processed' THEN 1 END) as processed_count,
                AVG(or.overtime_hours) as avg_hours_per_record
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get overtime by rate type
        $stmt = $pdo->prepare("
            SELECT 
                or.overtime_rate,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(*) as record_count
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
            GROUP BY or.overtime_rate
            ORDER BY total_hours DESC
        ");
        $stmt->execute($params);
        $byRate = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top overtime personnel
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                pk.nama_pangkat,
                b.nama_bagian,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(*) as record_count
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian
            HAVING total_hours > 0
            ORDER BY total_hours DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $topPersonnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get overtime trends (daily)
        $stmt = $pdo->prepare("
            SELECT 
                or.overtime_date,
                SUM(or.overtime_hours) as daily_hours,
                COUNT(*) as daily_records,
                SUM(or.total_compensation) as daily_compensation
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
            GROUP BY or.overtime_date
            ORDER BY or.overtime_date
        ");
        $stmt->execute($params);
        $dailyTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'overall_stats' => $overallStats,
                'by_rate' => $byRate,
                'top_personnel' => $topPersonnel,
                'daily_trends' => $dailyTrends
            ]
        ]);
        exit;
    }

    // GET: Pending approvals
    if ($action === 'get_pending_approvals') {
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE or.approval_status = 'pending'";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT or.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   s.shift_type,
                   s.start_time as schedule_start,
                   s.end_time as schedule_end,
                   DATEDIFF(CURDATE(), or.created_at) as days_pending
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN schedules s ON s.id = or.schedule_id
            $whereClause
            ORDER BY or.created_at ASC
        ");
        $stmt->execute($params);
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$pending]);
        exit;
    }

    // GET: Overtime summary for dashboard
    if ($action === 'get_overtime_summary') {
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $whereClause = "WHERE or.overtime_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get monthly summary by bagian
        $stmt = $pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(DISTINCT or.personil_id) as personnel_count,
                SUM(or.overtime_hours) as total_hours,
                SUM(or.total_compensation) as total_compensation,
                COUNT(CASE WHEN or.approval_status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN or.approval_status = 'approved' THEN 1 END) as approved_count,
                AVG(or.overtime_hours) as avg_hours_per_person
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY total_hours DESC
        ");
        $stmt->execute($params);
        $byBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get weekly breakdown
        $stmt = $pdo->prepare("
            SELECT 
                WEEK(or.overtime_date, 1) as week_number,
                DATE(or.overtime_date) as week_start,
                SUM(or.overtime_hours) as weekly_hours,
                COUNT(*) as weekly_records,
                SUM(or.total_compensation) as weekly_compensation
            FROM overtime_records or
            JOIN personil p ON p.nrp = or.personil_id
            $whereClause
            GROUP BY WEEK(or.overtime_date, 1), DATE(or.overtime_date)
            ORDER BY week_number
        ");
        $stmt->execute($params);
        $weeklyBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'by_bagian' => $byBagian,
                'weekly_breakdown' => $weeklyBreakdown,
                'period' => ['start' => $startDate, 'end' => $endDate, 'month' => $month, 'year' => $year]
            ]
        ]);
        exit;
    }

    // POST: Add overtime record
    if ($action === 'add_overtime_record') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $overtimeDate = trim($_POST['overtime_date'] ?? '');
        $regularHours = (float)($_POST['regular_hours'] ?? 8);
        $overtimeHours = (float)($_POST['overtime_hours'] ?? 0);
        $overtimeRate = $_POST['overtime_rate'] ?? 'regular';
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$personilId || !$overtimeDate || $overtimeHours <= 0) {
            echo json_encode(['success'=>false,'error'=>'Personil ID, date, and overtime hours required']); exit;
        }
        
        // Validate overtime rate
        $validRates = ['regular', 'holiday', 'weekend', 'emergency'];
        if (!in_array($overtimeRate, $validRates)) {
            echo json_encode(['success'=>false,'error'=>'Invalid overtime rate']); exit;
        }
        
        // Get rate multiplier
        $rateMultipliers = [
            'regular' => 1.5,
            'holiday' => 2.0,
            'weekend' => 1.75,
            'emergency' => 2.5
        ];
        $rateMultiplier = $rateMultipliers[$overtimeRate];
        
        // Calculate compensation (assuming base rate - this would need actual salary data)
        $baseHourlyRate = 50000; // Placeholder - should get from personnel salary data
        $totalCompensation = $overtimeHours * $baseHourlyRate * $rateMultiplier;
        
        // Check for auto-approval
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'overtime_auto_approval_limit'");
        $stmt->execute();
        $autoApprovalLimit = (float)($stmt->fetchColumn() ?? 2);
        
        $approvalStatus = 'pending';
        if ($overtimeHours <= $autoApprovalLimit) {
            $approvalStatus = 'approved';
        }
        
        $pdo->beginTransaction();
        
        // Create overtime record
        $stmt = $pdo->prepare("
            INSERT INTO overtime_records 
            (personil_id, schedule_id, overtime_date, regular_hours, overtime_hours, 
             overtime_rate, rate_multiplier, total_compensation, approval_status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $personilId, $scheduleId ?: null, $overtimeDate, $regularHours, $overtimeHours,
            $overtimeRate, $rateMultiplier, $totalCompensation, $approvalStatus, $notes
        ]);
        
        $overtimeId = $pdo->lastInsertId();
        
        // Update schedule overtime info
        if ($scheduleId) {
            $stmt = $pdo->prepare("
                UPDATE schedules 
                SET overtime_hours = ?, overtime_rate = ?, overtime_approved = ?
                WHERE id = ?
            ");
            $stmt->execute([$overtimeHours, $overtimeRate, $approvalStatus === 'approved' ? 1 : 0, $scheduleId]);
        }
        
        $pdo->commit();
        
        ActivityLog::logCreate('overtime_record', $overtimeId, "Added overtime record: $overtimeHours hours for $personilId");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Overtime record added successfully',
            'overtime_id' => $overtimeId,
            'approval_status' => $approvalStatus,
            'total_compensation' => $totalCompensation
        ]);
        exit;
    }

    // POST: Update overtime record
    if ($action === 'update_overtime_record') {
        $overtimeId = (int)($_POST['overtime_id'] ?? 0);
        
        if (!$overtimeId) {
            echo json_encode(['success'=>false,'error'=>'Overtime ID required']); exit;
        }
        
        $fields = [
            'regular_hours' => (float)($_POST['regular_hours'] ?? 8),
            'overtime_hours' => (float)($_POST['overtime_hours'] ?? 0),
            'overtime_rate' => $_POST['overtime_rate'] ?? 'regular',
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validate overtime rate if provided
        if (isset($_POST['overtime_rate'])) {
            $validRates = ['regular', 'holiday', 'weekend', 'emergency'];
            if (!in_array($fields['overtime_rate'], $validRates)) {
                echo json_encode(['success'=>false,'error'=>'Invalid overtime rate']); exit;
            }
        }
        
        // Recalculate compensation if hours or rate changed
        if (isset($_POST['overtime_hours']) || isset($_POST['overtime_rate'])) {
            $rateMultipliers = [
                'regular' => 1.5,
                'holiday' => 2.0,
                'weekend' => 1.75,
                'emergency' => 2.5
            ];
            $rateMultiplier = $rateMultipliers[$fields['overtime_rate']];
            $baseHourlyRate = 50000; // Placeholder
            $fields['rate_multiplier'] = $rateMultiplier;
            $fields['total_compensation'] = $fields['overtime_hours'] * $baseHourlyRate * $rateMultiplier;
        }
        
        $setClause = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $stmt = $pdo->prepare("UPDATE overtime_records SET $setClause WHERE id = ?");
        $stmt->execute([...array_values($fields), $overtimeId]);
        
        ActivityLog::logUpdate('overtime_record', $overtimeId, "Updated overtime record");
        
        echo json_encode(['success'=>true,'message'=>'Overtime record updated successfully']);
        exit;
    }

    // POST: Approve/Reject overtime
    if ($action === 'process_overtime_approval') {
        $overtimeId = (int)($_POST['overtime_id'] ?? 0);
        $approvalStatus = $_POST['approval_status'] ?? '';
        $approvalNotes = trim($_POST['approval_notes'] ?? '');
        
        if (!$overtimeId || !$approvalStatus) {
            echo json_encode(['success'=>false,'error'=>'Overtime ID and approval status required']); exit;
        }
        
        $validStatuses = ['approved', 'rejected', 'processed'];
        if (!in_array($approvalStatus, $validStatuses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid approval status']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Update overtime record
        $stmt = $pdo->prepare("
            UPDATE overtime_records 
            SET approval_status = ?, approved_by = ?, approved_at = NOW(), notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$approvalStatus, $_SESSION['username'], $approvalNotes, $overtimeId]);
        
        // Update schedule if approved
        if ($approvalStatus === 'approved') {
            $stmt = $pdo->prepare("
                UPDATE schedules s
                JOIN overtime_records or ON or.schedule_id = s.id
                SET s.overtime_approved = 1, s.overtime_approved_by = ?
                WHERE or.id = ?
            ");
            $stmt->execute([$_SESSION['username'], $overtimeId]);
        }
        
        $pdo->commit();
        
        ActivityLog::logUpdate('overtime_record', $overtimeId, "Processed overtime approval: $approvalStatus");
        
        echo json_encode([
            'success'=>true,
            'message'=>"Overtime $approvalStatus successfully"
        ]);
        exit;
    }

    // POST: Delete overtime record
    if ($action === 'delete_overtime_record') {
        $overtimeId = (int)($_POST['overtime_id'] ?? 0);
        
        if (!$overtimeId) {
            echo json_encode(['success'=>false,'error'=>'Overtime ID required']); exit;
        }
        
        // Get overtime info for logging
        $stmt = $pdo->prepare("SELECT personil_id, overtime_hours, overtime_date FROM overtime_records WHERE id = ?");
        $stmt->execute([$overtimeId]);
        $overtimeInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$overtimeInfo) {
            echo json_encode(['success'=>false,'error'=>'Overtime record not found']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Delete overtime record
        $stmt = $pdo->prepare("DELETE FROM overtime_records WHERE id = ?");
        $stmt->execute([$overtimeId]);
        
        // Reset schedule overtime info
        $stmt = $pdo->prepare("
            UPDATE schedules 
            SET overtime_hours = 0, overtime_rate = 'regular', overtime_approved = 0, overtime_approved_by = NULL
            WHERE id IN (SELECT schedule_id FROM overtime_records WHERE id = ?)
        ");
        // This won't work after deletion, so we need to get schedule_id first
        $scheduleId = $pdo->prepare("SELECT schedule_id FROM overtime_records WHERE id = ?");
        $scheduleId->execute([$overtimeId]);
        $sid = $scheduleId->fetchColumn();
        
        if ($sid) {
            $stmt = $pdo->prepare("
                UPDATE schedules 
                SET overtime_hours = 0, overtime_rate = 'regular', overtime_approved = 0, overtime_approved_by = NULL
                WHERE id = ?
            ");
            $stmt->execute([$sid]);
        }
        
        $pdo->commit();
        
        ActivityLog::logDelete('overtime_record', $overtimeId, "Deleted overtime record: {$overtimeInfo['overtime_hours']} hours for {$overtimeInfo['personil_id']}");
        
        echo json_encode(['success'=>true,'message'=>'Overtime record deleted successfully']);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[overtime_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
