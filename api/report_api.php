<?php
declare(strict_types=1);
/**
 * Reporting API
 * Generate various reports with filters and export options
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'timestamp' => date('c')
    ]);
    exit;
}

$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    switch ($action) {
        case 'personil_summary':
            // Summary report of all personil
            $report = generatePersonilSummary($pdo);
            echo json_encode([
                'success' => true,
                'message' => 'Personil summary report generated',
                'data' => $report,
                'timestamp' => date('c')
            ]);
            break;
            
        case 'demographic_report':
            // Demographic breakdown by gender, age, education
            $report = generateDemographicReport($pdo);
            echo json_encode([
                'success' => true,
                'message' => 'Demographic report generated',
                'data' => $report,
                'timestamp' => date('c')
            ]);
            break;
            
        case 'organizational_report':
            // Report by unsur, bagian, jabatan
            $unsur = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'unsur', FILTER_SANITIZE_STRING) ?? null;
            $bagian = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian', FILTER_SANITIZE_STRING) ?? null;
            $report = generateOrganizationalReport($pdo, $unsur, $bagian);
            echo json_encode([
                'success' => true,
                'message' => 'Organizational report generated',
                'data' => $report,
                'timestamp' => date('c')
            ]);
            break;
            
        case 'attendance_report':
            // Attendance/summary report (placeholder - would need attendance data)
            echo json_encode([
                'success' => true,
                'message' => 'Attendance report',
                'data' => [
                    'note' => 'Attendance tracking to be implemented',
                    'total_personil' => getTotalPersonil($pdo)
                ],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'export':
            // Export report to various formats
            $format = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'format', FILTER_SANITIZE_STRING) ?? 'json';
            $type = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'type', FILTER_SANITIZE_STRING) ?? 'personil_summary';
            
            switch ($type) {
                case 'personil_summary':
                    $data = generatePersonilSummary($pdo);
                    break;
                case 'demographic':
                    $data = generateDemographicReport($pdo);
                    break;
                default:
                    throw new Exception('Unknown report type');
            }
            
            // For CSV export
            if ($format === 'csv') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="report_' . $type . '_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                
                // Headers based on report type
                if ($type === 'personil_summary') {
                    fputcsv($output, ['Unsur', 'Bagian', 'Total', 'POLRI', 'ASN', 'P3K']);
                    foreach ($data['by_bagian'] as $row) {
                        fputcsv($output, [
                            $row['unsur'],
                            $row['bagian'],
                            $row['total'],
                            $row['polri'],
                            $row['asn'],
                            $row['p3k']
                        ]);
                    }
                }
                
                fclose($output);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Report data ready for export',
                'data' => $data,
                'timestamp' => date('c')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

// Helper functions
function getTotalPersonil($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_deleted = FALSE AND is_active = TRUE");
    return $stmt->fetchColumn();
}

function generatePersonilSummary($pdo) {
    // Summary by unsur and bagian
    $sql = "SELECT 
        u.nama_unsur as unsur,
        b.nama_bagian as bagian,
        COUNT(p.id) as total,
        COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri,
        COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn,
        COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k
    FROM unsur u
    LEFT JOIN bagian b ON u.id = b.id_unsur
    LEFT JOIN personil p ON b.id = p.id_bagian AND p.is_deleted = FALSE AND p.is_active = TRUE
    LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
    GROUP BY u.id, u.nama_unsur, b.id, b.nama_bagian
    ORDER BY u.urutan, b.nama_bagian";
    
    $stmt = $pdo->query($sql);
    $by_bagian = $stmt->fetchAll();
    
    // Totals
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri,
        COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn,
        COUNT(CASE WHEN mjp.kode_jenis = 'P3K' THEN 1 END) as p3k,
        COUNT(CASE WHEN p.JK = 'L' THEN 1 END) as laki,
        COUNT(CASE WHEN p.JK = 'P' THEN 1 END) as perempuan
    FROM personil p
    LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
    WHERE p.is_deleted = FALSE AND p.is_active = TRUE");
    $totals = $stmt->fetch();
    
    return [
        'generated_at' => date('c'),
        'totals' => $totals,
        'by_bagian' => $by_bagian,
        'summary' => [
            'title' => 'Ringkasan Kepegawaian POLRES Samosir',
            'total_personil' => $totals['total'],
            'gender_distribution' => [
                'male' => $totals['laki'],
                'female' => $totals['perempuan']
            ],
            'type_distribution' => [
                'polri' => $totals['polri'],
                'asn' => $totals['asn'],
                'p3k' => $totals['p3k']
            ]
        ]
    ];
}

function generateDemographicReport($pdo) {
    // Age distribution
    $stmt = $pdo->query("SELECT 
        CASE 
            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 30 THEN '< 30'
            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 30 AND 40 THEN '30-40'
            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 41 AND 50 THEN '41-50'
            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 51 AND 60 THEN '51-60'
            ELSE '> 60'
        END as age_group,
        COUNT(*) as count,
        COUNT(CASE WHEN mjp.kode_jenis = 'POLRI' THEN 1 END) as polri,
        COUNT(CASE WHEN mjp.kode_jenis = 'ASN' THEN 1 END) as asn
    FROM personil p
    LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
    WHERE p.is_deleted = FALSE AND p.is_active = TRUE AND p.tanggal_lahir IS NOT NULL
    GROUP BY age_group
    ORDER BY FIELD(age_group, '< 30', '30-40', '41-50', '51-60', '> 60')");
    $age_distribution = $stmt->fetchAll();
    
    // Education level
    $stmt = $pdo->query("SELECT 
        CASE 
            WHEN gelar_pendidikan IS NULL OR gelar_pendidikan = '' THEN 'Tidak Diketahui'
            ELSE gelar_pendidikan
        END as education,
        COUNT(*) as count
    FROM personil
    WHERE is_deleted = FALSE AND is_active = TRUE
    GROUP BY education
    ORDER BY count DESC
    LIMIT 10");
    $education = $stmt->fetchAll();
    
    return [
        'generated_at' => date('c'),
        'age_distribution' => $age_distribution,
        'education_distribution' => $education
    ];
}

function generateOrganizationalReport($pdo, $filterUnsur = null, $filterBagian = null) {
    $where = ["p.is_deleted = FALSE", "p.is_active = TRUE"];
    $params = [];
    
    if ($filterUnsur) {
        $where[] = "u.kode_unsur = ?";
        $params[] = $filterUnsur;
    }
    
    if ($filterBagian) {
        $where[] = "b.kode_bagian = ?";
        $params[] = $filterBagian;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT 
        p.nama,
        p.nrp,
        p.nip,
        p.JK as jenis_kelamin,
        u.nama_unsur,
        b.nama_bagian,
        j.nama_jabatan,
        pg.nama_pangkat,
        mjp.nama_jenis as jenis_pegawai
    FROM personil p
    LEFT JOIN unsur u ON p.id_unsur = u.id
    LEFT JOIN bagian b ON p.id_bagian = b.id
    LEFT JOIN jabatan j ON p.id_jabatan = j.id
    LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
    LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
    WHERE $whereClause
    ORDER BY u.urutan, b.nama_bagian, p.nama
    LIMIT 1000";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $personil = $stmt->fetchAll();
    
    return [
        'generated_at' => date('c'),
        'filters' => [
            'unsur' => $filterUnsur,
            'bagian' => $filterBagian
        ],
        'total_records' => count($personil),
        'personil' => $personil
    ];
}

?>