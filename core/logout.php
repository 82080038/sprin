<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Use AuthHelper for proper logout
AuthHelper::logout();

// Redirect to login
header('Location: ' . url('login.php'));
exit;
?>
