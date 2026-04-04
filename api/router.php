<?php
declare(strict_types=1);
/**
 * API Router - Central API endpoint with consistent JSON responses
 * Routes all API requests to appropriate handlers
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponseStandardizer.php';

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set JSON headers for all responses
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class APIRouter {
    private $routes = [];
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->registerRoutes();
    }
    
    private function registerRoutes(): void {
        $this->routes = [
            'GET' => [
                '/api/personil_simple' => 'handlePersonilSimple',
                '/api/calendar' => 'handleCalendar',
                '/api/personil' => 'handlePersonilList',
                '/api/stats' => 'handleStats'
            ],
            'POST' => [
                '/api/personil' => 'handlePersonilCreate',
                '/api/search' => 'handleSearch'
            ]
        ];
    }
    
    public function route(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove query string from URI
        $uri = explode('?', $uri)[0];
        
        if (!isset($this->routes[$method])) {
            APIResponseStandardizer::error("Method not allowed", 405);
            return;
        }
        
        if (!isset($this->routes[$method][$uri])) {
            APIResponseStandardizer::notFound("Endpoint not found: $uri");
            return;
        }
        
        $handler = $this->routes[$method][$uri];
        
        if (method_exists($this, $handler)) {
            try {
                $this->$handler();
            } catch (Exception $e) {
                if (ENVIRONMENT === 'development') {
                    APIResponseStandardizer::error($e->getMessage(), 500, [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                } else {
                    APIResponseStandardizer::serverError('Internal server error');
                }
            }
        } else {
            APIResponseStandardizer::serverError("Handler not implemented: $handler");
        }
    }
    
    private function handlePersonilSimple(): void {
        $limit = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) : 1000;
        $limit = min(1000, max(1, $limit));
        $search = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) ? trim(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) : '';
        
        $sql = "
            SELECT 
                p.id, p.nama, p.gelar_pendidikan, p.nrp, p.status_ket, p.status_nikah, p.JK,
                p.tanggal_lahir, p.tempat_lahir,
                mjp.nama_jenis as status_kepegawaian, mjp.kode_jenis as kode_kepegawaian,
                pg.nama_pangkat, pg.singkatan as pangkat_singkatan,
                j.nama_jabatan, b.nama_bagian, u.nama_unsur, u.kode_unsur,
                p.created_at, p.updated_at
            FROM personil p
            LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
            LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN bagian b ON p.id_bagian = b.id
            LEFT JOIN unsur u ON p.id_unsur = u.id
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE p.nama LIKE ? OR p.nrp LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $sql .= " ORDER BY p.nama ASC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $stats_sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN mjp.kategori = 'POLRI' THEN 1 ELSE 0 END) as polri_count,
                SUM(CASE WHEN mjp.kategori = 'ASN' THEN 1 ELSE 0 END) as asn_count,
                SUM(CASE WHEN mjp.kategori = 'P3K' THEN 1 ELSE 0 END) as p3k_count,
                SUM(CASE WHEN p.status_ket = 'AKTIF' THEN 1 ELSE 0 END) as aktif_count
            FROM personil p
            LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        ";
        
        $stats_stmt = $this->db->prepare($stats_sql);
        $stats_stmt->execute();
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        $response_data = [
            'personil' => $personil,
            'statistics' => [
                'total_personil' => (int)$stats['total'],
                'polri_count' => (int)$stats['polri_count'],
                'asn_count' => (int)$stats['asn_count'],
                'p3k_count' => (int)$stats['p3k_count'],
                'aktif_count' => (int)$stats['aktif_count']
            ],
            'search_info' => [
                'search_term' => $search,
                'limit_applied' => $limit,
                'results_count' => count($personil)
            ]
        ];
        
        APIResponseStandardizer::success($response_data, "Retrieved " . count($personil) . " personil records");
    }
    
    private function handleCalendar(): void {
        $action = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING)) ? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) : 'getStats';
        
        switch ($action) {
            case 'getStats':
                $today = date('Y-m-d');
                $week_end = date('Y-m-d', strtotime('+7 days'));
                
                // Default statistics
                $today_count = 0;
                $week_count = 0;
                
                try {
                    $schedule_sql = "SELECT COUNT(*) as count FROM jadwal WHERE DATE(tanggal_mulai) = ? AND status = 'AKTIF'";
                    $schedule_stmt = $this->db->prepare($schedule_sql);
                    $schedule_stmt->execute([$today]);
                    $today_result = $schedule_stmt->fetch(PDO::FETCH_ASSOC);
                    $today_count = (int)$today_result['count'];
                    
                    $week_sql = "SELECT COUNT(*) as count FROM jadwal WHERE DATE(tanggal_mulai) BETWEEN ? AND ? AND status = 'AKTIF'";
                    $week_stmt = $this->db->prepare($week_sql);
                    $week_stmt->execute([$today, $week_end]);
                    $week_result = $week_stmt->fetch(PDO::FETCH_ASSOC);
                    $week_count = (int)$week_result['count'];
                    
                } catch (Exception $e) {
                    error_log("Calendar stats error: " . $e->getMessage());
                }
                
                $response_data = [
                    'schedule_stats' => [
                        'today' => $today_count,
                        'week' => $week_count,
                        'date_range' => ['today' => $today, 'week_end' => $week_end]
                    ],
                    'calendar_info' => [
                        'current_date' => $today,
                        'timezone' => date_default_timezone_get(),
                        'server_time' => date('Y-m-d H:i:s')
                    ]
                ];
                
                APIResponseStandardizer::success($response_data, "Calendar statistics retrieved");
                break;
                
            default:
                APIResponseStandardizer::error("Invalid action: $action", 400);
        }
    }
    
    private function handlePersonilList(): void {
        $page = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'page', FILTER_SANITIZE_STRING) : 1;
        $limit = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING)) ? (int)filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) : 10;
        $search = isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) ? trim(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING)) : '';
        
        $offset = ($page - 1) * $limit;
        
        // Count total records
        $count_sql = "SELECT COUNT(*) as total FROM personil";
        $count_params = [];
        
        if (!empty($search)) {
            $count_sql .= " WHERE nama LIKE ? OR nrp LIKE ?";
            $searchParam = "%{$search}%";
            $count_params = [$searchParam, $searchParam];
        }
        
        $count_stmt = $this->db->prepare($count_sql);
        $count_stmt->execute($count_params);
        $total = $count_stmt->fetch()['total'];
        
        // Get records
        $sql = "SELECT id, nama, nrp, status_ket, gelar_pendidikan FROM personil";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE nama LIKE ? OR nrp LIKE ?";
            $params = [$searchParam, $searchParam];
        }
        
        $sql .= " ORDER BY nama ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        APIResponseStandardizer::paginated($personil, $total, $page, $limit, "Personil list retrieved");
    }
    
    private function handleStats(): void {
        $stats = [
            'personil' => [
                'total' => 0,
                'aktif' => 0,
                'polri' => 0,
                'asn' => 0,
                'p3k' => 0
            ],
            'unsur' => [
                'total' => 0,
                'active' => 0
            ],
            'bagian' => [
                'total' => 0,
                'active' => 0
            ],
            'jabatan' => [
                'total' => 0,
                'active' => 0
            ]
        ];
        
        // Get personil stats
        try {
            $personil_sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_ket = 'AKTIF' THEN 1 ELSE 0 END) as aktif,
                    SUM(CASE WHEN mjp.kategori = 'POLRI' THEN 1 ELSE 0 END) as polri,
                    SUM(CASE WHEN mjp.kategori = 'ASN' THEN 1 ELSE 0 END) as asn,
                    SUM(CASE WHEN mjp.kategori = 'P3K' THEN 1 ELSE 0 END) as p3k
                FROM personil p
                LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
            ";
            
            $personil_stmt = $this->db->prepare($personil_sql);
            $personil_stmt->execute();
            $personil_result = $personil_stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['personil'] = [
                'total' => (int)$personil_result['total'],
                'aktif' => (int)$personil_result['aktif'],
                'polri' => (int)$personil_result['polri'],
                'asn' => (int)$personil_result['asn'],
                'p3k' => (int)$personil_result['p3k']
            ];
            
        } catch (Exception $e) {
            error_log("Personil stats error: " . $e->getMessage());
        }
        
        APIResponseStandardizer::success($stats, "Statistics retrieved");
    }
    
    private function handlePersonilCreate(): void {
        // Implementation for creating personil
        APIResponseStandardizer::error("Not implemented yet", 501);
    }
    
    private function handleSearch(): void {
        $query = isset(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'query', FILTER_SANITIZE_STRING)) ? trim(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'query', FILTER_SANITIZE_STRING)) : '';
        $type = isset(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'type', FILTER_SANITIZE_STRING)) ? filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'type', FILTER_SANITIZE_STRING) : 'personil';
        
        if (empty($query)) {
            APIResponseStandardizer::error("Search query is required", 400);
            return;
        }
        
        $results = [];
        
        switch ($type) {
            case 'personil':
                $sql = "SELECT id, nama, nrp, gelar_pendidikan FROM personil WHERE nama LIKE ? OR nrp LIKE ? LIMIT 20";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(["%{$query}%", "%{$query}%"]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            default:
                APIResponseStandardizer::error("Invalid search type: $type", 400);
                return;
        }
        
        APIResponseStandardizer::success($results, "Search completed for: $query");
    }
}

// Route the request
$router = new APIRouter();
$router->route();
?>
