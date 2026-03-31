<?php
// Fix Argon2ID verification dengan options yang konsisten
require_once 'core/auth_helper.php';

// Test dengan options yang sama
$password = 'admin123';
$options = [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3,
];

// Generate hash dengan options yang sama
$hash1 = password_hash($password, PASSWORD_ARGON2ID, $options);
$hash2 = password_hash($password, PASSWORD_ARGON2ID, $options);

echo "Hash 1: $hash1\n";
echo "Hash 2: $hash2\n";
echo "Hash 1 verification: " . (password_verify($password, $hash1) ? 'SUCCESS' : 'FAILED') . "\n";
echo "Hash 2 verification: " . (password_verify($password, $hash2) ? 'SUCCESS' : 'FAILED') . "\n";
echo "Cross verification: " . (password_verify($password, $hash2) ? 'SUCCESS' : 'FAILED') . "\n";

// Update user dengan hash yang konsisten
echo "\n=== Update database dengan hash yang konsisten ===\n";
$finalHash = password_hash($password, PASSWORD_ARGON2ID, $options);
echo "Final hash: $finalHash\n";

// Update database command
echo "\nMySQL command:\n";
echo "UPDATE users SET password_hash = '\$argon2id\$v=19\$m=65536,t=4,p=3\$$finalHash' WHERE username='bagops';\n";
?>
