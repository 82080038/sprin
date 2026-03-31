<?php
// Test login dengan SessionManager
require_once 'core/config.php';
require_once 'core/SessionManager.php';
require_once 'core/auth_helper.php';

echo "=== Testing Login dengan SessionManager ===\n";

// Start session
SessionManager::start();
echo "✅ Session started\n";

// Test login
$loginResult = AuthHelper::login('bagops', 'admin123');
echo "Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";

if ($loginResult) {
    echo "Session data:\n";
    print_r($_SESSION);
    
    // Test validateSession
    $isValid = AuthHelper::validateSession();
    echo "Session validation: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
    
    // Test getCurrentUser
    $user = AuthHelper::getCurrentUser();
    echo "Current user:\n";
    print_r($user);
} else {
    echo "Login failed!\n";
}
?>
