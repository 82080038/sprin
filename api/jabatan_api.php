<?php
/**
 * Jabatan API - Separate API endpoints for jabatan operations
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
        case 'get_jabatan_detail':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $stmt = $pdo->prepare("
                SELECT j.*, u.nama_unsur, b.nama_bagian 
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                WHERE j.id = ?
            ");
            $stmt->execute([$id]);
            $jabatan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $jabatan
            ]);
            break;
            
        case 'get_all_jabatan':
            $stmt = $pdo->query("
                SELECT j.*, u.nama_unsur, b.nama_bagian,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = 0) as personil_count
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                ORDER BY u.urutan ASC, j.nama_jabatan ASC
            ");
            $jabatanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $jabatanList
            ]);
            break;
            
        case 'update_jabatan':
            $id = $_POST['id'] ?? 0;
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $keterangan = $_POST['keterangan'] ?? '';
            
            if (!$id || !$nama_jabatan) {
                throw new Exception('ID and nama jabatan are required');
            }
            
            $stmt = $pdo->prepare("
                UPDATE jabatan 
                SET nama_jabatan = ?, id_unsur = ?, id_bagian = ?, keterangan = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nama_jabatan, $id_unsur, $id_bagian, $keterangan, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan updated successfully'
            ]);
            break;
            
        case 'create_jabatan':
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $keterangan = $_POST['keterangan'] ?? '';
            
            if (!$nama_jabatan) {
                throw new Exception('Nama jabatan is required');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (nama_jabatan, id_unsur, id_bagian, keterangan) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$nama_jabatan, $id_unsur, $id_bagian, $keterangan]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'delete_jabatan':
            $id = $_POST['id'] ?? 0;
            
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            // Check if jabatan has personil
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete jabatan with active personil',
                    'personil_count' => $personilCount
                ]);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan deleted successfully'
            ]);
            break;
            
        case 'get_jabatan_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_jabatan,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil,
                    (SELECT COUNT(*) FROM unsur) as total_unsur,
                    (SELECT COUNT(*) FROM bagian) as total_bagian
                FROM jabatan
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'get_jabatan_by_unsur':
            $unsur_id = $_POST['unsur_id'] ?? $_GET['unsur_id'] ?? 0;
            
            if (!$unsur_id) {
                throw new Exception('Unsur ID is required');
            }
            
            $stmt = $pdo->prepare("
                SELECT j.*, b.nama_bagian,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = 0) as personil_count
                FROM jabatan j 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                WHERE j.id_unsur = ? 
                ORDER BY j.nama_jabatan ASC
            ");
            $stmt->execute([$unsur_id]);
            $jabatanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $jabatanList
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
