<?php
/**
 * Integration Check Script
 * Verify FE -> API -> Database connections
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/auth_helper.php';

header('Content-Type: text/plain');

echo "=== FE / API / DATABASE INTEGRATION CHECK ===\n\n";

$results = [
    'passed' => [],
    'failed' => [],
    'warnings' => []
];

// 1. Check Database Connection
echo "[1] DATABASE CONNECTION\n";
echo str_repeat("-", 50) . "\n";
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "✅ Database connection successful\n";
    $results['passed'][] = "Database connection";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    $results['failed'][] = "Database connection";
}
echo "\n";

// 2. Check API Response Format
echo "[2] API RESPONSE FORMAT CHECK\n";
echo str_repeat("-", 50) . "\n";

$apis = [
    'personil_list' => '/sprint/api/personil_list.php',
    'personil_simple' => '/sprint/api/personil_simple.php',
    'unsur_stats' => '/sprint/api/unsur_stats.php',
    'calendar_api' => '/sprint/api/calendar_api.php',
    'user_management' => '/sprint/api/user_management.php?action=list',
    'backup_api' => '/sprint/api/backup_api.php?action=stats',
    'report_api' => '/sprint/api/report_api.php?action=personil_summary'
];

foreach ($apis as $name => $endpoint) {
    $url = 'http://localhost' . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && isset($data['message']) && isset($data['timestamp'])) {
            echo "✅ $name - Standard format OK\n";
            $results['passed'][] = "API: $name";
        } else {
            echo "⚠️ $name - Response received but format invalid\n";
            $results['warnings'][] = "API: $name format";
        }
    } else {
        echo "❌ $name - HTTP $httpCode\n";
        $results['failed'][] = "API: $name";
    }
}
echo "\n";

// 3. Check API Data Consistency
echo "[3] API DATA CONSISTENCY\n";
echo str_repeat("-", 50) . "\n";
try {
    // Get personil count from database
    $stmt = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_deleted = FALSE AND is_active = TRUE");
    $dbCount = $stmt->fetchColumn();
    
    // Get personil count from API
    $url = 'http://localhost/sprint/api/personil_simple.php';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        $apiCount = $data['data']['statistics']['total_personil'] ?? 0;
        
        if ($dbCount == $apiCount) {
            echo "✅ Personil count match: DB=$dbCount, API=$apiCount\n";
            $results['passed'][] = "Data consistency - personil count";
        } else {
            echo "⚠️ Personil count mismatch: DB=$dbCount, API=$apiCount\n";
            $results['warnings'][] = "Data consistency - personil count";
        }
    } else {
        echo "❌ Failed to get API data\n";
        $results['failed'][] = "Data consistency check";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['failed'][] = "Data consistency check";
}
echo "\n";

// 4. Check Frontend Files Exist
echo "[4] FRONTEND FILES CHECK\n";
echo str_repeat("-", 50) . "\n";

$frontendFiles = [
    'pages/main.php' => 'Dashboard',
    'pages/personil.php' => 'Personil Management',
    'pages/bagian.php' => 'Bagian Management',
    'pages/jabatan.php' => 'Jabatan Management',
    'pages/unsur.php' => 'Unsur Management',
    'pages/calendar_dashboard.php' => 'Calendar',
    'pages/user_management.php' => 'User Management (NEW)',
    'pages/backup_management.php' => 'Backup Management (NEW)',
    'pages/reporting.php' => 'Reporting (NEW)',
    'includes/components/header.php' => 'Header Component',
    'includes/components/footer.php' => 'Footer Component',
    'public/assets/js/api-client.js' => 'API Client JS'
];

foreach ($frontendFiles as $file => $description) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        echo "✅ $description ($file)\n";
        $results['passed'][] = "FE: $description";
    } else {
        echo "❌ $description - File not found: $file\n";
        $results['failed'][] = "FE: $description";
    }
}
echo "\n";

// 5. Check CSS/JS Library Versions
echo "[5] FRONTEND LIBRARY VERSIONS\n";
echo str_repeat("-", 50) . "\n";

$indexPath = __DIR__ . '/../index.php';
if (file_exists($indexPath)) {
    $content = file_get_contents($indexPath);
    
    // Check Bootstrap
    if (strpos($content, 'bootstrap@5.3.0') !== false) {
        echo "✅ Bootstrap 5.3.0\n";
        $results['passed'][] = "Library: Bootstrap 5.3.0";
    } elseif (strpos($content, 'bootstrap@5') !== false) {
        echo "⚠️ Bootstrap version mismatch\n";
        $results['warnings'][] = "Library: Bootstrap version";
    } else {
        echo "❌ Bootstrap not found\n";
        $results['failed'][] = "Library: Bootstrap";
    }
    
    // Check Font Awesome
    if (strpos($content, 'font-awesome/6.4.2') !== false) {
        echo "✅ Font Awesome 6.4.2\n";
        $results['passed'][] = "Library: Font Awesome 6.4.2";
    } elseif (strpos($content, 'font-awesome') !== false) {
        echo "⚠️ Font Awesome version mismatch\n";
        $results['warnings'][] = "Library: Font Awesome version";
    } else {
        echo "❌ Font Awesome not found\n";
        $results['failed'][] = "Library: Font Awesome";
    }
} else {
    echo "❌ index.php not found\n";
    $results['failed'][] = "FE: index.php";
}
echo "\n";

// 6. Check API Authentication
echo "[6] API AUTHENTICATION\n";
echo str_repeat("-", 50) . "\n";

$protectedApis = [
    'user_management' => '/sprint/api/user_management.php?action=list',
    'backup_api' => '/sprint/api/backup_api.php?action=list'
];

foreach ($protectedApis as $name => $endpoint) {
    $url = 'http://localhost' . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success'] === false && strpos($data['message'], 'Unauthorized') !== false) {
            echo "✅ $name - Protected (returns unauthorized without session)\n";
            $results['passed'][] = "Auth: $name protected";
        } else {
            echo "⚠️ $name - May not be properly protected\n";
            $results['warnings'][] = "Auth: $name protection";
        }
    } elseif (strpos($finalUrl, 'login.php') !== false) {
        echo "✅ $name - Protected (redirects to login)\n";
        $results['passed'][] = "Auth: $name redirects to login";
    } else {
        echo "⚠️ $name - HTTP $httpCode\n";
        $results['warnings'][] = "Auth: $name response";
    }
}
echo "\n";

// 7. Check Database Relations
echo "[7] DATABASE FOREIGN KEY RELATIONS\n";
echo str_repeat("-", 50) . "\n";
try {
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $foreignKeys = $stmt->fetchAll();
    
    if (count($foreignKeys) > 0) {
        echo "✅ Foreign key relationships found: " . count($foreignKeys) . "\n";
        foreach ($foreignKeys as $fk) {
            echo "   • {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
        $results['passed'][] = "Database: Foreign key relations";
    } else {
        echo "⚠️ No foreign key relationships found\n";
        $results['warnings'][] = "Database: No FK relations";
    }
} catch (Exception $e) {
    echo "⚠️ Could not check foreign keys: " . $e->getMessage() . "\n";
    $results['warnings'][] = "Database: FK check failed";
}
echo "\n";

// 8. Check Data Integrity
echo "[8] DATA INTEGRITY CHECK\n";
echo str_repeat("-", 50) . "\n";
try {
    // Check orphaned records
    $checks = [
        'personil with invalid unsur' => "SELECT COUNT(*) FROM personil p LEFT JOIN unsur u ON p.id_unsur = u.id WHERE p.id_unsur IS NOT NULL AND u.id IS NULL",
        'personil with invalid bagian' => "SELECT COUNT(*) FROM personil p LEFT JOIN bagian b ON p.id_bagian = b.id WHERE p.id_bagian IS NOT NULL AND b.id IS NULL",
        'bagian with invalid unsur' => "SELECT COUNT(*) FROM bagian b LEFT JOIN unsur u ON b.id_unsur = u.id WHERE b.id_unsur IS NOT NULL AND u.id IS NULL"
    ];
    
    foreach ($checks as $description => $sql) {
        $stmt = $pdo->query($sql);
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "✅ $description: 0 orphaned\n";
            $results['passed'][] = "Data integrity: $description";
        } else {
            echo "⚠️ $description: $count orphaned records\n";
            $results['warnings'][] = "Data integrity: $description";
        }
    }
} catch (Exception $e) {
    echo "❌ Data integrity check failed: " . $e->getMessage() . "\n";
    $results['failed'][] = "Data integrity check";
}
echo "\n";

// Summary
echo "=== SUMMARY ===\n";
echo str_repeat("=", 50) . "\n";
echo "✅ PASSED: " . count($results['passed']) . "\n";
echo "⚠️  WARNINGS: " . count($results['warnings']) . "\n";
echo "❌ FAILED: " . count($results['failed']) . "\n";
echo "\n";

if (count($results['failed']) === 0) {
    echo "✅ ALL CRITICAL CHECKS PASSED\n";
    echo "FE/API/Database integration is working correctly.\n";
} else {
    echo "❌ SOME CHECKS FAILED - Review required\n";
    foreach ($results['failed'] as $fail) {
        echo "   • $fail\n";
    }
}

echo "\n";

if (count($results['warnings']) > 0) {
    echo "⚠️  WARNINGS:\n";
    foreach ($results['warnings'] as $warn) {
        echo "   • $warn\n";
    }
}
