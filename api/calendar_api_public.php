<?php
/**
 * Calendar API - Public endpoints for calendar operations
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_events':
            $start = $_GET['start'] ?? date('Y-m-01');
            $end = $_GET['end'] ?? date('Y-m-t');
            
            $stmt = $pdo->prepare("
                SELECT s.*, p.nama as personil_nama 
                FROM schedules s 
                LEFT JOIN personil p ON s.personil_id = p.nrp 
                WHERE s.shift_date >= ? AND s.shift_date <= ? 
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$start, $end]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for FullCalendar
            $formattedEvents = array_map(function($event) {
                return [
                    'id' => $event['id'],
                    'title' => ($event['personil_nama'] ?: $event['personil_name']) . ' - ' . $event['shift_type'],
                    'start' => $event['shift_date'] . 'T' . $event['start_time'],
                    'end' => $event['shift_date'] . 'T' . $event['end_time'],
                    'description' => $event['description'],
                    'color' => '#007bff',
                    'extendedProps' => [
                        'personil_id' => $event['personil_id'],
                        'personil_nama' => $event['personil_nama'] ?: $event['personil_name'],
                        'location' => $event['location'],
                        'shift_type' => $event['shift_type'],
                        'status' => $event['status']
                    ]
                ];
            }, $events);
            
            echo json_encode($formattedEvents);
            break;
            
        case 'add_event':
            $judul = $_POST['judul'] ?? '';
            $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
            $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
            $waktu_mulai = $_POST['waktu_mulai'] ?? '';
            $waktu_selesai = $_POST['waktu_selesai'] ?? '';
            $personil_id = $_POST['personil_id'] ?? null;
            $lokasi = $_POST['lokasi'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $jenis = $_POST['jenis'] ?? 'tugas';
            $warna = $_POST['warna'] ?? '#007bff';
            
            if (!$judul || !$tanggal_mulai || !$waktu_mulai) {
                throw new Exception('Judul, tanggal mulai, dan waktu mulai are required');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO schedules 
                (judul, tanggal_mulai, tanggal_selesai, waktu_mulai, waktu_selesai, personil_id, lokasi, deskripsi, jenis, warna)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$judul, $tanggal_mulai, $tanggal_selesai, $waktu_mulai, $waktu_selesai, $personil_id, $lokasi, $deskripsi, $jenis, $warna]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Event created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'get_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_events,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_events,
                    COUNT(CASE WHEN shift_date = CURDATE() THEN 1 END) as today_events
                FROM schedules
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'get_personil_list':
            $stmt = $pdo->query("
                SELECT id, nama, nrp 
                FROM personil 
                WHERE is_deleted = 0 AND is_active = 1 
                ORDER BY nama ASC
            ");
            $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $personil
            ]);
            break;
            
        default:
            // If no action, return basic info
            echo json_encode([
                'success' => true,
                'message' => 'Calendar API is working',
                'available_actions' => ['get_events', 'add_event', 'get_stats', 'get_personil_list']
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
