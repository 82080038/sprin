/**
 * includes/components/header.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php;session_start () ;/DevelopmentErrorReportingif (!defined ('DEVELOPMENT_MODE') ) {error_reporting (E_ALL) ;ini_set ('display_errors',1) ;ini_set ('display_startup_errors',1) ;}declare (strict_types=1) ;;/Includeconfigifnotalreadyincludedif (!defined ('BASE_URL') ) {require_once__DIR__..
    '/../../core/config..
    php';}/Getcurrentpageforactivestate$current_page=basename ($_SERVER['PHP_SELF']) ;?><!DOCTYPEhtml><htmllang="id"><head><metacharset="UTF-8"><metaname="viewport"content="width=device-width,initial-scale=1..
    0"><title><?php;session_start () ;echo$page_title??'SistemManajemenPOLRESSamosir';?></title><!--BootstrapCSS--><linkhref="https://cdn..
    jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min..
    css"rel="stylesheet"><!--FontAwesome6-Lateststableversion--><linkrel="stylesheet"href="https://cdnjs..
    cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min..
    css"integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="crossorigin="anonymous"referrerpolicy="no-referrer"/><style>/*Minimalcustomstyling-Bootstraphandlestherest*/body{padding-top:80px;}/*Brandcolorsonlyforspecificelements*/..
    navbar-brand{color:#1a237e!important;font-weight:bold;}/*Enhancedvisibilityfortogglebuttons*/..
    btn-outline-primary{color:#0d6efd!important;border-color:#0d6efd!important;background-color:rgba (13,110,253,0..
    1) !important;border-width:2px!important;box-shadow:02px4pxrgba (13,110,253,0.2) !important;}..
    btn-outline-primary:hover,..
    btn-outline-primary:focus{color:#ffffff!important;background-color:#0d6efd!important;border-color:#0d6efd!important;box-shadow:04px8pxrgba (13,110,253,0..
    3) !important;transform:translateY (-1px) !important;}..
    btn-outline-secondary{color:#6c757d!important;border-color:#6c757d!important;background-color:rgba (108,117,125,0..
    1) !important;border-width:2px!important;box-shadow:02px4pxrgba (108,117,125,0.2) !important;}..
    btn-outline-secondary:hover,..
    btn-outline-secondary:focus{color:#ffffff!important;background-color:#6c757d!important;border-color:#6c757d!important;box-shadow:04px8pxrgba (108,117,125,0..
    3) !important;transform:translateY (-1px) !important;}/*Ensuretogglebuttonsareprominent*/button[onclick*="toggle"]{min-width:2..
    5rem!important;min-height:2..
    5rem!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;position:relative!important;z-index:10!important;transition:all0..
    2sease!important;}button[onclick*="toggle"]:hover{transform:scale (1..
    05) !important;}button[onclick*="toggle"]i{visibility:visible!important;display:inline-block!important;font-size:14px!important;transition:transform0..
    2sease!important;}button[onclick*="toggle"]:hoveri{transform:scale (1..
    1) !important;}/*Makesuretogglebuttonsstandoutincardheaders*/..
    card-headerbutton[onclick*="toggle"]{background-color:rgba (13,110,253,0..
    15) !important;border-color:#0d6efd!important;}..
    card-headerbutton[onclick*="toggle"]:hover{background-color:#0d6efd!important;color:#ffffff!important;}/*Activecardstyling*/..
    card-header.active{background-color:#0d6efd!important;color:#ffffff!important;}.card-header..
    activeh5,.card-header.activeh6,.card-header..
    activesmall{color:#ffffff!important;}/*Clickablecardheaders*/..
    card-header[onclick]{transition:background-color0.2sease!important;}..
    card-header[onclick]:hover{background-color:rgba (13,110,253,0.1) !important;}.card-header..
    bg-primary[onclick]:hover{background-color:rgba (13,110,253,0.9) !important;}.card-header..
    bg-light[onclick]:hover{background-color:#f8f9fa!important;}/*EnhancedBootstrapaccordionstyling*/..
    accordion-button:not (..
    collapsed) {background-color:#0d6efd!important;color:#ffffff!important;box-shadow:none!important;}..
    accordion-button:not (.collapsed) :hover{background-color:#0b5ed7!important;}..
    accordion-button:focus{box-shadow:none!important;border-color:rgba (13,110,253,0..
    25) !important;}.accordion-button.collapsed:hover{background-color:rgba (13,110,253,0..
    05) !important;}.accordion-item{border:1pxsolidrgba (13,110,253,0..
    125) !important;margin-bottom:0.5rem!important;border-radius:0.375rem!important;}..
    accordion-item:first-of-type{border-top-left-radius:0..
    375rem!important;border-top-right-radius:0.375rem!important;}..
    accordion-item:last-of-type{border-bottom-left-radius:0..
    375rem!important;border-bottom-right-radius:0.375rem!important;}..
    accordion-header{border-radius:0.375rem!important;}/*Nestedaccordionstyling*/.accordion-flush..
    accordion-item{border-left:none!important;border-right:none!important;border-radius:0!important;}..
    accordion-flush.accordion-button{border-radius:0!important;padding-left:1..
    5rem!important;background-color:#f8f9fa!important;}.accordion-flush.accordion-button:not (..
    collapsed) {background-color:#e9ecef!important;color:#212529!important;}/*Buttonstylinginaccordionheaders*/..
    accordion-buttonbutton{pointer-events:auto!important;}.accordion-button.btn-sm{font-size:0..
    75rem!important;padding:0.25rem0..
    5rem!important;}</style></head><body><!--Navigation--><navclass="navbarnavbar-expand-lgnavbar-lightbg-whitefixed-topshadow-sm"><divclass="container"><aclass="navbar-brand"href="<?php;session_start () ;echoBASE_URL;?>/main..
    php"><iclass="fasfa-shield-alt"></i>POLRESSAMOSIR</a><buttonclass="navbar-toggler"type="button"data-bs-toggle="collapse"data-bs-target="#navbarNav"><spanclass="navbar-toggler-icon"></span></button><divclass="collapsenavbar-collapse"id="navbarNav"><ulclass="navbar-navme-auto"><liclass="nav-item"><aclass="nav-link<?php;session_start () ;echo$current_page=='main..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/main..
    php"><iclass="fasfa-home"></i>Dashboard</a></li><liclass="nav-item"><aclass="nav-link<?php;session_start () ;echo$current_page=='personil..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/personil..
    php"><iclass="fa-solidfa-users"></i>Personil</a></li><liclass="nav-itemdropdown"><aclass="nav-linkdropdown-toggle"href="#"role="button"data-bs-toggle="dropdown"><iclass="fa-solidfa-cogs"></i>Manajemen</a><ulclass="dropdown-menu"><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='unsur..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/unsur..
    php"><iclass="fa-solidfa-sitemap"></i>ManajemenUnsur</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='bagian..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/bagian..
    php"><iclass="fa-solidfa-gear"></i>ManajemenBagian</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='jenis_personil..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/jenis_personil..
    php"><iclass="fa-solidfa-users-cog"></i>ManajemenJenisPersonil</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='jabatan..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/jabatan..
    php"><iclass="fa-solidfa-user-tie"></i>ManajemenJabatan</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='pangkat..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/pangkat..
    php"><iclass="fa-solidfa-graduation-cap"></i>ManajemenPangkat</a></li></ul></li><liclass="nav-itemdropdown"><aclass="nav-linkdropdown-toggle"href="#"role="button"data-bs-toggle="dropdown"><iclass="fa-solidfa-calendar"></i>Jadwal</a><ulclass="dropdown-menu"><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='jadwal..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/jadwal..
    php"><iclass="fa-solidfa-calendar-alt"></i>ManajemenJadwal</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='calendar..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/calendar..
    php"><iclass="fa-solidfa-calendar"></i>Kalender</a></li></ul></li><liclass="nav-itemdropdown"><aclass="nav-linkdropdown-toggle"href="#"role="button"data-bs-toggle="dropdown"><iclass="fa-solidfa-chart-bar"></i>Laporan</a><ulclass="dropdown-menu"><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='laporan_personil..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/laporan_personil..
    php"><iclass="fa-solidfa-users"></i>LaporanPersonil</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='laporan_jadwal..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/laporan_jadwal..
    php"><iclass="fa-solidfa-calendar-alt"></i>LaporanJadwal</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='statistik..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/statistik..
    php"><iclass="fa-solidfa-chart-line"></i>Statistik</a></li></ul></li><liclass="nav-itemdropdown"><aclass="nav-linkdropdown-toggle"href="#"role="button"data-bs-toggle="dropdown"><iclass="fa-solidfa-tools"></i>Utilitas</a><ulclass="dropdown-menu"><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='backup..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/backup..
    php"><iclass="fa-solidfa-database"></i>Backup&Restore</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='export..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/export..
    php"><iclass="fa-solidfa-download"></i>ExportData</a></li><li><aclass="dropdown-item<?php;session_start () ;echo$current_page=='settings..
    php'?'active':'';?>"href="<?php;session_start () ;echoBASE_URL;?>/pages/settings..
    php"><iclass="fa-solidfa-cog"></i>Pengaturan</a></li></ul></li></ul><ulclass="navbar-nav"><liclass="nav-itemdropdown"><aclass="nav-linkdropdown-toggle"href="#"role="button"data-bs-toggle="dropdown"><iclass="fa-solidfa-user"></i><?php;session_start () ;echo$_SESSION['user_name']??'User';?></a><ulclass="dropdown-menudropdown-menu-end"><li><aclass="dropdown-item"href="performance_middleware..
    php"><iclass="fa-solidfa-user-circle"></i>Profil</a></li><li><aclass="dropdown-item"href="ActionDispatcher..
    d..
    ts"><iclass="fa-solidfa-key"></i>UbahPassword</a></li><li><hrclass="dropdown-divider"></li><li><aclass="dropdown-item"href="<?php;session_start () ;echoBASE_URL;?>/logout..
    php"><iclass="fa-solidfa-sign-out-alt"></i>Logout</a></li></ul></li></ul></div></div></nav><!--BootstrapJS--><scriptsrc="https://cdn..
    jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min..
    js"></script><!--ToastNotifications--><scriptsrc="https://cdnjs.cloudflare.com/ajax/libs/toastr..
    js/latest/toastr.min.js"></script><linkrel="stylesheet"href="https://cdnjs.cloudflare..
    com/ajax/libs/toastr.js/latest/toastr.min..
    css"><!--jQuery (requiredforsomeplugins) --><scriptsrc="https://code.jquery.com/jquery-3.6.0..
    min.js"></script><!--SweetAlert2forbettermodals--><scriptsrc="https://cdn.jsdelivr..
    net/npm/sweetalert2@11/dist/sweetalert2.all.min..
    js"></script><!--CustomJS--><scriptsrc="<?php;session_start () ;echoBASE_URL;?>/assets/js/app..
    js"></script>.
