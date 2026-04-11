<?php
/**
 * Bootstrap Layout Template for SPRIN Application
 * Ensures consistent layout across all pages
 */

// Include config if not already included
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../core/config.php';
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validate session for all pages except login
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file !== 'login.php' && (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)) {
    header('Location: ' . url('login.php'));
    exit;
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Get user data
$user = $_SESSION['user'] ?? [];
$username = $user['username'] ?? 'User';
$role = $user['role'] ?? 'viewer';
$roleLabels = ['admin'=>'Administrator','operator'=>'Operator','viewer'=>'Pimpinan'];

// Badge: jumlah personil piket hari ini
$_piketHariIni = 0;
try {
    require_once __DIR__ . '/../../core/Database.php';
    $_hPdo = Database::getInstance()->getConnection();
    $_piketHariIni = (int)$_hPdo->query(
        "SELECT COUNT(*) FROM schedules WHERE shift_date='".date('Y-m-d')."' AND tim_id IS NOT NULL"
    )->fetchColumn();
} catch (Exception $_e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistem Manajemen POLRES Samosir'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom SPRIN CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --blue-police: #1a237e;
            --gold-police: #ffd700;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--blue-police) !important;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .main-content {
            min-height: calc(100vh - 56px);
            padding: 1.5rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
            background-color: var(--light-color);
        }
        
        .form-control, .form-select {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .badge {
            font-weight: 500;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stats-card .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card .stats-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--blue-police), #3949ab);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }
        
        .page-header h1 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .page-header .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .page-header .breadcrumb-item {
            color: rgba(255,255,255,0.8);
        }
        
        .page-header .breadcrumb-item.active {
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: -250px;
                width: 250px;
                height: calc(100vh - 56px);
                z-index: 1000;
                transition: left 0.3s ease;
                background: white;
                box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            /* Overlay for mobile sidebar */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 56px;
                left: 0;
                width: 100%;
                height: calc(100vh - 56px);
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <button class="btn btn-outline-light mobile-menu-toggle d-md-none" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/pages/main.php">
                <i class="fas fa-shield-alt me-2"></i>SPRIN
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($username); ?>
                            <span class="badge bg-warning text-dark ms-1"><?php echo htmlspecialchars($roleLabels[$role] ?? $role); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>Profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/login.php?logout=1"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Layout Container -->
    <div class="container-fluid p-0">
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo $current_page == 'main.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/main.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link <?php echo $current_page == 'personil.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/personil.php">
                        <i class="fas fa-users me-2"></i>Personil
                        <?php if ($_piketHariIni > 0): ?>
                            <span class="badge bg-warning text-dark float-end"><?php echo $_piketHariIni; ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link <?php echo $current_page == 'operasi.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/operasi.php">
                        <i class="fas fa-shield-alt me-2"></i>Operasi
                    </a>
                    <a class="nav-link <?php echo $current_page == 'tim_piket.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/tim_piket.php">
                        <i class="fas fa-calendar-check me-2"></i>Tim Piket
                    </a>
                    <a class="nav-link <?php echo $current_page == 'calendar_dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/calendar_dashboard.php">
                        <i class="fas fa-calendar me-2"></i>Kalender
                    </a>
                    <a class="nav-link <?php echo $current_page == 'lhpt.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/lhpt.php">
                        <i class="fas fa-file-alt me-2"></i>LHPT
                    </a>
                    <a class="nav-link <?php echo $current_page == 'ekspedisi.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/ekspedisi.php">
                        <i class="fas fa-envelope me-2"></i>Ekspedisi
                    </a>
                    <a class="nav-link <?php echo $current_page == 'apel_nominal.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/apel_nominal.php">
                        <i class="fas fa-clipboard-list me-2"></i>Apel Nominal
                    </a>
                    <a class="nav-link <?php echo $current_page == 'pelatihan.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/pelatihan.php">
                        <i class="fas fa-graduation-cap me-2"></i>Pelatihan
                    </a>
                    
                    <!-- Admin Menu -->
                    <?php if ($role === 'admin'): ?>
                    <div class="nav-divider my-3"></div>
                    <h6 class="nav-header text-muted px-3">Admin</h6>
                    <a class="nav-link <?php echo $current_page == 'unsur.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/unsur.php">
                        <i class="fas fa-sitemap me-2"></i>Unsur
                    </a>
                    <a class="nav-link <?php echo $current_page == 'bagian.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/bagian.php">
                        <i class="fas fa-building me-2"></i>Bagian
                    </a>
                    <a class="nav-link <?php echo $current_page == 'jabatan.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/jabatan.php">
                        <i class="fas fa-id-badge me-2"></i>Jabatan
                    </a>
                    <a class="nav-link <?php echo $current_page == 'user_management.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/user_management.php">
                        <i class="fas fa-users-cog me-2"></i>Pengguna
                    </a>
                    <?php endif; ?>
                    
                    <!-- Reports -->
                    <div class="nav-divider my-3"></div>
                    <h6 class="nav-header text-muted px-3">Laporan</h6>
                    <a class="nav-link <?php echo $current_page == 'laporan_piket.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/laporan_piket.php">
                        <i class="fas fa-file-pdf me-2"></i>Laporan Piket
                    </a>
                    <a class="nav-link <?php echo $current_page == 'laporan_operasi.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/laporan_operasi.php">
                        <i class="fas fa-chart-bar me-2"></i>Laporan Operasi
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php if (isset($page_header) && $page_header): ?>
                <!-- Page Header -->
                <div class="page-header">
                    <div class="container-fluid">
                        <h1><?php echo $page_header['title'] ?? ''; ?></h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/pages/main.php" style="color: rgba(255,255,255,0.8);">Dashboard</a></li>
                                <?php if (isset($page_header['breadcrumb'])): ?>
                                    <?php foreach ($page_header['breadcrumb'] as $item): ?>
                                        <li class="breadcrumb-item <?php echo $item['active'] ?? false ? 'active' : ''; ?>">
                                            <?php if (!($item['active'] ?? false)): ?>
                                                <a href="<?php echo $item['url'] ?? '#'; ?>" style="color: rgba(255,255,255,0.8);"><?php echo $item['text']; ?></a>
                                            <?php else: ?>
                                                <?php echo $item['text']; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ol>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <div id="page-content">
                    <!-- Content will be inserted here -->
