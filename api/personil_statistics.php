<?php
/**
 * Personil Statistics API
 * Returns detailed statistics for personil data
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start session and check authentication
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Database connection
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get detailed statistics
    $statistics = [];
    
    // Total personil
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = 0");
    $statistics['total_personil'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active personil
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM personil WHERE is_active = 1 AND is_deleted = 0");
    $statistics['active_personil'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    // By pangkat
    $stmt = $pdo->query("
        SELECT p.nama_pangkat, COUNT(*) as count 
        FROM personil pr 
        LEFT JOIN pangkat p ON pr.id_pangkat = p.id 
        WHERE pr.is_deleted = 0 
        GROUP BY p.nama_pangkat 
        ORDER BY count DESC
    ");
    $statistics['by_pangkat'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // By jabatan
    $stmt = $pdo->query("
        SELECT j.nama_jabatan, COUNT(*) as count 
        FROM personil pr 
        LEFT JOIN jabatan j ON pr.id_jabatan = j.id 
        WHERE pr.is_deleted = 0 
        GROUP BY j.nama_jabatan 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $statistics['by_jabatan'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // By bagian
    $stmt = $pdo->query("
        SELECT b.nama_bagian, COUNT(*) as count 
        FROM personil pr 
        LEFT JOIN bagian b ON pr.id_bagian = b.id 
        WHERE pr.is_deleted = 0 
        GROUP BY b.nama_bagian 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $statistics['by_bagian'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // By unsur
    $stmt = $pdo->query("
        SELECT u.nama_unsur, COUNT(*) as count 
        FROM personil pr 
        LEFT JOIN jabatan j ON pr.id_jabatan = j.id 
        LEFT JOIN unsur u ON j.id_unsur = u.id 
        WHERE pr.is_deleted = 0 
        GROUP BY u.nama_unsur 
        ORDER BY count DESC
    ");
    $statistics['by_unsur'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gender distribution
    $stmt = $pdo->query("
        SELECT JK, COUNT(*) as count 
        FROM personil 
        WHERE is_deleted = 0 AND JK IS NOT NULL 
        GROUP BY JK
    ");
    $statistics['by_gender'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $statistics,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Personil Statistics API Error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>
