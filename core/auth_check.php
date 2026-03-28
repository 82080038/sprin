<?php
// Test mode - bypass authentication for testing
if (isset($_GET['test_mode']) && $_GET['test_mode'] == 'true') {
    // Set test session
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'Test User';
    $_SESSION['user_id'] = 1;
}

// Check authentication (only if not in test mode)
if (!isset($_GET['test_mode']) || $_GET['test_mode'] !== 'true') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /sprint/login.php');
        exit;
    }
}
?>
