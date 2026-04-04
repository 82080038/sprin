<?php
declare(strict_types=1);
/**
 * Personil Simple API - Standardized Version
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get parameters
    $limit = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) : 1000;
    $limit = min(1000, max(1, $limit));
    $jabatan_id = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jabatan_id', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jabatan_id', FILTER_SANITIZE_STRING) : null;
    
    // Build WHERE clause
    $where_clause = "WHERE p.is_deleted = 0 AND p.is_active = 1";
    if ($jabatan_id) {
        $where_clause .= " AND p.id_jabatan = :jabatan_id";
    }
    
    // Main query with correct structure
    $sql = "
        SELECT 
            p.id,
            p.nama,
            p.gelar_depan,
            p.gelar_belakang,
            p.nrp,
            p.status_ket,
            p.status_nikah,
            p.JK,
            p.tanggal_lahir,
            p.tempat_lahir,
            p.id_jabatan,
            mjp.nama_jenis as status_kepegawaian,
            mjp.kode_jenis as kode_kepegawaian,
            mjp.kategori as kategori_kepegawaian,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            j.nama_jabatan,
            b.nama_bagian,
            u.nama_unsur,
            u.kode_unsur,
            p.is_active,
            p.created_at,
            p.updated_at
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        $where_clause
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
    
    // Bind parameters
    if ($jabatan_id) {
        $stmt->bindParam(':jabatan_id', $jabatan_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $personil = $stmt->fetchAll();
    
    // Enhance results
    $enhancedPersonil = [];
    foreach ($personil as $item) {
        // Build full name with gelar - only use gelar if not already in nama
        $nama = $item['nama'];
        $gelar_depan = $item['gelar_depan'];
        $gelar_belakang = $item['gelar_belakang'];
        
        // Check if gelar already exists in nama
        $nama_lengkap = $nama;
        if ($gelar_depan && !str_contains($nama, $gelar_depan)) {
            $nama_lengkap = trim($gelar_depan . ' ' . $nama_lengkap);
        }
        if ($gelar_belakang && !str_contains($nama, $gelar_belakang)) {
            $nama_lengkap = trim($nama_lengkap . ', ' . $gelar_belakang);
        }
        
        $enhancedPersonil[] = [
            'id' => (int)$item['id'],
            'nama' => $item['nama'],
            'nama_lengkap' => $nama_lengkap,
            'gelar_depan' => $item['gelar_depan'],
            'gelar_belakang' => $item['gelar_belakang'],
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
            'id_jabatan' => $item['id_jabatan'] ? (int)$item['id_jabatan'] : null,
            'nama_jabatan' => $item['nama_jabatan'],
            'nama_bagian' => $item['nama_bagian'],
            'nama_unsur' => $item['nama_unsur'],
            'is_active' => (bool)$item['is_active'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ];
    }
    
    // Get statistics
    $total_personil = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = 0")->fetch()['total'];
    
    $polri_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = 0 AND mjp.kode_jenis = 'POLRI'
    ")->fetch()['total'];
    
    $ppns_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = 0 AND mjp.kode_jenis = 'PPNS'
    ")->fetch()['total'];
    
    $asn_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = 0 AND mjp.kode_jenis = 'ASN'
    ")->fetch()['total'];
    
    $p3k_count = $pdo->query("
        SELECT COUNT(*) as total FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = 0 AND mjp.kode_jenis = 'P3K'
    ")->fetch()['total'];
    
    $aktif_count = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = 0 AND is_active = 1")->fetch()['total'];
    
    // Unsur statistics
    $unsur_stats_result = $pdo->query("
        SELECT 
            u.kode_unsur,
            u.nama_unsur,
            COUNT(*) as total_personil
        FROM personil p
        LEFT JOIN unsur u ON p.id_unsur = u.id
        WHERE p.is_deleted = 0 AND u.kode_unsur IS NOT NULL
        GROUP BY u.kode_unsur, u.nama_unsur
        ORDER BY u.kode_unsur
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
?>