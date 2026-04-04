<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Check authentication using AuthHelper (only if not in test mode)
if (!isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'test_mode', FILTER_SANITIZE_STRING)) || filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'test_mode', FILTER_SANITIZE_STRING) !== 'true') {
    if (!AuthHelper::validateSession()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}
?>
