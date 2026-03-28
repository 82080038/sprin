<?php
/**
 * Search Personil API - Updated for New Database Structure
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Include calendar config
require_once __DIR__ . '/../core/calendar_config.php';

try {
    // Database connection with socket
    $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Get search parameters
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $unsur_filter = isset($_GET['unsur']) ? $_GET['unsur'] : null;
    $bagian_filter = isset($_GET['bagian']) ? $_GET['bagian'] : null;
    $kepegawaian_filter = isset($_GET['kepegawaian']) ? $_GET['kepegawaian'] : null;
    $jk_filter = isset($_GET['jk']) ? $_GET['jk'] : null;
    $gelar_filter = isset($_GET['gelar']) ? $_GET['gelar'] : null;
    
    if (empty($q)) {
        throw new Exception("Search query parameter 'q' is required");
    }
    
    // Build WHERE clause
    $where_conditions = ["p.is_deleted = FALSE", "p.is_active = TRUE"];
    $params = [];
    
    // Search across multiple fields - updated with new structure
    $search_condition = "(" . implode(" OR ", [
        "p.nama LIKE ?",
        "p.nrp LIKE ?", 
        "p.nip LIKE ?",
        "p.no_telepon LIKE ?",
        "p.email LIKE ?",
        "p.gelar_pendidikan LIKE ?",
        "j.nama_jabatan LIKE ?",
        "b.nama_bagian LIKE ?",
        "pg.nama_pangkat LIKE ?",
        "pg.singkatan LIKE ?",
        "mjp.nama_jenis LIKE ?"
    ]) . ")";
    
    $where_conditions[] = $search_condition;
    
    // Add search parameters (11 fields now)
    for ($i = 0; $i < 11; $i++) {
        $params[] = "%$q%";
    }
    
    // Add filters
    if ($unsur_filter) {
        $where_conditions[] = "u.kode_unsur = ?";
        $params[] = $unsur_filter;
    }
    
    if ($bagian_filter) {
        $where_conditions[] = "b.nama_bagian = ?";
        $params[] = $bagian_filter;
    }
    
    // Updated kepegawaian filter to use master_jenis_pegawai
    if ($kepegawaian_filter) {
        $where_conditions[] = "mjp.kode_jenis = ?";
        $params[] = $kepegawaian_filter;
    }
    
    // New filters
    if ($jk_filter) {
        $where_conditions[] = "p.JK = ?";
        $params[] = $jk_filter;
    }
    
    if ($gelar_filter) {
        $where_conditions[] = "p.gelar_pendidikan LIKE ?";
        $params[] = "%$gelar_filter%";
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Main search query - updated structure
    $sql = "
        SELECT 
            p.id,
            p.nama,
            p.gelar_pendidikan,
            p.nrp,
            p.nip,
            p.JK,
            p.tanggal_lahir,
            p.tempat_lahir,
            p.status_ket,
            p.status_nikah,
            p.no_telepon,
            p.email,
            p.alamat,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            j.nama_jabatan,
            b.nama_bagian,
            u.nama_unsur,
            u.kode_unsur,
            mjp.nama_jenis as status_kepegawaian,
            mjp.kode_jenis as kode_kepegawaian,
            mjp.kategori as kategori_kepegawaian
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        $where_clause
        ORDER BY 
            CASE 
                WHEN p.nama LIKE ? THEN 1
                WHEN p.nama LIKE ? THEN 2
                ELSE 3
            END,
            p.nama
        LIMIT ?
    ";
    
    // Add ordering parameters
    $params[] = "$q%";
    $params[] = "%$q%";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Build response data
    $response_data = [];
    foreach ($results as $personil) {
        // Build full name with gelar (updated structure)
        $nama_lengkap = trim($personil['nama'] . ($personil['gelar_pendidikan'] ? ', ' . $personil['gelar_pendidikan'] : ''));
        
        $response_data[] = [
            'id' => (int)$personil['id'],
            'nama' => $personil['nama'],
            'nama_lengkap' => $nama_lengkap,
            'gelar_pendidikan' => $personil['gelar_pendidikan'],
            'nrp' => $personil['nrp'],
            'nip' => $personil['nip'],
            'JK' => $personil['JK'],
            'tanggal_lahir' => $personil['tanggal_lahir'],
            'tempat_lahir' => $personil['tempat_lahir'],
            'status_ket' => $personil['status_ket'],
            'status_nikah' => $personil['status_nikah'],
            'no_telepon' => $personil['no_telepon'],
            'email' => $personil['email'],
            'alamat' => $personil['alamat'],
            
            // Relational data
            'pangkat' => [
                'nama_pangkat' => $personil['nama_pangkat'],
                'singkatan' => $personil['pangkat_singkatan']
            ],
            'jabatan' => [
                'nama_jabatan' => $personil['nama_jabatan']
            ],
            'bagian' => [
                'nama_bagian' => $personil['nama_bagian']
            ],
            'unsur' => [
                'nama_unsur' => $personil['nama_unsur'],
                'kode_unsur' => $personil['kode_unsur']
            ],
            'status_kepegawaian' => [
                'nama_jenis' => $personil['status_kepegawaian'],
                'kode_jenis' => $personil['kode_kepegawaian'],
                'kategori' => $personil['kategori_kepegawaian']
            ]
        ];
    }
    
    // Get search statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total_results,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI_DIK' THEN 1 END) as polri_dik_count,
            COUNT(CASE WHEN p.JK = 'L' THEN 1 END) as l_count,
            COUNT(CASE WHEN p.JK = 'P' THEN 1 END) as p_count
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        " . $where_clause;
    
    // Remove LIMIT and ORDER BY for stats
    $stats_params = array_slice($params, 0, -3); // Remove order and limit params
    
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'timestamp' => date('c'),
        'query' => $q,
        'filters' => [
            'unsur' => $unsur_filter,
            'bagian' => $bagian_filter,
            'kepegawaian' => $kepegawaian_filter,
            'jk' => $jk_filter,
            'gelar' => $gelar_filter
        ],
        'data' => [
            'results' => $response_data,
            'count' => count($response_data),
            'statistics' => [
                'total_results' => (int)$stats['total_results'],
                'by_jenis_pegawai' => [
                    'POLRI' => (int)$stats['polri_count'],
                    'ASN' => (int)$stats['asn_count'],
                    'P3K' => (int)$stats['p3k_count'],
                    'POLRI_DIK' => (int)$stats['polri_dik_count']
                ],
                'by_jk' => [
                    'L' => (int)$stats['l_count'],
                    'P' => (int)$stats['p_count']
                ]
            ]
        ],
        'message' => "Search completed successfully"
    ], JSON_PRETTY_PRINT);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'timestamp' => date('c'),
        'error' => [
            'message' => $e->getMessage(),
            'code' => 500,
            'hint' => 'Check search parameters and database connection'
        ]
    ], JSON_PRETTY_PRINT);
}
?>
