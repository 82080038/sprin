<?php
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
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3949ab;
            --accent-color: #ffd700;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white !important;
            font-size: 18px;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            padding: 8px 15px !important;
            border-radius: 6px;
            margin: 0 2px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .navbar-nav .nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white !important;
            transform: translateY(-1px);
        }
        
        .navbar-nav .nav-link.active {
            background: rgba(255,255,255,0.3);
            color: white !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .dropdown-item.active {
            background: var(--primary-color) !important;
            color: white !important;
        }
        
        .dropdown-item.active i {
            color: white !important;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            color: white;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
        }
        
        .logout-btn {
            color: white !important;
            padding: 8px 15px !important;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .dropdown-item:hover {
            background: var(--primary-color);
            color: white;
            padding-left: 25px;
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .dropdown-item:hover i {
            color: white;
        }
        
        @media (max-width: 768px) {
            .navbar-nav .nav-link {
                font-size: 12px;
                padding: 6px 10px !important;
            }
            
            .user-info {
                font-size: 12px;
                padding: 6px 10px;
            }
            
            .logout-btn {
                padding: 6px 10px !important;
                font-size: 12px;
            }
            
            .container {
                padding: 0 10px;
            }
        }
        
        /* Dashboard specific styles */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
            border: none;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .feature-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 12px;
        }
        
        .feature-description {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .btn-feature {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-feature:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .stats-section {
            padding: 40px 0;
            background: white;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            margin-top: 8px;
        }
    </style>

    <!-- Global Table Styles -->
    <style>
    /* Table layout improvements for all tables */
    .table {
        table-layout: fixed;
    }

    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table th:nth-child(2), 
    .table td:nth-child(2),
    .table th:nth-child(3), 
    .table td:nth-child(3) {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Personil table specific styles */
    .personil-table {
        table-layout: auto !important;
        width: 100% !important;
    }

    .personil-table th, 
    .personil-table td {
        vertical-align: middle !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        padding: 8px 10px !important;
    }

    /* Auto sizing with minimum widths */
    .personil-table th:nth-child(1), .personil-table td:nth-child(1) { /* No */
        width: auto !important;
        min-width: 40px !important;
        max-width: 60px !important;
    }
    .personil-table th:nth-child(2), .personil-table td:nth-child(2) { /* Nama */
        width: auto !important;
        min-width: 150px !important;
        max-width: 300px !important;
    }
    .personil-table th:nth-child(3), .personil-table td:nth-child(3) { /* NRP */
        width: auto !important;
        min-width: 80px !important;
        max-width: 120px !important;
    }
    .personil-table th:nth-child(4), .personil-table td:nth-child(4) { /* Pangkat */
        width: auto !important;
        min-width: 60px !important;
        max-width: 100px !important;
    }
    .personil-table th:nth-child(5), .personil-table td:nth-child(5) { /* Jabatan */
        width: auto !important;
        min-width: 120px !important;
        max-width: 250px !important;
    }
    .personil-table th:nth-child(6), .personil-table td:nth-child(6) { /* Status */
        width: auto !important;
        min-width: 60px !important;
        max-width: 80px !important;
    }
    .personil-table th:nth-child(7), .personil-table td:nth-child(7) { /* JK */
        width: auto !important;
        min-width: 40px !important;
        max-width: 50px !important;
    }
    .personil-table th:nth-child(8), .personil-table td:nth-child(8) { /* Aksi */
        width: auto !important;
        min-width: 80px !important;
        max-width: 100px !important;
    }

    /* Action icons styling */
    .personil-table .text-primary {
        color: #0d6efd !important;
        text-decoration: none !important;
        font-size: 14px;
        transition: color 0.2s;
    }

    .personil-table .text-primary:hover {
        color: #0b5ed7 !important;
    }

    .personil-table .text-danger {
        color: #dc3545 !important;
        text-decoration: none !important;
        font-size: 14px;
        transition: color 0.2s;
    }

    .personil-table .text-danger:hover {
        color: #bb2d3b !important;
    }

    .personil-table th:nth-child(2), 
    .personil-table td:nth-child(2),
    .personil-table th:nth-child(3), 
    .personil-table td:nth-child(3) {
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    /* Responsive adjustments for all tables */
    @media (max-width: 768px) {
        .table th:nth-child(2), 
        .table td:nth-child(2),
        .table th:nth-child(3), 
        .table td:nth-child(3),
        .personil-table th:nth-child(2), 
        .personil-table td:nth-child(2),
        .personil-table th:nth-child(3), 
        .personil-table td:nth-child(3) {
            white-space: normal !important;
            word-wrap: break-word !important;
            max-width: 150px !important;
        }
    }
    </style>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    
    <script>
    // Configure toastr to prevent conflicts
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }
    
    // Suppress specific console warnings
    const originalConsoleWarn = console.warn;
    console.warn = function(...args) {
        if (args[0] && typeof args[0] === 'string' && 
            (args[0].includes('downloadable font') || 
             args[0].includes('Font Awesome'))) {
            return;
        }
        originalConsoleWarn.apply(console, args);
    };
    
    // Global dropdown data - accessible from any page
    window.globalDropdownData = {
        pangkat: [],
        unsur: [],
        bagian: [],
        jabatan: [],
        loaded: false,
        loading: false
    };
    
    // Global function to load dropdown data
    window.loadGlobalDropdownData = function(callback) {
        // Return immediately if already loaded
        if (window.globalDropdownData.loaded) {
            if (callback) callback(window.globalDropdownData);
            return Promise.resolve(window.globalDropdownData);
        }
        
        // Return immediately if currently loading
        if (window.globalDropdownData.loading) {
            if (callback) {
                // Wait for loading to complete
                const checkLoaded = setInterval(() => {
                    if (window.globalDropdownData.loaded) {
                        clearInterval(checkLoaded);
                        callback(window.globalDropdownData);
                    }
                }, 100);
            }
            return Promise.resolve(window.globalDropdownData);
        }
        
        // Start loading
        window.globalDropdownData.loading = true;
        
        return fetch('<?php echo API_BASE_URL; ?>/personil_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_dropdown_data'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.globalDropdownData.pangkat = data.data.pangkat;
                window.globalDropdownData.unsur = data.data.unsur;
                window.globalDropdownData.bagian = data.data.bagian;
                window.globalDropdownData.jabatan = data.data.jabatan;
                window.globalDropdownData.loaded = true;
                window.globalDropdownData.loading = false;
                
                if (callback) callback(window.globalDropdownData);
                return window.globalDropdownData;
            } else {
                throw new Error(data.message || 'Failed to load dropdown data');
            }
        })
        .catch(error => {
            console.error('Error loading global dropdown data:', error);
            window.globalDropdownData.loading = false;
            throw error;
        });
    };
    
    // Global utility functions for dropdowns
    window.populateSelect = function(selectId, data, valueField, textField) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        select.innerHTML = '<option value="">-- Pilih --</option>';
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueField] || item.id;
            option.textContent = item[textField] || item.nama || item[valueField];
            select.appendChild(option);
        });
    };
    
    window.filterByField = function(data, field, value) {
        return data.filter(item => item[field] == value);
    };
    
    // Simple Bootstrap initialization
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing application...');
        
        // Check if Bootstrap is loaded
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap loaded successfully');
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            console.log('Bootstrap dropdowns ready');
            
        } else {
            console.error('Bootstrap not loaded, dropdowns may not work');
        }
        
        // Initialize page-specific functionality
        if (typeof initializePage === 'function') {
            initializePage();
        }
        
        console.log('Application initialization complete');
    });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="main.php">
                <i class="fa-solid fa-shield-halved me-2"></i>
                POLRES SAMOSIR
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'main.php' ? 'active' : ''; ?>" href="<?php echo url('pages/main.php'); ?>">
                            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['unsur.php', 'bagian.php', 'jabatan.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-building me-1"></i> Bagian
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'unsur.php' ? 'active' : ''; ?>" href="<?php echo url('pages/unsur.php'); ?>">
                                <i class="fa-solid fa-sitemap"></i> Manajemen Unsur
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'bagian.php' ? 'active' : ''; ?>" href="<?php echo url('pages/bagian.php'); ?>">
                                <i class="fa-solid fa-gear"></i> Manajemen Bagian
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'jabatan.php' ? 'active' : ''; ?>" href="<?php echo url('pages/jabatan.php'); ?>">
                                <i class="fa-solid fa-user-tie"></i> Manajemen Jabatan
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showStructure()">
                                <i class="fa-solid fa-sitemap"></i> Struktur Organisasi
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'personil.php' ? 'active' : ''; ?>" href="<?php echo url('pages/personil.php'); ?>">
                            <i class="fa-solid fa-users me-1"></i> Data Personil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'calendar_dashboard.php' ? 'active' : ''; ?>" href="<?php echo url('pages/calendar_dashboard.php'); ?>">
                            <i class="fa-solid fa-calendar-days me-1"></i> Schedule
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['export_personil.php', 'report_api.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-chart-bar me-1"></i> Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="generatePDF()">
                                <i class="fa-solid fa-file-pdf"></i> Export PDF
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="generateExcel()">
                                <i class="fa-solid fa-file-excel"></i> Export Excel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printReport()">
                                <i class="fa-solid fa-print"></i> Cetak Laporan
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showStatistics()">
                                <i class="fa-solid fa-chart-pie"></i> Statistik
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-cog me-1"></i> Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo url('pages/user_management.php'); ?>">
                                <i class="fa-solid fa-users-cog"></i> Manajemen User
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo url('pages/backup_management.php'); ?>">
                                <i class="fa-solid fa-database"></i> Manajemen Backup
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showSettings()">
                                <i class="fa-solid fa-cog"></i> Pengaturan Sistem
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fa-solid fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <a href="<?php echo url('core/logout.php'); ?>" class="nav-link logout-btn">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
        // Report Functions
        function generatePDF() {
            event.preventDefault();
            window.print();
        }
        
        function generateExcel() {
            event.preventDefault();
            alert('Export Excel akan segera tersedia');
        }
        
        function printReport() {
            event.preventDefault();
            window.print();
        }
        
        function showStatistics() {
            event.preventDefault();
            alert('Statistik akan segera tersedia');
        }
        
        function showStructure() {
            event.preventDefault();
            alert('Struktur Organisasi akan segera tersedia');
        }
    </script>

</body>
</html>
