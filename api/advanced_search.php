<?php
declare(strict_types=1);
/**
 * Advanced Search API - Updated for New Database Structure
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
    
    // Get advanced search parameters
    $q = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'q', FILTER_SANITIZE_STRING)) ? trim(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'q', FILTER_SANITIZE_STRING)) : '';
    $unsur = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) : null;
    $bagian = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian', FILTER_SANITIZE_STRING) : null;
    $kepegawaian = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kepegawaian', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kepegawaian', FILTER_SANITIZE_STRING) : null;
    $pangkat = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pangkat', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pangkat', FILTER_SANITIZE_STRING) : null;
    $status = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING) : null;
    $jenis_kelamin = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jk', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jk', FILTER_SANITIZE_STRING) : null; // Updated to use JK
    $gelar = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar', FILTER_SANITIZE_STRING) : null; // New filter
    $status_nikah = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_nikah', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_nikah', FILTER_SANITIZE_STRING) : null;
    $limit = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) : 50;
    $page = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING) : 1;
    $sort_by = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'sort_by', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'sort_by', FILTER_SANITIZE_STRING) : 'nama';
    $sort_order = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'sort_order', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'sort_order', FILTER_SANITIZE_STRING) : 'asc';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = min(100, max(5, $limit));
    $offset = ($page - 1) * $limit;
    
    // Updated sort fields with new structure
    $allowed_sort_fields = ['nama', 'nrp', 'nip', 'JK', 'tanggal_lahir', 'gelar_pendidikan', 'pangkat', 'jabatan', 'bagian', 'unsur', 'created_at'];
    $sort_by = in_array($sort_by, $allowed_sort_fields) ? $sort_by : 'nama';
    $sort_order = strtolower($sort_order) === 'desc' ? 'DESC' : 'ASC';
    
    if (empty($q) && !$unsur && !$bagian && !$kepegawaian && !$pangkat && !$status && !$jenis_kelamin && !$gelar && !$status_nikah) {
        // Return empty results instead of error for standard format compliance
        echo json_encode([
            'success' => true,
            'message' => 'No search criteria provided. Please provide at least one filter.',
            'data' => [
                'results' => [],
                'count' => 0
            ],
            'query' => [
                'q' => '',
                'filters' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => $limit,
                    'total_results' => 0,
                    'total_pages' => 0,
                    'has_next' => false,
                    'has_prev' => false
                ]
            ],
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    // Build WHERE clause
    $where_conditions = ["p.is_deleted = FALSE", "p.is_active = TRUE"];
    $params = [];
    
    // Text search - updated with new fields
    if (!empty($q)) {
        $search_condition = "(" . implode(" OR ", [
            "p.nama LIKE ?",
            "p.nrp LIKE ?",
            "p.nip LIKE ?",
            "p.gelar_pendidikan LIKE ?",
            "j.nama_jabatan LIKE ?",
            "b.nama_bagian LIKE ?",
            "pg.nama_pangkat LIKE ?",
            "pg.singkatan LIKE ?",
            "mjp.nama_jenis LIKE ?"
        ]) . ")";
        
        $where_conditions[] = $search_condition;
        for ($i = 0; $i < 9; $i++) {
            $params[] = "%$q%";
        }
    }
    
    // Filter by unsur
    if ($unsur) {
        $where_conditions[] = "u.kode_unsur = ?";
        $params[] = $unsur;
    }
    
    // Filter by bagian
    if ($bagian) {
        $where_conditions[] = "b.nama_bagian = ?";
        $params[] = $bagian;
    }
    
    // Filter by kepegawaian - updated to use master_jenis_pegawai
    if ($kepegawaian) {
        $where_conditions[] = "mjp.kode_jenis = ?";
        $params[] = $kepegawaian;
    }
    
    // Filter by pangkat
    if ($pangkat) {
        $where_conditions[] = "pg.singkatan = ?";
        $params[] = $pangkat;
    }
    
    // Filter by status
    if ($status) {
        $where_conditions[] = "p.status_ket = ?";
        $params[] = $status;
    }
    
    // Filter by jenis kelamin - updated to use JK
    if ($jenis_kelamin) {
        $where_conditions[] = "p.JK = ?";
        $params[] = $jenis_kelamin;
    }
    
    // Filter by gelar - new filter
    if ($gelar) {
        $where_conditions[] = "p.gelar_pendidikan LIKE ?";
        $params[] = "%$gelar%";
    }
    
    // Filter by status nikah
    if ($status_nikah) {
        $where_conditions[] = "p.status_nikah = ?";
        $params[] = $status_nikah;
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Count query for pagination
    $count_sql = "
        SELECT COUNT(*) as total
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        $where_clause
    ";
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetch()['total'];
    
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
            p.created_at,
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
        ORDER BY p.$sort_by $sort_order
        LIMIT $limit OFFSET $offset
    ";
    
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
            'created_at' => $personil['created_at'],
            
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
    
    // Calculate pagination
    $total_pages = ceil($total_results / $limit);
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'timestamp' => date('c'),
        'query' => [
            'q' => $q,
            'filters' => [
                'unsur' => $unsur,
                'bagian' => $bagian,
                'kepegawaian' => $kepegawaian,
                'pangkat' => $pangkat,
                'status' => $status,
                'jk' => $jenis_kelamin,
                'gelar' => $gelar,
                'status_nikah' => $status_nikah
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_results' => $total_results,
                'total_pages' => $total_pages,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ],
            'sort' => [
                'sort_by' => $sort_by,
                'sort_order' => strtolower($sort_order)
            ]
        ],
        'data' => [
            'results' => $response_data,
            'count' => count($response_data)
        ],
        'message' => "Advanced search completed successfully"
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
