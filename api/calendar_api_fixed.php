<?php
declare(strict_types=1);
/**
 * Fixed Calendar API - Consistent JSON Response
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';
require_once __DIR__ . '/APIResponseStandardizer.php';

// Initialize session
SessionManager::start();

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    APIResponseStandardizer::unauthorized('Unauthorized access');
}

try {
    // Use Database singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get action parameter
    $action = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) : 'getStats';
    
    switch ($action) {
        case 'getStats':
            // Get schedule statistics
            $today = date('Y-m-d');
            $week_end = date('Y-m-d', strtotime('+7 days'));
            
            // Default statistics (since jadwal table might not exist)
            $today_count = 0;
            $week_count = 0;
            
            // Try to get schedule data from jadwal table
            try {
                $schedule_sql = "
                    SELECT COUNT(*) as count
                    FROM jadwal 
                    WHERE DATE(tanggal_mulai) = ? 
                    AND status = 'AKTIF'
                ";
                
                $schedule_stmt = $pdo->prepare($schedule_sql);
                $schedule_stmt->execute([$today]);
                $today_result = $schedule_stmt->fetch(PDO::FETCH_ASSOC);
                $today_count = (int)$today_result['count'];
                
                // Get week statistics
                $week_sql = "
                    SELECT COUNT(*) as count
                    FROM jadwal 
                    WHERE DATE(tanggal_mulai) BETWEEN ? AND ?
                    AND status = 'AKTIF'
                ";
                
                $week_stmt = $pdo->prepare($week_sql);
                $week_stmt->execute([$today, $week_end]);
                $week_result = $week_stmt->fetch(PDO::FETCH_ASSOC);
                $week_count = (int)$week_result['count'];
                
            } catch (Exception $e) {
                // Jadwal table doesn't exist or other error, use defaults
                error_log("Calendar API - Jadwal table error: " . $e->getMessage());
            }
            
            // Get personil statistics for calendar context
            $personil_stats = [];
            try {
                $personil_sql = "
                    SELECT 
                        COUNT(*) as total_personil,
                        SUM(CASE WHEN status_ket = 'AKTIF' THEN 1 ELSE 0 END) as aktif_personil,
                        SUM(CASE WHEN status_ket = 'CUTI' THEN 1 ELSE 0 END) as cuti_personil,
                        SUM(CASE WHEN status_ket = 'TDK' THEN 1 ELSE 0 END) sebagai tdk_personil
                    FROM personil
                ";
                
                $personil_stmt = $pdo->prepare($personil_sql);
                $personil_stmt->execute();
                $personil_result = $personil_stmt->fetch(PDO::FETCH_ASSOC);
                
                $personil_stats = [
                    'total_personil' => (int)$personil_result['total_personil'],
                    'aktif_personil' => (int)$personil_result['aktif_personil'],
                    'cuti_personil' => (int)$personil_result['cuti_personil'],
                    'tdk_personil' => (int)$personil_result['tdk_personil']
                ];
                
            } catch (Exception $e) {
                error_log("Calendar API - Personil stats error: " . $e->getMessage());
            }
            
            // Get upcoming events (if any)
            $upcoming_events = [];
            try {
                $events_sql = "
                    SELECT 
                        id,
                        kegiatan as title,
                        tanggal_mulai as start_date,
                        tanggal_selesai as end_date,
                        lokasi as location,
                        deskripsi as description
                    FROM jadwal 
                    WHERE DATE(tanggal_mulai) >= ?
                    AND status = 'AKTIF'
                    ORDER BY tanggal_mulai ASC
                    LIMIT 10
                ";
                
                $events_stmt = $pdo->prepare($events_sql);
                $events_stmt->execute([$today]);
                $upcoming_events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                error_log("Calendar API - Events error: " . $e->getMessage());
            }
            
            $response_data = [
                'schedule_stats' => [
                    'today' => $today_count,
                    'week' => $week_count,
                    'date_range' => [
                        'today' => $today,
                        'week_end' => $week_end
                    ],
                    'note' => 'Schedule statistics from jadwal table'
                ],
                'personnel_stats' => $personil_stats,
                'upcoming_events' => $upcoming_events,
                'calendar_info' => [
                    'current_date' => $today,
                    'timezone' => date_default_timezone_get(),
                    'server_time' => date('Y-m-d H:i:s')
                ]
            ];
            
            APIResponseStandardizer::success($response_data, "Calendar statistics retrieved successfully");
            break;
            
        case 'getEvents':
            // Get calendar events
            $start_date = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'start', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'start', FILTER_SANITIZE_STRING) : date('Y-m-01');
            $end_date = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'end', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'end', FILTER_SANITIZE_STRING) : date('Y-m-t');
            
            $events = [];
            try {
                $events_sql = "
                    SELECT 
                        id,
                        kegiatan as title,
                        tanggal_mulai as start,
                        tanggal_selesai as end,
                        lokasi as location,
                        deskripsi as description,
                        status as status,
                        created_at
                    FROM jadwal 
                    WHERE DATE(tanggal_mulai) BETWEEN ? AND ?
                    ORDER BY tanggal_mulai ASC
                ";
                
                $events_stmt = $pdo->prepare($events_sql);
                $events_stmt->execute([$start_date, $end_date]);
                $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format events for calendar
                $formatted_events = [];
                foreach ($events as $event) {
                    $formatted_events[] = [
                        'id' => (int)$event['id'],
                        'title' => $event['title'],
                        'start' => $event['start'],
                        'end' => $event['end'],
                        'location' => $event['location'],
                        'description' => $event['description'],
                        'status' => $event['status'],
                        'created_at' => $event['created_at']
                    ];
                }
                
            } catch (Exception $e) {
                error_log("Calendar API - Get events error: " . $e->getMessage());
            }
            
            APIResponseStandardizer::success($formatted_events, "Retrieved " . count($formatted_events) . " events");
            break;
            
        default:
            APIResponseStandardizer::error("Invalid action: $action", 400, [
                'valid_actions' => ['getStats', 'getEvents']
            ]);
    }
    
} catch(Exception $e) {
    if (ENVIRONMENT === 'development') {
        APIResponseStandardizer::error('Database error: ' . $e->getMessage(), 500, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        APIResponseStandardizer::error('Failed to process calendar request', 500);
    }
}
?>
