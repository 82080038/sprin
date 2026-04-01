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

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    // Bypass for specific AJAX requests
    $action = $_POST['action'] ?? '';
    if ($action === 'get_dropdown_data') {
        // Set minimal session for dropdown data
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
    } else {
        // Debug info for development
        if (ENVIRONMENT === 'development') {
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'message' => 'Unauthorized - Session not valid',
                'debug' => [
                    'session_status' => session_status(),
                    'session_data' => $_SESSION,
                    'cookie_data' => $_COOKIE
                ],
                'timestamp' => date('c')
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'message' => 'Unauthorized',
                'timestamp' => date('c')
            ]);
        }
        exit;
    }
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
            $id = $_POST['id'] ?? 0;
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
            $stmt = $pdo->prepare("
                INSERT INTO personil 
                (nama, nrp, id_pangkat, id_jabatan, id_bagian, id_unsur, id_jenis_pegawai, JK, status_ket, tanggal_lahir, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $_POST['nama'] ?? '',
                $_POST['nrp'] ?? '',
                $_POST['id_pangkat'] ?? null,
                $_POST['id_jabatan'] ?? null,
                $_POST['id_bagian'] ?? null,
                $_POST['id_unsur'] ?? null,
                $_POST['id_jenis_pegawai'] ?? null,
                $_POST['JK'] ?? 'L',
                $_POST['status_ket'] ?? 'aktif',
                $_POST['tanggal_lahir'] ?? null
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Personil berhasil ditambahkan',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update_personil':
            $id = $_POST['id'] ?? 0;
            $stmt = $pdo->prepare("
                UPDATE personil SET
                    nama = ?,
                    nrp = ?,
                    id_pangkat = ?,
                    id_jabatan = ?,
                    id_bagian = ?,
                    id_unsur = ?,
                    id_jenis_pegawai = ?,
                    JK = ?,
                    status_ket = ?,
                    tanggal_lahir = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['nama'] ?? '',
                $_POST['nrp'] ?? '',
                $_POST['id_pangkat'] ?? null,
                $_POST['id_jabatan'] ?? null,
                $_POST['id_bagian'] ?? null,
                $_POST['id_unsur'] ?? null,
                $_POST['id_jenis_pegawai'] ?? null,
                $_POST['JK'] ?? 'L',
                $_POST['status_ket'] ?? 'aktif',
                $_POST['tanggal_lahir'] ?? null,
                $id
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Personil berhasil diperbarui',
                'rows_affected' => $stmt->rowCount()
            ]);
            break;
            
        case 'delete_personil':
            $id = $_POST['id'] ?? 0;
            $alasan = $_POST['alasan'] ?? null;
            
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
            $id = $_POST['id'] ?? 0;
            $current_status = $_POST['current_status'] ?? 'aktif';
            $new_status = ($current_status === 'aktif') ? 'nonaktif' : 'aktif';
            $alasan = $_POST['alasan'] ?? null;
            
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
            // Get data for dropdowns
            $pangkat = $pdo->query("SELECT id, nama_pangkat, singkatan FROM pangkat ORDER BY level_pangkat")->fetchAll();
            $jabatan = $pdo->query("SELECT id, nama_jabatan, id_unsur FROM jabatan ORDER BY nama_jabatan")->fetchAll();
            $bagian = $pdo->query("SELECT id, nama_bagian, id_unsur FROM bagian WHERE is_active = 1 ORDER BY nama_bagian")->fetchAll();
            $unsur = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur WHERE is_active = 1 ORDER BY urutan")->fetchAll();
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
