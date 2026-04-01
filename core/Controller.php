<?php
/**
 * Base Controller Class - Part of MVC Architecture
 * All controllers should extend this class
 */

abstract class Controller {
    
    protected $db;
    protected $request;
    protected $response = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->request = $this->getRequestData();
    }
    
    /**
     * Get request data (GET/POST/JSON)
     */
    protected function getRequestData() {
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];
        
        switch ($method) {
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    $data = json_decode($json, true) ?? [];
                } else {
                    $data = $_POST;
                }
                break;
            case 'PUT':
            case 'DELETE':
                $json = file_get_contents('php://input');
                $data = json_decode($json, true) ?? [];
                break;
        }
        
        return $this->sanitizeInput($data);
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Send JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Success response
     */
    protected function success($data = null, $message = 'Operation successful') {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }
    
    /**
     * Error response
     */
    protected function error($message = 'An error occurred', $statusCode = 400, $data = null) {
        $this->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ], $statusCode);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Load view
     */
    protected function view($viewName, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View not found: {$viewName}");
        }
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data = null) {
        $data = $data ?? $this->request;
        $missing = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->error(
                'Missing required fields: ' . implode(', ', $missing),
                400,
                ['missing_fields' => $missing]
            );
        }
        
        return true;
    }
    
    /**
     * Get specific input value
     */
    protected function input($key, $default = null) {
        return $this->request[$key] ?? $default;
    }
    
    /**
     * Set flash message
     */
    protected function flash($message, $type = 'info') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
    }
    
    /**
     * Get and clear flash message
     */
    protected function getFlash() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash;
        }
        
        return null;
    }
}
