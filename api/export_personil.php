<?php
/**
 * Export Personil API - Updated for New Database Structure
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CSV download
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=personil_export_" . date('Y-m-d_H-i-s') . ".csv");
header("Access-Control-Allow-Origin: *");

// Include proper configuration
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Get export parameters
    $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
    $unsur_filter = isset($_GET['unsur']) ? $_GET['unsur'] : null;
    $bagian_filter = isset($_GET['bagian']) ? $_GET['bagian'] : null;
    $kepegawaian_filter = isset($_GET['kepegawaian']) ? $_GET['kepegawaian'] : null;
    $jk_filter = isset($_GET['jk']) ? $_GET['jk'] : null;
    $include_details = isset($_GET['details']) && $_GET['details'] === 'true';
    
    // Build WHERE clause
    $where_conditions = ["p.is_deleted = FALSE", "p.is_active = TRUE"];
    $params = [];
    
    if ($unsur_filter) {
        $where_conditions[] = "u.kode_unsur = ?";
        $params[] = $unsur_filter;
    }
    
    if ($bagian_filter) {
        $where_conditions[] = "b.nama_bagian = ?";
        $params[] = $bagian_filter;
    }
    
    // Updated kepegawaian filter
    if ($kepegawaian_filter) {
        $where_conditions[] = "mjp.kode_jenis = ?";
        $params[] = $kepegawaian_filter;
    }
    
    // New JK filter
    if ($jk_filter) {
        $where_conditions[] = "p.JK = ?";
        $params[] = $jk_filter;
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Main export query - updated structure
    $sql = "
        SELECT 
            p.nama,
            p.gelar_pendidikan,
            p.nrp,
            p.nip,
            p.JK,
            p.tanggal_lahir,
            p.tempat_lahir,
            p.status_ket,
            p.status_nikah,
            p.tanggal_masuk,
            p.tanggal_pensiun,
            p.no_karpeg,
            p.jabatan_struktural,
            p.jabatan_fungsional,
            p.golongan,
            p.eselon,
            pg.nama_pangkat,
            pg.singkatan as pangkat_singkatan,
            j.nama_jabatan,
            j.tingkat_jabatan,
            b.nama_bagian,
            b.kode_bagian,
            u.nama_unsur,
            u.kode_unsur,
            mjp.nama_jenis as status_kepegawaian,
            mjp.kode_jenis as kode_kepegawaian,
            mjp.kategori as kategori_kepegawaian,
            p.created_at
        FROM personil p
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        $where_clause
        ORDER BY 
            b.nama_bagian, 
            u.nama_unsur,
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
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // CSV Headers - updated with new fields
    $headers = [
        'NAMA',
        'GELAR PENDIDIKAN',
        'NRP',
        'NIP',
        'JK',
        'TANGGAL LAHIR',
        'TEMPAT LAHIR',
        'STATUS KET',
        'STATUS NAIKAH',
        'TANGGAL MASUK',
        'TANGGAL PENSIUN',
        'NO KARPEG',
        'JABATAN STRUKTURAL',
        'JABATAN FUNGSIONAL',
        'GOLONGAN',
        'ESELON',
        'PANGKAT',
        'PANGKAT SINGKATAN',
        'JABATAN',
        'TINGKAT JABATAN',
        'BAGIAN',
        'KODE BAGIAN',
        'UNSUR',
        'KODE UNSUR',
        'STATUS KEPEGAWAIAN',
        'KODE KEPEGAWAIAN',
        'KATEGORI KEPEGAWAIAN',
        'CREATED AT'
    ];
    
    // Optional: Add detail columns if requested
    if ($include_details) {
        $headers = array_merge($headers, [
            'EMAIL UTAMA',
            'MEDSOS INSTAGRAM',
            'PENDIDIKAN TERAKHIR'
        ]);
    }
    
    fputcsv($output, $headers);
    
    // CSV Data
    foreach ($results as $row) {
        $csv_row = [
            $row['nama'],
            $row['gelar_pendidikan'],
            $row['nrp'],
            $row['nip'],
            $row['JK'],
            $row['tanggal_lahir'],
            $row['tempat_lahir'],
            $row['status_ket'],
            $row['status_nikah'],
            $row['tanggal_masuk'],
            $row['tanggal_pensiun'],
            $row['no_karpeg'],
            $row['jabatan_struktural'],
            $row['jabatan_fungsional'],
            $row['golongan'],
            $row['eselon'],
            $row['nama_pangkat'],
            $row['pangkat_singkatan'],
            $row['nama_jabatan'],
            $row['tingkat_jabatan'],
            $row['nama_bagian'],
            $row['kode_bagian'],
            $row['nama_unsur'],
            $row['kode_unsur'],
            $row['status_kepegawaian'],
            $row['kode_kepegawaian'],
            $row['kategori_kepegawaian'],
            $row['created_at']
        ];
        
        // Optional: Add detail data
        if ($include_details) {
            // Get contact info
            $email_sql = "
                SELECT nilai_kontak FROM personil_kontak 
                WHERE id_personil = ? AND jenis_kontak = \"email\" AND is_utama = 1
            ";
            $email_stmt = $pdo->prepare($email_sql);
            $email_stmt->execute([$row["id"] ?? 0]);
            $email_utama = $email_stmt->fetchColumn() ?: "-";
            
            $medsos_sql = "
                SELECT username FROM personil_medsos 
                WHERE id_personil = ? AND platform_medsos = \"instagram\"
            ";
            $medsos_stmt = $pdo->prepare($medsos_sql);
            $medsos_stmt->execute([$row["id"] ?? 0]);
            $instagram = $medsos_stmt->fetchColumn() ?: "-";
            
            $pendidikan_sql = "
                SELECT mp.nama_pendidikan FROM personil_pendidikan pp
                LEFT JOIN master_pendidikan mp ON pp.id_pendidikan = mp.id
                WHERE pp.id_personil = ? AND pp.is_pendidikan_terakhir = 1
            ";
            $pendidikan_stmt = $pdo->prepare($pendidikan_sql);
            $pendidikan_stmt->execute([$row["id"] ?? 0]);
            $pendidikan_terakhir = $pendidikan_stmt->fetchColumn() ?: "-";
            
            $csv_row = array_merge($csv_row, [
                $email_utama,
                $instagram,
                $pendidikan_terakhir
            ]);
        }
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    
    // Log export
    $log_sql = "
        INSERT INTO export_logs (export_type, total_records, filters, exported_by, exported_at) 
        VALUES ('personil', ?, ?, ?, NOW())
    ";
    
    $filters_json = json_encode([
        'unsur' => $unsur_filter,
        'bagian' => $bagian_filter,
        'kepegawaian' => $kepegawaian_filter,
        'jk' => $jk_filter,
        'include_details' => $include_details
    ]);
    
    try {
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([count($results), $filters_json, 'system']);
    } catch (Exception $e) {
        // Log table might not exist, ignore
    }
    
} catch(Exception $e) {
    // Error handling for CSV export
    echo "Error: " . $e->getMessage();
}
?>
