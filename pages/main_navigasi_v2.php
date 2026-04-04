<?php
declare(strict_types=1);
/**
 * Main Navigation Index v2.0
 * Personil-First Flow Architecture
 * PERKAP No. 23/2010 | Perpol No. 3/2024 | PP No. 100/2000 Compliant
 */

require_once '../core/config.php';

// Set breadcrumb
$breadcrumb = [
    ['title' => 'Navigasi Utama']
];

$page_title = 'Navigasi Sistem - SPRIN v2.0';

include '../includes/components/nav_header_v2.php';
?>

<style>
    .nav-index-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .flow-diagram {
        background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .flow-diagram::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    
    .flow-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 10px;
        text-align: center;
    }
    
    .flow-subtitle {
        text-align: center;
        opacity: 0.9;
        margin-bottom: 30px;
    }
    
    .flow-steps {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .flow-step {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px 25px;
        background: rgba(255,255,255,0.1);
        border-radius: 15px;
        border: 2px solid rgba(255,215,0,0.3);
        transition: all 0.3s ease;
        min-width: 200px;
    }
    
    .flow-step:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-5px);
        border-color: var(--polri-gold);
    }
    
    .flow-step.active {
        background: rgba(255,215,0,0.2);
        border-color: var(--polri-gold);
    }
    
    .flow-step-icon {
        width: 50px;
        height: 50px;
        background: var(--polri-gold);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--polri-blue);
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .flow-step-content h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .flow-step-content p {
        margin: 0;
        font-size: 0.85rem;
        opacity: 0.8;
    }
    
    .flow-arrow {
        font-size: 2rem;
        color: var(--polri-gold);
    }
    
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .module-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
    }
    
    .module-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    
    .module-header {
        padding: 25px;
        color: white;
        position: relative;
    }
    
    .module-header.personil {
        background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
    }
    
    .module-header.kepegawaian {
        background: linear-gradient(135deg, #198754 0%, #28a745 100%);
    }
    
    .module-header.penugasan {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }
    
    .module-header.struktur {
        background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
    }
    
    .module-header.compliance {
        background: linear-gradient(135deg, #dc3545 0%, #f44 100%);
    }
    
    .module-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }
    
    .module-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .module-desc {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .module-body {
        padding: 20px 25px;
    }
    
    .module-features {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    
    .module-features li {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .module-features li:last-child {
        border-bottom: none;
    }
    
    .module-features i {
        color: var(--polri-blue);
        width: 20px;
    }
    
    .module-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-module {
        flex: 1;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
    }
    
    .btn-module-primary {
        background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
        color: white;
    }
    
    .btn-module-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(26,35,126,0.3);
        color: white;
    }
    
    .btn-module-secondary {
        background: #f8f9fa;
        color: var(--text-primary);
        border: 1px solid #dee2e6;
    }
    
    .btn-module-secondary:hover {
        background: #e9ecef;
    }
    
    .regulation-bar {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .regulation-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--polri-blue);
    }
    
    .regulation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .regulation-item {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid var(--polri-blue);
        transition: all 0.3s ease;
    }
    
    .regulation-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .regulation-item h5 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--polri-blue);
    }
    
    .regulation-item p {
        font-size: 0.85rem;
        margin: 0;
        color: var(--text-secondary);
    }
    
    .compliance-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-left: 10px;
    }
    
    .compliance-status.ok {
        background: #d4edda;
        color: #155724;
    }
    
    .quick-access-bar {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .quick-access-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--polri-blue);
    }
    
    .quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .quick-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f8f9fa;
        border-radius: 25px;
        text-decoration: none;
        color: var(--text-primary);
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
    }
    
    .quick-link:hover {
        background: var(--polri-blue);
        color: white;
        border-color: var(--polri-blue);
        transform: translateY(-2px);
    }
    
    .quick-link i {
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .flow-diagram {
            padding: 25px;
        }
        
        .flow-title {
            font-size: 1.5rem;
        }
        
        .flow-steps {
            flex-direction: column;
        }
        
        .flow-arrow {
            transform: rotate(90deg);
        }
        
        .module-grid {
            grid-template-columns: 1fr;
        }
        
        .module-actions {
            flex-direction: column;
        }
    }
</style>

<div class="nav-index-container">
    <!-- Flow Diagram -->
    <div class="flow-diagram">
        <h2 class="flow-title">
            <i class="fa-solid fa-sitemap me-3"></i>
            Personil-First Flow Architecture
        </h2>
        <p class="flow-subtitle">Alur kerja sistem sesuai regulasi POLRI</p>
        
        <div class="flow-steps">
            <div class="flow-step active" onclick="window.location.href='personil_management_v2.php'">
                <div class="flow-step-icon">1</div>
                <div class="flow-step-content">
                    <h4>Personil</h4>
                    <p>Data Master Personil</p>
                </div>
            </div>
            
            <i class="fa-solid fa-arrow-right flow-arrow"></i>
            
            <div class="flow-step" onclick="window.location.href='kepegawaian_management_v2.php'">
                <div class="flow-step-icon">2</div>
                <div class="flow-step-content">
                    <h4>Kepegawaian</h4>
                    <p>Karir & Promosi</p>
                </div>
            </div>
            
            <i class="fa-solid fa-arrow-right flow-arrow"></i>
            
            <div class="flow-step" onclick="window.location.href='penugasan_management_v2.php'">
                <div class="flow-step-icon">3</div>
                <div class="flow-step-content">
                    <h4>Penugasan</h4>
                    <p>Assignment</p>
                </div>
            </div>
            
            <i class="fa-solid fa-arrow-right flow-arrow"></i>
            
            <div class="flow-step" onclick="window.location.href='dashboard_v2.php'">
                <div class="flow-step-icon">4</div>
                <div class="flow-step-content">
                    <h4>Monitoring</h4>
                    <p>Compliance & Report</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Access -->
    <div class="quick-access-bar">
        <h3 class="quick-access-title">
            <i class="fa-solid fa-bolt me-2"></i>
            Akses Cepat
        </h3>
        <div class="quick-links">
            <a href="personil_management_v2.php" class="quick-link">
                <i class="fa-solid fa-user-plus"></i>
                Tambah Personil
            </a>
            <a href="kepegawaian_management_v2.php" class="quick-link">
                <i class="fa-solid fa-arrow-up"></i>
                Kenaikan Pangkat
            </a>
            <a href="penugasan_management_v2.php" class="quick-link">
                <i class="fa-solid fa-user-clock"></i>
                Assign PS
            </a>
            <a href="dashboard_v2.php" class="quick-link">
                <i class="fa-solid fa-chart-pie"></i>
                Dashboard
            </a>
            <a href="#" class="quick-link" onclick="showHelp()">
                <i class="fa-solid fa-circle-question"></i>
                Bantuan
            </a>
        </div>
    </div>
    
    <!-- Module Grid -->
    <div class="module-grid">
        <!-- Personil Module -->
        <div class="module-card">
            <div class="module-header personil">
                <div class="module-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="module-title">Manajemen Personil</h3>
                <p class="module-desc">Data master & informasi personil</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> Data lengkap 256 personil</li>
                    <li><i class="fa-solid fa-check"></i> Validasi NRP 8 digit</li>
                    <li><i class="fa-solid fa-check"></i> Riwayat karir terintegrasi</li>
                    <li><i class="fa-solid fa-check"></i> Filter & pencarian advanced</li>
                </ul>
                <div class="module-actions">
                    <a href="personil_management_v2.php" class="btn-module btn-module-primary">
                        <i class="fa-solid fa-arrow-right me-2"></i>Buka Modul
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('personil')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Kepegawaian Module -->
        <div class="module-card">
            <div class="module-header kepegawaian">
                <div class="module-icon">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h3 class="module-title">Kepegawaian</h3>
                <p class="module-desc">Kenaikan pangkat & mutasi jabatan</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> Eligibility checking otomatis</li>
                    <li><i class="fa-solid fa-check"></i> Kenaikan pangkat reguler/luar biasa</li>
                    <li><i class="fa-solid fa-check"></i> Mutasi dengan validasi eselon</li>
                    <li><i class="fa-solid fa-check"></i> Tracking jenjang karir</li>
                </ul>
                <div class="module-actions">
                    <a href="kepegawaian_management_v2.php" class="btn-module btn-module-primary" style="background: linear-gradient(135deg, #198754 0%, #28a745 100%);">
                        <i class="fa-solid fa-arrow-right me-2"></i>Buka Modul
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('kepegawaian')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Penugasan Module -->
        <div class="module-card">
            <div class="module-header penugasan">
                <div class="module-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h3 class="module-title">Penugasan</h3>
                <p class="module-desc">Assignment & compliance monitoring</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> Definitif, PS, Plt, Pjs, Plh, Pj</li>
                    <li><i class="fa-solid fa-check"></i> Monitoring PS percentage ≤15%</li>
                    <li><i class="fa-solid fa-check"></i> Expiration alerts</li>
                    <li><i class="fa-solid fa-check"></i> Extend & end penugasan</li>
                </ul>
                <div class="module-actions">
                    <a href="penugasan_management_v2.php" class="btn-module btn-module-primary" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                        <i class="fa-solid fa-arrow-right me-2"></i>Buka Modul
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('penugasan')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Struktur Module -->
        <div class="module-card">
            <div class="module-header struktur">
                <div class="module-icon">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <h3 class="module-title">Struktur Organisasi</h3>
                <p class="module-desc">Hierarki POLRES per PERKAP 23/2010</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> 6 Unsur POLRI</li>
                    <li><i class="fa-solid fa-check"></i> Satuan Fungsi & Unit Pendukung</li>
                    <li><i class="fa-solid fa-check"></i> Eselon I, II, III, IV</li>
                    <li><i class="fa-solid fa-check"></i> Polsek Kewilayahan</li>
                </ul>
                <div class="module-actions">
                    <a href="bagian.php" class="btn-module btn-module-primary" style="background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);">
                        <i class="fa-solid fa-arrow-right me-2"></i>Lihat Struktur
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('struktur')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Compliance Module -->
        <div class="module-card">
            <div class="module-header compliance">
                <div class="module-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="module-title">Compliance Monitoring</h3>
                <p class="module-desc">Validasi regulasi & pelaporan</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> PS percentage monitoring</li>
                    <li><i class="fa-solid fa-check"></i> Validasi eselon vs pangkat</li>
                    <li><i class="fa-solid fa-check"></i> Compliance reports</li>
                    <li><i class="fa-solid fa-check"></i> Audit trail ready</li>
                </ul>
                <div class="module-actions">
                    <a href="dashboard_v2.php" class="btn-module btn-module-primary" style="background: linear-gradient(135deg, #dc3545 0%, #f44 100%);">
                        <i class="fa-solid fa-arrow-right me-2"></i>Monitor
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('compliance')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Module -->
        <div class="module-card">
            <div class="module-header personil">
                <div class="module-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <h3 class="module-title">Dashboard & Analytics</h3>
                <p class="module-desc">Executive view & reporting</p>
            </div>
            <div class="module-body">
                <ul class="module-features">
                    <li><i class="fa-solid fa-check"></i> Real-time statistics</li>
                    <li><i class="fa-solid fa-check"></i> Interactive charts</li>
                    <li><i class="fa-solid fa-check"></i> Export PDF/Excel</li>
                    <li><i class="fa-solid fa-check"></i> KPI monitoring</li>
                </ul>
                <div class="module-actions">
                    <a href="dashboard_v2.php" class="btn-module btn-module-primary">
                        <i class="fa-solid fa-arrow-right me-2"></i>Buka Dashboard
                    </a>
                    <button class="btn-module btn-module-secondary" onclick="showHelp('dashboard')">
                        <i class="fa-solid fa-info-circle me-2"></i>Info
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Regulation Info -->
    <div class="regulation-bar">
        <h3 class="regulation-title">
            <i class="fa-solid fa-balance-scale me-2"></i>
            Dasar Regulasi
            <span class="compliance-status ok">
                <i class="fa-solid fa-check-circle"></i> 100% Compliant
            </span>
        </h3>
        <div class="regulation-grid">
            <div class="regulation-item">
                <h5>PERKAP No. 23/2010</h5>
                <p>Pembentukan dan Susunan Organisasi Kepolisian Republik Indonesia - Struktur Unsur POLRI</p>
            </div>
            <div class="regulation-item">
                <h5>Perpol No. 3/2024</h5>
                <p>Perubahan atas Perpol No. 7/2020 tentang Organisasi dan Tata Kerja Kepolisian - Satuan Fungsi & Unit Pendukung</p>
            </div>
            <div class="regulation-item">
                <h5>PP No. 100/2000</h5>
                <p>Jabatan Pejabat Pemerintah Sipil dan Gaji Pokoknya - Eselon & Pangkat Requirements</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Help function for modules
    function showHelp(module) {
        const helpTexts = {
            'personil': 'Manajemen Personil:\n\nModul utama untuk mengelola data personil POLRES Samosir.\n\nFitur:\n- CRUD personil dengan validasi NRP 8 digit\n- Filter berdasarkan unsur, bagian, pangkat\n- Riwayat karir lengkap\n- Import/export data',
            'kepegawaian': 'Kepegawaian:\n\nMengelola karir dan promosi personil.\n\nFitur:\n- Kenaikan pangkat (reguler/luar biasa)\n- Mutasi jabatan dengan validasi eselon\n- Eligibility checking otomatis\n- Tracking riwayat karir',
            'penugasan': 'Penugasan:\n\nMengelola assignment personil ke jabatan.\n\nJenis Penugasan:\n- Definitif: Penugasan tetap\n- PS: Pejabat Sementara (max 15%)\n- Plt: Pelaksana Tugas\n- Pjs: Pejabat Sementara\n- Plh: Pelaksana Harian\n- Pj: Penjabat\n\nFitur monitoring compliance PS percentage.',
            'struktur': 'Struktur Organisasi:\n\nHierarki POLRES Samosir per PERKAP 23/2010:\n\n1. Unsur Pimpinan (Eselon I-II)\n2. Unsur Pembantu Pimpinan (Eselon III)\n3. Unsur Pelaksana Tugas Pokok (Satuan Fungsi)\n4. Unsur Pelaksana Kewilayahan (Polsek)\n5. Unsur Pendukung (Unit Pendukung)\n6. Unsur Lainnya',
            'compliance': 'Compliance Monitoring:\n\nMemastikan kepatuhan terhadap regulasi:\n\n- PS Percentage ≤ 15%\n- Validasi eselon vs pangkat\n- Monitoring penugasan expired\n- Compliance reports\n- Audit trail',
            'dashboard': 'Dashboard & Analytics:\n\nExecutive view sistem personil:\n\n- Real-time statistics\n- Interactive charts (Chart.js)\n- Key Performance Indicators\n- Quick actions\n- Export PDF/Excel'
        };
        
        alert(helpTexts[module] || 'Pilih modul untuk melihat informasi lebih detail.');
    }
</script>

<?php
include '../includes/components/nav_footer_v2.php';
?>
