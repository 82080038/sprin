<?php
/**
 * Load Testing Suite for SPRIN Production Scale
 * Tests system performance under various load conditions
 */

class LoadTestSuite {
    private $pdo;
    private $testResults = [];
    private $baseUrl;
    private $concurrentUsers = 0;
    private $testDuration = 60; // seconds
    private $rampUpTime = 10; // seconds
    
    public function __construct($pdo, $baseUrl = 'http://localhost/sprin') {
        $this->pdo = $pdo;
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Run comprehensive load test suite
     */
    public function runLoadTestSuite() {
        echo "=== SPRIN Load Test Suite ===\n";
        echo "Starting at: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Test 1: Database Connection Performance
        $this->testDatabaseConnections();
        
        // Test 2: API Endpoint Performance
        $this->testAPIEndpoints();
        
        // Test 3: Concurrent User Simulation
        $this->testConcurrentUsers();
        
        // Test 4: Mobile API Performance
        $this->testMobileAPI();
        
        // Test 5: Notification System Load
        $this->testNotificationSystem();
        
        // Test 6: Report Generation Load
        $this->testReportGeneration();
        
        // Test 7: Database Query Performance
        $this->testDatabaseQueries();
        
        // Generate comprehensive report
        $this->generateLoadTestReport();
        
        return $this->testResults;
    }
    
    /**
     * Test database connection performance
     */
    private function testDatabaseConnections() {
        echo "Testing Database Connection Performance...\n";
        
        $results = [
            'test_name' => 'Database Connections',
            'start_time' => microtime(true),
            'metrics' => []
        ];
        
        // Test connection pooling
        $connectionTimes = [];
        for ($i = 0; $i < 100; $i++) {
            $start = microtime(true);
            try {
                $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                    DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $connectionTimes[] = microtime(true) - $start;
                $pdo = null; // Close connection
            } catch (Exception $e) {
                $connectionTimes[] = -1; // Error
            }
        }
        
        $successfulConnections = array_filter($connectionTimes, function($time) { return $time > 0; });
        
        $results['metrics'] = [
            'total_attempts' => 100,
            'successful_connections' => count($successfulConnections),
            'success_rate' => round(count($successfulConnections) / 100 * 100, 2),
            'avg_connection_time' => round(array_sum($successfulConnections) / count($successfulConnections) * 1000, 2) . 'ms',
            'max_connection_time' => round(max($successfulConnections) * 1000, 2) . 'ms',
            'min_connection_time' => round(min($successfulConnections) * 1000, 2) . 'ms'
        ];
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "Database Connections: {$results['metrics']['success_rate']}% success rate, {$results['metrics']['avg_connection_time']} avg time\n\n";
    }
    
    /**
     * Test API endpoint performance
     */
    private function testAPIEndpoints() {
        echo "Testing API Endpoint Performance...\n";
        
        $endpoints = [
            'personil_list' => '/api/unified-api.php?resource=personil&action=list',
            'bagian_list' => '/api/unified-api.php?resource=bagian&action=list',
            'stats_dashboard' => '/api/unified-api.php?resource=stats&action=dashboard',
            'fatigue_stats' => '/api/unified-api.php?resource=fatigue&action=get_stats',
            'analytics_dashboard' => '/api/unified-api.php?resource=analytics&action=get_dashboard'
        ];
        
        $results = [
            'test_name' => 'API Endpoints',
            'start_time' => microtime(true),
            'endpoints' => []
        ];
        
        foreach ($endpoints as $name => $endpoint) {
            $responseTimes = [];
            $successCount = 0;
            
            for ($i = 0; $i < 50; $i++) {
                $start = microtime(true);
                $response = $this->makeAPIRequest($endpoint);
                $responseTime = microtime(true) - $start;
                
                if ($response && $response['success']) {
                    $responseTimes[] = $responseTime;
                    $successCount++;
                }
            }
            
            $results['endpoints'][$name] = [
                'endpoint' => $endpoint,
                'total_requests' => 50,
                'successful_requests' => $successCount,
                'success_rate' => round($successCount / 50 * 100, 2),
                'avg_response_time' => $responseTimes ? round(array_sum($responseTimes) / count($responseTimes) * 1000, 2) . 'ms' : 'N/A',
                'max_response_time' => $responseTimes ? round(max($responseTimes) * 1000, 2) . 'ms' : 'N/A',
                'min_response_time' => $responseTimes ? round(min($responseTimes) * 1000, 2) . 'ms' : 'N/A'
            ];
            
            echo "  $name: {$results['endpoints'][$name]['success_rate']}% success, {$results['endpoints'][$name]['avg_response_time']} avg\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Test concurrent users simulation
     */
    private function testConcurrentUsers() {
        echo "Testing Concurrent User Simulation...\n";
        
        $results = [
            'test_name' => 'Concurrent Users',
            'start_time' => microtime(true),
            'scenarios' => []
        ];
        
        $scenarios = [
            ['users' => 10, 'duration' => 30],
            ['users' => 25, 'duration' => 30],
            ['users' => 50, 'duration' => 30],
            ['users' => 100, 'duration' => 30]
        ];
        
        foreach ($scenarios as $scenario) {
            echo "  Testing {$scenario['users']} concurrent users for {$scenario['duration']}s...\n";
            
            $scenarioResults = $this->simulateConcurrentUsers($scenario['users'], $scenario['duration']);
            
            $results['scenarios'][] = [
                'concurrent_users' => $scenario['users'],
                'duration' => $scenario['duration'],
                'total_requests' => $scenarioResults['total_requests'],
                'successful_requests' => $scenarioResults['successful_requests'],
                'success_rate' => round($scenarioResults['successful_requests'] / $scenarioResults['total_requests'] * 100, 2),
                'avg_response_time' => round($scenarioResults['avg_response_time'] * 1000, 2) . 'ms',
                'requests_per_second' => round($scenarioResults['total_requests'] / $scenario['duration'], 2),
                'errors' => $scenarioResults['errors']
            ];
            
            echo "    Success: {$results['scenarios'][count($results['scenarios'])-1]['success_rate']}%, ";
            echo "RPS: {$results['scenarios'][count($results['scenarios'])-1]['requests_per_second']}\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Test mobile API performance
     */
    private function testMobileAPI() {
        echo "Testing Mobile API Performance...\n";
        
        $results = [
            'test_name' => 'Mobile API',
            'start_time' => microtime(true),
            'endpoints' => []
        ];
        
        $mobileEndpoints = [
            'mobile_login' => '/api/mobile_api.php?action=login',
            'mobile_dashboard' => '/api/mobile_api.php?action=dashboard',
            'my_schedules' => '/api/mobile_api.php?action=my_schedules',
            'my_fatigue' => '/api/mobile_api.php?action=my_fatigue',
            'my_notifications' => '/api/mobile_api.php?action=my_notifications'
        ];
        
        foreach ($mobileEndpoints as $name => $endpoint) {
            $responseTimes = [];
            $successCount = 0;
            
            for ($i = 0; $i < 30; $i++) {
                $start = microtime(true);
                
                // Add mobile API headers
                $headers = [
                    'X-API-Key: SPRIN_MOBILE_2026',
                    'Content-Type: application/x-www-form-urlencoded'
                ];
                
                $postData = '';
                if ($name === 'mobile_login') {
                    $postData = 'username=testuser&password=testpass&device_token=test123';
                }
                
                $response = $this->makeAPIRequest($endpoint, $postData, $headers);
                $responseTime = microtime(true) - $start;
                
                if ($response && $response['success']) {
                    $responseTimes[] = $responseTime;
                    $successCount++;
                }
            }
            
            $results['endpoints'][$name] = [
                'endpoint' => $endpoint,
                'total_requests' => 30,
                'successful_requests' => $successCount,
                'success_rate' => round($successCount / 30 * 100, 2),
                'avg_response_time' => $responseTimes ? round(array_sum($responseTimes) / count($responseTimes) * 1000, 2) . 'ms' : 'N/A'
            ];
            
            echo "  $name: {$results['endpoints'][$name]['success_rate']}% success, {$results['endpoints'][$name]['avg_response_time']} avg\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Test notification system load
     */
    private function testNotificationSystem() {
        echo "Testing Notification System Load...\n";
        
        $results = [
            'test_name' => 'Notification System',
            'start_time' => microtime(true),
            'scenarios' => []
        ];
        
        $scenarios = [
            ['notifications' => 100, 'concurrent' => 5],
            ['notifications' => 500, 'concurrent' => 10],
            ['notifications' => 1000, 'concurrent' => 20]
        ];
        
        foreach ($scenarios as $scenario) {
            echo "  Testing {$scenario['notifications']} notifications with {$scenario['concurrent']} concurrent processes...\n";
            
            $scenarioResults = $this->testNotificationLoad($scenario['notifications'], $scenario['concurrent']);
            
            $results['scenarios'][] = [
                'notifications_count' => $scenario['notifications'],
                'concurrent_processes' => $scenario['concurrent'],
                'successful_notifications' => $scenarioResults['successful'],
                'failed_notifications' => $scenarioResults['failed'],
                'success_rate' => round($scenarioResults['successful'] / $scenario['notifications'] * 100, 2),
                'avg_generation_time' => round($scenarioResults['avg_generation_time'] * 1000, 2) . 'ms',
                'notifications_per_second' => round($scenarioResults['successful'] / $scenarioResults['total_time'], 2)
            ];
            
            echo "    Success: {$results['scenarios'][count($results['scenarios'])-1]['success_rate']}%, ";
            echo "NPS: {$results['scenarios'][count($results['scenarios'])-1]['notifications_per_second']}\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Test report generation load
     */
    private function testReportGeneration() {
        echo "Testing Report Generation Load...\n";
        
        $results = [
            'test_name' => 'Report Generation',
            'start_time' => microtime(true),
            'reports' => []
        ];
        
        $reportTypes = [
            'personnel_summary' => 'Personnel Summary Report',
            'attendance_report' => 'Attendance Report',
            'fatigue_analysis' => 'Fatigue Analysis Report',
            'certification_compliance' => 'Certification Compliance Report'
        ];
        
        foreach ($reportTypes as $type => $name) {
            echo "  Testing $name...\n";
            
            $generationTimes = [];
            $successCount = 0;
            
            for ($i = 0; $i < 10; $i++) {
                $start = microtime(true);
                
                try {
                    $reportingService = new \ReportingService($this->pdo);
                    $result = $reportingService->generateReport($type, ['format' => 'pdf']);
                    
                    $generationTime = microtime(true) - $start;
                    
                    if ($result['success']) {
                        $generationTimes[] = $generationTime;
                        $successCount++;
                    }
                } catch (Exception $e) {
                    // Log error but continue
                }
            }
            
            $results['reports'][$type] = [
                'report_name' => $name,
                'total_attempts' => 10,
                'successful_generations' => $successCount,
                'success_rate' => round($successCount / 10 * 100, 2),
                'avg_generation_time' => $generationTimes ? round(array_sum($generationTimes) / count($generationTimes), 2) . 's' : 'N/A',
                'max_generation_time' => $generationTimes ? round(max($generationTimes), 2) . 's' : 'N/A',
                'min_generation_time' => $generationTimes ? round(min($generationTimes), 2) . 's' : 'N/A'
            ];
            
            echo "    Success: {$results['reports'][$type]['success_rate']}%, ";
            echo "Avg: {$results['reports'][$type]['avg_generation_time']}\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Test database query performance
     */
    private function testDatabaseQueries() {
        echo "Testing Database Query Performance...\n";
        
        $results = [
            'test_name' => 'Database Queries',
            'start_time' => microtime(true),
            'queries' => []
        ];
        
        $queries = [
            'personil_count' => "SELECT COUNT(*) FROM personil WHERE is_active = 1 AND is_deleted = 0",
            'personil_with_relations' => "
                SELECT p.*, pk.nama_pangkat, b.nama_bagian, u.nama_unsur
                FROM personil p
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                LEFT JOIN unsur u ON u.id = b.id_unsur
                WHERE p.is_active = 1 AND p.is_deleted = 0
                LIMIT 100
            ",
            'schedules_with_attendance' => "
                SELECT s.*, p.nama, pa.status as attendance_status
                FROM schedules s
                JOIN personil p ON p.nrp = s.personil_id
                LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
                WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                LIMIT 100
            ",
            'fatigue_aggregate' => "
                SELECT 
                    AVG(fatigue_score) as avg_score,
                    COUNT(CASE WHEN fatigue_level = 'critical' THEN 1 END) as critical_count,
                    COUNT(*) as total_records
                FROM fatigue_tracking
                WHERE tracking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ",
            'notifications_complex' => "
                SELECT n.*, p.nama as personil_name
                FROM notifications n
                LEFT JOIN personil p ON p.nrp = n.target_personil
                WHERE n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY n.created_at DESC
                LIMIT 50
            "
        ];
        
        foreach ($queries as $name => $sql) {
            $queryTimes = [];
            
            for ($i = 0; $i < 20; $i++) {
                $start = microtime(true);
                
                try {
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $queryTimes[] = microtime(true) - $start;
                } catch (Exception $e) {
                    $queryTimes[] = -1; // Error
                }
            }
            
            $successfulQueries = array_filter($queryTimes, function($time) { return $time > 0; });
            
            $results['queries'][$name] = [
                'total_executions' => 20,
                'successful_executions' => count($successfulQueries),
                'success_rate' => round(count($successfulQueries) / 20 * 100, 2),
                'avg_query_time' => $successfulQueries ? round(array_sum($successfulQueries) / count($successfulQueries) * 1000, 2) . 'ms' : 'N/A',
                'max_query_time' => $successfulQueries ? round(max($successfulQueries) * 1000, 2) . 'ms' : 'N/A',
                'min_query_time' => $successfulQueries ? round(min($successfulQueries) * 1000, 2) . 'ms' : 'N/A'
            ];
            
            echo "  $name: {$results['queries'][$name]['success_rate']}% success, {$results['queries'][$name]['avg_query_time']} avg\n";
        }
        
        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);
        
        $this->testResults[] = $results;
        echo "\n";
    }
    
    /**
     * Simulate concurrent users
     */
    private function simulateConcurrentUsers($userCount, $duration) {
        $totalRequests = 0;
        $successfulRequests = 0;
        $responseTimes = [];
        $errors = [];
        
        $endTime = time() + $duration;
        $processes = [];
        
        // Create child processes for concurrent simulation
        for ($i = 0; $i < $userCount; $i++) {
            $pid = pcntl_fork();
            
            if ($pid == -1) {
                // Fork failed
                continue;
            } elseif ($pid == 0) {
                // Child process
                $this->simulateUserRequests($endTime);
                exit(0);
            } else {
                // Parent process
                $processes[] = $pid;
            }
        }
        
        // Wait for all child processes
        foreach ($processes as $pid) {
            pcntl_waitpid($pid, $status);
        }
        
        // Collect results from temporary files
        for ($i = 0; $i < $userCount; $i++) {
            $tempFile = sys_get_temp_dir() . "/load_test_$i.json";
            if (file_exists($tempFile)) {
                $userResults = json_decode(file_get_contents($tempFile), true);
                $totalRequests += $userResults['total_requests'];
                $successfulRequests += $userResults['successful_requests'];
                $responseTimes = array_merge($responseTimes, $userResults['response_times']);
                $errors = array_merge($errors, $userResults['errors']);
                unlink($tempFile);
            }
        }
        
        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'avg_response_time' => $responseTimes ? array_sum($responseTimes) / count($responseTimes) : 0,
            'errors' => $errors
        ];
    }
    
    /**
     * Simulate individual user requests
     */
    private function simulateUserRequests($endTime) {
        $userId = getmypid();
        $results = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'response_times' => [],
            'errors' => []
        ];
        
        $endpoints = [
            '/api/unified-api.php?resource=personil&action=list',
            '/api/unified-api.php?resource=stats&action=dashboard',
            '/api/unified-api.php?resource=fatigue&action=get_stats'
        ];
        
        while (time() < $endTime) {
            $endpoint = $endpoints[array_rand($endpoints)];
            $start = microtime(true);
            
            $response = $this->makeAPIRequest($endpoint);
            $responseTime = microtime(true) - $start;
            
            $results['total_requests']++;
            $results['response_times'][] = $responseTime;
            
            if ($response && $response['success']) {
                $results['successful_requests']++;
            } else {
                $results['errors'][] = "Failed request to $endpoint";
            }
            
            usleep(rand(100000, 500000)); // 0.1-0.5 seconds delay
        }
        
        // Save results to temporary file
        $tempFile = sys_get_temp_dir() . "/load_test_$userId.json";
        file_put_contents($tempFile, json_encode($results));
    }
    
    /**
     * Test notification load
     */
    private function testNotificationLoad($notificationCount, $concurrentProcesses) {
        $successful = 0;
        $failed = 0;
        $generationTimes = [];
        $startTime = microtime(true);
        
        // Create sample notifications
        $notifications = [];
        for ($i = 0; $i < $notificationCount; $i++) {
            $notifications[] = [
                'type' => 'test_notification',
                'title' => "Test Notification $i",
                'message' => "This is test notification number $i",
                'target_personil' => '123456',
                'priority' => 'medium',
                'delivery_methods' => ['in_app']
            ];
        }
        
        // Process notifications in batches
        $batchSize = ceil($notificationCount / $concurrentProcesses);
        
        for ($i = 0; $i < $concurrentProcesses; $i++) {
            $batch = array_slice($notifications, $i * $batchSize, $batchSize);
            
            $start = microtime(true);
            
            foreach ($batch as $notification) {
                try {
                    // Simulate notification creation
                    $stmt = $this->pdo->prepare("
                        INSERT INTO notifications 
                        (notification_type, title, message, target_personil, priority_level, delivery_methods, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $notification['type'],
                        $notification['title'],
                        $notification['message'],
                        $notification['target_personil'],
                        $notification['priority'],
                        json_encode($notification['delivery_methods']),
                        'load_test'
                    ]);
                    
                    $successful++;
                } catch (Exception $e) {
                    $failed++;
                }
            }
            
            $generationTimes[] = microtime(true) - $start;
        }
        
        $totalTime = microtime(true) - $startTime;
        
        return [
            'successful' => $successful,
            'failed' => $failed,
            'avg_generation_time' => $generationTimes ? array_sum($generationTimes) / count($generationTimes) : 0,
            'total_time' => $totalTime
        ];
    }
    
    /**
     * Make API request
     */
    private function makeAPIRequest($endpoint, $postData = '', $headers = []) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return ['success' => true, 'data' => $data, 'http_code' => $httpCode];
        }
        
        return ['success' => false, 'error' => "HTTP $httpCode", 'response' => $response];
    }
    
    /**
     * Generate comprehensive load test report
     */
    private function generateLoadTestReport() {
        echo "=== Load Test Report ===\n";
        echo "Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        
        $report = "# SPRIN Load Test Report\n\n";
        $report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($this->testResults as $test) {
            $report .= "## {$test['test_name']}\n\n";
            $report .= "**Duration:** {$test['duration']}s\n\n";
            
            if (isset($test['metrics'])) {
                $report .= "### Metrics\n";
                foreach ($test['metrics'] as $key => $value) {
                    $report .= "- **" . ucwords(str_replace('_', ' ', $key)) . ":** $value\n";
                }
                $report .= "\n";
            }
            
            if (isset($test['endpoints'])) {
                $report .= "### Endpoints\n";
                foreach ($test['endpoints'] as $name => $endpoint) {
                    $report .= "- **$name:**\n";
                    foreach ($endpoint as $key => $value) {
                        if ($key !== 'endpoint') {
                            $report .= "  - " . ucwords(str_replace('_', ' ', $key)) . ": $value\n";
                        }
                    }
                    $report .= "\n";
                }
            }
            
            if (isset($test['scenarios'])) {
                $report .= "### Scenarios\n";
                foreach ($test['scenarios'] as $scenario) {
                    $report .= "- **{$scenario['concurrent_users']} Users ({$scenario['duration']}s):**\n";
                    foreach ($scenario as $key => $value) {
                        if ($key !== 'concurrent_users' && $key !== 'duration') {
                            $report .= "  - " . ucwords(str_replace('_', ' ', $key)) . ": $value\n";
                        }
                    }
                    $report .= "\n";
                }
            }
            
            if (isset($test['reports'])) {
                $report .= "### Reports\n";
                foreach ($test['reports'] as $name => $reportData) {
                    $report .= "- **$name:**\n";
                    foreach ($reportData as $key => $value) {
                        if ($key !== 'report_name') {
                            $report .= "  - " . ucwords(str_replace('_', ' ', $key)) . ": $value\n";
                        }
                    }
                    $report .= "\n";
                }
            }
            
            if (isset($test['queries'])) {
                $report .= "### Queries\n";
                foreach ($test['queries'] as $name => $query) {
                    $report .= "- **$name:**\n";
                    foreach ($query as $key => $value) {
                        if ($key !== 'total_executions') {
                            $report .= "  - " . ucwords(str_replace('_', ' ', $key)) . ": $value\n";
                        }
                    }
                    $report .= "\n";
                }
            }
        }
        
        // Performance recommendations
        $report .= "## Performance Recommendations\n\n";
        
        // Analyze results and provide recommendations
        $recommendations = $this->analyzePerformanceResults();
        
        foreach ($recommendations as $recommendation) {
            $report .= "- $recommendation\n";
        }
        
        // Save report to file
        $reportFile = '../file/load_test_report_' . date('Y-m-d_H-i-s') . '.md';
        file_put_contents($reportFile, $report);
        
        echo "Report saved to: $reportFile\n";
        echo "\n" . $report;
    }
    
    /**
     * Analyze performance results and provide recommendations
     */
    private function analyzePerformanceResults() {
        $recommendations = [];
        
        // Analyze database connections
        $dbTest = $this->findTestResult('Database Connections');
        if ($dbTest && $dbTest['metrics']['success_rate'] < 95) {
            $recommendations[] = "Consider implementing connection pooling - Database connection success rate is below 95%";
        }
        
        // Analyze API response times
        $apiTest = $this->findTestResult('API Endpoints');
        if ($apiTest) {
            foreach ($apiTest['endpoints'] as $endpoint) {
                $avgTime = (float)str_replace('ms', '', $endpoint['avg_response_time']);
                if ($avgTime > 1000) {
                    $recommendations[] = "Optimize {$endpoint['endpoint']} - Response time is above 1 second";
                }
            }
        }
        
        // Analyze concurrent user performance
        $concurrentTest = $this->findTestResult('Concurrent Users');
        if ($concurrentTest) {
            foreach ($concurrentTest['scenarios'] as $scenario) {
                if ($scenario['success_rate'] < 90) {
                    $recommendations[] = "Scale up infrastructure - Success rate drops below 90% at {$scenario['concurrent_users']} concurrent users";
                }
                
                $rps = $scenario['requests_per_second'];
                if ($rps < 50 && $scenario['concurrent_users'] >= 50) {
                    $recommendations[] = "Implement caching - Low requests per second ($rps) under load";
                }
            }
        }
        
        // Analyze mobile API
        $mobileTest = $this->findTestResult('Mobile API');
        if ($mobileTest) {
            foreach ($mobileTest['endpoints'] as $endpoint) {
                if ($endpoint['success_rate'] < 95) {
                    $recommendations[] = "Optimize mobile authentication - {$endpoint['endpoint']} has low success rate";
                }
            }
        }
        
        // Analyze notification system
        $notificationTest = $this->findTestResult('Notification System');
        if ($notificationTest) {
            foreach ($notificationTest['scenarios'] as $scenario) {
                if ($scenario['success_rate'] < 95) {
                    $recommendations[] = "Implement notification queue - Success rate drops under load";
                }
                
                $nps = $scenario['notifications_per_second'];
                if ($nps < 100) {
                    $recommendations[] = "Optimize notification processing - Low notifications per second ($nps)";
                }
            }
        }
        
        // Analyze report generation
        $reportTest = $this->findTestResult('Report Generation');
        if ($reportTest) {
            foreach ($reportTest['reports'] as $report) {
                $avgTime = (float)str_replace('s', '', $report['avg_generation_time']);
                if ($avgTime > 10) {
                    $recommendations[] = "Optimize {$report['report_name']} - Generation time is above 10 seconds";
                }
            }
        }
        
        // Analyze database queries
        $queryTest = $this->findTestResult('Database Queries');
        if ($queryTest) {
            foreach ($queryTest['queries'] as $query) {
                $avgTime = (float)str_replace('ms', '', $query['avg_query_time']);
                if ($avgTime > 500) {
                    $recommendations[] = "Optimize database query - {$query['query_name']} is above 500ms";
                }
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "System performance is within acceptable limits";
        }
        
        return $recommendations;
    }
    
    /**
     * Find test result by name
     */
    private function findTestResult($testName) {
        foreach ($this->testResults as $test) {
            if ($test['test_name'] === $testName) {
                return $test;
            }
        }
        return null;
    }
}

// Run load test if executed directly
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../core/config.php';
    
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $loadTest = new LoadTestSuite($pdo);
    $results = $loadTest->runLoadTestSuite();
    
    echo "\nLoad test completed. Check the generated report for detailed results.\n";
}
?>
