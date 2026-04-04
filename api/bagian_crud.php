<?php
declare(strict_types=1);
/**
 * Bagian CRUD API - Create, Read, Update, Delete operations
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
if (in_array($action, ['get_bagian_list', 'get_bagian_by_unsur'])) {
    // Allow public access for list operations
} else {
    // Check if action exists first before requiring auth
    if (!in_array($action, ['get_bagian_list', 'get_bagian', 'create_bagian', 'update_bagian', 'delete_bagian', 'move_bagian', 'update_order', 'get_bagian_by_unsur'])) {
        $auth->sendError('Invalid action', 400);
        exit;
    }
    $auth->requireAuth();
}

// Validate request method
$auth->validateMethod(['POST']);

// Get action
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
$valid_actions = ['get_bagian_list', 'get_bagian', 'create_bagian', 'update_bagian', 'delete_bagian', 'move_bagian', 'update_order', 'get_bagian_by_unsur'];

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
        case 'get_bagian_list':
            $unsur_filter = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) ?? null;
            $include_inactive = (filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_inactive', FILTER_SANITIZE_STRING) ?? 'false') === 'true';
            
            $where_conditions = [];
            $params = [];
            
            if (!$include_inactive) {
                $where_conditions[] = "b.is_active = 1";
            }
            
            if ($unsur_filter) {
                $where_conditions[] = "b.id_unsur = ?";
                $params[] = $unsur_filter;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $sql = "
                SELECT 
                    b.*,
                    u.nama_unsur,
                    u.kode_unsur,
                    u.urutan as unsur_urutan,
                    COUNT(p.id) as personil_count
                FROM bagian b
                LEFT JOIN unsur u ON b.id_unsur = u.id
                LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                $where_clause
                GROUP BY b.id, b.is_active
                ORDER BY u.urutan ASC, b.urutan ASC, b.nama_bagian ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $bagianData = $stmt->fetchAll();
            
            // Format response data
            $formattedData = array_map(function($bagian) {
                return [
                    'id' => (int)$bagian['id'],
                    'nama_bagian' => $bagian['nama_bagian'],
                    'kode_bagian' => $bagian['kode_bagian'] ?? '',
                    'id_unsur' => (int)($bagian['id_unsur']),
                    'urutan' => (int)$bagian['urutan'],
                    'unsur' => [
                        'id' => (int)($bagian['id_unsur']),
                        'nama' => $bagian['nama_unsur'],
                        'kode' => $bagian['kode_unsur'],
                        'urutan' => (int)($bagian['unsur_urutan'])
                    ],
                    'personil_count' => (int)$bagian['personil_count'],
                    'is_active' => (bool)$bagian['is_active'],
                    'created_at' => $bagian['created_at'],
                    'updated_at' => $bagian['updated_at']
                ];
            }, $bagianData);
            
            echo json_encode(APIResponse::success($formattedData, 'Bagian list retrieved successfully'));
            break;
            
        case 'get_bagian':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid bagian ID', 400));
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.*,
                    u.nama_unsur,
                    u.kode_unsur,
                    u.urutan as unsur_urutan,
                    COUNT(p.id) as personil_count,
                    COUNT(CASE WHEN p.status_ket = 'aktif' THEN p.id END) as aktif_count,
                    COUNT(CASE WHEN p.status_ket = 'nonaktif' THEN p.id END) as nonaktif_count
                FROM bagian b
                LEFT JOIN unsur u ON b.id_unsur = u.id
                LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                WHERE b.id = ?
                GROUP BY b.id
            ");
            $stmt->execute([$id]);
            $bagian = $stmt->fetch();
            
            if ($bagian) {
                // Get pimpinan data
                $pimpinanStmt = $pdo->prepare("
                    SELECT p.nama, p.nrp, pg.nama_pangkat, pg.singkatan
                    FROM bagian_pimpinan bp
                    JOIN personil p ON bp.id_personil = p.id
                    JOIN pangkat pg ON p.id_pangkat = pg.id
                    WHERE bp.id_bagian = ? AND bp.is_active = 1
                    ORDER BY bp.urutan
                ");
                $pimpinanStmt->execute([$id]);
                $pimpinanData = $pimpinanStmt->fetchAll();
                
                $formattedData = [
                    'id' => (int)$bagian['id'],
                    'nama_bagian' => $bagian['nama_bagian'],
                    'kode_bagian' => $bagian['kode_bagian'] ?? '',
                    'id_unsur' => (int)$bagian['id_unsur'],
                    'urutan' => (int)$bagian['urutan'],
                    'unsur' => [
                        'id' => (int)$bagian['id_unsur'],
                        'nama' => $bagian['nama_unsur'],
                        'kode' => $bagian['kode_unsur'],
                        'urutan' => (int)$bagian['unsur_urutan']
                    ],
                    'personil_count' => (int)$bagian['personil_count'],
                    'aktif_count' => (int)$bagian['aktif_count'],
                    'nonaktif_count' => (int)$bagian['nonaktif_count'],
                    'pimpinan' => array_map(function($p) {
                        return [
                            'nama' => $p['nama'],
                            'nrp' => $p['nrp'],
                            'pangkat' => $p['nama_pangkat'],
                            'singkatan' => $p['singkatan']
                        ];
                    }, $pimpinanData),
                    'is_active' => (bool)$bagian['is_active'],
                    'created_at' => $bagian['created_at'],
                    'updated_at' => $bagian['updated_at']
                ];
                
                echo json_encode(APIResponse::success($formattedData, 'Bagian data retrieved'));
            } else {
                echo json_encode(APIResponse::error('Bagian not found', 404));
            }
            break;
            
        case 'get_bagian_by_unsur':
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
                    b.id,
                    b.nama_bagian,
                    b.kode_bagian,
                    b.urutan,
                    COUNT(p.id) as personil_count
                FROM bagian b
                LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                WHERE b.id_unsur = ? AND b.is_active = 1
                GROUP BY b.id
                ORDER BY b.urutan ASC, b.nama_bagian ASC
            ");
            $stmt->execute([$id_unsur]);
            $bagianData = $stmt->fetchAll();
            
            $formattedData = array_map(function($bagian) {
                return [
                    'id' => (int)$bagian['id'],
                    'nama_bagian' => $bagian['nama_bagian'],
                    'kode_bagian' => $bagian['kode_bagian'] ?? '',
                    'urutan' => (int)$bagian['urutan'],
                    'personil_count' => (int)$bagian['personil_count']
                ];
            }, $bagianData);
            
            echo json_encode(APIResponse::success($formattedData, 'Bagian data retrieved successfully'));
            break;
            
        case 'create_bagian':
            // Validate required fields
            $required_fields = ['nama_bagian', 'id_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_bagian = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_bagian', FILTER_SANITIZE_STRING));
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            $kode_bagian = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_bagian', FILTER_SANITIZE_STRING) ?? '');
            $urutan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'urutan', FILTER_SANITIZE_STRING) ?? 1);
            
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
            
            // Check for duplicate nama_bagian within same unsur
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE nama_bagian = ? AND id_unsur = ?");
            $checkStmt->execute([$nama_bagian, $id_unsur]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama bagian already exists in this unsur', 400));
                exit;
            }
            
            // Check for duplicate kode_bagian
            if (!empty($kode_bagian)) {
                $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE kode_bagian = ?");
                $checkStmt->execute([$kode_bagian]);
                if ($checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Kode bagian already exists', 400));
                    exit;
                }
            }
            
            // Validate urutan uniqueness within same unsur
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE urutan = ? AND id_unsur = ?");
            $checkStmt->execute([$urutan, $id_unsur]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Urutan already exists in this unsur', 400));
                exit;
            }
            
            // Insert bagian
            $stmt = $pdo->prepare("
                INSERT INTO bagian (nama_bagian, kode_bagian, id_unsur, urutan, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ");
            
            $result = $stmt->execute([$nama_bagian, $kode_bagian, $id_unsur, $urutan]);
            
            if ($result) {
                $newId = $pdo->lastInsertId();
                echo json_encode(APIResponse::success([
                    'id' => (int)$newId,
                    'nama_bagian' => $nama_bagian,
                    'id_unsur' => $id_unsur,
                    'urutan' => $urutan
                ], 'Bagian berhasil ditambahkan'));
            } else {
                echo json_encode(APIResponse::error('Failed to create bagian', 500));
            }
            break;
            
        case 'update_bagian':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid bagian ID', 400));
                exit;
            }
            
            // Check if bagian exists
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Bagian not found', 404));
                exit;
            }
            
            // Validate required fields
            $required_fields = ['nama_bagian', 'id_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_bagian = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_bagian', FILTER_SANITIZE_STRING));
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            $kode_bagian = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_bagian', FILTER_SANITIZE_STRING) ?? '');
            $urutan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'urutan', FILTER_SANITIZE_STRING) ?? 1);
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
            
            // Check for duplicate nama_bagian within same unsur (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE nama_bagian = ? AND id_unsur = ? AND id != ?");
            $checkStmt->execute([$nama_bagian, $id_unsur, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama bagian already exists in this unsur', 400));
                exit;
            }
            
            // Check for duplicate kode_bagian (excluding current record)
            if (!empty($kode_bagian)) {
                $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE kode_bagian = ? AND id != ?");
                $checkStmt->execute([$kode_bagian, $id]);
                if ($checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Kode bagian already exists', 400));
                    exit;
                }
            }
            
            // Validate urutan uniqueness within same unsur (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE urutan = ? AND id_unsur = ? AND id != ?");
            $checkStmt->execute([$urutan, $id_unsur, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Urutan already exists in this unsur', 400));
                exit;
            }
            
            // Update bagian
            $stmt = $pdo->prepare("
                UPDATE bagian SET
                    nama_bagian = ?,
                    kode_bagian = ?,
                    id_unsur = ?,
                    urutan = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$nama_bagian, $kode_bagian, $id_unsur, $urutan, $is_active, $id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'rows_affected' => $stmt->rowCount()
                ], 'Bagian berhasil diperbarui'));
            } else {
                echo json_encode(APIResponse::error('Failed to update bagian', 500));
            }
            break;
            
        case 'move_bagian':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            $new_unsur_id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_unsur_id', FILTER_SANITIZE_STRING) ?? 0);
            $new_urutan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_urutan', FILTER_SANITIZE_STRING) ?? 1);
            
            if ($id <= 0 || $new_unsur_id <= 0) {
                echo json_encode(APIResponse::error('Invalid bagian or unsur ID', 400));
                exit;
            }
            
            // Check if bagian exists
            $checkStmt = $pdo->prepare("SELECT id, nama_bagian, id_unsur FROM bagian WHERE id = ?");
            $checkStmt->execute([$id]);
            $bagian = $checkStmt->fetch();
            
            if (!$bagian) {
                echo json_encode(APIResponse::error('Bagian not found', 404));
                exit;
            }
            
            // Check if new unsur exists
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ? AND is_active = 1");
            $checkStmt->execute([$new_unsur_id]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('New unsur not found', 404));
                exit;
            }
            
            // Validate urutan uniqueness in new unsur
            $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE urutan = ? AND id_unsur = ?");
            $checkStmt->execute([$new_urutan, $new_unsur_id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Urutan already exists in target unsur', 400));
                exit;
            }
            
            // Move bagian
            $stmt = $pdo->prepare("
                UPDATE bagian SET
                    id_unsur = ?,
                    urutan = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$new_unsur_id, $new_urutan, $id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama_bagian' => $bagian['nama_bagian'],
                    'old_unsur_id' => (int)$bagian['id_unsur'],
                    'new_unsur_id' => $new_unsur_id,
                    'new_urutan' => $new_urutan,
                    'rows_affected' => $stmt->rowCount()
                ], 'Bagian berhasil dipindahkan'));
            } else {
                echo json_encode(APIResponse::error('Failed to move bagian', 500));
            }
            break;
            
        case 'delete_bagian':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid bagian ID', 400));
                exit;
            }
            
            // Check if bagian exists
            $checkStmt = $pdo->prepare("SELECT id, nama_bagian FROM bagian WHERE id = ?");
            $checkStmt->execute([$id]);
            $bagian = $checkStmt->fetch();
            
            if (!$bagian) {
                echo json_encode(APIResponse::error('Bagian not found', 404));
                exit;
            }
            
            // Check if bagian has personil
            $personilStmt = $pdo->prepare("SELECT COUNT(*) as total FROM personil WHERE id_bagian = ? AND is_deleted = 0");
            $personilStmt->execute([$id]);
            $personilCount = $personilStmt->fetch()['total'];
            
            if ($personilCount > 0) {
                echo json_encode(APIResponse::error(
                    "Cannot delete bagian '{$bagian['nama_bagian']}' because it has {$personilCount} personil. " .
                    "Please reassign or delete the personil first.", 400
                ));
                exit;
            }
            
            // Soft delete bagian
            $stmt = $pdo->prepare("UPDATE bagian SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama_bagian' => $bagian['nama_bagian'],
                    'rows_affected' => $stmt->rowCount()
                ], 'Bagian berhasil dihapus'));
            } else {
                echo json_encode(APIResponse::error('Failed to delete bagian', 500));
            }
            break;
            
        case 'update_order':
            $orders = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'orders', FILTER_SANITIZE_STRING) ?? [];
            
            if (empty($orders) || !is_array($orders)) {
                echo json_encode(APIResponse::error('Invalid orders data', 400));
                exit;
            }
            
            $updated = [];
            $errors = [];
            
            $pdo->beginTransaction();
            
            try {
                foreach ($orders as $order) {
                    $id = (int)($order['id'] ?? 0);
                    $urutan = (int)($order['urutan'] ?? 0);
                    $id_unsur = (int)($order['id_unsur'] ?? 0);
                    
                    if ($id <= 0 || $urutan <= 0 || $id_unsur <= 0) {
                        $errors[] = "Invalid data for ID: $id";
                        continue;
                    }
                    
                    // Validate bagian exists
                    $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE id = ?");
                    $checkStmt->execute([$id]);
                    if (!$checkStmt->fetch()) {
                        $errors[] = "Bagian not found for ID: $id";
                        continue;
                    }
                    
                    // Validate unsur exists
                    $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ? AND is_active = 1");
                    $checkStmt->execute([$id_unsur]);
                    if (!$checkStmt->fetch()) {
                        $errors[] = "Unsur not found for ID: $id_unsur";
                        continue;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE bagian SET urutan = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$urutan, $id]);
                    
                    if ($result) {
                        $updated[] = $id;
                    } else {
                        $errors[] = "Failed to update ID: $id";
                    }
                }
                
                if (empty($errors)) {
                    $pdo->commit();
                    echo json_encode(APIResponse::success([
                        'updated' => $updated,
                        'total_updated' => count($updated)
                    ], 'Bagian order updated successfully'));
                } else {
                    $pdo->rollBack();
                    echo json_encode(APIResponse::error('Failed to update some orders', 500, $errors));
                }
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
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
