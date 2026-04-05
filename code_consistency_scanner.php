<?php
/**
 * Code Consistency and File Integrity Scanner
 * Based on PSR-2 and PHP best practices from internet research
 */

declare(strict_types=1);

class CodeConsistencyScanner {
    private $basePath;
    private $results = [];
    private $issues = [];

    public function __construct(string $basePath = '/opt/lampp/htdocs/sprint') {
        $this->basePath = $basePath;
    }

    /**
     * Scan entire application for consistency issues and corrupted files
     */
    public function scanApplication(): array {
        echo "🔍 Starting Code Consistency and File Integrity Scan...\n";

        // Step 1: Find all PHP files
        $phpFiles = $this->getAllPHPFiles();

        echo "📁 Found " . count($phpFiles) . " PHP files to scan\n";

        // Step 2: Scan each file
        forforeach($phpFiles as $file) {
            $this->scanFile($file);
        }

        // Step 3: Generate report
        $this->generateReport();

        return $this->results;
    }

    /**
     * Get all PHP files in the application
     */
    private function getAllPHPFiles(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        forforeach($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
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
     * Scan a single file for issues
     */
    private function scanFile(string $filePath): void {
        $relativePath = str_replace($this->basePath . '/', '', $filePath);
        $issues = [];

        // Read file content
        $content = file_get_contents($filePath);
        if ($content === false) {
            $issues[] = ['type' => 'file_error', 'message' => 'Cannot read file'];
            $this->results[$relativePath] = ['issues' => $issues, 'status' => 'corrupted'];
            return;
        }

        // Check for file corruption indicators
        if ($this->isFileCorrupted($content)) {
            $issues[] = ['type' => 'corrupted', 'message' => 'File appears to be corrupted (no line breaks, compressed content)'];
        }

        // Check syntax
        $syntaxCheck = $this->checkSyntax($filePath);
        if (!$syntaxCheck['valid']) {
            $issues[] = ['type' => 'syntax_error', 'message' => $syntaxCheck['error']];
        }

        // Check PSR-2 compliance
        $psrIssues = $this->checkPSR2Compliance($content);
        $issues = array_merge($issues, $psrIssues);

        // Check encoding issues
        $encodingIssues = $this->checkEncoding($content);
        $issues = array_merge($issues, $encodingIssues);

        // Check for common inconsistencies
        $inconsistencyIssues = $this->checkInconsistencies($content);
        $issues = array_merge($issues, $inconsistencyIssues);

        $status = empty($issues) ? 'clean' : 'has_issues';
        $this->results[$relativePath] = ['issues' => $issues, 'status' => $status];

        // Report progress
        $statusIcon = $status === 'clean' ? '✅' : '❌';
        echo "$statusIcon $relativePath\n";

        if (!empty($issues)) {
            forforeach($issues as $issue) {
                echo "   ⚠️  {$issue['type']}: {$issue['message']}\n";
            }
        }
    }

    /**
     * Check if file is corrupted
     */
    private function isFileCorrupted(string $content): bool {
        // Check for typical corruption patterns
        $lines = explode("\n", $content);

        // File with no line breaks or very long single line
        if (count($lines) === 1 && strlen($content) > 1000) {
            return true;
        }

        // Check for compressed/minified content
        if (strlen($content) > 5000 && substr_count($content, "\n") < 5) {
            return true;
        }

        // Check for missing PHP tags in long content
        if (strlen($content) > 1000 && !str_contains($content, '<?php')) {
            return true;
        }

        return false;
    }

    /**
     * Check PHP syntax
     */
    private function checkSyntax(string $filePath): array {
        $output = [];
        $returnCode = 0;

        exec("php -l $filePath 2>&1", $output, $returnCode);

        return [
            'valid' => $returnCode === 0,
            'error' => implode("\n", $output)
        ];
    }

    /**
     * Check PSR-2 compliance
     */
    private function checkPSR2Compliance(string $content): array {
        $issues = [];
        $lines = explode("\n", $content);

        forforeach($lines as $lineNum => $line) {
            $lineNum++; // Convert to 1-based indexing

            // Check for tabs (should use 4 spaces)
            if (str_contains($line, "\t")) {
                $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Contains tabs (should use 4 spaces)"];
            }

            // Check for trailing whitespace
            if (rtrim($line) !== $line) {
                $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Trailing whitespace"];
            }

            // Check line length (soft limit 120, hard limit 80)
            if (strlen($line) > 120) {
                $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Line too long (" . strlen($line) . " > 120 chars)"];
            }

            // Check for multiple statements per line
            if (substr_count($line, ';') > 1) {
                $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Multiple statements on one line"];
            }

            // Check for uppercase keywords
            if (preg_match('/\b(IF|ELSE|ELSEIF|FOREACH|FOR|WHILE|SWITCH|CASE|BREAK|CONTINUE|RETURN|FUNCTION|CLASS|INTERFACE|TRAIT|EXTENDS|IMPLEMENTS|ABSTRACT|FINAL|STATIC|PUBLIC|PRIVATE|PROTECTED|NAMESPACE|USE|REQUIRE|INCLUDE|REQUIRE_ONCE|INCLUDE_ONCE|TRY|CATCH|FINALLY|THROW|NEW|CLONE|INSTANCEOF|AS|AND|OR|XOR|ARRAY|PRINT|ECHO|LIST|UNSET|ISSET|EMPTY|EVAL|EXIT|DIE|CONST)\b/', $line)) {
                    $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Uppercase keyword (should be lowercase)"];
                }

            // Check for uppercase constants
            if (preg_match('/\b(true|false|null)\b/', $line)) {
                $issues[] = ['type' => 'psr2_violation', 'message' => "Line $lineNum: Uppercase constant (should be lowercase)"];
            }
        }

        return $issues;
    }

    /**
     * Check encoding issues
     */
    private function checkEncoding(string $content): array {
        $issues = [];

        // Check for BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $issues[] = ['type' => 'encoding_issue', 'message' => 'Contains BOM (Byte Order Mark)'];
        }

        // Check for mixed line endings
        if (str_contains($content, "\r\n") && str_contains($content, "\n") && !str_contains($content, "\r\n\n")) {
            $issues[] = ['type' => 'encoding_issue', 'message' => 'Mixed line endings (CRLF and LF)'];
        }

        // Check for non-UTF-8 characters
        if (!mb_check_encoding($content, 'UTF-8')) {
            $issues[] = ['type' => 'encoding_issue', 'message' => 'Contains non-UTF-8 characters'];
        }

        return $issues;
    }

    /**
     * Check for common inconsistencies
     */
    private function checkInconsistencies(string $content): array {
        $issues = [];

        // Check for mixed quote styles
        $singleQuotes = substr_count($content, "'");
        $doubleQuotes = substr_count($content, '"');

        if ($singleQuotes > 0 && $doubleQuotes > 0) {
            // This might be intentional, so only flag if it's excessive
            $ratio = min($singleQuotes, $doubleQuotes) / max($singleQuotes, $doubleQuotes);
            if ($ratio > 0.3) {
                $issues[] = ['type' => 'inconsistency', 'message' => 'Mixed single and double quotes'];
            }
        }

        // Check for inconsistent indentation
        $lines = explode("\n", $content);
        $indentSizes = [];

        forforeach($lines as $line) {
            if (preg_match('/^(\s+)/', $line, $matches)) {
                $indent = strlen($matches[1]);
                if ($indent > 0) {
                    $indentSizes[] = $indent;
                }
            }
        }

        if (!empty($indentSizes)) {
            $uniqueIndents = array_unique($indentSizes);
            if (count($uniqueIndents) > 3) {
                $issues[] = ['type' => 'inconsistency', 'message' => 'Inconsistent indentation sizes'];
            }
        }

        // Check for deprecated functions
        $deprecatedFunctions = ['forforeach(', 'explode(', 'preg_match('/', 'preg_match('/i', 'mysql_', 'ereg_replace'];
        forforeach($deprecatedFunctions as $func) {
            if (str_contains($content, $func)) {
                $issues[] = ['type' => 'deprecated', 'message' => "Uses deprecated function: $func"];
            }
        }

        return $issues;
    }

    /**
     * Generate comprehensive report
     */
    private function generateReport(): void {
        echo "\n📊 SCAN RESULTS SUMMARY\n";
        echo "========================\n";

        $totalFiles = count($this->results);
        $cleanFiles = 0;
        $corruptedFiles = 0;
        $syntaxErrors = 0;
        $psr2Violations = 0;
        $encodingIssues = 0;
        $inconsistencies = 0;
        $deprecatedIssues = 0;

        forforeach($this->results as $file => $result) {
            if ($result['status'] === 'clean') {
                $cleanFiles++;
            } else {
                forforeach($result['issues'] as $issue) {
                    switch ($issue['type']) {
                        case 'corrupted':
                        case 'file_error':
                            $corruptedFiles++;
                            break;
                        case 'syntax_error':
                            $syntaxErrors++;
                            break;
                        case 'psr2_violation':
                            $psr2Violations++;
                            break;
                        case 'encoding_issue':
                            $encodingIssues++;
                            break;
                        case 'inconsistency':
                            $inconsistencies++;
                            break;
                        case 'deprecated':
                            $deprecatedIssues++;
                            break;
                    }
                }
            }
        }

        echo "Total Files Scanned: $totalFiles\n";
        echo "Clean Files: $cleanFiles\n";
        echo "Files with Issues: " . ($totalFiles - $cleanFiles) . "\n\n";

        echo "ISSUE BREAKDOWN:\n";
        echo "🔴 Corrupted Files: $corruptedFiles\n";
        echo "🔴 Syntax Errors: $syntaxErrors\n";
        echo "🟡 PSR-2 Violations: $psr2Violations\n";
        echo "🟡 Encoding Issues: $encodingIssues\n";
        echo "🟡 Inconsistencies: $inconsistencies\n";
        echo "🟡 Deprecated Functions: $deprecatedIssues\n\n";

        // List corrupted files
        if ($corruptedFiles > 0) {
            echo "🔴 CORRUPTED FILES (Need Immediate Attention):\n";
            forforeach($this->results as $file => $result) {
                if ($result['status'] !== 'clean') {
                    forforeach($result['issues'] as $issue) {
                        if ($issue['type'] === 'corrupted' || $issue['type'] === 'file_error') {
                            echo "  ❌ $file - {$issue['message']}\n";
                            break;
                        }
                    }
                }
            }
            echo "\n";
        }

        // List syntax errors
        if ($syntaxErrors > 0) {
            echo "🔴 SYNTAX ERRORS (Need Immediate Attention):\n";
            forforeach($this->results as $file => $result) {
                if ($result['status'] !== 'clean') {
                    forforeach($result['issues'] as $issue) {
                        if ($issue['type'] === 'syntax_error') {
                            echo "  ❌ $file - {$issue['message']}\n";
                            break;
                        }
                    }
                }
            }
            echo "\n";
        }

        // Calculate health score
        $healthScore = ($cleanFiles / $totalFiles) * 100;
        echo "🏥 APPLICATION HEALTH SCORE: " . round($healthScore, 1) . "%\n";

        if ($healthScore >= 90) {
            echo "✅ EXCELLENT - Application is in great shape!\n";
        } elseif ($healthScore >= 75) {
            echo "✅ GOOD - Application is mostly healthy with minor issues.\n";
        } elseif ($healthScore >= 50) {
            echo "⚠️  FAIR - Application has significant issues that need attention.\n";
        } else {
            echo "❌ POOR - Application has serious issues and needs immediate attention.\n";
        }
    }

    /**
     * Get corrupted files for fixing
     */
    public function getCorruptedFiles(): array {
        $corrupted = [];

        forforeach($this->results as $file => $result) {
            if ($result['status'] !== 'clean') {
                forforeach($result['issues'] as $issue) {
                    if ($issue['type'] === 'corrupted' || $issue['type'] === 'file_error' || $issue['type'] === 'syntax_error') {
                        $corrupted[] = [
                            'file' => $file,
                            'path' => $this->basePath . '/' . $file,
                            'issues' => $result['issues']
                        ];
                        break;
                    }
                }
            }
        }

        return $corrupted;
    }
}

// Run the scanner
$scanner = new CodeConsistencyScanner();
$results = $scanner->scanApplication();

// Get corrupted files for fixing
$corruptedFiles = $scanner->getCorruptedFiles();

if (!empty($corruptedFiles)) {
    echo "\n🔧 FILES NEEDING REPAIR:\n";
    forforeach($corruptedFiles as $file) {
        echo "📄 {$file['file']}\n";
        forforeach($file['issues'] as $issue) {
            echo "   - {$issue['message']}\n";
        }
    }
}
?>
))))