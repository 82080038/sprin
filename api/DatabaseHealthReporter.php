<?php
/**
 * Database Health Reporter
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';

/**
 * Database Health Reporter Class
 */
class DatabaseHealthReporter {

    private $pdo;

    /**
     * Constructor
     */
    public function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';unix_socket=' . DB_SOCKET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get database health report
     */
    public function getHealthReport(): array {
        $report = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Check connection
        $report['checks']['connection'] = $this->checkConnection();

        // Check tables
        $report['checks']['tables'] = $this->checkTables();

        // Check indexes
        $report['checks']['indexes'] = $this->checkIndexes();

        // Check size
        $report['checks']['size'] = $this->checkDatabaseSize();

        // Check performance
        $report['checks']['performance'] = $this->checkPerformance();

        // Determine overall status
        $report['status'] = $this->determineOverallStatus($report['checks']);

        return $report;
    }

    /**
     * Check database connection
     */
    private function checkConnection(): array {
        try {
            $stmt = $this->pdo->query('SELECT 1');
            $stmt->fetch();

            return [
                'status' => 'healthy',
                'message' => 'Connection successful'
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check database tables
     */
    private function checkTables(): array {
        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'status' => 'healthy',
                'count' => count($tables),
                'tables' => $tables
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check database indexes
     */
    private function checkIndexes(): array {
        try {
            $stmt = $this->pdo->query('
                SELECT TABLE_NAME, INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
            ', [DB_NAME]);

            $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'healthy',
                'count' => count($indexes),
                'indexes' => $indexes
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check database size
     */
    private function checkDatabaseSize(): array {
        try {
            $stmt = $this->pdo->query('
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
            ', [DB_NAME]);

            $size = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'status' => 'healthy',
                'size_mb' => $size['size_mb'] ?? 0
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check database performance
     */
    private function checkPerformance(): array {
        try {
            $start = microtime(true);
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM personil');
            $stmt->fetch();
            $time = (microtime(true) - $start) * 1000;

            return [
                'status' => $time < 100 ? 'healthy' : 'slow',
                'query_time_ms' => round($time, 2)
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Determine overall status
     */
    private function determineOverallStatus(array $checks): string {
        foreach ($checks as $check) {
            if ($check['status'] === 'unhealthy') {
                return 'unhealthy';
            }
        }

        foreach ($checks as $check) {
            if ($check['status'] === 'slow') {
                return 'slow';
            }
        }

        return 'healthy';
    }
}

// Handle API requests
if (basename($_SERVER['PHP_SELF']) === 'DatabaseHealthReporter.php') {
    header('Content-Type: application/json');

    try {
        $reporter = new DatabaseHealthReporter();
        $report = $reporter->getHealthReport();
        echo json_encode($report);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
