<?php
/**
 * Unsur Statistics API - Standardized Version
 */

// Set JSON content type first
header('Content-Type: application/json; charset=UTF-8');

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
    $unsur_filter = isset($_GET['unsur']) ? $_GET['unsur'] : null;
    $include_details = isset($_GET['details']) && $_GET['details'] === 'true';
    
    // Build WHERE clause
    $where_conditions = ["p.is_deleted = FALSE", "p.is_active = TRUE"];
    $params = [];
    
    if ($unsur_filter) {
        $where_conditions[] = "u.kode_unsur = ?";
        $params[] = $unsur_filter;
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Main unsur statistics query - updated for new structure
    $sql = "
        SELECT 
            u.id,
            u.kode_unsur,
            u.nama_unsur,
            u.deskripsi,
            u.urutan,
            COUNT(p.id) as total_personil,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI_DIK' THEN 1 END) as polri_dik_count,
            COUNT(CASE WHEN p.JK = 'L' THEN 1 END) as l_count,
            COUNT(CASE WHEN p.JK = 'P' THEN 1 END) as p_count,
            COUNT(CASE WHEN p.gelar_pendidikan IS NOT NULL AND p.gelar_pendidikan != '' THEN 1 END) as with_gelar_count,
            COUNT(CASE WHEN p.tanggal_lahir IS NOT NULL AND p.tanggal_lahir != '0000-00-00' THEN 1 END) as with_tanggal_lahir_count
        FROM unsur u
        LEFT JOIN personil p ON u.id = p.id_unsur
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        $where_clause
        GROUP BY u.id, u.kode_unsur, u.nama_unsur, u.deskripsi, u.urutan
        ORDER BY u.urutan, u.nama_unsur
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $unsur_stats = $stmt->fetchAll();
    
    // Build response data
    $response_data = [];
    foreach ($unsur_stats as $unsur) {
        $unsur_data = [
            'id' => (int)$unsur['id'],
            'kode_unsur' => $unsur['kode_unsur'],
            'nama_unsur' => $unsur['nama_unsur'],
            'deskripsi' => $unsur['deskripsi'],
            'urutan' => (int)$unsur['urutan'],
            'total_personil' => (int)$unsur['total_personil'],
            'by_jenis_pegawai' => [
                'POLRI' => (int)$unsur['polri_count'],
                'ASN' => (int)$unsur['asn_count'],
                'P3K' => (int)$unsur['p3k_count'],
                'POLRI_DIK' => (int)$unsur['polri_dik_count']
            ],
            'by_jk' => [
                'L' => (int)$unsur['l_count'],
                'P' => (int)$unsur['p_count']
            ],
            'data_completeness' => [
                'with_gelar' => (int)$unsur['with_gelar_count'],
                'with_tanggal_lahir' => (int)$unsur['with_tanggal_lahir_count'],
                'gelar_percentage' => $unsur['total_personil'] > 0 ? round(($unsur['with_gelar_count'] / $unsur['total_personil']) * 100, 1) : 0,
                'tanggal_lahir_percentage' => $unsur['total_personil'] > 0 ? round(($unsur['with_tanggal_lahir_count'] / $unsur['total_personil']) * 100, 1) : 0
            ]
        ];
        
        // Add detailed personil list if requested
        if ($include_details && $unsur['total_personil'] > 0) {
            $detail_sql = "
                SELECT 
                    p.id,
                    p.nama,
                    p.gelar_pendidikan,
                    p.nrp,
                    p.nip,
                    p.JK,
                    p.tanggal_lahir,
                    pg.nama_pangkat,
                    j.nama_jabatan,
                    mjp.nama_jenis as status_kepegawaian
                FROM personil p
                LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
                WHERE p.is_deleted = FALSE AND p.is_active = TRUE AND p.id_unsur = ?
                ORDER BY 
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
            ";
            
            $detail_stmt = $pdo->prepare($detail_sql);
            $detail_stmt->execute([$unsur['id']]);
            $personil_details = $detail_stmt->fetchAll();
            
            $unsur_data['personil_details'] = array_map(function($personil) {
                return [
                    'id' => (int)$personil['id'],
                    'nama' => $personil['nama'],
                    'gelar_pendidikan' => $personil['gelar_pendidikan'],
                    'nrp' => $personil['nrp'],
                    'nip' => $personil['nip'],
                    'JK' => $personil['JK'],
                    'tanggal_lahir' => $personil['tanggal_lahir'],
                    'pangkat' => $personil['nama_pangkat'],
                    'jabatan' => $personil['nama_jabatan'],
                    'status_kepegawaian' => $personil['status_kepegawaian']
                ];
            }, $personil_details);
        }
        
        $response_data[] = $unsur_data;
    }
    
    // Get overall statistics
    $overall_stats_sql = "
        SELECT 
            COUNT(*) as total_personil,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k_count,
            COUNT(CASE WHEN mjp.kode_jenis = 'POLRI_DIK' THEN 1 END) as polri_dik_count,
            COUNT(CASE WHEN p.JK = 'L' THEN 1 END) as l_count,
            COUNT(CASE WHEN p.JK = 'P' THEN 1 END) as p_count,
            COUNT(CASE WHEN p.gelar_pendidikan IS NOT NULL AND p.gelar_pendidikan != '' THEN 1 END) as with_gelar_count,
            COUNT(CASE WHEN p.tanggal_lahir IS NOT NULL AND p.tanggal_lahir != '0000-00-00' THEN 1 END) as with_tanggal_lahir_count
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
    ";
    
    $overall_stats = $pdo->query($overall_stats_sql)->fetch();
    
    // Get gelar distribution
    $gelar_stats_sql = "
        SELECT 
            p.gelar_pendidikan,
            COUNT(*) as count
        FROM personil p
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE 
        AND p.gelar_pendidikan IS NOT NULL AND p.gelar_pendidikan != ''
        GROUP BY p.gelar_pendidikan
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $gelar_stats = $pdo->query($gelar_stats_sql)->fetchAll();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'timestamp' => date('c'),
        'filters' => [
            'unsur' => $unsur_filter,
            'include_details' => $include_details
        ],
        'data' => [
            'unsur_statistics' => $response_data,
            'overall_statistics' => [
                'total_personil' => (int)$overall_stats['total_personil'],
                'by_jenis_pegawai' => [
                    'POLRI' => (int)$overall_stats['polri_count'],
                    'ASN' => (int)$overall_stats['asn_count'],
                    'P3K' => (int)$overall_stats['p3k_count'],
                    'POLRI_DIK' => (int)$overall_stats['polri_dik_count']
                ],
                'by_jk' => [
                    'L' => (int)$overall_stats['l_count'],
                    'P' => (int)$overall_stats['p_count']
                ],
                'data_completeness' => [
                    'with_gelar' => (int)$overall_stats['with_gelar_count'],
                    'with_tanggal_lahir' => (int)$overall_stats['with_tanggal_lahir_count'],
                    'gelar_percentage' => round(($overall_stats['with_gelar_count'] / $overall_stats['total_personil']) * 100, 1),
                    'tanggal_lahir_percentage' => round(($overall_stats['with_tanggal_lahir_count'] / $overall_stats['total_personil']) * 100, 1)
                ]
            ],
            'top_gelar_distribution' => array_map(function($item) {
                return [
                    'gelar' => $item['gelar_pendidikan'] ?? '',
                    'count' => (int)($item['count'] ?? 0)
                ];
            }, $gelar_stats ?? [])
        ],
        'message' => "Unsur statistics retrieved successfully",
        'timestamp' => date('c')
    ]);
    
    exit;
    
} catch(Exception $e) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'timestamp' => date('c')
    ]);
    exit;
}
?>
