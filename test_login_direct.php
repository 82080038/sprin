<?php
// Test password verification dengan hash baru
require_once 'core/auth_helper.php';

$password = 'admin123';
$hash = '$argon2id$v=19$m=65536,t=4,p=3$OHNlTGE0MC90cU5VMFZwdw$iMJrqO/ojh490yMDtHWtsVpImxVprmx9u8VetcjT0Ww';

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification result: " . (AuthHelper::verifyPassword($password, $hash) ? 'SUCCESS' : 'FAILED') . "\n";

// Test langsung login
$loginResult = AuthHelper::login('bagops', 'admin123');
echo "Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";
?>
