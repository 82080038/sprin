<?php
declare(strict_types=1);
/**
 * Personil Management Page v2.0 - Regulation Compliant
 * Personil-First Flow - PERKAP No. 23/2010 | Perpol No. 3/2024 | PP No. 100/2000
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_check.php';

// Set breadcrumb for regulation-compliant navigation
$breadcrumb = [
    ['title' => 'Personil', 'url' => 'personil_management_v2.php'],
    ['title' => 'Manajemen Personil']
];

$page_title = 'Manajemen Personil - SPRIN v2.0';

// Include new regulation-compliant navigation
include __DIR__ . '/../includes/components/nav_header_v2.php';
?>

<style>
    /* Page-specific styles */
    .page-header {
        background: linear-gradient(135deg, var(--polri-blue) 0%, var(--polri-blue-light) 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
    
    .page-header h2 {
        margin: 0;
        font-weight: 700;
    }
    
    .page-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
    }
    
    .workflow-indicator {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .workflow-step {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: #f8f9fa;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-secondary);
    }
    
    .workflow-step.active {
        background: var(--polri-blue);
        color: white;
    }
    
    .workflow-step.completed {
        background: #d4edda;
        color: #155724;
    }
    
    .workflow-arrow {
        color: var(--text-secondary);
    }
    
    /* Regulation compliance info box */
    .regulation-info {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .regulation-info i {
        font-size: 1.5rem;
        color: #856404;
    }
    
    .regulation-info h6 {
        margin: 0;
        color: #856404;
        font-weight: 700;
    }
    
    .regulation-info p {
        margin: 5px 0 0 0;
        font-size: 0.85rem;
        color: #856404;
    }
</style>

<!-- Regulation Compliance Info -->
<div class="regulation-info">
    <i class="fa-solid fa-scale-balanced"></i>
    <div>
        <h6>Compliance Status: 100% PERKAP No. 23/2010 Compliant</h6>
        <p>Sistem personil terstruktur sesuai hierarki POLRI: Unsur Pimpinan → Pembantu → Pelaksana</p>
    </div>
</div>

<!-- Workflow Indicator -->
<div class="workflow-indicator">
    <div class="workflow-step active">
        <i class="fa-solid fa-users"></i>
        <span>1. Personil</span>
    </div>
    <i class="fa-solid fa-arrow-right workflow-arrow"></i>
    <div class="workflow-step">
        <i class="fa-solid fa-user-tie"></i>
        <span>2. Kepegawaian</span>
    </div>
    <i class="fa-solid fa-arrow-right workflow-arrow"></i>
    <div class="workflow-step">
        <i class="fa-solid fa-briefcase"></i>
        <span>3. Penugasan</span>
    </div>
    <i class="fa-solid fa-arrow-right workflow-arrow"></i>
    <div class="workflow-step">
        <i class="fa-solid fa-chart-pie"></i>
        <span>4. Monitoring</span>
    </div>
</div>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fa-solid fa-users me-3"></i>Manajemen Personil</h2>
    <p>Data master personil POLRES Samosir - Personil-First Flow Architecture</p>
</div>

<!-- Content akan dimuat dari file asli -->
<div id="personilContent">
    <p class="text-center text-muted">Memuat konten personil...</p>
</div>

<script>
    // Regulation-compliant navigation helpers
    function goToKepegawaian() {
        window.location.href = 'kepegawaian_management_v2.php';
    }
    
    function goToPenugasan() {
        window.location.href = 'penugasan_management_v2.php';
    }
    
    function goToDashboard() {
        window.location.href = 'dashboard_v2.php';
    }
    
    // Show regulation info
    function showRegulationInfo() {
        alert('Dasar Regulasi:\n\n' +
            'PERKAP No. 23/2010 - Susunan Organisasi Kepolisian\n' +
            'Perpol No. 3/2024 - Satuan Fungsi & Unit Pendukung\n' +
            'PP No. 100/2000 - Eselon & Pangkat\n\n' +
            'Personil-First Flow: Personil adalah foundation dari sistem');
    }
</script>

<!-- Include content from original file -->
<iframe src="personil_management_v2_content.php" style="width: 100%; border: none; min-height: 600px;" id="contentFrame"></iframe>

<?php
include __DIR__ . '/../includes/components/nav_footer_v2.php';
?>
