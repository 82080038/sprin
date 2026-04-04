<?php
declare(strict_types=1);
/**
 * Personil List API - Returns personil data grouped by unsur and bagian
 * Standardized Version
 */

// Set headers first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 0); // Disable logging to prevent permission issues
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get filter parameters
    $search = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) ? trim(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) : '';
    $unsur_filter = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) : null;
    $bagian_filter = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian', FILTER_SANITIZE_STRING) : null;
    $status_filter = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING) : null;
    $page = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING) : 1;
    $per_page = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'per_page', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'per_page', FILTER_SANITIZE_STRING) : 10;
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($per_page < 1 || $per_page > 100) $per_page = 10;
    
    // Build WHERE clause - get all non-deleted personil
    $where_conditions = ["p.is_deleted = 0"];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.nama LIKE ? OR p.nrp LIKE ? OR pg.nama_pangkat LIKE ? OR j.nama_jabatan LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($unsur_filter) {
        $where_conditions[] = "u.id = ?";
        $params[] = $unsur_filter;
    }
    
    if ($bagian_filter) {
        $where_conditions[] = "b.id = ?";
        $params[] = $bagian_filter;
    }
    
    if ($status_filter) {
        $where_conditions[] = "p.status_ket = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Get total count for pagination
    $count_sql = "
        SELECT COUNT(*) as total
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE $where_clause
    ";
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetch()['total'];
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Main query
    $sql = "
        SELECT 
            p.id,
            p.nama,
            p.gelar_depan,
            p.gelar_belakang,
            p.nrp,
            p.JK,
            p.status_ket,
            p.alasan_status,
            p.tanggal_lahir,
            p.tempat_lahir,
            p.tanggal_masuk,
            p.tanggal_pensiun,
            p.no_karpeg,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            pg.level_pangkat,
            j.nama_jabatan,
            b.id as bagian_id,
            b.nama_bagian,
            u.id as unsur_id,
            u.nama_unsur,
            u.kode_unsur,
            u.urutan as unsur_urutan,
            mjp.kategori as status_kepegawaian,
            CASE 
                WHEN pg.singkatan IN ('AKBP', 'KOMPOL', 'AKP', 'IPTU', 'IPDA', 'AIPTU', 'AIPDA', 'BRIPKA', 'BRIGPOL', 'BRIPTU', 'BRIPDA', 'BRPDA') 
                OR pg.nama_pangkat LIKE '%KOMISARIS%' 
                OR pg.nama_pangkat LIKE '%INSPEKTUR%'
                OR pg.nama_pangkat LIKE '%AJUDAN%'
                OR pg.nama_pangkat LIKE '%BRIGADIR%'
                OR pg.nama_pangkat LIKE '%BHARADA%'
                OR pg.nama_pangkat LIKE '%BRIPTU%'
                OR pg.nama_pangkat LIKE '%BRIPDA%'
                THEN 1
                ELSE 0
            END as is_polri,
            p.created_at,
            p.updated_at
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE $where_clause
        ORDER BY u.urutan ASC, b.nama_bagian ASC, pg.level_pangkat ASC, p.nama ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $personilData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for consistency
    $formattedData = array_map(function($person) {
        return [
            'id' => (int)$person['id'],
            'nama' => $person['nama'],
            'nrp' => $person['nrp'],
            'nip' => $person['nip'] ?? '',
            'jk' => $person['JK'],
            'status' => $person['status_ket'],
            'alasan_status' => $person['alasan_status'] ?? '',
            'tanggal_lahir' => $person['tanggal_lahir'],
            'tempat_lahir' => $person['tempat_lahir'] ?? '',
            'tanggal_masuk' => $person['tanggal_masuk'] ?? '',
            'tanggal_pensiun' => $person['tanggal_pensiun'] ?? '',
            'no_karpeg' => $person['no_karpeg'] ?? '',
            'pangkat' => [
                'nama' => $person['nama_pangkat'] ?? '',
                'singkatan' => $person['pangkat_singkatan'] ?? '',
                'level' => (int)($person['level_pangkat'] ?? 0)
            ],
            'jabatan' => $person['nama_jabatan'] ?? '',
            'bagian' => [
                'id' => (int)($person['bagian_id'] ?? 0),
                'nama' => $person['nama_bagian'] ?? ''
            ],
            'unsur' => [
                'id' => (int)($person['unsur_id'] ?? 0),
                'nama' => $person['nama_unsur'] ?? '',
                'kode' => $person['kode_unsur'] ?? '',
                'urutan' => (int)($person['unsur_urutan'] ?? 0)
            ],
            'status_kepegawaian' => $person['status_kepegawaian'] ?? '',
            'is_polri' => (bool)$person['is_polri'],
            'created_at' => $person['created_at'],
            'updated_at' => $person['updated_at']
        ];
    }, $personilData);
    
    // Group data by unsur and bagian
    $personil_grouped = [];
    $statistics = [
        'total' => $total,
        'by_jk' => ['L' => 0, 'P' => 0],
        'by_status' => ['aktif' => 0, 'nonaktif' => 0],
        'by_unsur' => [],
        'by_bagian' => []
    ];
    
    foreach ($formattedData as $person) {
        $unsur_nama = $person['unsur']['nama'] ?: 'Tidak Diketahui';
        $bagian_nama = $person['bagian']['nama'] ?: 'Tidak Diketahui';
        
        // Group by unsur -> bagian -> personil
        if (!isset($personil_grouped[$unsur_nama])) {
            $personil_grouped[$unsur_nama] = [
                'id' => $person['unsur']['id'],
                'nama' => $unsur_nama,
                'kode' => $person['unsur']['kode'],
                'urutan' => $person['unsur']['urutan'],
                'bagian' => []
            ];
        }
        
        if (!isset($personil_grouped[$unsur_nama]['bagian'][$bagian_nama])) {
            $personil_grouped[$unsur_nama]['bagian'][$bagian_nama] = [
                'id' => $person['bagian']['id'],
                'nama' => $bagian_nama,
                'personil' => []
            ];
        }
        
        $personil_grouped[$unsur_nama]['bagian'][$bagian_nama]['personil'][] = $person;
        
        // Update statistics
        $statistics['by_jk'][$person['jk']]++;
        $status = $person['status'] === 'aktif' ? 'aktif' : 'nonaktif';
        $statistics['by_status'][$status]++;
        
        $statistics['by_unsur'][$unsur_nama] = ($statistics['by_unsur'][$unsur_nama] ?? 0) + 1;
        $statistics['by_bagian'][$bagian_nama] = ($statistics['by_bagian'][$bagian_nama] ?? 0) + 1;
    }
    
    // Sort unsur by urutan
    uasort($personil_grouped, function($a, $b) {
        return $a['urutan'] - $b['urutan'];
    });
    
    // Return standardized response
    $response_data = [
        'personil_grouped' => $personil_grouped,
        'statistics' => $statistics,
        'personil' => $formattedData
    ];
    
    echo json_encode(APIResponse::paginated($response_data, $total, $page, $per_page, 'Personil data retrieved successfully'));
    
} catch (Exception $e) {
    handleAPIError($e, 'personil_list');
}
?>
