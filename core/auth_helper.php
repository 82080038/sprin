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
     * Simple bypass for development mode
     */
    public static function login($username, $password) {
        // Simple bypass for development
        if ($username === 'bagops' && $password === 'admin123') {
            SessionManager::start();
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = 0; // System user
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            $_SESSION['session_token'] = self::generateSessionToken();
            
            return true;
        }
        
        // Try database users first
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
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                return true;
            }
        } catch (Exception $e) {
            // Database not available or users table doesn't exist, fallback to hardcoded
            error_log("Database login failed, using fallback: " . $e->getMessage());
        }
        
        // Fallback to hardcoded credentials (backward compatibility)
        $valid_credentials = [
            'username' => 'bagops',
            'password_hash' => '$argon2id$v=19$m=65536,t=4,p=3$Wk1TdTdGTGw3OEJzZTZUOQ$CGQbP9WVhKKsHiHx6hzzKc4OXpa/0kwAu5WqlW0E6gk' // admin123
        ];
        
        if ($username === $valid_credentials['username']) {
            if (self::verifyPassword($password, $valid_credentials['password_hash'])) {
                SessionManager::start();
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = 0; // System user
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                $_SESSION['login_time'] = date('Y-m-d H:i:s');
                $_SESSION['session_token'] = self::generateSessionToken();
                
                return true;
            }
        }
        
        return false;
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
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generate CSRF token (for future implementation)
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}
