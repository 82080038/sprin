<?php
declare(strict_types=1);
// Include config if not already included
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../core/config.php';
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistem Manajemen POLRES Samosir'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 - Latest stable version -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        /* Minimal custom styling - Bootstrap handles the rest */
        body {
            padding-top: 80px;
        }
        
        /* Brand colors only for specific elements */
        .navbar-brand {
            color: #1a237e !important;
            font-weight: bold;
        }
        
        /* Enhanced visibility for toggle buttons */
        .btn-outline-primary {
            color: #0d6efd !important;
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.1) !important;
            border-width: 2px !important;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2) !important;
        }
        
        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: #ffffff !important;
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3) !important;
            transform: translateY(-1px) !important;
        }
        
        .btn-outline-secondary {
            color: #6c757d !important;
            border-color: #6c757d !important;
            background-color: rgba(108, 117, 125, 0.1) !important;
            border-width: 2px !important;
            box-shadow: 0 2px 4px rgba(108, 117, 125, 0.2) !important;
        }
        
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: #ffffff !important;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3) !important;
            transform: translateY(-1px) !important;
        }
        
        /* Ensure toggle buttons are prominent */
        button[onclick*="toggle"] {
            min-width: 2.5rem !important;
            min-height: 2.5rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            z-index: 10 !important;
            transition: all 0.2s ease !important;
        }
        
        button[onclick*="toggle"]:hover {
            transform: scale(1.05) !important;
        }
        
        button[onclick*="toggle"] i {
            visibility: visible !important;
            display: inline-block !important;
            font-size: 14px !important;
            transition: transform 0.2s ease !important;
        }
        
        button[onclick*="toggle"]:hover i {
            transform: scale(1.1) !important;
        }
        
        /* Make sure toggle buttons stand out in card headers */
        .card-header button[onclick*="toggle"] {
            background-color: rgba(13, 110, 253, 0.15) !important;
            border-color: #0d6efd !important;
        }
        
        .card-header button[onclick*="toggle"]:hover {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
        
        /* Active card styling */
        .card-header.active {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
        
        .card-header.active h5,
        .card-header.active h6,
        .card-header.active small {
            color: #ffffff !important;
        }
        
        /* Clickable card headers */
        .card-header[onclick] {
            transition: background-color 0.2s ease !important;
        }
        
        .card-header[onclick]:hover {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        
        .card-header.bg-primary[onclick]:hover {
            background-color: rgba(13, 110, 253, 0.9) !important;
        }
        
        .card-header.bg-light[onclick]:hover {
            background-color: #f8f9fa !important;
        }
        
        /* Enhanced Bootstrap accordion styling */
        .accordion-button:not(.collapsed) {
            background-color: #0d6efd !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }
        
        .accordion-button:not(.collapsed):hover {
            background-color: #0b5ed7 !important;
        }
        
        .accordion-button:focus {
            box-shadow: none !important;
            border-color: rgba(13, 110, 253, 0.25) !important;
        }
        
        .accordion-button.collapsed:hover {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }
        
        .accordion-item {
            border: 1px solid rgba(13, 110, 253, 0.125) !important;
            margin-bottom: 0.5rem !important;
            border-radius: 0.375rem !important;
        }
        
        .accordion-item:first-of-type {
            border-top-left-radius: 0.375rem !important;
            border-top-right-radius: 0.375rem !important;
        }
        
        .accordion-item:last-of-type {
            border-bottom-left-radius: 0.375rem !important;
            border-bottom-right-radius: 0.375rem !important;
        }
        
        .accordion-header {
            border-radius: 0.375rem !important;
        }
        
        /* Nested accordion styling */
        .accordion-flush .accordion-item {
            border-left: none !important;
            border-right: none !important;
            border-radius: 0 !important;
        }
        
        .accordion-flush .accordion-button {
            border-radius: 0 !important;
            padding-left: 1.5rem !important;
            background-color: #f8f9fa !important;
        }
        
        .accordion-flush .accordion-button:not(.collapsed) {
            background-color: #e9ecef !important;
            color: #212529 !important;
        }
        
        /* Button styling in accordion headers */
        .accordion-button button {
            pointer-events: auto !important;
        }
        
        .accordion-button .btn-sm {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.5rem !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/main.php">
                <i class="fas fa-shield-alt"></i> POLRES SAMOSIR
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'main.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/main.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'personil.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/personil.php">
                            <i class="fa-solid fa-users"></i> Personil
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-cogs"></i> Manajemen
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'unsur.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/unsur.php">
                                <i class="fa-solid fa-sitemap"></i> Manajemen Unsur
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'bagian.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/bagian.php">
                                <i class="fa-solid fa-gear"></i> Manajemen Bagian
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'jenis_personil.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/jenis_personil.php">
                                <i class="fa-solid fa-users-cog"></i> Manajemen Jenis Personil
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'jabatan.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/jabatan.php">
                                <i class="fa-solid fa-user-tie"></i> Manajemen Jabatan
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'pangkat.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/pangkat.php">
                                <i class="fa-solid fa-graduation-cap"></i> Manajemen Pangkat
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-calendar"></i> Jadwal
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'jadwal.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/jadwal.php">
                                <i class="fa-solid fa-calendar-alt"></i> Manajemen Jadwal
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'calendar.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/calendar.php">
                                <i class="fa-solid fa-calendar"></i> Kalender
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-chart-bar"></i> Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'laporan_personil.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/laporan_personil.php">
                                <i class="fa-solid fa-users"></i> Laporan Personil
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'laporan_jadwal.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/laporan_jadwal.php">
                                <i class="fa-solid fa-calendar-alt"></i> Laporan Jadwal
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'statistik.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/statistik.php">
                                <i class="fa-solid fa-chart-line"></i> Statistik
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-tools"></i> Utilitas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/backup.php">
                                <i class="fa-solid fa-database"></i> Backup & Restore
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'export.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/export.php">
                                <i class="fa-solid fa-download"></i> Export Data
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/settings.php">
                                <i class="fa-solid fa-cog"></i> Pengaturan
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user"></i> <?php echo $_SESSION['user_name'] ?? 'User'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/profile.php">
                                <i class="fa-solid fa-user-circle"></i> Profil
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/change_password.php">
                                <i class="fa-solid fa-key"></i> Ubah Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="fa-solid fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toast Notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- jQuery (required for some plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert2 for better modals -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
