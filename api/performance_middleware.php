<?php
declare(strict_types=1);
/**
 * Performance Middleware
 * Provides performance optimizations for API endpoints
 */

class PerformanceMiddleware {
    private static $instance = null;
    private $startTime;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    /**
     * Enable caching
     */
    public function enableCaching($ttl = 300) {
        $cacheKey = $this->getCacheKey();
        $cacheFile = sys_get_temp_dir() . '/api_cache_' . md5($cacheKey);
        
        // Check if cached response exists and is valid
        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            
            if ($cacheData && (time() - $cacheData['timestamp']) < $ttl) {
                // Serve cached response
                header('X-Cache: HIT');
                header('Content-Type: application/json');
                echo $cacheData['response'];
                exit;
            }
        }
        
        // Start output buffering for caching
        ob_start(function($buffer) use ($cacheFile) {
            $cacheData = [
                'timestamp' => time(),
                'response' => $buffer
            ];
            file_put_contents($cacheFile, json_encode($cacheData));
            return $buffer;
        });
        
        header('X-Cache: MISS');
    }
    
    /**
     * Generate cache key based on request
     */
    private function getCacheKey() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $params = $_REQUEST;
        ksort($params);
        
        return $method . ':' . $uri . ':' . md5(serialize($params));
    }
    
    /**
     * Compress response
     */
    public function enableCompression() {
        if (extension_loaded('zlib') && !ob_get_level()) {
            ini_set('zlib.output_compression', 1);
            ini_set('zlib.output_compression_level', 6);
        }
    }
    
    /**
     * Set performance headers
     */
    public function setPerformanceHeaders() {
        // Connection: keep-alive
        header('Connection: keep-alive');
        
        // Cache control for static responses
        if ($this->isCacheable()) {
            $maxAge = 300; // 5 minutes
            header('Cache-Control: public, max-age=' . $maxAge);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        
        // ETag for conditional requests
        $etag = $this->generateETag();
        header('ETag: "' . $etag . '"');
        
        // Check if client has cached version
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }
    
    /**
     * Check if request is cacheable
     */
    private function isCacheable() {
        $method = $_SERVER['REQUEST_METHOD'];
        $cacheableMethods = ['GET', 'HEAD'];
        
        return in_array($method, $cacheableMethods) && !isset($_SESSION['user_id']);
    }
    
    /**
     * Generate ETag
     */
    private function generateETag() {
        $data = [
            $_SERVER['REQUEST_URI'],
            $_REQUEST,
            filemtime(__FILE__)
        ];
        
        return md5(serialize($data));
    }
    
    /**
     * Database query optimization
     */
    public function optimizeDatabaseQuery($sql, $params = []) {
        // Add LIMIT if not present for large datasets
        if (stripos($sql, 'LIMIT') === false && stripos($sql, 'SELECT') === 0) {
            $sql .= ' LIMIT 1000';
        }
        
        return [$sql, $params];
    }
    
    /**
     * Memory usage monitoring
     */
    public function checkMemoryUsage() {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit !== '-1') {
            $limitBytes = $this->parseMemoryLimit($memoryLimit);
            $usagePercent = ($memoryUsage / $limitBytes) * 100;
            
            if ($usagePercent > 80) {
                error_log("High memory usage: {$usagePercent}%");
            }
        }
        
        return $memoryUsage;
    }
    
    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit($limit) {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return (int) $limit;
        }
    }
    
    /**
     * Log performance metrics
     */
    public function logPerformance() {
        $executionTime = (microtime(true) - $this->startTime) * 1000;
        $memoryUsage = $this->checkMemoryUsage();
        
        $metrics = [
            'timestamp' => date('c'),
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_bytes' => $memoryUsage,
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'uri' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'response_code' => http_response_code()
        ];
        
        $logFile = ROOT_PATH . '/logs/performance.log';
        $logLine = json_encode($metrics) . "\n";
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Enable CORS for specific origins
     */
    public function enableCORS($allowedOrigins = ['*']) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit;
        }
    }
    
    /**
     * Minify JSON response
     */
    public function minifyJSON($data) {
        if (is_array($data) || is_object($data)) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Remove unnecessary whitespace
            $json = preg_replace('/\s+/', ' ', $json);
            $json = str_replace([' { ', ' }', ' [ ', ' ] ', ': ', ', '], ['{','}', '[',']',':',','], $json);
            
            return $json;
        }
        
        return $data;
    }
    
    /**
     * Async response support
     */
    public function enableAsyncResponse() {
        if (function_exists('fastcgi_finish_request')) {
            register_shutdown_function(function() {
                if (!headers_sent()) {
                    header('Connection: close');
                    header('Content-Length: ' . ob_get_length());
                }
                
                ob_end_flush();
                flush();
                fastcgi_finish_request();
            });
        }
    }
}

// Helper function for quick access
function getPerformanceMiddleware() {
    return PerformanceMiddleware::getInstance();
}
?>
