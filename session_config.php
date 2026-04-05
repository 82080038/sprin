<?php
/**
 * Session Configuration
 */

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.cookie_samesite', 'Lax');

// Session garbage collection
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
?>
