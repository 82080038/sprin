<?php
/**
 * URL Helper Integrator for SPRIN Application
 * Integrates URL helper functions into all PHP files
 */

declare(strict_types=1);

class URLHelperIntegrator {
    private $basePath;
    private $filesUpdated = [];

    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }

    /**
     * Integrate URL helper into all PHP files
     */
    public function integrateURLHelper(): array {
        echo "🔗 Starting URL Helper Integration...\n";

        // Get all PHP files
        $phpFiles = $this->getAllPHPFiles();

        foreach ($phpFiles as $file) {
            $this->integrateFile($file);
        }

        echo "\n📊 Integration Summary:\n";
        echo "Files Updated: " . count($this->filesUpdated) . "\n";
        echo "Total PHP Files: " . count($phpFiles) . "\n";
        echo "Integration Rate: " . round((count($this->filesUpdated) / count($phpFiles)) * 100, 1) . "%\n";

        return $this->filesUpdated;
    }

    /**
     * Get all PHP files
     */
    private function getAllPHPFiles(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Skip certain directories
                $path = $file->getPathname();
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
     * Integrate URL helper into a specific file
     */
    private function integrateFile(string $filePath): void {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        $changes = [];

        // Skip if already integrated
        if (strpos($content, 'url_helper.php') !== false) {
            return;
        }

        // Add URL helper include after config.php
        if (strpos($content, 'config.php') !== false) {
            $content = preg_replace(
                '/(require_once\s+[\'"][^\'"]*config\.php[\'"];)/',
                '$1' . "\nrequire_once __DIR__ . \'/url_helper.php\';",
                $content
            );
            $changes[] = 'Added url_helper.php include';
        }

        // Replace hardcoded base URLs
        $patterns = [
            // Base URLs
            '/["\']http:\/\/localhost\/sprint["\']/' => 'base_url()',
            '/["\']http:\/\/localhost\/sprint\//' => 'base_url(\'',

            // Page URLs
            '/["\']pages\/([^"\']+)["\']/' => 'page_url(\'$1\')',
            '/["\']\.\.\/pages\/([^"\']+)["\']/' => 'page_url(\'$1\')',

            // API URLs
            '/["\']api\/([^"\']+)["\']/' => 'api_url(\'$1\')',
            '/["\']\.\.\/api\/([^"\']+)["\']/' => 'api_url(\'$1\')',

            // Asset URLs
            '/["\']public\/assets\/([^"\']+)["\']/' => 'asset_url(\'$1\')',
            '/["\']assets\/([^"\']+)["\']/' => 'asset_url(\'$1\')',

            // Header redirects
            '/header\s*\(\s*["\']Location:\s*([^"\']+)["\']\s*\)/' => 'safe_redirect(\'$1\')',
            '/header\s*\(\s*["\']Location:\s*([^"\']+)["\']\s*,\s*(\d+)\s*\)/' => 'safe_redirect(\'$1\', $2)',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $count = 0;
            $content = preg_replace($pattern, $replacement, $content, -1, $count);
            if ($count > 0) {
                $changes[] = "Replaced pattern: " . substr($pattern, 0, 30) . "...";
            }
        }

        // Write back if changed
        if ($content !== $originalContent && !empty($changes)) {
            file_put_contents($filePath, $content);

            $this->filesUpdated[] = [
                'file' => str_replace($this->basePath . '/', '', $filePath),
                'changes' => $changes
            ];

            echo "✅ Updated: " . str_replace($this->basePath . '/', '', $filePath) . "\n";
        }
    }

    /**
     * Verify integration
     */
    public function verifyIntegration(): array {
        echo "\n🔍 Verifying URL Helper Integration...\n";

        $verification = [
            'files_with_url_helper' => 0,
            'files_using_base_url' => 0,
            'files_using_page_url' => 0,
            'files_using_api_url' => 0,
            'files_using_asset_url' => 0,
            'files_using_safe_redirect' => 0
        ];

        $phpFiles = $this->getAllPHPFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'url_helper.php') !== false) {
                $verification['files_with_url_helper']++;
            }
            if (strpos($content, 'base_url(') !== false) {
                $verification['files_using_base_url']++;
            }
            if (strpos($content, 'page_url(') !== false) {
                $verification['files_using_page_url']++;
            }
            if (strpos($content, 'api_url(') !== false) {
                $verification['files_using_api_url']++;
            }
            if (strpos($content, 'asset_url(') !== false) {
                $verification['files_using_asset_url']++;
            }
            if (strpos($content, 'safe_redirect(') !== false) {
                $verification['files_using_safe_redirect']++;
            }
        }

        echo "📊 Verification Results:\n";
        echo "Files with URL Helper: " . $verification['files_with_url_helper'] . "\n";
        echo "Files using base_url(): " . $verification['files_using_base_url'] . "\n";
        echo "Files using page_url(): " . $verification['files_using_page_url'] . "\n";
        echo "Files using api_url(): " . $verification['files_using_api_url'] . "\n";
        echo "Files using asset_url(): " . $verification['files_using_asset_url'] . "\n";
        echo "Files using safe_redirect(): " . $verification['files_using_safe_redirect'] . "\n";

        return $verification;
    }

    /**
     * Test integrated URLs
     */
    public function testIntegratedURLs(): array {
        echo "\n🧪 Testing Integrated URLs...\n";

        $testResults = [];

        // Test key files
        $keyFiles = [
            'pages/main.php',
            'pages/personil.php',
            'pages/bagian.php',
            'login.php',
            'index.php'
        ];

        foreach ($keyFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);

                $testResults[$file] = [
                    'has_url_helper' => strpos($content, 'url_helper.php') !== false,
                    'has_base_url' => strpos($content, 'base_url(') !== false,
                    'has_page_url' => strpos($content, 'page_url(') !== false,
                    'has_api_url' => strpos($content, 'api_url(') !== false,
                    'has_asset_url' => strpos($content, 'asset_url(') !== false,
                    'has_safe_redirect' => strpos($content, 'safe_redirect(') !== false,
                ];

                echo "📄 Testing {$file}:\n";
                foreach ($testResults[$file] as $feature => $result) {
                    echo "  " . ($result ? "✅" : "❌") . " " . $feature . "\n";
                }
                echo "\n";
            }
        }

        return $testResults;
    }

    /**
     * Run complete integration process
     */
    public function runCompleteIntegration(): array {
        echo "🚀 Starting Complete URL Helper Integration...\n";

        // Step 1: Integrate URL helper
        $integrationResults = $this->integrateURLHelper();

        // Step 2: Verify integration
        $verificationResults = $this->verifyIntegration();

        // Step 3: Test integrated URLs
        $testResults = $this->testIntegratedURLs();

        echo "\n🎉 URL Helper Integration Completed!\n";

        return [
            'integration' => $integrationResults,
            'verification' => $verificationResults,
            'tests' => $testResults
        ];
    }
}

// Run the integrator
$integrator = new URLHelperIntegrator();
$results = $integrator->runCompleteIntegration();

echo "\n🎯 Integration Summary:\n";
echo "Files Updated: " . count($results['integration']) . "\n";
echo "Files with URL Helper: " . $results['verification']['files_with_url_helper'] . "\n";
echo "Files using helper functions: " . (
    $results['verification']['files_using_base_url'] +
    $results['verification']['files_using_page_url'] +
    $results['verification']['files_using_api_url'] +
    $results['verification']['files_using_asset_url']
) . "\n";
?>
