<?php
declare(strict_types=1);
/**
 * Export Personil API - Updated for New Database Structure
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $format = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'format', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'format', FILTER_SANITIZE_STRING) : 'csv';
    $filter = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'filter', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'filter', FILTER_SANITIZE_STRING) : 'all';
    $include_headers = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_headers', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'include_headers', FILTER_SANITIZE_STRING) : '1';
    $gender = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gender', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gender', FILTER_SANITIZE_STRING) : null;
    $status = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING) : null;
    $pangkat = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pangkat', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pangkat', FILTER_SANITIZE_STRING) : null;
    $per_page = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'per_page', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'per_page', FILTER_SANITIZE_STRING) : null; // Add this parameter
    
    // Build WHERE clause
    $where_conditions = ["p.is_deleted = 0"];
    $params = [];
    
    // Apply filters
    if ($filter === 'aktif') {
        $where_conditions[] = "p.status_ket = 'aktif'";
    } elseif ($filter === 'nonaktif') {
        $where_conditions[] = "p.status_ket = 'nonaktif'";
    }
    
    if ($gender) {
        $where_conditions[] = "p.JK = ?";
        $params[] = $gender;
    }
    
    if ($status) {
        $where_conditions[] = "p.status_ket = ?";
        $params[] = $status;
    }
    
    if ($pangkat) {
        $where_conditions[] = "p.id_pangkat = ?";
        $params[] = $pangkat;
    }
    
    // Set headers based on format
    switch ($format) {
        case 'csv':
            header("Content-Type: text/csv; charset=UTF-8");
            header("Content-Disposition: attachment; filename=personil_export_" . date('Y-m-d_H-i-s') . ".csv");
            break;
        case 'excel':
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=personil_export_" . date('Y-m-d_H-i-s') . ".xlsx");
            break;
        case 'pdf':
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=personil_export_" . date('Y-m-d_H-i-s') . ".pdf");
            break;
        default:
            header("Content-Type: text/csv; charset=UTF-8");
            header("Content-Disposition: attachment; filename=personil_export_" . date('Y-m-d_H-i-s') . ".csv");
    }
    
    header("Access-Control-Allow-Origin: *");
    
    // Build complete WHERE clause
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Main export query
    $sql = "
        SELECT 
            p.nama,
            p.nrp,
            p.nip,
            p.JK,
            p.tanggal_lahir,
            p.tempat_lahir,
            p.status_ket,
            pg.nama_pangkat,
            j.nama_jabatan,
            b.nama_bagian,
            u.nama_unsur,
            p.created_at
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        $where_clause
        ORDER BY 
            b.nama_bagian, 
            u.nama_unsur,
            p.nama
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Create output based on format
    if ($format === 'csv') {
        createCSVOutput($results, $include_headers);
    } elseif ($format === 'excel') {
        createExcelOutput($results, $include_headers);
    } elseif ($format === 'pdf') {
        createPDFOutput($results, $include_headers);
    } else {
        createCSVOutput($results, $include_headers);
    }
    
} catch (Exception $e) {
    error_log("Export Error: " . $e->getMessage());
    header("HTTP/1.0 500 Internal Server Error");
    echo "Error: " . $e->getMessage();
}

function createCSVOutput($results, $include_headers) {
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // CSV Headers
    if ($include_headers === '1') {
        $headers = [
            'NAMA',
            'NRP',
            'NIP',
            'JK',
            'TANGGAL LAHIR',
            'TEMPAT LAHIR',
            'STATUS',
            'PANGKAT',
            'JABATAN',
            'BAGIAN',
            'UNSUR'
        ];
        fputcsv($output, $headers);
    }
    
    // CSV Data
    foreach ($results as $row) {
        $csv_row = [
            $row['nama'] ?? '',
            $row['nrp'] ?? '',
            $row['nip'] ?? '',
            $row['JK'] ?? '',
            $row['tanggal_lahir'] ?? '',
            $row['tempat_lahir'] ?? '',
            $row['status_ket'] ?? '',
            $row['nama_pangkat'] ?? '',
            $row['nama_jabatan'] ?? '',
            $row['nama_bagian'] ?? '',
            $row['nama_unsur'] ?? ''
        ];
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
}

function createExcelOutput($results, $include_headers) {
    // For now, fall back to CSV (Excel implementation would require additional libraries)
    createCSVOutput($results, $include_headers);
}

function createPDFOutput($results, $include_headers) {
    // For now, fall back to CSV (PDF implementation would require additional libraries)
    createCSVOutput($results, $include_headers);
}
?>
