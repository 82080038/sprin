<?php
declare(strict_types=1);
/**
 * POLRI Regulation-Compliant Navigation Header v2.0
 * Based on PERKAP No. 23/2010, Perpol No. 3/2024, PP No. 100/2000
 * Personil-First Flow Architecture
 */

// Include config if not already included
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../core/config.php';
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Define navigation structure based on POLRI hierarchy
$nav_structure = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fa-gauge-high',
        'url' => 'dashboard_v2.php',
        'permission' => 'all'
    ],
    'personil' => [
        'title' => 'Manajemen Personil',
        'icon' => 'fa-users',
        'url' => 'personil_management_v2.php',
        'permission' => 'all',
        'submenu' => [
            ['title' => 'Data Personil', 'url' => 'personil_management_v2.php', 'icon' => 'fa-user-list'],
            ['title' => 'Tambah Personil', 'url' => '#add-personil', 'icon' => 'fa-user-plus', 'action' => 'showAddPersonilModal()'],
            ['title' => 'Import Data', 'url' => '#import', 'icon' => 'fa-file-import'],
        ]
    ],
    'kepegawaian' => [
        'title' => 'Kepegawaian',
        'icon' => 'fa-user-tie',
        'url' => 'kepegawaian_management_v2.php',
        'permission' => 'admin',
        'submenu' => [
            ['title' => 'Kenaikan Pangkat', 'url' => '#kenaikan', 'icon' => 'fa-arrow-up', 'action' => 'showKenaikanPangkat()'],
            ['title' => 'Mutasi Jabatan', 'url' => '#mutasi', 'icon' => 'fa-exchange-alt', 'action' => 'showMutasiJabatan()'],
            ['title' => 'Riwayat Karir', 'url' => '#riwayat', 'icon' => 'fa-history'],
            ['title' => 'Jenjang Karir', 'url' => '#jenjang', 'icon' => 'fa-stairs'],
        ]
    ],
    'penugasan' => [
        'title' => 'Penugasan',
        'icon' => 'fa-briefcase',
        'url' => 'penugasan_management_v2.php',
        'permission' => 'admin',
        'submenu' => [
            ['title' => 'Definitif', 'url' => '#definitif', 'icon' => 'fa-id-card', 'jenis' => 'definitif'],
            ['title' => 'Pejabat Sementara (PS)', 'url' => '#ps', 'icon' => 'fa-user-clock', 'jenis' => 'PS'],
            ['title' => 'Pelaksana Tugas (Plt)', 'url' => '#plt', 'icon' => 'fa-user-cog', 'jenis' => 'Plt'],
            ['title' => 'Pejabat Sementara (Pjs)', 'url' => '#pjs', 'icon' => 'fa-user-shield', 'jenis' => 'Pjs'],
            ['title' => 'Pelaksana Harian (Plh)', 'url' => '#plh', 'icon' => 'fa-calendar-day', 'jenis' => 'Plh'],
            ['title' => 'Penjabat (Pj)', 'url' => '#pj', 'icon' => 'fa-user-tag', 'jenis' => 'Pj'],
        ]
    ],
    'struktur' => [
        'title' => 'Struktur Organisasi',
        'icon' => 'fa-sitemap',
        'url' => '#',
        'permission' => 'admin',
        'submenu' => [
            ['title' => 'Unsur Pimpinan', 'url' => '#unsur-pimpinan', 'icon' => 'fa-crown', 'kategori' => 'pimpinan'],
            ['title' => 'Unsur Pembantu', 'url' => '#unsur-pembantu', 'icon' => 'fa-hands-helping', 'kategori' => 'pembantu_pimpinan'],
            ['title' => 'Pelaksana Tugas Pokok', 'url' => '#unsur-pelaksana', 'icon' => 'fa-tasks', 'kategori' => 'pelaksana_tugas_pokok'],
            ['title' => 'Pelaksana Kewilayahan', 'url' => '#unsur-kewilayahan', 'icon' => 'fa-map-marked-alt', 'kategori' => 'pelaksana_kewilayahan'],
            ['title' => 'Unsur Pendukung', 'url' => '#unsur-pendukung', 'icon' => 'fa-cogs', 'kategori' => 'pendukung'],
            ['title' => 'Bagian/Satuan', 'url' => 'bagian.php', 'icon' => 'fa-building'],
            ['title' => 'Jabatan', 'url' => 'jabatan.php', 'icon' => 'fa-user-tie'],
        ]
    ],
    'compliance' => [
        'title' => 'Compliance & Regulasi',
        'icon' => 'fa-balance-scale',
        'url' => '#',
        'permission' => 'admin',
        'submenu' => [
            ['title' => 'Monitoring PS %', 'url' => '#monitor-ps', 'icon' => 'fa-percentage', 'action' => 'showPsMonitoring()'],
            ['title' => 'Validasi Eselon', 'url' => '#validasi-eselon', 'icon' => 'fa-check-double', 'action' => 'showEselonValidation()'],
            ['title' => 'Peraturan', 'url' => '#regulasi', 'icon' => 'fa-book'],
            ['title' => 'Laporan Compliance', 'url' => '#laporan-compliance', 'icon' => 'fa-file-contract'],
        ]
    ],
    'laporan' => [
        'title' => 'Laporan & Analisis',
        'icon' => 'fa-chart-bar',
        'url' => '#',
        'permission' => 'all',
        'submenu' => [
            ['title' => 'Dashboard Analytics', 'url' => 'dashboard_v2.php', 'icon' => 'fa-chart-pie'],
            ['title' => 'Statistik Personil', 'url' => '#stats-personil', 'icon' => 'fa-users', 'action' => 'showPersonilStats()'],
            ['title' => 'Export PDF', 'url' => '#export-pdf', 'icon' => 'fa-file-pdf', 'action' => 'exportPDF()'],
            ['title' => 'Export Excel', 'url' => '#export-excel', 'icon' => 'fa-file-excel', 'action' => 'exportExcel()'],
        ]
    ],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'SPRIN v2.0 - Sistem Manajemen Personil POLRI'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    
    <style>
        :root {
            /* POLRI Colors */
            --polri-blue: #1a237e;
            --polri-blue-light: #3949ab;
            --polri-gold: #ffd700;
            --polri-dark: #0d1642;
            --polri-accent: #ff6b35;
            
            /* Theme Variables */
            --primary-color: var(--polri-blue);
            --secondary-color: var(--polri-blue-light);
            --accent-color: var(--polri-gold);
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --text-light: #ffffff;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --border-color: #dee2e6;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --hover-bg: rgba(26, 35, 126, 0.05);
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        /* Dark Theme */
        @media (prefers-color-scheme: dark) {
            :root {
                --primary-color: var(--polri-blue-light);
                --text-primary: #ffffff;
                --text-secondary: #b3b3b3;
                --bg-primary: #1a1a1a;
                --bg-secondary: #2d2d2d;
                --border-color: #404040;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Top Navigation Bar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: linear-gradient(135deg, var(--polri-blue) 0%, var(--polri-dark) 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            color: #fff !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .navbar-brand i {
            font-size: 2rem;
            color: var(--polri-gold);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-title {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .top-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .top-menu-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-menu-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .top-menu-btn i {
            font-size: 1rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            margin-left: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(255,255,255,0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--polri-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--polri-blue);
            font-weight: 700;
            font-size: 1rem;
        }

        .user-info {
            color: #fff;
            font-size: 0.85rem;
        }

        .user-name {
            font-weight: 600;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: linear-gradient(180deg, #fff 0%, #f8f9fa 100%);
            border-right: 1px solid var(--border-color);
            z-index: 1020;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 2px solid var(--border-color);
            background: linear-gradient(135deg, var(--polri-blue) 0%, var(--polri-blue-light) 100%);
            color: #fff;
        }

        .sidebar-title {
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-subtitle {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 4px;
        }

        .nav-section {
            padding: 8px 0;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            padding: 16px 20px 8px;
            border-top: 1px solid var(--border-color);
        }

        .nav-section:first-child .nav-section-title {
            border-top: none;
        }

        .nav-item-wrapper {
            position: relative;
        }

        .nav-link-main {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-link-main:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
            border-left-color: var(--polri-gold);
        }

        .nav-link-main.active {
            background: linear-gradient(90deg, var(--hover-bg) 0%, rgba(26,35,126,0.1) 100%);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .nav-link-main i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .nav-link-main:hover i,
        .nav-link-main.active i {
            color: var(--primary-color);
        }

        .nav-link-main .arrow {
            margin-left: auto;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
            color: var(--text-secondary);
        }

        .nav-link-main[aria-expanded="true"] .arrow {
            transform: rotate(90deg);
        }

        /* Submenu */
        .nav-submenu {
            background: #f1f3f4;
            overflow: hidden;
        }

        .nav-submenu .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 56px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-submenu .nav-link:hover {
            background: rgba(26,35,126,0.05);
            color: var(--primary-color);
            border-left-color: var(--polri-gold);
        }

        .nav-submenu .nav-link.active {
            background: rgba(26,35,126,0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .nav-submenu .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        /* Compliance Badge */
        .compliance-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: auto;
        }

        .compliance-badge.compliant {
            background: #d4edda;
            color: #155724;
        }

        .compliance-badge.warning {
            background: #fff3cd;
            color: #856404;
        }

        .compliance-badge.danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Quick Stats Bar */
        .quick-stats {
            display: flex;
            gap: 15px;
            padding: 15px 20px;
            background: linear-gradient(135deg, var(--polri-blue) 0%, var(--polri-blue-light) 100%);
            color: #fff;
            margin: 0;
        }

        .quick-stat {
            text-align: center;
            flex: 1;
        }

        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--polri-gold);
        }

        .quick-stat-label {
            font-size: 0.7rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }

        /* Breadcrumb */
        .breadcrumb-wrapper {
            background: #fff;
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            margin: -30px -30px 30px;
        }

        .breadcrumb {
            margin: 0;
            padding: 0;
            background: none;
            font-size: 0.9rem;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .brand-subtitle {
                display: none;
            }

            .top-menu-btn span {
                display: none;
            }

            .user-info {
                display: none;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(0,0,0,0.3);
        }

        /* Personil-First Flow Indicator */
        .flow-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 15px;
            background: linear-gradient(135deg, var(--polri-gold) 0%, #ffed4e 100%);
            color: var(--polri-blue);
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 20px;
            margin: 10px 20px;
        }

        .flow-indicator i {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <a class="navbar-brand" href="<?php echo url('pages/dashboard_v2.php'); ?>">
            <i class="fa-solid fa-shield-halved"></i>
            <div class="brand-text">
                <span class="brand-title">SPRIN v2.0</span>
                <span class="brand-subtitle">POLRES Samosir</span>
            </div>
        </a>

        <div class="top-menu">
            <button class="top-menu-btn" onclick="window.location.href='<?php echo url('pages/dashboard_v2.php'); ?>'">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </button>
            
            <button class="top-menu-btn" onclick="window.location.href='<?php echo url('pages/personil_management_v2.php'); ?>'">
                <i class="fa-solid fa-users"></i>
                <span>Personil</span>
            </button>

            <button class="top-menu-btn" onclick="toggleFullscreen()">
                <i class="fa-solid fa-expand"></i>
            </button>

            <button class="top-menu-btn" onclick="showHelp()">
                <i class="fa-solid fa-circle-question"></i>
            </button>
        </div>

        <div class="user-profile dropdown">
            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                <div class="user-role">Administrator</div>
            </div>
            <i class="fa-solid fa-chevron-down" style="color: #fff; font-size: 0.8rem;"></i>
            
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user-gear"></i> Profil</a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-key"></i> Ganti Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo url('core/logout.php'); ?>">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a></li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="quick-stat">
                <div class="quick-stat-value" id="statTotalPersonil">256</div>
                <div class="quick-stat-label">Personil</div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-value" id="statPsPercentage">12%</div>
                <div class="quick-stat-label">PS %</div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-value" id="statCompliance">OK</div>
                <div class="quick-stat-label">Status</div>
            </div>
        </div>

        <!-- Flow Indicator -->
        <div class="flow-indicator">
            <i class="fa-solid fa-sitemap"></i>
            <span>Personil-First Flow</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="nav-section">
            <?php foreach ($nav_structure as $key => $item): ?>
                <?php if (!isset($item['permission']) || $item['permission'] === 'all' || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')): ?>
                    
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Menu with Submenu -->
                        <div class="nav-item-wrapper">
                            <a class="nav-link-main <?php echo $current_page == $item['url'] || (isset($item['submenu']) && in_array($current_page, array_column($item['submenu'], 'url'))) ? 'active' : ''; ?>" 
                               data-bs-toggle="collapse" 
                               href="#submenu-<?php echo $key; ?>" 
                               role="button" 
                               aria-expanded="<?php echo $current_page == $item['url'] || (isset($item['submenu']) && in_array($current_page, array_column($item['submenu'], 'url'))) ? 'true' : 'false'; ?>">
                                <i class="fa-solid <?php echo $item['icon']; ?>"></i>
                                <span><?php echo $item['title']; ?></span>
                                <i class="fa-solid fa-chevron-right arrow"></i>
                            </a>
                            <div class="collapse <?php echo $current_page == $item['url'] || (isset($item['submenu']) && in_array($current_page, array_column($item['submenu'], 'url'))) ? 'show' : ''; ?>" id="submenu-<?php echo $key; ?>">
                                <div class="nav-submenu">
                                    <?php foreach ($item['submenu'] as $subitem): ?>
                                        <?php if (isset($subitem['action'])): ?>
                                            <a class="nav-link" href="javascript:void(0)" onclick="<?php echo $subitem['action']; ?>">
                                                <i class="fa-solid <?php echo $subitem['icon']; ?>"></i>
                                                <?php echo $subitem['title']; ?>
                                            </a>
                                        <?php else: ?>
                                            <a class="nav-link <?php echo $current_page == $subitem['url'] ? 'active' : ''; ?>" 
                                               href="<?php echo $subitem['url'] != '#' ? url('pages/' . $subitem['url']) : '#'; ?>">
                                                <i class="fa-solid <?php echo $subitem['icon']; ?>"></i>
                                                <?php echo $subitem['title']; ?>
                                                <?php if (isset($subitem['jenis']) && $subitem['jenis'] == 'PS'): ?>
                                                    <span class="compliance-badge warning">%</span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Simple Menu -->
                        <div class="nav-item-wrapper">
                            <a class="nav-link-main <?php echo $current_page == $item['url'] ? 'active' : ''; ?>" 
                               href="<?php echo url('pages/' . $item['url']); ?>">
                                <i class="fa-solid <?php echo $item['icon']; ?>"></i>
                                <span><?php echo $item['title']; ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <!-- Regulation Info -->
        <div class="nav-section">
            <div class="nav-section-title">Regulasi</div>
            <div class="nav-item-wrapper">
                <a class="nav-link-main" href="#" onclick="showRegulation('PERKAP23')">
                    <i class="fa-solid fa-book"></i>
                    <span>PERKAP No. 23/2010</span>
                </a>
            </div>
            <div class="nav-item-wrapper">
                <a class="nav-link-main" href="#" onclick="showRegulation('Perpol3')">
                    <i class="fa-solid fa-book"></i>
                    <span>Perpol No. 3/2024</span>
                </a>
            </div>
            <div class="nav-item-wrapper">
                <a class="nav-link-main" href="#" onclick="showRegulation('PP100')">
                    <i class="fa-solid fa-book"></i>
                    <span>PP No. 100/2000</span>
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="nav-section" style="margin-top: auto; border-top: 1px solid var(--border-color);">
            <div class="nav-item-wrapper">
                <a class="nav-link-main" href="#" onclick="showAbout()">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>v2.0 Personil-First</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('pages/dashboard_v2.php'); ?>"><i class="fa-solid fa-home"></i> Beranda</a></li>
                    <?php foreach ($breadcrumb as $crumb): ?>
                        <?php if (isset($crumb['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['title']; ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?php echo $crumb['title']; ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
        <?php endif; ?}

?>