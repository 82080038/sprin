<?php
// Debug POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG POST - All POST data: " . print_r($_POST, true));
    error_log("DEBUG POST - Raw input: " . file_get_contents('php://input'));
    
    $username = AuthHelper::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    error_log("DEBUG LOGIN - Raw username: '" . ($_POST['username'] ?? 'NULL') . "'");
    error_log("DEBUG LOGIN - Sanitized username: '$username'");
    error_log("DEBUG LOGIN - Password length: " . strlen($password));
    
    if (AuthHelper::login($username, $password)) {
        error_log("DEBUG LOGIN - SUCCESS");
        header('Location: ' . url('pages/main.php'));
        exit;
    } else {
        error_log("DEBUG LOGIN - FAILED");
        $error = 'Username atau password salah!';
    }
}
?>
