<?php
declare(strict_types=1);
/**
 * API Response Standardizer - Consistent API Response Format
 */

class APIResponse {
    public static function success($data = null, $message = 'Success', $meta = []) {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
            'meta' => array_merge([
                'version' => '1.2.0-dev',
                'environment' => ENVIRONMENT
            ], $meta)
        ];
    }
    
    public static function error($message = 'Error', $code = 400, $details = []) {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'error_code' => $code,
            'details' => $details,
            'timestamp' => date('c'),
            'meta' => [
                'version' => '1.2.0-dev',
                'environment' => ENVIRONMENT
            ]
        ];
    }
    
    public static function paginated($data, $total, $page = 1, $per_page = 10, $message = 'Data retrieved') {
        return self::success($data, $message, [
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page),
                'has_next' => ($page * $per_page) < $total,
                'has_prev' => $page > 1
            ]
        ]);
    }
    
    public static function validateRequired($data, $required_fields) {
        $missing = [];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return self::error('Missing required fields: ' . implode(', ', $missing), 400, [
                'missing_fields' => $missing
            ]);
        }
        
        return null;
    }
    
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map(function($item) {
                return is_string($item) ? trim(htmlspecialchars($item, ENT_QUOTES, 'UTF-8')) : $item;
            }, $data);
        }
        return is_string($data) ? trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')) : $data;
    }
}

// Standard error handler for APIs
function handleAPIError($exception, $context = '') {
    error_log("API Error in $context: " . $exception->getMessage());
    
    if (ENVIRONMENT === 'development') {
        echo json_encode(APIResponse::error($exception->getMessage(), 500, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]));
    } else {
        echo json_encode(APIResponse::error('Internal server error', 500));
    }
    exit;
}
?>
