<?php
declare(strict_types=1);
/**
 * API Response Standardizer
 * Ensures all API endpoints return consistent JSON responses
 */

class APIResponseStandardizer {
    private static function sendJSONResponse(array $data, int $httpCode = 200): void {
        // Clear any previous output
        if (ob_get_length()) {
            ob_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Set HTTP status code
        http_response_code($httpCode);
        
        // Send JSON response
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    public static function success(array $data = [], string $message = 'Success'): void {
        self::sendJSONResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }
    
    public static function error(string $message, int $code = 400, array $details = []): void {
        self::sendJSONResponse([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
                'details' => $details
            ],
            'timestamp' => date('c')
        ], $code);
    }
    
    public static function unauthorized(string $message = 'Unauthorized'): void {
        self::error($message, 401);
    }
    
    public static function forbidden(string $message = 'Forbidden'): void {
        self::error($message, 403);
    }
    
    public static function notFound(string $message = 'Not Found'): void {
        self::error($message, 404);
    }
    
    public static function serverError(string $message = 'Internal Server Error'): void {
        self::error($message, 500);
    }
    
    public static function paginated(array $data, int $total, int $page, int $limit, string $message = 'Data retrieved'): void {
        self::sendJSONResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ],
            'timestamp' => date('c')
        ]);
    }
}

// Helper function to catch any XML output and convert to JSON error
function catchXMLOutput(): void {
    if (ob_get_length()) {
        $output = ob_get_clean();
        
        // Check if output contains XML
        if (stripos($output, '<?xml') !== false) {
            APIResponseStandardizer::serverError('API returned XML instead of JSON. Please check the API endpoint.');
        } else {
            // Some other output, treat as error
            APIResponseStandardizer::serverError('Unexpected output from API.');
        }
    }
}

// Register shutdown function to catch any unexpected output
register_shutdown_function('catchXMLOutput');
?>
