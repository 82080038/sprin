<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/SessionManager.php';
require_once __DIR__ . '/core/auth_helper.php';

SessionManager::start();

// Redirect to main application entry point
if (AuthHelper::validateSession()) {
    header('Location: ' . url('pages/main.php'));
} else {
    header('Location: ' . url('login.php'));
}
exit;

// Legacy SPA code below - kept for reference but no longer executed
$page_title = 'SPRIN - Sistem Manajemen Personil & Jadwal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
require_once __DIR__ . '/core/SessionManager.php'; echo htmlspecialchars($page_title); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
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
        }
        
        .dropdown-item.active {
            background: var(--primary-color) !important;
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
        
        /* Loading spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        
        .loading-spinner.active {
            display: block;
        }
        
        /* Page transition */
        .page-content {
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .page-content.loading {
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#dashboard">
                <i class="fa-solid fa-shield-halved me-2"></i>
                POLRES SAMOSIR
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="dropdown" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard" data-page="dashboard">
                            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-building me-1"></i> Bagian
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#unsur" data-page="unsur">
                                <i class="fa-solid fa-sitemap"></i> Manajemen Unsur
                            </a></li>
                            <li><a class="dropdown-item" href="#bagian" data-page="bagian">
                                <i class="fa-solid fa-gear"></i> Manajemen Bagian
                            </a></li>
                            <li><a class="dropdown-item" href="#personil" data-page="personil">
                                <i class="fa-solid fa-users"></i> Data Personil
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#personil" data-page="personil">
                            <i class="fa-solid fa-users me-1"></i> Data Personil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#schedule" data-page="schedule">
                            <i class="fa-solid fa-calendar-days me-1"></i> Schedule
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
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
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-cog me-1"></i> Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#users" data-page="users">
                                <i class="fa-solid fa-users-cog"></i> Manajemen User
                            </a></li>
                            <li><a class="dropdown-item" href="#backup" data-page="backup">
                                <i class="fa-solid fa-database"></i> Manajemen Backup
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fa-solid fa-user"></i>
                        <span><?php
require_once __DIR__ . '/core/SessionManager.php'; echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    </div>
                    <a href="core/logout.php" class="nav-link logout-btn">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container">
        <div id="main-content" class="page-content">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="text-muted mb-0">
                &copy; <?php
require_once __DIR__ . '/core/SessionManager.php'; echo date('Y'); ?> POLRES Samosir - Sistem Manajemen Personil & Jadwal (SPRIN)
            </p>
        </div>
    </footer>

    <!-- jQuery and Bootstrap JS loaded in header - no duplicates needed -->
    
    <!-- Toastr Notifications -->
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    
    <!-- Main Application JavaScript -->
    <script>
        // Global Application State
        window.SPRINT = {
            currentPage: 'dashboard',
            isLoading: false,
            history: [],
            
            // Initialize application
            init: function() {
                console.log('SPRIN Application initializing...');
                
                // Setup navigation handlers
                this.setupNavigation();
                
                // Handle browser back/forward
                window.addEventListener('popstate', (e) => {
                    if (e.state && e.state.page) {
                        this.loadPage(e.state.page, false);
                    }
                });
                
                // Load initial page
                const hash = window.location.hash.substring(1) || 'dashboard';
                this.loadPage(hash);
                
                console.log('SPRIN Application initialized');
            },
            
            // Setup navigation handlers
            setupNavigation: function() {
                // Handle navigation clicks
                document.querySelectorAll('[data-page]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const page = link.getAttribute('data-page');
                        this.loadPage(page);
                    });
                });
                
                // Handle hash changes
                window.addEventListener('hashchange', () => {
                    const hash = window.location.hash.substring(1) || 'dashboard';
                    this.loadPage(hash);
                });
            },
            
            // Load page content
            loadPage: function(page, addToHistory = true) {
                if (this.isLoading || page === this.currentPage) {
                    return;
                }
                
                this.isLoading = true;
                this.showLoading();
                
                // Update active navigation
                this.updateActiveNavigation(page);
                
                // Load page content
                const pageFile = `pages/${page}.php`;
                
                fetch(pageFile, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Update content
                    document.getElementById('main-content').innerHTML = html;
                    
                    // Update page state
                    this.currentPage = page;
                    
                    // Update URL
                    if (addToHistory) {
                        history.pushState({page: page}, '', `#${page}`);
                    }
                    
                    // Initialize page-specific functionality
                    this.initializePage(page);
                    
                    // Update document title
                    this.updatePageTitle(page);
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    this.showError(`Gagal memuat halaman: ${error.message}`);
                })
                .finally(() => {
                    this.isLoading = false;
                    this.hideLoading();
                });
            },
            
            // Update active navigation state
            updateActiveNavigation: function(page) {
                // Remove all active classes
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                // Add active class to current page
                document.querySelectorAll(`[data-page="${page}"]`).forEach(link => {
                    link.classList.add('active');
                });
                
                // Also check parent dropdowns
                const parentDropdown = document.querySelector(`[data-page="${page}"]`).closest('.dropdown');
                if (parentDropdown) {
                    const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                    if (dropdownToggle) {
                        dropdownToggle.classList.add('active');
                    }
                }
            },
            
            // Initialize page-specific functionality
            initializePage: function(page) {
                console.log(`Initializing page: ${page}`);
                
                // Common initialization
                this.initializeCommon();
                
                // Page-specific initialization
                switch(page) {
                    case 'unsur':
                        this.initializeUnsurPage();
                        break;
                    case 'bagian':
                        this.initializeBagianPage();
                        break;
                    case 'personil':
                        this.initializePersonilPage();
                        break;
                    case 'schedule':
                        this.initializeSchedulePage();
                        break;
                    case 'dashboard':
                        this.initializeDashboardPage();
                        break;
                }
            },
            
            // Common initialization
            initializeCommon: function() {
                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Initialize dropdowns (Bootstrap handles this automatically with data-bs-toggle)
                
                // Setup common event handlers
                this.setupCommonHandlers();
            },
            
            // Setup common event handlers
            setupCommonHandlers: function() {
                // Handle form submissions
                document.addEventListener('submit', (e) => {
                    if (e.target.hasAttribute('data-ajax')) {
                        e.preventDefault();
                        this.handleAjaxForm(e.target);
                    }
                });
                
                // Handle modal events
                document.addEventListener('shown.bs.modal', (e) => {
                    // Focus first input in modal
                    const firstInput = e.target.querySelector('input:not([type="hidden"])');
                    if (firstInput) {
                        firstInput.focus();
                    }
                });
            },
            
            // Initialize Unsur page
            initializeUnsurPage: function() {
                // Load SortableJS if needed
                if (!window.Sortable) {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
                    script.onload = () => {
                        this.setupSortableUnsur();
                    };
                    document.head.appendChild(script);
                } else {
                    this.setupSortableUnsur();
                }
            },
            
            // Setup sortable for Unsur page
            setupSortableUnsur: function() {
                const container = document.getElementById('sortable-container');
                if (container && window.Sortable) {
                    new Sortable(container, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-dragging',
                        handle: '.drag-handle',
                        onEnd: (evt) => {
                            this.updateOrderNumbers();
                            this.showSaveButton();
                        }
                    });
                }
            },
            
            // Initialize Bagian page
            initializeBagianPage: function() {
                // Similar to Unsur page
                this.setupSortableBagian();
            },
            
            // Setup sortable for Bagian page
            setupSortableBagian: function() {
                const container = document.getElementById('sortable-container');
                if (container && window.Sortable) {
                    new Sortable(container, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-dragging',
                        handle: '.drag-handle',
                        onEnd: (evt) => {
                            this.updateOrderNumbers();
                            this.showSaveButton();
                        }
                    });
                }
            },
            
            // Initialize Personil page
            initializePersonilPage: function() {
                // Load dropdown data if needed
                if (!window.globalDropdownData || !window.globalDropdownData.loaded) {
                    this.loadDropdownData();
                }
            },
            
            // Initialize Schedule page
            initializeSchedulePage: function() {
                // Calendar initialization
                if (typeof initializeCalendar === 'function') {
                    initializeCalendar();
                }
            },
            
            // Initialize Dashboard page
            initializeDashboardPage: function() {
                // Dashboard widgets initialization
                this.loadDashboardStats();
            },
            
            // Load dropdown data
            loadDropdownData: function() {
                fetch('api/personil_crud.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=get_dropdown_data'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.globalDropdownData = data.data;
                        window.globalDropdownData.loaded = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading dropdown data:', error);
                });
            },
            
            // Update page title
            updatePageTitle: function(page) {
                const titles = {
                    'dashboard': 'Dashboard - SPRIN',
                    'unsur': 'Manajemen Unsur - SPRIN',
                    'bagian': 'Manajemen Bagian - SPRIN',
                    'personil': 'Data Personil - SPRIN',
                    'schedule': 'Schedule Management - SPRIN',
                    'users': 'Manajemen User - SPRIN',
                    'backup': 'Manajemen Backup - SPRIN'
                };
                
                document.title = titles[page] || 'SPRIN - Sistem Manajemen Personil & Jadwal';
            },
            
            // Show loading spinner
            showLoading: function() {
                document.getElementById('loadingSpinner').classList.add('active');
                document.getElementById('main-content').classList.add('loading');
            },
            
            // Hide loading spinner
            hideLoading: function() {
                document.getElementById('loadingSpinner').classList.remove('active');
                document.getElementById('main-content').classList.remove('loading');
            },
            
            // Show error message
            showError: function(message) {
                if (window.toastr) {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            },
            
            // Show success message
            showSuccess: function(message) {
                if (window.toastr) {
                    toastr.success(message);
                } else {
                    alert(message);
                }
            },
            
            // Update order numbers (for sortable)
            updateOrderNumbers: function() {
                const items = document.querySelectorAll('.sortable-item');
                items.forEach((item, index) => {
                    item.dataset.urutan = index + 1;
                    const idDisplay = item.querySelector('small.text-muted');
                    if (idDisplay) {
                        idDisplay.textContent = `Urutan: ${index + 1}`;
                    }
                    
                    // Visual feedback
                    item.classList.add('order-updated');
                    setTimeout(() => {
                        item.classList.remove('order-updated');
                    }, 500);
                });
                
                this.showSaveButton();
            },
            
            // Show save button
            showSaveButton: function() {
                const saveBtn = document.getElementById('saveOrderBtn');
                const cancelBtn = document.getElementById('cancelOrderBtn');
                
                if (saveBtn) {
                    saveBtn.classList.remove('btn-success');
                    saveBtn.classList.add('btn-warning');
                    saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Simpan Perubahan';
                }
                
                if (cancelBtn) {
                    cancelBtn.style.display = 'inline-block';
                }
            },
            
            // Load dashboard statistics
            loadDashboardStats: function() {
                // Implementation for dashboard stats
                console.log('Loading dashboard statistics...');
            }
        };
        
        // Initialize application when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            window.SPRINT.init();
        });
    </script>
</body>
</html>
