<?php
/**
 * Jabatan CRUD API - Create, Read, Update, Delete operations for Jabatan
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Include configuration
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/calendar_config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get action
$action = $_POST['action'] ?? '';
$valid_actions = [
    'get_jabatan_list', 
    'get_jabatan_detail', 
    'create_jabatan', 
    'update_jabatan', 
    'delete_jabatan',
    'get_jabatan_by_unsur'
];

if (!in_array($action, $valid_actions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    switch ($action) {
        case 'get_jabatan_list':
            $unsurId = $_POST['id_unsur'] ?? null;
            
            $sql = "
                SELECT 
                    j.id,
                    j.nama_jabatan,
                    j.id_unsur,
                    u.nama_unsur,
                    u.urutan as urutan_unsur,
                    (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = FALSE AND p.is_active = TRUE) as personil_count
                FROM jabatan j
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE 1=1
            ";
            
            $params = [];
            if ($unsurId) {
                $sql .= " AND j.id_unsur = ?";
                $params[] = $unsurId;
            }
            
            $sql .= " ORDER BY u.urutan ASC, j.nama_jabatan ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $jabatanData = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $jabatanData,
                'message' => 'Jabatan data retrieved successfully'
            ]);
            break;
            
        case 'get_jabatan_detail':
            $id = $_POST['id'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT 
                    j.id,
                    j.nama_jabatan,
                    j.id_unsur,
                    u.nama_unsur,
                    u.urutan as urutan_unsur
                FROM jabatan j
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE j.id = ?
            ");
            $stmt->execute([$id]);
            $jabatan = $stmt->fetch();
            
            if ($jabatan) {
                echo json_encode(['success' => true, 'data' => $jabatan]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Jabatan not found']);
            }
            break;
            
        case 'create_jabatan':
            $nama_jabatan = trim($_POST['nama_jabatan'] ?? '');
            $id_unsur = $_POST['id_unsur'] ?? null;
            
            if (empty($nama_jabatan)) {
                echo json_encode(['success' => false, 'message' => 'Nama jabatan wajib diisi']);
                exit;
            }
            
            if (empty($id_unsur)) {
                echo json_encode(['success' => false, 'message' => 'Unsur wajib dipilih']);
                exit;
            }
            
            // Check for duplicate
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ?");
            $checkStmt->execute([$nama_jabatan, $id_unsur]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Jabatan dengan nama tersebut sudah ada di unsur ini']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO jabatan (nama_jabatan, id_unsur) VALUES (?, ?)");
            $stmt->execute([$nama_jabatan, $id_unsur]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Jabatan berhasil ditambahkan',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_jabatan':
            $id = $_POST['id'] ?? 0;
            $nama_jabatan = trim($_POST['nama_jabatan'] ?? '');
            $id_unsur = $_POST['id_unsur'] ?? null;
            
            if (empty($nama_jabatan)) {
                echo json_encode(['success' => false, 'message' => 'Nama jabatan wajib diisi']);
                exit;
            }
            
            if (empty($id_unsur)) {
                echo json_encode(['success' => false, 'message' => 'Unsur wajib dipilih']);
                exit;
            }
            
            // Check for duplicate (excluding current record)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ? AND id != ?");
            $checkStmt->execute([$nama_jabatan, $id_unsur, $id]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Jabatan dengan nama tersebut sudah ada di unsur ini']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE jabatan SET nama_jabatan = ?, id_unsur = ? WHERE id = ?");
            $stmt->execute([$nama_jabatan, $id_unsur, $id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Jabatan berhasil diperbarui',
                'rows_affected' => $stmt->rowCount()
            ]);
            break;
            
        case 'delete_jabatan':
            $id = $_POST['id'] ?? 0;
            
            // Check if jabatan has personil
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_deleted = FALSE");
            $checkStmt->execute([$id]);
            $personilCount = $checkStmt->fetchColumn();
            
            if ($personilCount > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Tidak dapat menghapus jabatan yang masih memiliki $personilCount personil!"
                ]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Jabatan berhasil dihapus',
                'rows_affected' => $stmt->rowCount()
            ]);
            break;
            
        case 'get_jabatan_by_unsur':
            $id_unsur = $_POST['id_unsur'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT id, nama_jabatan 
                FROM jabatan 
                WHERE id_unsur = ? 
                ORDER BY nama_jabatan ASC
            ");
            $stmt->execute([$id_unsur]);
            $jabatanList = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $jabatanList
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
