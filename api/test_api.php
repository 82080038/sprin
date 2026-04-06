<?php
/**
 * Test API endpoint for debugging
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Database connection
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE is_deleted = FALSE");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test response
    echo json_encode([
        'success' => true,
        'message' => 'API test successful',
        'data' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'database_connection' => 'OK',
            'personil_count' => $result['total']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
