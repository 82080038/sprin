<?php
/**
 * Direct test for predictive analytics dashboard
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING PREDICTIVE DASHBOARD ===\n\n";

// Test 1: Check if file exists
echo "1. Checking if dashboard file exists...\n";
$dashboard_file = '/opt/lampp/htdocs/sprin/pages/predictive_analytics_dashboard.php';
if (file_exists($dashboard_file)) {
    echo "   File exists: YES\n";
    echo "   File size: " . filesize($dashboard_file) . " bytes\n";
} else {
    echo "   File exists: NO\n";
    exit;
}

// Test 2: Check PHP syntax
echo "\n2. Checking PHP syntax...\n";
$output = [];
$return_code = 0;
exec('/opt/lampp/bin/php -l ' . $dashboard_file . ' 2>&1', $output, $return_code);

if ($return_code === 0) {
    echo "   PHP syntax: VALID\n";
} else {
    echo "   PHP syntax: INVALID\n";
    echo "   Error: " . implode("\n   ", $output) . "\n";
}

// Test 3: Check required dependencies
echo "\n3. Checking dependencies...\n";
$required_files = [
    '../core/config.php',
    '../includes/header.php',
    '../includes/sidebar.php',
    '../includes/footer.php'
];

foreach ($required_files as $file) {
    if (file_exists(dirname($dashboard_file) . '/' . $file)) {
        echo "   $file: EXISTS\n";
    } else {
        echo "   $file: MISSING\n";
    }
}

// Test 4: Test API connectivity
echo "\n4. Testing API connectivity...\n";
$api_url = 'http://localhost/sprin/api/unified-api.php?resource=predictive_analytics&action=predictive_dashboard';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = file_get_contents($api_url, false, $context);

if ($response !== false) {
    echo "   API connectivity: SUCCESS\n";
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "   API response: VALID\n";
        echo "   Data keys: " . implode(', ', array_keys($data['data'])) . "\n";
    } else {
        echo "   API response: INVALID\n";
    }
} else {
    echo "   API connectivity: FAILED\n";
}

// Test 5: Check database connection
echo "\n5. Testing database connection...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bagops", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test operations data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM operasi_kepolisian");
    $result = $stmt->fetch();
    echo "   Operations data: {$result['count']} records\n";
    
    // Test personnel data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM personil WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "   Personnel data: {$result['count']} records\n";
    
    // Test equipment data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM equipment");
    $result = $stmt->fetch();
    echo "   Equipment data: {$result['count']} records\n";
    
    echo "   Database connection: SUCCESS\n";
    
} catch (Exception $e) {
    echo "   Database connection: FAILED - " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
