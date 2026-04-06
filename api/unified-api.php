<?php
/**
 * Unified API Gateway
 * Centralized API endpoint for all CRUD operations
 * Provides consistent interface and error handling
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

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
    send_error('Database connection failed', 500, $e->getMessage());
}

// Get request parameters
$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? 0;

try {
    // Route request based on resource
    switch ($resource) {
        case 'unsur':
            handle_unsur_request($pdo, $method, $action, $id);
            break;
        case 'bagian':
            handle_bagian_request($pdo, $method, $action, $id);
            break;
        case 'jabatan':
            handle_jabatan_request($pdo, $method, $action, $id);
            break;
        case 'personil':
            handle_personil_request($pdo, $method, $action, $id);
            break;
        case 'stats':
            handle_stats_request($pdo, $action);
            break;
        default:
            send_error('Invalid resource', 404);
    }
} catch (Exception $e) {
    send_error('Internal server error', 500, $e->getMessage());
}

// Response helper functions
function send_success($data = null, $message = 'Success') {
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

function send_error($message, $code = 400, $details = null) {
    http_response_code($code);
    
    $response = [
        'success' => false,
        'message' => $message,
        'code' => $code,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    echo json_encode($response);
    exit;
}

// Unsur handlers
function handle_unsur_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT u.*, 
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_unsur = u.id) as jabatan_count,
                       (SELECT COUNT(*) FROM personil p 
                        JOIN jabatan j ON p.id_jabatan = j.id 
                        WHERE j.id_unsur = u.id AND p.is_deleted = 0) as personil_count
                FROM unsur u 
                ORDER BY u.urutan ASC, u.nama_unsur ASC
            ");
            $unsurList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($unsurList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("SELECT * FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            $unsur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$unsur) send_error('Unsur not found', 404);
            send_success($unsur);
            
        case 'create':
            $nama_unsur = $_POST['nama_unsur'] ?? '';
            $kode_unsur = $_POST['kode_unsur'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tingkat = $_POST['tingkat'] ?? '';
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_unsur) send_error('Nama unsur is required');
            
            $stmt = $pdo->prepare("
                INSERT INTO unsur (nama_unsur, kode_unsur, deskripsi, tingkat, urutan) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_unsur, $kode_unsur, $deskripsi, $tingkat, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId()], 'Unsur created successfully');
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_unsur = $_POST['nama_unsur'] ?? '';
            $kode_unsur = $_POST['kode_unsur'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tingkat = $_POST['tingkat'] ?? '';
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_unsur) send_error('Nama unsur is required');
            
            $stmt = $pdo->prepare("
                UPDATE unsur 
                SET nama_unsur = ?, kode_unsur = ?, deskripsi = ?, tingkat = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_unsur, $kode_unsur, $deskripsi, $tingkat, $urutan, $id]);
            
            send_success(null, 'Unsur updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE id_unsur = ?");
            $stmt->execute([$id]);
            $jabatanCount = $stmt->fetchColumn();
            
            if ($jabatanCount > 0) {
                send_error("Cannot delete unsur with {$jabatanCount} jabatan", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Unsur deleted successfully');
            
        case 'stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_unsur,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil
                FROM unsur
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for unsur', 400);
    }
}

// Bagian handlers
function handle_bagian_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT b.*, 
                       u.nama_unsur,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_bagian = b.id AND p.is_deleted = 0) as personil_count,
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_bagian = b.id) as jabatan_count
                FROM bagian b 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                ORDER BY b.nama_bagian ASC
            ");
            $bagianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($bagianList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT b.*, u.nama_unsur 
                FROM bagian b 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$bagian) send_error('Bagian not found', 404);
            send_success($bagian);
            
        case 'create':
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $kode_bagian = $_POST['kode_bagian'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_bagian) send_error('Nama bagian is required');
            
            $stmt = $pdo->prepare("
                INSERT INTO bagian (nama_bagian, kode_bagian, deskripsi, id_unsur, urutan) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_bagian, $kode_bagian, $deskripsi, $id_unsur, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId()], 'Bagian created successfully');
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $kode_bagian = $_POST['kode_bagian'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_bagian) send_error('Nama bagian is required');
            
            $stmt = $pdo->prepare("
                UPDATE bagian 
                SET nama_bagian = ?, kode_bagian = ?, deskripsi = ?, id_unsur = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_bagian, $kode_bagian, $deskripsi, $id_unsur, $urutan, $id]);
            
            send_success(null, 'Bagian updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                send_error("Cannot delete bagian with {$personilCount} personil", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Bagian deleted successfully');
            
        default:
            send_error('Invalid action for bagian', 400);
    }
}

// Jabatan handlers
function handle_jabatan_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT j.*, u.nama_unsur, b.nama_bagian,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = 0) as personil_count
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                ORDER BY u.urutan ASC, j.nama_jabatan ASC
            ");
            $jabatanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($jabatanList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT j.*, u.nama_unsur, b.nama_bagian 
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                WHERE j.id = ?
            ");
            $stmt->execute([$id]);
            $jabatan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$jabatan) send_error('Jabatan not found', 404);
            send_success($jabatan);
            
        case 'create':
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $kode_jabatan = $_POST['kode_jabatan'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_jabatan) send_error('Nama jabatan is required');
            
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (nama_jabatan, kode_jabatan, deskripsi, id_unsur, id_bagian, urutan) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_jabatan, $kode_jabatan, $deskripsi, $id_unsur, $id_bagian, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId()], 'Jabatan created successfully');
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $kode_jabatan = $_POST['kode_jabatan'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_jabatan) send_error('Nama jabatan is required');
            
            $stmt = $pdo->prepare("
                UPDATE jabatan 
                SET nama_jabatan = ?, kode_jabatan = ?, deskripsi = ?, id_unsur = ?, id_bagian = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_jabatan, $kode_jabatan, $deskripsi, $id_unsur, $id_bagian, $urutan, $id]);
            
            send_success(null, 'Jabatan updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                send_error("Cannot delete jabatan with {$personilCount} personil", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Jabatan deleted successfully');
            
        default:
            send_error('Invalid action for jabatan', 400);
    }
}

// Personil handlers
function handle_personil_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT p.*, 
                       pa.nama_pangkat, pa.singkatan as pangkat_singkatan,
                       j.nama_jabatan, b.nama_bagian, u.nama_unsur
                FROM personil p
                LEFT JOIN pangkat pa ON p.id_pangkat = pa.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE p.is_deleted = 0
                ORDER BY p.nama ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $personilList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($personilList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT p.*, 
                       pa.nama_pangkat, pa.singkatan as pangkat_singkatan,
                       j.nama_jabatan, b.nama_bagian, u.nama_unsur
                FROM personil p
                LEFT JOIN pangkat pa ON p.id_pangkat = pa.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE p.id = ? AND p.is_deleted = 0
            ");
            $stmt->execute([$id]);
            $personil = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$personil) send_error('Personil not found', 404);
            send_success($personil);
            
        case 'stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_personil,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_personil,
                    COUNT(CASE WHEN JK = 'L' THEN 1 END) as male_personil,
                    COUNT(CASE WHEN JK = 'P' THEN 1 END) as female_personil
                FROM personil 
                WHERE is_deleted = 0
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for personil', 400);
    }
}

// Stats handlers
function handle_stats_request($pdo, $action) {
    switch ($action) {
        case 'dashboard':
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0 AND is_active = 1) as active_personil,
                    (SELECT COUNT(*) FROM unsur) as total_unsur,
                    (SELECT COUNT(*) FROM bagian) as total_bagian,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        case 'unsur':
            $stmt = $pdo->query("
                SELECT u.nama_unsur, COUNT(j.id) as jabatan_count, COUNT(p.id) as personil_count
                FROM unsur u
                LEFT JOIN jabatan j ON u.id = j.id_unsur
                LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
                GROUP BY u.id, u.nama_unsur
                ORDER BY u.urutan ASC
            ");
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for stats', 400);
    }
}
?>
