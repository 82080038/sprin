<?php
// Debug POST data dengan config lengkap
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG POST2 - All POST data: " . print_r($_POST, true));
    
    $username = AuthHelper::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    error_log("DEBUG LOGIN2 - Raw username: '" . ($_POST['username'] ?? 'NULL') . "'");
    error_log("DEBUG LOGIN2 - Sanitized username: '$username'");
    error_log("DEBUG LOGIN2 - Password length: " . strlen($password));
    
    if (AuthHelper::login($username, $password)) {
        error_log("DEBUG LOGIN2 - SUCCESS");
        header('Location: ' . url('pages/main.php'));
        exit;
    } else {
        error_log("DEBUG LOGIN2 - FAILED");
        $error = 'Username atau password salah!';
    }
}
?>
