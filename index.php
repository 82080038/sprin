<?php
session_start();
require_once __DIR__ . '/core/config.php';

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ' . url('pages/main.php'));
    exit;
}

// If not logged in, show welcome page with login option
$page_title = 'Selamat Datang - Sistem Manajemen POLRES Samosir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #ffd700 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            backdrop-filter: blur(10px);
        }
        .logo-section {
            margin-bottom: 30px;
        }
        .logo-section i {
            font-size: 4rem;
            color: #1a237e;
            margin-bottom: 20px;
        }
        .welcome-title {
            color: #1a237e;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .welcome-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            color: white;
            text-decoration: none;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        .feature-item {
            text-align: center;
            padding: 20px;
            background: rgba(26, 35, 126, 0.05);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            transform: translateY(-5px);
            background: rgba(26, 35, 126, 0.1);
        }
        .feature-item i {
            font-size: 2rem;
            color: #1a237e;
            margin-bottom: 10px;
        }
        .feature-title {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }
        .feature-desc {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="logo-section">
            <i class="fas fa-shield-alt"></i>
            <h1 class="welcome-title">POLRES SAMOSIR</h1>
            <p class="welcome-subtitle">Sistem Manajemen Personil & Jadwal</p>
        </div>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-users"></i>
                <div class="feature-title">Manajemen Personil</div>
                <div class="feature-desc">Data personil real-time</div>
            </div>
            <div class="feature-item">
                <i class="fas fa-calendar-alt"></i>
                <div class="feature-title">Jadwal Piket</div>
                <div class="feature-desc">Penjadwalan otomatis</div>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <div class="feature-title">Dashboard</div>
                <div class="feature-desc">Statistik lengkap</div>
            </div>
        </div>
        
        <div style="margin-top: 40px;">
            <a href="<?php echo url('login.php'); ?>" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem
            </a>
        </div>
        
        <div style="margin-top: 20px;">
            <small style="color: #666;">
                <i class="fas fa-lock"></i> Sistem terjamin keamanannya
            </small>
        </div>
    </div>
</body>
</html>
