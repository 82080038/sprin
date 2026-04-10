<?php
/**
 * Personil Simple API - Standardized Version
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get limit parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
    $limit = min(1000, max(1, $limit));
    
    // Main query with new structure
    $sql = "
        SELECT 
            p.id,
            p.nama,
            p.gelar_pendidikan,
            p.nrp,
            p.status_ket,
            p.status_nikah,
            p.JK,
            p.tanggal_lahir,
            p.tempat_lahir,
            mjp.nama_jenis as status_kepegawaian,
            mjp.kode_jenis as kode_kepegawaian,
            mjp.kategori as kategori_kepegawaian,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            j.nama_jabatan,
            b.nama_bagian,
            u.nama_unsur,
            u.kode_unsur,
            p.created_at,
            p.updated_at
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ORDER BY 
                u.urutan, 
                b.nama_bagian,
                CASE WHEN pg.level_pangkat IS NULL THEN 999999 ELSE pg.level_pangkat END ASC,
                CASE 
                    WHEN p.nrp REGEXP '^[0-9]{8}' THEN 
                        CASE 
                            WHEN SUBSTRING(p.nrp, 1, 1) = '0' THEN CONCAT('20', SUBSTRING(p.nrp, 1, 4))
                            ELSE CONCAT('19', SUBSTRING(p.nrp, 1, 4))
                        END
                    WHEN p.nrp REGEXP '^[0-9]{9}' THEN CONCAT('19', SUBSTRING(p.nrp, 1, 6))
                    ELSE '99999999'
                END ASC,
                p.nama
        LIMIT $limit
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $personil = $stmt->fetchAll();
    
    // Enhance results
    $enhancedPersonil = [];
    foreach ($personil as $item) {
        // Build full name with gelar
        $nama_lengkap = trim($item['nama'] . ($item['gelar_pendidikan'] ? ', ' . $item['gelar_pendidikan'] : ''));
        
        $enhancedPersonil[] = [
            'id' => (int)$item['id'],
            'nama' => $item['nama'],
            'nama_lengkap' => $nama_lengkap,
            'gelar_pendidikan' => $item['gelar_pendidikan'],
            'nrp' => $item['nrp'],
            'JK' => $item['JK'],
            'tanggal_lahir' => $item['tanggal_lahir'],
            'tempat_lahir' => $item['tempat_lahir'],
            'status_ket' => $item['status_ket'],
            'status_nikah' => $item['status_nikah'],
            'status_kepegawaian' => $item['status_kepegawaian'],
            'kode_kepegawaian' => $item['kode_kepegawaian'],
            'kategori_kepegawaian' => $item['kategori_kepegawaian'],
            'nama_pangkat' => $item['nama_pangkat'],
            'pangkat_singkatan' => $item['pangkat_singkatan'],
            'nama_jabatan' => $item['nama_jabatan'],
            'nama_bagian' => $item['nama_bagian'],
            'nama_unsur' => $item['nama_unsur'],
            'kode_unsur' => $item['kode_unsur'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ];
    }
    
    // Get statistics
    $total_personil = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = FALSE")->fetch()['total'];
    
    $polri_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = FALSE AND mjp.kode_jenis = 'POLRI'
    ")->fetch()['total'];
    
    $asn_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = FALSE AND mjp.kode_jenis = 'ASN'
    ")->fetch()['total'];
    
    $p3k_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = FALSE AND mjp.kode_jenis = 'P3K'
    ")->fetch()['total'];
    
    $aktif_count = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = FALSE AND is_active = TRUE")->fetch()['total'];
    
    // Unsur statistics
    $unsur_stats_result = $pdo->query("
        SELECT 
            u.kode_unsur,
            u.nama_unsur,
            COUNT(*) as total_personil
        FROM personil p
        LEFT JOIN unsur u ON p.id_unsur = u.id
        WHERE p.is_deleted = FALSE AND u.kode_unsur IS NOT NULL
        GROUP BY u.kode_unsur, u.nama_unsur
        ORDER BY u.urutan
    ")->fetchAll();
    
    $unsur_stats = [];
    foreach ($unsur_stats_result as $row) {
        $unsur_stats[$row['kode_unsur']] = $row['total_personil'];
    }
    
    $response_data = [
        'personil' => $enhancedPersonil,
        'statistics' => [
            'total_personil' => (int)$total_personil,
            'polri_count' => (int)$polri_count,
            'asn_count' => (int)$asn_count,
            'p3k_count' => (int)$p3k_count,
            'aktif_count' => (int)$aktif_count,
            'unsur_distribution' => $unsur_stats
        ]
    ];
    
    // Send JSON response
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'message' => "Retrieved " . count($enhancedPersonil) . " personil records",
        'data' => $response_data,
        'timestamp' => date('c')
    ]);
    
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
            'message' => 'Failed to retrieve personil data',
            'timestamp' => date('c')
        ]);
    }
}