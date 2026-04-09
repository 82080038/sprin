<?php
/**
 * Jabatan API - Separate API endpoints for jabatan operations
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

// CSRF validation for mutating requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token required']);
        exit;
    }
    session_start();
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
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
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $nama_jabatan = trim(strip_tags($_POST['nama_jabatan'] ?? ''));
            $id_unsur = filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $id_bagian = filter_var($_POST['id_bagian'] ?? null, FILTER_VALIDATE_INT) ?: null;
            
            if (!$id || !$nama_jabatan) {
                throw new Exception('ID and nama jabatan are required');
            }
            if (strlen($nama_jabatan) > 255) {
                throw new Exception('Nama jabatan terlalu panjang (maks 255 karakter)');
            }
            
            $stmt = $pdo->prepare("
                UPDATE jabatan 
                SET nama_jabatan = ?, id_unsur = ?, id_bagian = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nama_jabatan, $id_unsur, $id_bagian, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan updated successfully'
            ]);
            break;
            
        case 'create_jabatan':
            $nama_jabatan = trim(strip_tags($_POST['nama_jabatan'] ?? ''));
            $id_unsur = filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $id_bagian = filter_var($_POST['id_bagian'] ?? null, FILTER_VALIDATE_INT) ?: null;
            
            if (!$nama_jabatan) {
                throw new Exception('Nama jabatan is required');
            }
            if (strlen($nama_jabatan) > 255) {
                throw new Exception('Nama jabatan terlalu panjang (maks 255 karakter)');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (nama_jabatan, id_unsur, id_bagian) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$nama_jabatan, $id_unsur, $id_bagian]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'delete_jabatan':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            
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
            
        case 'update_order':
            $orders = json_decode($_POST['orders'] ?? '[]', true);
            
            if (empty($orders) || !is_array($orders)) {
                throw new Exception('Orders data is required');
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE jabatan SET urutan = ?, id_unsur = ?, id_bagian = ? WHERE id = ?");
                foreach ($orders as $order) {
                    $oid = filter_var($order['id'] ?? 0, FILTER_VALIDATE_INT);
                    $ourutan = filter_var($order['urutan'] ?? 0, FILTER_VALIDATE_INT);
                    $oUnsur = filter_var($order['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null;
                    $oBagian = filter_var($order['id_bagian'] ?? null, FILTER_VALIDATE_INT) ?: null;
                    if ($oid) {
                        $stmt->execute([$ourutan, $oUnsur, $oBagian, $oid]);
                    }
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Urutan jabatan berhasil disimpan']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
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
    error_log('[jabatan_api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $safeMessage = in_array($e->getMessage(), [
        'ID is required', 'ID and nama jabatan are required',
        'Nama jabatan is required', 'Nama jabatan terlalu panjang (maks 255 karakter)',
        'Unsur ID is required', 'Invalid action', 'Orders data is required'
    ]) ? $e->getMessage() : 'Terjadi kesalahan. Silakan coba lagi.';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $safeMessage]);
}
?>
