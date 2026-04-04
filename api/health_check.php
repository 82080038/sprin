<?php
declare(strict_types=1);
/**
 * Health Check API
 * Monitor F2E/E2E services status
 */

require_once '../core/config.php';

class HealthCheck {
    private $checks = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    /**
     * Run all health checks
     */
    public function runAllChecks() {
        $this->checkDatabase();
        $this->checkAPIEndpoints();
        $this->checkF2EServices();
        $this->checkE2EServices();
        $this->checkFileSystem();
        $this->checkDependencies();
        
        return $this->generateReport();
    }
    
    /**
     * Check database connectivity
     */
    private function checkDatabase() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                $this->checks['database'] = [
                    'status' => 'error',
                    'message' => 'Database connection failed: ' . $conn->connect_error,
                    'response_time' => 0
                ];
                return;
            }
            
            // Test query
            $start = microtime(true);
            $result = $conn->query("SELECT 1");
            $responseTime = (microtime(true) - $start) * 1000;
            
            if ($result) {
                $this->checks['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful',
                    'response_time' => round($responseTime, 2)
                ];
            } else {
                $this->checks['database'] = [
                    'status' => 'error',
                    'message' => 'Database query failed',
                    'response_time' => round($responseTime, 2)
                ];
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            $this->checks['database'] = [
                'status' => 'error',
                'message' => 'Database exception: ' . $e->getMessage(),
                'response_time' => 0
            ];
        }
    }
    
    /**
     * Check API endpoints
     */
    private function checkAPIEndpoints() {
        $endpoints = [
            '/api/personil_list.php',
            '/api/unsur_crud.php',
            '/api/bagian_crud.php',
            '/api/jabatan_crud.php'
        ];
        
        $results = [];
        $totalTime = 0;
        
        foreach ($endpoints as $endpoint) {
            $start = microtime(true);
            
            $postData = '';
            switch ($endpoint) {
                case '/api/unsur_crud.php':
                    $postData = http_build_query(['action' => 'get_unsur_list']);
                    break;
                case '/api/bagian_crud.php':
                    $postData = http_build_query(['action' => 'get_bagian_list']);
                    break;
                case '/api/jabatan_crud.php':
                    $postData = http_build_query(['action' => 'get_jabatan_list']);
                    break;
                default:
                    $postData = http_build_query(['per_page' => 1]);
            }
            
            $ch = curl_init(BASE_URL . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $time = (microtime(true) - $start) * 1000;
            
            curl_close($ch);
            
            $results[$endpoint] = [
                'status' => $httpCode === 200 ? 'healthy' : 'error',
                'http_code' => $httpCode,
                'response_time' => round($time, 2)
            ];
            
            $totalTime += $time;
        }
        
        $avgTime = count($results) > 0 ? $totalTime / count($results) : 0;
        
        $this->checks['api_endpoints'] = [
            'status' => $this->calculateOverallStatus($results),
            'message' => count($results) . ' endpoints checked',
            'response_time' => round($avgTime, 2),
            'details' => $results
        ];
    }
    
    /**
     * Check F2E services
     */
    private function checkF2EServices() {
        $f2eClientPath = ROOT_PATH . '/public/assets/js/f2e-client.js';
        
        if (file_exists($f2eClientPath)) {
            $fileSize = filesize($f2eClientPath);
            $lastModified = filemtime($f2eClientPath);
            
            $this->checks['f2e_services'] = [
                'status' => 'healthy',
                'message' => 'F2E client file exists and accessible',
                'file_size' => $fileSize,
                'last_modified' => date('Y-m-d H:i:s', $lastModified)
            ];
        } else {
            $this->checks['f2e_services'] = [
                'status' => 'error',
                'message' => 'F2E client file not found'
            ];
        }
    }
    
    /**
     * Check E2E services
     */
    private function checkE2EServices() {
        $e2eClientPath = ROOT_PATH . '/api/E2EClient.php';
        
        if (file_exists($e2eClientPath)) {
            // Test E2E client functionality
            try {
                require_once $e2eClientPath;
                
                $start = microtime(true);
                $client = new E2EClient();
                $testResult = $client->get('/api/personil_list.php', ['per_page' => 1]);
                $responseTime = (microtime(true) - $start) * 1000;
                
                if ($testResult && isset($testResult['success'])) {
                    $this->checks['e2e_services'] = [
                        'status' => 'healthy',
                        'message' => 'E2E client functional',
                        'response_time' => round($responseTime, 2),
                        'test_result' => $testResult['success'] ? 'pass' : 'fail'
                    ];
                } else {
                    $this->checks['e2e_services'] = [
                        'status' => 'error',
                        'message' => 'E2E client test failed',
                        'response_time' => round($responseTime, 2)
                    ];
                }
                
            } catch (Exception $e) {
                $this->checks['e2e_services'] = [
                    'status' => 'error',
                    'message' => 'E2E client error: ' . $e->getMessage()
                ];
            }
        } else {
            $this->checks['e2e_services'] = [
                'status' => 'error',
                'message' => 'E2E client file not found'
            ];
        }
    }
    
    /**
     * Check file system permissions
     */
    private function checkFileSystem() {
        $paths = [
            '../api/' => 'readable',
            '../public/assets/js/' => 'readable',
            '../database/' => 'readable',
            '../logs/' => 'writable'
        ];
        
        $results = [];
        $allGood = true;
        
        foreach ($paths as $path => $requiredPermission) {
            $exists = file_exists($path);
            $readable = is_readable($path);
            $writable = is_writable($path);
            
            $status = 'healthy';
            if (!$exists) {
                $status = 'error';
                $allGood = false;
            } elseif ($requiredPermission === 'writable' && !$writable) {
                $status = 'warning';
                $allGood = false;
            } elseif ($requiredPermission === 'readable' && !$readable) {
                $status = 'error';
                $allGood = false;
            }
            
            $results[$path] = [
                'status' => $status,
                'exists' => $exists,
                'readable' => $readable,
                'writable' => $writable,
                'required' => $requiredPermission
            ];
        }
        
        $this->checks['filesystem'] = [
            'status' => $allGood ? 'healthy' : 'warning',
            'message' => 'File system permissions checked',
            'details' => $results
        ];
    }
    
    /**
     * Check PHP dependencies
     */
    private function checkDependencies() {
        $required = [
            'mysqli' => false,
            'curl' => false,
            'json' => false,
            'mbstring' => false
        ];
        
        $missing = [];
        
        foreach ($required as $ext => $loaded) {
            if (extension_loaded($ext)) {
                $required[$ext] = true;
            } else {
                $missing[] = $ext;
            }
        }
        
        $this->checks['dependencies'] = [
            'status' => empty($missing) ? 'healthy' : 'warning',
            'message' => empty($missing) ? 'All required extensions loaded' : 'Missing extensions: ' . implode(', ', $missing),
            'extensions' => $required
        ];
    }
    
    /**
     * Calculate overall status from results
     */
    private function calculateOverallStatus($results) {
        $hasError = false;
        $hasWarning = false;
        
        foreach ($results as $result) {
            if ($result['status'] === 'error') {
                $hasError = true;
            } elseif ($result['status'] === 'warning') {
                $hasWarning = true;
            }
        }
        
        if ($hasError) return 'error';
        if ($hasWarning) return 'warning';
        return 'healthy';
    }
    
    /**
     * Generate health report
     */
    private function generateReport() {
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        $overallStatus = $this->calculateOverallStatus($this->checks);
        
        return [
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'response_time' => round($totalTime, 2),
            'checks' => $this->checks,
            'summary' => [
                'total_checks' => count($this->checks),
                'healthy' => count(array_filter($this->checks, fn($c) => $c['status'] === 'healthy')),
                'warnings' => count(array_filter($this->checks, fn($c) => $c['status'] === 'warning')),
                'errors' => count(array_filter($this->checks, fn($c) => $c['status'] === 'error'))
            ]
        ];
    }
}

// Handle request
header('Content-Type: application/json');

$healthCheck = new HealthCheck();

if (isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'check', FILTER_SANITIZE_STRING)) && method_exists($healthCheck, 'check' . ucfirst(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'check', FILTER_SANITIZE_STRING)))) {
    $method = 'check' . ucfirst(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'check', FILTER_SANITIZE_STRING));
    $healthCheck->$method();
    echo json_encode($healthCheck->generateReport());
} else {
    echo json_encode($healthCheck->runAllChecks());
}
?>
