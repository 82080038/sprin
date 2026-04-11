<?php
/**
 * Emergency Task Assignment API
 * Handle emergency tasks, urgent assignments, and personnel replacements
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
    'get_emergency_tasks','get_task_details','get_available_personnel',
    'get_task_conflicts','get_task_statistics','get_replacement_candidates'
];
CSRFHelper::applyProtection($readOnlyActions);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // GET: Emergency tasks list
    if ($action === 'get_emergency_tasks') {
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        
        $whereClause = "WHERE et.start_time BETWEEN ? AND ?";
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($status) {
            $whereClause .= " AND et.status = ?";
            $params[] = $status;
        }
        
        if ($priority) {
            $whereClause .= " AND et.priority_level = ?";
            $params[] = $priority;
        }
        
        $stmt = $pdo->prepare("
            SELECT et.*, 
                   p.nama as assigned_name,
                   pk.nama_pangkat as assigned_pangkat,
                   b.nama_bagian as assigned_bagian,
                   u.nama_unsur as assigned_unsur,
                   s.shift_type as original_shift,
                   s.personil_name as original_personil
            FROM emergency_tasks et
            LEFT JOIN personil p ON p.nrp = et.assigned_to
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN schedules s ON s.id = et.original_schedule_id
            $whereClause
            ORDER BY et.priority_level DESC, et.start_time ASC
        ");
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$tasks]);
        exit;
    }

    // GET: Task details
    if ($action === 'get_task_details') {
        $taskId = (int)($_GET['task_id'] ?? 0);
        
        if (!$taskId) {
            echo json_encode(['success'=>false,'error'=>'Task ID required']); exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT et.*, 
                   p.nama as assigned_name,
                   pk.nama_pangkat as assigned_pangkat,
                   b.nama_bagian as assigned_bagian,
                   u.nama_unsur as assigned_unsur,
                   s.shift_type as original_shift,
                   s.personil_name as original_personil,
                   s.start_time as original_start_time,
                   s.end_time as original_end_time
            FROM emergency_tasks et
            LEFT JOIN personil p ON p.nrp = et.assigned_to
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN schedules s ON s.id = et.original_schedule_id
            WHERE et.id = ?
        ");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            echo json_encode(['success'=>false,'error'=>'Task not found']); exit;
        }
        
        // Get conflicts related to this task
        $stmt = $pdo->prepare("
            SELECT tc.*, 
                   s.personil_name,
                   s.shift_type,
                   s.start_time,
                   s.end_time
            FROM task_conflicts tc
            LEFT JOIN schedules s ON s.id = tc.schedule_id
            WHERE tc.emergency_task_id = ?
        ");
        $stmt->execute([$taskId]);
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'task' => $task,
                'conflicts' => $conflicts
            ]
        ]);
        exit;
    }

    // GET: Available personnel for emergency assignment
    if ($action === 'get_available_personnel') {
        $dateTime = $_GET['datetime'] ?? date('Y-m-d H:i:s');
        $bagianId = $_GET['bagian_id'] ?? '';
        $requiredSkills = $_GET['required_skills'] ?? '';
        
        $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
        $params = [];
        
        if ($bagianId) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $bagianId;
        }
        
        // Check if personnel has existing schedule at the time
        $dateTimeObj = new DateTime($dateTime);
        $date = $dateTimeObj->format('Y-m-d');
        $time = $dateTimeObj->format('H:i:s');
        
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   pk.nama_pangkat,
                   b.nama_bagian,
                   u.nama_unsur,
                   p.wellness_score,
                   p.fatigue_level,
                   CASE 
                       WHEN s.id IS NOT NULL THEN 'scheduled'
                       WHEN EXISTS (
                           SELECT 1 FROM emergency_tasks et2 
                           WHERE et2.assigned_to = p.nrp 
                           AND et2.start_time <= ? 
                           AND (et2.end_time >= ? OR et2.end_time IS NULL)
                           AND et2.status IN ('assigned', 'in_progress')
                       ) THEN 'emergency_assigned'
                       ELSE 'available'
                   END as availability_status,
                   s.shift_type,
                   s.start_time,
                   s.end_time
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            LEFT JOIN unsur u ON u.id = b.id_unsur
            LEFT JOIN schedules s ON s.personil_id = p.nrp 
                AND s.shift_date = ? 
                AND s.start_time <= ? 
                AND (s.end_time >= ? OR s.end_time < s.start_time)
            $whereClause
            ORDER BY 
                CASE WHEN p.wellness_score >= 80 THEN 1 ELSE 2 END,
                CASE WHEN p.fatigue_level = 'low' THEN 1 ELSE 2 END,
                p.nama
        ");
        $stmt->execute(array_merge([$dateTime, $dateTime, $date, $time, $time], $params));
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success'=>true,'data'=>$personnel]);
        exit;
    }

    // GET: Replacement candidates for a specific schedule
    if ($action === 'get_replacement_candidates') {
        $scheduleId = (int)($_GET['schedule_id'] ?? 0);
        
        if (!$scheduleId) {
            echo json_encode(['success'=>false,'error'=>'Schedule ID required']); exit;
        }
        
        // Get original schedule info
        $stmt = $pdo->prepare("
            SELECT s.*, t.nama_tim, t.id_bagian
            FROM schedules s
            LEFT JOIN tim_piket t ON t.id = s.tim_id
            WHERE s.id = ?
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            echo json_encode(['success'=>false,'error'=>'Schedule not found']); exit;
        }
        
        // Get available candidates from same bagian
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   pk.nama_pangkat,
                   b.nama_bagian,
                   p.wellness_score,
                   p.fatigue_level,
                   CASE 
                       WHEN EXISTS (
                           SELECT 1 FROM schedules s2 
                           WHERE s2.personil_id = p.nrp 
                           AND s2.shift_date = ? 
                           AND s2.id != ?
                       ) THEN 'has_schedule'
                       WHEN EXISTS (
                           SELECT 1 FROM emergency_tasks et 
                           WHERE et.assigned_to = p.nrp 
                           AND et.start_time <= ? 
                           AND (et.end_time >= ? OR et.end_time IS NULL)
                           AND et.status IN ('assigned', 'in_progress')
                       ) THEN 'emergency_assigned'
                       ELSE 'available'
                   END as availability_status
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian b ON b.id = p.id_bagian
            WHERE p.id_bagian = ? 
                AND p.is_active = 1 
                AND p.is_deleted = 0 
                AND p.nrp != ?
            ORDER BY 
                CASE WHEN availability_status = 'available' THEN 1 ELSE 2 END,
                p.wellness_score DESC,
                p.nama
        ");
        $stmt->execute([
            $schedule['shift_date'], 
            $scheduleId,
            $schedule['shift_date'] . ' ' . $schedule['start_time'],
            $schedule['shift_date'] . ' ' . $schedule['end_time'],
            $schedule['id_bagian'],
            $schedule['personil_id']
        ]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'schedule' => $schedule,
                'candidates' => $candidates
            ]
        ]);
        exit;
    }

    // POST: Create emergency task
    if ($action === 'create_emergency_task') {
        $taskName = trim($_POST['task_name'] ?? '');
        $taskType = $_POST['task_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $priorityLevel = $_POST['priority_level'] ?? 'high';
        $location = trim($_POST['location'] ?? '');
        $requiredPersonnel = (int)($_POST['required_personnel'] ?? 1);
        $estimatedDuration = (float)($_POST['estimated_duration'] ?? 0);
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $assignedTo = trim($_POST['assigned_to'] ?? '');
        $originalScheduleId = (int)($_POST['original_schedule_id'] ?? 0);
        $replacementReason = trim($_POST['replacement_reason'] ?? '');
        
        if (!$taskName || !$taskType || !$startTime) {
            echo json_encode(['success'=>false,'error'=>'Task name, type, and start time required']); exit;
        }
        
        // Validate task type
        $validTypes = ['urgent', 'critical', 'emergency', 'recall'];
        if (!in_array($taskType, $validTypes)) {
            echo json_encode(['success'=>false,'error'=>'Invalid task type']); exit;
        }
        
        // Generate unique task code
        $taskCode = 'ET' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $pdo->beginTransaction();
        
        // Create emergency task
        $stmt = $pdo->prepare("
            INSERT INTO emergency_tasks 
            (task_code, task_name, task_type, description, priority_level, location, 
             required_personnel, estimated_duration, start_time, end_time, 
             assigned_to, original_schedule_id, replacement_reason, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $taskCode, $taskName, $taskType, $description, $priorityLevel, $location,
            $requiredPersonnel, $estimatedDuration, $startTime, $endTime,
            $assignedTo, $originalScheduleId ?: null, $replacementReason, $_SESSION['username']
        ]);
        
        $taskId = $pdo->lastInsertId();
        
        // If replacing a schedule, update the original schedule
        if ($originalScheduleId && $assignedTo) {
            // Mark original schedule as replaced
            $stmt = $pdo->prepare("
                UPDATE schedules 
                SET status = 'replaced', 
                    notes = CONCAT(IFNULL(notes, ''), '\nReplaced by emergency task: $taskCode')
                WHERE id = ?
            ");
            $stmt->execute([$originalScheduleId]);
            
            // Create new schedule for replacement person
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
            $stmt->execute([$originalScheduleId]);
            $originalSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($originalSchedule) {
                $stmt = $pdo->prepare("
                    INSERT INTO schedules 
                    (personil_id, personil_name, bagian, shift_type, shift_date, 
                     start_time, end_time, location, description, tim_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'replaced_emergency')
                ");
                $stmt->execute([
                    $assignedTo,
                    $assignedTo, // Will be updated with actual name
                    $originalSchedule['bagian'],
                    $originalSchedule['shift_type'],
                    $originalSchedule['shift_date'],
                    $originalSchedule['start_time'],
                    $originalSchedule['end_time'],
                    $originalSchedule['location'],
                    'Emergency replacement: ' . $taskCode,
                    $originalSchedule['tim_id']
                ]);
            }
        }
        
        // Check for conflicts
        if ($assignedTo && $startTime) {
            $conflictCheck = checkScheduleConflicts($pdo, $assignedTo, $startTime, $endTime ?: null, $taskId);
            if (!empty($conflictCheck)) {
                foreach ($conflictCheck as $conflict) {
                    $stmt = $pdo->prepare("
                        INSERT INTO task_conflicts 
                        (schedule_id, emergency_task_id, conflict_type, resolution_status)
                        VALUES (?, ?, ?, 'pending')
                    ");
                    $stmt->execute([$conflict['schedule_id'], $taskId, $conflict['type']]);
                }
            }
        }
        
        $pdo->commit();
        
        ActivityLog::logCreate('emergency_task', $taskId, "Created emergency task: $taskName ($taskCode)");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Emergency task created successfully',
            'task_id' => $taskId,
            'task_code' => $taskCode
        ]);
        exit;
    }

    // POST: Assign personnel to emergency task
    if ($action === 'assign_personnel') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $personnelId = trim($_POST['personnel_id'] ?? '');
        $assignmentNote = trim($_POST['assignment_note'] ?? '');
        
        if (!$taskId || !$personnelId) {
            echo json_encode(['success'=>false,'error'=>'Task ID and personnel ID required']); exit;
        }
        
        $pdo->beginTransaction();
        
        // Get task details
        $stmt = $pdo->prepare("SELECT * FROM emergency_tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            echo json_encode(['success'=>false,'error'=>'Task not found']); exit;
        }
        
        // Update task assignment
        $stmt = $pdo->prepare("
            UPDATE emergency_tasks 
            SET assigned_to = ?, status = 'assigned'
            WHERE id = ?
        ");
        $stmt->execute([$personnelId, $taskId]);
        
        // Get personnel name
        $stmt = $pdo->prepare("SELECT nama FROM personil WHERE nrp = ?");
        $stmt->execute([$personnelId]);
        $personnelName = $stmt->fetchColumn() ?: $personnelId;
        
        // If this is a replacement, update the original schedule
        if ($task['original_schedule_id']) {
            $stmt = $pdo->prepare("
                UPDATE schedules 
                SET personil_id = ?, personil_name = ?, status = 'replaced_emergency'
                WHERE id = ?
            ");
            $stmt->execute([$personnelId, $personnelName, $task['original_schedule_id']]);
        }
        
        // Check for conflicts
        $conflictCheck = checkScheduleConflicts($pdo, $personnelId, $task['start_time'], $task['end_time'], $taskId);
        if (!empty($conflictCheck)) {
            foreach ($conflictCheck as $conflict) {
                $stmt = $pdo->prepare("
                    INSERT INTO task_conflicts 
                    (schedule_id, emergency_task_id, conflict_type, resolution_status)
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt->execute([$conflict['schedule_id'], $taskId, $conflict['type']]);
            }
        }
        
        $pdo->commit();
        
        ActivityLog::logUpdate('emergency_task', $taskId, "Assigned $personnelName to emergency task");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Personnel assigned successfully',
            'conflicts_detected' => count($conflictCheck)
        ]);
        exit;
    }

    // POST: Update task status
    if ($action === 'update_task_status') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $completionNote = trim($_POST['completion_note'] ?? '');
        
        if (!$taskId || !$status) {
            echo json_encode(['success'=>false,'error'=>'Task ID and status required']); exit;
        }
        
        $validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success'=>false,'error'=>'Invalid status']); exit;
        }
        
        $updateData = ['status' => $status];
        
        if ($status === 'completed') {
            $updateData['end_time'] = date('Y-m-d H:i:s');
        }
        
        if ($status === 'in_progress') {
            $updateData['start_time'] = date('Y-m-d H:i:s');
        }
        
        $setClause = implode(' = ?, ', array_keys($updateData)) . ' = ?';
        $stmt = $pdo->prepare("UPDATE emergency_tasks SET $setClause WHERE id = ?");
        $stmt->execute([...array_values($updateData), $taskId]);
        
        ActivityLog::logUpdate('emergency_task', $taskId, "Updated status to: $status");
        
        echo json_encode([
            'success'=>true,
            'message'=>'Task status updated successfully'
        ]);
        exit;
    }

    // GET: Task statistics
    if ($action === 'get_task_statistics') {
        $period = $_GET['period'] ?? '30'; // days
        $startDate = date('Y-m-d', strtotime("-$period days"));
        $endDate = date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_tasks,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_tasks,
                COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned_tasks,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_tasks,
                COUNT(CASE WHEN priority_level = 'critical' THEN 1 END) as critical_tasks,
                COUNT(CASE WHEN task_type = 'emergency' THEN 1 END) as emergency_tasks,
                AVG(estimated_duration) as avg_duration
            FROM emergency_tasks 
            WHERE DATE(start_time) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get tasks by type
        $stmt = $pdo->prepare("
            SELECT task_type, COUNT(*) as count
            FROM emergency_tasks 
            WHERE DATE(start_time) BETWEEN ? AND ?
            GROUP BY task_type
        ");
        $stmt->execute([$startDate, $endDate]);
        $byType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Get tasks by priority
        $stmt = $pdo->prepare("
            SELECT priority_level, COUNT(*) as count
            FROM emergency_tasks 
            WHERE DATE(start_time) BETWEEN ? AND ?
            GROUP BY priority_level
        ");
        $stmt->execute([$startDate, $endDate]);
        $byPriority = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        echo json_encode([
            'success'=>true,
            'data'=>[
                'summary' => $stats,
                'by_type' => $byType,
                'by_priority' => $byPriority
            ]
        ]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Invalid action']);

} catch (Exception $e) {
    error_log('[emergency_task_api] ' . $e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

// Helper function to check schedule conflicts
function checkScheduleConflicts($pdo, $personnelId, $startTime, $endTime, $excludeTaskId = 0) {
    $conflicts = [];
    
    // Check regular schedule conflicts
    $stmt = $pdo->prepare("
        SELECT id, shift_date, start_time, end_time, shift_type
        FROM schedules 
        WHERE personil_id = ? 
        AND CONCAT(shift_date, ' ', start_time) < ?
        AND CONCAT(shift_date, ' ', COALESCE(end_time, '23:59:59')) > COALESCE(?, ?)
    ");
    $stmt->execute([$personnelId, $endTime ?: '2099-12-31 23:59:59', $startTime, $startTime]);
    $scheduleConflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($scheduleConflicts as $conflict) {
        $conflicts[] = [
            'schedule_id' => $conflict['id'],
            'type' => 'overlap',
            'details' => 'Schedule overlap with ' . $conflict['shift_type']
        ];
    }
    
    // Check other emergency task conflicts
    $stmt = $pdo->prepare("
        SELECT id, task_name, start_time, end_time
        FROM emergency_tasks 
        WHERE assigned_to = ? 
        AND id != ?
        AND start_time < ?
        AND (end_time > ? OR end_time IS NULL)
        AND status IN ('assigned', 'in_progress')
    ");
    $stmt->execute([$personnelId, $excludeTaskId, $endTime ?: '2099-12-31 23:59:59', $startTime]);
    $taskConflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($taskConflicts as $conflict) {
        $conflicts[] = [
            'schedule_id' => $conflict['id'],
            'type' => 'resource',
            'details' => 'Emergency task conflict: ' . $conflict['task_name']
        ];
    }
    
    return $conflicts;
}
?>
