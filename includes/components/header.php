<?php
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
    // Redirect to login if not authenticated
    header('Location: ' . url('login.php'));
    exit;
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

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
    <!-- Font Awesome 6 - Latest stable version -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Unified SPRIN CSS Framework -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/sprin.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    
    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        
        const APP_CONFIG = {
            baseUrl: '<?php echo BASE_URL; ?>',
            apiUrl: '<?php echo API_BASE_URL; ?>',
            apiVersion: '<?php echo API_VERSION; ?>',
            csrfToken: '<?php echo \AuthHelper::generateCSRFToken(); ?>',
            debugMode: false
        };

        return fetch(APP_CONFIG.apiUrl + '/personil_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': APP_CONFIG.csrfToken
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
            
            // Initialize all dropdowns explicitly
            const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdowns.forEach(function(dropdown) {
                new bootstrap.Dropdown(dropdown);
            });
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            console.log('Bootstrap dropdowns and tooltips initialized');
            
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
            <a class="navbar-brand" href="<?php echo url('pages/main.php'); ?>">
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
                            <li><a class="dropdown-item <?php echo $current_page == 'struktur_organisasi.php' ? 'active' : ''; ?>" href="<?php echo url('pages/struktur_organisasi.php'); ?>">
                                <i class="fa-solid fa-sitemap"></i> Struktur Organisasi
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'personil.php' ? 'active' : ''; ?>" href="<?php echo url('pages/personil.php'); ?>">
                            <i class="fa-solid fa-users me-1"></i> Data Personil
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['calendar_dashboard.php','operasi.php','tim_piket.php','ekspedisi.php','apel_nominal.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-calendar-days me-1"></i> Operasional
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'calendar_dashboard.php' ? 'active' : ''; ?>" href="<?php echo url('pages/calendar_dashboard.php'); ?>">
                                <i class="fa-solid fa-calendar-alt"></i> Schedule / Jadwal
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'operasi.php' ? 'active' : ''; ?>" href="<?php echo url('pages/operasi.php'); ?>">
                                <i class="fa-solid fa-tasks"></i> Daftar Operasi
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'tim_piket.php' ? 'active' : ''; ?>" href="<?php echo url('pages/tim_piket.php'); ?>">
                                <i class="fa-solid fa-users-gear"></i> Tim / Regu Piket
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'jadwal_piket.php' ? 'active' : ''; ?>" href="<?php echo url('pages/jadwal_piket.php'); ?>">
                                <i class="fa-solid fa-calendar-week"></i> Jadwal Piket
                                <?php if ($_piketHariIni > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $_piketHariIni ?></span>
                                <?php endif; ?>
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'laporan_piket.php' ? 'active' : ''; ?>" href="<?php echo url('pages/laporan_piket.php'); ?>">
                                <i class="fa-solid fa-clipboard-list"></i> Rekap Absensi
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'ekspedisi.php' ? 'active' : ''; ?>" href="<?php echo url('pages/ekspedisi.php'); ?>">
                                <i class="fa-solid fa-envelope-open-text"></i> Ekspedisi Surat
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'apel_nominal.php' ? 'active' : ''; ?>" href="<?php echo url('pages/apel_nominal.php'); ?>">
                                <i class="fa-solid fa-flag"></i> Apel Nominal
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'pelatihan.php' ? 'active' : ''; ?>" href="<?php echo url('pages/pelatihan.php'); ?>">
                                <i class="fa-solid fa-dumbbell"></i> Pelatihan
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['export_personil.php', 'report_api.php','laporan_piket.php','laporan_operasi.php','lhpt.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-chart-bar me-1"></i> Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page=='laporan_piket.php'?'active':''; ?>" href="<?php echo url('pages/laporan_piket.php'); ?>">
                                <i class="fa-solid fa-clipboard-list me-1"></i> Rekap Absensi Piket
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page=='laporan_operasi.php'?'active':''; ?>" href="<?php echo url('pages/laporan_operasi.php'); ?>">
                                <i class="fa-solid fa-chart-bar me-1"></i> Laporan Operasi
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page=='lhpt.php'?'active':''; ?>" href="<?php echo url('pages/lhpt.php'); ?>">
                                <i class="fa-solid fa-file-lines me-1"></i> LHPT
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="generatePDF()">
                                <i class="fa-solid fa-file-pdf"></i> Export PDF Personil
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="generateExcel()">
                                <i class="fa-solid fa-file-excel"></i> Export Excel Personil
                            </a></li>
                        </ul>
                    </li>
                    <?php if (AuthHelper::isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['user_management.php','backup_management.php','pengaturan.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-cog me-1"></i> Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page == 'user_management.php' ? 'active' : ''; ?>" href="<?php echo url('pages/user_management.php'); ?>">
                                <i class="fa-solid fa-users-cog"></i> Manajemen User
                            </a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'backup_management.php' ? 'active' : ''; ?>" href="<?php echo url('pages/backup_management.php'); ?>">
                                <i class="fa-solid fa-database"></i> Manajemen Backup
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>" href="<?php echo url('pages/pengaturan.php'); ?>">
                                <i class="fa-solid fa-sliders"></i> Pengaturan Sistem
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fa-solid fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <?php
                        $roleBadges = ['admin'=>'bg-danger','operator'=>'bg-warning text-dark','viewer'=>'bg-secondary'];
                        $roleLabel  = ['admin'=>'Admin','operator'=>'Operator','viewer'=>'Viewer'];
                        $r = AuthHelper::getRole();
                        ?>
                        <span class="badge <?= $roleBadges[$r] ?? 'bg-secondary' ?> ms-1" style="font-size:.6rem"><?= $roleLabel[$r] ?? $r ?></span>
                    </div>
                    <a href="<?php echo url('core/logout.php'); ?>" class="nav-link logout-btn">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
        // User role for client-side UI control
        const SPRIN_USER_ROLE = '<?= AuthHelper::getRole() ?>';
        const SPRIN_CAN_EDIT  = <?= AuthHelper::canEdit() ? 'true' : 'false' ?>;
        const SPRIN_IS_ADMIN  = <?= AuthHelper::isAdmin() ? 'true' : 'false' ?>;

        // Report Functions
        function generatePDF() {
            if (event) event.preventDefault();
            window.print();
        }
        
        function generateExcel() {
            if (event) event.preventDefault();
            window.location.href = '<?php echo url("api/export_personil.php"); ?>';
            if (typeof showToast === 'function') {
                showToast('info', 'Download CSV personil dimulai...');
            }
        }
        
        function printReport() {
            if (event) event.preventDefault();
            window.print();
        }
    </script>

</body>
</html>
