<?php
/**
 * Configuration for POLRES Samosir Application
 */

// Start output buffering to prevent header issues
ob_start();

// Base URL Configuration
define('BASE_URL', 'http://localhost/sprint');
define('API_BASE_URL', BASE_URL . '/api');
define('API_VERSION', 'v1');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/public/assets');
define('DOCS_PATH', ROOT_PATH . '/docs');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bagops');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Application Configuration
define('APP_NAME', 'POLRES Samosir Management System');
define('APP_VERSION', '1.0.0');
define('ENVIRONMENT', 'development');

// Security Configuration
define('JWT_SECRET', 'your-secret-key-here');
define('SESSION_LIFETIME', 3600); // 1 hour

// API Configuration
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TIMEOUT', 30); // seconds

// Debug Configuration - ON for development
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Environment-based logging (optional)
if (ENVIRONMENT !== 'development') {
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php_error.log');
}

// Initialize Error Handler
require_once __DIR__ . '/error_handler.php';
ErrorHandler::init();

// Custom Error Handler for Production
if (ENVIRONMENT !== 'development') {
    set_error_handler(function($severity, $message, $file, $line) {
        // Log all errors but don't display them
        $error_type = match($severity) {
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        };
        
        $log_message = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error_type,
            $message,
            $file,
            $line
        );
        
        error_log($log_message);
        
        // Don't show errors to users in production
        return true;
    });
}

// Helper Functions
function getBaseUrl() {
    return BASE_URL;
}

function getApiBaseUrl($version = API_VERSION) {
    return API_BASE_URL . '/' . $version;
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $path;
}

// URL Generation Functions
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function api_url($path = '', $version = API_VERSION) {
    return getApiBaseUrl($version) . '/' . ltrim($path, '/');
}

function asset_url($path = '') {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

// Exception Handler for Production
if (ENVIRONMENT !== 'development') {
    set_exception_handler(function($exception) {
        $log_message = sprintf(
            "[%s] FATAL ERROR: %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        error_log($log_message);
        
        // Show user-friendly error page
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            include __DIR__ . '/error_pages/500.php';
        } else {
            echo '<h1>System Error</h1><p>Please try again later.</p>';
        }
        exit;
    });
}

// End of configuration
// Note: Response headers moved to individual pages to avoid conflicts

?>
