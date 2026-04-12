<?php
/**
 * Advanced Analytics Dashboard - Enhanced UI with Charts and Graphs
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

$page_title = 'Analytics Dashboard - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';
?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-chart-line me-2"></i>Analytics Dashboard</h3>
                <div>
                    <select class="form-select d-inline-block w-auto" id="periodFilter">
                        <option value="7">7 Hari</option>
                        <option value="30" selected>30 Hari</option>
                        <option value="90">90 Hari</option>
                        <option value="365">1 Tahun</option>
                    </select>
                    <button class="btn btn-primary ms-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalPersonnel">0</h4>
                            <p class="mb-0">Total Personil</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="personnelGrowth">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h4 class="mb-0" id="attendanceRate">0%</h4>
                            <p class="mb-0">Tingkat Kehadiran</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="attendanceTrend">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
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
                            <h4 class="mb-0" id="fatigueIndex">0</h4>
                            <p class="mb-0">Indeks Kelelahan</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-down"></i> <span id="fatigueTrend">-0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-battery-half fa-2x"></i>
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
                            <h4 class="mb-0" id="complianceRate">0%</h4>
                            <p class="mb-0">Tingkat Kepatuhan</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="complianceTrend">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Trend Kehadiran (6 Bulan)</h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Distribusi Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Analisis Beban Kerja</h5>
                </div>
                <div class="card-body">
                    <canvas id="workloadChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area me-2"></i>Tingkat Kelelahan per Bagian</h5>
                </div>
                <div class="card-body">
                    <canvas id="fatigueByBagianChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Predictive Analytics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-brain me-2"></i>Prediksi Kebutuhan Personil (7 Hari ke Depan)</h5>
                </div>
                <div class="card-body">
                    <canvas id="predictionChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fairness Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-balance-scale me-2"></i>Analisis Keadilan Distribusi Piket</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="fairnessRadarChart" height="150"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div id="fairnessMetrics">
                                <h6>Metrik Keadilan</h6>
                                <div class="mb-2">
                                    <small>Rata-rata Jam Piket:</small>
                                    <strong id="avgHours">0</strong>
                                </div>
                                <div class="mb-2">
                                    <small>Standar Deviasi:</small>
                                    <strong id="stdDev">0</strong>
                                </div>
                                <div class="mb-2">
                                    <small>Skor Keadilan:</small>
                                    <strong id="fairnessScore">0%</strong>
                                </div>
                                <div class="progress mb-2">
                                    <div class="progress-bar" id="fairnessProgressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Alert dan Peringatan</h5>
                </div>
                <div class="card-body">
                    <div id="alertsContainer">
                        <!-- Alerts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>Top 10 Personil dengan Beban Tertinggi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="topWorkloadTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pangkat</th>
                                    <th>Bagian</th>
                                    <th>Total Shift</th>
                                    <th>Shift Malam</th>
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
                    <h5><i class="fas fa-calendar me-2"></i>Jadwal Prediksi Minggu Depan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="schedulePredictionTable">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Pagi</th>
                                    <th>Siang</th>
                                    <th>Malam</th>
                                    <th>Total</th>
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

<style>
.metric-card {
    transition: transform 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
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

.chart-container {
    position: relative;
    height: 300px;
}

.trend-positive {
    color: #28a745;
}

.trend-negative {
    color: #dc3545;
}

.trend-neutral {
    color: #6c757d;
}
</style>

<script>
let charts = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
    
    // Period filter change
    document.getElementById('periodFilter').addEventListener('change', loadDashboardData);
});

// Initialize all charts
function initializeCharts() {
    // Attendance Trend Chart
    const attendanceCtx = document.getElementById('attendanceTrendChart').getContext('2d');
    charts.attendanceTrend = new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Kehadiran',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    charts.statusDistribution = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Sakit', 'Ijin', 'Cuti', 'Tidak Hadir'],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6f42c1',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Workload Chart
    const workloadCtx = document.getElementById('workloadChart').getContext('2d');
    charts.workload = new Chart(workloadCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Shift',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Fatigue by Bagian Chart
    const fatigueCtx = document.getElementById('fatigueByBagianChart').getContext('2d');
    charts.fatigueByBagian = new Chart(fatigueCtx, {
        type: 'radar',
        data: {
            labels: [],
            datasets: [{
                label: 'Tingkat Kelelahan',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Prediction Chart
    const predictionCtx = document.getElementById('predictionChart').getContext('2d');
    charts.prediction = new Chart(predictionCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Prediksi Kebutuhan',
                data: [],
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                borderDash: [5, 5]
            }, {
                label: 'Confidence Interval',
                data: [],
                borderColor: 'rgba(255, 205, 86, 0.3)',
                backgroundColor: 'rgba(255, 205, 86, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Fairness Radar Chart
    const fairnessCtx = document.getElementById('fairnessRadarChart').getContext('2d');
    charts.fairnessRadar = new Chart(fairnessCtx, {
        type: 'radar',
        data: {
            labels: ['Keadilan', 'Efisiensi', 'Kepatuhan', 'Kesejahteraan', 'Produktivitas'],
            datasets: [{
                label: 'Skor Performa',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// Load all dashboard data
function loadDashboardData() {
    const period = document.getElementById('periodFilter').value;
    
    // Load key metrics
    loadKeyMetrics();
    
    // Load attendance trend
    loadAttendanceTrend();
    
    // Load status distribution
    loadStatusDistribution();
    
    // Load workload analysis
    loadWorkloadAnalysis();
    
    // Load fatigue by bagian
    loadFatigueByBagian();
    
    // Load predictions
    loadPredictions(period);
    
    // Load fairness analysis
    loadFairnessAnalysis();
    
    // Load alerts
    loadAlerts();
    
    // Load top workload table
    loadTopWorkloadTable();
    
    // Load schedule predictions
    loadSchedulePredictions();
}

// Load key metrics
function loadKeyMetrics() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_analytics_dashboard`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const metrics = data.data.key_metrics;
                
                document.getElementById('totalPersonnel').textContent = metrics.total_personnel || 0;
                document.getElementById('attendanceRate').textContent = '85%'; // Sample data
                document.getElementById('fatigueIndex').textContent = '72'; // Sample data
                document.getElementById('complianceRate').textContent = '91%'; // Sample data
                
                // Update trends
                document.getElementById('personnelGrowth').textContent = '+2.5%';
                document.getElementById('attendanceTrend').textContent = '+3.2%';
                document.getElementById('fatigueTrend').textContent = '-1.8%';
                document.getElementById('complianceTrend').textContent = '+1.5%';
            }
        })
        .catch(error => console.error('Error loading key metrics:', error));
}

// Load attendance trend
function loadAttendanceTrend() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_piket_trend`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const trendData = data.data;
                
                charts.attendanceTrend.data.labels = trendData.map(item => formatMonth(item.bulan));
                charts.attendanceTrend.data.datasets[0].data = trendData.map(item => 
                    item.hadir > 0 ? Math.round((item.hadir / item.total_jadwal) * 100) : 0
                );
                charts.attendanceTrend.update();
            }
        })
        .catch(error => console.error('Error loading attendance trend:', error));
}

// Load status distribution
function loadStatusDistribution() {
    // Sample data - in real implementation, this would come from API
    const statusData = [45, 8, 12, 15, 20];
    
    charts.statusDistribution.data.datasets[0].data = statusData;
    charts.statusDistribution.update();
}

// Load workload analysis
function loadWorkloadAnalysis() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_personil_workload`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const workloadData = data.data.slice(0, 10);
                
                charts.workload.data.labels = workloadData.map(item => item.nama);
                charts.workload.data.datasets[0].data = workloadData.map(item => item.total_shift);
                charts.workload.update();
            }
        })
        .catch(error => console.error('Error loading workload analysis:', error));
}

// Load fatigue by bagian
function loadFatigueByBagian() {
    // Sample data - in real implementation, this would come from API
    const bagianData = {
        labels: ['Intelpam', 'Reserse', 'Samapta', 'Lantas', 'Sabhara'],
        data: [65, 72, 58, 80, 45]
    };
    
    charts.fatigueByBagian.data.labels = bagianData.labels;
    charts.fatigueByBagian.data.datasets[0].data = bagianData.data;
    charts.fatigueByBagian.update();
}

// Load predictions
function loadPredictions(period) {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_predictive_analytics&period=${period}&analytics_type=staffing`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const predictions = data.data.predictions;
                
                charts.prediction.data.labels = predictions.map(item => formatDate(item.date));
                charts.prediction.data.datasets[0].data = predictions.map(item => item.predicted_demand);
                charts.prediction.data.datasets[1].data = predictions.map(item => item.confidence_interval_low);
                charts.prediction.update();
            }
        })
        .catch(error => console.error('Error loading predictions:', error));
}

// Load fairness analysis
function loadFairnessAnalysis() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_fairness_index`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fairness = data.fairness;
                
                document.getElementById('avgHours').textContent = fairness.avg_jam || 0;
                document.getElementById('stdDev').textContent = fairness.std_dev || 0;
                document.getElementById('fairnessScore').textContent = (fairness.fairness_score || 0) + '%';
                
                // Update progress bar
                const progressBar = document.getElementById('fairnessProgressBar');
                progressBar.style.width = (fairness.fairness_score || 0) + '%';
                
                // Update radar chart
                charts.fairnessRadar.data.datasets[0].data = [
                    fairness.fairness_score || 0,
                    85, // Sample efficiency
                    91, // Sample compliance
                    72, // Sample wellness
                    88  // Sample productivity
                ];
                charts.fairnessRadar.update();
            }
        })
        .catch(error => console.error('Error loading fairness analysis:', error));
}

// Load alerts
function loadAlerts() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_analytics_dashboard`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const alerts = data.data.alerts || [];
                displayAlerts(alerts);
            }
        })
        .catch(error => console.error('Error loading alerts:', error));
}

// Display alerts
function displayAlerts(alerts) {
    const container = document.getElementById('alertsContainer');
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada alert aktif</p>';
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

// Load top workload table
function loadTopWorkloadTable() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_personil_workload`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const workloadData = data.data.slice(0, 10);
                const tbody = document.querySelector('#topWorkloadTable tbody');
                
                tbody.innerHTML = workloadData.map(item => `
                    <tr>
                        <td>${item.nama}</td>
                        <td>${item.pangkat}</td>
                        <td>-</td>
                        <td>${item.total_shift}</td>
                        <td>${item.shift_malam}</td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading top workload:', error));
}

// Load schedule predictions
function loadSchedulePredictions() {
    fetch(`${API_BASE_URL}/analytics_api.php?action=get_demand_forecast&forecast_days=7`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const forecast = data.data.forecast;
                const tbody = document.querySelector('#schedulePredictionTable tbody');
                
                tbody.innerHTML = forecast.map(item => `
                    <tr>
                        <td>${formatDay(item.date)}</td>
                        <td>${item.shifts.PAGI || 0}</td>
                        <td>${item.shifts.SIANG || 0}</td>
                        <td>${item.shifts.MALAM || 0}</td>
                        <td><strong>${item.total_demand}</strong></td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading schedule predictions:', error));
}

// Refresh dashboard
function refreshDashboard() {
    loadDashboardData();
}

// Utility functions
function formatMonth(monthStr) {
    const date = new Date(monthStr + '-01');
    return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function formatDay(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { weekday: 'long' });
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
