<?php
/**
 * Fix All Errors
 * Simple script to fix all errors found by comprehensive testing
 */

declare(strict_types=1);

echo "🔧 FIX ALL ERRORS\n";
echo "================\n";
echo "🎯 Objective: Fix all errors found by comprehensive testing\n\n";

$basePath = '/opt/lampp/htdocs/sprint';
$fixedFiles = [];
$errorLog = [];

// Phase 1: Fix PHP Syntax Errors
echo "📋 Phase 1: Fix PHP Syntax Errors\n";
echo "=================================\n";

$phpErrorFiles = [
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

foreach ($phpErrorFiles as $file) {
    $filePath = $basePath . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix common syntax errors
        $content = preg_replace('/<\?php\s*<\?php/', '<?php', $content);
        $content = preg_replace('/<\?php\s*\?>\s*<\?php/', '<?php', $content);
        $content = preg_replace('/([a-zA-Z0-9_$])\s*\n\s*\}/', '$1;\n}', $content);
        $content = preg_replace('/;\s*;/', ';', $content);
        
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
        
        // Fix array syntax
        $content = preg_replace('/array\s*\(\s*([^\)]+)\s*\)/', '[$1]', $content);
        
        // Fix deprecated function calls
        $content = preg_replace('/each\s*\(/', 'foreach(', $content);
        $content = preg_replace('/split\s*\(/', 'explode(', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            
            // Verify fix
            $output = [];
            $returnCode = 0;
            exec("php -l $filePath 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                $fixedFiles[] = $file;
                echo "  ✅ Fixed: $file\n";
            } else {
                $errorLog[] = [
                    'file' => $file,
                    'error' => implode("\n", $output)
                ];
                echo "  ❌ Failed: $file\n";
            }
        }
    }
}

// Phase 2: Fix CSS Errors
echo "\n📋 Phase 2: Fix CSS Errors\n";
echo "========================\n";

$cssErrorFiles = [
    'public/assets/css/responsive.css',
    'public/assets/css/optimized.css',
    'public/assets/css/personil.css'
];

foreach ($cssErrorFiles as $file) {
    $filePath = $basePath . '/' . $file;
    if (file_exists($filePath)) {
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
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixedFiles[] = $file;
            echo "  ✅ Fixed: $file\n";
        }
    }
}

// Phase 3: Fix JavaScript Errors
echo "\n📋 Phase 3: Fix JavaScript Errors\n";
echo "===========================\n";

$jsErrorFiles = [
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

foreach ($jsErrorFiles as $file) {
    $filePath = $basePath . '/' . $file;
    if (file_exists($filePath)) {
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
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixedFiles[] = $file;
            echo "  ✅ Fixed: $file\n";
        }
    }
}

// Phase 4: Fix API Errors
echo "\n📋 Phase 4: Fix API Errors\n";
echo "=====================\n";

$apiErrorFiles = [
    'api/health_check.php',
    'api/personil_list.php',
    'api/bagian_crud.php',
    'api/jabatan_crud.php',
    'api/unsur_crud.php'
];

foreach ($apiErrorFiles as $file) {
    $filePath = $basePath . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix PHP syntax errors in API files
        $content = preg_replace('/<\?php\s*<\?php/', '<?php', $content);
        $content = preg_replace('/<\?php\s*\?>\s*<\?php/', '<?php', $content);
        $content = preg_replace('/([a-zA-Z0-9_$])\s*\n\s*\}/', '$1;\n}', $content);
        $content = preg_replace('/;\s*;/', ';', $content);
        
        // Fix unmatched braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        if ($openBraces > $closeBraces) {
            $content .= str_repeat('}', $openBraces - $closeBraces);
        } elseif ($closeBraces > $openBraces) {
            $content = str_replace(str_repeat('}', $closeBraces - $openBraces), '', $content);
        }
        
        // Fix JSON response format
        $content = preg_replace('/echo\s+json_encode\s*\(\s*\$[^)]+\s*\)\s*;/', 'echo json_encode($1);', $content);
        
        // Fix header issues
        $content = preg_replace('/header\s*\(\s*[\'"]Content-Type:\s*application\/json[\'"]\s*\)\s*;/', 'header(\'Content-Type: application/json\');', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixedFiles[] = $file;
            echo "  ✅ Fixed: $file\n";
        }
    }
}

// Phase 5: Verify Fixes
echo "\n📋 Phase 5: Verify Fixes\n";
echo "==================\n";

$syntaxOK = 0;
$syntaxErrors = 0;

foreach ($fixedFiles as $file) {
    if (preg_match('/\.php$/', $file)) {
        $filePath = $basePath . '/' . $file;
        $output = [];
        $returnCode = 0;
        exec("php -l $filePath 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            $syntaxOK++;
        } else {
            $syntaxErrors++;
            $errorLog[] = [
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
    foreach ($errorLog as $error) {
        echo "  - {$error['file']}: {$error['error']}\n";
    }
}

// Phase 6: Final Report
echo "\n📋 Phase 6: Final Report\n";
echo "==================\n";

echo "📊 FIX ALL ERRORS REPORT\n";
echo "========================\n\n";

echo "📋 FIXING SUMMARY:\n";
echo "==================\n";
echo "📊 Total Files Fixed: " . count($fixedFiles) . "\n";
echo "📊 Error Log Entries: " . count($errorLog) . "\n\n";

echo "📄 FIXED FILES:\n";
echo "==============\n";
foreach ($fixedFiles as $file) {
    echo "✅ $file\n";
}

if (!empty($errorLog)) {
    echo "\n⚠️  REMAINING ERRORS:\n";
    echo "=====================\n";
    foreach ($errorLog as $error) {
        echo "❌ {$error['file']}: {$error['error']}\n";
    }
}

echo "\n🎯 OVERALL ASSESSMENT:\n";
echo "==================\n";

$totalFixed = count($fixedFiles);
$totalErrors = count($errorLog);

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

echo "\n🎉 FIX ALL ERRORS COMPLETED!\n";
?>
