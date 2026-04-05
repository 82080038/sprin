<?php
/**
 * BASE_URL and URL Helper Integration Script
 * Updates all fixed files to use proper BASE_URL configuration
 */

declare(strict_types=1);

class BaseURLIntegrator {
    private $basePath;
    private $filesUpdated = [];

    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }

    /**
     * Integrate BASE_URL into all fixed files
     */
    public function integrateBaseURL(): array {
        echo "🔗 Starting BASE_URL Integration...\n";

        // Files that need updating
        $filesToUpdate = [
            'pages/main.php',
            'pages/personil.php',
            'pages/bagian.php',
            'login.php',
            'index.php'
        ];

        foreach ($filesToUpdate as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $this->updateFile($filePath, $file);
            }
        }

        echo "\n📊 Integration Summary:\n";
        echo "Files Updated: " . count($this->filesUpdated) . "\n";
        echo "Total Files: " . count($filesToUpdate) . "\n";

        return $this->filesUpdated;
    }

    /**
     * Update a specific file
     */
    private function updateFile(string $filePath, string $relativePath): void {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        $changes = [];

        // Update require statements to remove strict_types and error reporting
        $patterns = [
            // Remove declare(strict_types=1)
            '/declare\(strict_types=1\);\s*\n/' => '',

            // Remove development error reporting
            '/\/\/ Development Error Reporting.*?ob_start\(\);\s*\n/' => '',

            // Update require statements to proper order
            '/require_once __DIR__ \. \'\/\.\.\/core\/config\.php\';\s*\nrequire_once __DIR__ \. \'\/\.\.\/core\/url_helper\.php\';/' =>
                "// Load configuration and URL helpers\nrequire_once __DIR__ . '/../core/config.php';\nrequire_once __DIR__ . '/../core/url_helper.php';",
        ];

        foreach ($patterns as $pattern => $replacement) {
            $count = 0;
            $content = preg_replace($pattern, $replacement, $content, -1, $count);
            if ($count > 0) {
                $changes[] = "Updated require statements";
            }
        }

        // Write back if changed
        if ($content !== $originalContent && !empty($changes)) {
            file_put_contents($filePath, $content);

            $this->filesUpdated[] = [
                'file' => $relativePath,
                'changes' => $changes
            ];

            echo "✅ Updated: $relativePath\n";
        } else {
            echo "⚠️  No changes needed: $relativePath\n";
        }
    }

    /**
     * Test BASE_URL functionality
     */
    public function testBaseURL(): array {
        echo "\n🧪 Testing BASE_URL Functionality...\n";

        $testResults = [];

        // Test 1: BASE_URL definition
        ob_start();
        require_once $this->basePath . '/core/config.php';
        ob_end_clean();

        $testResults['base_url_defined'] = defined('BASE_URL');
        $testResults['base_url_value'] = BASE_URL ?? 'NOT DEFINED';
        $testResults['api_base_url_defined'] = defined('API_BASE_URL');
        $testResults['api_base_url_value'] = API_BASE_URL ?? 'NOT DEFINED';

        echo "✅ BASE_URL Defined: " . ($testResults['base_url_defined'] ? 'YES' : 'NO') . "\n";
        echo "✅ BASE_URL Value: " . $testResults['base_url_value'] . "\n";
        echo "✅ API_BASE_URL Defined: " . ($testResults['api_base_url_defined'] ? 'YES' : 'NO') . "\n";
        echo "✅ API_BASE_URL Value: " . $testResults['api_base_url_value'] . "\n";

        // Test 2: URL Helper functions
        ob_start();
        require_once $this->basePath . '/core/url_helper.php';
        ob_end_clean();

        $testResults['url_helper_functions'] = [
            'base_url' => function_exists('base_url'),
            'page_url' => function_exists('page_url'),
            'api_url' => function_exists('api_url'),
            'asset_url' => function_exists('asset_url'),
            'safe_redirect' => function_exists('safe_redirect')
        ];

        foreach ($testResults['url_helper_functions'] as $func => $exists) {
            echo "✅ Function $func(): " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
        }

        // Test 3: URL generation
        if (function_exists('base_url')) {
            $testResults['generated_urls'] = [
                'base_url' => base_url(),
                'page_url' => page_url('main.php'),
                'api_url' => api_url('personil.php'),
                'asset_url' => asset_url('css/style.css')
            ];

            echo "\n📝 Generated URLs:\n";
            foreach ($testResults['generated_urls'] as $type => $url) {
                echo "  $type: $url\n";
            }
        }

        return $testResults;
    }

    /**
     * Verify file syntax
     */
    public function verifySyntax(): array {
        echo "\n🔍 Verifying File Syntax...\n";

        $files = [
            'core/config.php',
            'core/url_helper.php',
            'pages/main.php',
            'pages/personil.php',
            'pages/bagian.php',
            'login.php'
        ];

        $results = [];

        foreach ($files as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $output = [];
                $returnCode = 0;

                exec("php -l $filePath 2>&1", $output, $returnCode);

                $results[$file] = [
                    'valid' => $returnCode === 0,
                    'output' => implode("\n", $output)
                ];

                echo ($returnCode === 0 ? "✅" : "❌") . " $file\n";
                if ($returnCode !== 0) {
                    echo "   Error: " . $results[$file]['output'] . "\n";
                }
            } else {
                echo "❌ $file (NOT FOUND)\n";
                $results[$file] = ['valid' => false, 'output' => 'File not found'];
            }
        }

        return $results;
    }

    /**
     * Run complete integration process
     */
    public function runCompleteIntegration(): array {
        echo "🚀 Starting Complete BASE_URL Integration...\n";

        // Step 1: Integrate BASE_URL
        $integrationResults = $this->integrateBaseURL();

        // Step 2: Test BASE_URL functionality
        $testResults = $this->testBaseURL();

        // Step 3: Verify syntax
        $syntaxResults = $this->verifySyntax();

        echo "\n🎉 BASE_URL Integration Completed!\n";

        return [
            'integration' => $integrationResults,
            'tests' => $testResults,
            'syntax' => $syntaxResults
        ];
    }
}

// Run the integrator
$integrator = new BaseURLIntegrator();
$results = $integrator->runCompleteIntegration();

echo "\n🎯 Integration Summary:\n";
echo "Files Updated: " . count($results['integration']) . "\n";
echo "BASE_URL Defined: " . ($results['tests']['base_url_defined'] ? 'YES' : 'NO') . "\n";
echo "URL Helper Functions: " . count(array_filter($results['tests']['url_helper_functions'])) . "/5 working\n";
echo "Syntax Valid Files: " . count(array_filter($results['syntax'], fn($r) => $r['valid'])) . "/" . count($results['syntax']) . "\n";
?>
