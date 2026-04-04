<?php
declare(strict_types=1);
/**
 * Standardized API Response Helper
 */

class ApiResponse {
    
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_ERROR = 500;
    
    /**
     * Send standardized JSON response
     */
    public static function json($success, $data = null, $message = '', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'request_id' => uniqid('req_', true)
        ];
        
        // Remove null data from response
        if ($data === null) {
            unset($response['data']);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Success response
     */
    public static function success($data = null, $message = 'Operation successful') {
        self::json(true, $data, $message, self::HTTP_OK);
    }
    
    /**
     * Created response
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        self::json(true, $data, $message, self::HTTP_CREATED);
    }
    
    /**
     * Error response
     */
    public static function error($message = 'An error occurred', $statusCode = 400, $data = null) {
        self::json(false, $data, $message, $statusCode);
    }
    
    /**
     * Validation error
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::json(false, ['errors' => $errors], $message, self::HTTP_BAD_REQUEST);
    }
    
    /**
     * Not found
     */
    public static function notFound($message = 'Resource not found') {
        self::json(false, null, $message, self::HTTP_NOT_FOUND);
    }
    
    /**
     * Unauthorized
     */
    public static function unauthorized($message = 'Unauthorized access') {
        self::json(false, null, $message, self::HTTP_UNAUTHORIZED);
    }
    
    /**
     * Server error
     */
    public static function serverError($message = 'Internal server error') {
        self::json(false, null, $message, self::HTTP_INTERNAL_ERROR);
    }
    
    /**
     * Paginated response
     */
    public static function paginated($data, $page, $limit, $total, $message = 'Data retrieved successfully') {
        $totalPages = ceil($total / $limit);
        
        $response = [
            'items' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_items' => (int)$total,
                'total_pages' => (int)$totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
        
        self::json(true, $response, $message, self::HTTP_OK);
    }
    
    /**
     * Handle API authentication
     */
    public static function checkApiAuth() {
        session_start();
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            self::unauthorized('Authentication required');
        }
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::validationError(
                array_fill_keys($missing, 'This field is required'),
                'Missing required fields: ' . implode(', ', $missing)
            );
        }
    }
    
    /**
     * Get JSON input
     */
    public static function getJsonInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::error('Invalid JSON format', self::HTTP_BAD_REQUEST);
        }
        
        return $data ?? [];
    }
}

?>