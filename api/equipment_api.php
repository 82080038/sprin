<?php
/**
 * Equipment & Asset Management API
 * Track weapons, vehicles, radios, and other police equipment
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
    'get_equipment','get_equipment_details','get_equipment_assignments',
    'get_equipment_statistics','get_maintenance_schedule','get_personil_equipment'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Equipment list with filters
    if ($action === 'get_equipment') {
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $bagianId = $_GET['bagian_id'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($type) {
            $whereClause .= " AND e.equipment_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $whereClause .= " AND e.current_status = ?";
            $params[] = $status;
        }
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        if ($search) {
            $whereClause .= " AND (e.equipment_name LIKE ? OR e.equipment_code LIKE ? OR e.serial_number LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   p.nama as assigned_name,
                   pk.nama_pangkat as assigned_pangkat,
                   b.nama_bagian as assigned_bagian,
                   u.nama_unsur as assigned_unsur,
                   DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance,
                   CASE 
                       WHEN e.next_maintenance < CURDATE() THEN 'overdue'
                       WHEN e.next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'due_soon'
                       ELSE 'on_schedule'
                   END as maintenance_status
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY e.equipment_type, e.equipment_name
        ");
        $stmt->execute($params);
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$equipment]);
        exit;
    }

    // GET: Equipment details with assignment history
    if ($action === 'get_equipment_details') {
        $equipmentId = (int)($_GET['equipment_id'] ?? 0);
        
        if (!$equipmentId) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID required']); exit;
        }
        
        // Get equipment info
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   p.nama as assigned_name,
                   pk.nama_pangkat as assigned_pangkat,
                   b.nama_bagian as assigned_bagian,
                   u.nama_unsur as assigned_unsur,
                   DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance,
                   CASE 
                       WHEN e.next_maintenance < CURDATE() THEN 'overdue'
                       WHEN e.next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'due_soon'
                       ELSE 'on_schedule'
                   END as maintenance_status
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE e.id = ?
        ");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipment) {
            echo json_encode(['success'=>false,'error'=>'Equipment not found']); exit;
        }
        
        // Get assignment history
        $stmt = $pdo->prepare("
            SELECT ea.*, 
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   TIMESTAMPDIFF(DAY, ea.assignment_date, COALESCE(ea.return_date, CURDATE())) as assignment_days
            FROM equipment_assignments ea
            LEFT JOIN personil p ON p.nrp = ea.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            WHERE ea.equipment_id = ?
            ORDER BY ea.assignment_date DESC
        ");
        $stmt->execute([$equipmentId]);
        $assignmentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'equipment' => $equipment,
                'assignment_history' => $assignmentHistory
            ]
        ]);
        exit;
    }

    // GET: Equipment assignments
    if ($action === 'get_equipment_assignments') {
        $status = $_GET['status'] ?? '';
        $personilId = $_GET['personil_id'] ?? '';
        $equipmentType = $_GET['equipment_type'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $whereClause = "WHERE ea.assignment_date BETWEEN ? AND ?";
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($status) {
            $whereClause .= " AND ea.status = ?";
            $params[] = $status;
        }
        
        if ($personilId) {
            $whereClause .= " AND ea.personil_id = ?";
            $params[] = $personilId;
        }
        
        if ($equipmentType) {
            $whereClause .= " AND e.equipment_type = ?";
            $params[] = $equipmentType;
        }
        
        $stmt = $pdo->prepare("
            SELECT ea.*, 
                   e.equipment_code,
                   e.equipment_name,
                   e.equipment_type,
                   e.serial_number,
                   p.nama as personil_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   TIMESTAMPDIFF(DAY, ea.assignment_date, COALESCE(ea.return_date, CURDATE())) as assignment_days,
                   CASE 
                       WHEN ea.status = 'active' AND ea.return_date IS NULL AND ea.assignment_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 'overdue'
                       WHEN ea.status = 'active' AND ea.return_date IS NULL THEN 'active'
                       ELSE 'completed'
                   END as current_status
            FROM equipment_assignments ea
            JOIN equipment e ON e.id = ea.equipment_id
            JOIN personil p ON p.nrp = ea.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY ea.assignment_date DESC
        ");
        $stmt->execute($params);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$assignments]);
        exit;
    }

    // GET: Equipment statistics
    if ($action === 'get_equipment_statistics') {
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Overall statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_equipment,
                COUNT(CASE WHEN current_status = 'available' THEN 1 END) as available_count,
                COUNT(CASE WHEN current_status = 'assigned' THEN 1 END) as assigned_count,
                COUNT(CASE WHEN current_status = 'maintenance' THEN 1 END) as maintenance_count,
                COUNT(CASE WHEN current_status = 'retired' THEN 1 END) as retired_count,
                COUNT(CASE WHEN current_status = 'lost' THEN 1 END) as lost_count,
                COUNT(CASE WHEN next_maintenance < CURDATE() THEN 1 END) as maintenance_overdue,
                COUNT(CASE WHEN next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND next_maintenance >= CURDATE() THEN 1 END) as maintenance_due_soon
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            $whereClause
        ");
        $stmt->execute($params);
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Equipment type breakdown
        $stmt = $pdo->prepare("
            SELECT 
                equipment_type,
                COUNT(*) as count,
                COUNT(CASE WHEN current_status = 'assigned' THEN 1 END) as assigned_count,
                COUNT(CASE WHEN current_status = 'available' THEN 1 END) as available_count,
                COUNT(CASE WHEN current_status = 'maintenance' THEN 1 END) as maintenance_count,
                COUNT(CASE WHEN next_maintenance < CURDATE() THEN 1 END) as maintenance_overdue
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            $whereClause
            GROUP BY equipment_type
            ORDER BY count DESC
        ");
        $stmt->execute($params);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Assignment statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_assignments,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_assignments,
                COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_assignments,
                AVG(TIMESTAMPDIFF(DAY, assignment_date, COALESCE(return_date, CURDATE()))) as avg_assignment_days
            FROM equipment_assignments ea
            JOIN equipment e ON e.id = ea.equipment_id
            LEFT JOIN personil p ON p.nrp = ea.personil_id
            $whereClause
        ");
        $stmt->execute($params);
        $assignmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Top equipment by usage
        $stmt = $pdo->prepare("
            SELECT 
                e.equipment_name,
                e.equipment_type,
                COUNT(ea.id) as assignment_count,
                AVG(TIMESTAMPDIFF(DAY, ea.assignment_date, COALESCE(ea.return_date, CURDATE()))) as avg_usage_days
            FROM equipment e
            LEFT JOIN equipment_assignments ea ON ea.equipment_id = e.id
            LEFT JOIN personil p ON p.nrp = ea.personil_id
            $whereClause
            GROUP BY e.id, e.equipment_name, e.equipment_type
            HAVING assignment_count > 0
            ORDER BY assignment_count DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $topUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'overall_stats' => $overallStats,
                'by_type' => $byType,
                'assignment_stats' => $assignmentStats,
                'top_usage' => $topUsage
            ]
        ]);
        exit;
    }

    // GET: Maintenance schedule
    if ($action === 'get_maintenance_schedule') {
        $days = (int)($_GET['days'] ?? 30);
        $equipmentType = $_GET['equipment_type'] ?? '';
        $bagianId = $_GET['bagian_id'] ?? '';
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+$days days"));
        
        $whereClause = "WHERE e.next_maintenance BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($equipmentType) {
            $whereClause .= " AND e.equipment_type = ?";
            $params[] = $equipmentType;
        }
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   p.nama as assigned_name,
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance,
                   CASE 
                       WHEN e.next_maintenance < CURDATE() THEN 'overdue'
                       WHEN e.next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'urgent'
                       WHEN e.next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 'soon'
                       ELSE 'scheduled'
                   END as priority_level
            FROM equipment e
            LEFT JOIN personil p ON p.nrp = e.current_assignment
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            $whereClause
            ORDER BY e.next_maintenance ASC
        ");
        $stmt->execute($params);
        $maintenanceSchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$maintenanceSchedule]);
        exit;
    }

    // GET: Personil equipment
    if ($action === 'get_personil_equipment') {
        $personilId = trim($_GET['personil_id'] ?? '');
        
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
            WHERE p.nrp = ?
        ");
        $stmt->execute([$personilId]);
        $personil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$personil) {
            echo json_encode(['success'=>false,'error'=>'Personil not found']); exit;
        }
        
        // Get current assignments
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   ea.assignment_date,
                   ea.assignment_purpose,
                   ea.condition_assigned,
                   ea.notes as assignment_notes,
                   TIMESTAMPDIFF(DAY, ea.assignment_date, CURDATE()) as days_assigned,
                   DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance
            FROM equipment e
            JOIN equipment_assignments ea ON ea.equipment_id = e.id
            WHERE ea.personil_id = ? AND ea.status = 'active'
            ORDER BY e.equipment_type, e.equipment_name
        ");
        $stmt->execute([$personilId]);
        $currentAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get assignment history
        $stmt = $pdo->prepare("
            SELECT ea.*, 
                   e.equipment_code,
                   e.equipment_name,
                   e.equipment_type,
                   TIMESTAMPDIFF(DAY, ea.assignment_date, COALESCE(ea.return_date, CURDATE())) as assignment_days
            FROM equipment_assignments ea
            JOIN equipment e ON e.id = ea.equipment_id
            WHERE ea.personil_id = ? AND ea.status != 'active'
            ORDER BY ea.assignment_date DESC
            LIMIT 20
        ");
        $stmt->execute([$personilId]);
        $assignmentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'personil' => $personil,
                'current_assignments' => $currentAssignments,
                'assignment_history' => $assignmentHistory
            ]
        ]);
        exit;
    }

    // POST: Add equipment
    if ($action === 'add_equipment') {
        $equipmentCode = trim($_POST['equipment_code'] ?? '');
        $equipmentName = trim($_POST['equipment_name'] ?? '');
        $equipmentType = $_POST['equipment_type'] ?? '';
        $serialNumber = trim($_POST['serial_number'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');
        $purchaseCost = (float)($_POST['purchase_cost'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $maintenanceSchedule = trim($_POST['maintenance_schedule'] ?? '');
        $nextMaintenance = trim($_POST['next_maintenance'] ?? '');
        
        if (!$equipmentCode || !$equipmentName || !$equipmentType) {
            echo json_encode(['success'=>false,'error'=>'Equipment code, name, and type required']); exit;
        }
        
        // Validate equipment type
        $validTypes = ['weapon', 'vehicle', 'radio', 'protective', 'tools', 'other'];
        if (!in_array($equipmentType, $validTypes)) {
            echo json_encode(['success'=>false,'error'=>'Invalid equipment type']); exit;
        }
        
        // Calculate next maintenance if not provided
        if (!$nextMaintenance && $purchaseDate) {
            $nextMaintenance = date('Y-m-d', strtotime($purchaseDate . ' +6 months'));
        } elseif (!$nextMaintenance) {
            $nextMaintenance = date('Y-m-d', strtotime('+6 months'));
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO equipment 
            (equipment_code, equipment_name, equipment_type, serial_number, model, 
             manufacturer, purchase_date, purchase_cost, location, maintenance_schedule, 
             last_maintenance, next_maintenance)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $equipmentCode, $equipmentName, $equipmentType, $serialNumber, $model,
            $manufacturer, $purchaseDate, $purchaseCost, $location, $maintenanceSchedule,
            $purchaseDate, $nextMaintenance
        ]);
        
        $equipmentId = $pdo->lastInsertId();
        
        ActivityLog::logCreate('equipment', $equipmentId, "Added equipment: $equipmentName ($equipmentCode)");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Equipment added successfully',
            'equipment_id' => $equipmentId
        ]);
        exit;
    }

    // POST: Assign equipment to personil
    if ($action === 'assign_equipment') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $personilId = trim($_POST['personil_id'] ?? '');
        $assignmentPurpose = trim($_POST['assignment_purpose'] ?? '');
        $conditionAssigned = trim($_POST['condition_assigned'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$equipmentId || !$personilId) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID and personnel ID required']); exit;
        }
        
        // Check if equipment is available
        $stmt = $pdo->prepare("SELECT current_status FROM equipment WHERE id = ?");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipment) {
            echo json_encode(['success'=>false,'error'=>'Equipment not found']); exit;
        }
        
        if ($equipment['current_status'] !== 'available') {
            echo json_encode(['success'=>false,'error'=>'Equipment is not available for assignment']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Create assignment record
        $stmt = $pdo->prepare("
            INSERT INTO equipment_assignments 
            (equipment_id, personil_id, assignment_date, assignment_purpose, condition_assigned, notes, assigned_by)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");
        $stmt->execute([$equipmentId, $personilId, $assignmentPurpose, $conditionAssigned, $notes, $_SESSION['username']]);
        
        // Update equipment status
        $stmt = $pdo->prepare("UPDATE equipment SET current_assignment = ?, current_status = 'assigned' WHERE id = ?");
        $stmt->execute([$personilId, $equipmentId]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('equipment', $equipmentId, "Assigned equipment to $personilId");
        
        echo json_encode(['success'=>true,'message'=>'Equipment assigned successfully']);
        exit;
    }

    // POST: Return equipment
    if ($action === 'return_equipment') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $personilId = trim($_POST['personil_id'] ?? '');
        $conditionReturned = trim($_POST['condition_returned'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$equipmentId || !$personilId) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID and personnel ID required']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Update assignment record
        $stmt = $pdo->prepare("
            UPDATE equipment_assignments 
            SET return_date = NOW(), condition_returned = ?, status = 'returned', notes = ?
            WHERE equipment_id = ? AND personil_id = ? AND status = 'active'
        ");
        $stmt->execute([$conditionReturned, $notes, $equipmentId, $personilId]);
        
        // Update equipment status
        $stmt = $pdo->prepare("UPDATE equipment SET current_assignment = NULL, current_status = 'available' WHERE id = ?");
        $stmt->execute([$equipmentId]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('equipment', $equipmentId, "Equipment returned by $personilId");
        
        echo json_encode(['success'=>true,'message'=>'Equipment returned successfully']);
        exit;
    }

    // POST: Update equipment
    if ($action === 'update_equipment') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        
        if (!$equipmentId) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID required']); exit;
        }
        
        $fields = [
            'equipment_name' => trim($_POST['equipment_name'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'manufacturer' => trim($_POST['manufacturer'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'maintenance_schedule' => trim($_POST['maintenance_schedule'] ?? ''),
            'next_maintenance' => trim($_POST['next_maintenance'] ?? ''),
            'current_status' => $_POST['current_status'] ?? ''
        ];
        
        // Remove empty fields
        $fields = array_filter($fields, function($value) {
            return $value !== '';
        });
        
        if (empty($fields)) {
            echo json_encode(['success'=>false,'error'=>'No fields to update']); exit;
        }
        
        $setClause = implode(' = ?, ', array_keys($fields)) . ' = ?';
        $stmt = $pdo->prepare("UPDATE equipment SET $setClause WHERE id = ?");
        $stmt->execute([...array_values($fields), $equipmentId]);
        
        ActivityLog::logUpdate('equipment', $equipmentId, "Updated equipment details");
        
        echo json_encode(['success'=>true,'message'=>'Equipment updated successfully']);
        exit;
    }

    // POST: Record maintenance
    if ($action === 'record_maintenance') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $maintenanceDate = trim($_POST['maintenance_date'] ?? '');
        $maintenanceType = trim($_POST['maintenance_type'] ?? '');
        $maintenanceNotes = trim($_POST['maintenance_notes'] ?? '');
        $nextMaintenanceDate = trim($_POST['next_maintenance_date'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);
        
        if (!$equipmentId || !$maintenanceDate) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID and maintenance date required']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Update equipment maintenance records
        $stmt = $pdo->prepare("
            UPDATE equipment 
            SET last_maintenance = ?, next_maintenance = ?, current_status = 'available'
            WHERE id = ?
        ");
        $stmt->execute([$maintenanceDate, $nextMaintenanceDate ?: date('Y-m-d', strtotime($maintenanceDate . ' +6 months')), $equipmentId]);
        
        $pdo->commit();
        
        ActivityLog::logUpdate('equipment', $equipmentId, "Recorded maintenance on $maintenanceDate");
        
        echo json_encode(['success'=>true,'message'=>'Maintenance recorded successfully']);
        exit;
    }

    // POST: Delete equipment
    if ($action === 'delete_equipment') {
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        
        if (!$equipmentId) {
            echo json_encode(['success'=>false,'error'=>'Equipment ID required']); exit;
        }
        
        // Get equipment info for logging
        $stmt = $pdo->prepare("SELECT equipment_name, equipment_code FROM equipment WHERE id = ?");
        $stmt->execute([$equipmentId]);
        $equipmentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipmentInfo) {
            echo json_encode(['success'=>false,'error'=>'Equipment not found']); exit;
        }
        
        // Check if equipment is assigned
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment_assignments WHERE equipment_id = ? AND status = 'active'");
        $stmt->execute([$equipmentId]);
        $activeAssignments = $stmt->fetchColumn();
        
        if ($activeAssignments > 0) {
            echo json_encode(['success'=>false,'error'=>'Cannot delete equipment with active assignments']); exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$equipmentId]);
        
        ActivityLog::logDelete('equipment', $equipmentId, "Deleted equipment: {$equipmentInfo['equipment_name']} ({$equipmentInfo['equipment_code']})");
        
        echo json_encode(['success'=>true,'message'=>'Equipment deleted successfully']);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[equipment_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
