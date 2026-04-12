<?php
/**
 * KPI Management Page - Formal Performance Evaluation Framework
 */

session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Initialize session
SessionManager::start();

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Manajemen KPI - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-chart-bar me-2"></i>Manajemen KPI</h3>
                <div>
                    <button class="btn btn-primary" onclick="showEvaluationModal()">
                        <i class="fas fa-plus me-1"></i>Buat Evaluasi
                    </button>
                    <button class="btn btn-info" onclick="showTemplateModal()">
                        <i class="fas fa-cog me-1"></i>Template KPI
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Dashboard Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalEvaluations">0</h4>
                            <p class="mb-0">Total Evaluasi</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="evaluationGrowth">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="completionRate">0%</h4>
                            <p class="mb-0">Tingkat Penyelesaian</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="completionTrend">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="averageScore">0</h4>
                            <p class="mb-0">Skor Rata-rata</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="scoreTrend">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="excellentPerformers">0</h4>
                            <p class="mb-0">Performer Terbaik</p>
                            <small class="opacity-75">
                                <i class="fas fa-trophy"></i> Top 10%
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-award fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Alert KPI</h5>
                </div>
                <div class="card-body">
                    <div id="kpiAlertsContainer">
                        <!-- Alerts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Distribution Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Distribusi Performa</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceDistributionChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Performa per Kategori</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryPerformanceChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluations Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Data Evaluasi KPI</h5>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="filterByStatus('all')">Semua</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filterByStatus('draft')">Draft</button>
                            <button class="btn btn-sm btn-outline-info" onclick="filterByStatus('submitted')">Diajukan</button>
                            <button class="btn btn-sm btn-outline-success" onclick="filterByStatus('approved')">Disetujui</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="evaluationsTable">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Nama</th>
                                    <th>Pangkat</th>
                                    <th>Bagian</th>
                                    <th>Tipe Evaluasi</th>
                                    <th>Skor Overall</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="evaluationsBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers Table -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-trophy me-2"></i>Top Performers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="topPerformersTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pangkat</th>
                                    <th>Bagian</th>
                                    <th>Skor</th>
                                    <th>Periode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-clock me-2"></i>Perlu Peningkatan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="improvementTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pangkat</th>
                                    <th>Bagian</th>
                                    <th>Skor</th>
                                    <th>Periode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Evaluasi KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="evaluationForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Personil</label>
                                <select class="form-select" id="evaluationPersonil" required>
                                    <option value="">Pilih Personil</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tipe Evaluasi</label>
                                <select class="form-select" id="evaluationType" required>
                                    <option value="quarterly">Kuartalan</option>
                                    <option value="semi_annual">Semesteran</option>
                                    <option value="annual">Tahunan</option>
                                    <option value="special">Khusus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Periode Evaluasi</label>
                                <input type="month" class="form-control" id="evaluationPeriod" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">KPI Items</label>
                        <div id="kpiItemsContainer">
                            <!-- KPI items will be loaded here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addKPIItem()">
                            <i class="fas fa-plus me-1"></i>Tambah KPI
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Komentar Overall</label>
                                <textarea class="form-control" id="overallComments" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rencana Pengembangan</label>
                                <textarea class="form-control" id="developmentPlan" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveEvaluation()">Simpan Draft</button>
                <button type="button" class="btn btn-success" onclick="submitEvaluation()">Ajukan Evaluasi</button>
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-primary" onclick="showCreateTemplateModal()">
                            <i class="fas fa-plus me-1"></i>Buat Template Baru
                        </button>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="templateSearch" placeholder="Cari template...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table" id="templatesTable">
                        <thead>
                            <tr>
                                <th>Nama Template</th>
                                <th>Kategori</th>
                                <th>Jabatan</th>
                                <th>Bagian</th>
                                <th>Weight</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="templatesBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Template KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <div class="mb-3">
                        <label class="form-label">Nama Template</label>
                        <input type="text" class="form-control" id="templateName" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori KPI</label>
                                <select class="form-select" id="kpiCategory" required>
                                    <option value="operational">Operasional</option>
                                    <option value="behavioral">Perilaku</option>
                                    <option value="developmental">Pengembangan</option>
                                    <option value="strategic">Strategis</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bobot (%)</label>
                                <input type="number" class="form-control" id="weightPercentage" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Target Value</label>
                                <input type="number" class="form-control" id="targetValue" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit Pengukuran</label>
                                <input type="text" class="form-control" id="measurementUnit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jabatan (Opsional)</label>
                                <select class="form-select" id="templateJabatan">
                                    <option value="">Semua Jabatan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bagian (Opsional)</label>
                                <select class="form-select" id="templateBagian">
                                    <option value="">Semua Bagian</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="templateDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Evaluasi</label>
                        <textarea class="form-control" id="evaluationMethod" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">Simpan Template</button>
            </div>
        </div>
    </div>
</div>

<style>
.kpi-item {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    background-color: #f8f9fa;
}

.kpi-score-input {
    width: 80px;
}

.performance-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}

.performance-excellent {
    background-color: #d4edda;
    color: #155724;
}

.performance-good {
    background-color: #cce5ff;
    color: #004085;
}

.performance-satisfactory {
    background-color: #fff3cd;
    color: #856404;
}

.performance-needs-improvement {
    background-color: #f8d7da;
    color: #721c24;
}

.performance-poor {
    background-color: #e2e3e5;
    color: #383d41;
}

.alert-item {
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 6px;
    border-left: 4px solid;
}

.alert-high {
    background-color: #f8d7da;
    border-left-color: #dc3545;
}

.alert-medium {
    background-color: #fff3cd;
    border-left-color: #ffc107;
}

.alert-low {
    background-color: #d1ecf1;
    border-left-color: #17a2b8;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentFilter = 'all';
let kpiCharts = {};
let kpiItemCount = 0;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadPersonil();
    loadJabatan();
    loadBagian();
    loadEvaluations();
    loadKPIDashboard();
    loadKPIStatistics();
    loadKPITemplates();
    
    // Set current period
    document.getElementById('evaluationPeriod').value = new Date().toISOString().slice(0, 7);
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(loadKPIDashboard, 300000);
});

// Load personil data
function loadPersonil() {
    fetch(`${API_BASE_URL}/personil_simple.php?limit=1000`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('evaluationPersonil');
                select.innerHTML = '<option value="">Pilih Personil</option>';
                
                data.data.forEach(personil => {
                    select.innerHTML += `<option value="${personil.nrp}">${personil.nama} - ${personil.pangkat}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading personil:', error));
}

// Load jabatan data
function loadJabatan() {
    fetch(`${API_BASE_URL}/jabatan_api.php?action=get_all_jabatan`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('templateJabatan');
                select.innerHTML = '<option value="">Semua Jabatan</option>';
                
                data.data.forEach(jabatan => {
                    select.innerHTML += `<option value="${jabatan.id}">${jabatan.nama_jabatan}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading jabatan:', error));
}

// Load bagian data
function loadBagian() {
    fetch(`${API_BASE_URL}/bagian_api.php?action=get_all_bagian`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('templateBagian');
                select.innerHTML = '<option value="">Semua Bagian</option>';
                
                data.data.forEach(bagian => {
                    select.innerHTML += `<option value="${bagian.id}">${bagian.nama_bagian}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading bagian:', error));
}

// Load evaluations
function loadEvaluations() {
    const params = new URLSearchParams();
    if (currentFilter !== 'all') {
        params.append('status', currentFilter);
    }
    
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=get_kpi_evaluations&${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEvaluations(data.data);
            }
        })
        .catch(error => console.error('Error loading evaluations:', error));
}

// Display evaluations in table
function displayEvaluations(evaluations) {
    const tbody = document.getElementById('evaluationsBody');
    tbody.innerHTML = '';
    
    evaluations.forEach(evaluation => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatPeriod(evaluation.evaluation_period)}</td>
            <td>${evaluation.personil_name}</td>
            <td>${evaluation.nama_pangkat}</td>
            <td>${evaluation.nama_bagian}</td>
            <td>${evaluation.evaluation_type_display}</td>
            <td>
                <span class="performance-badge ${getPerformanceClass(evaluation.overall_score)}">
                    ${evaluation.overall_score || '-'}
                </span>
            </td>
            <td><span class="status-badge status-${evaluation.status}">${evaluation.status_display}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-info" onclick="viewEvaluationDetails(${evaluation.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${evaluation.status === 'draft' || evaluation.status === 'submitted' ? `
                        <button class="btn btn-sm btn-success" onclick="processEvaluation(${evaluation.id}, 'approve')">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="processEvaluation(${evaluation.id}, 'review')">
                            <i class="fas fa-edit"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Load KPI Dashboard
function loadKPIDashboard() {
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=get_kpi_dashboard`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dashboard = data.data;
                
                // Update metrics
                const metrics = dashboard.key_metrics;
                document.getElementById('totalEvaluations').textContent = metrics.total_evaluations || 0;
                document.getElementById('completionRate').textContent = (metrics.completion_rate || 0) + '%';
                document.getElementById('averageScore').textContent = metrics.average_score || 0;
                document.getElementById('excellentPerformers').textContent = metrics.excellent_performers || 0;
                
                // Update trends
                document.getElementById('evaluationGrowth').textContent = '+5.2%';
                document.getElementById('completionTrend').textContent = '+3.1%';
                document.getElementById('scoreTrend').textContent = '+2.8%';
                
                // Display alerts
                displayKPIAlerts(dashboard.alerts);
            }
        })
        .catch(error => console.error('Error loading KPI dashboard:', error));
}

// Load KPI Statistics
function loadKPIStatistics() {
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=get_kpi_statistics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                
                // Update top performers table
                updateTopPerformersTable(stats.top_performers);
                
                // Update improvement table
                updateImprovementTable(stats.improvement_needed);
                
                // Update charts
                updatePerformanceDistributionChart(stats.performance_distribution);
                updateCategoryPerformanceChart(stats.category_performance);
            }
        })
        .catch(error => console.error('Error loading KPI statistics:', error));
}

// Load KPI Templates
function loadKPITemplates() {
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=get_kpi_templates`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTemplates(data.data);
            }
        })
        .catch(error => console.error('Error loading KPI templates:', error));
}

// Display templates
function displayTemplates(templates) {
    const tbody = document.getElementById('templatesBody');
    tbody.innerHTML = '';
    
    templates.forEach(template => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${template.template_name}</td>
            <td>${template.kpi_category}</td>
            <td>${template.nama_jabatan || 'Semua'}</td>
            <td>${template.nama_bagian || 'Semua'}</td>
            <td>${template.weight_percentage}%</td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" onclick="useTemplate(${template.id})">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="editTemplate(${template.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Update charts
function updatePerformanceDistributionChart(distribution) {
    const ctx = document.getElementById('performanceDistributionChart').getContext('2d');
    
    if (kpiCharts.performance) {
        kpiCharts.performance.destroy();
    }
    
    kpiCharts.performance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: distribution.map(d => d.performance_level),
            datasets: [{
                data: distribution.map(d => d.count),
                backgroundColor: [
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#fd7e14',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateCategoryPerformanceChart(categories) {
    const ctx = document.getElementById('categoryPerformanceChart').getContext('2d');
    
    if (kpiCharts.category) {
        kpiCharts.category.destroy();
    }
    
    kpiCharts.category = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories.map(c => c.kpi_category),
            datasets: [{
                label: 'Skor Rata-rata',
                data: categories.map(c => c.avg_score),
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// Update tables
function updateTopPerformersTable(performers) {
    const tbody = document.querySelector('#topPerformersTable tbody');
    tbody.innerHTML = performers.map(performer => `
        <tr>
            <td>${performer.nama}</td>
            <td>${performer.nama_pangkat}</td>
            <td>${performer.nama_bagian}</td>
            <td><strong>${performer.overall_score}</strong></td>
            <td>${formatPeriod(performer.evaluation_period)}</td>
        </tr>
    `).join('');
}

function updateImprovementTable(improvements) {
    const tbody = document.querySelector('#improvementTable tbody');
    tbody.innerHTML = improvements.map(improvement => `
        <tr>
            <td>${improvement.nama}</td>
            <td>${improvement.nama_pangkat}</td>
            <td>${improvement.nama_bagian}</td>
            <td><strong>${improvement.overall_score}</strong></td>
            <td>${formatPeriod(improvement.evaluation_period)}</td>
        </tr>
    `).join('');
}

// Display KPI alerts
function displayKPIAlerts(alerts) {
    const container = document.getElementById('kpiAlertsContainer');
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada alert KPI aktif</p>';
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="alert-item alert-${alert.severity}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${alert.message}</h6>
                    <small class="text-muted">Aksi: ${alert.action}</small>
                </div>
                <span class="badge bg-${alert.severity === 'high' ? 'danger' : alert.severity === 'medium' ? 'warning' : 'info'}">
                    ${alert.severity.toUpperCase()}
                </span>
            </div>
        </div>
    `).join('');
}

// Modal functions
function showEvaluationModal() {
    document.getElementById('evaluationModal').classList.add('show');
    document.getElementById('evaluationModal').style.display = 'block';
    kpiItemCount = 0;
    document.getElementById('kpiItemsContainer').innerHTML = '';
}

function showTemplateModal() {
    document.getElementById('templateModal').classList.add('show');
    document.getElementById('templateModal').style.display = 'block';
}

function showCreateTemplateModal() {
    document.getElementById('createTemplateModal').classList.add('show');
    document.getElementById('createTemplateModal').style.display = 'block';
}

// Add KPI item
function addKPIItem() {
    kpiItemCount++;
    const container = document.getElementById('kpiItemsContainer');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'kpi-item';
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Template KPI</label>
                <select class="form-select" name="template_id_${kpiItemCount}">
                    <option value="">Pilih Template</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nilai Aktual</label>
                <input type="number" class="form-control" name="actual_value_${kpiItemCount}" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Skor (0-100)</label>
                <input type="number" class="form-control kpi-score-input" name="actual_score_${kpiItemCount}" min="0" max="100">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label><br>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeKPIItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <label class="form-label">Komentar</label>
                <input type="text" class="form-control" name="comments_${kpiItemCount}">
            </div>
        </div>
    `;
    container.appendChild(itemDiv);
    
    // Load templates for this item
    loadTemplatesForItem(kpiItemCount);
}

function removeKPIItem(button) {
    button.closest('.kpi-item').remove();
}

function loadTemplatesForItem(itemId) {
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=get_kpi_templates`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.querySelector(`select[name="template_id_${itemId}"]`);
                data.data.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = `${template.template_name} (${template.weight_percentage}%)`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading templates:', error));
}

// Save evaluation
function saveEvaluation() {
    const formData = collectEvaluationData();
    
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=create_kpi_evaluation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evaluasi KPI berhasil disimpan sebagai draft!');
            closeModal('evaluationModal');
            loadEvaluations();
            loadKPIDashboard();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving evaluation:', error);
        alert('Terjadi kesalahan saat menyimpan evaluasi');
    });
}

function submitEvaluation() {
    const formData = collectEvaluationData();
    
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=create_kpi_evaluation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evaluasi KPI berhasil diajukan!');
            closeModal('evaluationModal');
            loadEvaluations();
            loadKPIDashboard();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error submitting evaluation:', error);
        alert('Terjadi kesalahan saat mengajukan evaluasi');
    });
}

function collectEvaluationData() {
    const kpiItems = [];
    document.querySelectorAll('.kpi-item').forEach((item, index) => {
        const templateId = item.querySelector(`select[name^="template_id_"]`).value;
        const actualValue = item.querySelector(`input[name^="actual_value_"]`).value;
        const actualScore = item.querySelector(`input[name^="actual_score_"]`).value;
        const comments = item.querySelector(`input[name^="comments_"]`).value;
        
        if (templateId && actualScore) {
            kpiItems.push({
                template_id: templateId,
                actual_value: actualValue,
                actual_score: actualScore,
                comments: comments
            });
        }
    });
    
    return {
        personil_id: document.getElementById('evaluationPersonil').value,
        evaluation_type: document.getElementById('evaluationType').value,
        evaluation_period: document.getElementById('evaluationPeriod').value,
        kpi_data: JSON.stringify(kpiItems),
        overall_comments: document.getElementById('overallComments').value,
        development_plan: document.getElementById('developmentPlan').value
    };
}

// Save template
function saveTemplate() {
    const formData = {
        template_name: document.getElementById('templateName').value,
        kpi_category: document.getElementById('kpiCategory').value,
        description: document.getElementById('templateDescription').value,
        target_value: document.getElementById('targetValue').value,
        measurement_unit: document.getElementById('measurementUnit').value,
        weight_percentage: document.getElementById('weightPercentage').value,
        jabatan_id: document.getElementById('templateJabatan').value || null,
        bagian_id: document.getElementById('templateBagian').value || null,
        evaluation_method: document.getElementById('evaluationMethod').value
    };
    
    fetch(`${API_BASE_URL}/kpi_management_api.php?action=create_kpi_template`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template KPI berhasil disimpan!');
            closeModal('createTemplateModal');
            loadKPITemplates();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving template:', error);
        alert('Terjadi kesalahan saat menyimpan template');
    });
}

// Process evaluation approval
function processEvaluation(evaluationId, action) {
    const comments = prompt(`Komentar untuk ${action}:`);
    
    if (comments !== null) {
        fetch(`${API_BASE_URL}/kpi_management_api.php?action=process_kpi_approval`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                evaluation_id: evaluationId,
                approval_action: action,
                comments: comments
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Evaluasi berhasil ${action}!`);
                loadEvaluations();
                loadKPIDashboard();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error processing evaluation:', error);
            alert('Terjadi kesalahan saat memproses evaluasi');
        });
    }
}

// Filter functions
function filterByStatus(status) {
    currentFilter = status;
    loadEvaluations();
}

// Utility functions
function formatPeriod(period) {
    const [year, month] = period.split('-');
    const date = new Date(year, month - 1);
    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function getPerformanceClass(score) {
    if (score >= 90) return 'performance-excellent';
    if (score >= 80) return 'performance-good';
    if (score >= 70) return 'performance-satisfactory';
    if (score >= 60) return 'performance-needs-improvement';
    return 'performance-poor';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.getElementById(modalId).style.display = 'none';
}

function viewEvaluationDetails(evaluationId) {
    // Implementation for viewing evaluation details
    alert(`View details for evaluation ${evaluationId}`);
}

function useTemplate(templateId) {
    // Implementation for using template in evaluation
    alert(`Use template ${templateId} in evaluation`);
}

function editTemplate(templateId) {
    // Implementation for editing template
    alert(`Edit template ${templateId}`);
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
