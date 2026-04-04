<?php
declare(strict_types=1);
/**
 * Database Optimization & Index Management
 * Performance optimization for SPRIN application
 */

class DatabaseOptimizer {
    
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Recommended indexes for optimal performance
     */
    private $recommendedIndexes = [
        // Personil table indexes
        'personil' => [
            ['columns' => ['nrp'], 'name' => 'idx_nrp', 'type' => 'UNIQUE'],
            ['columns' => ['nama'], 'name' => 'idx_nama', 'type' => 'INDEX'],
            ['columns' => ['id_unsur'], 'name' => 'idx_unsur', 'type' => 'INDEX'],
            ['columns' => ['id_bagian'], 'name' => 'idx_bagian', 'type' => 'INDEX'],
            ['columns' => ['id_pangkat'], 'name' => 'idx_pangkat', 'type' => 'INDEX'],
            ['columns' => ['id_jabatan'], 'name' => 'idx_jabatan', 'type' => 'INDEX'],
            ['columns' => ['id_jenis_pegawai'], 'name' => 'idx_jenis_pegawai', 'type' => 'INDEX'],
            ['columns' => ['is_deleted'], 'name' => 'idx_is_deleted', 'type' => 'INDEX'],
            ['columns' => ['is_active'], 'name' => 'idx_is_active', 'type' => 'INDEX'],
            ['columns' => ['is_deleted', 'is_active'], 'name' => 'idx_status', 'type' => 'INDEX'],
        ],
        
        // Schedules table indexes
        'schedules' => [
            ['columns' => ['personil_id'], 'name' => 'idx_personil', 'type' => 'INDEX'],
            ['columns' => ['tanggal'], 'name' => 'idx_tanggal', 'type' => 'INDEX'],
            ['columns' => ['shift'], 'name' => 'idx_shift', 'type' => 'INDEX'],
            ['columns' => ['tanggal', 'shift'], 'name' => 'idx_tanggal_shift', 'type' => 'INDEX'],
            ['columns' => ['is_synced'], 'name' => 'idx_is_synced', 'type' => 'INDEX'],
        ],
        
        // Other tables
        'bagian' => [
            ['columns' => ['id_unsur'], 'name' => 'idx_bagian_unsur', 'type' => 'INDEX'],
        ],
        'jabatan' => [
            ['columns' => ['id_bagian'], 'name' => 'idx_jabatan_bagian', 'type' => 'INDEX'],
        ],
        'pangkat' => [
            ['columns' => ['level_pangkat'], 'name' => 'idx_level_pangkat', 'type' => 'INDEX'],
        ],
        'operations' => [
            ['columns' => ['tanggal_mulai'], 'name' => 'idx_tanggal_mulai', 'type' => 'INDEX'],
            ['columns' => ['status'], 'name' => 'idx_status', 'type' => 'INDEX'],
        ],
    ];
    
    /**
     * Optimize all tables
     */
    public function optimizeAll() {
        $results = [];
        
        foreach (array_keys($this->recommendedIndexes) as $table) {
            $results[$table] = $this->optimizeTable($table);
        }
        
        return $results;
    }
    
    /**
     * Optimize specific table
     */
    public function optimizeTable($table) {
        $results = [
            'table' => $table,
            'indexes_added' => [],
            'indexes_skipped' => [],
            'errors' => []
        ];
        
        if (!isset($this->recommendedIndexes[$table])) {
            return ['error' => 'No optimization recommendations for table: ' . $table];
        }
        
        foreach ($this->recommendedIndexes[$table] as $index) {
            try {
                if ($this->createIndex($table, $index)) {
                    $results['indexes_added'][] = $index['name'];
                } else {
                    $results['indexes_skipped'][] = $index['name'];
                }
            } catch (Exception $e) {
                $results['errors'][] = $index['name'] . ': ' . $e->getMessage();
            }
        }
        
        // Run OPTIMIZE TABLE
        try {
            $this->db->getConnection()->exec("OPTIMIZE TABLE $table");
            $results['optimized'] = true;
        } catch (Exception $e) {
            $results['optimized'] = false;
            $results['errors'][] = 'OPTIMIZE failed: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Create index if not exists
     */
    private function createIndex($table, $index) {
        $indexName = $index['name'];
        $columns = implode(', ', $index['columns']);
        $type = $index['type'] ?? 'INDEX';
        
        // Check if index already exists
        $sql = "SHOW INDEX FROM $table WHERE Key_name = :name";
        $existing = $this->db->fetchAll($sql, ['name' => $indexName]);
        
        if (!empty($existing)) {
            return false; // Index already exists
        }
        
        // Create index
        $sql = "CREATE $type $indexName ON $table ($columns)";
        $this->db->getConnection()->exec($sql);
        
        return true;
    }
    
    /**
     * Get table statistics
     */
    public function getTableStats() {
        $tables = array_keys($this->recommendedIndexes);
        $stats = [];
        
        foreach ($tables as $table) {
            $rowCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
            $tableSize = $this->db->fetchOne("
                SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                AND table_name = '$table'
            ");
            
            $stats[$table] = [
                'row_count' => $rowCount,
                'size_mb' => $tableSize['size_mb'] ?? 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get query performance analysis
     */
    public function analyzeQueryPerformance() {
        // Get slow queries from slow log (if enabled)
        try {
            $slowQueries = $this->db->fetchAll("
                SELECT 
                    sql_text,
                    COUNT(*) as exec_count,
                    AVG(query_time) as avg_time,
                    MAX(query_time) as max_time
                FROM mysql.slow_log 
                WHERE start_time > DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY sql_text
                HAVING avg_time > 1
                ORDER BY avg_time DESC
                LIMIT 10
            ");
            
            return ['slow_queries' => $slowQueries];
        } catch (Exception $e) {
            return ['error' => 'Slow query log not accessible: ' . $e->getMessage()];
        }
    }
    
    /**
     * Create performance monitoring table
     */
    public function createPerformanceTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                endpoint VARCHAR(255),
                method VARCHAR(10),
                execution_time DECIMAL(10,4),
                memory_usage INT,
                query_count INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_endpoint (endpoint),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB
        ";
        
        try {
            $this->db->getConnection()->exec($sql);
            return true;
        } catch (Exception $e) {
            error_log('Failed to create performance_logs: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log performance metrics
     */
    public function logPerformance($endpoint, $method, $executionTime, $memoryUsage, $queryCount) {
        try {
            $sql = "
                INSERT INTO performance_logs 
                (endpoint, method, execution_time, memory_usage, query_count) 
                VALUES (:endpoint, :method, :execution_time, :memory_usage, :query_count)
            ";
            
            $this->db->query($sql, [
                'endpoint' => $endpoint,
                'method' => $method,
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage,
                'query_count' => $queryCount
            ]);
        } catch (Exception $e) {
            error_log('Failed to log performance: ' . $e->getMessage());
        }
    }
    
    /**
     * Run all optimizations
     */
    public function runAllOptimizations() {
        $this->createPerformanceTable();
        return $this->optimizeAll();
    }
}

// CLI usage
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'optimize') {
    require_once __DIR__ . '/config.php';
    
    $optimizer = new DatabaseOptimizer();
    $results = $optimizer->runAllOptimizations();
    
    echo "Database Optimization Results:\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($results as $table => $result) {
        echo "Table: $table\n";
        if (isset($result['error'])) {
            echo "  Error: {$result['error']}\n";
        } else {
            echo "  Indexes Added: " . count($result['indexes_added']) . "\n";
            echo "  Indexes Skipped: " . count($result['indexes_skipped']) . "\n";
            echo "  Optimized: " . ($result['optimized'] ? 'Yes' : 'No') . "\n";
        }
        echo "\n";
    }
}

?>