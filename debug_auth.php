<?php
// Test AuthHelper::login langsung
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/auth_helper.php';

$username = 'bagops';
$password = 'admin123';

error_log("DEBUG AUTH - Testing login with username: '$username'");

$loginResult = AuthHelper::login($username, $password);
error_log("DEBUG AUTH - Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED'));

// Cek session setelah login
error_log("DEBUG AUTH - Session data: " . print_r($_SESSION, true));
?>
