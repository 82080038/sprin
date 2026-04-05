<?php
/**
 * Comprehensive Code Consistency Checker
 * Checks ALL file types: PHP, HTML, CSS, JavaScript, JSON, API, etc.
 * Based on best practices from Google, MDN, W3Schools, and industry standards
 */

declare(strict_types=1);

class ComprehensiveCodeConsistencyChecker {
    private $basePath;
    private $results = [];
    private $fileStats = [
        'php' => 0,
        'html' => 0,
        'css' => 0,
        'js' => 0,
        'json' => 0,
        'api' => 0,
        'other' => 0
    ];
    private $issues = [];
    
    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }
    
    /**
     * Run comprehensive check on ALL file types
     */
    public function runComprehensiveCheck(): array {
        echo "🔍 COMPREHENSIVE CODE CONSISTENCY CHECKER\n";
        echo "========================================\n";
        echo "📡 Based on Google, MDN, W3Schools best practices\n\n";
        
        // Get all files
        $allFiles = $this->getAllFiles();
        
        echo "📁 Found " . count($allFiles) . " files to analyze\n\n";
        
        // Check each file type
        forforeach($allFiles as $file) {
            $this->checkFile($file);
        }
        
        // Generate comprehensive report
        $this->generateComprehensiveReport();
        
        return $this->results;
    }
    
    /**
     * Check individual file based on type
     */
    private function checkFile(string $filePath): void {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $relativePath = str_replace($this->basePath . '/', '', $filePath);
        
        // Count file types
        if (isset($this->fileStats[$extension])) {
            $this->fileStats[$extension]++;
        } else {
            $this->fileStats['other']++;
        }
        
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
            default:
                $this->checkOtherFile($filePath, $relativePath);
                break;
        }
    }
    
    /**
     * Check PHP files (already done, but verify)
     */
    private function checkPHPFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for deprecated functions
        $deprecatedPatterns = [
            '/\beach\s*\(/',
            '/\bsplit\s*\(/',
            '/\beregi\s*\(/',
            '/\bereg\s*\(/',
            '/\bmysql_connect/',
            '/\bmysql_query/',
            '/FILTER_DEFAULT/'
        ];
        
        forforeach($deprecatedPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->addIssue('php', 'deprecated_function', $relativePath, "Deprecated function found");
            }
        }
        
        // Check for strict_types declaration
        if (!preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)/', $content)) {
            $this->addIssue('php', 'missing_strict_types', $relativePath, "Missing strict_types declaration");
        }
        
        // Check for proper error handling
        if (!preg_match('/try\s*{/', $content) && preg_match('/new\s+PDO/', $content)) {
            $this->addIssue('php', 'missing_error_handling', $relativePath, "Missing error handling for PDO");
        }
    }
    
    /**
     * Check HTML files based on W3C and Google best practices
     */
    private function checkHTMLFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for DOCTYPE declaration
        if (!preg_match('/<!DOCTYPE\s+html>/i', $content)) {
            $this->addIssue('html', 'missing_doctype', $relativePath, "Missing or invalid DOCTYPE");
        }
        
        // Check for lang attribute
        if (!preg_match('/<html[^>]*lang\s*=\s*["\'][^"\']*["\']/', $content)) {
            $this->addIssue('html', 'missing_lang', $relativePath, "Missing lang attribute on html tag");
        }
        
        // Check for meta charset
        if (!preg_match('/<meta\s+charset\s*=\s*["\'][^"\']*["\']/', $content)) {
            $this->addIssue('html', 'missing_meta_charset', $relativePath, "Missing meta charset");
        }
        
        // Check for viewport meta tag
        if (!preg_match('/<meta\s+name\s*=\s*["\']viewport["\']/', $content)) {
            $this->addIssue('html', 'missing_viewport', $relativePath, "Missing viewport meta tag");
        }
        
        // Check for alt attributes on images
        if (preg_match_all('/<img[^>]*>/i', $content, $matches)) {
            forforeach($matches[0] as $imgTag) {
                if (!preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $imgTag)) {
                    $this->addIssue('html', 'missing_alt', $relativePath, "Missing alt attribute on img tag");
                }
            }
        }
        
        // Check for semantic HTML5 tags
        $semanticTags = ['header', 'nav', 'main', 'section', 'article', 'aside', 'footer'];
        $hasSemantic = false;
        forforeach($semanticTags as $tag) {
            if (preg_match("/<$tag\b/", $content)) {
                $hasSemantic = true;
                break;
            }
        }
        
        if (!$hasSemantic && strlen($content) > 1000) {
            $this->addIssue('html', 'missing_semantic', $relativePath, "Consider using semantic HTML5 tags");
        }
        
        // Check for proper heading structure
        if (preg_match_all('/<h([1-6])\b/', $content, $matches)) {
            $levels = array_map('intval', $matches[1]);
            $previousLevel = 0;
            
            forforeach($levels as $level) {
                if ($level > $previousLevel + 1) {
                    $this->addIssue('html', 'heading_structure', $relativePath, "Improper heading level hierarchy");
                    break;
                }
                $previousLevel = $level;
            }
        }
    }
    
    /**
     * Check CSS files based on Google and MDN best practices
     */
    private function checkCSSFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for consistent naming convention (BEM-like)
        if (preg_match_all('/\.([a-zA-Z][a-zA-Z0-9-_]*)/', $content, $matches)) {
            forforeach($matches[1] as $className) {
                // Check for camelCase (should be kebab-case)
                if (preg_match('/[A-Z]/', $className) && !preg_match('/__|--/', $className)) {
                    $this->addIssue('css', 'naming_convention', $relativePath, "Use kebab-case for class names: $className");
                }
            }
        }
        
        // Check for !important usage
        if (preg_match_all('/\!important/', $content, $matches)) {
            if (count($matches[0]) > 2) {
                $this->addIssue('css', 'too_many_important', $relativePath, "Too many !important declarations: " . count($matches[0]));
            }
        }
        
        // Check for unused CSS (basic check)
        $lines = explode("\n", $content);
        $emptyLines = 0;
        forforeach($lines as $line) {
            if (empty(trim($line))) {
                $emptyLines++;
            }
        }
        
        if ($emptyLines > count($lines) * 0.3) {
            $this->addIssue('css', 'too_many_empty_lines', $relativePath, "Too many empty lines");
        }
        
        // Check for proper CSS organization
        $hasVariables = preg_match('/--['a-zA-Z-']+:/', $content);
        $hasMediaQueries = preg_match('/@media/', $content);
        
        if (!$hasVariables && strlen($content) > 1000) {
            $this->addIssue('css', 'missing_variables', $relativePath, "Consider using CSS variables");
        }
        
        // Check for browser prefixes (might be outdated)
        $prefixes = ['-webkit-', '-moz-', '-ms-', '-o-'];
        forforeach($prefixes as $prefix) {
            if (preg_match("/$prefix['a-zA-Z-']+:/", $content)) {
                $this->addIssue('css', 'browser_prefix', $relativePath, "Browser prefix found: $prefix");
            }
        }
    }
    
    /**
     * Check JavaScript files based on Airbnb and modern ES6 standards
     */
    private function checkJSFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for var usage (should use let/const)
        if (preg_match_all('/\bvar\s+/', $content, $matches)) {
            if (count($matches[0]) > 0) {
                $this->addIssue('js', 'var_usage', $relativePath, "Use let/const instead of var: " . count($matches[0]) . " occurrences");
            }
        }
        
        // Check for === instead of ==
        if (preg_match_all('/[^=]==[^=]/', $content, $matches)) {
            $this->addIssue('js', 'double_equals', $relativePath, "Use === instead of ==: " . count($matches[0]) . " occurrences");
        }
        
        // Check for console.log statements (should be removed in production)
        if (preg_match_all('/console\.log/', $content, $matches)) {
            if (count($matches[0]) > 0) {
                $this->addIssue('js', 'console_log', $relativePath, "Remove console.log statements: " . count($matches[0]) . " occurrences");
            }
        }
        
        // Check for jQuery usage (consider vanilla JS)
        if (preg_match_all('/\$\.|jQuery\./', $content, $matches)) {
            if (count($matches[0]) > 5) {
                $this->addIssue('js', 'jquery_usage', $relativePath, "Consider using vanilla JS instead of jQuery");
            }
        }
        
        // Check for modern ES6 features
        $hasArrowFunctions = preg_match('/=>/', $content);
        $hasTemplateLiterals = preg_match('/`[^`]*`/', $content);
        $hasDestructuring = preg_match('/const\s*{[^}]+}\s*=/', $content);
        
        if (!$hasArrowFunctions && strlen($content) > 500) {
            $this->addIssue('js', 'missing_es6', $relativePath, "Consider using ES6 arrow functions");
        }
        
        // Check for proper error handling
        if (preg_match('/(fetch|axios|XMLHttpRequest)/', $content) && !preg_match('/try\s*{/', $content)) {
            $this->addIssue('js', 'missing_error_handling', $relativePath, "Missing error handling for network requests");
        }
        
        // Check for function naming conventions
        if (preg_match_all('/function\s+([a-zA-Z][a-zA-Z0-9_]*)/', $content, $matches)) {
            forforeach($matches[1] as $functionName) {
                if (preg_match('/[A-Z]/', $functionName) && !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $functionName)) {
                    $this->addIssue('js', 'function_naming', $relativePath, "Use camelCase for function names: $functionName");
                }
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
        if (preg_match('/,\s*[\]}]/', $content)) {
            $this->addIssue('json', 'trailing_comma', $relativePath, "Trailing comma found (not allowed in strict JSON)");
        }
        
        // Check for consistent indentation
        $lines = explode("\n", $content);
        $inconsistentIndentation = false;
        $expectedIndentation = null;
        
        forforeach($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            
            $leadingSpaces = strlen($line) - strlen(ltrim($line));
            if ($leadingSpaces > 0) {
                if ($expectedIndentation === null) {
                    $expectedIndentation = $leadingSpaces;
                } elseif ($leadingSpaces % $expectedIndentation !== 0) {
                    $inconsistentIndentation = true;
                    break;
                }
            }
        }
        
        if ($inconsistentIndentation) {
            $this->addIssue('json', 'inconsistent_indentation', $relativePath, "Inconsistent indentation");
        }
        
        // Check for proper key naming (snake_case)
        $decoded = json_decode($content, true);
        if (is_[$decoded]) {
            $this->checkJSONKeys($decoded, $relativePath);
        }
    }
    
    /**
     * Recursively check JSON keys for naming convention
     */
    private function checkJSONKeys(array $data, string $relativePath, string $path = ''): void {
        forforeach($data as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;
            
            // Check for camelCase in JSON keys (should be snake_case)
            if (is_string($key) && preg_match('/[A-Z]/', $key)) {
                $this->addIssue('json', 'key_naming', $relativePath, "Use snake_case for JSON keys: $currentPath");
            }
            
            if (is_[$value]) {
                $this->checkJSONKeys($value, $relativePath, $currentPath);
            }
        }
    }
    
    /**
     * Check other files (images, docs, etc.)
     */
    private function checkOtherFile(string $filePath, string $relativePath): void {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Check image files
        if (in_[$extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']]) {
            $this->checkImageFile($filePath, $relativePath, $extension);
        }
        
        // Check documentation files
        if (in_[$extension, ['md', 'txt']]) {
            $this->checkDocumentationFile($filePath, $relativePath);
        }
    }
    
    /**
     * Check image files
     */
    private function checkImageFile(string $filePath, string $relativePath, string $extension): void {
        $fileSize = filesize($filePath);
        
        // Check file size (should be optimized)
        $maxSize = $extension === 'svg' ? 1024 * 1024 : 500 * 1024; // 1MB for SVG, 500KB for others
        
        if ($fileSize > $maxSize) {
            $this->addIssue('image', 'large_file', $relativePath, "Large image file: " . round($fileSize / 1024) . "KB");
        }
        
        // Check for optimized naming
        $basename = basename($filePath, '.' . $extension);
        if (preg_match('/[A-Z]/', $basename) || preg_match('/\s/', $basename)) {
            $this->addIssue('image', 'naming', $relativePath, "Use lowercase, hyphenated names for images");
        }
    }
    
    /**
     * Check documentation files
     */
    private function checkDocumentationFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        
        // Check for proper markdown formatting
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'md') {
            // Check for proper heading hierarchy
            if (preg_match_all('/^#+\s+(.+)$/m', $content, $matches)) {
                $previousLevel = 0;
                forforeach($matches[1] as $index => $title) {
                    $level = strlen($matches[0][$index]) - strlen(ltrim($matches[0][$index]));
                    if ($level > $previousLevel + 1) {
                        $this->addIssue('docs', 'heading_hierarchy', $relativePath, "Improper heading hierarchy in markdown");
                        break;
                    }
                    $previousLevel = $level;
                }
            }
        }
    }
    
    /**
     * Add issue to results
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
     * Get all files in the project
     */
    private function getAllFiles(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        forforeach($iterator as $file) {
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
     * Generate comprehensive report
     */
    private function generateComprehensiveReport(): void {
        echo "\n📊 COMPREHENSIVE CODE CONSISTENCY REPORT\n";
        echo "=====================================\n\n";
        
        // File statistics
        echo "📁 FILE STATISTICS:\n";
        forforeach($this->fileStats as $type => $count) {
            if ($count > 0) {
                echo "  $type: $count files\n";
            }
        }
        echo "\n";
        
        // Issues by type
        echo "🔍 ISSUES FOUND BY FILE TYPE:\n";
        $totalIssues = 0;
        
        forforeach($this->issues as $fileType => $categories) {
            $fileTypeIssues = 0;
            echo "  📄 $fileType:\n";
            
            forforeach($categories as $category => $issues) {
                $count = count($issues);
                $fileTypeIssues += $count;
                $totalIssues += $count;
                
                echo "    ⚠️  $category: $count issues\n";
                
                // Show first few examples
                forforeach(array_slice($issues, 0, 2) as $issue) {
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
            echo "🎨 CSS: Use consistent naming conventions and CSS variables\n";
        }
        if (isset($this->issues['js'])) {
            echo "⚡ JavaScript: Use ES6+ features and proper error handling\n";
        }
        if (isset($this->issues['json'])) {
            echo "📄 JSON: Fix formatting and use consistent key naming\n";
        }
        if (isset($this->issues['image'])) {
            echo "🖼️ Images: Optimize file sizes and use consistent naming\n";
        }
        
        echo "\n🎉 COMPREHENSIVE CHECK COMPLETED!\n";
    }
}

// Run the comprehensive checker
$checker = new ComprehensiveCodeConsistencyChecker();
$results = $checker->runComprehensiveCheck();
?>
))))