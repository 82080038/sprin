<?php
declare(strict_types=1);
/**
 * Fixed Personil Simple API - Consistent JSON Response
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponseStandardizer.php';

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get limit parameter
    $limit = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) : 1000;
    $limit = min(1000, max(1, $limit));
    
    // Get search parameter
    $search = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) ? trim(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) : '';
    
    // Build query
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
    ";
    
    $params = [];
    
    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " WHERE p.nama LIKE ? OR p.nrp LIKE ? OR p.gelar_pendidikan LIKE ?";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam, $searchParam];
    }
    
    $sql .= " ORDER BY p.nama ASC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enhance personil data
    $enhancedPersonil = [];
    foreach ($personil as $p) {
        $enhancedPersonil[] = [
            'id' => (int)$p['id'],
            'nama' => $p['nama'],
            'gelar_pendidikan' => $p['gelar_pendidikan'],
            'nrp' => $p['nrp'],
            'status_ket' => $p['status_ket'],
            'status_nikah' => $p['status_nikah'],
            'jk' => $p['JK'],
            'tanggal_lahir' => $p['tanggal_lahir'],
            'tempat_lahir' => $p['tempat_lahir'],
            'status_kepegawaian' => [
                'nama' => $p['status_kepegawaian'],
                'kode' => $p['kode_kepegawaian'],
                'kategori' => $p['kategori_kepegawaian']
            ],
            'pangkat' => [
                'nama' => $p['nama_pangkat'],
                'singkatan' => $p['pangkat_singkatan']
            ],
            'jabatan' => $p['nama_jabatan'],
            'bagian' => $p['nama_bagian'],
            'unsur' => [
                'nama' => $p['nama_unsur'],
                'kode' => $p['kode_unsur']
            ],
            'created_at' => $p['created_at'],
            'updated_at' => $p['updated_at']
        ];
    }
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN mjp.kategori = 'POLRI' THEN 1 ELSE 0 END) as polri_count,
            SUM(CASE WHEN mjp.kategori = 'ASN' THEN 1 ELSE 0 END) as asn_count,
            SUM(CASE WHEN mjp.kategori = 'P3K' THEN 1 ELSE 0 END) as p3k_count,
            SUM(CASE WHEN p.status_ket = 'AKTIF' THEN 1 ELSE 0 END) as aktif_count
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
    ";
    
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get unsur distribution
    $unsur_sql = "
        SELECT u.nama_unsur, u.kode_unsur, COUNT(p.id) as count
        FROM personil p
        LEFT JOIN unsur u ON p.id_unsur = u.id
        WHERE u.nama_unsur IS NOT NULL
        GROUP BY u.id, u.nama_unsur, u.kode_unsur
        ORDER BY count DESC
    ";
    
    $unsur_stmt = $pdo->prepare($unsur_sql);
    $unsur_stmt->execute();
    $unsur_stats = $unsur_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response_data = [
        'personil' => $enhancedPersonil,
        'statistics' => [
            'total_personil' => (int)$stats['total'],
            'polri_count' => (int)$stats['polri_count'],
            'asn_count' => (int)$stats['asn_count'],
            'p3k_count' => (int)$stats['p3k_count'],
            'aktif_count' => (int)$stats['aktif_count'],
            'unsur_distribution' => $unsur_stats
        ],
        'search_info' => [
            'search_term' => $search,
            'limit_applied' => $limit,
            'results_count' => count($enhancedPersonil)
        ]
    ];
    
    // Send standardized JSON response
    APIResponseStandardizer::success($response_data, "Retrieved " . count($enhancedPersonil) . " personil records");
    
} catch(Exception $e) {
    if (ENVIRONMENT === 'development') {
        APIResponseStandardizer::error('Database error: ' . $e->getMessage(), 500, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        APIResponseStandardizer::error('Failed to retrieve personil data', 500);
    }
}
?>
