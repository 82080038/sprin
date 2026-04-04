<?php
declare(strict_types=1);
// Insert default user bagops
require_once 'core/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['bagops']);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        // Insert default user with Argon2ID hash
        $passwordHash = '$argon2id$v=19$m=65536,t=4,p=3$OHNlTGE0MC90cU5VMFZwdw$iMJrqO/ojh490yMDtHWtsVpImxVprmx9u8VetcjT0Ww';
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'bagops',
            $passwordHash,
            'bagops@polres-samosir.polri.go.id',
            'Administrator BAGOPS',
            'admin',
            1
        ]);
        
        echo "Default user 'bagops' created successfully!\n";
    } else {
        echo "User 'bagops' already exists.\n";
    }
    
    // Test login
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute(['bagops']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found in database:\n";
        echo "- ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
        echo "- Role: " . $user['role'] . "\n";
        echo "- Active: " . $user['is_active'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
