<?php
/**
 * API Response Handler
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';

/**
 * API Response Class
 */
class APIResponse {

    /**
     * Send success response
     */
    public static function success($data = null, string $message = 'Success'): void {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Send error response
     */
    public static function error(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ]);
    }

    /**
     * Send validation error response
     */
    public static function validationError(array $errors): void {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
    }

    /**
     * Send not found response
     */
    public static function notFound(string $message = 'Resource not found'): void {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}
?>
