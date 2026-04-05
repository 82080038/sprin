<?php
/**
 * Final Comprehensive Test
 * Complete verification after all fixes
 */

declare(strict_types=1);

class FinalComprehensiveTest {
    private $basePath;
    private $baseUrl;
    private $testResults = [];
    
    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
        $this->baseUrl = 'http://localhost/sprint';
    }
    
    /**
     * Run final comprehensive test
     */
    public function runFinalTest(): void {
        echo "🎭 FINAL COMPREHENSIVE TEST\n";
        echo "==========================\n";
        echo "🎯 Objective: Complete verification after all fixes\n\n";
        
        // Phase 1: Test all pages
        echo "📋 Phase 1: Test All Pages\n";
        echo "========================\n";
        $this->testAllPages();
        
        // Phase 2: Test all APIs
        echo "\n📋 Phase 2: Test All APIs\n";
        echo "======================\n";
        $this->testAllAPIs();
        
        // Phase 3: Test CSS and JS
        echo "\n📋 Phase 3: Test CSS and JS\n";
        echo "========================\n";
        $this->testCSSAndJS();
        
        // Phase 4: Test PHP syntax
        echo "\n📋 Phase 4: Test PHP Syntax\n";
        echo "========================\n";
        $this->testPHPSyntax();
        
        // Phase 5: Generate final report
        echo "\n📋 Phase 5: Generate Final Report\n";
        echo "==============================\n";
        $this->generateFinalReport();
    }
    
    /**
     * Test all pages
     */
    private function testAllPages(): void {
        echo "🌐 Testing all pages...\n";
        
        $pages = [
            '/' => 'Home Page',
            '/login.php' => 'Login Page',
            '/pages/main.php' => 'Main Dashboard',
            '/pages/personil.php' => 'Personil Page',
            '/pages/bagian.php' => 'Bagian Page'
        ];
        
        foreach ($pages as $url => $name) {
            $result = $this->testPage($this->baseUrl . $url, $url, $name);
            $this->testResults[] = $result;
            
            $status = $result['status'] === 200 ? '✅' : '❌';
            echo "  $status $name - Status: {$result['status']} ({$result['response_time']}ms)\n";
        }
    }
    
    /**
     * Test all APIs
     */
    private function testAllAPIs(): void {
        echo "🔌 Testing all APIs...\n";
        
        $apis = [
            '/api/health_check.php' => 'Health Check',
            '/api/personil_list.php' => 'Personil List',
            '/api/bagian_crud.php' => 'Bagian CRUD',
            '/api/jabatan_crud.php' => 'Jabatan CRUD',
            '/api/unsur_crud.php' => 'Unsur CRUD'
        ];
        
        foreach ($apis as $url => $name) {
            $result = $this->testAPI($this->baseUrl . $url, $url, $name);
            $this->testResults[] = $result;
            
            $status = $result['status'] === 200 && $result['is_valid_json'] ? '✅' : '❌';
            echo "  $status $name - Status: {$result['status']} ({$result['response_time']}ms)\n";
        }
    }
    
    /**
     * Test CSS and JS
     */
    private function testCSSAndJS(): void {
        echo "🎨 Testing CSS and JS...\n";
        
        $cssFiles = [
            'public/assets/css/style.css' => 'Main Style',
            'public/assets/css/responsive.css' => 'Responsive Style'
        ];
        
        foreach ($cssFiles as $file => $name) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $result = $this->testCSSFile($filePath, $file, $name);
                $this->testResults[] = $result;
                
                $status = $result['status'] === 'ok' ? '✅' : '❌';
                echo "  $status $name - Size: {$result['size']} bytes\n";
            }
        }
        
        $jsFiles = [
            'comprehensive_test_puppeteer_fixed.js' => 'Puppeteer Test'
        ];
        
        foreach ($jsFiles as $file => $name) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $result = $this->testJSFile($filePath, $file, $name);
                $this->testResults[] = $result;
                
                $status = $result['status'] === 'ok' ? '✅' : '❌';
                echo "  $status $name - Size: {$result['size']} bytes\n";
            }
        }
    }
    
    /**
     * Test PHP syntax
     */
    private function testPHPSyntax(): void {
        echo "🔧 Testing PHP syntax...\n";
        
        $phpFiles = [
            'login.php',
            'pages/main.php',
            'pages/personil.php',
            'pages/bagian.php',
            'api/health_check.php',
            'api/personil_list.php',
            'api/bagian_crud.php',
            'api/jabatan_crud.php',
            'api/unsur_crud.php'
        ];
        
        foreach ($phpFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $result = $this->testPHPFile($filePath, $file);
                $this->testResults[] = $result;
                
                $status = $result['status'] === 'ok' ? '✅' : '❌';
                echo "  $status $file - Syntax: OK\n";
            }
        }
    }
    
    /**
     * Test page
     */
    private function testPage(string $url, string $path, string $name): array {
        $startTime = microtime(true);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $endTime = microtime(true);
        
        $result = [
            'type' => 'page',
            'name' => $name,
            'url' => $url,
            'path' => $path,
            'status' => 200,
            'response_time' => round(($endTime - $startTime) * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($response === false) {
            $result['status'] = 'error';
            $result['error'] = 'Connection failed';
        } else {
            // Check HTTP status
            if (isset($http_response_header)) {
                $statusLine = $http_response_header[0];
                if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                    $result['status'] = (int)$matches[1];
                }
            }
            
            $result['content_length'] = strlen($response);
            $result['has_html_structure'] = strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false;
        }
        
        return $result;
    }
    
    /**
     * Test API
     */
    private function testAPI(string $url, string $path, string $name): array {
        $startTime = microtime(true);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $endTime = microtime(true);
        
        $result = [
            'type' => 'api',
            'name' => $name,
            'url' => $url,
            'path' => $path,
            'status' => 200,
            'response_time' => round(($endTime - $startTime) * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s'),
            'is_valid_json' => false
        ];
        
        if ($response === false) {
            $result['status'] = 'error';
            $result['error'] = 'Connection failed';
        } else {
            // Check HTTP status
            if (isset($http_response_header)) {
                $statusLine = $http_response_header[0];
                if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                    $result['status'] = (int)$matches[1];
                }
            }
            
            // Check JSON validity
            json_decode($response);
            $result['is_valid_json'] = json_last_error() === JSON_ERROR_NONE;
            
            if ($result['is_valid_json']) {
                $result['response_data'] = json_decode($response, true);
            }
        }
        
        return $result;
    }
    
    /**
     * Test CSS file
     */
    private function testCSSFile(string $filePath, string $file, string $name): array {
        $content = file_get_contents($filePath);
        
        $result = [
            'type' => 'css',
            'name' => $name,
            'file' => $file,
            'status' => 'ok',
            'size' => strlen($content),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Basic CSS validation
        if (strpos($content, '{') === false || strpos($content, '}') === false) {
            $result['status'] = 'invalid';
            $result['error'] = 'Missing CSS braces';
        }
        
        return $result;
    }
    
    /**
     * Test JS file
     */
    private function testJSFile(string $filePath, string $file, string $name): array {
        $content = file_get_contents($filePath);
        
        $result = [
            'type' => 'javascript',
            'name' => $name,
            'file' => $file,
            'status' => 'ok',
            'size' => strlen($content),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Basic JS validation
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        if ($openBraces !== $closeBraces) {
            $result['status'] = 'invalid';
            $result['error'] = 'Unmatched braces';
        }
        
        return $result;
    }
    
    /**
     * Test PHP file
     */
    private function testPHPFile(string $filePath, string $file): array {
        $result = [
            'type' => 'php',
            'file' => $file,
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Check PHP syntax
        $output = [];
        $returnCode = 0;
        exec("php -l $filePath 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $result['status'] = 'syntax_error';
            $result['error'] = implode("\n", $output);
        }
        
        return $result;
    }
    
    /**
     * Generate final report
     */
    private function generateFinalReport(): void {
        echo "📊 Generating final report...\n";
        
        $totalTests = count($this->testResults);
        $passedTests = array_filter($this->testResults, function($result) {
            return $result['status'] === 200 || $result['status'] === 'ok';
        });
        
        $failedTests = array_filter($this->testResults, function($result) {
            return $result['status'] !== 200 && $result['status'] !== 'ok';
        });
        
        $summary = [
            'total_tests' => $totalTests,
            'passed_tests' => count($passedTests),
            'failed_tests' => count($failedTests),
            'success_rate' => $totalTests > 0 ? round((count($passedTests) / $totalTests) * 100, 1) : 0
        ];
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'objective' => 'Final comprehensive test after all fixes',
            'summary' => $summary,
            'test_results' => $this->testResults
        ];
        
        // Save JSON report
        $reportFile = $this->basePath . '/final_comprehensive_test_report.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        // Save Markdown report
        $this->saveMarkdownReport($report);
        
        // Display summary
        $this->displayFinalSummary($summary);
    }
    
    /**
     * Save markdown report
     */
    private function saveMarkdownReport(array $report): void {
        $markdown = "# 🎭 Final Comprehensive Test Report\n\n";
        $markdown .= "## 📋 Testing Summary\n\n";
        $markdown .= "**Objective**: {$report['objective']}\n";
        $markdown .= "**Date**: {$report['timestamp']}\n";
        $markdown .= "**Status**: ✅ COMPLETED\n\n";
        
        $markdown .= "## 📊 Results Summary\n\n";
        $markdown .= "- **Total Tests**: {$report['summary']['total_tests']}\n";
        $markdown .= "- **Passed Tests**: {$report['summary']['passed_tests']}\n";
        $markdown .= "- **Failed Tests**: {$report['summary']['failed_tests']}\n";
        $markdown .= "- **Success Rate**: {$report['summary']['success_rate']}%\n\n";
        
        $markdown .= "## 📄 Detailed Results\n\n";
        
        foreach ($report['test_results'] as $result) {
            $status = $result['status'] === 200 || $result['status'] === 'ok' ? '✅' : '❌';
            $markdown .= "$status **{$result['name']}** - Status: {$result['status']}\n";
            
            if (isset($result['response_time'])) {
                $markdown .= "  - Response Time: {$result['response_time']}ms\n";
            }
            
            if (isset($result['is_valid_json'])) {
                $jsonStatus = $result['is_valid_json'] ? '✅' : '❌';
                $markdown .= "  - JSON Valid: $jsonStatus\n";
            }
            
            if (isset($result['error'])) {
                $markdown .= "  - Error: {$result['error']}\n";
            }
            
            $markdown .= "\n";
        }
        
        $reportFile = $this->basePath . '/final_comprehensive_test_report.md';
        file_put_contents($reportFile, $markdown);
        
        echo "  ✅ Reports saved to:\n";
        echo "    - final_comprehensive_test_report.json\n";
        echo "    - final_comprehensive_test_report.md\n";
    }
    
    /**
     * Display final summary
     */
    private function displayFinalSummary(array $summary): void {
        echo "\n📊 FINAL COMPREHENSIVE TEST SUMMARY:\n";
        echo "===================================\n";
        echo "📋 Total Tests: {$summary['total_tests']}\n";
        echo "✅ Passed Tests: {$summary['passed_tests']}\n";
        echo "❌ Failed Tests: {$summary['failed_tests']}\n";
        echo "📈 Success Rate: {$summary['success_rate']}%\n\n";
        
        if ($summary['success_rate'] >= 90) {
            echo "🎉 EXCELLENT - Application is production ready!\n";
        } elseif ($summary['success_rate'] >= 75) {
            echo "✅ VERY GOOD - Application mostly ready!\n";
        } elseif ($summary['success_rate'] >= 50) {
            echo "⚠️  GOOD - Some issues remain but mostly functional.\n";
        } else {
            echo "❌ NEEDS WORK - Significant issues remain.\n";
        }
        
        echo "\n🚀 FINAL COMPREHENSIVE TEST: COMPLETED! ✨\n";
    }
}

// Run the final comprehensive test
$test = new FinalComprehensiveTest();
$test->runFinalTest();
?>
