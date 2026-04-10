<?php
/**
 * Personil CRUD API - Create, Read, Update, Delete operations
 * Standardized Version
 */

// Set headers first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Initialize session
SessionManager::start();

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized - Please login to access this resource',
        'timestamp' => date('c')
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed',
        'timestamp' => date('c')
    ]);
    exit;
}

// Get action
$action = $_POST['action'] ?? '';
$valid_actions = ['get_personil', 'create_personil', 'update_personil', 'delete_personil', 'get_dropdown_data', 'toggle_status'];

if (!in_array($action, $valid_actions)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid action',
        'timestamp' => date('c')
    ]);
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
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('ID personil tidak valid');
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    pg.nama_pangkat,
                    pg.singkatan,
                    j.nama_jabatan,
                    b.nama_bagian,
                    u.nama_unsur,
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
                echo json_encode(['success' => true, 'data' => $personil]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Personil not found']);
            }
            break;
            
        case 'create_personil':
            $nama   = trim(strip_tags($_POST['nama'] ?? ''));
            $nrp    = trim(preg_replace('/[^0-9]/', '', $_POST['nrp'] ?? ''));
            $jk     = in_array($_POST['JK'] ?? 'L', ['L', 'P']) ? $_POST['JK'] : 'L';
            $status = in_array($_POST['status_ket'] ?? 'aktif', ['aktif', 'nonaktif', 'BKO', 'cuti', 'sakit']) ? $_POST['status_ket'] : 'aktif';
            $tgl_lahir = !empty($_POST['tanggal_lahir']) && strtotime($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;

            if (empty($nama)) throw new Exception('Nama personil wajib diisi');
            if (strlen($nama) > 255) throw new Exception('Nama terlalu panjang (maks 255 karakter)');
            if (empty($nrp)) throw new Exception('NRP wajib diisi');

            // Check NRP duplicate
            $chkStmt = $pdo->prepare("SELECT id FROM personil WHERE nrp = ? AND is_deleted = FALSE");
            $chkStmt->execute([$nrp]);
            if ($chkStmt->fetch()) throw new Exception('NRP sudah terdaftar: ' . $nrp);

            $stmt = $pdo->prepare("
                INSERT INTO personil 
                (nama, nrp, id_pangkat, id_jabatan, id_bagian, id_unsur, id_jenis_pegawai, JK, status_ket, tanggal_lahir, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $nama, $nrp,
                filter_var($_POST['id_pangkat'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_jabatan'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_bagian'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_jenis_pegawai'] ?? null, FILTER_VALIDATE_INT) ?: null,
                $jk, $status, $tgl_lahir
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Personil berhasil ditambahkan',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_personil':
            $id     = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $nama   = trim(strip_tags($_POST['nama'] ?? ''));
            $nrp    = trim(preg_replace('/[^0-9]/', '', $_POST['nrp'] ?? ''));
            $jk     = in_array($_POST['JK'] ?? 'L', ['L', 'P']) ? $_POST['JK'] : 'L';
            $status = in_array($_POST['status_ket'] ?? 'aktif', ['aktif', 'nonaktif', 'BKO', 'cuti', 'sakit']) ? $_POST['status_ket'] : 'aktif';
            $tgl_lahir = !empty($_POST['tanggal_lahir']) && strtotime($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;

            if (!$id) throw new Exception('ID personil tidak valid');
            if (empty($nama)) throw new Exception('Nama personil wajib diisi');
            if (strlen($nama) > 255) throw new Exception('Nama terlalu panjang (maks 255 karakter)');
            if (empty($nrp)) throw new Exception('NRP wajib diisi');

            // Check NRP duplicate (excluding self)
            $chkStmt = $pdo->prepare("SELECT id FROM personil WHERE nrp = ? AND is_deleted = FALSE AND id != ?");
            $chkStmt->execute([$nrp, $id]);
            if ($chkStmt->fetch()) throw new Exception('NRP sudah digunakan oleh personil lain: ' . $nrp);

            $stmt = $pdo->prepare("
                UPDATE personil SET
                    nama = ?, nrp = ?,
                    id_pangkat = ?, id_jabatan = ?,
                    id_bagian = ?, id_unsur = ?,
                    id_jenis_pegawai = ?, JK = ?,
                    status_ket = ?, tanggal_lahir = ?,
                    updated_at = NOW()
                WHERE id = ? AND is_deleted = FALSE
            ");
            $stmt->execute([
                $nama, $nrp,
                filter_var($_POST['id_pangkat'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_jabatan'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_bagian'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_unsur'] ?? null, FILTER_VALIDATE_INT) ?: null,
                filter_var($_POST['id_jenis_pegawai'] ?? null, FILTER_VALIDATE_INT) ?: null,
                $jk, $status, $tgl_lahir, $id
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Personil berhasil diperbarui',
                'rows_affected' => $stmt->rowCount()
            ]);
            break;
            
        case 'delete_personil':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $alasan = trim(strip_tags($_POST['alasan'] ?? ''));
            if (!$id) throw new Exception('ID personil tidak valid');
            
            // Alasan is required for deletion
            if (empty($alasan)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Alasan wajib diisi saat menghapus personil (contoh: Pensiun, Pindah, Meninggal, Dipecat)'
                ]);
                exit;
            }
            
            // Soft delete with alasan
            $stmt = $pdo->prepare("UPDATE personil SET is_deleted = TRUE, status_ket = 'nonaktif', alasan_status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$alasan, $id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Personil berhasil dihapus',
                'alasan' => $alasan,
                'rows_affected' => $stmt->rowCount()
            ]);
            break;
            
        case 'toggle_status':
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('ID personil tidak valid');
            $current_status = in_array($_POST['current_status'] ?? '', ['aktif', 'nonaktif']) ? $_POST['current_status'] : 'aktif';
            $new_status = ($current_status === 'aktif') ? 'nonaktif' : 'aktif';
            $alasan = trim(strip_tags($_POST['alasan'] ?? ''));
            
            // If changing to nonaktif, alasan is required
            if ($new_status === 'nonaktif' && empty($alasan)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Alasan wajib diisi saat menonaktifkan personil'
                ]);
                exit;
            }
            
            // Update query - include alasan only when going nonaktif
            if ($new_status === 'nonaktif') {
                $stmt = $pdo->prepare("UPDATE personil SET status_ket = ?, alasan_status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $alasan, $id]);
            } else {
                // Clear alasan when reactivating
                $stmt = $pdo->prepare("UPDATE personil SET status_ket = ?, alasan_status = NULL, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Status berhasil diubah menjadi ' . $new_status,
                'data' => [
                    'new_status' => $new_status,
                    'alasan' => $alasan,
                    'rows_affected' => $stmt->rowCount()
                ],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'get_dropdown_data':
            // Get data for dropdowns — no is_active filter on bagian/unsur (column may not exist)
            $pangkat = $pdo->query("SELECT id, nama_pangkat, singkatan FROM pangkat ORDER BY level_pangkat")->fetchAll();
            $jabatan = $pdo->query("SELECT id, nama_jabatan, id_unsur FROM jabatan ORDER BY nama_jabatan")->fetchAll();
            $bagian  = $pdo->query("SELECT id, nama_bagian, id_unsur FROM bagian ORDER BY nama_bagian")->fetchAll();
            $unsur   = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur ORDER BY urutan")->fetchAll();
            $jenis_pegawai = $pdo->query("SELECT id, kategori FROM master_jenis_pegawai ORDER BY kategori")->fetchAll();
            
            echo json_encode([
                'success' => true,
                'message' => 'Dropdown data retrieved successfully',
                'data' => [
                    'pangkat' => $pangkat,
                    'jabatan' => $jabatan,
                    'bagian' => $bagian,
                    'unsur' => $unsur,
                    'jenis_pegawai' => $jenis_pegawai
                ],
                'timestamp' => date('c')
            ]);
            break;
    }
    
} catch(Exception $e) {
    header('Content-Type: application/json; charset=UTF-8');
    if (ENVIRONMENT === 'development') {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'timestamp' => date('c')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process request',
            'timestamp' => date('c')
        ]);
    }
}
