<?php
declare(strict_types=1);
/**
 * Simple API for bagian data - Standardized Version
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
    // Send JSON response
    echo json_encode([
        'success' => true,
        'message' => "Retrieved " . count($bagianData) . " bagian records",
        'data' => [
            'bagian' => $bagianData,
            'total_bagian' => count($bagianData)
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
            'message' => 'Failed to retrieve bagian data',
            'timestamp' => date('c')
        ]);
    }
}
?>
