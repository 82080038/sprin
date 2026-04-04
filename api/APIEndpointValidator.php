<?php
declare(strict_types=1);
/**
 * API Endpoint Validator
 * Comprehensive validation of all API endpoints for consistency and functionality
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class APIEndpointValidator {
    private $pdo;
    private $results = [];
    private $errors = [];
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Validate all API endpoints
     */
    public function validateAll() {
        $this->results = [];
        $this->errors = [];
        
        // Test Personil CRUD
        $this->testPersonilCRUD();
        
        // Test Unsur CRUD
        $this->testUnsurCRUD();
        
        // Test Bagian CRUD
        $this->testBagianCRUD();
        
        // Test Jabatan CRUD
        $this->testJabatanCRUD();
        
        // Test Export API
        $this->testExportAPI();
        
        // Test Stats API
        $this->testStatsAPI();
        
        return $this->generateReport();
    }
    
    /**
     * Test Personil CRUD operations
     */
    private function testPersonilCRUD() {
        $endpoint = '/api/personil_crud.php';
        
        // Test GET personil list
        $this->testEndpoint($endpoint, [
            'action' => 'get_personil',
            'id' => 1
        ], 'GET personil detail');
        
        // Test CREATE personil
        $this->testEndpoint($endpoint, [
            'action' => 'create_personil',
            'nama' => 'Test Personil',
            'nrp' => 'TEST999',
            'JK' => 'L'
        ], 'CREATE personil');
        
        // Test UPDATE personil
        $this->testEndpoint($endpoint, [
            'action' => 'update_personil',
            'id' => 1,
            'nama' => 'Updated Personil',
            'nrp' => 'TEST999'
        ], 'UPDATE personil');
        
        // Test DELETE personil
        $this->testEndpoint($endpoint, [
            'action' => 'delete_personil',
            'id' => 999999,
            'alasan' => 'Test deletion'
        ], 'DELETE personil');
        
        // Test dropdown data
        $this->testEndpoint($endpoint, [
            'action' => 'get_dropdown_data'
        ], 'GET dropdown data');
    }
    
    /**
     * Test Unsur CRUD operations
     */
    private function testUnsurCRUD() {
        $endpoint = '/api/unsur_crud.php';
        
        // Test GET unsur list
        $this->testEndpoint($endpoint, [
            'action' => 'get_unsur_list'
        ], 'GET unsur list');
        
        // Test CREATE unsur
        $this->testEndpoint($endpoint, [
            'action' => 'create_unsur',
            'nama_unsur' => 'Test Unsur',
            'kode_unsur' => 'TEST',
            'urutan' => 999
        ], 'CREATE unsur');
        
        // Test UPDATE unsur
        $this->testEndpoint($endpoint, [
            'action' => 'update_unsur',
            'id' => 1,
            'nama_unsur' => 'Updated Unsur',
            'kode_unsur' => 'TEST',
            'urutan' => 1
        ], 'UPDATE unsur');
        
        // Test DELETE unsur
        $this->testEndpoint($endpoint, [
            'action' => 'delete_unsur',
            'id' => 999999
        ], 'DELETE unsur');
        
        // Test stats
        $this->testEndpoint($endpoint, [
            'action' => 'get_unsur_stats',
            'include_details' => 'true'
        ], 'GET unsur stats');
    }
    
    /**
     * Test Bagian CRUD operations
     */
    private function testBagianCRUD() {
        $endpoint = '/api/bagian_crud.php';
        
        // Test GET bagian list
        $this->testEndpoint($endpoint, [
            'action' => 'get_bagian_list'
        ], 'GET bagian list');
        
        // Test CREATE bagian
        $this->testEndpoint($endpoint, [
            'action' => 'create_bagian',
            'nama_bagian' => 'Test Bagian',
            'id_unsur' => 1,
            'urutan' => 999
        ], 'CREATE bagian');
        
        // Test UPDATE bagian
        $this->testEndpoint($endpoint, [
            'action' => 'update_bagian',
            'id' => 1,
            'nama_bagian' => 'Updated Bagian',
            'id_unsur' => 1,
            'urutan' => 1
        ], 'UPDATE bagian');
        
        // Test MOVE bagian
        $this->testEndpoint($endpoint, [
            'action' => 'move_bagian',
            'id' => 1,
            'new_unsur_id' => 1,
            'new_urutan' => 1
        ], 'MOVE bagian');
        
        // Test DELETE bagian
        $this->testEndpoint($endpoint, [
            'action' => 'delete_bagian',
            'id' => 999999
        ], 'DELETE bagian');
    }
    
    /**
     * Test Jabatan CRUD operations
     */
    private function testJabatanCRUD() {
        $endpoint = '/api/jabatan_crud.php';
        
        // Test GET jabatan list
        $this->testEndpoint($endpoint, [
            'action' => 'get_jabatan_list'
        ], 'GET jabatan list');
        
        // Test CREATE jabatan
        $this->testEndpoint($endpoint, [
            'action' => 'create_jabatan',
            'nama_jabatan' => 'Test Jabatan',
            'id_unsur' => 1
        ], 'CREATE jabatan');
        
        // Test UPDATE jabatan
        $this->testEndpoint($endpoint, [
            'action' => 'update_jabatan',
            'id' => 1,
            'nama_jabatan' => 'Updated Jabatan',
            'id_unsur' => 1
        ], 'UPDATE jabatan');
        
        // Test DELETE jabatan
        $this->testEndpoint($endpoint, [
            'action' => 'delete_jabatan',
            'id' => 999999
        ], 'DELETE jabatan');
    }
    
    /**
     * Test Export API
     */
    private function testExportAPI() {
        $endpoint = '/api/export_personil.php';
        
        // Test CSV export
        $this->testEndpoint($endpoint, [
            'format' => 'csv',
            'per_page' => 1
        ], 'CSV Export', 'GET');
        
        // Test Excel export
        $this->testEndpoint($endpoint, [
            'format' => 'excel',
            'per_page' => 1
        ], 'Excel Export', 'GET');
    }
    
    /**
     * Test Stats API
     */
    private function testStatsAPI() {
        $endpoint = '/api/unsur_stats.php';
        
        // Test stats endpoint
        $this->testEndpoint($endpoint, [
            'details' => 'true'
        ], 'GET unsur stats', 'GET');
    }
    
    /**
     * Test individual endpoint
     */
    private function testEndpoint($endpoint, $data, $test_name, $method = 'POST') {
        $url = BASE_URL . $endpoint;
        
        try {
            $ch = curl_init();
            
            if ($method === 'GET') {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Test-Request: 1'
                ]
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                $this->errors[] = [
                    'endpoint' => $endpoint,
                    'test' => $test_name,
                    'error' => "CURL Error: $error",
                    'data_sent' => $data
                ];
                return false;
            }
            
            // Parse response
            $response_data = json_decode($response, true);
            
            if (!$response_data) {
                $this->errors[] = [
                    'endpoint' => $endpoint,
                    'test' => $test_name,
                    'error' => 'Invalid JSON response',
                    'http_code' => $http_code,
                    'response' => substr($response, 0, 200)
                ];
                return false;
            }
            
            // Validate response structure
            $validation_result = $this->validateResponseStructure($response_data);
            
            $this->results[] = [
                'endpoint' => $endpoint,
                'test' => $test_name,
                'method' => $method,
                'http_code' => $http_code,
                'success' => $response_data['success'] ?? false,
                'validation' => $validation_result,
                'response_time' => microtime(true),
                'data_sent' => $data
            ];
            
            return true;
            
        } catch (Exception $e) {
            $this->errors[] = [
                'endpoint' => $endpoint,
                'test' => $test_name,
                'error' => $e->getMessage(),
                'data_sent' => $data
            ];
            return false;
        }
    }
    
    /**
     * Validate response structure
     */
    private function validateResponseStructure($response) {
        $issues = [];
        
        // Check required fields
        if (!isset($response['success'])) {
            $issues[] = 'Missing success field';
        }
        
        if (!isset($response['message']) && !isset($response['data'])) {
            $issues[] = 'Missing message or data field';
        }
        
        if (!isset($response['timestamp'])) {
            $issues[] = 'Missing timestamp field';
        }
        
        // Validate success field type
        if (isset($response['success']) && !is_bool($response['success'])) {
            $issues[] = 'Success field should be boolean';
        }
        
        // Validate timestamp format
        if (isset($response['timestamp'])) {
            $timestamp = $response['timestamp'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $timestamp)) {
                $issues[] = 'Invalid timestamp format (should be ISO 8601)';
            }
        }
        
        // Check for meta field in success responses
        if ($response['success'] && !isset($response['meta'])) {
            $issues[] = 'Missing meta field in success response';
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
    
    /**
     * Generate validation report
     */
    private function generateReport() {
        $total_tests = count($this->results) + count($this->errors);
        $successful_tests = count(array_filter($this->results, function($r) { 
            return $r['success'] && $r['validation']['valid']; 
        }));
        $failed_tests = $total_tests - $successful_tests;
        
        $endpoint_summary = [];
        foreach ($this->results as $result) {
            $endpoint = $result['endpoint'];
            if (!isset($endpoint_summary[$endpoint])) {
                $endpoint_summary[$endpoint] = [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0
                ];
            }
            $endpoint_summary[$endpoint]['total']++;
            if ($result['success'] && $result['validation']['valid']) {
                $endpoint_summary[$endpoint]['successful']++;
            } else {
                $endpoint_summary[$endpoint]['failed']++;
            }
        }
        
        return [
            'summary' => [
                'total_tests' => $total_tests,
                'successful_tests' => $successful_tests,
                'failed_tests' => $failed_tests,
                'success_rate' => $total_tests > 0 ? round(($successful_tests / $total_tests) * 100, 2) : 0,
                'total_errors' => count($this->errors)
            ],
            'endpoint_summary' => $endpoint_summary,
            'test_results' => $this->results,
            'errors' => $this->errors,
            'recommendations' => $this->generateRecommendations(),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Generate recommendations based on test results
     */
    private function generateRecommendations() {
        $recommendations = [];
        
        // Check for common issues
        $validation_issues = [];
        foreach ($this->results as $result) {
            if (!$result['validation']['valid']) {
                $validation_issues = array_merge($validation_issues, $result['validation']['issues']);
            }
        }
        
        $unique_issues = array_unique($validation_issues);
        
        if (in_array('Missing success field', $unique_issues)) {
            $recommendations[] = 'Ensure all API responses include a boolean success field';
        }
        
        if (in_array('Missing timestamp field', $unique_issues)) {
            $recommendations[] = 'Add timestamp field to all API responses';
        }
        
        if (in_array('Missing meta field in success response', $unique_issues)) {
            $recommendations[] = 'Include meta field with version and environment info in success responses';
        }
        
        if (in_array('Invalid timestamp format (should be ISO 8601)', $unique_issues)) {
            $recommendations[] = 'Standardize timestamp format to ISO 8601 (YYYY-MM-DDTHH:MM:SS+00:00)';
        }
        
        // Check for HTTP errors
        $http_errors = array_filter($this->results, function($r) { 
            return $r['http_code'] >= 400; 
        });
        
        if (!empty($http_errors)) {
            $recommendations[] = 'Review HTTP error codes and ensure proper error handling';
        }
        
        // Check for CURL errors
        if (!empty($this->errors)) {
            $recommendations[] = 'Fix CURL/connection errors in API endpoints';
        }
        
        return $recommendations;
    }
}

// API endpoint for validation
if (isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING)) && filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) === 'validate_endpoints') {
    try {
        $validator = new APIEndpointValidator();
        $result = $validator->validateAll();
        
        echo json_encode(APIResponse::success($result, 'API endpoints validation completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
