<?php
// Test password verification dengan hash yang sama
require_once 'core/auth_helper.php';

$password = 'admin123';
$hash = '$argon2id$v=19$m=65536,t=4,p=3$OHNlTGE0MC90cU5VMFZwdw$iMJrqO/ojh490yMDtHWtsVpImxVprmx9u8VetcjT0Ww';

echo "Testing password verification:\n";
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification result: " . (AuthHelper::verifyPassword($password, $hash) ? 'SUCCESS' : 'FAILED') . "\n";

// Generate new hash dan test
$newHash = AuthHelper::hashPassword($password);
echo "\nNew hash: $newHash\n";
echo "New hash verification: " . (AuthHelper::verifyPassword($password, $newHash) ? 'SUCCESS' : 'FAILED') . "\n";

// Test dengan hash yang baru
echo "\nTesting with new hash in auth_helper:\n";
$valid_credentials = [
    'username' => 'bagops',
    'password_hash' => $newHash
];

if ($password === 'admin123') {
    if (AuthHelper::verifyPassword($password, $valid_credentials['password_hash'])) {
        echo "✅ Verification with new hash: SUCCESS\n";
    } else {
        echo "❌ Verification with new hash: FAILED\n";
    }
}
?>
