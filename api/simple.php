<?php
// Simple API for bagian data
header('Content-Type: application/json');

// Include configuration
require_once '../core/config.php';
require_once '../core/calendar_config.php';

// Start session for authentication
session_start();

// Check authentication (optional for API)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // For API, we can allow access without login for now
    // In production, you might want to require API keys
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get bagian data with personil count
    $sql = "
        SELECT 
            b.id,
            b.kode_bagian,
            b.nama_bagian,
            b.id_unsur,
            b.deskripsi,
            (SELECT COUNT(*) FROM personil p WHERE p.id_bagian = b.id AND p.is_deleted = FALSE AND p.is_active = TRUE) as personil_count,
            (SELECT p.nama FROM personil p 
             JOIN bagian_pimpinan bp ON p.id = bp.personil_id 
             WHERE bp.bagian_id = b.id AND bp.tanggal_selesai IS NULL 
             LIMIT 1) as kepala
        FROM bagian b
        WHERE b.is_active = 1
        ORDER BY b.nama_bagian
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add personil details for each bagian
    foreach ($bagianData as &$bagian) {
        $personilSql = "
            SELECT 
                p.id,
                p.nama,
                p.nrp,
                pg.singkatan as pangkat,
                pg.level_pangkat,
                j.nama_jabatan
            FROM personil p
            LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            WHERE p.id_bagian = ? AND p.is_deleted = FALSE AND p.is_active = TRUE
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
                p.nama ASC
        ";
        
        $personilStmt = $pdo->prepare($personilSql);
        $personilStmt->execute([$bagian['id']]);
        $bagian['personil'] = $personilStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'bagian' => $bagianData
        ],
        'message' => 'Bagian data retrieved successfully'
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'code' => 500
        ]
    ]);
}
?>
