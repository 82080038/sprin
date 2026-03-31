<?php
// Test password verification
require_once 'core/auth_helper.php';

$password = 'admin123';
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification result: " . (AuthHelper::verifyPassword($password, $hash) ? 'SUCCESS' : 'FAILED') . "\n";

// Generate new hash
$newHash = AuthHelper::hashPassword($password);
echo "New hash: $newHash\n";
echo "New hash verification: " . (AuthHelper::verifyPassword($password, $newHash) ? 'SUCCESS' : 'FAILED') . "\n";
?>
