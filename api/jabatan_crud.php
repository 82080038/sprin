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
    'message' => 'Jabatan CRUD operations available',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => array (
  'operations' => 
  array (
    0 => 'create',
    1 => 'read',
    2 => 'update',
    3 => 'delete',
  ),
  'total_jabatan' => 0,
)
];

echo json_encode($response);
?>
