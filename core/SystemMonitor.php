<?php
declare(strict_types=1);
/**
 * System Monitor for SPRIN
 * Performance monitoring, health checks, and alerts
 */

class SystemMonitor {
    
    private $db;
    private $logPath;
    private $thresholds;
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
        
        $this->logPath = __DIR__ . '/../logs/monitor/';
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // Performance thresholds
        $this->thresholds = [
            'db_query_time' => 1.0,        // seconds
            'memory_usage' => 128 * 1024 * 1024,  // 128MB
            'disk_usage' => 90,            // percentage
            'response_time' => 3.0         // seconds
        ];
        
        $this->createMonitorTables();
    }
    
    /**
     * Create monitoring tables
     */
    private function createMonitorTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS system_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_type VARCHAR(50) NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,4),
                metric_unit VARCHAR(20),
                threshold_value DECIMAL(15,4),
                alert_triggered BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_type (metric_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Run health check
     */
    public function healthCheck() {
        $checks = [
            'database' => $this->checkDatabase(),
            'disk_space' => $this->checkDiskSpace(),
            'memory' => $this->checkMemory(),
            'php_extensions' => $this->checkPHPExtensions(),
            'permissions' => $this->checkPermissions()
        ];
        
        $allHealthy = true;
        foreach ($checks as $check) {
            if (!$check['status']) {
                $allHealthy = false;
                break;
            }
        }
        
        return [
            'overall_status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => date('c'),
            'checks' => $checks
        ];
    }
    
    /**
     * Check database health
     */
    private function checkDatabase() {
        try {
            $start = microtime(true);
            $this->db->query("SELECT 1");
            $responseTime = microtime(true) - $start;
            
            // Check connection count
            $connections = $this->db->fetchOne("SHOW STATUS LIKE 'Threads_connected'");
            $maxConnections = $this->db->fetchOne("SHOW VARIABLES LIKE 'max_connections'");
            
            // Get database size
            $dbSize = $this->db->fetchOne("
                SELECT SUM(data_length + index_length) / 1024 / 1024 as size_mb 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            return [
                'status' => $responseTime < $this->thresholds['db_query_time'],
                'response_time_ms' => round($responseTime * 1000, 2),
                'connections' => [
                    'active' => $connections['Value'] ?? 0,
                    'max' => $maxConnections['Value'] ?? 0
                ],
                'size_mb' => round($dbSize['size_mb'] ?? 0, 2),
                'message' => $responseTime < $this->thresholds['db_query_time'] 
                    ? 'Database is healthy' 
                    : 'Database response time is slow'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
                'message' => 'Database connection failed'
            ];
        }
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace() {
        $total = disk_total_space(__DIR__);
        $free = disk_free_space(__DIR__);
        $used = $total - $free;
        $percentage = ($used / $total) * 100;
        
        return [
            'status' => $percentage < $this->thresholds['disk_usage'],
            'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            'used_gb' => round($used / 1024 / 1024 / 1024, 2),
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'usage_percent' => round($percentage, 2),
            'message' => $percentage < $this->thresholds['disk_usage']
                ? 'Disk space is healthy'
                : 'Warning: High disk usage'
        ];
    }
    
    /**
     * Check memory usage
     */
    private function checkMemory() {
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsage = memory_get_usage(true);
        $peakUsage = memory_get_peak_usage(true);
        
        return [
            'status' => $memoryUsage < $this->thresholds['memory_usage'],
            'limit_mb' => round($memoryLimit / 1024 / 1024, 2),
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($peakUsage / 1024 / 1024, 2),
            'usage_percent' => round(($memoryUsage / $memoryLimit) * 100, 2),
            'message' => $memoryUsage < $this->thresholds['memory_usage']
                ? 'Memory usage is healthy'
                : 'Warning: High memory usage'
        ];
    }
    
    /**
     * Check PHP extensions
     */
    private function checkPHPExtensions() {
        $required = [
            'pdo', 'pdo_mysql', 'json', 'mbstring', 'gd', 'zip'
        ];
        
        $missing = [];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        return [
            'status' => empty($missing),
            'required' => $required,
            'missing' => $missing,
            'message' => empty($missing)
                ? 'All required extensions are installed'
                : 'Missing extensions: ' . implode(', ', $missing)
        ];
    }
    
    /**
     * Check file permissions
     */
    private function checkPermissions() {
        $paths = [
            __DIR__ . '/../logs/' => 'read_write',
            __DIR__ . '/../cache/' => 'read_write',
            __DIR__ . '/../exports/' => 'read_write',
            __DIR__ . '/../backups/' => 'read_write'
        ];
        
        $issues = [];
        foreach ($paths as $path => $required) {
            if (!is_dir($path)) {
                $issues[] = "Directory missing: {$path}";
            } elseif (!is_writable($path) && $required === 'read_write') {
                $issues[] = "Directory not writable: {$path}";
            }
        }
        
        return [
            'status' => empty($issues),
            'paths' => array_keys($paths),
            'issues' => $issues,
            'message' => empty($issues)
                ? 'All directories have correct permissions'
                : 'Permission issues found'
        ];
    }
    
    /**
     * Record performance metric
     */
    public function recordMetric($type, $name, $value, $unit = null) {
        $threshold = $this->thresholds[$name] ?? null;
        $alertTriggered = $threshold ? ($value > $threshold) : false;
        
        $sql = "
            INSERT INTO system_metrics 
            (metric_type, metric_name, metric_value, metric_unit, threshold_value, alert_triggered, created_at)
            VALUES (:type, :name, :value, :unit, :threshold, :alert, NOW())
        ";
        
        $this->db->query($sql, [
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'unit' => $unit,
            'threshold' => $threshold,
            'alert' => $alertTriggered
        ]);
        
        if ($alertTriggered) {
            $this->sendAlert("{$name} exceeded threshold: {$value} {$unit}");
        }
        
        return true;
    }
    
    /**
     * Get performance metrics
     */
    public function getMetrics($type = null, $hours = 24) {
        $whereClause = $type ? "AND metric_type = :type" : "";
        
        $sql = "
            SELECT 
                metric_type,
                metric_name,
                AVG(metric_value) as avg_value,
                MAX(metric_value) as max_value,
                MIN(metric_value) as min_value,
                COUNT(*) as count
            FROM system_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
            {$whereClause}
            GROUP BY metric_type, metric_name
        ";
        
        $params = ['hours' => $hours];
        if ($type) $params['type'] = $type;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Send alert
     */
    private function sendAlert($message) {
        $alert = [
            'timestamp' => date('c'),
            'message' => $message,
            'severity' => 'warning'
        ];
        
        // Log to file
        $logFile = $this->logPath . 'alerts_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($alert) . "\n", FILE_APPEND | LOCK_EX);
        
        // Could also send email, SMS, or Slack notification here
    }
    
    /**
     * Get system information
     */
    public function getSystemInfo() {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'database' => $this->getDatabaseInfo(),
            'memory' => [
                'limit' => ini_get('memory_limit'),
                'usage' => memory_get_usage(true)
            ],
            'timezone' => date_default_timezone_get(),
            'locale' => setlocale(LC_ALL, 0)
        ];
    }
    
    /**
     * Get database information
     */
    private function getDatabaseInfo() {
        try {
            $version = $this->db->fetchOne("SELECT VERSION() as version");
            
            return [
                'version' => $version['version'] ?? 'Unknown',
                'type' => 'MySQL/MariaDB'
            ];
        } catch (Exception $e) {
            return [
                'version' => 'Unknown',
                'type' => 'MySQL/MariaDB',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit($limit) {
        if ($limit === '-1') return PHP_INT_MAX;
        
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Generate system report
     */
    public function generateReport() {
        return [
            'generated_at' => date('c'),
            'health' => $this->healthCheck(),
            'system_info' => $this->getSystemInfo(),
            'metrics' => $this->getMetrics(null, 24),
            'recent_alerts' => $this->getRecentAlerts()
        ];
    }
    
    /**
     * Get recent alerts
     */
    private function getRecentAlerts($hours = 24) {
        $logFile = $this->logPath . 'alerts_' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $alerts = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff = strtotime("-{$hours} hours");
        
        foreach ($lines as $line) {
            $alert = json_decode($line, true);
            if ($alert && strtotime($alert['timestamp']) >= $cutoff) {
                $alerts[] = $alert;
            }
        }
        
        return array_slice($alerts, -50); // Last 50 alerts
    }
}

?>