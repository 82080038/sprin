/**
 * includes/components/nav_header_v2.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php;session_start () ;/DevelopmentErrorReportingif (!defined ('DEVELOPMENT_MODE') ) {error_reporting (E_ALL) ;ini_set ('display_errors',1) ;ini_set ('display_startup_errors',1) ;}declare (strict_types=1) ;;/***POLRIRegulation-CompliantNavigationHeaderv2..
    0*BasedonPERKAPNo.23/2010,PerpolNo.3/2024,PPNo..
    100/2000*Personil-FirstFlowArchitecture*//Includeconfigifnotalreadyincludedif (!defined ('BASE_URL') ) {require_once__DIR__..
    '/../../core/config..
    php';}/Getcurrentpageforactivestate$current_page=basename ($_SERVER['PHP_SELF']) ;/DefinenavigationstructurebasedonPOLRIhierarchy$nav_structure='\1\'=>\2,'personil'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],'kepegawaian'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],'penugasan'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],'struktur'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],'compliance'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],'laporan'=>'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,'\1\'=>\2,]],];?><!DOCTYPEhtml><htmllang="id"><head><metacharset="UTF-8"><metaname="viewport"content="width=device-width,initial-scale=1..
    0"><title><?php;session_start () ;echo$page_title??'SPRINv2..
    0-SistemManajemenPersonilPOLRI';?></title><!--BootstrapCSS--><linkhref="https://cdn.jsdelivr..
    net/npm/bootstrap@5.3.0/dist/css/bootstrap.min..
    css"rel="stylesheet"><!--FontAwesome6--><linkrel="stylesheet"href="https://cdnjs.cloudflare..
    com/ajax/libs/font-awesome/6.4.2/css/all.min..
    css"/><style>:root{/*POLRIColors*/--polri-blue:#1a237e;--polri-blue-light:#3949ab;--polri-gold:#ffd700;--polri-dark:#0d1642;--polri-accent:#ff6b35;/*ThemeVariables*/--primary-color:var (--polri-blue) ;--secondary-color:var (--polri-blue-light) ;--accent-color:var (--polri-gold) ;--text-primary:#212529;--text-secondary:#6c757d;--text-light:#ffffff;--bg-primary:#ffffff;--bg-secondary:#f8f9fa;--border-color:#dee2e6;--shadow-color:rgba (0,0,0,0..
    1) ;--hover-bg:rgba (26,35,126,0..
    05) ;--sidebar-width:280px;--header-height:70px;}/*DarkTheme*/@media (prefers-color-scheme:dark) {:root{--primary-color:var (--polri-blue-light) ;--text-primary:#ffffff;--text-secondary:#b3b3b3;--bg-primary:#1a1a1a;--bg-secondary:#2d2d2d;--border-color:#404040;}}*{margin:0;padding:0;box-sizing:border-box;}body{font-family:'SegoeUI',Tahoma,Geneva,Verdana,sans-serif;background:var (--bg-secondary) ;color:var (--text-primary) ;line-height:1..
    6;overflow-x:hidden;}/*TopNavigationBar*/..
    top-navbar{position:fixed;top:0;left:0;right:0;height:var (--header-height) ;background:linear-gradient (135deg,var (--polri-blue) 0%,var (--polri-dark) 100%) ;box-shadow:04px20pxrgba (0,0,0,0..
    3) ;z-index:1030;display:flex;align-items:center;padding:020px;}..
    navbar-brand{font-weight:800;font-size:1..
    4rem;color:#fff!important;text-decoration:none;display:flex;align-items:center;gap:12px;text-shadow:2px2px4pxrgba (0,0,0,0..
    3) ;}.navbar-brandi{font-size:2rem;color:var (--polri-gold) ;}..
    brand-text{display:flex;flex-direction:column;line-height:1.2;}.brand-title{font-size:1..
    1rem;font-weight:800;letter-spacing:1px;}.brand-subtitle{font-size:0.75rem;opacity:0..
    9;font-weight:500;}.top-menu{display:flex;align-items:center;gap:8px;margin-left:auto;}..
    top-menu-btn{background:rgba (255,255,255,0.1) ;border:1pxsolidrgba (255,255,255,0..
    2) ;color:#fff;padding:8px16px;border-radius:8px;font-size:0..
    9rem;font-weight:500;cursor:pointer;transition:all0..
    3sease;display:flex;align-items:center;gap:8px;}..
    top-menu-btn:hover{background:rgba (255,255,255,0.2) ;transform:translateY (-2px) ;}..
    top-menu-btni{font-size:1rem;}..
    user-profile{display:flex;align-items:center;gap:12px;padding:8px16px;background:rgba (255,255,255,0..
    1) ;border-radius:30px;margin-left:15px;cursor:pointer;transition:all0.3sease;}..
    user-profile:hover{background:rgba (255,255,255,0.2) ;}..
    user-avatar{width:36px;height:36px;background:var (--polri-gold) ;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var (--polri-blue) ;font-weight:700;font-size:1rem;}..
    user-info{color:#fff;font-size:0.85rem;}.user-name{font-weight:600;line-height:1.2;}..
    user-role{font-size:0.75rem;opacity:0.8;}/*SidebarNavigation*/..
    sidebar{position:fixed;top:var (--header-height) ;left:0;width:var (--sidebar-width) ;height:calc (100vh-var (--header-height) ) ;background:linear-gradient (180deg,#fff0%,#f8f9fa100%) ;border-right:1pxsolidvar (--border-color) ;z-index:1020;overflow-y:auto;overflow-x:hidden;box-shadow:4px020pxrgba (0,0,0,0..
    05) ;}..
    sidebar-header{padding:20px;border-bottom:2pxsolidvar (--border-color) ;background:linear-gradient (135deg,var (--polri-blue) 0%,var (--polri-blue-light) 100%) ;color:#fff;}..
    sidebar-title{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:10px;}..
    sidebar-subtitle{font-size:0.75rem;opacity:0.9;margin-top:4px;}.nav-section{padding:8px0;}..
    nav-section-title{font-size:0..
    7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var (--text-secondary) ;padding:16px20px8px;border-top:1pxsolidvar (--border-color) ;}..
    nav-section:first-child.nav-section-title{border-top:none;}..
    nav-item-wrapper{position:relative;}..
    nav-link-main{display:flex;align-items:center;padding:12px20px;color:var (--text-primary) ;text-decoration:none;font-weight:500;font-size:0..
    95rem;transition:all0.3sease;border-left:3pxsolidtransparent;position:relative;}..
    nav-link-main:hover{background:var (--hover-bg) ;color:var (--primary-color) ;border-left-color:var (--polri-gold) ;}..
    nav-link-main.active{background:linear-gradient (90deg,var (--hover-bg) 0%,rgba (26,35,126,0..
    1) 100%) ;color:var (--primary-color) ;border-left-color:var (--primary-color) ;font-weight:600;}..
    nav-link-maini{width:24px;text-align:center;margin-right:12px;font-size:1..
    1rem;color:var (--text-secondary) ;transition:all0.3sease;}.nav-link-main:hoveri,.nav-link-main..
    activei{color:var (--primary-color) ;}.nav-link-main.arrow{margin-left:auto;font-size:0..
    8rem;transition:transform0.3sease;color:var (--text-secondary) ;}..
    nav-link-main[aria-expanded="true"].arrow{transform:rotate (90deg) ;}/*Submenu*/..
    nav-submenu{background:#f1f3f4;overflow:hidden;}.nav-submenu..
    nav-link{display:flex;align-items:center;padding:10px20px10px56px;color:var (--text-secondary) ;text-decoration:none;font-size:0..
    9rem;transition:all0.3sease;border-left:3pxsolidtransparent;}.nav-submenu..
    nav-link:hover{background:rgba (26,35,126,0..
    05) ;color:var (--primary-color) ;border-left-color:var (--polri-gold) ;}.nav-submenu.nav-link..
    active{background:rgba (26,35,126,0..
    1) ;color:var (--primary-color) ;border-left-color:var (--primary-color) ;font-weight:600;}..
    nav-submenu.nav-linki{width:20px;margin-right:10px;font-size:0.9rem;}/*ComplianceBadge*/..
    compliance-badge{display:inline-flex;align-items:center;padding:2px8px;border-radius:10px;font-size:0..
    7rem;font-weight:700;margin-left:auto;}.compliance-badge..
    compliant{background:#d4edda;color:#155724;}.compliance-badge..
    warning{background:#fff3cd;color:#856404;}.compliance-badge..
    danger{background:#f8d7da;color:#721c24;}/*QuickStatsBar*/..
    quick-stats{display:flex;gap:15px;padding:15px20px;background:linear-gradient (135deg,var (--polri-blue) 0%,var (--polri-blue-light) 100%) ;color:#fff;margin:0;}..
    quick-stat{text-align:center;flex:1;}.quick-stat-value{font-size:1..
    5rem;font-weight:700;color:var (--polri-gold) ;}.quick-stat-label{font-size:0.7rem;opacity:0..
    9;text-transform:uppercase;letter-spacing:0.5px;}/*MainContentArea*/..
    main-content{margin-left:var (--sidebar-width) ;margin-top:var (--header-height) ;padding:30px;min-height:calc (100vh-var (--header-height) ) ;}/*Breadcrumb*/..
    breadcrumb-wrapper{background:#fff;padding:15px30px;border-bottom:1pxsolidvar (--border-color) ;margin:-30px-30px30px;}..
    breadcrumb{margin:0;padding:0;background:none;font-size:0.9rem;}..
    breadcrumb-itema{color:var (--primary-color) ;text-decoration:none;}.breadcrumb-item..
    active{color:var (--text-secondary) ;font-weight:500;}/*Responsive*/@media (max-width:992px) {..
    sidebar{transform:translateX (-100%) ;transition:transform0.3sease;}.sidebar..
    show{transform:translateX (0) ;}.main-content{margin-left:0;}..
    mobile-toggle{display:block;}}@media (max-width:768px) {.brand-subtitle{display:none;}..
    top-menu-btnspan{display:none;}.user-info{display:none;}}/*ScrollbarStyling*/..
    sidebar::-webkit-scrollbar{width:6px;}..
    sidebar::-webkit-scrollbar-track{background:transparent;}..
    sidebar::-webkit-scrollbar-thumb{background:rgba (0,0,0,0.2) ;border-radius:3px;}..
    sidebar::-webkit-scrollbar-thumb:hover{background:rgba (0,0,0,0..
    3) ;}/*Personil-FirstFlowIndicator*/..
    flow-indicator{display:flex;align-items:center;justify-content:center;gap:8px;padding:8px15px;background:linear-gradient (135deg,var (--polri-gold) 0%,#ffed4e100%) ;color:var (--polri-blue) ;font-size:0..
    75rem;font-weight:700;border-radius:20px;margin:10px20px;}.flow-indicatori{font-size:0..
    9rem;}</style></head><body><!--TopNavigationBar--><navclass="top-navbar"><aclass="navbar-brand"href="index..
    php?phpechourl (page_url ('dashboard_v2..
    php') ) ;?>"><iclass="fa-solidfa-shield-halved"></i><divclass="brand-text"><spanclass="brand-title">SPRINv2..
    0</span><spanclass="brand-subtitle">POLRESSamosir</span></div></a><divclass="top-menu"><buttonclass="top-menu-btn"onclick="window..
    location.href='index.php?phpechourl (page_url ('dashboard_v2..
    php') ) ;?>'"><iclass="fa-solidfa-gauge-high"></i><span>Dashboard</span></button><buttonclass="top-menu-btn"onclick="window..
    location.href='index.php?phpechourl (page_url ('personil_management_v2..
    php') ) ;?>'"><iclass="fa-solidfa-users"></i><span>Personil</span></button><buttonclass="top-menu-btn"onclick="toggleFullscreen () "><iclass="fa-solidfa-expand"></i></button><buttonclass="top-menu-btn"onclick="showHelp () "><iclass="fa-solidfa-circle-question"></i></button></div><divclass="user-profiledropdown"><divclass="user-avatar"><iclass="fa-solidfa-user"></i></div><divclass="user-info"><divclass="user-name"><?php;session_start () ;echohtmlspecialchars ($_SESSION['username']??'Admin') ;?></div><divclass="user-role">Administrator</div></div><iclass="fa-solidfa-chevron-down"style="color:#fff;font-size:0..
    8rem;"></i><ulclass="dropdown-menudropdown-menu-end"><li><aclass="dropdown-item"href="#"><iclass="fa-solidfa-user-gear"></i>Profil</a></li><li><aclass="dropdown-item"href="#"><iclass="fa-solidfa-key"></i>GantiPassword</a></li><li><hrclass="dropdown-divider"></li><li><aclass="dropdown-itemtext-danger"href="index..
    php?phpechourl ('core/logout..
    php') ;?>"><iclass="fa-solidfa-right-from-bracket"></i>Logout</a></li></ul></div></nav><!--SidebarNavigation--><asideclass="sidebar"><!--QuickStats--><divclass="quick-stats"><divclass="quick-stat"><divclass="quick-stat-value"id="statTotalPersonil">256</div><divclass="quick-stat-label">Personil</div></div><divclass="quick-stat"><divclass="quick-stat-value"id="statPsPercentage">12%</div><divclass="quick-stat-label">PS%</div></div><divclass="quick-stat"><divclass="quick-stat-value"id="statCompliance">OK</div><divclass="quick-stat-label">Status</div></div></div><!--FlowIndicator--><divclass="flow-indicator"><iclass="fa-solidfa-sitemap"></i><span>Personil-FirstFlow</span></div><!--NavigationMenu--><navclass="nav-section"><?php;session_start () ;foreach ($nav_structureas$key=>$item) :?><?php;.
session_start () ;if (!isset ($item['permission']) ||$item['permission']==='all'|| (isset ($_SESSION['user_role']) &&$_SESSION['user_role']==='admin') ) :?><?php;session_start () ;if (isset ($item['submenu']) ) :?><!--MenuwithSubmenu--><divclass="nav-item-wrapper"><aclass="nav-link-main<?php;session_start () ;echo$current_page==$item['url']|| (isset ($item['submenu']) &&in_[$current_page,array_column ($item['submenu'],'url']?'active':'';?>"data-bs-toggle="collapse"href="#submenu-<?php;session_start () ;echo$key;?>"role="button"aria-expanded="<?php;session_start () ;echo$current_page==$item['url']|| (isset ($item['submenu']) &&in_[$current_page,array_column ($item['submenu'],'url']?'true':'false';?>"><iclass="fa-solid<?php;session_start () ;echo$item['icon'];?>"></i><span><?php;session_start () ;echo$item['title'];?></span><iclass="fa-solidfa-chevron-rightarrow"></i></a><divclass="collapse<?php;session_start () ;echo$current_page==$item['url']|| (isset ($item['submenu']) &&in_[$current_page,array_column ($item['submenu'],'url']?'show':'';?>"id="submenu-<?php;session_start () ;echo$key;?>"><divclass="nav-submenu"><?php;session_start () ;foreach ($item['submenu']as$subitem) :?><?php;session_start () ;if (isset ($subitem['action']) ) :?><aclass="nav-link"href="ActionDispatcher..
    js"onclick="<?php;session_start () ;echo$subitem['action'];?>"><iclass="fa-solid<?php;session_start () ;echo$subitem['icon'];?>"></i><?php;session_start () ;echo$subitem['title'];?></a><?php;session_start () ;else:?><aclass="nav-link<?php;session_start () ;echo$current_page==$subitem['url']?'active':'';?>"href="<?php;session_start () ;echo$subitem['url']!='#'?url ('pages/'..
    $subitem['url']) :'#';?>"><iclass="fa-solid<?php;session_start () ;echo$subitem['icon'];?>"></i><?php;session_start () ;echo$subitem['title'];?><?php;session_start () ;if (isset ($subitem['jenis']) &&$subitem['jenis']=='PS') :?><spanclass="compliance-badgewarning">%</span><?php;session_start () ;endif;?></a><?php;session_start () ;endif;?><?php;session_start () ;endforeach;?></div></div></div><?php;session_start () ;else:?><!--SimpleMenu--><divclass="nav-item-wrapper"><aclass="nav-link-main<?php;session_start () ;echo$current_page==$item['url']?'active':'';?>"href="index..
    php?phpechourl ('pages/'..
    $item['url']) ;?>"><iclass="fa-solid<?php;session_start () ;echo$item['icon'];?>"></i><span><?php;session_start () ;echo$item['title'];?></span></a></div><?php;session_start () ;endif;?><?php;session_start () ;endif;?><?php;session_start () ;endforeach;?></nav><!--RegulationInfo--><divclass="nav-section"><divclass="nav-section-title">Regulasi</div><divclass="nav-item-wrapper"><aclass="nav-link-main"href="#"onclick="showRegulation ('PERKAP23') "><iclass="fa-solidfa-book"></i><span>PERKAPNo..
    23/2010</span></a></div><divclass="nav-item-wrapper"><aclass="nav-link-main"href="#"onclick="showRegulation ('Perpol3') "><iclass="fa-solidfa-book"></i><span>PerpolNo..
    3/2024</span></a></div><divclass="nav-item-wrapper"><aclass="nav-link-main"href="#"onclick="showRegulation ('PP100') "><iclass="fa-solidfa-book"></i><span>PPNo..
    100/2000</span></a></div></div><!--SystemInfo--><divclass="nav-section"style="margin-top:auto;border-top:1pxsolidvar (--border-color) ;"><divclass="nav-item-wrapper"><aclass="nav-link-main"href="#"onclick="showAbout () "><iclass="fa-solidfa-circle-info"></i><span>v2..
    0Personil-First</span></a></div></div></aside><!--MainContent--><mainclass="main-content"><?php;session_start () ;if (isset ($breadcrumb) &&!empty ($breadcrumb) ) :?><divclass="breadcrumb-wrapper"><navaria-label="breadcrumb"><olclass="breadcrumb"><liclass="breadcrumb-item"><ahref="index..
    php?phpechourl (page_url ('dashboard_v2..
    php') ) ;?>"><iclass="fa-solidfa-home"></i>Beranda</a></li><?php;session_start () ;foreach ($breadcrumbas$crumb) :?><?php;session_start () ;if (isset ($crumb['url']) ) :?><liclass="breadcrumb-item"><ahref="index..
    php?phpecho$crumb['url'];?>">index..
    php?phpecho$crumb['title'];?></a></li><?php;session_start () ;else:?><liclass="breadcrumb-itemactive">index..
    php?phpecho$crumb['title'];?></li><?php;session_start () ;endif;?><?php;session_start () ;endforeach;?></ol></nav></div><?php;session_start () ;endif;?}?>.
