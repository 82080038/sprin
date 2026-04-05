<?php
/**
 * SPRIN Application Router
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Get request info
 = $_SERVER['REQUEST_METHOD'];
 = $_SERVER['REQUEST_URI'] ?? '/';

// Basic routing
 = [
    '/' => ['method' => 'GET', 'handler' => 'home'],
    '/login' => ['method' => 'GET', 'handler' => 'login'],
    '/logout' => ['method' => 'GET', 'handler' => 'logout'],
];

 = [
    'status' => 'success',
    'message' => 'Router working',
    'method' => ,
    'path' => ,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode();
?>
