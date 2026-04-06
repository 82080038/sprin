<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Check authentication using AuthHelper (only if not in test mode)
if (!isset($_GET['test_mode']) || $_GET['test_mode'] !== 'true') {
    if (!AuthHelper::validateSession()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}
?>
