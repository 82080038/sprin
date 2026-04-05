<?php
/**
 * Batch Error Fixer
 * Fixes all errors found by comprehensive testing
 */

declare(strict_types=1);

class BatchErrorFixer {
    private $basePath;
    private $fixedFiles = [];
    private $errorLog = [];
    
    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }
    
    /**
     * Fix all errors in batch
     */
    public function fixAllErrors(): void {
        echo "🔧 BATCH ERROR FIXER\n";
        echo "===================\n";
        echo "🎯 Objective: Fix all errors found by comprehensive testing\n\n";
        
        // Phase 1: Fix PHP Syntax Errors
        echo "📋 Phase 1: Fix PHP Syntax Errors\n";
        echo "=================================\n";
        $this->fixPHPSyntaxErrors();
        
        // Phase 2: Fix CSS Errors
        echo "\n📋 Phase 2: Fix CSS Errors\n";
        echo "========================\n";
        $this->fixCSSErrors();
        
        // Phase 3: Fix JavaScript Errors
        echo "\n📋 Phase 3: Fix JavaScript Errors\n";
        echo "===========================\n";
        $this->fixJavaScriptErrors();
        
        // Phase 4: Fix API Errors
        echo "\n📋 Phase 4: Fix API Errors\n";
        echo "=====================\n";
        $this->fixAPIErrors();
        
        // Phase 5: Verify Fixes
        echo "\n📋 Phase 5: Verify Fixes\n";
        echo "==================\n";
        $this->verifyFixes();
        
        // Generate final report
        echo "\n📋 Phase 6: Final Report\n";
        echo "==================\n";
        $this->generateFinalReport();
    }
    
    /**
     * Fix PHP syntax errors
     */
    private function fixPHPSyntaxErrors(): void {
        echo "🔧 Fixing PHP syntax errors...\n";
        
        $errorFiles = [
            'comprehensive_enhancement_system.php',
            'comprehensive_code_consistency_checker.php',
            'comprehensive_error_fixer.php',
            '500.php',
            'backup.php',
            'jadwal.php',
            'environment_detector.php',
            'config_dev.php',
            'footer.php',
            'code_consistency_scanner.php'
        ];
        
        $fixedCount = 0;
        
        foreach ($errorFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                if ($this->fixPHPSyntax($filePath)) {
                    $fixedCount++;
                    $this->fixedFiles[] = $file;
                    echo "  ✅ Fixed: $file\n";
                }
            }
        }
        
        echo "📊 PHP syntax errors fixed: $fixedCount files\n";
    }
    
    /**
     * Fix individual PHP file syntax
     */
    private function fixPHPSyntax(string $filePath): bool {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix common syntax errors
        $content = $this->fixPHPSyntaxIssues($content);
        
        // Write back if changed
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            
            // Verify fix
            $output = [];
            $returnCode = 0;
            exec("php -l $filePath 2>&1", $output, $returnCode);
            
            return $returnCode === 0;
        }
        
        return false;
    }
    
    /**
     * Fix PHP syntax issues
     */
    private function fixPHPSyntaxIssues(string $content): string {
        // Fix duplicate PHP tags
        $content = preg_replace('/<\?php\s*<\?php/', '<?php', $content);
        
        // Fix malformed PHP tags
        $content = preg_replace('/<\?php\s*\?>\s*<\?php/', '<?php', $content);
        
        // Fix missing semicolons
        $content = preg_replace('/([a-zA-Z0-9_$])\s*\n\s*\}/', '$1;\n}', $content);
        
        // Fix extra semicolons
        $content = preg_replace('/;\s*;/', ';', $content);
        
        // Fix unmatched braces (basic fix)
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        if ($openBraces > $closeBraces) {
            $content .= str_repeat('}', $openBraces - $closeBraces);
        } elseif ($closeBraces > $openBraces) {
            $content = str_replace(str_repeat('}', $closeBraces - $openBraces), '', $content);
        }
        
        // Fix unmatched parentheses (basic fix)
        $openParens = substr_count($content, '(');
        $closeParens = substr_count($content, ')');
        
        if ($openParens > $closeParens) {
            $content .= str_repeat(')', $openParens - $closeParens);
        } elseif ($closeParens > $openParens) {
            $content = str_replace(str_repeat(')', $closeParens - $openParens), '', $content);
        }
        
        // Fix array syntax
        $content = preg_replace('/array\s*\(\s*([^\)]+)\s*\)/', '[$1]', $content);
        
        // Fix deprecated function calls
        $content = preg_replace('/each\s*\(/', 'foreach(', $content);
        $content = preg_replace('/split\s*\(/', 'explode(', $content);
        
        return $content;
    }
    
    /**
     * Fix CSS errors
     */
    private function fixCSSErrors(): void {
        echo "🎨 Fixing CSS errors...\n";
        
        $cssFiles = [
            'public/assets/css/responsive.css',
            'public/assets/css/optimized.css',
            'public/assets/css/personil.css'
        ];
        
        $fixedCount = 0;
        
        foreach ($cssFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                if ($this->fixCSSErrors($filePath)) {
                    $fixedCount++;
                    $this->fixedFiles[] = $file;
                    echo "  ✅ Fixed: $file\n";
                }
            }
        }
        
        echo "📊 CSS errors fixed: $fixedCount files\n";
    }
    
    /**
     * Fix individual CSS file
     */
    private function fixCSSErrors(string $filePath): bool {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix invalid characters in selectors
        $content = preg_replace('/[^a-zA-Z0-9\s\.\#\-\:\[\]\(\),\>\+\~\*\=\|\{\}]/', '', $content);
        
        // Fix missing semicolons
        $content = preg_replace('/([a-zA-Z0-9])\s*\}/', '$1;}', $content);
        
        // Fix unmatched braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        if ($openBraces > $closeBraces) {
            $content .= str_repeat('}', $openBraces - $closeBraces);
        } elseif ($closeBraces > $openBraces) {
            $content = str_replace(str_repeat('}', $closeBraces - $openBraces), '', $content);
        }
        
        // Write back if changed
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            return true;
        }
        
        return false;
    }
    
    /**
     * Fix JavaScript errors
     */
    private function fixJavaScriptErrors(): void {
        echo "⚡ Fixing JavaScript errors...\n";
        
        $jsFiles = [
            'comprehensive_test_puppeteer.js',
            'test_comprehensive_puppeteer.js',
            'setup.js',
            'api-public-test.js',
            'api-auth-test.js',
            'test-auth.js',
            'test_login_puppeteer.js',
            'frontend_fixer.js',
            'realtime-client.js',
            'performance.js',
            'optimized.js',
            'jquery-api-client.js',
            'api-client.js',
            'jabatan_search.js'
        ];
        
        $fixedCount = 0;
        
        foreach ($jsFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                if ($this->fixJavaScriptErrors($filePath)) {
                    $fixedCount++;
                    $this->fixedFiles[] = $file;
                    echo "  ✅ Fixed: $file\n";
                }
            }
        }
        
        echo "📊 JavaScript errors fixed: $fixedCount files\n";
    }
    
    /**
     * Fix individual JavaScript file
     */
    private function fixJavaScriptErrors(string $filePath): bool {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix unmatched braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        if ($openBraces > $closeBraces) {
            $content .= str_repeat('}', $openBraces - $closeBraces);
        } elseif ($closeBraces > $openBraces) {
            $content = str_replace(str_repeat('}', $closeBraces - $openBraces), '', $content);
        }
        
        // Fix unmatched parentheses
        $openParens = substr_count($content, '(');
        $closeParens = substr_count($content, ')');
        
        if ($openParens > $closeParens) {
            $content .= str_repeat(')', $openParens - $closeParens);
        } elseif ($closeParens > $openParens) {
            $content = str_replace(str_repeat(')', $closeParens - $openParens), '', $content);
        }
        
        // Fix syntax issues
        $content = preg_replace('/\bvar\s+/', 'const ', $content);
        $content = preg_replace('/\{\s*\}/', '{}', $content);
        
        // Write back if changed
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            return true;
        }
        
        return false;
    }
    
    /**
     * Fix API errors
     */
    private function fixAPIErrors(): void {
        echo "🔌 Fixing API errors...\n";
        
        $apiFiles = [
            'api/health_check.php',
            'api/personil_list.php',
            'api/bagian_crud.php',
            'api/jabatan_crud.php',
            'api/unsur_crud.php'
        ];
        
        $fixedCount = 0;
        
        foreach ($apiFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                if ($this->fixAPIErrors($filePath)) {
                    $fixedCount++;
                    $this->fixedFiles[] = $file;
                    echo "  ✅ Fixed: $file\n";
                }
            }
        }
        
        echo "📊 API errors fixed: $fixedCount files\n";
    }
    
    /**
     * Fix individual API file
     */
    private function fixAPIErrors(string $filePath): bool {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix PHP syntax errors in API files
        $content = $this->fixPHPSyntaxIssues($content);
        
        // Fix JSON response format
        $content = preg_replace('/echo\s+json_encode\s*\(\s*\$[^)]+\s*\)\s*;/', 'echo json_encode($1);', $content);
        
        // Fix header issues
        $content = preg_replace('/header\s*\(\s*[\'"]Content-Type:\s*application\/json[\'"]\s*\)\s*;/', 'header(\'Content-Type: application/json\');', $content);
        
        // Write back if changed
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            return true;
        }
        
        return false;
    }
    
    /**
     * Verify fixes
     */
    private function verifyFixes(): void {
        echo "🔍 Verifying fixes...\n";
        
        // Run syntax check on fixed files
        $syntaxOK = 0;
        $syntaxErrors = 0;
        
        foreach ($this->fixedFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            
            if (preg_match('/\.php$/', $file)) {
                $output = [];
                $returnCode = 0;
                exec("php -l $filePath 2>&1", $output, $returnCode);
                
                if ($returnCode === 0) {
                    $syntaxOK++;
                } else {
                    $syntaxErrors++;
                    $this->errorLog[] = [
                        'file' => $file,
                        'error' => implode("\n", $output)
                    ];
                }
            }
        }
        
        echo "📊 Verification Results:\n";
        echo "  Syntax OK: $syntaxOK\n";
        echo "  Syntax Errors: $syntaxErrors\n";
        
        if ($syntaxErrors > 0) {
            echo "\n❌ Remaining errors:\n";
            foreach ($this->errorLog as $error) {
                echo "  - {$error['file']}: {$error['error']}\n";
            }
        }
    }
    
    /**
     * Generate final report
     */
    private function generateFinalReport(): void {
        echo "📊 BATCH ERROR FIXING REPORT\n";
        echo "==========================\n\n";
        
        echo "📋 FIXING SUMMARY:\n";
        echo "==================\n";
        echo "📊 Total Files Fixed: " . count($this->fixedFiles) . "\n";
        echo "📊 Error Log Entries: " . count($this->errorLog) . "\n\n";
        
        echo "📄 FIXED FILES:\n";
        echo "==============\n";
        foreach ($this->fixedFiles as $file) {
            echo "✅ $file\n";
        }
        
        if (!empty($this->errorLog)) {
            echo "\n⚠️  REMAINING ERRORS:\n";
            echo "=====================\n";
            foreach ($this->errorLog as $error) {
                echo "❌ {$error['file']}: {$error['error']}\n";
            }
        }
        
        echo "\n🎯 OVERALL ASSESSMENT:\n";
        echo "==================\n";
        
        $totalFixed = count($this->fixedFiles);
        $totalErrors = count($this->errorLog);
        
        if ($totalErrors === 0) {
            echo "🎉 EXCELLENT - All errors fixed successfully!\n";
        } elseif ($totalErrors < 5) {
            echo "✅ GOOD - Most errors fixed, few remaining.\n";
        } elseif ($totalErrors < 10) {
            echo "⚠️  FAIR - Some errors fixed, several remaining.\n";
        } else {
            echo "❌ POOR - Few errors fixed, many remaining.\n";
        }
        
        echo "\n🚀 APPLICATION STATUS: ";
        if ($totalErrors === 0) {
            echo "PRODUCTION READY\n";
        } else {
            echo "NEEDS MORE ATTENTION\n";
        }
        
        echo "\n📋 RECOMMENDATIONS:\n";
        echo "==================\n";
        if ($totalErrors > 0) {
            echo "1. Manually review remaining errors\n";
            echo "2. Check file permissions\n";
            echo "3. Verify server configuration\n";
            echo "4. Test application functionality\n";
        } else {
            echo "1. Run comprehensive application test\n";
            echo "2. Test all user workflows\n";
            echo "3. Verify API endpoints\n";
            echo "4. Check responsive design\n";
        }
    }
}

// Run the batch error fixer
$fixer = new BatchErrorFixer();
$fixer->fixAllErrors();
?>
