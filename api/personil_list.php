<?php
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

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $unsur_filter = isset($_GET['unsur']) ? $_GET['unsur'] : null;
    $bagian_filter = isset($_GET['bagian']) ? $_GET['bagian'] : null;
    $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Build WHERE clause - get all non-deleted personil
    $where_conditions = ["p.is_deleted = FALSE"];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.nama LIKE ? OR p.nrp LIKE ? OR pg.nama_pangkat LIKE ? OR j.nama_jabatan LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
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
    
    // Main query
    $sql = "
        SELECT 
            p.id,
            p.nama,
            p.nrp,
            p.JK,
            p.status_ket,
            p.alasan_status,
            p.tanggal_lahir,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            j.nama_jabatan,
            b.id as bagian_id,
            b.nama_bagian,
            u.id as unsur_id,
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
        WHERE $where_clause
        ORDER BY 
            u.urutan ASC,
            b.nama_bagian ASC,
            CASE WHEN pg.level_pangkat IS NULL THEN 999999 ELSE pg.level_pangkat END ASC,
            p.nama ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $personil = $stmt->fetchAll();
    
    // Group by unsur and bagian
    $grouped_data = [];
    foreach ($personil as $p) {
        $unsur_id = $p['unsur_id'] ?? 0;
        $unsur_name = $p['nama_unsur'] ?: 'TANPA UNSUR';
        $bagian_id = $p['bagian_id'] ?? 0;
        $bagian_name = $p['nama_bagian'] ?: 'TANPA BAGIAN';
        
        if (!isset($grouped_data[$unsur_id])) {
            $grouped_data[$unsur_id] = [
                'id' => $unsur_id,
                'nama_unsur' => $unsur_name,
                'kode_unsur' => $p['kode_unsur'] ?? '',
                'urutan' => $p['unsur_urutan'] ?? 999,
                'bagian' => []
            ];
        }
        
        if (!isset($grouped_data[$unsur_id]['bagian'][$bagian_id])) {
            $grouped_data[$unsur_id]['bagian'][$bagian_id] = [
                'id' => $bagian_id,
                'nama_bagian' => $bagian_name,
                'personil' => []
            ];
        }
        
        $grouped_data[$unsur_id]['bagian'][$bagian_id]['personil'][] = [
            'id' => $p['id'],
            'nama' => $p['nama'],
            'nrp' => $p['nrp'],
            'JK' => $p['JK'],
            'status_ket' => $p['status_ket'] ?: 'aktif',
            'alasan_status' => $p['alasan_status'],
            'tanggal_lahir' => $p['tanggal_lahir'],
            'nama_pangkat' => $p['nama_pangkat'],
            'pangkat_singkatan' => $p['pangkat_singkatan'],
            'nama_jabatan' => $p['nama_jabatan'],
            'status_kepegawaian' => $p['status_kepegawaian'] ?: '-'
        ];
    }
    
    // Sort by unsur urutan
    uasort($grouped_data, function($a, $b) {
        return $a['urutan'] <=> $b['urutan'];
    });
    
    // Get statistics
    $stats = [
        'total' => count($personil),
        'by_jk' => ['L' => 0, 'P' => 0],
        'by_status' => [],
        'by_pangkat' => []
    ];
    
    foreach ($personil as $p) {
        $jk = $p['JK'] ?? 'L';
        $stats['by_jk'][$jk] = ($stats['by_jk'][$jk] ?? 0) + 1;
        
        $status = $p['status_ket'] ?: 'aktif';
        $stats['by_status'][$status] = ($stats['by_status'][$status] ?? 0) + 1;
        
        // Group by pangkat
        $pangkat_name = $p['pangkat_singkatan'] ?: $p['nama_pangkat'] ?: 'TANPA PANGKAT';
        $stats['by_pangkat'][$pangkat_name] = ($stats['by_pangkat'][$pangkat_name] ?? 0) + 1;
    }
    
    // Sort pangkat by rank (highest to lowest: POLRI first, then non-POLRI)
    $pangkat_order = [
        // POLRI - Tinggi ke Rendah
        'AKBP' => 1,
        'KOMPOL' => 2,
        'AKP' => 3,
        'IPTU' => 4,
        'IPDA' => 5,
        'AIPTU' => 6,
        'AIPDA' => 7,
        'BRIPKA' => 8,
        'BRIGPOL' => 9,
        'BRIPTU' => 10,
        'BRIPDA' => 11,  // Tambahkan BRIPDA
        'BRPDA' => 12,   // Tambahkan BRPDA jika ada
        // Non-POLRI - setelah semua POLRI
        'PENATA' => 100,
        'PENDA' => 101,
        'TANPA PANGKAT' => 999
    ];
    
    // Sort: POLRI first (by rank), then non-POLRI
    uksort($stats['by_pangkat'], function($a, $b) use ($pangkat_order) {
        $order_a = $pangkat_order[$a] ?? 500; // Unknown = 500 (after POLRI, before non-POLRI)
        $order_b = $pangkat_order[$b] ?? 500;
        
        // If both are POLRI (order < 100), sort by rank
        // If both are non-POLRI (order >= 100), sort alphabetically
        // POLRI always comes before non-POLRI
        if ($order_a < 100 && $order_b >= 100) return -1;
        if ($order_a >= 100 && $order_b < 100) return 1;
        return $order_a <=> $order_b;
    });
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Retrieved " . count($personil) . " personil records",
        'data' => [
            'personil_grouped' => $grouped_data,
            'statistics' => $stats,
            'total_count' => count($personil)
        ],
        'filters' => [
            'search' => $search,
            'unsur' => $unsur_filter,
            'bagian' => $bagian_filter,
            'status' => $status_filter
        ],
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
