<?php
/**
 * [API Name] - Standardized API Template
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set security headers
header("Content-Type: application/json; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Start session
SessionManager::start();

// CSRF validation for mutating requests
$readOnlyActions = ['get_all', 'get_detail'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $readOnlyActions)) {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    if (empty($csrfToken) || !\AuthHelper::validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'csrf_expired' => true
        ]);
        exit;
    }
}

// Database connection
try {
    require_once __DIR__ . '/../core/Database.php';
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Main logic
try {
    switch ($action) {
        case 'get_all':
            // Implementation
            echo json_encode(['success' => true, 'data' => []]);
            break;
            
        case 'create':
            // Validate input
            // Insert data
            echo json_encode(['success' => true, 'message' => 'Created']);
            break;
            
        case 'update':
            // Validate input
            // Update data
            echo json_encode(['success' => true, 'message' => 'Updated']);
            break;
            
        case 'delete':
            // Delete data
            echo json_encode(['success' => true, 'message' => 'Deleted']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log('[API Error] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
