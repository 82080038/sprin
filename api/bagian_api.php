<?php
/**
 * Bagian API - Separate API endpoints for bagian operations
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
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
        case 'get_bagian_detail':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM bagian WHERE id = ?");
            $stmt->execute([$id]);
            $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $bagian
            ]);
            break;
            
        case 'get_all_bagian':
            $stmt = $pdo->query("
                SELECT b.*, 
                       (SELECT COUNT(*) FROM personil p WHERE p.id_bagian = b.id AND p.is_deleted = 0) as personil_count,
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_bagian = b.id) as jabatan_count
                FROM bagian b 
                ORDER BY b.nama_bagian ASC
            ");
            $bagianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $bagianList
            ]);
            break;
            
        case 'update_bagian':
            $id = $_POST['id'] ?? 0;
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $keterangan = $_POST['keterangan'] ?? '';
            
            if (!$id || !$nama_bagian) {
                throw new Exception('ID and nama bagian are required');
            }
            
            $stmt = $pdo->prepare("UPDATE bagian SET nama_bagian = ?, keterangan = ? WHERE id = ?");
            $stmt->execute([$nama_bagian, $keterangan, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Bagian updated successfully'
            ]);
            break;
            
        case 'create_bagian':
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $keterangan = $_POST['keterangan'] ?? '';
            
            if (!$nama_bagian) {
                throw new Exception('Nama bagian is required');
            }
            
            $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian, keterangan) VALUES (?, ?)");
            $stmt->execute([$nama_bagian, $keterangan]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Bagian created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'delete_bagian':
            $id = $_POST['id'] ?? 0;
            
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            // Check if bagian has personil
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete bagian with active personil',
                    'personil_count' => $personilCount
                ]);
                break;
            }
            
            // Check if bagian has jabatan
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE id_bagian = ?");
            $stmt->execute([$id]);
            $jabatanCount = $stmt->fetchColumn();
            
            if ($jabatanCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete bagian with active jabatan',
                    'jabatan_count' => $jabatanCount
                ]);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Bagian deleted successfully'
            ]);
            break;
            
        case 'get_bagian_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_bagian,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan
                FROM bagian
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
