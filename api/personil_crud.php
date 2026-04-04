<?php
declare(strict_types=1);
/**
 * Personil CRUD API - Create, Read, Update, Delete operations
 * Standardized Version with proper validation and error handling
 */

// Set headers first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';
require_once __DIR__ . '/APIResponse.php';

// Initialize session
SessionManager::start();

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    // Bypass for specific AJAX requests
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    if ($action === 'get_dropdown_data') {
        // Set minimal session for dropdown data
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
    } else {
        echo json_encode(APIResponse::error('Unauthorized - Session not valid', 401));
        exit;
    }
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(APIResponse::error('Method not allowed', 405));
    exit;
}

// Get action
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
$valid_actions = ['get_personil', 'create_personil', 'update_personil', 'delete_personil', 'get_dropdown_data', 'toggle_status'];

if (!in_array($action, $valid_actions)) {
    echo json_encode(APIResponse::error('Invalid action', 400));
    exit;
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    switch ($action) {
        case 'get_personil':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid personil ID', 400));
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    pg.nama_pangkat,
                    pg.singkatan,
                    pg.level_pangkat,
                    j.nama_jabatan,
                    b.nama_bagian,
                    u.nama_unsur,
                    u.kode_unsur,
                    u.urutan as unsur_urutan,
                    mjp.kategori as status_kepegawaian
                FROM personil p
                LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON p.id_unsur = u.id
                LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
                WHERE p.id = ? AND p.is_deleted = FALSE
            ");
            $stmt->execute([$id]);
            $personil = $stmt->fetch();
            
            if ($personil) {
                // Format response data
                $formattedData = [
                    'id' => (int)$personil['id'],
                    'nama' => $personil['nama'],
                    'nrp' => $personil['nrp'],
                    'nip' => $personil['nip'] ?? '',
                    'jk' => $personil['JK'],
                    'status_ket' => $personil['status_ket'],
                    'alasan_status' => $personil['alasan_status'] ?? '',
                    'tanggal_lahir' => $personil['tanggal_lahir'],
                    'tempat_lahir' => $personil['tempat_lahir'] ?? '',
                    'tanggal_masuk' => $personil['tanggal_masuk'] ?? '',
                    'tanggal_pensiun' => $personil['tanggal_pensiun'] ?? '',
                    'no_karpeg' => $personil['no_karpeg'] ?? '',
                    'pangkat' => [
                        'id' => (int)($personil['id_pangkat'] ?? 0),
                        'nama' => $personil['nama_pangkat'] ?? '',
                        'singkatan' => $personil['singkatan'] ?? '',
                        'level' => (int)($personil['level_pangkat'] ?? 0)
                    ],
                    'jabatan' => [
                        'id' => (int)($personil['id_jabatan'] ?? 0),
                        'nama' => $personil['nama_jabatan'] ?? ''
                    ],
                    'bagian' => [
                        'id' => (int)($personil['id_bagian'] ?? 0),
                        'nama' => $personil['nama_bagian'] ?? ''
                    ],
                    'unsur' => [
                        'id' => (int)($personil['id_unsur'] ?? 0),
                        'nama' => $personil['nama_unsur'] ?? '',
                        'kode' => $personil['kode_unsur'] ?? '',
                        'urutan' => (int)($personil['unsur_urutan'] ?? 0)
                    ],
                    'jenis_pegawai' => [
                        'id' => (int)($personil['id_jenis_pegawai'] ?? 0),
                        'kategori' => $personil['status_kepegawaian'] ?? ''
                    ],
                    'created_at' => $personil['created_at'],
                    'updated_at' => $personil['updated_at']
                ];
                
                echo json_encode(APIResponse::success($formattedData, 'Personil data retrieved'));
            } else {
                echo json_encode(APIResponse::error('Personil not found', 404));
            }
            break;
            
        case 'create_personil':
            // Validate required fields
            $required_fields = ['nama', 'nrp'];
            $validation = APIResponse::validateRequired($_POST, $required_fields);
            if ($validation) {
                echo json_encode($validation);
                exit;
            }
            
            // Check for duplicate NRP
            $nrp = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nrp', FILTER_SANITIZE_STRING));
            $checkStmt = $pdo->prepare("SELECT id FROM personil WHERE nrp = ? AND is_deleted = FALSE");
            $checkStmt->execute([$nrp]);
            if ($checkStmt->fetch()) {
                echo json_encode(APIResponse::error('NRP already exists', 400));
                exit;
            }
            
            // Validate foreign keys
            $id_pangkat = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? 0);
            $id_jabatan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? 0);
            $id_bagian = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? 0);
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            $id_jenis_pegawai = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id_pangkat > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM pangkat WHERE id = ?");
                $checkStmt->execute([$id_pangkat]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid pangkat ID', 400));
                    exit;
                }
            }
            
            if ($id_jabatan > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM jabatan WHERE id = ?");
                $checkStmt->execute([$id_jabatan]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid jabatan ID', 400));
                    exit;
                }
            }
            
            if ($id_bagian > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE id = ?");
                $checkStmt->execute([$id_bagian]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid bagian ID', 400));
                    exit;
                }
            }
            
            if ($id_unsur > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ?");
                $checkStmt->execute([$id_unsur]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                    exit;
                }
            }
            
            if ($id_jenis_pegawai > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM master_jenis_pegawai WHERE id = ?");
                $checkStmt->execute([$id_jenis_pegawai]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid jenis pegawai ID', 400));
                    exit;
                }
            }
            
            // Insert personil
            $stmt = $pdo->prepare("
                INSERT INTO personil 
                (nama, nrp, nip, id_pangkat, id_jabatan, id_bagian, id_unsur, id_jenis_pegawai, 
                 JK, status_ket, alasan_status, tanggal_lahir, tempat_lahir, tanggal_masuk, 
                 tanggal_pensiun, no_karpeg, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $result = $stmt->execute([
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? ''),
                $nrp,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nip', FILTER_SANITIZE_STRING) ?? ''),
                $id_pangkat ?: null,
                $id_jabatan ?: null,
                $id_bagian ?: null,
                $id_unsur ?: null,
                $id_jenis_pegawai ?: null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'JK', FILTER_SANITIZE_STRING) ?? 'L',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_ket', FILTER_SANITIZE_STRING) ?? 'aktif',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_status', FILTER_SANITIZE_STRING) ?? null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_lahir', FILTER_SANITIZE_STRING) ?? null,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tempat_lahir', FILTER_SANITIZE_STRING) ?? ''),
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_masuk', FILTER_SANITIZE_STRING) ?? null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_pensiun', FILTER_SANITIZE_STRING) ?? null,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_karpeg', FILTER_SANITIZE_STRING) ?? '')
            ]);
            
            if ($result) {
                $newId = $pdo->lastInsertId();
                echo json_encode(APIResponse::success([
                    'id' => (int)$newId,
                    'nrp' => $nrp,
                    'nama' => trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? '')
                ], 'Personil berhasil ditambahkan'));
            } else {
                echo json_encode(APIResponse::error('Failed to create personil', 500));
            }
            break;
            
        case 'update_personil':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid personil ID', 400));
                exit;
            }
            
            // Check if personil exists
            $checkStmt = $pdo->prepare("SELECT id FROM personil WHERE id = ? AND is_deleted = FALSE");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                echo json_encode(APIResponse::error('Personil not found', 404));
                exit;
            }
            
            // Check for duplicate NRP (excluding current record)
            $nrp = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nrp', FILTER_SANITIZE_STRING) ?? '');
            if (!empty($nrp)) {
                $checkStmt = $pdo->prepare("SELECT id FROM personil WHERE nrp = ? AND id != ? AND is_deleted = FALSE");
                $checkStmt->execute([$nrp, $id]);
                if ($checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('NRP already exists', 400));
                    exit;
                }
            }
            
            // Validate foreign keys
            $id_pangkat = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? 0);
            $id_jabatan = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? 0);
            $id_bagian = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? 0);
            $id_unsur = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? 0);
            $id_jenis_pegawai = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? 0);
            
            if ($id_pangkat > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM pangkat WHERE id = ?");
                $checkStmt->execute([$id_pangkat]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid pangkat ID', 400));
                    exit;
                }
            }
            
            if ($id_jabatan > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM jabatan WHERE id = ?");
                $checkStmt->execute([$id_jabatan]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid jabatan ID', 400));
                    exit;
                }
            }
            
            if ($id_bagian > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM bagian WHERE id = ?");
                $checkStmt->execute([$id_bagian]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid bagian ID', 400));
                    exit;
                }
            }
            
            if ($id_unsur > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM unsur WHERE id = ?");
                $checkStmt->execute([$id_unsur]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid unsur ID', 400));
                    exit;
                }
            }
            
            if ($id_jenis_pegawai > 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM master_jenis_pegawai WHERE id = ?");
                $checkStmt->execute([$id_jenis_pegawai]);
                if (!$checkStmt->fetch()) {
                    echo json_encode(APIResponse::error('Invalid jenis pegawai ID', 400));
                    exit;
                }
            }
            
            // Update personil
            $stmt = $pdo->prepare("
                UPDATE personil SET
                    nama = ?,
                    nrp = ?,
                    nip = ?,
                    id_pangkat = ?,
                    id_jabatan = ?,
                    id_bagian = ?,
                    id_unsur = ?,
                    id_jenis_pegawai = ?,
                    JK = ?,
                    status_ket = ?,
                    alasan_status = ?,
                    tanggal_lahir = ?,
                    tempat_lahir = ?,
                    tanggal_masuk = ?,
                    tanggal_pensiun = ?,
                    no_karpeg = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? ''),
                $nrp,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nip', FILTER_SANITIZE_STRING) ?? ''),
                $id_pangkat ?: null,
                $id_jabatan ?: null,
                $id_bagian ?: null,
                $id_unsur ?: null,
                $id_jenis_pegawai ?: null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'JK', FILTER_SANITIZE_STRING) ?? 'L',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_ket', FILTER_SANITIZE_STRING) ?? 'aktif',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_status', FILTER_SANITIZE_STRING) ?? null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_lahir', FILTER_SANITIZE_STRING) ?? null,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tempat_lahir', FILTER_SANITIZE_STRING) ?? ''),
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_masuk', FILTER_SANITIZE_STRING) ?? null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_pensiun', FILTER_SANITIZE_STRING) ?? null,
                trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_karpeg', FILTER_SANITIZE_STRING) ?? ''),
                $id
            ]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'rows_affected' => $stmt->rowCount()
                ], 'Personil berhasil diperbarui'));
            } else {
                echo json_encode(APIResponse::error('Failed to update personil', 500));
            }
            break;
            
        case 'delete_personil':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            $alasan = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '');
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid personil ID', 400));
                exit;
            }
            
            // Alasan is required for deletion
            if (empty($alasan)) {
                echo json_encode(APIResponse::error('Alasan wajib diisi saat menghapus personil (contoh: Pensiun, Pindah, Meninggal, Dipecat)', 400));
                exit;
            }
            
            // Check if personil exists
            $checkStmt = $pdo->prepare("SELECT id, nama FROM personil WHERE id = ? AND is_deleted = FALSE");
            $checkStmt->execute([$id]);
            $personil = $checkStmt->fetch();
            
            if (!$personil) {
                echo json_encode(APIResponse::error('Personil not found', 404));
                exit;
            }
            
            // Soft delete with alasan
            $stmt = $pdo->prepare("UPDATE personil SET is_deleted = TRUE, status_ket = 'nonaktif', alasan_status = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$alasan, $id]);
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama' => $personil['nama'],
                    'alasan' => $alasan,
                    'rows_affected' => $stmt->rowCount()
                ], 'Personil berhasil dihapus'));
            } else {
                echo json_encode(APIResponse::error('Failed to delete personil', 500));
            }
            break;
            
        case 'toggle_status':
            $id = (int)(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0);
            $current_status = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'current_status', FILTER_SANITIZE_STRING) ?? 'aktif';
            $new_status = ($current_status === 'aktif') ? 'nonaktif' : 'aktif';
            $alasan = trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '');
            
            if ($id <= 0) {
                echo json_encode(APIResponse::error('Invalid personil ID', 400));
                exit;
            }
            
            // Check if personil exists
            $checkStmt = $pdo->prepare("SELECT id, nama, status_ket FROM personil WHERE id = ? AND is_deleted = FALSE");
            $checkStmt->execute([$id]);
            $personil = $checkStmt->fetch();
            
            if (!$personil) {
                echo json_encode(APIResponse::error('Personil not found', 404));
                exit;
            }
            
            // If changing to nonaktif, alasan is required
            if ($new_status === 'nonaktif' && empty($alasan)) {
                echo json_encode(APIResponse::error('Alasan wajib diisi saat menonaktifkan personil', 400));
                exit;
            }
            
            // Update query - include alasan only when going nonaktif
            if ($new_status === 'nonaktif') {
                $stmt = $pdo->prepare("UPDATE personil SET status_ket = ?, alasan_status = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$new_status, $alasan, $id]);
            } else {
                // Clear alasan when reactivating
                $stmt = $pdo->prepare("UPDATE personil SET status_ket = ?, alasan_status = NULL, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$new_status, $id]);
            }
            
            if ($result) {
                echo json_encode(APIResponse::success([
                    'id' => $id,
                    'nama' => $personil['nama'],
                    'old_status' => $personil['status_ket'],
                    'new_status' => $new_status,
                    'alasan' => $alasan,
                    'rows_affected' => $stmt->rowCount()
                ], 'Status berhasil diubah menjadi ' . $new_status));
            } else {
                echo json_encode(APIResponse::error('Failed to update status', 500));
            }
            break;
            
        case 'get_dropdown_data':
            // Get data for dropdowns with proper validation
            $pangkat = $pdo->query("SELECT id, nama_pangkat, singkatan, level_pangkat FROM pangkat ORDER BY level_pangkat, nama_pangkat")->fetchAll();
            $jabatan = $pdo->query("SELECT id, nama_jabatan, id_unsur FROM jabatan ORDER BY nama_jabatan")->fetchAll();
            $bagian = $pdo->query("SELECT id, nama_bagian, id_unsur FROM bagian WHERE is_active = 1 ORDER BY nama_bagian")->fetchAll();
            $unsur = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur WHERE is_active = 1 ORDER BY urutan, nama_unsur")->fetchAll();
            $jenis_pegawai = $pdo->query("SELECT id, kategori FROM master_jenis_pegawai ORDER BY kategori")->fetchAll();
            
            echo json_encode(APIResponse::success([
                'pangkat' => $pangkat,
                'jabatan' => $jabatan,
                'bagian' => $bagian,
                'unsur' => $unsur,
                'jenis_pegawai' => $jenis_pegawai
            ], 'Dropdown data retrieved successfully'));
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
