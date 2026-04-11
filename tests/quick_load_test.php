<?php
/**
 * Quick Load Test Runner
 * Simple performance testing for SPRIN system
 */

require_once __DIR__ . '/../core/config.php';

class QuickLoadTest {
    private $pdo;
    private $baseUrl;
    private $results = [];
    
    public function __construct($baseUrl = 'http://localhost/sprin') {
        $this->baseUrl = $baseUrl;
        $this->pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    
    /**
     * Run quick performance tests
     */
    public function runQuickTests() {
        echo "=== Quick Load Test ===\n";
        echo "Testing basic system performance...\n\n";
        
        $this->testBasicConnectivity();
        $this->testDatabasePerformance();
        $this->testAPIPerformance();
        $this->testMobileAPI();
        $this->generateQuickReport();
        
        return $this->results;
    }
    
    /**
     * Test basic connectivity
     */
    private function testBasicConnectivity() {
        echo "Testing Basic Connectivity...\n";
        
        $start = microtime(true);
        
        // Test database connection
        try {
            $stmt = $this->pdo->query("SELECT 1");
            $dbResult = $stmt->fetchColumn();
            $dbSuccess = $dbResult === '1';
        } catch (Exception $e) {
            $dbSuccess = false;
        }
        
        // Test web server
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $webSuccess = $httpCode === 200;
        
        $this->results['connectivity'] = [
            'database_success' => $dbSuccess,
            'web_success' => $webSuccess,
            'response_time' => round((microtime(true) - $start) * 1000, 2) . 'ms'
        ];
        
        echo "Database: " . ($dbSuccess ? "OK" : "FAIL") . "\n";
        echo "Web Server: " . ($webSuccess ? "OK" : "FAIL") . "\n";
        echo "Response Time: {$this->results['connectivity']['response_time']}\n\n";
    }
    
    /**
     * Test database performance
     */
    private function testDatabasePerformance() {
        echo "Testing Database Performance...\n";
        
        $queries = [
            'simple_count' => "SELECT COUNT(*) FROM personil WHERE is_active = 1",
            'complex_join' => "
                SELECT p.nama, pk.nama_pangkat, b.nama_bagian
                FROM personil p
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                WHERE p.is_active = 1
                LIMIT 50
            ",
            'aggregate_query' => "
                SELECT 
                    COUNT(*) as total,
                    AVG(YEAR(CURDATE()) - YEAR(tanggal_lahir)) as avg_age
                FROM personil
                WHERE is_active = 1
            "
        ];
        
        $results = [];
        
        foreach ($queries as $name => $sql) {
            $times = [];
            
            for ($i = 0; $i < 5; $i++) {
                $start = microtime(true);
                $stmt = $this->pdo->query($sql);
                $stmt->fetchAll(PDO::FETCH_ASSOC);
                $times[] = microtime(true) - $start;
            }
            
            $avgTime = array_sum($times) / count($times);
            $results[$name] = round($avgTime * 1000, 2) . 'ms';
            
            echo "$name: {$results[$name]}\n";
        }
        
        $this->results['database'] = $results;
        echo "\n";
    }
    
    /**
     * Test API performance
     */
    private function testAPIPerformance() {
        echo "Testing API Performance...\n";
        
        $endpoints = [
            'personil' => '/api/unified-api.php?resource=personil&action=list',
            'bagian' => '/api/unified-api.php?resource=bagian&action=list',
            'stats' => '/api/unified-api.php?resource=stats&action=dashboard'
        ];
        
        $results = [];
        
        foreach ($endpoints as $name => $endpoint) {
            $times = [];
            $successCount = 0;
            
            for ($i = 0; $i < 3; $i++) {
                $start = microtime(true);
                
                $ch = curl_init($this->baseUrl . $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $time = microtime(true) - $start;
                $times[] = $time;
                
                if ($httpCode === 200) {
                    $successCount++;
                }
            }
            
            $avgTime = array_sum($times) / count($times);
            $successRate = ($successCount / 3) * 100;
            
            $results[$name] = [
                'avg_time' => round($avgTime * 1000, 2) . 'ms',
                'success_rate' => round($successRate, 1) . '%'
            ];
            
            echo "$name: {$results[$name]['avg_time']}, {$results[$name]['success_rate']} success\n";
        }
        
        $this->results['api'] = $results;
        echo "\n";
    }
    
    /**
     * Test mobile API
     */
    private function testMobileAPI() {
        echo "Testing Mobile API...\n";
        
        $headers = [
            'X-API-Key: SPRIN_MOBILE_2026',
            'Content-Type: application/x-www-form-urlencoded'
        ];
        
        $postData = 'username=test&password=test&device_token=test123';
        
        $times = [];
        $successCount = 0;
        
        for ($i = 0; $i < 3; $i++) {
            $start = microtime(true);
            
            $ch = curl_init($this->baseUrl . '/api/mobile_api.php?action=login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $time = microtime(true) - $start;
            $times[] = $time;
            
            if ($httpCode === 200) {
                $successCount++;
            }
        }
        
        $avgTime = array_sum($times) / count($times);
        $successRate = ($successCount / 3) * 100;
        
        $this->results['mobile_api'] = [
            'avg_time' => round($avgTime * 1000, 2) . 'ms',
            'success_rate' => round($successRate, 1) . '%'
        ];
        
        echo "Mobile Login: {$this->results['mobile_api']['avg_time']}, {$this->results['mobile_api']['success_rate']} success\n\n";
    }
    
    /**
     * Generate quick report
     */
    private function generateQuickReport() {
        echo "=== Quick Test Results ===\n";
        
        // Overall health check
        $healthScore = 100;
        
        if (!$this->results['connectivity']['database_success']) $healthScore -= 30;
        if (!$this->results['connectivity']['web_success']) $healthScore -= 30;
        
        // Check database performance
        foreach ($this->results['database'] as $time) {
            $ms = (float)str_replace('ms', '', $time);
            if ($ms > 500) $healthScore -= 10;
        }
        
        // Check API performance
        foreach ($this->results['api'] as $endpoint) {
            $rate = (float)str_replace('%', '', $endpoint['success_rate']);
            if ($rate < 100) $healthScore -= 5;
            
            $ms = (float)str_replace('ms', '', $endpoint['avg_time']);
            if ($ms > 1000) $healthScore -= 5;
        }
        
        echo "Overall Health Score: $healthScore/100\n";
        
        if ($healthScore >= 90) {
            echo "Status: EXCELLENT - System is performing well\n";
        } elseif ($healthScore >= 70) {
            echo "Status: GOOD - Minor performance issues detected\n";
        } elseif ($healthScore >= 50) {
            echo "Status: FAIR - Performance needs attention\n";
        } else {
            echo "Status: POOR - Significant performance issues\n";
        }
        
        // Save results
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'health_score' => $healthScore,
            'results' => $this->results
        ];
        
        $reportFile = '../file/quick_test_results.json';
        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
        
        echo "\nDetailed results saved to: $reportFile\n";
    }
}

// Run test if executed directly
if (php_sapi_name() === 'cli') {
    $test = new QuickLoadTest();
    $test->runQuickTests();
} else {
    // Web interface
    header('Content-Type: application/json');
    
    $test = new QuickLoadTest();
    $results = $test->runQuickTests();
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
