<?php
/**
 * Jabatan API - Separate API endpoints for jabatan operations
 */

// Error reporting - FORCE ON for development debugging
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Force error reporting ON for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/opt/lampp/logs/php_error_log');

// Capture all errors and warnings
$phpErrors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$phpErrors) {
    $phpErrors[] = "[$errno] $errstr in $errfile:$errline";
    return true; // Don't execute PHP internal error handler
});

// Capture fatal errors
register_shutdown_function(function() use (&$phpErrors) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $phpErrors[] = "[FATAL] {$error['message']} in {$error['file']}:{$error['line']}";
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error occurred',
            'php_errors' => $phpErrors
        ]);
    }
});

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// CSRF validation for mutating requests only (skip for read-only actions)
$readOnlyActions = ['get_jabatan_detail', 'get_all_jabatan', 'get_csrf_token'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $readOnlyActions)) {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    // Always start session first
    SessionManager::start();
    
    // Debug info for troubleshooting
    $debugInfo = [
        'action' => $action,
        'token_received' => !empty($csrfToken) ? substr($csrfToken, 0, 10) . '...' : 'EMPTY',
        'session_active' => SessionManager::isActive(),
        'session_id' => session_id(),
        'has_csrf_in_session' => isset($_SESSION['csrf_token']),
        'session_csrf_preview' => isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 10) . '...' : 'NONE',
        'cookies_received' => isset($_COOKIE) ? array_keys($_COOKIE) : [],
        'php_session_cookie' => isset($_COOKIE['PHPSESSID']) ? 'YES' : 'NO'
    ];
    
    error_log("[JABATAN API CSRF DEBUG] " . json_encode($debugInfo));
    
    if (empty($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'CSRF token required',
            'debug' => $debugInfo,
            'csrf_expired' => true
        ]);
        exit;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'CSRF token not found in session. Try refreshing the page.',
            'debug' => $debugInfo,
            'csrf_expired' => true
        ]);
        exit;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid CSRF token',
            'debug' => $debugInfo,
            'csrf_expired' => true,
            'retry_with_fresh_token' => true
        ]);
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
        case 'get_csrf_token':
            SessionManager::start();
            $token = \AuthHelper::generateCSRFToken();
            echo json_encode([
                'success' => true,
                'csrf_token' => $token
            ]);
            break;
            
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
            
            // Auto-regenerate kode_jabatan if nama_jabatan changed
            $kode_jabatan = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama_jabatan));
            
            // Check for duplicate kode_jabatan (excluding current record)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE kode_jabatan = ? AND id != ?");
            $checkStmt->execute([$kode_jabatan, $id]);
            if ($checkStmt->fetchColumn() > 0) {
                $counter = 1;
                do {
                    $new_kode = $kode_jabatan . $counter;
                    $checkStmt->execute([$new_kode, $id]);
                    $counter++;
                } while ($checkStmt->fetchColumn() > 0);
                $kode_jabatan = $new_kode;
            }
            
            $stmt = $pdo->prepare("
                UPDATE jabatan 
                SET nama_jabatan = ?, kode_jabatan = ?, id_unsur = ?, id_bagian = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nama_jabatan, $kode_jabatan, $id_unsur, $id_bagian, $id]);
            
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
            
            // Auto-generate kode_jabatan from nama_jabatan
            $kode_jabatan = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama_jabatan));
            
            // Check for duplicate kode_jabatan and append number if needed
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE kode_jabatan = ?");
            $checkStmt->execute([$kode_jabatan]);
            if ($checkStmt->fetchColumn() > 0) {
                $counter = 1;
                do {
                    $new_kode = $kode_jabatan . $counter;
                    $checkStmt->execute([$new_kode]);
                    $counter++;
                } while ($checkStmt->fetchColumn() > 0);
                $kode_jabatan = $new_kode;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (kode_jabatan, nama_jabatan, id_unsur, id_bagian) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$kode_jabatan, $nama_jabatan, $id_unsur, $id_bagian]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jabatan created successfully with code: ' . $kode_jabatan,
                'id' => $pdo->lastInsertId(),
                'kode_jabatan' => $kode_jabatan
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
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'php_errors' => $phpErrors,
        'session_id' => session_id(),
        'has_session' => isset($_SESSION)
    ]);
}
?>
