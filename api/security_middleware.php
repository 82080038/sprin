<?php
declare(strict_types=1);
/**
 * Security Middleware
 * Provides security enhancements for API endpoints
 */

class SecurityMiddleware {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Apply security headers
     */
    public function applySecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'");
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // HSTS (only in HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Rate limiting
     */
    public function checkRateLimit($limit = 100, $window = 3600) {
        $clientIp = $this->getClientIp();
        $key = 'rate_limit_' . md5($clientIp);
        
        // Simple file-based rate limiting
        $file = sys_get_temp_dir() . '/' . $key;
        $data = [];
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }
        
        $now = time();
        $windowStart = $now - $window;
        
        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Check if limit exceeded
        if (count($data) >= $limit) {
            header('HTTP/1.0 429 Too Many Requests');
            header('Retry-After: ' . ($window - ($now - end($data))));
            echo json_encode([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'error_code' => 429
            ]);
            exit;
        }
        
        // Add current request
        $data[] = $now;
        file_put_contents($file, json_encode($data));
    }
    
    /**
     * Input sanitization
     */
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRF($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token',
                'error_code' => 403
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get client IP
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'event' => $event,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $logFile = ROOT_PATH . '/logs/security.log';
        $logLine = json_encode($logEntry) . "\n";
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Validate file upload
     */
    public function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            throw new Exception('File too large');
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Invalid file type');
            }
        }
        
        return true;
    }
    
    /**
     * Encrypt sensitive data
     */
    public function encrypt($data, $key = null) {
        $key = $key ?: (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-key');
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decrypt($encryptedData, $key = null) {
        $key = $key ?: (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-key');
        $method = 'AES-256-CBC';
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, $method, $key, 0, $iv);
    }
}

// Helper function for quick access
function getSecurityMiddleware() {
    return SecurityMiddleware::getInstance();
}
?>
