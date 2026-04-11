<?php
/**
 * Certification & Training Compliance API
 * Manage personnel certifications, training requirements, and compliance tracking
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
    'get_certifications','get_personil_certifications','get_expiring_certifications',
    'get_training_compliance','get_compliance_dashboard','get_certification_statistics'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: All certifications with filters
    if ($action === 'get_certifications') {
        $personilId = $_GET['personil_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $expiringDays = $_GET['expiring_days'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($personilId) {
            $whereClause .= " AND c.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($status) {
            $whereClause .= " AND c.status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $whereClause .= " AND c.certification_type = ?";
            $params[] = $type;
        }
        
        if ($expiringDays) {
            $whereClause .= " AND c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
            $params[] = $expiringDays;
        }
        
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY c.expiry_date ASC, c.certification_type
        ");
        $stmt->execute($params);
        $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$certifications]);
        exit;
    }

    // GET: Personil certifications
    if ($action === 'get_personil_certifications') {
        $personilId = trim($_GET['personil_id'] ?? '');
        
        if (!$personilId) {
            echo json_encode(['success'=>false,'error'=>'Personil ID required']); exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry,
                   CASE 
                       WHEN c.expiry_date < CURDATE() THEN 'expired'
                       WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
                       ELSE 'valid'
                   END as urgency_status
            FROM certifications c
            WHERE c.personil_id = ?
            ORDER BY c.expiry_date ASC
        ");
        $stmt->execute([$personilId]);
        $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        // Get required certifications for position
        $stmt = $pdo->prepare("
            SELECT required_certifications 
            FROM jabatan j
            JOIN personil p ON p.id_jabatan = j.id
            WHERE p.nrp = ?
        ");
        $stmt->execute([$personilId]);
        $requiredCerts = $stmt->fetchColumn();
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'certifications' => $certifications,
                'required_certifications' => $requiredCerts ? json_decode($requiredCerts, true) : []
            ]
        ]);
        exit;
    }

    // GET: Expiring certifications
    if ($action === 'get_expiring_certifications') {
        $days = (int)($_GET['days'] ?? 30);
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $params = [$days];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry,
                   CASE 
                       WHEN c.expiry_date < CURDATE() THEN 'expired'
                       WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'critical'
                       WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 'urgent'
                       ELSE 'warning'
                   END as urgency_level
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY c.expiry_date ASC
        ");
        $stmt->execute($params);
        $expiring = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$expiring]);
        exit;
    }

    // GET: Training compliance
    if ($action === 'get_training_compliance') {
        $personilId = $_GET['personil_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $trainingType = $_GET['training_type'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($personilId) {
            $whereClause .= " AND tc.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($status) {
            $whereClause .= " AND tc.status = ?";
            $params[] = $status;
        }
        
        if ($trainingType) {
            $whereClause .= " AND tc.training_type = ?";
            $params[] = $trainingType;
        }
        
        $stmt = $pdo->prepare("
            SELECT tc.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   DATEDIFF(tc.next_due, CURDATE()) as days_to_due,
                   CASE 
                       WHEN tc.next_due < CURDATE() THEN 'overdue'
                       WHEN tc.next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'due_soon'
                       ELSE 'on_track'
                   END as due_status
            FROM training_compliance tc
            JOIN personil p ON p.nrp = tc.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY tc.next_due ASC, tc.status
        ");
        $stmt->execute($params);
        $training = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$training]);
        exit;
    }

    // GET: Compliance dashboard
    if ($action === 'get_compliance_dashboard') {
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get certification compliance stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT p.nrp) as total_personil,
                COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) as valid_certifications,
                COUNT(DISTINCT CASE WHEN c.status = 'expired' THEN p.nrp END) as expired_certifications,
                COUNT(DISTINCT CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.expiry_date >= CURDATE() THEN p.nrp END) as expiring_soon,
                COUNT(*) as total_certifications
            FROM personil p
            LEFT JOIN certifications c ON c.personil_id = p.nrp
            $whereClause
        ");
        $stmt->execute($params);
        $certStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get training compliance stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT tc.personil_id) as total_in_training,
                COUNT(CASE WHEN tc.status = 'completed' THEN 1 END) as completed_training,
                COUNT(CASE WHEN tc.status = 'required' THEN 1 END) as required_training,
                COUNT(CASE WHEN tc.next_due < CURDATE() THEN 1 END) as overdue_training,
                COUNT(CASE WHEN tc.next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND tc.next_due >= CURDATE() THEN 1 END) as due_soon_training
            FROM training_compliance tc
            JOIN personil p ON p.nrp = tc.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $trainingStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get compliance by bagian
        $stmt = $pdo->prepare("
            SELECT 
                b.nama_bagian,
                COUNT(DISTINCT p.nrp) as total_personil,
                COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) as compliant_personil,
                ROUND(COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) * 100.0 / COUNT(DISTINCT p.nrp), 2) as compliance_rate,
                COUNT(CASE WHEN c.status = 'expired' THEN 1 END) as expired_count,
                COUNT(CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.expiry_date >= CURDATE() THEN 1 END) as expiring_count
            FROM personil p
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN certifications c ON c.personil_id = p.nrp
            $whereClause
            GROUP BY b.id, b.nama_bagian
            ORDER BY compliance_rate DESC
        ");
        $stmt->execute($params);
        $complianceByBagian = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top compliance issues
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                pk.nama_pangkat,
                b.nama_bagian,
                COUNT(CASE WHEN c.status = 'expired' THEN 1 END) as expired_count,
                COUNT(CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.expiry_date >= CURDATE() THEN 1 END) as expiring_count,
                COUNT(CASE WHEN tc.next_due < CURDATE() THEN 1 END) as overdue_training
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN certifications c ON c.personil_id = p.nrp
            LEFT JOIN training_compliance tc ON tc.personil_id = p.nrp
            $whereClause
            GROUP BY p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian
            HAVING expired_count > 0 OR expiring_count > 0 OR overdue_training > 0
            ORDER BY expired_count DESC, expiring_count DESC, overdue_training DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $complianceIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'certification_stats' => $certStats,
                'training_stats' => $trainingStats,
                'compliance_by_bagian' => $complianceByBagian,
                'compliance_issues' => $complianceIssues
            ]
        ]);
        exit;
    }

    // POST: Add certification
    if ($action === 'add_certification') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $certType = trim($_POST['certification_type'] ?? '');
        $certName = trim($_POST['certification_name'] ?? '');
        $issuingAuthority = trim($_POST['issuing_authority'] ?? '');
        $certNumber = trim($_POST['certificate_number'] ?? '');
        $issueDate = trim($_POST['issue_date'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $attachmentPath = trim($_POST['attachment_path'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$personilId || !$certType || !$certName || !$expiryDate) {
            echo json_encode(['success'=>false,'error'=>'Personil ID, type, name, and expiry date required']); exit;
        }
        
        // Determine status based on expiry date
        $status = 'valid';
        if ($expiryDate < date('Y-m-d')) {
            $status = 'expired';
        } elseif ($expiryDate <= date('Y-m-d', strtotime('+30 days'))) {
            $status = 'expiring';
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO certifications 
            (personil_id, certification_type, certification_name, issuing_authority, 
             certificate_number, issue_date, expiry_date, status, attachment_path, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $personilId, $certType, $certName, $issuingAuthority,
            $certNumber, $issueDate, $expiryDate, $status, $attachmentPath, $notes
        ]);
        
        $certId = $pdo->lastInsertId();
        
        ActivityLog::logCreate('certification', $certId, "Added certification: $certName for $personilId");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Certification added successfully',
            'certification_id' => $certId,
            'status' => $status
        ]);
        exit;
    }

    // POST: Update certification
    if ($action === 'update_certification') {
        $certId = (int)($_POST['certification_id'] ?? 0);
        
        if (!$certId) {
            echo json_encode(['success'=>false,'error'=>'Certification ID required']); exit;
        }
        
        $fields = [
            'certification_type' => trim($_POST['certification_type'] ?? ''),
            'certification_name' => trim($_POST['certification_name'] ?? ''),
            'issuing_authority' => trim($_POST['issuing_authority'] ?? ''),
            'certificate_number' => trim($_POST['certificate_number'] ?? ''),
            'issue_date' => trim($_POST['issue_date'] ?? ''),
            'expiry_date' => trim($_POST['expiry_date'] ?? ''),
            'attachment_path' => trim($_POST['attachment_path'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Remove empty fields
        $fields = array_filter($fields, function($value) {
            return $value !== '';
        });
        
        if (empty($fields)) {
            echo json_encode(['success'=>false,'error'=>'No fields to update']); exit;
        }
        
        // Update status if expiry date changed
        if (isset($fields['expiry_date'])) {
            $expiryDate = $fields['expiry_date'];
            if ($expiryDate < date('Y-m-d')) {
                $fields['status'] = 'expired';
            } elseif ($expiryDate <= date('Y-m-d', strtotime('+30 days'))) {
                $fields['status'] = 'expiring';
            } else {
                $fields['status'] = 'valid';
            }
        }
        
        $setClause = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $stmt = $pdo->prepare("UPDATE certifications SET $setClause WHERE id = ?");
        $stmt->execute([...array_values($fields), $certId]);
        
        ActivityLog::logUpdate('certification', $certId, "Updated certification");
        
        echo json_encode(['success'=>true,'message'=>'Certification updated successfully']);
        exit;
    }

    // POST: Add training compliance
    if ($action === 'add_training_compliance') {
        $personilId = trim($_POST['personil_id'] ?? '');
        $trainingType = trim($_POST['training_type'] ?? '');
        $trainingName = trim($_POST['training_name'] ?? '');
        $provider = trim($_POST['provider'] ?? '');
        $trainingDate = trim($_POST['training_date'] ?? '');
        $completionDate = trim($_POST['completion_date'] ?? '');
        $status = $_POST['status'] ?? 'required';
        $hoursCompleted = (float)($_POST['hours_completed'] ?? 0);
        $requiredHours = (float)($_POST['required_hours'] ?? 0);
        $nextDue = trim($_POST['next_due'] ?? '');
        $certificateRequired = isset($_POST['certificate_required']) ? 1 : 0;
        $certificatePath = trim($_POST['completion_certificate_path'] ?? '');
        
        if (!$personilId || !$trainingType || !$trainingName) {
            echo json_encode(['success'=>false,'error'=>'Personil ID, type, and name required']); exit;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO training_compliance 
            (personil_id, training_type, training_name, provider, training_date, 
             completion_date, status, hours_completed, required_hours, next_due, 
             certificate_required, completion_certificate_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $personilId, $trainingType, $trainingName, $provider, $trainingDate,
            $completionDate, $status, $hoursCompleted, $requiredHours, $nextDue,
            $certificateRequired, $certificatePath
        ]);
        
        $trainingId = $pdo->lastInsertId();
        
        ActivityLog::logCreate('training_compliance', $trainingId, "Added training: $trainingName for $personilId");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Training compliance added successfully',
            'training_id' => $trainingId
        ]);
        exit;
    }

    // POST: Update training compliance
    if ($action === 'update_training_compliance') {
        $trainingId = (int)($_POST['training_id'] ?? 0);
        
        if (!$trainingId) {
            echo json_encode(['success'=>false,'error'=>'Training ID required']); exit;
        }
        
        $fields = [
            'training_type' => trim($_POST['training_type'] ?? ''),
            'training_name' => trim($_POST['training_name'] ?? ''),
            'provider' => trim($_POST['provider'] ?? ''),
            'training_date' => trim($_POST['training_date'] ?? ''),
            'completion_date' => trim($_POST['completion_date'] ?? ''),
            'status' => $_POST['status'] ?? '',
            'hours_completed' => (float)($_POST['hours_completed'] ?? 0),
            'required_hours' => (float)($_POST['required_hours'] ?? 0),
            'next_due' => trim($_POST['next_due'] ?? ''),
            'certificate_required' => isset($_POST['certificate_required']) ? 1 : 0,
            'completion_certificate_path' => trim($_POST['completion_certificate_path'] ?? '')
        ];
        
        // Remove empty fields except boolean
        $fields = array_filter($fields, function($value, $key) {
            return $value !== '' || $key === 'certificate_required';
        }, ARRAY_FILTER_USE_BOTH);
        
        if (empty($fields)) {
            echo json_encode(['success'=>false,'error'=>'No fields to update']); exit;
        }
        
        $setClause = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $stmt = $pdo->prepare("UPDATE training_compliance SET $setClause WHERE id = ?");
        $stmt->execute([...array_values($fields), $trainingId]);
        
        ActivityLog::logUpdate('training_compliance', $trainingId, "Updated training compliance");
        
        echo json_encode(['success'=>true,'message'=>'Training compliance updated successfully']);
        exit;
    }

    // POST: Delete certification
    if ($action === 'delete_certification') {
        $certId = (int)($_POST['certification_id'] ?? 0);
        
        if (!$certId) {
            echo json_encode(['success'=>false,'error'=>'Certification ID required']); exit;
        }
        
        // Get certification info for logging
        $stmt = $pdo->prepare("SELECT certification_name, personil_id FROM certifications WHERE id = ?");
        $stmt->execute([$certId]);
        $certInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certInfo) {
            echo json_encode(['success'=>false,'error'=>'Certification not found']); exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM certifications WHERE id = ?");
        $stmt->execute([$certId]);
        
        ActivityLog::logDelete('certification', $certId, "Deleted certification: {$certInfo['certification_name']} for {$certInfo['personil_id']}");
        
        echo json_encode(['success'=>true,'message'=>'Certification deleted successfully']);
        exit;
    }

    // POST: Delete training compliance
    if ($action === 'delete_training_compliance') {
        $trainingId = (int)($_POST['training_id'] ?? 0);
        
        if (!$trainingId) {
            echo json_encode(['success'=>false,'error'=>'Training ID required']); exit;
        }
        
        // Get training info for logging
        $stmt = $pdo->prepare("SELECT training_name, personil_id FROM training_compliance WHERE id = ?");
        $stmt->execute([$trainingId]);
        $trainingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$trainingInfo) {
            echo json_encode(['success'=>false,'error'=>'Training not found']); exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM training_compliance WHERE id = ?");
        $stmt->execute([$trainingId]);
        
        ActivityLog::logDelete('training_compliance', $trainingId, "Deleted training: {$trainingInfo['training_name']} for {$trainingInfo['personil_id']}");
        
        echo json_encode(['success'=>true,'message'=>'Training compliance deleted successfully']);
        exit;
    }

    // GET: Certification statistics
    if ($action === 'get_certification_statistics') {
        $period = $_GET['period'] ?? '90'; // days
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Get overall statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_certifications,
                COUNT(CASE WHEN c.status = 'valid' THEN 1 END) as valid_certifications,
                COUNT(CASE WHEN c.status = 'expired' THEN 1 END) as expired_certifications,
                COUNT(CASE WHEN c.status = 'expiring' THEN 1 END) as expiring_certifications,
                COUNT(CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND c.expiry_date >= CURDATE() THEN 1 END) as expiring_soon,
                COUNT(DISTINCT c.personil_id) as personil_with_certifications
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            $whereClause
        ");
        $stmt->execute(array_merge([$period], $params));
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get certification type distribution
        $stmt = $pdo->prepare("
            SELECT c.certification_type, COUNT(*) as count
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            $whereClause
            GROUP BY c.certification_type
            ORDER BY count DESC
        ");
        $stmt->execute($params);
        $typeDistribution = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Get expiry trends (monthly)
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(c.expiry_date, '%Y-%m') as month,
                COUNT(*) as expiring_count,
                COUNT(CASE WHEN c.status = 'expired' THEN 1 END) as expired_count
            FROM certifications c
            JOIN personil p ON p.nrp = c.personil_id
            $whereClause
            AND c.expiry_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(c.expiry_date, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute($params);
        $expiryTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'overall_stats' => $overallStats,
                'type_distribution' => $typeDistribution,
                'expiry_trends' => $expiryTrends
            ]
        ]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[certification_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
