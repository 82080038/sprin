<?php
/**
 * Unsur API - Separate API endpoints for unsur operations
 * No authentication required for GET operations
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_unsur_detail':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            $unsur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $unsur
            ]);
            break;
            
        case 'get_all_unsur':
            $stmt = $pdo->query("
                SELECT u.*, 
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_unsur = u.id) as jabatan_count,
                       (SELECT COUNT(*) FROM personil p 
                        JOIN jabatan j ON p.id_jabatan = j.id 
                        WHERE j.id_unsur = u.id AND p.is_deleted = 0) as personil_count
                FROM unsur u 
                ORDER BY u.urutan ASC, u.nama_unsur ASC
            ");
            $unsurList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $unsurList
            ]);
            break;
            
        case 'get_unsur_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_unsur,
                    COUNT(CASE WHEN u.urutan <= 5 THEN 1 END) as struktural_unsur,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil
                FROM unsur u
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
