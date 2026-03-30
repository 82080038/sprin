<?php
/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/config.php';

class AuthHelper {
    
    /**
     * Hash password using secure method
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
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
     * Validate session
     */
    public static function validateSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check session lifetime
        if (isset($_SESSION['login_time'])) {
            $login_time = strtotime($_SESSION['login_time']);
            $current_time = time();
            $session_duration = $current_time - $login_time;
            
            if ($session_duration > SESSION_LIFETIME) {
                self::logout();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Login user with credentials
     */
    public static function login($username, $password) {
        // For now, use hardcoded credentials (in production, use database)
        $valid_credentials = [
            'username' => 'bagops',
            'password_hash' => self::hashPassword('admin123')
        ];
        
        if ($username === $valid_credentials['username']) {
            if (self::verifyPassword($password, $valid_credentials['password_hash'])) {
                session_start();
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
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
