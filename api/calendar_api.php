<?php
/**
 * Calendar API - Simple Schedule Statistics
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Initialize session
SessionManager::start();

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'timestamp' => date('c')
    ]);
    exit;
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get action parameter
    $action = isset($_GET['action']) ? $_GET['action'] : 'getStats';
    
    switch ($action) {
        case 'getStats':
            // Get schedule statistics
            $today = date('Y-m-d');
            $week_end = date('Y-m-d', strtotime('+7 days'));
            
            // Try to get schedule data from jadwal table
            try {
                // Count schedules for today
                $today_sql = "
                    SELECT COUNT(*) as count 
                    FROM jadwal 
                    WHERE tanggal = ? AND is_deleted = FALSE
                ";
                $today_stmt = $pdo->prepare($today_sql);
                $today_stmt->execute([$today]);
                $today_count = $today_stmt->fetch()['count'];
                
                // Count schedules for next 7 days
                $week_sql = "
                    SELECT COUNT(*) as count 
                    FROM jadwal 
                    WHERE tanggal BETWEEN ? AND ? AND is_deleted = FALSE
                ";
                $week_stmt = $pdo->prepare($week_sql);
                $week_stmt->execute([$today, $week_end]);
                $week_count = $week_stmt->fetch()['count'];
                
                // If jadwal table doesn't exist or has no data, return defaults
                if ($today_count === false) $today_count = 0;
                if ($week_count === false) $week_count = 0;
                
            } catch (Exception $e) {
                // If jadwal table doesn't exist, use default values
                $today_count = 0;
                $week_count = 0;
            }
            
            echo json_encode([
                'success' => true,
                'timestamp' => date('c'),
                'data' => [
                    'today' => (int)$today_count,
                    'week' => (int)$week_count,
                    'date_range' => [
                        'today' => $today,
                        'week_end' => $week_end
                    ],
                    'note' => 'Schedule data not available - using default values'
                ],
                'message' => "Schedule statistics retrieved successfully"
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'get_schedules':
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
            
            $stmt = $pdo->prepare("
                SELECT * FROM jadwal 
                WHERE tanggal BETWEEN ? AND ? AND is_deleted = FALSE
                ORDER BY tanggal ASC, waktu_mulai ASC
            ");
            $stmt->execute([$start_date, $end_date]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $schedules
            ]);
            break;
            
        case 'create_schedule':
            $tanggal = $_POST['tanggal'] ?? '';
            $waktu_mulai = $_POST['waktu_mulai'] ?? '';
            $waktu_selesai = $_POST['waktu_selesai'] ?? '';
            $kegiatan = $_POST['kegiatan'] ?? '';
            $personil_id = $_POST['personil_id'] ?? null;
            $lokasi = $_POST['lokasi'] ?? '';
            
            if (!$tanggal || !$waktu_mulai || !$kegiatan) {
                throw new Exception('Tanggal, waktu mulai, dan kegiatan harus diisi');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jadwal (tanggal, waktu_mulai, waktu_selesai, kegiatan, personil_id, lokasi, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tanggal, $waktu_mulai, $waktu_selesai, $kegiatan, $personil_id, $lokasi]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jadwal berhasil ditambahkan',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_schedule':
            $schedule_id = $_POST['schedule_id'] ?? 0;
            $tanggal = $_POST['tanggal'] ?? '';
            $waktu_mulai = $_POST['waktu_mulai'] ?? '';
            $waktu_selesai = $_POST['waktu_selesai'] ?? '';
            $kegiatan = $_POST['kegiatan'] ?? '';
            $personil_id = $_POST['personil_id'] ?? null;
            $lokasi = $_POST['lokasi'] ?? '';
            
            if (!$schedule_id) {
                throw new Exception('Schedule ID is required');
            }
            
            $stmt = $pdo->prepare("
                UPDATE jadwal 
                SET tanggal = ?, waktu_mulai = ?, waktu_selesai = ?, kegiatan = ?, personil_id = ?, lokasi = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$tanggal, $waktu_mulai, $waktu_selesai, $kegiatan, $personil_id, $lokasi, $schedule_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jadwal berhasil diperbarui'
            ]);
            break;
            
        case 'delete_schedule':
            $schedule_id = $_POST['schedule_id'] ?? 0;
            
            if (!$schedule_id) {
                throw new Exception('Schedule ID is required');
            }
            
            // Soft delete
            $stmt = $pdo->prepare("UPDATE jadwal SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$schedule_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus'
            ]);
            break;
            
        default:
            throw new Exception("Invalid action: $action");
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'timestamp' => date('c'),
        'error' => [
            'message' => $e->getMessage(),
            'code' => 500,
            'hint' => 'Check action parameter and database connection'
        ]
    ], JSON_PRETTY_PRINT);
}
?>
