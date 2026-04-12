<?php
/**
 * Direct test for operations API endpoint
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIRECT OPERATIONS API TEST ===\n\n";

// Test the API endpoint directly
$url = "http://localhost/sprin/api/unified-api.php?resource=operasional&action=get_operasi_list";

echo "Testing URL: $url\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'timeout' => 10
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "FAILED: Could not get response\n";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "\n";
} else {
    echo "SUCCESS: Got response\n";
    echo "Response length: " . strlen($response) . " bytes\n";
    echo "Response preview:\n";
    echo substr($response, 0, 500) . "...\n\n";
    
    // Try to decode JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        echo "JSON decode: SUCCESS\n";
        echo "Success status: " . ($data['success'] ? 'true' : 'false') . "\n";
        if (isset($data['data'])) {
            echo "Data count: " . count($data['data']) . "\n";
        }
    } else {
        echo "JSON decode: FAILED\n";
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
?>
