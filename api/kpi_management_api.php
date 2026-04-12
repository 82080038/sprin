<?php
/**
 * KPI Management API - Formal Performance Evaluation Framework
 * Track, evaluate, and manage Key Performance Indicators for personnel
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
    'get_kpi_templates','get_personil_kpi','get_kpi_evaluations',
    'get_kpi_statistics','get_kpi_dashboard','get_kpi_history'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: KPI Templates
    if ($action === 'get_kpi_templates') {
        $jabatanId = $_GET['jabatan_id'] ?? '';
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($jabatanId) {
            $whereClause .= " AND kt.jabatan_id = ?";
            $params[] = $jabatanId;
        }
        
        if ($bagianId) {
            $whereClause .= " AND kt.bagian_id = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT kt.*, 
                   j.nama_jabatan,
                   b.nama_bagian,
                   u.nama_unsur
            FROM kpi_templates kt
            LEFT JOIN jabatan j ON j.id = kt.jabatan_id
            LEFT JOIN bagian b ON b.id = kt.bagian_id
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY kt.template_name ASC
        ");
        $stmt->execute($params);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$templates]);
        exit;
    }

    // GET: Personil KPI
    if ($action === 'get_personil_kpi') {
        $personilId = trim($_GET['personil_id'] ?? '');
        $period = $_GET['period'] ?? date('Y-m');
        $status = $_GET['status'] ?? '';
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        $whereClause = "WHERE pk.personil_id = ? AND pk.evaluation_period = ?";
        $params = [$personilId, $period];
        
        if ($status) {
            $whereClause .= " AND pk.status = ?";
            $params[] = $status;
        }
        
        $stmt = $pdo->prepare("
            SELECT pk.*, 
                   kt.template_name,
                   kt.kpi_category,
                   kt.weight_percentage,
                   kt.target_value,
                   kt.measurement_unit,
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian
            FROM personil_kpi pk
            LEFT JOIN kpi_templates kt ON kt.id = pk.template_id
            JOIN personil p ON p.nrp = pk.personil_id
            LEFT JOIN pangkat pk_pangkat ON pk_pangkat.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause
            ORDER BY kt.kpi_category ASC, kt.template_name ASC
        ");
        $stmt->execute($params);
        $kpiData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$kpiData]);
        exit;
    }

    // GET: KPI Evaluations
    if ($action === 'get_kpi_evaluations') {
        $personilId = $_GET['personil_id'] ?? '';
        $period = $_GET['period'] ?? date('Y-m');
        $evaluationType = $_GET['evaluation_type'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($personilId) {
            $whereClause .= " AND ke.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($period) {
            $whereClause .= " AND ke.evaluation_period = ?";
            $params[] = $period;
        }
        
        if ($evaluationType) {
            $whereClause .= " AND ke.evaluation_type = ?";
            $params[] = $evaluationType;
        }
        
        $stmt = $pdo->prepare("
            SELECT ke.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   evaluator.nama as evaluator_name,
                   evaluator_pangkat.nama_pangkat as evaluator_pangkat,
                   CASE ke.evaluation_type
                       WHEN 'quarterly' THEN 'Kuartalan'
                       WHEN 'semi_annual' THEN 'Semesteran'
                       WHEN 'annual' THEN 'Tahunan'
                       WHEN 'special' THEN 'Khusus'
                       ELSE ke.evaluation_type
                   END as evaluation_type_display,
                   CASE ke.status
                       WHEN 'draft' THEN 'Draft'
                       WHEN 'submitted' THEN 'Diajukan'
                       WHEN 'reviewed' THEN 'Direview'
                       WHEN 'approved' => 'Disetujui'
                       WHEN 'rejected' => 'Ditolak'
                       ELSE ke.status
                   END as status_display
            FROM kpi_evaluations ke
            JOIN personil p ON p.nrp = ke.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN personil evaluator ON evaluator.nrp = ke.evaluator_id
            LEFT JOIN pangkat evaluator_pangkat ON evaluator_pangkat.id = evaluator.id_pangkat
            $whereClause
            ORDER BY ke.created_at DESC
        ");
        $stmt->execute($params);
        $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$evaluations]);
        exit;
    }

    // GET: KPI Statistics
    if ($action === 'get_kpi_statistics') {
        $period = $_GET['period'] ?? date('Y-m');
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE ke.evaluation_period = ?";
        $params = [$period];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stats = [
            'total_evaluations' => 0,
            'completed_evaluations' => 0,
            'pending_evaluations' => 0,
            'average_score' => 0,
            'performance_distribution' => [],
            'category_performance' => [],
            'top_performers' => [],
            'improvement_needed' => []
        ];
        
        // Get basic statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ke.status = 'approved' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN ke.status IN ('draft', 'submitted') THEN 1 ELSE 0 END) as pending,
                AVG(ke.overall_score) as avg_score
            FROM kpi_evaluations ke
            JOIN personil p ON p.nrp = ke.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_evaluations'] = (int)$result['total'];
        $stats['completed_evaluations'] = (int)$result['completed'];
        $stats['pending_evaluations'] = (int)$result['pending'];
        $stats['average_score'] = round((float)$result['avg_score'], 2);
        
        // Get performance distribution
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN ke.overall_score >= 90 THEN 'Excellent'
                    WHEN ke.overall_score >= 80 THEN 'Good'
                    WHEN ke.overall_score >= 70 THEN 'Satisfactory'
                    WHEN ke.overall_score >= 60 THEN 'Needs Improvement'
                    ELSE 'Poor'
                END as performance_level,
                COUNT(*) as count
            FROM kpi_evaluations ke
            JOIN personil p ON p.nrp = ke.personil_id
            $whereClause AND ke.status = 'approved'
            GROUP BY performance_level
        ");
        $stmt->execute($params);
        $stats['performance_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get category performance
        $stmt = $pdo->prepare("
            SELECT 
                kt.kpi_category,
                AVG(pk.actual_score) as avg_score,
                COUNT(*) as count
            FROM personil_kpi pk
            LEFT JOIN kpi_templates kt ON kt.id = pk.template_id
            LEFT JOIN kpi_evaluations ke ON ke.id = pk.evaluation_id
            JOIN personil p ON p.nrp = pk.personil_id
            $whereClause AND ke.status = 'approved'
            GROUP BY kt.kpi_category
        ");
        $stmt->execute($params);
        $stats['category_performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top performers
        $stmt = $pdo->prepare("
            SELECT 
                p.nama,
                pk.nama_pangkat,
                b.nama_bagian,
                ke.overall_score,
                ke.evaluation_period
            FROM kpi_evaluations ke
            JOIN personil p ON p.nrp = ke.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause AND ke.status = 'approved'
            ORDER BY ke.overall_score DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $stats['top_performers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get improvement needed
        $stmt = $pdo->prepare("
            SELECT 
                p.nama,
                pk.nama_pangkat,
                b.nama_bagian,
                ke.overall_score,
                ke.evaluation_period
            FROM kpi_evaluations ke
            JOIN personil p ON p.nrp = ke.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            $whereClause AND ke.status = 'approved' AND ke.overall_score < 70
            ORDER BY ke.overall_score ASC
            LIMIT 10
        ");
        $stmt->execute($params);
        $stats['improvement_needed'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$stats]);
        exit;
    }

    // GET: KPI Dashboard
    if ($action === 'get_kpi_dashboard') {
        $period = $_GET['period'] ?? date('Y-m');
        
        $dashboard = [
            'key_metrics' => getKPIDashboardMetrics($pdo, $period),
            'trend_indicators' => getKPITrendIndicators($pdo, $period),
            'alerts' => getKPIAlerts($pdo, $period),
            'upcoming_evaluations' => getUpcomingEvaluations($pdo, $period)
        ];
        
        echo json_encode(['success'=>true,'data'=>$dashboard]);
        exit;
    }

    // POST: Create KPI Template
    if ($action === 'create_kpi_template') {
        $templateName = trim($_POST['template_name'] ?? '');
        $kpiCategory = in_array($_POST['kpi_category'] ?? '', ['operational','behavioral','developmental','strategic']) ? $_POST['kpi_category'] : 'operational';
        $description = trim($_POST['description'] ?? '');
        $targetValue = (float)($_POST['target_value'] ?? 0);
        $measurementUnit = trim($_POST['measurement_unit'] ?? '');
        $weightPercentage = (float)($_POST['weight_percentage'] ?? 0);
        $jabatanId = !empty($_POST['jabatan_id']) ? (int)$_POST['jabatan_id'] : null;
        $bagianId = !empty($_POST['bagian_id']) ? (int)$_POST['bagian_id'] : null;
        $evaluationMethod = trim($_POST['evaluation_method'] ?? '');
        
        if (!$templateName || !$kpiCategory) {
            echo json_encode(['success'=>false,'error'=>'Template name and category are required']); exit;
        }
        
        if ($weightPercentage <= 0 || $weightPercentage > 100) {
            echo json_encode(['success'=>false,'error'=>'Weight percentage must be between 0 and 100']); exit;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO kpi_templates 
            (template_name, kpi_category, description, target_value, measurement_unit, 
             weight_percentage, jabatan_id, bagian_id, evaluation_method, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $templateName, $kpiCategory, $description, $targetValue, $measurementUnit,
            $weightPercentage, $jabatanId, $bagianId, $evaluationMethod, $_SESSION['username']
        ]);
        
        echo json_encode([
            'success'=>true,
            'message'=>'KPI template created successfully',
            'template_id' => $pdo->lastInsertId()
        ]);
    }

    // POST: Create KPI Evaluation
    if ($action === 'create_kpi_evaluation') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $evaluationType = in_array($_POST['evaluation_type'] ?? '', ['quarterly','semi_annual','annual','special']) ? $_POST['evaluation_type'] : 'quarterly';
        $evaluationPeriod = $_POST['evaluation_period'] ?? date('Y-m');
        $kpiData = json_decode($_POST['kpi_data'] ?? '[]', true);
        $overallComments = trim($_POST['overall_comments'] ?? '');
        $developmentPlan = trim($_POST['development_plan'] ?? '');
        
        if (!$personilId || empty($kpiData)) {
            echo json_encode(['success'=>false,'error'=>'Personil ID and KPI data are required']); exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Create evaluation record
            $stmt = $pdo->prepare("
                INSERT INTO kpi_evaluations 
                (personil_id, evaluation_type, evaluation_period, overall_comments, 
                 development_plan, status, created_by)
                VALUES (?, ?, ?, ?, ?, 'draft', ?)
            ");
            $stmt->execute([
                $personilId, $evaluationType, $evaluationPeriod, $overallComments,
                $developmentPlan, $_SESSION['username']
            ]);
            
            $evaluationId = $pdo->lastInsertId();
            
            // Add KPI items
            $totalWeightedScore = 0;
            $totalWeight = 0;
            
            foreach ($kpiData as $kpi) {
                $templateId = (int)($kpi['template_id'] ?? 0);
                $actualValue = (float)($kpi['actual_value'] ?? 0);
                $actualScore = (float)($kpi['actual_score'] ?? 0);
                $comments = trim($kpi['comments'] ?? '');
                
                // Get template details for weight
                $stmt = $pdo->prepare("SELECT weight_percentage FROM kpi_templates WHERE id = ?");
                $stmt->execute([$templateId]);
                $template = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($template) {
                    $weight = $template['weight_percentage'];
                    $weightedScore = ($actualScore / 100) * $weight;
                    $totalWeightedScore += $weightedScore;
                    $totalWeight += $weight;
                    
                    // Insert personil KPI record
                    $stmt = $pdo->prepare("
                        INSERT INTO personil_kpi 
                        (evaluation_id, template_id, personil_id, evaluation_period, 
                         actual_value, actual_score, comments, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                    ");
                    $stmt->execute([
                        $evaluationId, $templateId, $personilId, $evaluationPeriod,
                        $actualValue, $actualScore, $comments
                    ]);
                }
            }
            
            // Calculate overall score
            $overallScore = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) * 100 : 0;
            
            // Update evaluation with overall score
            $stmt = $pdo->prepare("
                UPDATE kpi_evaluations 
                SET overall_score = ?, total_weight = ?
                WHERE id = ?
            ");
            $stmt->execute([$overallScore, $totalWeight, $evaluationId]);
            
            // Create notification for evaluator
            $notificationService = new NotificationService($pdo);
            $notificationService->createNotification([
                'type' => 'kpi_evaluation',
                'title' => 'Evaluasi KPI Baru',
                'message' => "Evaluasi KPI untuk periode $evaluationPeriod menunggu review",
                'target_group' => ['supervisors'],
                'priority' => 'medium',
                'action_required' => true,
                'action_url' => "kpi_management.php?action=review&id=$evaluationId",
                'created_by' => 'system'
            ]);
            
            $pdo->commit();
            
            echo json_encode([
                'success'=>true,
                'message'=>'KPI evaluation created successfully',
                'evaluation_id' => $evaluationId,
                'overall_score' => $overallScore
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    // POST: Process KPI Evaluation Approval
    if ($action === 'process_kpi_approval') {
        $evaluationId = (int)($_POST['evaluation_id'] ?? 0);
        $action = $_POST['approval_action'] ?? '';
        $comments = trim($_POST['comments'] ?? '');
        $currentUser = $_SESSION['username'] ?? '';
        
        if (!$evaluationId || !in_array($action, ['submit', 'review', 'approve', 'reject'])) {
            echo json_encode(['success'=>false,'error'=>'Invalid evaluation ID or action']); exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Get current evaluation
            $stmt = $pdo->prepare("SELECT * FROM kpi_evaluations WHERE id = ?");
            $stmt->execute([$evaluationId]);
            $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$evaluation) {
                throw new Exception('Evaluation not found');
            }
            
            // Update status based on action
            $newStatus = $action === 'reject' ? 'rejected' : 
                       ($action === 'approve' ? 'approved' : 
                       ($action === 'review' ? 'reviewed' : 'submitted'));
            
            $updateFields = [
                'status' => $newStatus,
                'evaluator_id' => $currentUser,
                'evaluator_comments' => $comments
            ];
            
            if ($action === 'approve') {
                $updateFields['approved_at'] = date('Y-m-d H:i:s');
            }
            
            $setClause = implode(' = ?, ', array_keys($updateFields)) . ' = ?';
            $stmt = $pdo->prepare("
                UPDATE kpi_evaluations 
                SET $setClause
                WHERE id = ?
            ");
            $stmt->execute([...array_values($updateFields), $evaluationId]);
            
            // Create notification for personil
            $notificationService = new NotificationService($pdo);
            $notificationService->createNotification([
                'type' => 'kpi_status',
                'title' => "Evaluasi KPI $newStatus",
                'message' => "Evaluasi KPI Anda telah " . strtolower($newStatus),
                'target_personil' => $evaluation['personil_id'],
                'priority' => $action === 'approve' ? 'low' : 'high',
                'action_required' => false,
                'created_by' => $currentUser
            ]);
            
            $pdo->commit();
            
            echo json_encode([
                'success'=>true,
                'message'=>"KPI evaluation $action successfully"
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

} catch (Exception $e) {
    error_log('[kpi_management_api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

// Helper Functions

function getKPIDashboardMetrics($pdo, $period) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_evaluations,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as completed,
            AVG(overall_score) as avg_score,
            SUM(CASE WHEN overall_score >= 90 THEN 1 ELSE 0 END) as excellent_count,
            SUM(CASE WHEN overall_score < 70 THEN 1 ELSE 0 END) as improvement_count
        FROM kpi_evaluations 
        WHERE evaluation_period = ?
    ");
    $stmt->execute([$period]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_evaluations' => (int)$result['total_evaluations'],
        'completion_rate' => $result['total_evaluations'] > 0 ? 
            round(($result['completed'] / $result['total_evaluations']) * 100, 2) : 0,
        'average_score' => round((float)$result['avg_score'], 2),
        'excellent_performers' => (int)$result['excellent_count'],
        'improvement_needed' => (int)$result['improvement_count']
    ];
}

function getKPITrendIndicators($pdo, $period) {
    // Compare with previous period
    $currentPeriod = $period;
    $previousPeriod = date('Y-m', strtotime($currentPeriod . ' -1 month'));
    
    $stmt = $pdo->prepare("
        SELECT AVG(overall_score) as avg_score
        FROM kpi_evaluations 
        WHERE evaluation_period = ? AND status = 'approved'
    ");
    
    $stmt->execute([$currentPeriod]);
    $current = $stmt->fetchColumn();
    
    $stmt->execute([$previousPeriod]);
    $previous = $stmt->fetchColumn();
    
    $trend = 'stable';
    if ($current > $previous + 2) $trend = 'improving';
    elseif ($current < $previous - 2) $trend = 'declining';
    
    return [
        'score_trend' => $trend,
        'completion_trend' => 'stable',
        'participation_trend' => 'stable'
    ];
}

function getKPIAlerts($pdo, $period) {
    $alerts = [];
    
    // Check for overdue evaluations
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM kpi_evaluations 
        WHERE evaluation_period < ? AND status IN ('draft', 'submitted')
    ");
    $stmt->execute([$period]);
    $overdue = $stmt->fetchColumn();
    
    if ($overdue > 0) {
        $alerts[] = [
            'type' => 'overdue_evaluations',
            'severity' => 'high',
            'message' => "$overdue evaluasi KPI terlambat",
            'action' => 'Proses evaluasi yang tertunda'
        ];
    }
    
    // Check for low performers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM kpi_evaluations 
        WHERE evaluation_period = ? AND status = 'approved' AND overall_score < 60
    ");
    $stmt->execute([$period]);
    $lowPerformers = $stmt->fetchColumn();
    
    if ($lowPerformers > 0) {
        $alerts[] = [
            'type' => 'low_performance',
            'severity' => 'medium',
            'message' => "$lowPerformers personil dengan performa rendah",
            'action' => 'Buat rencana peningkatan'
        ];
    }
    
    return $alerts;
}

function getUpcomingEvaluations($pdo, $period) {
    $nextPeriod = date('Y-m', strtotime($period . ' +1 month'));
    
    $stmt = $pdo->prepare("
        SELECT p.nama, pk.nama_pangkat, b.nama_bagian
        FROM personil p
        LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
        LEFT JOIN bagian b ON b.id = p.id_bagian
        WHERE p.is_active = 1 AND p.is_deleted = 0
        AND NOT EXISTS (
            SELECT 1 FROM kpi_evaluations ke 
            WHERE ke.personil_id = p.nrp AND ke.evaluation_period = ?
        )
        LIMIT 5
    ");
    $stmt->execute([$nextPeriod]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
