<?php
declare(strict_types=1);
/**
 * Unsur CRUD API - Create, Read, Update, Delete operations
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
if (in_array($action, ['get_unsur_list', 'get_unsur_stats'])) {
    // Allow public access for list operations
} else {
    // Check if action exists first before requiring auth
    if (!in_array($action, ['get_unsur_list', 'get_unsur', 'create_unsur', 'update_unsur', 'delete_unsur', 'update_order', 'get_unsur_stats'])) {
        $auth->sendError('Invalid action', 400);
        exit;
    }
    $auth->requireAuth();
}

// Validate request method
$auth->validateMethod(['POST']);

// Get action
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
$valid_actions = ['get_unsur_list', 'get_unsur', 'create_unsur', 'update_unsur', 'delete_unsur', 'update_order', 'get_unsur_stats'];

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
        case 'get_unsur_list':
            $unsur_filter = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) ?? null;
            $include_inactive = (filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_inactive', FILTER_SANITIZE_STRING) ?? 'false') === 'true';
            
            $where_conditions = [];
            $params = [];
            
            if (!$include_inactive) {
                $where_conditions[] = "is_active = 1";
            }
            
            if ($unsur_filter) {
                $where_conditions[] = "kode_unsur = ?";
                $params[] = $unsur_filter;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $sql = "SELECT * FROM unsur $where_clause ORDER BY urutan ASC, nama_unsur ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $unsurData = $stmt->fetchAll();
            
            // Format response data
            $formattedData = array_map(function($unsur) {
                return [
                    'id' => (int)$unsur['id'],
                    'nama_unsur' => $unsur['nama_unsur'],
                    'kode_unsur' => $unsur['kode_unsur'],
                    'urutan' => (int)$unsur['urutan'],
                    'deskripsi' => $unsur['deskripsi'] ?? '',
                    'is_active' => (bool)$unsur['is_active'],
                    'created_at' => $unsur['created_at'],
                    'updated_at' => $unsur['updated_at']
                ];
            }, $unsurData);
            
            echo json_encode(APIResponse::success($formattedData, 'Unsur list retrieved successfully'));
            break;
            
        case 'get_unsur':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT u.*, 
                       COUNT(b.id) as bagian_count,
                       COUNT(p.id) as personil_count
                FROM unsur u
                LEFT JOIN bagian b ON u.id = b.id_unsur AND b.is_active = 1
                LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$id]);
            $unsur = $stmt->fetch();
            
            if ($unsur) {
                // Get pimpinan data
                $pimpinanStmt = $pdo->prepare("
                    SELECT p.nama, p.nrp, pg.nama_pangkat, pg.singkatan
                    FROM unsur_pimpinan up
                    JOIN personil p ON up.id_personil = p.id
                    JOIN pangkat pg ON p.id_pangkat = pg.id
                    WHERE up.id_unsur = ? AND up.is_active = 1
                    ORDER BY up.urutan
                ");
                $pimpinanStmt->execute([$id]);
                $pimpinanData = $pimpinanStmt->fetchAll();
                
                $formattedData = [
                    'id' => (int)$unsur['id'],
                    'nama_unsur' => $unsur['nama_unsur'],
                    'kode_unsur' => $unsur['kode_unsur'],
                    'urutan' => (int)$unsur['urutan'],
                    'deskripsi' => $unsur['deskripsi'] ?? '',
                    'is_active' => (bool)$unsur['is_active'],
                    'bagian_count' => (int)$unsur['bagian_count'],
                    'personil_count' => (int)$unsur['personil_count'],
                    'pimpinan' => array_map(function($p) {
                        return [
                            'nama' => $p['nama'],
                            'nrp' => $p['nrp'],
                            'pangkat' => $p['nama_pangkat'],
                            'singkatan' => $p['singkatan']
                        ];
                    }, $pimpinanData),
                    'created_at' => $unsur['created_at'],
                    'updated_at' => $unsur['updated_at']
                ];
                
                echo json_encode(APIResponse::success($formattedData, 'Unsur data retrieved'));
            } else {
                echo json_encode(APIResponse::error('Unsur not found', 404));
            }
            break;
            
        case 'create_unsur':
            // Validate required fields
            $required_fields = ['nama_unsur', 'kode_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_unsur = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_unsur', FILTER_SANITIZE_STRING));
            $kode_unsur = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_unsur', FILTER_SANITIZE_STRING));
            $urutan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'urutan', FILTER_SANITIZE_STRING) ?? 1);
            $deskripsi = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'deskripsi', FILTER_SANITIZE_STRING) ?? '');
            
            // Check for duplicate kode_unsur
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE kode_unsur = ?");
            $checkStmt->execute([$kode_unsur]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Kode unsur already exists', 400));
                exit;
            }
            
            // Check for duplicate nama_unsur
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE nama_unsur = ?");
            $checkStmt->execute([$nama_unsur]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama unsur already exists', 400));
                exit;
            }
            
            // Validate urutan uniqueness
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE urutan = ?");
            $checkStmt->execute([$urutan]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Urutan already exists', 400));
                exit;
            }
            
            // Insert unsur
            $stmt = $pdo->prepare("
                INSERT INTO unsur (nama_unsur, kode_unsur, urutan, deskripsi, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ");
            
            $result = $stmt->execute([$nama_unsur, $kode_unsur, $urutan, $deskripsi]);
            
            if ($result) {
                $newId = $pdo->lastInsertId();
                echo json_encode(APIResponse::success([
                    'id' => (int)$newId,
                    'nama_unsur' => $nama_unsur,
                    'kode_unsur' => $kode_unsur,
                    'urutan' => $urutan
                ], 'Unsur berhasil ditambahkan'));
            } else {
                echo json_encode(APIResponse::error('Failed to create unsur', 500));
            }
            break;
            
        case 'update_unsur':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            // Check if unsur exists
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Unsur not found', 404));
                exit;
            }
            
            // Validate required fields
            $required_fields = ['nama_unsur', 'kode_unsur'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            $nama_unsur = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_unsur', FILTER_SANITIZE_STRING));
            $kode_unsur = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_unsur', FILTER_SANITIZE_STRING));
            $urutan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'urutan', FILTER_SANITIZE_STRING) ?? 1);
            $deskripsi = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'deskripsi', FILTER_SANITIZE_STRING) ?? '');
            $is_active = isset(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'is_active', FILTER_SANITIZE_STRING)) ? (int)filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'is_active', FILTER_SANITIZE_STRING) : 1;
            
            // Check for duplicate kode_unsur (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE kode_unsur = ? AND id != ?");
            $checkStmt->execute([$kode_unsur, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Kode unsur already exists', 400));
                exit;
            }
            
            // Check for duplicate nama_unsur (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE nama_unsur = ? AND id != ?");
            $checkStmt->execute([$nama_unsur, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Nama unsur already exists', 400));
                exit;
            }
            
            // Validate urutan uniqueness (excluding current record)
            $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE urutan = ? AND id != ?");
            $checkStmt->execute([$urutan, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Urutan already exists', 400));
                exit;
            }
            
            // Update unsur
            $stmt = $pdo->prepare("
                UPDATE unsur SET
                    nama_unsur = ?,
                    kode_unsur = ?,
                    urutan = ?,
                    deskripsi = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$nama_unsur, $kode_unsur, $urutan, $deskripsi, $is_active, $id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'rows_affected' => $stmt->rowCount()
                ], 'Unsur berhasil diperbarui'));
            } else {
                echo json_encode(APIResponse::error('Failed to update unsur', 500));
            }
            break;
            
        case 'delete_unsur':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                exit;
            }
            
            // Check if unsur exists
            $checkStmt = $pdo->prepare("SELECT id, nama_unsur FROM unsur WHERE id = ?");
            $checkStmt->execute([$id]);
            $unsur = $checkStmt->fetch();
            
            if (!$unsur) {
                echo json_encode(APIResponse::error('Unsur not found', 404));
                exit;
            }
            
            // Check if unsur has bagian
            $bagianStmt = $pdo->prepare("SELECT COUNT(*) as total FROM bagian WHERE id_unsur = ? AND is_active = 1");
            $bagianStmt->execute([$id]);
            $bagianCount = $bagianStmt->fetch()['total'];
            
            if ($bagianCount > 0) {
                echo json_encode(APIResponse::error(
                    "Cannot delete unsur '{$unsur['nama_unsur']}' because it has {$bagianCount} active bagian. " .
                    "Please reassign or delete the bagian first.", 400
                ));
                exit;
            }
            
            // Soft delete unsur
            $stmt = $pdo->prepare("UPDATE unsur SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama_unsur' => $unsur['nama_unsur'],
                    'rows_affected' => $stmt->rowCount()
                ], 'Unsur berhasil dihapus'));
            } else {
                echo json_encode(APIResponse::error('Failed to delete unsur', 500));
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
                    
                    if ($id <= 0 || $urutan <= 0) {
                        $errors[] = "Invalid data for ID: $id";
                        continue;
                    }
                    
                    // Validate unsur exists
                    $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ?");
                    $checkStmt->execute([$id]);
                    if (!$checkStmt->fetch()) {
                        $errors[] = "Unsur not found for ID: $id";
                        continue;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE unsur SET urutan = ?, updated_at = NOW() WHERE id = ?");
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
                    ], 'Unsur order updated successfully'));
                } else {
                    $pdo->rollBack();
                    echo json_encode(APIResponse::error('Failed to update some orders', 500, $errors));
                }
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'get_unsur_stats':
            $unsur_filter = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) ?? null;
            $include_details = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_details', FILTER_SANITIZE_STRING) === 'true';
            
            $where_conditions = ["u.is_active = 1"];
            $params = [];
            
            if ($unsur_filter) {
                $where_conditions[] = "u.kode_unsur = ?";
                $params[] = $unsur_filter;
            }
            
            $where_clause = implode(" AND ", $where_conditions);
            
            if ($include_details) {
                // Detailed statistics
                $sql = "
                    SELECT 
                        u.id,
                        u.nama_unsur,
                        u.kode_unsur,
                        u.urutan,
                        COUNT(DISTINCT b.id) as bagian_count,
                        COUNT(DISTINCT p.id) as personil_count,
                        COUNT(DISTINCT CASE WHEN p.status_ket = 'aktif' THEN p.id END) as aktif_count,
                        COUNT(DISTINCT CASE WHEN p.status_ket = 'nonaktif' THEN p.id END) as nonaktif_count,
                        GROUP_CONCAT(DISTINCT b.nama_bagian ORDER BY b.nama_bagian) as bagian_list
                    FROM unsur u
                    LEFT JOIN bagian b ON u.id = b.id_unsur AND b.is_active = 1
                    LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                    WHERE $where_clause
                    GROUP BY u.id, u.nama_unsur, u.kode_unsur, u.urutan
                    ORDER BY u.urutan
                ";
            } else {
                // Basic statistics
                $sql = "
                    SELECT 
                        u.id,
                        u.nama_unsur,
                        u.kode_unsur,
                        u.urutan,
                        COUNT(DISTINCT b.id) as bagian_count,
                        COUNT(DISTINCT p.id) as personil_count
                    FROM unsur u
                    LEFT JOIN bagian b ON u.id = b.id_unsur AND b.is_active = 1
                    LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = 0
                    WHERE $where_clause
                    GROUP BY u.id, u.nama_unsur, u.kode_unsur, u.urutan
                    ORDER BY u.urutan
                ";
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetchAll();
            
            // Format statistics
            $formattedStats = array_map(function($stat) use ($include_details) {
                $formatted = [
                    'id' => (int)$stat['id'],
                    'nama_unsur' => $stat['nama_unsur'],
                    'kode_unsur' => $stat['kode_unsur'],
                    'urutan' => (int)$stat['urutan'],
                    'bagian_count' => (int)$stat['bagian_count'],
                    'personil_count' => (int)$stat['personil_count']
                ];
                
                if ($include_details) {
                    $formatted['aktif_count'] = (int)$stat['aktif_count'];
                    $formatted['nonaktif_count'] = (int)$stat['nonaktif_count'];
                    $formatted['bagian_list'] = $stat['bagian_list'] ? explode(',', $stat['bagian_list']) : [];
                }
                
                return $formatted;
            }, $stats);
            
            // Calculate totals
            $totals = [
                'total_unsur' => count($formattedStats),
                'total_bagian' => array_sum(array_column($formattedStats, 'bagian_count')),
                'total_personil' => array_sum(array_column($formattedStats, 'personil_count'))
            ];
            
            if ($include_details) {
                $totals['total_aktif'] = array_sum(array_column($formattedStats, 'aktif_count'));
                $totals['total_nonaktif'] = array_sum(array_column($formattedStats, 'nonaktif_count'));
            }
            
            echo json_encode(APIResponse::success([
                'stats' => $formattedStats,
                'totals' => $totals
            ], 'Unsur statistics retrieved successfully'));
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
