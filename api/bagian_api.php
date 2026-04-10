<?php
/**
 * Bagian API - Separate API endpoints for bagian operations
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
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
    SessionManager::start();
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
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $nama_bagian = trim(strip_tags($_POST['nama_bagian'] ?? ''));
            $id_unsur = filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null;
            
            if (!$id || !$nama_bagian) {
                throw new Exception('ID and nama bagian are required');
            }
            if (strlen($nama_bagian) > 255) {
                throw new Exception('Nama bagian terlalu panjang (maks 255 karakter)');
            }
            
            $stmt = $pdo->prepare("UPDATE bagian SET nama_bagian = ?, id_unsur = ? WHERE id = ?");
            $stmt->execute([$nama_bagian, $id_unsur, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Bagian updated successfully'
            ]);
            break;
            
        case 'create_bagian':
            $nama_bagian = trim(strip_tags($_POST['nama_bagian'] ?? ''));
            $id_unsur = filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null;
            
            if (!$nama_bagian) {
                throw new Exception('Nama bagian is required');
            }
            if (strlen($nama_bagian) > 255) {
                throw new Exception('Nama bagian terlalu panjang (maks 255 karakter)');
            }
            
            // Get next urutan if id_unsur is provided
            if ($id_unsur) {
                $stmt = $pdo->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 as next_urutan FROM bagian WHERE id_unsur = ?");
                $stmt->execute([$id_unsur]);
                $nextUrutan = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian, id_unsur, urutan) VALUES (?, ?, ?)");
                $stmt->execute([$nama_bagian, $id_unsur, $nextUrutan]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian) VALUES (?)");
                $stmt->execute([$nama_bagian]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Bagian created successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'delete_bagian':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            
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
            
        case 'move_bagian':
            $bagianId = filter_var($_POST['bagian_id'] ?? 0, FILTER_VALIDATE_INT);
            $newUnsurId = filter_var($_POST['new_unsur_id'] ?? 0, FILTER_VALIDATE_INT);
            $newUrutan = filter_var($_POST['new_urutan'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$bagianId) {
                throw new Exception('Bagian ID is required');
            }
            
            try {
                $pdo->beginTransaction();
                
                // Check if urutan column exists
                $columnCheck = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
                $hasUrutanColumn = $columnCheck->rowCount() > 0;
                
                if ($hasUrutanColumn) {
                    // Update bagian's unsur and urutan
                    $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ?, urutan = ? WHERE id = ?");
                    $stmt->execute([$newUnsurId, $newUrutan, $bagianId]);
                    
                    // Reorder other bagian in the same unsur to maintain sequence
                    $stmt = $pdo->prepare("SELECT id, urutan FROM bagian WHERE id_unsur = ? AND id != ? ORDER BY urutan");
                    $stmt->execute([$newUnsurId, $bagianId]);
                    $otherBagians = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $urutan = 1;
                    foreach ($otherBagians as $other) {
                        if ($urutan == $newUrutan) $urutan++; // Skip the moved position
                        $updateStmt = $pdo->prepare("UPDATE bagian SET urutan = ? WHERE id = ?");
                        $updateStmt->execute([$urutan, $other['id']]);
                        $urutan++;
                    }
                    
                    $message = 'Bagian berhasil dipindahkan dan urutan diperbarui!';
                } else {
                    // Fallback: only update unsur if urutan column doesn't exist
                    $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ? WHERE id = ?");
                    $stmt->execute([$newUnsurId, $bagianId]);
                    $message = 'Bagian berhasil dipindahkan (urutan tidak disimpan karena column tidak ada)';
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => $message
                ]);
            } catch (Exception $e) {
                $pdo->rollback();
                throw $e;
            }
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
    error_log('[bagian_api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $safeMessage = in_array($e->getMessage(), [
        'ID is required', 'ID and nama bagian are required',
        'Nama bagian is required', 'Nama bagian terlalu panjang (maks 255 karakter)',
        'Bagian ID is required', 'Invalid action'
    ]) ? $e->getMessage() : 'Terjadi kesalahan. Silakan coba lagi.';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $safeMessage]);
}
?>
