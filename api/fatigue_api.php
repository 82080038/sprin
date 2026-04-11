<?php
/**
 * Fatigue Management API
 * Monitor and manage personnel fatigue levels, rest periods, and wellness
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
    'get_fatigue_stats','get_personil_fatigue','get_fatigue_report',
    'get_wellness_dashboard','check_fatigue_violations','get_fatigue_trends'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Fatigue statistics dashboard
    if ($action === 'get_fatigue_stats') {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        
        $stats = [
            'total_personil' => 0,
            'high_fatigue' => 0,
            'medium_fatigue' => 0,
            'critical_fatigue' => 0,
            'weekly_violations' => 0,
            'avg_weekly_hours' => 0,
            'wellness_distribution' => ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0]
        ];

        // Get fatigue distribution
        $stmt = $pdo->prepare("
            SELECT fatigue_level, COUNT(*) as count
            FROM personil 
            WHERE is_active = 1 AND is_deleted = 0
            GROUP BY fatigue_level
        ");
        $stmt->execute();
        $fatigueDist = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stats['high_fatigue'] = $fatigueDist['high'] ?? 0;
        $stats['medium_fatigue'] = $fatigueDist['medium'] ?? 0;
        $stats['critical_fatigue'] = $fatigueDist['critical'] ?? 0;
        
        // Get wellness distribution
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN wellness_score >= 90 THEN 'excellent'
                    WHEN wellness_score >= 75 THEN 'good'
                    WHEN wellness_score >= 60 THEN 'fair'
                    ELSE 'poor'
                END as wellness_category,
                COUNT(*) as count
            FROM personil 
            WHERE is_active = 1 AND is_deleted = 0
            GROUP BY wellness_category
        ");
        $stmt->execute();
        $wellnessDist = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['wellness_distribution'] = array_merge($stats['wellness_distribution'], $wellnessDist);
        
        // Get weekly violations
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as violations
            FROM fatigue_tracking 
            WHERE tracking_date BETWEEN ? AND ?
            AND JSON_LENGTH(violations) > 0
        ");
        $stmt->execute([$weekStart, $weekEnd]);
        $stats['weekly_violations'] = $stmt->fetchColumn();
        
        // Get average weekly hours
        $stmt = $pdo->prepare("
            SELECT AVG(hours_worked) as avg_hours
            FROM fatigue_tracking 
            WHERE tracking_date BETWEEN ? AND ?
        ");
        $stmt->execute([$weekStart, $weekEnd]);
        $stats['avg_weekly_hours'] = round($stmt->fetchColumn(), 1);
        
        // Get total active personil
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE is_active = 1 AND is_deleted = 0");
        $stmt->execute();
        $stats['total_personil'] = $stmt->fetchColumn();
        
        echo json_encode(['success'=>true,'data'=>$stats]);
        exit;
    }

    // GET: Personil fatigue details
    if ($action === 'get_personil_fatigue') {
        $personilId = $_GET['personil_id'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        // Get personil info
        $stmt = $pdo->prepare("
            SELECT p.*, pk.nama_pangkat, b.nama_bagian, u.nama_unsur
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE p.nrp = ? AND p.is_active = 1
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil) {
            echo json_encode(['success'=>false,'error'=>'Personil not found']); exit;
        }
        
        // Get fatigue tracking data
        $stmt = $pdo->prepare("
            SELECT *
            FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date BETWEEN ? AND ?
            ORDER BY tracking_date DESC
        ");
        $stmt->execute([$personilId, $startDate, $endDate]);
        $tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get current week summary
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $pdo->prepare("
            SELECT 
                SUM(hours_worked) as total_hours,
                AVG(fatigue_score) as avg_fatigue_score,
                COUNT(*) as days_tracked,
                COUNT(CASE WHEN fatigue_level = 'critical' THEN 1 END) as critical_days,
                COUNT(CASE WHEN JSON_LENGTH(violations) > 0 THEN 1 END) as violation_days
            FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date >= ?
        ");
        $stmt->execute([$personilId, $weekStart]);
        $weekSummary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'tracking' => $tracking,
                'week_summary' => $weekSummary
            ]
        ]);
        exit;
    }

    // GET: Fatigue report for management
    if ($action === 'get_fatigue_report') {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $bagianId = $_GET['bagian_id'] ?? '';
        $reportType = $_GET['report_type'] ?? 'summary';
        
        $whereClause = "WHERE ft.tracking_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        if ($reportType === 'summary') {
            // Summary report
            $stmt = $pdo->prepare("
                SELECT 
                    b.nama_bagian,
                    COUNT(DISTINCT ft.personil_id) as total_personil,
                    AVG(ft.hours_worked) as avg_hours,
                    AVG(ft.fatigue_score) as avg_fatigue_score,
                    COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_cases,
                    COUNT(CASE WHEN JSON_LENGTH(ft.violations) > 0 THEN 1 END) as violation_cases,
                    COUNT(DISTINCT CASE WHEN ft.fatigue_level = 'critical' THEN ft.personil_id END) as critical_personil
                FROM fatigue_tracking ft
                JOIN personil p ON p.nrp = ft.personil_id
                JOIN bagian b ON b.id = p.id_bagian
                $whereClause
                GROUP BY b.id, b.nama_bagian
                ORDER BY critical_cases DESC, violation_cases DESC
            ");
            $stmt->execute($params);
            $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success'=>true,'data'=>['summary'=>$summary]]);
            exit;
            
        } elseif ($reportType === 'detailed') {
            // Detailed report with personil breakdown
            $stmt = $pdo->prepare("
                SELECT 
                    p.nrp,
                    p.nama,
                    pk.nama_pangkat,
                    b.nama_bagian,
                    u.nama_unsur,
                    p.wellness_score,
                    p.fatigue_level,
                    AVG(ft.hours_worked) as avg_hours,
                    MIN(ft.fatigue_score) as min_fatigue_score,
                    COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_days,
                    COUNT(CASE WHEN JSON_LENGTH(ft.violations) > 0 THEN 1 END) as violation_days,
                    COUNT(*) as total_days
                FROM fatigue_tracking ft
                JOIN personil p ON p.nrp = ft.personil_id
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                LEFT JOIN unsur u ON u.id = b.id_unsur
                $whereClause
                GROUP BY p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian, u.nama_unsur, p.wellness_score, p.fatigue_level
                HAVING critical_days > 0 OR violation_days > 0
                ORDER BY critical_days DESC, violation_days DESC, avg_hours DESC
            ");
            $stmt->execute($params);
            $detailed = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success'=>true,'data'=>['detailed'=>$detailed]]);
            exit;
        }
    }

    // GET: Wellness dashboard
    if ($action === 'get_wellness_dashboard') {
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get wellness distribution by bagian
        $stmt = $pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(*) as total_personil,
                AVG(p.wellness_score) as avg_wellness_score,
                COUNT(CASE WHEN p.wellness_score >= 90 THEN 1 END) as excellent_count,
                COUNT(CASE WHEN p.wellness_score >= 75 AND p.wellness_score < 90 THEN 1 END) as good_count,
                COUNT(CASE WHEN p.wellness_score >= 60 AND p.wellness_score < 75 THEN 1 END) as fair_count,
                COUNT(CASE WHEN p.wellness_score < 60 THEN 1 END) as poor_count
            FROM personil p
            JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY avg_wellness_score DESC
        ");
        $stmt->execute($params);
        $wellnessByBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top wellness concerns
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                pk.nama_pangkat,
                b.nama_bagian,
                p.wellness_score,
                p.fatigue_level,
                p.last_fatigue_check
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            ORDER BY p.wellness_score ASC, p.fatigue_level DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $concerns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'wellness_by_bagian' => $wellnessByBagian,
                'top_concerns' => $concerns
            ]
        ]);
        exit;
    }

    // POST: Update fatigue tracking
    if ($action === 'update_fatigue_tracking') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $trackingDate = trim($_POST['tracking_date'] ?? '');
        $hoursWorked = (float)($_POST['hours_worked'] ?? 0);
        $restHours = (float)($_POST['rest_hours'] ?? 0);
        $fatigueScore = (int)($_POST['fatigue_score'] ?? 100);
        $violations = $_POST['violations'] ?? '[]';
        
        if (!$personilId || !$trackingDate) {
            echo json_encode(['success'=>false,'error'=>'Personil ID and tracking date required']); exit;
        }
        
        // Calculate fatigue level
        $fatigueLevel = 'low';
        if ($fatigueScore < 40) {
            $fatigueLevel = 'critical';
        } elseif ($fatigueScore < 60) {
            $fatigueLevel = 'high';
        } elseif ($fatigueScore < 80) {
            $fatigueLevel = 'medium';
        }
        
        // Check for violations
        $violationList = json_decode($violations, true) ?: [];
        if ($hoursWorked > 12) {
            $violationList[] = ['type' => 'excessive_hours', 'value' => $hoursWorked];
        }
        if ($restHours < 12) {
            $violationList[] = ['type' => 'insufficient_rest', 'value' => $restHours];
        }
        
        $pdo->beginTransaction();
        
        // Update personil wellness score
        $stmt = $pdo->prepare("
            UPDATE personil 
            SET wellness_score = ?, fatigue_level = ?, last_fatigue_check = ?
            WHERE nrp = ?
        ");
        $stmt->execute([$fatigueScore, $fatigueLevel, $trackingDate, $personilId]);
        
        // Upsert fatigue tracking
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
        $stmt->execute([$personilId, $trackingDate, $hoursWorked, $restHours, $fatigueScore, $fatigueLevel, json_encode($violationList)]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('fatigue_tracking', $personilId, "Updated fatigue tracking for $trackingDate");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Fatigue tracking updated successfully',
            'fatigue_level' => $fatigueLevel,
            'violations_count' => count($violationList)
        ]);
        exit;
    }

    // POST: Check fatigue violations
    if ($action === 'check_fatigue_violations') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $checkDate = trim($_POST['check_date'] ?? date('Y-m-d'));
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        // Get system settings
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'fatigue_max_weekly_hours'");
        $stmt->execute();
        $maxWeeklyHours = (float)($stmt->fetchColumn() ?? 40);
        
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'fatigue_min_rest_hours'");
        $stmt->execute();
        $minRestHours = (float)($stmt->fetchColumn() ?? 12);
        
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'fatigue_consecutive_days_limit'");
        $stmt->execute();
        $maxConsecutiveDays = (int)($stmt->fetchColumn() ?? 7);
        
        // Check weekly hours
        $weekStart = date('Y-m-d', strtotime('monday', strtotime($checkDate)));
        $weekEnd = date('Y-m-d', strtotime('sunday', strtotime($checkDate)));
        
        $stmt = $pdo->prepare("
            SELECT SUM(hours_worked) as total_hours
            FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date BETWEEN ? AND ?
        ");
        $stmt->execute([$personilId, $weekStart, $weekEnd]);
        $weeklyHours = (float)($stmt->fetchColumn() ?: 0);
        
        // Check consecutive days
        $stmt = $pdo->prepare("
            SELECT tracking_date, hours_worked
            FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date <= ?
            ORDER BY tracking_date DESC
            LIMIT 30
        ");
        $stmt->execute([$personilId, $checkDate]);
        $recentDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $consecutiveDays = 0;
        $lastWorkingDay = null;
        
        foreach ($recentDays as $day) {
            if ($day['hours_worked'] > 0) {
                if ($lastWorkingDay === null || 
                    date('Y-m-d', strtotime($lastWorkingDay . ' -1 day')) === $day['tracking_date']) {
                    $consecutiveDays++;
                    $lastWorkingDay = $day['tracking_date'];
                } else {
                    break;
                }
            }
        }
        
        // Check rest periods
        $stmt = $pdo->prepare("
            SELECT tracking_date, rest_hours
            FROM fatigue_tracking 
            WHERE personil_id = ? AND tracking_date <= ?
            ORDER BY tracking_date DESC
            LIMIT 7
        ");
        $stmt->execute([$personilId, $checkDate]);
        $restPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $violations = [];
        
        if ($weeklyHours > $maxWeeklyHours) {
            $violations[] = [
                'type' => 'weekly_hours_exceeded',
                'current' => $weeklyHours,
                'limit' => $maxWeeklyHours,
                'severity' => 'high'
            ];
        }
        
        if ($consecutiveDays > $maxConsecutiveDays) {
            $violations[] = [
                'type' => 'consecutive_days_exceeded',
                'current' => $consecutiveDays,
                'limit' => $maxConsecutiveDays,
                'severity' => 'medium'
            ];
        }
        
        foreach ($restPeriods as $period) {
            if ($period['rest_hours'] < $minRestHours) {
                $violations[] = [
                    'type' => 'insufficient_rest',
                    'date' => $period['tracking_date'],
                    'current' => $period['rest_hours'],
                    'limit' => $minRestHours,
                    'severity' => 'medium'
                ];
            }
        }
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'violations' => $violations,
                'weekly_hours' => $weeklyHours,
                'consecutive_days' => $consecutiveDays,
                'risk_level' => count($violations) > 2 ? 'high' : (count($violations) > 0 ? 'medium' : 'low')
            ]
        ]);
        exit;
    }

    // GET: Fatigue trends
    if ($action === 'get_fatigue_trends') {
        $period = $_GET['period'] ?? '30'; // days
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        $whereClause = "WHERE ft.tracking_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get daily trends
        $stmt = $pdo->prepare("
            SELECT 
                ft.tracking_date,
                AVG(ft.hours_worked) as avg_hours,
                AVG(ft.fatigue_score) as avg_fatigue_score,
                COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_count,
                COUNT(CASE WHEN JSON_LENGTH(ft.violations) > 0 THEN 1 END) as violation_count,
                COUNT(*) as total_records
            FROM fatigue_tracking ft
            JOIN personil p ON p.nrp = ft.personil_id
            $whereClause
            GROUP BY ft.tracking_date
            ORDER BY ft.tracking_date
        ");
        $stmt->execute($params);
        $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>['trends'=>$trends]]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[fatigue_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
