<?php
/**
 * User Management API
 * CRUD operations for user management
 * Standardized API Response Format
 */

// Set headers first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Initialize session
SessionManager::start();

// Disable error display in production
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'timestamp' => date('c')
    ]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    switch ($action) {
        case 'list':
            // Get all users
            $stmt = $pdo->query("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'message' => count($users) . ' users found',
                'data' => ['users' => $users],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'get':
            // Get single user
            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                throw new Exception('User ID required');
            }
            
            $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User found',
                'data' => ['user' => $user],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'create':
            // Create new user
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'viewer';
            
            // Validation
            if (empty($username) || empty($password) || empty($full_name)) {
                throw new Exception('Username, password, and full name are required');
            }
            
            if (strlen($username) < 3) {
                throw new Exception('Username must be at least 3 characters');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (!in_array($role, ['admin', 'operator', 'viewer'])) {
                throw new Exception('Invalid role');
            }
            
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }
            
            // Hash password
            $password_hash = AuthHelper::hashPassword($password);
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password_hash, $email, $full_name, $role, $_SESSION['user_id'] ?? null]);
            
            $user_id = $pdo->lastInsertId();
            
            // Log activity
            logActivity($pdo, $_SESSION['user_id'] ?? null, 'CREATE_USER', "Created user: $username", $_SERVER['REMOTE_ADDR'] ?? null);
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'data' => ['user_id' => $user_id],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'update':
            // Update user
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('User ID required');
            }
            
            // Get current user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Prevent self-demotion from admin
            if ($id == ($_SESSION['user_id'] ?? 0) && isset($_POST['role']) && $_POST['role'] !== 'admin') {
                throw new Exception('Cannot change your own admin role');
            }
            
            $updates = [];
            $params = [];
            
            if (isset($_POST['email'])) {
                $updates[] = "email = ?";
                $params[] = trim($_POST['email']);
            }
            
            if (isset($_POST['full_name'])) {
                $updates[] = "full_name = ?";
                $params[] = trim($_POST['full_name']);
            }
            
            if (isset($_POST['role'])) {
                if (!in_array($_POST['role'], ['admin', 'operator', 'viewer'])) {
                    throw new Exception('Invalid role value');
                }
                $updates[] = "role = ?";
                $params[] = $_POST['role'];
            }
            
            if (isset($_POST['is_active'])) {
                $updates[] = "is_active = ?";
                $params[] = intval($_POST['is_active']);
            }
            
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 6) {
                    throw new Exception('Password must be at least 6 characters');
                }
                $updates[] = "password_hash = ?";
                $params[] = AuthHelper::hashPassword($_POST['password']);
            }
            
            if (empty($updates)) {
                throw new Exception('No fields to update');
            }
            
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Log activity
            logActivity($pdo, $_SESSION['user_id'] ?? null, 'UPDATE_USER', "Updated user ID: $id", $_SERVER['REMOTE_ADDR'] ?? null);
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully',
                'timestamp' => date('c')
            ]);
            break;
            
        case 'delete':
            // Delete user (soft delete by deactivating)
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('User ID required');
            }
            
            // Prevent self-deletion
            if ($id == ($_SESSION['user_id'] ?? 0)) {
                throw new Exception('Cannot delete your own account');
            }
            
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            // Log activity
            logActivity($pdo, $_SESSION['user_id'] ?? null, 'DELETE_USER', "Deactivated user ID: $id", $_SERVER['REMOTE_ADDR'] ?? null);
            
            echo json_encode([
                'success' => true,
                'message' => 'User deactivated successfully',
                'timestamp' => date('c')
            ]);
            break;
            
        case 'change_password':
            // Change own password
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;
            
            if (empty($current_password) || empty($new_password)) {
                throw new Exception('Current and new password required');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('New password must be at least 6 characters');
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !AuthHelper::verifyPassword($current_password, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $new_hash = AuthHelper::hashPassword($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $user_id]);
            
            // Log activity
            logActivity($pdo, $user_id, 'CHANGE_PASSWORD', 'Password changed', $_SERVER['REMOTE_ADDR'] ?? null);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully',
                'timestamp' => date('c')
            ]);
            break;
            
        case 'get_roles':
            // Get available roles
            echo json_encode([
                'success' => true,
                'message' => 'Roles retrieved',
                'data' => [
                    'roles' => [
                        ['value' => 'admin', 'label' => 'Administrator', 'description' => 'Full access to all features'],
                        ['value' => 'operator', 'label' => 'Operator', 'description' => 'Can manage data but not users'],
                        ['value' => 'viewer', 'label' => 'Viewer', 'description' => 'Read-only access']
                    ]
                ],
                'timestamp' => date('c')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json; charset=UTF-8');
    if (ENVIRONMENT === 'development') {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Operation failed',
            'timestamp' => date('c')
        ]);
    }
}

// Helper function to log activity
function logActivity($pdo, $user_id, $action, $details, $ip_address) {
    try {
        $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip_address]);
    } catch (Exception $e) {
        // Silently fail - don't break main operation
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
