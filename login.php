<?php
session_start();
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/auth_helper.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3949ab;
            --accent-color: #ffd700;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            <h2 style="font-size: 2.5rem;">POLRES SAMOSIR</h2>
            <p>Sistem Manajemen Personil & Schedule Management</p>
            <p>Bagian Operasional (BAGOPS)</p>
            
            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <span>Data Personil Lengkap</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Manajemen Jadwal</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Dokumen Digital</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan & Statistik</span>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <h3>Login System</h3>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                
                <button type="button" class="btn btn-quick-login" onclick="quickLogin()">
                    <i class="fas fa-rocket me-2"></i>Quick Login
                </button>
            </form>
            
            <div class="footer-info">
                <p><strong>Default Login:</strong></p>
                <p>Username: <code>bagops</code></p>
                <p>Password: <code>admin123</code></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function quickLogin() {
            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Masuk...';
            btn.disabled = true;
            
            // Submit credentials via POST
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    username: 'bagops',
                    password: 'admin123'
                }),
                redirect: 'manual' // Don't follow redirects automatically
            })
            .then(response => {
                // Check if we got a redirect (302) or successful response
                if (response.status === 0 || response.ok || response.status === 302) {
                    // Redirect to main dashboard
                    window.location.href = 'pages/main.php';
                } else {
                    throw new Error('Login failed');
                }
            })
            .catch(error => {
                // For opaque responses or redirects, try navigating anyway
                window.location.href = 'pages/main.php';
            });
        }
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
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
