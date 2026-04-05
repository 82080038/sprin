<?php
require_once 'session_config.php';
/**
 * Login Page
 */

session_start();

// If already logged in, redirect to main page
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/main.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple validation (in production, use proper authentication)
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        header('Location: /pages/main.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPRIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .card { border: none; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">SPIN Login</h2>
                    <p class="text-center text-muted mb-4">Sistem Manajemen Personil</p>
                    
                    <?php
require_once 'session_config.php'; if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php
require_once 'session_config.php'; endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">Default: admin / admin</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>