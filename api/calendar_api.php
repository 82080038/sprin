<?php
/**
 * Calendar API - Simple Schedule Statistics
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Include calendar config
require_once __DIR__ . '/../core/calendar_config.php';

try {
    // Database connection with socket
    $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Get action parameter
    $action = isset($_GET['action']) ? $_GET['action'] : 'getStats';
    
    switch ($action) {
        case 'getStats':
            // Get schedule statistics
            $today = date('Y-m-d');
            $week_end = date('Y-m-d', strtotime('+7 days'));
            
            // Try to get schedule data from jadwal table
            try {
                // Count schedules for today
                $today_sql = "
                    SELECT COUNT(*) as count 
                    FROM jadwal 
                    WHERE tanggal = ? AND is_deleted = FALSE
                ";
                $today_stmt = $pdo->prepare($today_sql);
                $today_stmt->execute([$today]);
                $today_count = $today_stmt->fetch()['count'];
                
                // Count schedules for next 7 days
                $week_sql = "
                    SELECT COUNT(*) as count 
                    FROM jadwal 
                    WHERE tanggal BETWEEN ? AND ? AND is_deleted = FALSE
                ";
                $week_stmt = $pdo->prepare($week_sql);
                $week_stmt->execute([$today, $week_end]);
                $week_count = $week_stmt->fetch()['count'];
                
                // If jadwal table doesn't exist or has no data, return defaults
                if ($today_count === false) $today_count = 0;
                if ($week_count === false) $week_count = 0;
                
            } catch (Exception $e) {
                // If jadwal table doesn't exist, use default values
                $today_count = 0;
                $week_count = 0;
            }
            
            echo json_encode([
                'success' => true,
                'timestamp' => date('c'),
                'data' => [
                    'today' => (int)$today_count,
                    'week' => (int)$week_count,
                    'date_range' => [
                        'today' => $today,
                        'week_end' => $week_end
                    ],
                    'note' => 'Schedule data not available - using default values'
                ],
                'message' => "Schedule statistics retrieved successfully"
            ], JSON_PRETTY_PRINT);
            break;
            
        default:
            throw new Exception("Invalid action: $action");
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'timestamp' => date('c'),
        'error' => [
            'message' => $e->getMessage(),
            'code' => 500,
            'hint' => 'Check action parameter and database connection'
        ]
    ], JSON_PRETTY_PRINT);
}
?>
