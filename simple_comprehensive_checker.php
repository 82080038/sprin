<?php
/**
 * Simple Comprehensive Code Consistency Checker
 * Checks ALL file types with best practices
 */

declare(strict_types=1);

class SimpleComprehensiveChecker {
    private $basePath;
    private $results = [];
    private $fileStats = [];
    private $issues = [];
    
    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }
    
    /**
     * Run comprehensive check
     */
    public function runCheck(): void {
        echo "🔍 COMPREHENSIVE CODE CONSISTENCY CHECKER\n";
        echo "========================================\n";
        echo "📡 Based on Google, MDN, W3Schools best practices\n\n";
        
        // Get all files
        $allFiles = $this->getAllFiles();
        
        echo "📁 Found " . count($allFiles) . " files to analyze\n\n";
        
        // Check files
        foreach ($allFiles as $file) {
            $this->checkFile($file);
        }
        
        // Generate report
        $this->generateReport();
    }
    
    /**
     * Check individual file
     */
    private function checkFile(string $filePath): void {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $relativePath = str_replace($this->basePath . '/', '', $filePath);
        
        // Count file types
        if (!isset($this->fileStats[$extension])) {
            $this->fileStats[$extension] = 0;
        }
        $this->fileStats[$extension]++;
        
        // Check based on file type
        switch ($extension) {
            case 'php':
                $this->checkPHPFile($filePath, $relativePath);
                break;
            case 'html':
            case 'htm':
                $this->checkHTMLFile($filePath, $relativePath);
                break;
            case 'css':
                $this->checkCSSFile($filePath, $relativePath);
                break;
            case 'js':
                $this->checkJSFile($filePath, $relativePath);
                break;
            case 'json':
                $this->checkJSONFile($filePath, $relativePath);
                break;
        }
    }
    
    /**
     * Check PHP files
     */
    private function checkPHPFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for deprecated functions
        $deprecatedPatterns = [
            'each(',
            'split(',
            'eregi(',
            'ereg(',
            'mysql_connect',
            'mysql_query',
            'FILTER_DEFAULT'
        ];
        
        foreach ($deprecatedPatterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                $this->addIssue('php', 'deprecated_function', $relativePath, "Deprecated function: $pattern");
            }
        }
        
        // Check for strict_types
        if (strpos($content, 'declare(strict_types=1)') === false) {
            $this->addIssue('php', 'missing_strict_types', $relativePath, "Missing strict_types declaration");
        }
    }
    
    /**
     * Check HTML files
     */
    private function checkHTMLFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for DOCTYPE
        if (strpos($content, '<!DOCTYPE html>') === false) {
            $this->addIssue('html', 'missing_doctype', $relativePath, "Missing DOCTYPE");
        }
        
        // Check for lang attribute
        if (preg_match('/<html[^>]*>/i', $content, $matches)) {
            if (strpos($matches[0], 'lang=') === false) {
                $this->addIssue('html', 'missing_lang', $relativePath, "Missing lang attribute");
            }
        }
        
        // Check for meta charset
        if (strpos($content, '<meta charset=') === false) {
            $this->addIssue('html', 'missing_meta_charset', $relativePath, "Missing meta charset");
        }
        
        // Check for viewport
        if (strpos($content, 'name="viewport"') === false) {
            $this->addIssue('html', 'missing_viewport', $relativePath, "Missing viewport meta tag");
        }
        
        // Check for alt attributes
        if (preg_match_all('/<img[^>]*>/i', $content, $matches)) {
            foreach ($matches[0] as $imgTag) {
                if (strpos($imgTag, 'alt=') === false) {
                    $this->addIssue('html', 'missing_alt', $relativePath, "Missing alt attribute");
                }
            }
        }
    }
    
    /**
     * Check CSS files
     */
    private function checkCSSFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for !important
        $importantCount = substr_count($content, '!important');
        if ($importantCount > 2) {
            $this->addIssue('css', 'too_many_important', $relativePath, "Too many !important: $importantCount");
        }
        
        // Check for CSS variables
        if (strlen($content) > 1000 && strpos($content, '--') === false) {
            $this->addIssue('css', 'missing_variables', $relativePath, "Consider using CSS variables");
        }
        
        // Check for browser prefixes
        $prefixes = ['-webkit-', '-moz-', '-ms-', '-o-'];
        foreach ($prefixes as $prefix) {
            if (strpos($content, $prefix) !== false) {
                $this->addIssue('css', 'browser_prefix', $relativePath, "Browser prefix found: $prefix");
            }
        }
    }
    
    /**
     * Check JavaScript files
     */
    private function checkJSFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for var usage
        $varCount = substr_count($content, 'var ');
        if ($varCount > 0) {
            $this->addIssue('js', 'var_usage', $relativePath, "Use let/const instead of var: $varCount");
        }
        
        // Check for == vs ===
        $doubleEqualsCount = substr_count($content, ' == ');
        if ($doubleEqualsCount > 0) {
            $this->addIssue('js', 'double_equals', $relativePath, "Use === instead of ==: $doubleEqualsCount");
        }
        
        // Check for console.log
        $consoleLogCount = substr_count($content, 'console.log');
        if ($consoleLogCount > 0) {
            $this->addIssue('js', 'console_log', $relativePath, "Remove console.log: $consoleLogCount");
        }
        
        // Check for jQuery
        $jqueryCount = substr_count($content, '$.') + substr_count($content, 'jQuery.');
        if ($jqueryCount > 5) {
            $this->addIssue('js', 'jquery_usage', $relativePath, "Consider vanilla JS instead of jQuery");
        }
        
        // Check for ES6 features
        if (strlen($content) > 500) {
            $hasArrow = strpos($content, '=>') !== false;
            $hasTemplate = strpos($content, '`') !== false;
            
            if (!$hasArrow) {
                $this->addIssue('js', 'missing_es6', $relativePath, "Consider ES6 arrow functions");
            }
        }
    }
    
    /**
     * Check JSON files
     */
    private function checkJSONFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check if valid JSON
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addIssue('json', 'invalid_json', $relativePath, "Invalid JSON: " . json_last_error_msg());
            return;
        }
        
        // Check for trailing commas
        if (preg_match('/,\s*[}\]]/', $content)) {
            $this->addIssue('json', 'trailing_comma', $relativePath, "Trailing comma found");
        }
    }
    
    /**
     * Add issue
     */
    private function addIssue(string $type, string $category, string $file, string $description): void {
        if (!isset($this->issues[$type])) {
            $this->issues[$type] = [];
        }
        
        if (!isset($this->issues[$type][$category])) {
            $this->issues[$type][$category] = [];
        }
        
        $this->issues[$type][$category][] = [
            'file' => $file,
            'description' => $description
        ];
    }
    
    /**
     * Get all files
     */
    private function getAllFiles(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
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
    
    /**
     * Generate report
     */
    private function generateReport(): void {
        echo "\n📊 COMPREHENSIVE CODE CONSISTENCY REPORT\n";
        echo "=====================================\n\n";
        
        // File statistics
        echo "📁 FILE STATISTICS:\n";
        foreach ($this->fileStats as $type => $count) {
            if ($count > 0) {
                echo "  $type: $count files\n";
            }
        }
        echo "\n";
        
        // Issues by type
        echo "🔍 ISSUES FOUND BY FILE TYPE:\n";
        $totalIssues = 0;
        
        foreach ($this->issues as $fileType => $categories) {
            $fileTypeIssues = 0;
            echo "  📄 $fileType:\n";
            
            foreach ($categories as $category => $issues) {
                $count = count($issues);
                $fileTypeIssues += $count;
                $totalIssues += $count;
                
                echo "    ⚠️  $category: $count issues\n";
                
                // Show examples
                foreach (array_slice($issues, 0, 2) as $issue) {
                    echo "      - {$issue['file']}: {$issue['description']}\n";
                }
                
                if ($count > 2) {
                    echo "      ... and " . ($count - 2) . " more\n";
                }
            }
            
            echo "    📊 Total $fileType issues: $fileTypeIssues\n\n";
        }
        
        // Overall assessment
        echo "🎯 OVERALL ASSESSMENT:\n";
        echo "====================\n";
        
        $totalFiles = array_sum($this->fileStats);
        $issueRate = $totalFiles > 0 ? ($totalIssues / $totalFiles) * 100 : 0;
        
        echo "📊 Total Files: $totalFiles\n";
        echo "📊 Total Issues: $totalIssues\n";
        echo "📊 Issue Rate: " . round($issueRate, 1) . "%\n\n";
        
        if ($issueRate < 5) {
            echo "🏆 EXCELLENT - Very high code consistency!\n";
        } elseif ($issueRate < 15) {
            echo "✅ GOOD - Good code consistency with minor issues.\n";
        } elseif ($issueRate < 30) {
            echo "⚠️  FAIR - Some consistency issues need attention.\n";
        } else {
            echo "❌ POOR - Major consistency issues found.\n";
        }
        
        // Recommendations
        echo "\n📋 RECOMMENDATIONS:\n";
        echo "==================\n";
        
        if (isset($this->issues['php'])) {
            echo "🔧 PHP: Update deprecated functions and add strict_types declarations\n";
        }
        if (isset($this->issues['html'])) {
            echo "🌐 HTML: Add proper DOCTYPE, meta tags, and semantic HTML5 elements\n";
        }
        if (isset($this->issues['css'])) {
            echo "🎨 CSS: Use CSS variables and reduce !important usage\n";
        }
        if (isset($this->issues['js'])) {
            echo "⚡ JavaScript: Use ES6+ features and proper error handling\n";
        }
        if (isset($this->issues['json'])) {
            echo "📄 JSON: Fix formatting issues\n";
        }
        
        echo "\n🎉 COMPREHENSIVE CHECK COMPLETED!\n";
    }
}

// Run the checker
$checker = new SimpleComprehensiveChecker();
$checker->runCheck();
?>
