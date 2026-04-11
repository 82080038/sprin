<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/SessionManager.php';
require_once __DIR__ . '/core/auth_helper.php';

// Start session using SessionManager
SessionManager::start();

// Check if already logged in
if (AuthHelper::validateSession()) {
    header('Location: ' . url('pages/main.php'));
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = AuthHelper::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (AuthHelper::login($username, $password)) {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Location: ' . url('pages/main.php'));
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen POLRES Samosir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --accent-color: #ffd700;
            --blue-police: #1a237e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-sidebar {
            background: linear-gradient(135deg, var(--blue-police), #3949ab);
            color: white;
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .form-control {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
        }
        
        .btn-quick-login {
            background: var(--accent-color);
            color: var(--blue-police);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-quick-login:hover {
            background: #ffcd39;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        
        @media (max-width: 768px) {
            .login-sidebar {
                display: none;
            }
            
            .login-form {
                padding: 2rem;
            }
        }
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #ffd700 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 500px;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1;
        }
        
        .login-right {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            font-size: 4rem;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .login-left h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .login-left p {
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .login-right h3 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-quick-login {
            background: linear-gradient(135deg, var(--accent-color), #ff6f00);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-quick-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
            color: white;
        }
        
        .btn-quick-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .features {
            margin-top: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .feature-item i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .footer-info {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .login-left {
                padding: 30px;
            }
            
            .login-right {
                padding: 30px;
            }
            
            .logo {
                font-size: 3rem;
            }
            
            .login-left h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            
            <!-- Login Form -->
            <div class="col-md-7">
                <div class="login-form">
                    <h3 class="fw-bold mb-4">Login System</h3>
                    
                    <form method="POST" action="">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-login btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-quick-login" onclick="quickLogin()">
                                <i class="fas fa-rocket me-2"></i>Quick Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="footer-info">
                        <p><strong>Default Login:</strong></p>
                        <p>Username: <code>bagops</code></p>
                        <p>Password: <code>admin123</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function quickLogin() {
            document.getElementById('username').value = 'bagops';
            document.getElementById('password').value = 'admin123';
            
            // Add loading effect
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                document.querySelector('form').submit();
            }, 500);
        }
        
        // Add input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-group-text').classList.add('text-primary');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-group-text').classList.remove('text-primary');
        
        // Allow Enter key to submit form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                    e.preventDefault();
                    form.querySelector('button[type="submit"]').click();
                }
            });
        });
    </script>
</body>
</html>
