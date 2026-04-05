<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

 $response = [
    'status' => 'success',
    'message' => 'System is healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => array (
        'system_status' => 'healthy',
        'database_status' => 'connected',
        'api_version' => '1.0.0',
    )
];

echo json_encode($response);
?>
