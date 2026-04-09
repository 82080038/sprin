<?php
/**
 * Unsur API - Separate API endpoints for unsur operations
 * No authentication required for GET operations
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

// CSRF validation for mutating POST requests (skip GET-only actions)
$readOnlyActions = ['get_unsur_detail', 'get_all_unsur', 'get_unsur_stats'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $readOnlyActions)) {
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

// Action already resolved above

try {
    switch ($action) {
        case 'get_unsur_detail':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            $unsur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $unsur
            ]);
            break;
            
        case 'get_all_unsur':
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
            
            echo json_encode([
                'success' => true,
                'data' => $unsurList
            ]);
            break;
            
        case 'get_unsur_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_unsur,
                    COUNT(CASE WHEN u.urutan <= 5 THEN 1 END) as struktural_unsur,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil
                FROM unsur u
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'create_unsur':
            $nama_unsur = trim(strip_tags($_POST['nama_unsur'] ?? ''));
            $deskripsi = trim(strip_tags($_POST['deskripsi'] ?? ''));
            
            if (!$nama_unsur) {
                throw new Exception('Nama unsur is required');
            }
            if (strlen($nama_unsur) > 255) {
                throw new Exception('Nama unsur terlalu panjang (maks 255 karakter)');
            }
            
            // Auto-generate kode_unsur from nama_unsur
            $kode_unsur = preg_replace('/[^a-zA-Z0-9_]/', '_', strtoupper($nama_unsur));
            
            // Get the highest current urutan and add 1
            $stmt = $pdo->query("SELECT MAX(urutan) as max_urutan FROM unsur");
            $maxUrutan = $stmt->fetch()['max_urutan'];
            $newUrutan = ($maxUrutan ?? 0) + 1;
            
            $stmt = $pdo->prepare("INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, urutan) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kode_unsur, $nama_unsur, $deskripsi, $newUrutan]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Unsur created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_unsur':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $nama_unsur = trim(strip_tags($_POST['nama_unsur'] ?? ''));
            $deskripsi = trim(strip_tags($_POST['deskripsi'] ?? ''));
            $urutan = filter_var($_POST['urutan'] ?? null, FILTER_VALIDATE_INT) ?: null;
            
            if (!$id || !$nama_unsur) {
                throw new Exception('ID and nama unsur are required');
            }
            if (strlen($nama_unsur) > 255) {
                throw new Exception('Nama unsur terlalu panjang (maks 255 karakter)');
            }
            
            // Auto-generate kode_unsur from nama_unsur
            $kode_unsur = preg_replace('/[^a-zA-Z0-9_]/', '_', strtoupper($nama_unsur));
            
            $stmt = $pdo->prepare("UPDATE unsur SET nama_unsur = ?, kode_unsur = ?, deskripsi = ?, urutan = ? WHERE id = ?");
            $stmt->execute([$nama_unsur, $kode_unsur, $deskripsi, $urutan, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Unsur updated successfully'
            ]);
            break;
            
        case 'delete_unsur':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            // Check if unsur has jabatan
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE id_unsur = ?");
            $stmt->execute([$id]);
            $jabatanCount = $stmt->fetchColumn();
            
            if ($jabatanCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete unsur with active jabatan',
                    'jabatan_count' => $jabatanCount
                ]);
                break;
            }
            
            // Check if unsur has bagian
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bagian WHERE id_unsur = ?");
            $stmt->execute([$id]);
            $bagianCount = $stmt->fetchColumn();
            
            if ($bagianCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete unsur with active bagian',
                    'bagian_count' => $bagianCount
                ]);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Unsur deleted successfully'
            ]);
            break;
            
        case 'update_order':
            $orders = json_decode($_POST['orders'] ?? '[]', true);
            
            if (empty($orders)) {
                throw new Exception('Orders data is required');
            }
            
            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("UPDATE unsur SET urutan = ? WHERE id = ?");
                foreach ($orders as $order) {
                    $stmt->execute([$order['urutan'], $order['id']]);
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Order updated successfully'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'force_delete_unsur':
            $id = $_POST['id'] ?? 0;
            $reassignToUnsurId = $_POST['reassign_to_unsur_id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $pdo->beginTransaction();
            
            try {
                if ($reassignToUnsurId) {
                    // Reassign bagian to another unsur
                    $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ? WHERE id_unsur = ?");
                    $stmt->execute([$reassignToUnsurId, $id]);
                    
                    // Reassign jabatan to another unsur
                    $stmt = $pdo->prepare("UPDATE jabatan SET id_unsur = ? WHERE id_unsur = ?");
                    $stmt->execute([$reassignToUnsurId, $id]);
                } else {
                    // Delete all related bagian
                    $stmt = $pdo->prepare("DELETE FROM bagian WHERE id_unsur = ?");
                    $stmt->execute([$id]);
                    
                    // Delete all related jabatan
                    $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id_unsur = ?");
                    $stmt->execute([$id]);
                }
                
                // Delete the unsur
                $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
                $stmt->execute([$id]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Unsur force deleted successfully'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log('[unsur_api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $safeMessage = in_array($e->getMessage(), [
        'ID is required', 'ID and nama unsur are required',
        'Nama unsur is required', 'Nama unsur terlalu panjang (maks 255 karakter)',
        'Orders data is required', 'Invalid action',
        'CSRF token required', 'Invalid CSRF token'
    ]) ? $e->getMessage() : 'Terjadi kesalahan. Silakan coba lagi.';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $safeMessage]);
}
?>
