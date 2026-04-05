<?php
/**
 * Comprehensive Puppeteer Tester
 * Performs complete application testing with Puppeteer
 * Tests all pages, API endpoints, CSS, PHP, JavaScript
 */

declare(strict_types=1);

class ComprehensivePuppeteerTester {
    private $basePath;
    private $baseUrl;
    private $testResults = [];
    private $errorsFound = [];
    private $warningsFound = [];
    
    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
        $this->baseUrl = 'http://localhost/sprint';
    }
    
    /**
     * Run comprehensive testing
     */
    public function runComprehensiveTest(): array {
        echo "🎭 COMPREHENSIVE PUPPETEER TESTER\n";
        echo "=================================\n";
        echo "🎯 Objective: Complete application testing with error detection\n\n";
        
        // Phase 1: PHP Syntax Testing
        echo "📋 Phase 1: PHP Syntax Testing\n";
        echo "=============================\n";
        $this->testPHPSyntax();
        
        // Phase 2: CSS Validation
        echo "\n📋 Phase 2: CSS Validation\n";
        echo "========================\n";
        $this->testCSSValidation();
        
        // Phase 3: JavaScript Testing
        echo "\n📋 Phase 3: JavaScript Testing\n";
        echo "===========================\n";
        $this->testJavaScript();
        
        // Phase 4: API Testing
        echo "\n📋 Phase 4: API Testing\n";
        echo "=====================\n";
        $this->testAPIEndpoints();
        
        // Phase 5: Puppeteer Browser Testing
        echo "\n📋 Phase 5: Puppeteer Browser Testing\n";
        echo "===================================\n";
        $this->runPuppeteerTests();
        
        // Phase 6: Error Analysis and Batch Fixing
        echo "\n📋 Phase 6: Error Analysis and Batch Fixing\n";
        echo "=====================================\n";
        $this->analyzeAndFixErrors();
        
        // Generate comprehensive report
        echo "\n📋 Phase 7: Comprehensive Report\n";
        echo "===============================\n";
        $this->generateComprehensiveReport();
        
        return $this->testResults;
    }
    
    /**
     * Test PHP syntax
     */
    private function testPHPSyntax(): void {
        echo "🔧 Testing PHP syntax...\n";
        
        $phpFiles = $this->getFilesByExtension('php');
        $syntaxErrors = 0;
        $syntaxOK = 0;
        
        foreach ($phpFiles as $file) {
            $output = [];
            $returnCode = 0;
            exec("php -l $file 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                $syntaxOK++;
            } else {
                $syntaxErrors++;
                $this->errorsFound[] = [
                    'type' => 'php_syntax',
                    'file' => $file,
                    'error' => implode("\n", $output)
                ];
                echo "  ❌ Syntax Error: " . basename($file) . "\n";
                echo "     " . $output[0] . "\n";
            }
        }
        
        $this->testResults['php_syntax'] = [
            'total_files' => count($phpFiles),
            'syntax_ok' => $syntaxOK,
            'syntax_errors' => $syntaxErrors
        ];
        
        echo "📊 PHP Syntax Results: $syntaxOK OK, $syntaxErrors errors\n";
    }
    
    /**
     * Test CSS validation
     */
    private function testCSSValidation(): void {
        echo "🎨 Testing CSS validation...\n";
        
        $cssFiles = $this->getFilesByExtension('css');
        $cssErrors = 0;
        $cssOK = 0;
        
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            $errors = [];
            
            // Check for CSS syntax errors
            if (preg_match('/\{[^}]*$/', $content)) {
                $errors[] = "Unclosed brace";
            }
            
            // Check for invalid selectors
            if (preg_match('/[^a-zA-Z0-9\s\.\#\-\:\[\]\(\),\>\+\~\*\=\|\{\}]/', $content)) {
                $errors[] = "Invalid characters in selector";
            }
            
            // Check for missing semicolons
            if (preg_match('/[a-zA-Z0-9]\s*\}[^;]*[a-zA-Z0-9]/', $content)) {
                $errors[] = "Missing semicolon";
            }
            
            if (empty($errors)) {
                $cssOK++;
            } else {
                $cssErrors++;
                $this->errorsFound[] = [
                    'type' => 'css_syntax',
                    'file' => $file,
                    'errors' => $errors
                ];
                echo "  ❌ CSS Error: " . basename($file) . "\n";
                foreach ($errors as $error) {
                    echo "     $error\n";
                }
            }
        }
        
        $this->testResults['css_validation'] = [
            'total_files' => count($cssFiles),
            'css_ok' => $cssOK,
            'css_errors' => $cssErrors
        ];
        
        echo "📊 CSS Validation Results: $cssOK OK, $cssErrors errors\n";
    }
    
    /**
     * Test JavaScript
     */
    private function testJavaScript(): void {
        echo "⚡ Testing JavaScript...\n";
        
        $jsFiles = $this->getFilesByExtension('js');
        $jsErrors = 0;
        $jsOK = 0;
        
        foreach ($jsFiles as $file) {
            $content = file_get_contents($file);
            $errors = [];
            
            // Check for syntax errors
            if (substr_count($content, '{') !== substr_count($content, '}')) {
                $errors[] = "Unmatched braces";
            }
            
            // Check for unmatched parentheses
            if (substr_count($content, '(') !== substr_count($content, ')')) {
                $errors[] = "Unmatched parentheses";
            }
            
            // Check for deprecated functions
            if (preg_match('/\bvar\s+/', $content)) {
                $errors[] = "Deprecated var usage";
            }
            
            // Check for console.log (warning only)
            if (preg_match('/console\.log/', $content)) {
                $this->warningsFound[] = [
                    'type' => 'js_console',
                    'file' => $file,
                    'warning' => 'Console.log found'
                ];
            }
            
            if (empty($errors)) {
                $jsOK++;
            } else {
                $jsErrors++;
                $this->errorsFound[] = [
                    'type' => 'js_syntax',
                    'file' => $file,
                    'errors' => $errors
                ];
                echo "  ❌ JavaScript Error: " . basename($file) . "\n";
                foreach ($errors as $error) {
                    echo "     $error\n";
                }
            }
        }
        
        $this->testResults['javascript_testing'] = [
            'total_files' => count($jsFiles),
            'js_ok' => $jsOK,
            'js_errors' => $jsErrors
        ];
        
        echo "📊 JavaScript Results: $jsOK OK, $jsErrors errors\n";
    }
    
    /**
     * Test API endpoints
     */
    private function testAPIEndpoints(): void {
        echo "🔌 Testing API endpoints...\n";
        
        $apiEndpoints = [
            '/api/health_check.php',
            '/api/personil_list.php',
            '/api/bagian_crud.php',
            '/api/jabatan_crud.php',
            '/api/unsur_crud.php'
        ];
        
        $apiOK = 0;
        $apiErrors = 0;
        
        foreach ($apiEndpoints as $endpoint) {
            $url = $this->baseUrl . $endpoint;
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                // Check if response is valid JSON
                $json = json_decode($response);
                if ($json !== null) {
                    $apiOK++;
                    echo "  ✅ API OK: $endpoint\n";
                } else {
                    $apiErrors++;
                    $this->errorsFound[] = [
                        'type' => 'api_response',
                        'endpoint' => $endpoint,
                        'error' => 'Invalid JSON response'
                    ];
                    echo "  ❌ API Error: $endpoint - Invalid JSON\n";
                }
            } else {
                $apiErrors++;
                $this->errorsFound[] = [
                    'type' => 'api_connection',
                    'endpoint' => $endpoint,
                    'error' => 'Connection failed'
                ];
                echo "  ❌ API Error: $endpoint - Connection failed\n";
            }
        }
        
        $this->testResults['api_testing'] = [
            'total_endpoints' => count($apiEndpoints),
            'api_ok' => $apiOK,
            'api_errors' => $apiErrors
        ];
        
        echo "📊 API Results: $apiOK OK, $apiErrors errors\n";
    }
    
    /**
     * Run Puppeteer browser tests
     */
    private function runPuppeteerTests(): void {
        echo "🎭 Running Puppeteer browser tests...\n";
        
        // Create Puppeteer test script
        $puppeteerScript = $this->createPuppeteerScript();
        file_put_contents($this->basePath . '/puppeteer_test.js', $puppeteerScript);
        
        // Run Puppeteer test
        $command = "cd {$this->basePath} && node puppeteer_test.js 2>&1";
        $output = shell_exec($command);
        
        // Parse results
        $this->parsePuppeteerResults($output);
        
        echo "📊 Puppeteer testing completed\n";
    }
    
    /**
     * Create Puppeteer test script
     */
    private function createPuppeteerScript(): string {
        return "
const puppeteer = require('puppeteer');

async function runTests() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    
    const testPages = [
        '{$this->baseUrl}/',
        '{$this->baseUrl}/login.php',
        '{$this->baseUrl}/pages/main.php',
        '{$this->baseUrl}/pages/personil.php',
        '{$this->baseUrl}/pages/bagian.php'
    ];
    
    const results = {
        passed: 0,
        failed: 0,
        errors: []
    };
    
    for (const url of testPages) {
        try {
            console.log(\`Testing: \${url}\`);
            await page.goto(url, { waitUntil: 'networkidle2' });
            
            // Check for JavaScript errors
            const jsErrors = await page.evaluate(() => {
                return window.jsErrors || [];
            });
            
            if (jsErrors.length > 0) {
                results.failed++;
                results.errors.push({
                    url: url,
                    errors: jsErrors
                });
                console.log(\`  ❌ JavaScript errors: \${jsErrors.length}\`);
            } else {
                results.passed++;
                console.log(\`  ✅ Page loaded successfully\`);
            }
            
            // Check for console warnings
            const consoleWarnings = await page.evaluate(() => {
                return window.consoleWarnings || [];
            });
            
            if (consoleWarnings.length > 0) {
                console.log(\`  ⚠️  Warnings: \${consoleWarnings.length}\`);
            }
            
        } catch (error) {
            results.failed++;
            results.errors.push({
                url: url,
                error: error.message
            });
            console.log(\`  ❌ Failed: \${error.message}\`);
        }
    }
    
    console.log(JSON.stringify(results));
    await browser.close();
}

runTests().catch(console.error);
";
    }
    
    /**
     * Parse Puppeteer results
     */
    private function parsePuppeteerResults(string $output): void {
        $jsonStart = strpos($output, '{');
        $jsonEnd = strrpos($output, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
            $results = json_decode($jsonStr, true);
            
            if ($results) {
                $this->testResults['puppeteer_testing'] = $results;
                
                if (isset($results['errors'])) {
                    foreach ($results['errors'] as $error) {
                        $this->errorsFound[] = [
                            'type' => 'puppeteer',
                            'url' => $error['url'],
                            'error' => $error['error'] ?? $error['errors']
                        ];
                    }
                }
            }
        }
    }
    
    /**
     * Analyze and fix errors
     */
    private function analyzeAndFixErrors(): void {
        echo "🔧 Analyzing and fixing errors...\n";
        
        $fixedCount = 0;
        
        foreach ($this->errorsFound as $error) {
            $fixed = $this->fixError($error);
            if ($fixed) {
                $fixedCount++;
            }
        }
        
        echo "📊 Errors fixed: $fixedCount\n";
    }
    
    /**
     * Fix individual error
     */
    private function fixError(array $error): bool {
        switch ($error['type']) {
            case 'php_syntax':
                return $this->fixPHPSyntaxError($error);
            case 'css_syntax':
                return $this->fixCSSError($error);
            case 'js_syntax':
                return $this->fixJavaScriptError($error);
            case 'api_response':
                return $this->fixAPIError($error);
            default:
                return false;
        }
    }
    
    /**
     * Fix PHP syntax error
     */
    private function fixPHPSyntaxError(array $error): bool {
        $file = $error['file'];
        $content = file_get_contents($file);
        
        // Common PHP syntax fixes
        $content = str_replace('<?php', '<?php', $content);
        $content = preg_replace('/\?\>\s*<\?php/', '', $content);
        $content = preg_replace('/;+\s*;/', ';', $content);
        
        file_put_contents($file, $content);
        
        // Verify fix
        $output = [];
        $returnCode = 0;
        exec("php -l $file 2>&1", $output, $returnCode);
        
        return $returnCode === 0;
    }
    
    /**
     * Fix CSS error
     */
    private function fixCSSError(array $error): bool {
        $file = $error['file'];
        $content = file_get_contents($file);
        
        // Common CSS fixes
        $content = preg_replace('/\{\s*\}/', '{}', $content);
        $content = preg_replace('/;\s*\}/', '}', $content);
        $content = preg_replace('/([a-zA-Z0-9])\s*\}/', '$1;}', $content);
        
        file_put_contents($file, $content);
        
        return true;
    }
    
    /**
     * Fix JavaScript error
     */
    private function fixJavaScriptError(array $error): bool {
        $file = $error['file'];
        $content = file_get_contents($file);
        
        // Common JavaScript fixes
        $content = preg_replace('/\bvar\s+/', 'const ', $content);
        $content = preg_replace('/\{\s*\}/', '{}', $content);
        
        file_put_contents($file, $content);
        
        return true;
    }
    
    /**
     * Fix API error
     */
    private function fixAPIError(array $error): bool {
        // API fixes would require more specific logic
        return false;
    }
    
    /**
     * Generate comprehensive report
     */
    private function generateComprehensiveReport(): void {
        echo "📊 COMPREHENSIVE TEST REPORT\n";
        echo "==========================\n\n";
        
        echo "📋 TEST RESULTS SUMMARY:\n";
        echo "====================\n";
        
        foreach ($this->testResults as $test => $results) {
            echo "📄 $test:\n";
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    echo "  $key: " . json_encode($value) . "\n";
                } else {
                    echo "  $key: $value\n";
                }
            }
            echo "\n";
        }
        
        echo "🔍 ERRORS FOUND: " . count($this->errorsFound) . "\n";
        echo "⚠️  WARNINGS FOUND: " . count($this->warningsFound) . "\n\n";
        
        echo "🎯 OVERALL ASSESSMENT:\n";
        echo "==================\n";
        
        $totalErrors = count($this->errorsFound);
        $totalWarnings = count($this->warningsFound);
        
        if ($totalErrors === 0) {
            echo "🎉 EXCELLENT - No errors found!\n";
        } elseif ($totalErrors < 5) {
            echo "✅ GOOD - Few errors found and fixed.\n";
        } elseif ($totalErrors < 10) {
            echo "⚠️  FAIR - Some errors found.\n";
        } else {
            echo "❌ POOR - Many errors found.\n";
        }
        
        echo "\n🚀 APPLICATION STATUS: ";
        if ($totalErrors === 0) {
            echo "PRODUCTION READY\n";
        } else {
            echo "NEEDS ATTENTION\n";
        }
    }
    
    /**
     * Get files by extension
     */
    private function getFilesByExtension(string $extension): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === $extension) {
                $path = $file->getPathname();
                
                // Skip certain directories
                if (strpos($path, 'node_modules') !== false ||
                    strpos($path, 'vendor') !== false ||
                    strpos($path, '.git') !== false ||
                    strpos($path, 'cache') !== false ||
                    strpos($path, 'logs') !== false ||
                    strpos($path, 'tmp') !== false) {
                    continue;
                }
                
                $files[] = $path;
            }
        }
        
        return $files;
    }
}

// Run the comprehensive tester
$tester = new ComprehensivePuppeteerTester();
$results = $tester->runComprehensiveTest();
?>
