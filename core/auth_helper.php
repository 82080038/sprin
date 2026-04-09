<?php
/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SessionManager.php';

class AuthHelper {
    
    /**
     * Hash password using secure method
     */
    public static function hashPassword($password) {
        // Use Argon2ID if available, otherwise fallback to PASSWORD_DEFAULT
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        }
        
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure session token
     */
    public static function generateSessionToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate user session
     */
    public static function validateSession() {
        if (!SessionManager::isActive()) {
            return false;
        }
        
        return isset($_SESSION['logged_in']) && 
               $_SESSION['logged_in'] === true && 
               isset($_SESSION['username']) &&
               isset($_SESSION['login_time']) &&
               (time() - strtotime($_SESSION['login_time'])) < SESSION_LIFETIME;
    }
    
    /**
     * Get current user data
     */
    public static function getCurrentUser() {
        if (!self::validateSession()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['role'] ?? 'user',
            'login_time' => $_SESSION['login_time'] ?? ''
        ];
    }
    
    /**
     * Login user with credentials
     * Proper database authentication only
     */
    public static function login($username, $password) {
        // Input validation
        if (empty($username) || empty($password)) {
            return false;
        }
        
        // Sanitize input
        $username = self::sanitizeInput($username);
        
        try {
            require_once __DIR__ . '/Database.php';
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && self::verifyPassword($password, $user['password_hash'])) {
                SessionManager::start();
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = date('Y-m-d H:i:s');
                $_SESSION['session_token'] = self::generateSessionToken();
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            // Log error but don't expose details
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        SessionManager::destroy();
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    /**
     * Update user password
     */
    public static function updatePassword($username, $newPassword) {
        if (empty($username) || empty($newPassword)) {
            return false;
        }
        
        $username = self::sanitizeInput($username);
        $passwordHash = self::hashPassword($newPassword);
        
        try {
            require_once __DIR__ . '/Database.php';
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE username = ?");
            $result = $stmt->execute([$passwordHash, $username]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map('self::sanitizeInput', $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!SessionManager::isActive()) {
            SessionManager::start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (!SessionManager::isActive()) {
            return false;
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Require CSRF token validation
     */
    public static function requireCSRFToken() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!self::validateCSRFToken($token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token validation failed'
            ]);
            exit;
        }
    }
}
