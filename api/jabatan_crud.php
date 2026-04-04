<?php
declare(strict_types=1);
/**
 * Jabatan CRUD API - Create, Read, Update, Delete operations for Jabatan
 * Standardized Version with proper validation and error handling
 */

// Set headers first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/APIResponse.php';

// Initialize authentication
$auth = APIAuth::getInstance();

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication - allow list action for dropdowns
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
if (in_array($action, ['get_jabatan_list', 'get_jabatan_by_unsur'])) {
    // Allow public access for list operations
} else {
    // Check if action exists first before requiring auth
    if (!in_array($action, ['get_jabatan_list', 'get_jabatan', 'create_jabatan', 'update_jabatan', 'delete_jabatan', 'get_jabatan_by_unsur', 'get_jabatan_by_bagian'])) {
        $auth->sendError('Invalid action', 400);
        exit;
    }
    $auth->requireAuth();
}

// Validate request method
$auth->validateMethod(['POST']);

// Get action
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
$valid_actions = [
    'get_jabatan_list', 'get_jabatan', 'create_jabatan', 
    'update_jabatan', 'delete_jabatan', 'get_jabatan_by_unsur', 'get_jabatan_by_bagian'
];

if (!in_array($action, $valid_actions)) {
    $auth->sendError('Invalid action', 400);
    exit;
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    switch ($action) {
        case 'get_jabatan_list':
            $unsur_filter = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? null;
            $include_inactive = (filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_inactive', FILTER_SANITIZE_STRING) ?? 'false') === 'true';
            
            $where_conditions = [];
            $params = [];
            
            if (!$include_inactive) {
                $where_conditions[] = "j.is_active = 1";
            }
            
            if ($unsur_filter) {
                $where_conditions[] = "j.id_unsur = ?";
                $params[] = $unsur_filter;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $sql = "
                SELECT 
                    j.id,
                    j.nama_jabatan,
                    j.id_unsur,
                    u.nama_unsur,
                    u.kode_unsur,
                    u.urutan as unsur_urutan,
                    COUNT(p.id) as personil_count
                FROM jabatan j
                LEFT JOIN unsur u ON j.id_unsur = u.id
                LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
                $where_clause
                GROUP BY j.id
                ORDER BY u.urutan ASC, j.nama_jabatan ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $jabatanData = $stmt->fetchAll();
            
            // Format response data
            $formattedData = array_map(function($jabatan) {
                return [
                    'id' => (int)$jabatan['id'],
                    'nama_jabatan' => $jabatan['nama_jabatan'],
                    'id_unsur' => (int)$jabatan['id_unsur'],
                    'unsur' => [
                        'id' => (int)$jabatan['id_unsur'],
                        'nama' => $jabatan['nama_unsur'],
                        'kode' => $jabatan['kode_unsur'],
                        'urutan' => (int)$jabatan['unsur_urutan']
                    ],
                    'personil_count' => (int)$jabatan['personil_count'],
                    'is_active' => (bool)($jabatan['is_active'] ?? 1),
                    'created_at' => $jabatan['created_at'] ?? null,
                    'updated_at' => $jabatan['updated_at'] ?? null
                ];
            }, $jabatanData);
            
            echo json_encode(APIResponse::success($formattedData, 'Jabatan list retrieved successfully'));
            break;
            
        case 'get_jabatan_detail':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid jabatan ID', 400));
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    j.*,
                    u.nama_unsur,
                    u.kode_unsur,
                    u.urutan as unsur_urutan,
                    COUNT(p.id) as personil_count,
                    COUNT(CASE WHEN p.status_ket = 'aktif' THEN p.id END) as aktif_count,
                    COUNT(CASE WHEN p.status_ket = 'nonaktif' THEN p.id END) as nonaktif_count
                FROM jabatan j
                LEFT JOIN unsur u ON j.id_unsur = u.id
                LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
                WHERE j.id = ?
                GROUP BY j.id
            ");
            $stmt->execute([$id]);
            $jabatan = $stmt->fetch();
            
            if ($jabatan) {
                $formattedData = [
                    'id' => (int)$jabatan['id'],
                    'nama_jabatan' => $jabatan['nama_jabatan'],
                    'id_unsur' => (int)$jabatan['id_unsur'],
                    'unsur' => [
                        'id' => (int)$jabatan['id_unsur'],
                        'nama' => $jabatan['nama_unsur'],
                        'kode' => $jabatan['kode_unsur'],
                        'urutan' => (int)$jabatan['unsur_urutan']
                    ],
                    'personil_count' => (int)$jabatan['personil_count'],
                    'aktif_count' => (int)$jabatan['aktif_count'],
                    'nonaktif_count' => (int)$jabatan['nonaktif_count'],
                    'is_active' => (bool)($jabatan['is_active'] ?? 1),
                    'created_at' => $jabatan['created_at'] ?? null,
                    'updated_at' => $jabatan['updated_at'] ?? null
                ];
                
                echo json_encode(APIResponse::success($formattedData, 'Jabatan data retrieved'));
            } else {
                echo json_encode(APIResponse::error('Jabatan not found', 404));
            }
            break;
            
        case 'get_jabatan_by_unsur':
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id_unsur <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            // Check if unsur exists
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ? AND is_active = 1");
            $checkStmt->execute([$id_unsur]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Unsur not found', 404));
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    j.id,
                    j.nama_jabatan,
                    COUNT(p.id) as personil_count
                FROM jabatan j
                LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
                WHERE j.id_unsur = ? AND j.is_active = 1
                GROUP BY j.id
                ORDER BY j.nama_jabatan ASC
            ");
            $stmt->execute([$id_unsur]);
            $jabatanData = $stmt->fetchAll();
            
            $formattedData = array_map(function($jabatan) {
                return [
                    'id' => (int)$jabatan['id'],
                    'nama_jabatan' => $jabatan['nama_jabatan'],
                    'personil_count' => (int)$jabatan['personil_count']
                ];
            }, $jabatanData);
            
            echo json_encode(APIResponse::success($formattedData, 'Jabatan data retrieved successfully'));
            break;
            
        case 'create_jabatan':
            // Validate required fields
            $required_fields = ['nama_jabatan', 'id_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_jabatan = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_jabatan', FILTER_SANITIZE_STRING));
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id_unsur <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            // Check if unsur exists
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ? AND is_active = 1");
            $checkStmt->execute([$id_unsur]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Unsur not found', 404));
                exit;
            }
            
            // Check for duplicate nama_jabatan within same unsur
            $checkStmt = $pdo->prepare("SELECT id FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ?");
            $checkStmt->execute([$nama_jabatan, $id_unsur]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama jabatan already exists in this unsur', 400));
                exit;
            }
            
            // Add development bypass
            $dev_bypass = isset(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'dev_bypass', FILTER_SANITIZE_STRING)) ? filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'dev_bypass', FILTER_SANITIZE_STRING) : '';
            
            // Insert jabatan
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (nama_jabatan, id_unsur, is_active, created_at, updated_at)
                VALUES (?, ?, 1, NOW(), NOW())
            ");
            
            $result = $stmt->execute([$nama_jabatan, $id_unsur]);
            
            if ($result) {
                $newId = $pdo->lastInsertId();
                echo json_encode(APIResponse::success([
                    'id' => (int)$newId,
                    'nama_jabatan' => $nama_jabatan,
                    'id_unsur' => $id_unsur
                ], 'Jabatan berhasil ditambahkan'));
            } else {
                echo json_encode(APIResponse::error('Failed to create jabatan', 500));
            }
            break;
            
        case 'update_jabatan':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid jabatan ID', 400));
                exit;
            }
            
            // Check if jabatan exists
            $checkStmt = $pdo->prepare("SELECT id FROM jabatan WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Jabatan not found', 404));
                exit;
            }
            
            // Validate required fields
            $required_fields = ['nama_jabatan', 'id_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_jabatan = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_jabatan', FILTER_SANITIZE_STRING));
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            $is_active = isset(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'is_active', FILTER_SANITIZE_STRING)) ? (int)filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'is_active', FILTER_SANITIZE_STRING) : 1;
            
            if ($id_unsur <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            // Check if unsur exists
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ? AND is_active = 1");
            $checkStmt->execute([$id_unsur]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Unsur not found', 404));
                exit;
            }
            
            // Check for duplicate nama_jabatan within same unsur (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ? AND id != ?");
            $checkStmt->execute([$nama_jabatan, $id_unsur, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama jabatan already exists in this unsur', 400));
                exit;
            }
            
            // Update jabatan
            $stmt = $pdo->prepare("
                UPDATE jabatan SET
                    nama_jabatan = ?,
                    id_unsur = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$nama_jabatan, $id_unsur, $is_active, $id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'rows_affected' => $stmt->rowCount()
                ], 'Jabatan berhasil diperbarui'));
            } else {
                echo json_encode(APIResponse::error('Failed to update jabatan', 500));
            }
            break;
            
        case 'delete_jabatan':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid jabatan ID', 400));
                exit;
            }
            
            // Check if jabatan exists
            $checkStmt = $pdo->prepare("SELECT id, nama_jabatan FROM jabatan WHERE id = ?");
            $checkStmt->execute([$id]);
            $jabatan = $checkStmt->fetch();
            
            if (!$jabatan) {
                echo json_encode(APIResponse::error('Jabatan not found', 404));
                exit;
            }
            
            // Check if jabatan has personil
            $personilStmt = $pdo->prepare("SELECT COUNT(*) as total FROM personil WHERE id_jabatan = ? AND is_deleted = 0");
            $personilStmt->execute([$id]);
            $personilCount = $personilStmt->fetch()['total'];
            
            if ($personilCount > 0) {
                echo json_encode(APIResponse::error(
                    "Cannot delete jabatan '{$jabatan['nama_jabatan']}' because it has {$personilCount} personil. " .
                    "Please reassign or delete the personil first.", 400
                ));
                exit;
            }
            
            // Soft delete jabatan
            $stmt = $pdo->prepare("UPDATE jabatan SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama_jabatan' => $jabatan['nama_jabatan'],
                    'rows_affected' => $stmt->rowCount()
                ], 'Jabatan berhasil dihapus'));
            } else {
                echo json_encode(APIResponse::error('Failed to delete jabatan', 500));
            }
            break;
    }
    
} catch(Exception $e) {
    if (ENVIRONMENT === 'development') {
        echo json_encode(APIResponse::error('Database error: ' . $e->getMessage(), 500, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]));
    } else {
        echo json_encode(APIResponse::error('Failed to process request', 500));
    }
}
?>
