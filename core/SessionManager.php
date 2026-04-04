<?php
declare(strict_types=1);
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
            ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            
            session_start();
            self::$started = true;
            
            // Regenerate session ID for security
            if (!isset($_SESSION['regenerated'])) {
                session_regenerate_id(true);
                $_SESSION['regenerated'] = true;
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
