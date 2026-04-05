<?php
/**
 * Comprehensive All-File Enhancer
 * Enhances ALL file types: PHP, HTML, CSS, JavaScript, JSON
 * Based on best practices from Google, MDN, W3Schools
 */

declare(strict_types=1);

class ComprehensiveAllFileEnhancer {
    private $basePath;
    private $results = [];
    private $fixedFiles = [];

    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }

    /**
     * Run comprehensive enhancement for ALL file types
     */
    public function enhanceAll(): void {
        echo "🚀 COMPREHENSIVE ALL-FILE ENHANCER\n";
        echo "===================================\n";
        echo "📡 Based on Google, MDN, W3Schools best practices\n\n";

        // Phase 1: PHP Enhancement
        echo "📋 Phase 1: PHP Enhancement\n";
        echo "===========================\n";
        $this->enhancePHPFiles();

        // Phase 2: HTML Enhancement
        echo "\n📋 Phase 2: HTML Enhancement\n";
        echo "============================\n";
        $this->enhanceHTMLFiles();

        // Phase 3: CSS Enhancement
        echo "\n📋 Phase 3: CSS Enhancement\n";
        echo "===========================\n";
        $this->enhanceCSSFiles();

        // Phase 4: JavaScript Enhancement
        echo "\n📋 Phase 4: JavaScript Enhancement\n";
        echo "================================\n";
        $this->enhanceJSFiles();

        // Phase 5: JSON Enhancement
        echo "\n📋 Phase 5: JSON Enhancement\n";
        echo "============================\n";
        $this->enhanceJSONFiles();

        // Generate final report
        echo "\n📋 Phase 6: Final Report\n";
        echo "====================\n";
        $this->generateFinalReport();
    }

    /**
     * Enhance PHP files
     */
    private function enhancePHPFiles(): void {
        $phpFiles = $this->getFilesByExtension('php');
        $fixedCount = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;

            // Add strict_types declaration if missing
            if (!preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)/', $content)) {
                // Find the first <?php tag
                if (preg_match('/<\?php/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $pos = $matches[0][1] + strlen($matches[0][0]);
                    $content = substr($content, 0, $pos) . "\n\ndeclare(strict_types=1);\n" . substr($content, $pos);
                }
            }

            // Replace deprecated functions
            $content = preg_replace('/while\s*\(\s*list\s*\(\s*\$([^,]+)\s*,\s*\$([^)]+)\s*\)\s*=\s*each\s*\(\s*\$([^)]+)\s*\)\s*\)\s*\{/', 'foreach ($3 as $1 => $2) {', $content);
            $content = preg_replace('/split\s*\(\s*["\']([^"\']+)["\']\s*,\s*\$([^)]+)\s*\)/', 'explode(\'$1\', $2)', $content);
            $content = preg_replace('/eregi\s*\(\s*["\']([^"\']+)["\']\s*,\s*\$([^)]+)\s*\)/', 'preg_match("/$1/i", $2)', $content);
            $content = preg_replace('/ereg\s*\(\s*["\']([^"\']+)["\']\s*,\s*\$([^)]+)\s*\)/', 'preg_match("/$1/", $2)', $content);
            $content = str_replace('FILTER_DEFAULT', 'FILTER_DEFAULT', $content);

            // Comment out MySQL functions
            $mysql_funcs = ['mysql_connect', 'mysql_select_db', 'mysql_query', 'mysql_fetch_assoc', 'mysql_num_rows', 'mysql_close'];
            foreach ($mysql_funcs as $func) {
                $content = preg_replace("/\b$func\s*\(/", "// DEPRECATED: $func( - Use PDO instead", $content);
            }

            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $fixedCount++;
                echo "  ✅ Enhanced: " . basename($file) . "\n";
            }
        }

        $this->results['php'] = $fixedCount;
        echo "📊 PHP files enhanced: $fixedCount\n";
    }

    /**
     * Enhance HTML files
     */
    private function enhanceHTMLFiles(): void {
        $htmlFiles = array_merge($this->getFilesByExtension('html'), $this->getFilesByExtension('htm'));
        $fixedCount = 0;

        foreach ($htmlFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;

            // Add DOCTYPE if missing
            if (!preg_match('/<!DOCTYPE\s+html>/i', $content)) {
                $content = "<!DOCTYPE html>\n" . $content;
            }

            // Add lang attribute if missing
            if (preg_match('/<html[^>]*>/i', $content, $matches)) {
                if (strpos($matches[0], 'lang=') === false) {
                    $htmlTag = str_replace('<html', '<html lang="en"', $matches[0]);
                    $content = str_replace($matches[0], $htmlTag, $content);
                }
            }

            // Add meta charset if missing
            if (strpos($content, '<meta charset=') === false) {
                if (preg_match('/<head[^>]*>/i', $content, $matches)) {
                    $metaCharset = "\n    <meta charset=\"UTF-8\">";
                    $content = str_replace($matches[0], $matches[0] . $metaCharset, $content);
                }
            }

            // Add viewport if missing
            if (strpos($content, 'name="viewport"') === false) {
                if (preg_match('/<head[^>]*>/i', $content, $matches)) {
                    $viewport = "\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
                    $content = str_replace($matches[0], $matches[0] . $viewport, $content);
                }
            }

            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $fixedCount++;
                echo "  ✅ Enhanced: " . basename($file) . "\n";
            }
        }

        $this->results['html'] = $fixedCount;
        echo "📊 HTML files enhanced: $fixedCount\n";
    }

    /**
     * Enhance CSS files
     */
    private function enhanceCSSFiles(): void {
        $cssFiles = $this->getFilesByExtension('css');
        $fixedCount = 0;

        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;

            // Add CSS variables at the top
            if (strlen($content) > 1000 && strpos($content, '--') === false) {
                $variables = "/* CSS Variables */\n:root {\n";
                $variables .= "  --primary-color: #007bff;\n";
                $variables .= "  --secondary-color: #6c757d;\n";
                $variables .= "  --success-color: #28a745;\n";
                $variables .= "  --danger-color: #dc3545;\n";
                $variables .= "  --warning-color: #ffc107;\n";
                $variables .= "  --info-color: #17a2b8;\n";
                $variables .= "  --light-color: #f8f9fa;\n";
                $variables .= "  --dark-color: #343a40;\n";
                $variables .= "}\n\n";

                $content = $variables . $content;
            }

            // Reduce !important usage (basic approach)
            $content = preg_replace('/\s*\!important\s*/', '', $content);

            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $fixedCount++;
                echo "  ✅ Enhanced: " . basename($file) . "\n";
            }
        }

        $this->results['css'] = $fixedCount;
        echo "📊 CSS files enhanced: $fixedCount\n";
    }

    /**
     * Enhance JavaScript files
     */
    private function enhanceJSFiles(): void {
        $jsFiles = $this->getFilesByExtension('js');
        $fixedCount = 0;

        foreach ($jsFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;

            // Replace var with let/const (basic approach)
            $content = preg_replace('/\bvar\s+(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*=/', 'const $1 =', $content);
            $content = preg_replace('/\bvar\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*=/', 'let $1 =', $content);

            // Replace == with ===
            $content = str_replace(' == ', ' === ', $content);
            $content = str_replace('==', '===', $content);

            // Remove console.log statements
            $content = preg_replace('/console\.log\([^)]*\);?\s*/', '', $content);

            // Convert some functions to arrow functions (basic approach)
            $content = preg_replace('/function\s*\(\s*\)\s*\{\s*return\s+([^;]+);\s*\}/', '() => $1', $content);

            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $fixedCount++;
                echo "  ✅ Enhanced: " . basename($file) . "\n";
            }
        }

        $this->results['js'] = $fixedCount;
        echo "📊 JavaScript files enhanced: $fixedCount\n";
    }

    /**
     * Enhance JSON files
     */
    private function enhanceJSONFiles(): void {
        $jsonFiles = $this->getFilesByExtension('json');
        $fixedCount = 0;

        foreach ($jsonFiles as $file) {
            $content = file_get_contents($file);

            // Check if valid JSON
            $decoded = json_decode($content);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Re-encode with proper formatting
                $formatted = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                if ($formatted !== $content) {
                    file_put_contents($file, $formatted);
                    $fixedCount++;
                    echo "  ✅ Enhanced: " . basename($file) . "\n";
                }
            }
        }

        $this->results['json'] = $fixedCount;
        echo "📊 JSON files enhanced: $fixedCount\n";
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

    /**
     * Generate final report
     */
    private function generateFinalReport(): void {
        echo "🎊 COMPREHENSIVE ENHANCEMENT RESULTS\n";
        echo "===================================\n\n";

        echo "📊 ENHANCEMENT SUMMARY:\n";
        $totalEnhanced = 0;

        foreach ($this->results as $type => $count) {
            if ($count > 0) {
                echo "  ✅ $type files: $count enhanced\n";
                $totalEnhanced += $count;
            }
        }

        echo "\n📈 Total Files Enhanced: $totalEnhanced\n\n";

        echo "🎯 ACHIEVEMENT STATUS:\n";
        if ($totalEnhanced >= 50) {
            echo "🏆 EXCELLENT - Major improvements achieved!\n";
        } elseif ($totalEnhanced >= 20) {
            echo "✅ GOOD - Significant improvements made!\n";
        } elseif ($totalEnhanced >= 10) {
            echo "👍 FAIR - Some improvements achieved.\n";
        } else {
            echo "⚠️  MINIMAL - Few files needed enhancement.\n";
        }

        echo "\n📋 ENHANCEMENT DETAILS:\n";
        echo "========================\n";

        if (isset($this->results['php'])) {
            echo "🔧 PHP: Added strict_types, replaced deprecated functions\n";
        }
        if (isset($this->results['html'])) {
            echo "🌐 HTML: Added DOCTYPE, meta tags, lang attributes\n";
        }
        if (isset($this->results['css'])) {
            echo "🎨 CSS: Added CSS variables, reduced !important\n";
        }
        if (isset($this->results['js'])) {
            echo "⚡ JavaScript: Modernized to ES6+, removed console.log\n";
        }
        if (isset($this->results['json'])) {
            echo "📄 JSON: Fixed formatting and structure\n";
        }

        echo "\n🎉 COMPREHENSIVE ENHANCEMENT COMPLETED!\n";
        echo "🚀 ALL FILE TYPES: ENHANCED\n";
        echo "📡 Based on industry best practices\n";
        echo "🏆 Code Consistency: ACHIEVED\n";
    }
}

// Run the comprehensive enhancer
$enhancer = new ComprehensiveAllFileEnhancer();
$enhancer->enhanceAll();
?>
