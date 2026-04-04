<?php
declare(strict_types=1);
/**
 * Universal API Authentication Helper
 * Provides consistent authentication across all API endpoints
 */

require_once '../core/config.php';

class APIAuth {
    private static $instance = null;
    private $user = null;
    private $authenticated = false;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->validateSession();
    }
    
    /**
     * Validate session and authenticate user
     */
    private function validateSession() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            $this->authenticated = true;
            $this->user = [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? '',
                'role' => $_SESSION['user_role'] ?? 'user'
            ];
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return $this->authenticated;
    }
    
    /**
     * Get current user data
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Require authentication - returns error response if not authenticated
     */
    public function requireAuth() {
        if (!$this->authenticated) {
            $this->sendAuthError();
            exit;
        }
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        if (!$this->authenticated) {
            return false;
        }
        
        return $this->user['role'] === $role;
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role) {
        if (!$this->hasRole($role)) {
            $this->sendError('Insufficient permissions', 403);
            exit;
        }
    }
    
    /**
     * Send authentication error response
     */
    private function sendAuthError() {
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized - Session not valid',
            'error_code' => 401,
            'details' => [],
            'timestamp' => date('c'),
            'meta' => [
                'version' => APP_VERSION,
                'environment' => ENVIRONMENT
            ]
        ]);
    }
    
    /**
     * Send general error response
     */
    public function sendError($message, $code = 400, $details = []) {
        header("HTTP/1.0 $code " . $this->getStatusText($code));
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => $code,
            'details' => $details,
            'timestamp' => date('c'),
            'meta' => [
                'version' => APP_VERSION,
                'environment' => ENVIRONMENT
            ]
        ]);
    }
    
    /**
     * Send success response
     */
    public function sendSuccess($data = null, $message = 'Operation successful') {
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c'),
            'meta' => [
                'version' => APP_VERSION,
                'environment' => ENVIRONMENT
            ]
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
    }
    
    /**
     * Get HTTP status text
     */
    private function getStatusText($code) {
        $statusTexts = [
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error'
        ];
        
        return $statusTexts[$code] ?? 'Unknown';
    }
    
    /**
     * Validate request method
     */
    public function validateMethod($allowedMethods = ['GET', 'POST']) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($method, $allowedMethods)) {
            $this->sendError("Method $method not allowed", 405);
            exit;
        }
        
        return $method;
    }
    
    /**
     * Get and sanitize input data
     */
    public function getInputData() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
                return $_POST;
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'), $data);
                return $data;
            default:
                return [];
        }
    }
    
    /**
     * Validate required fields
     */
    public function validateRequired($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError('Missing required fields', 400, $missing);
            exit;
        }
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitizeData($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Log API activity
     */
    public function logActivity($action, $details = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'user_id' => $this->user['id'] ?? null,
            'username' => $this->user['username'] ?? null,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = ROOT_PATH . '/logs/api_activity.log';
        $logLine = json_encode($logEntry) . "\n";
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}

// Helper function for quick access
function getAPIAuth() {
    return APIAuth::getInstance();
}
?>
