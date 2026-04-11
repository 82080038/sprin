<?php
/**
 * CSRF Helper — Cross-Site Request Forgery Protection
 * Reusable CSRF validation for all APIs
 */

require_once __DIR__ . '/SessionManager.php';

class CSRFHelper {
    
    /**
     * Generate CSRF token and store in session
     */
    public static function generateToken() {
        SessionManager::start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }
    
    /**
     * Get current CSRF token (generate if not exists)
     */
    public static function getToken() {
        SessionManager::start();
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return self::generateToken();
        }
        // Regenerate if older than 1 hour
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            return self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * @return bool true if valid, false otherwise
     */
    public static function validate($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        
        SessionManager::start();
        
        if (empty($token)) {
            return false;
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Check token age (1 hour max)
        if (isset($_SESSION['csrf_token_time']) && time() - $_SESSION['csrf_token_time'] > 3600) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validate and return error response if invalid
     * @return array|null error array if invalid, null if valid
     */
    public static function validateWithError() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        SessionManager::start();
        
        $debugInfo = [
            'token_received' => !empty($token) ? substr($token, 0, 10) . '...' : 'EMPTY',
            'session_active' => SessionManager::isActive(),
            'session_id' => session_id(),
            'has_csrf_in_session' => isset($_SESSION['csrf_token']),
            'session_csrf_preview' => isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 10) . '...' : 'NONE',
        ];
        
        if (empty($token)) {
            http_response_code(403);
            return [
                'success' => false,
                'message' => 'CSRF token required',
                'debug' => $debugInfo,
                'csrf_expired' => true
            ];
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            http_response_code(403);
            return [
                'success' => false,
                'message' => 'CSRF token not found in session. Try refreshing the page.',
                'debug' => $debugInfo,
                'csrf_expired' => true
            ];
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            return [
                'success' => false,
                'message' => 'CSRF token mismatch. Possible session hijacking attempt.',
                'debug' => $debugInfo,
                'csrf_expired' => true
            ];
        }
        
        // Check token age
        if (isset($_SESSION['csrf_token_time']) && time() - $_SESSION['csrf_token_time'] > 3600) {
            http_response_code(403);
            return [
                'success' => false,
                'message' => 'CSRF token expired. Refresh the page and try again.',
                'debug' => $debugInfo,
                'csrf_expired' => true
            ];
        }
        
        return null; // Valid
    }
    
    /**
     * Apply CSRF validation for POST requests (skip read-only actions)
     * @param array $readOnlyActions actions that don't need CSRF
     */
    public static function applyProtection($readOnlyActions = []) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        if (in_array($action, $readOnlyActions)) {
            return;
        }
        
        $error = self::validateWithError();
        if ($error !== null) {
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;
        }
    }
}
