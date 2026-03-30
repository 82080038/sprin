<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

// Check authentication (only if not in test mode)
if (!isset($_GET['test_mode']) || $_GET['test_mode'] !== 'true') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . url('login.php'));
        exit;
    }
}
?>
