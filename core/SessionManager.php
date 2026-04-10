<?php
/**
 * Session Manager - Centralized session handling
 * Solves session conflicts and multiple session_start() issues
 */

class SessionManager {
    private static $started = false;
    
    /**
     * Start session safely - only once
     */
    public static function start() {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            // Set session parameters before start
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Allow non-HTTPS for development
            ini_set('session.use_strict_mode', 0); // Disable strict mode for compatibility
            ini_set('session.cookie_samesite', ''); // Empty for maximum compatibility
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            
            session_start();
            self::$started = true;
            
            // Only regenerate if needed (not on every request)
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            }
        }
    }
    
    /**
     * Check if session is active
     */
    public static function isActive() {
        return self::$started || session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        if (self::isActive()) {
            session_unset();
            session_destroy();
            self::$started = false;
        }
    }
    
    /**
     * Clear all session data
     */
    public static function clear() {
        if (self::isActive()) {
            $_SESSION = array();
        }
    }
}
?>
