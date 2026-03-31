<?php
// Test database connection dan login
require_once 'core/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection SUCCESS\n";
    
    // Test user query
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute(['bagops']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found:\n";
        echo "- ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
        echo "- Hash: " . $user['password_hash'] . "\n";
        
        // Test password verification
        require_once 'core/auth_helper.php';
        $password = 'admin123';
        $verification = AuthHelper::verifyPassword($password, $user['password_hash']);
        echo "- Password verification: " . ($verification ? 'SUCCESS' : 'FAILED') . "\n";
        
        // Test full login
        $loginResult = AuthHelper::login('bagops', 'admin123');
        echo "- Full login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";
        
        if ($loginResult) {
            echo "- Session data: " . print_r($_SESSION, true) . "\n";
        }
    } else {
        echo "❌ User not found\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
