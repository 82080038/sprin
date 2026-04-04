<?php
declare(strict_types=1);
/**
 * Dashboard and Reporting Page v2.0
 * Executive Dashboard for SPRIN v2.0
 */

require_once '../core/config.php';
require_once '../includes/components/header.php';
require_once '../includes/components/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SPRIN v2.0</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
    <link href="../public/assets/css/responsive.css" rel="stylesheet">
    
    <style>
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .compliance-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .compliance-badge.compliant {
            background: #d4edda;
            color: #155724;
        }
        .compliance-badge.non-compliant {
            background: #f8d7da;
            color: #721c24;
        }
        .quick-link {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        .quick-link:hover {
            transform: translateX(5px);
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        .activity-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 0.5rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/components/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard Executive
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>
                
                <!-- Key Metrics Row -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Personil</h6>
                                    <h3 class="mb-0" id="metricTotalPersonil">0</h3>
                                    <small class="text-success">
                                        <i class="fas fa-arrow-up"></i> Aktif
                                    </small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #1a237e, #3949ab);">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Jabatan Terisi</h6>
                                    <h3 class="mb-0" id="metricJabatanTerisi">0</h3>
                                    <small class="text-muted">dari <span id="metricTotalJabatan">0</span> jabatan</small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #198754, #28a745);">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Penugasan Aktif</h6>
                                    <h3 class="mb-0" id="metricPenugasanAktif">0</h3>
                                    <small class="text-warning">Sementara</small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Compliance</h6>
                                    <h3 class="mb-0" id="metricCompliance">0%</h3>
                                    <div id="complianceStatus"></div>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #dc3545, #f44);">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-pie me-2"></i>
                                Distribusi Personil per Unsur
                            </h5>
                            <div class="chart-container">
                                <canvas id="unsurChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-bar me-2"></i>
                                Distribusi per Pangkat
                            </h5>
                            <div class="chart-container">
                                <canvas id="pangkatChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Compliance and Alerts Row -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Status Compliance
                            </h5>
                            <div id="complianceDetails">
                                <!-- Compliance details will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-bell me-2"></i>
                                Peringatan & Notifikasi
                            </h5>
                            <div id="alertsContainer">
                                <!-- Alerts will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links and Recent Activity -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-link me-2"></i>
                                Quick Links
                            </h5>
                            <div class="list-group list-group-flush">
                                <a href="personil_management_v2.php" class="list-group-item list-group-item-action quick-link">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    Personil Management
                                    <i class="fas fa-chevron-right float-end mt-1"></i>
                                </a>
                                <a href="kepegawaian_management_v2.php" class="list-group-item list-group-item-action quick-link">
                                    <i class="fas fa-user-tie me-2 text-success"></i>
                                    Kepegawaian
                                    <i class="fas fa-chevron-right float-end mt-1"></i>
                                </a>
                                <a href="penugasan_management_v2.php" class="list-group-item list-group-item-action quick-link">
                                    <i class="fas fa-briefcase me-2 text-warning"></i>
                                    Penugasan
                                    <i class="fas fa-chevron-right float-end mt-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <div class="card">
                            <h5 class="mb-3">
                                <i class="fas fa-history me-2"></i>
                                Aktivitas Terbaru
                            </h5>
                            <div class="recent-activity" id="recentActivity">
                                <!-- Activity will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let unsurChart, pangkatChart;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });
        
        function refreshDashboard() {
            loadDashboardData();
        }
        
        function loadDashboardData() {
            loadPersonilStatistics();
            loadKepegawaianStatistics();
            loadPenugasanStatistics();
            loadComplianceStatus();
        }
        
        function loadPersonilStatistics() {
            const formData = new FormData();
            formData.append('action', 'get_personil_statistics');
            
            fetch('../api/personil_management_v2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('metricTotalPersonil').textContent = data.data.total.aktif || 0;
                    
                    // Render Unsur Chart
                    renderUnsurChart(data.data.by_unsur);
                    
                    // Render Pangkat Chart
                    renderPangkatChart(data.data.by_pangkat);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function loadKepegawaianStatistics() {
            const formData = new FormData();
            formData.append('action', 'get_kepegawaian_statistics');
            
            fetch('../api/kepegawaian_management_v2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const jabatanKosong = data.data.jabatan_kosong.empty_positions || 0;
                    const jabatanTerisi = 98 - jabatanKosong; // Total 98 jabatan
                    document.getElementById('metricJabatanTerisi').textContent = jabatanTerisi;
                    document.getElementById('metricTotalJabatan').textContent = 98;
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function loadPenugasanStatistics() {
            const formData = new FormData();
            formData.append('action', 'get_penugasan_statistics');
            
            fetch('../api/penugasan_management_v2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const total = data.data.by_type.reduce((sum, item) => sum + parseInt(item.count), 0);
                    document.getElementById('metricPenugasanAktif').textContent = total;
                    
                    // Calculate overall compliance
                    const psPercentage = parseFloat(data.data.ps_compliance.percentage);
                    const compliance = psPercentage <= 15 ? 100 : Math.max(0, 100 - ((psPercentage - 15) * 2));
                    document.getElementById('metricCompliance').textContent = compliance.toFixed(1) + '%';
                    
                    const statusDiv = document.getElementById('complianceStatus');
                    if (compliance >= 90) {
                        statusDiv.innerHTML = '<span class="badge bg-success">Excellent</span>';
                    } else if (compliance >= 70) {
                        statusDiv.innerHTML = '<span class="badge bg-warning">Good</span>';
                    } else {
                        statusDiv.innerHTML = '<span class="badge bg-danger">Needs Attention</span>';
                    }
                    
                    // Render compliance details
                    renderComplianceDetails(data.data);
                    
                    // Render alerts
                    renderAlerts(data.data);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function loadComplianceStatus() {
            // This would load detailed compliance status
            // For now, we'll use mock data
            renderRecentActivity();
        }
        
        function renderUnsurChart(unsurData) {
            const ctx = document.getElementById('unsurChart').getContext('2d');
            
            if (unsurChart) unsurChart.destroy();
            
            const labels = unsurData.map(item => item.nama_unsur.replace('UNSUR ', ''));
            const data = unsurData.map(item => item.count);
            const colors = ['#1a237e', '#3949ab', '#1976d2', '#2196f3', '#64b5f6', '#90caf9'];
            
            unsurChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }
        
        function renderPangkatChart(pangkatData) {
            const ctx = document.getElementById('pangkatChart').getContext('2d');
            
            if (pangkatChart) pangkatChart.destroy();
            
            const labels = pangkatData.map(item => item.nama_pangkat);
            const data = pangkatData.map(item => item.count);
            
            pangkatChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Personil',
                        data: data,
                        backgroundColor: '#3949ab',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        function renderComplianceDetails(data) {
            const container = document.getElementById('complianceDetails');
            
            const psPercentage = parseFloat(data.ps_compliance.percentage);
            const isCompliant = psPercentage <= 15;
            
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>PS Percentage</span>
                    <span class="compliance-badge ${isCompliant ? 'compliant' : 'non-compliant'}">
                        ${psPercentage}% ${isCompliant ? '✓' : '✗'}
                    </span>
                </div>
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar ${isCompliant ? 'bg-success' : 'bg-danger'}" 
                         role="progressbar" 
                         style="width: ${Math.min(psPercentage / 15 * 100, 100)}%">
                    </div>
                </div>
                <small class="text-muted">Maximum allowed: 15%</small>
            `;
        }
        
        function renderAlerts(data) {
            const container = document.getElementById('alertsContainer');
            let html = '';
            
            const expiring = parseInt(data.expiring_soon.expiring_soon);
            const expired = parseInt(data.expired.expired);
            
            if (expired > 0) {
                html += `
                    <div class="alert alert-danger mb-2">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>${expired}</strong> penugasan sudah expired dan perlu ditindaklanjuti
                    </div>
                `;
            }
            
            if (expiring > 0) {
                html += `
                    <div class="alert alert-warning mb-2">
                        <i class="fas fa-clock me-2"></i>
                        <strong>${expiring}</strong> penugasan akan berakhir dalam 7 hari
                    </div>
                `;
            }
            
            const psPercentage = parseFloat(data.ps_compliance.percentage);
            if (psPercentage > 15) {
                html += `
                    <div class="alert alert-danger mb-2">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Compliance Warning!</strong> PS percentage (${psPercentage}%) melebihi batas 15%
                    </div>
                `;
            }
            
            if (html === '') {
                html = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Tidak ada peringatan saat ini</div>';
            }
            
            container.innerHTML = html;
        }
        
        function renderRecentActivity() {
            const container = document.getElementById('recentActivity');
            // This would load from API - using mock data for now
            container.innerHTML = `
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <strong>Kenaikan Pangkat</strong>
                        <small class="text-muted">2 jam yang lalu</small>
                    </div>
                    <p class="mb-0 text-muted">AKP Bambang Susanto naik pangkat menjadi KOMPOL</p>
                </div>
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <strong>Mutasi Jabatan</strong>
                        <small class="text-muted">5 jam yang lalu</small>
                    </div>
                    <p class="mb-0 text-muted">IPTU Siti Aminah mutasi ke SAT INTELKAM</p>
                </div>
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <strong>Penugasan Baru</strong>
                        <small class="text-muted">1 hari yang lalu</small>
                    </div>
                    <p class="mb-0 text-muted">Plt KABAG OPS diassign ke AKP John Doe</p>
                </div>
            `;
        }
    </script>
</body>
</html>
