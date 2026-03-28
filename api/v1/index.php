<?php
/**
 * RESTful API for POLRES Samosir
 * Version 1.0
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Debug logging
error_log("API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Include dependencies
require_once dirname(__DIR__, 2) . '/core/config.php';
require_once dirname(__DIR__, 2) . '/core/auth_check.php';

// API Response class
class ApiResponse {
    public static function success($data = null, $message = 'Success') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    public static function error($message, $code = 400, $data = null) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
                'data' => $data
            ],
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    public static function validation($errors) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => 'Validation failed',
                'code' => 422,
                'details' => $errors
            ],
            'timestamp' => date('c')
        ]);
        exit;
    }
}

// Database connection
class Database {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                ApiResponse::error('Database connection failed: ' . $e->getMessage(), 500);
            }
        }
        return self::$pdo;
    }
}

// Authentication middleware
class Auth {
    public static function check() {
        // For now, allow test mode
        if (isset($_GET['test_mode']) && $_GET['test_mode'] === 'true') {
            return ['user_id' => 1, 'username' => 'Test User'];
        }
        
        // Check session authentication
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            ApiResponse::error('Unauthorized', 401);
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? 1,
            'username' => $_SESSION['username'] ?? 'User'
        ];
    }
}

// Request validator
class Validator {
    private $data;
    private $rules;
    private $errors = [];
    
    public function __construct($data, $rules = []) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public function required($field) {
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            $this->errors[$field][] = "$field is required";
        }
        return $this;
    }
    
    public function string($field) {
        if (isset($this->data[$field]) && !is_string($this->data[$field])) {
            $this->errors[$field][] = "$field must be a string";
        }
        return $this;
    }
    
    public function maxLength($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = "$field must not exceed $length characters";
        }
        return $this;
    }
    
    public function isValid() {
        return empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Debug
error_log("Original path: $path");
error_log("Path parts: " . print_r($path_parts, true));

// Remove 'api' and version from path
if ($path_parts[0] === 'api' && isset($path_parts[1]) && $path_parts[1] === 'v1') {
    $path_parts = array_slice($path_parts, 2);
}

$endpoint = implode('/', $path_parts);
$query_string = $_SERVER['QUERY_STRING'];

// Debug
error_log("Final endpoint: $endpoint");
error_log("Query string: $query_string");

// Route the request
switch ($endpoint) {
    
    // Authentication endpoints
    case 'auth/login':
        if ($method === 'POST') {
            // Simple login for testing
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = 'Test User';
            $_SESSION['user_id'] = 1;
            
            ApiResponse::success([
                'user' => [
                    'id' => 1,
                    'username' => 'Test User'
                ],
                'token' => 'test_token_' . time()
            ], 'Login successful');
        }
        break;
        
    case 'auth/logout':
        if ($method === 'POST') {
            session_destroy();
            ApiResponse::success(null, 'Logout successful');
        }
        break;
        
    case 'auth/profile':
        if ($method === 'GET') {
            $user = Auth::check();
            ApiResponse::success($user);
        }
        break;
    
    // Bagian endpoints
    case 'bagian':
        handleBagian($method);
        break;
        
    case 'bagian/' . ($path_parts[1] ?? ''):
        handleBagianDetail($method, $path_parts[1] ?? null);
        break;
    
    // Personil endpoints
    case 'personil':
        handlePersonil($method);
        break;
        
    case 'personil/' . ($path_parts[1] ?? ''):
        handlePersonilDetail($method, $path_parts[1] ?? null);
        break;
    
    // Statistics endpoints
    case 'stats/bagian':
        if ($method === 'GET') {
            handleStatsBagian();
        }
        break;
        
    case 'stats/personil':
        if ($method === 'GET') {
            handleStatsPersonil();
        }
        break;
        
    case 'stats/pangkat':
        if ($method === 'GET') {
            handleStatsPangkat();
        }
        break;
    
    default:
        ApiResponse::error('Endpoint not found', 404);
}

// Bagian handlers
function handleBagian($method) {
    $db = Database::getConnection();
    
    switch ($method) {
        case 'GET':
            // Get all bagian
            $stmt = $db->query("
                SELECT b.*, 
                       (SELECT COUNT(*) FROM personil WHERE bagian_id = b.id) as personil_count
                FROM bagian b 
                ORDER BY b.nama_bagian
            ");
            $bagian = $stmt->fetchAll();
            
            ApiResponse::success($bagian);
            
        case 'POST':
            // Create new bagian
            Auth::check();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $validator = new Validator($data);
            $validator->required('nama_bagian')->string('nama_bagian')->maxLength('nama_bagian', 255);
            
            if (!$validator->isValid()) {
                ApiResponse::validation($validator->getErrors());
            }
            
            try {
                $stmt = $db->prepare("INSERT INTO bagian (nama_bagian, type) VALUES (?, 'BAG/SAT/SIE')");
                $stmt->execute([$data['nama_bagian']]);
                
                $bagianId = $db->lastInsertId();
                
                // Get created bagian
                $stmt = $db->prepare("SELECT * FROM bagian WHERE id = ?");
                $stmt->execute([$bagianId]);
                $bagian = $stmt->fetch();
                
                ApiResponse::success($bagian, 'Bagian created successfully');
                
            } catch(PDOException $e) {
                ApiResponse::error('Failed to create bagian: ' . $e->getMessage(), 500);
            }
            
        default:
            ApiResponse::error('Method not allowed', 405);
    }
}

function handleBagianDetail($method, $id) {
    if (!$id) {
        ApiResponse::error('Bagian ID is required', 400);
    }
    
    $db = Database::getConnection();
    
    switch ($method) {
        case 'GET':
            $stmt = $db->prepare("SELECT * FROM bagian WHERE id = ?");
            $stmt->execute([$id]);
            $bagian = $stmt->fetch();
            
            if (!$bagian) {
                ApiResponse::error('Bagian not found', 404);
            }
            
            ApiResponse::success($bagian);
            
        case 'PUT':
            Auth::check();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $validator = new Validator($data);
            $validator->required('nama_bagian')->string('nama_bagian')->maxLength('nama_bagat', 255);
            
            if (!$validator->isValid()) {
                ApiResponse::validation($validator->getErrors());
            }
            
            try {
                $stmt = $db->prepare("UPDATE bagian SET nama_bagian = ? WHERE id = ?");
                $stmt->execute([$data['nama_bagian'], $id]);
                
                if ($stmt->rowCount() === 0) {
                    ApiResponse::error('Bagian not found', 404);
                }
                
                // Get updated bagian
                $stmt = $db->prepare("SELECT * FROM bagian WHERE id = ?");
                $stmt->execute([$id]);
                $bagian = $stmt->fetch();
                
                ApiResponse::success($bagian, 'Bagian updated successfully');
                
            } catch(PDOException $e) {
                ApiResponse::error('Failed to update bagian: ' . $e->getMessage(), 500);
            }
            
        case 'DELETE':
            Auth::check();
            
            // Check if bagian has personil
            $stmt = $db->prepare("SELECT COUNT(*) FROM personil WHERE bagian_id = ?");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                ApiResponse::error('Cannot delete bagian with existing personil', 400);
            }
            
            try {
                $stmt = $db->prepare("DELETE FROM bagian WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() === 0) {
                    ApiResponse::error('Bagian not found', 404);
                }
                
                ApiResponse::success(null, 'Bagian deleted successfully');
                
            } catch(PDOException $e) {
                ApiResponse::error('Failed to delete bagian: ' . $e->getMessage(), 500);
            }
            
        default:
            ApiResponse::error('Method not allowed', 405);
    }
}

// Personil handlers
function handlePersonil($method) {
    $db = Database::getConnection();
    
    switch ($method) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $stmt = $db->query("SELECT COUNT(*) as total FROM personil");
            $total = $stmt->fetch()['total'];
            
            // Get personil with joins
            $stmt = $db->query("
                SELECT p.id, p.nama, p.nrp, p.status_ket, p.status_kepegawaian,
                       pg.nama_pangkat, pg.singkatan, pg.level_pangkat,
                       j.nama_jabatan,
                       b.nama_bagian
                FROM personil p
                JOIN pangkat pg ON p.pangkat_id = pg.id
                JOIN jabatan j ON p.jabatan_id = j.id
                LEFT JOIN bagian b ON p.bagian_id = b.id
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
                    p.nama
                LIMIT $limit OFFSET $offset
            ");
            $personil = $stmt->fetchAll();
            
            ApiResponse::success([
                'data' => $personil,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        default:
            ApiResponse::error('Method not allowed', 405);
    }
}

function handlePersonilDetail($method, $id) {
    if (!$id) {
        ApiResponse::error('Personil ID is required', 400);
    }
    
    $db = Database::getConnection();
    
    switch ($method) {
        case 'GET':
            $stmt = $db->prepare("
                SELECT p.*, 
                       pg.nama_pangkat, pg.singkatan,
                       j.nama_jabatan,
                       b.nama_bagian
                FROM personil p
                JOIN pangkat pg ON p.pangkat_id = pg.id
                JOIN jabatan j ON p.jabatan_id = j.id
                LEFT JOIN bagian b ON p.bagian_id = b.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $personil = $stmt->fetch();
            
            if (!$personil) {
                ApiResponse::error('Personil not found', 404);
            }
            
            ApiResponse::success($personil);
            
        default:
            ApiResponse::error('Method not allowed', 405);
    }
}

// Statistics handlers
function handleStatsBagian() {
    $db = Database::getConnection();
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_bagian,
            COUNT(CASE WHEN type = 'BAG/SAT/SIE' THEN 1 END) as bag_sat_sie,
            COUNT(CASE WHEN type LIKE '%POLSEK%' THEN 1 END) as polsek
        FROM bagian
    ");
    $stats = $stmt->fetch();
    
    ApiResponse::success($stats);
}

function handleStatsPersonil() {
    $db = Database::getConnection();
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_personil,
            COUNT(CASE WHEN status_kepegawaian = 'POLRI' THEN 1 END) as polri,
            COUNT(CASE WHEN status_kepegawaian = 'ASN' THEN 1 END) as asn,
            COUNT(CASE WHEN status_kepegawaian = 'P3K' THEN 1 END) as p3k,
            COUNT(CASE WHEN status_ket = 'aktif' THEN 1 END) as aktif
        FROM personil
    ");
    $stats = $stmt->fetch();
    
    ApiResponse::success($stats);
}

function handleStatsPangkat() {
    $db = Database::getConnection();
    
    $stmt = $db->query("
        SELECT 
            pg.nama_pangkat,
            pg.singkatan,
            COUNT(p.id) as jumlah,
            ROUND(COUNT(p.id) * 100.0 / (SELECT COUNT(*) FROM personil), 2) as persen
        FROM pangkat pg
        LEFT JOIN personil p ON pg.id = p.pangkat_id
        GROUP BY pg.id, pg.nama_pangkat, pg.singkatan
        HAVING COUNT(p.id) > 0
        ORDER BY COUNT(p.id) DESC
    ");
    $stats = $stmt->fetchAll();
    
    ApiResponse::success($stats);
}

?>
