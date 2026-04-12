<?php
/**
 * Predictive Analytics Dashboard for SPRIN Application
 * Advanced Analytics - Predictive Features Interface
 */

require_once '../core/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Page title
$page_title = "Predictive Analytics Dashboard - SPRIN";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            border-left: 4px solid #007bff;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .risk-high {
            border-left-color: #dc3545;
        }
        .risk-medium {
            border-left-color: #ffc107;
        }
        .risk-low {
            border-left-color: #28a745;
        }
        .prediction-chart {
            max-height: 300px;
        }
        .insight-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }
        .recommendation-item {
            padding: 8px;
            margin: 3px 0;
            background: #e7f3ff;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .prediction-confidence {
            font-size: 0.8em;
            color: #6c757d;
        }
        .trend-up {
            color: #28a745;
        }
        .trend-down {
            color: #dc3545;
        }
        .trend-stable {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>
            
            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-chart-line me-2"></i>
                        Predictive Analytics Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReport()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <h3 class="card-title" id="staffing-demand">45</h3>
                                <p class="card-text">Staffing Demand (Next 7 Days)</p>
                                <small class="prediction-confidence">Confidence: 85%</small>
                                <div class="trend-up">
                                    <i class="fas fa-arrow-up"></i> +15%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <h3 class="card-title" id="fatigue-alerts">7</h3>
                                <p class="card-text">Fatigue Alerts</p>
                                <small class="prediction-confidence">2 Critical, 5 High</small>
                                <div class="trend-up">
                                    <i class="fas fa-exclamation-triangle"></i> Action Required
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <h3 class="card-title" id="success-rate">78%</h3>
                                <p class="card-text">Operation Success Rate</p>
                                <small class="prediction-confidence">Last Month: 82%</small>
                                <div class="trend-down">
                                    <i class="fas fa-arrow-down"></i> -4%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <h3 class="card-title" id="resource-utilization">75%</h3>
                                <p class="card-text">Resource Utilization</p>
                                <small class="prediction-confidence">Optimal Range</small>
                                <div class="trend-stable">
                                    <i class="fas fa-equals"></i> Stable
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Staffing Demand Prediction
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="loading-spinner">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <canvas id="staffingChart" class="prediction-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Fatigue Risk Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="loading-spinner">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <canvas id="fatigueChart" class="prediction-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Predictions -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Absence Pattern Prediction
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="loading-spinner">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <canvas id="absenceChart" class="prediction-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Resource Allocation Forecast
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="loading-spinner">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <canvas id="resourceChart" class="prediction-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Insights and Recommendations -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Key Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="insights-container">
                                    <div class="insight-item">
                                        <strong>Staffing Trend:</strong> Demand expected to increase by 15% next week
                                    </div>
                                    <div class="insight-item">
                                        <strong>Fatigue Risk:</strong> 2 personnel at critical risk require immediate attention
                                    </div>
                                    <div class="insight-item">
                                        <strong>Performance:</strong> Operation success rate improved by 4% this month
                                    </div>
                                    <div class="insight-item">
                                        <strong>Resources:</strong> Current utilization within optimal range
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>
                                    Recommendations
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="recommendations-container">
                                    <div class="recommendation-item">
                                        <i class="fas fa-user-plus text-primary"></i> Increase staffing allocation for upcoming operations
                                    </div>
                                    <div class="recommendation-item">
                                        <i class="fas fa-bed text-warning"></i> Implement fatigue mitigation measures for high-risk personnel
                                    </div>
                                    <div class="recommendation-item">
                                        <i class="fas fa-cogs text-success"></i> Maintain current resource management strategy
                                    </div>
                                    <div class="recommendation-item">
                                        <i class="fas fa-chart-line text-info"></i> Continue monitoring operational success factors
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personnel Risk Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Personnel Risk Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="personnel-risk-table">
                                        <thead>
                                            <tr>
                                                <th>NRP</th>
                                                <th>Nama</th>
                                                <th>Risk Level</th>
                                                <th>Risk Score</th>
                                                <th>Night Shifts</th>
                                                <th>Recommendations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Model Performance -->
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-brain me-2"></i>
                                    Model Performance Metrics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-primary">82%</h4>
                                            <small>Staffing Prediction Accuracy</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-warning">78%</h4>
                                            <small>Fatigue Risk Precision</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-info">75%</h4>
                                            <small>Absence Prediction Recall</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-success">85%</h4>
                                            <small>Success Probability Confidence</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let charts = {};
        
        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            initializeCharts();
        });
        
        // Load dashboard data from API
        function loadDashboardData() {
            showLoading();
            
            fetch('../api/predictive_analytics_api.php?action=predictive_dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardMetrics(data.data);
                        updateInsights(data.data.key_insights);
                        updateRecommendations(data.data.recommendations);
                        updatePersonnelRiskTable(data.data.fatigue_alerts);
                        updateCharts(data.data);
                    } else {
                        console.error('Failed to load dashboard data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        // Update dashboard metrics
        function updateDashboardMetrics(data) {
            document.getElementById('staffing-demand').textContent = data.staffing_predictions.next_7_days.predicted_demand;
            document.getElementById('fatigue-alerts').textContent = data.fatigue_alerts.total_alerts;
            document.getElementById('success-rate').textContent = data.operation_success_rates.overall_success_rate;
            document.getElementById('resource-utilization').textContent = data.resource_forecasts.vehicle_utilization;
        }
        
        // Update insights
        function updateInsights(insights) {
            const container = document.getElementById('insights-container');
            container.innerHTML = '';
            
            insights.forEach(insight => {
                const div = document.createElement('div');
                div.className = 'insight-item';
                div.innerHTML = `<strong>${insight.split(':')[0]}:</strong> ${insight.split(':')[1]}`;
                container.appendChild(div);
            });
        }
        
        // Update recommendations
        function updateRecommendations(recommendations) {
            const container = document.getElementById('recommendations-container');
            container.innerHTML = '';
            
            recommendations.forEach(recommendation => {
                const div = document.createElement('div');
                div.className = 'recommendation-item';
                div.innerHTML = recommendation;
                container.appendChild(div);
            });
        }
        
        // Update personnel risk table
        function updatePersonnelRiskTable(fatigueAlerts) {
            const tbody = document.querySelector('#personnel-risk-table tbody');
            tbody.innerHTML = '';
            
            // Sample data - would come from API
            const sampleData = [
                { nrp: '198401012015031001', nama: 'Ahmad Rizki', risk_level: 'critical', risk_score: 85, night_shifts: 5, recommendations: ['Immediate rest', 'Reduce shifts'] },
                { nrp: '198502022015031002', nama: 'Budi Santoso', risk_level: 'high', risk_score: 65, night_shifts: 3, recommendations: ['Monitor closely', 'Increase rest'] },
                { nrp: '198603032015031003', nama: 'Cahaya Putra', risk_level: 'medium', risk_score: 35, night_shifts: 2, recommendations: ['Regular breaks'] }
            ];
            
            sampleData.forEach(person => {
                const row = document.createElement('tr');
                row.className = `risk-${person.risk_level}`;
                
                const riskBadge = getRiskBadge(person.risk_level);
                const recommendations = person.recommendations.join(', ');
                
                row.innerHTML = `
                    <td>${person.nrp}</td>
                    <td>${person.nama}</td>
                    <td>${riskBadge}</td>
                    <td>${person.risk_score}</td>
                    <td>${person.night_shifts}</td>
                    <td><small>${recommendations}</small></td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Get risk badge HTML
        function getRiskBadge(riskLevel) {
            const badges = {
                critical: '<span class="badge bg-danger">Critical</span>',
                high: '<span class="badge bg-warning">High</span>',
                medium: '<span class="badge bg-info">Medium</span>',
                low: '<span class="badge bg-success">Low</span>'
            };
            return badges[riskLevel] || badges.low;
        }
        
        // Initialize charts
        function initializeCharts() {
            // Staffing Demand Chart
            const staffingCtx = document.getElementById('staffingChart').getContext('2d');
            charts.staffing = new Chart(staffingCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Predicted Demand',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Fatigue Risk Chart
            const fatigueCtx = document.getElementById('fatigueChart').getContext('2d');
            charts.fatigue = new Chart(fatigueCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical', 'High', 'Medium', 'Low'],
                    datasets: [{
                        data: [2, 5, 8, 15],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#17a2b8',
                            '#28a745'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Absence Pattern Chart
            const absenceCtx = document.getElementById('absenceChart').getContext('2d');
            charts.absence = new Chart(absenceCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Predicted Absences',
                        data: [],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
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
            
            // Resource Allocation Chart
            const resourceCtx = document.getElementById('resourceChart').getContext('2d');
            charts.resource = new Chart(resourceCtx, {
                type: 'pie',
                data: {
                    labels: ['Vehicles', 'Equipment', 'Weapons', 'Communication'],
                    datasets: [{
                        data: [75, 88, 65, 92],
                        backgroundColor: [
                            '#36a2eb',
                            '#ff6384',
                            '#ffce56',
                            '#4bc0c0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Update charts with data
        function updateCharts(data) {
            // Update staffing chart with sample data
            const staffingLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const staffingData = [42, 45, 48, 44, 46, 43, 41];
            
            charts.staffing.data.labels = staffingLabels;
            charts.staffing.data.datasets[0].data = staffingData;
            charts.staffing.update();
            
            // Update fatigue chart
            charts.fatigue.data.datasets[0].data = [2, 5, 8, 15];
            charts.fatigue.update();
            
            // Update absence chart
            const absenceLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const absenceData = [1, 2, 1, 3, 2, 1, 1];
            
            charts.absence.data.labels = absenceLabels;
            charts.absence.data.datasets[0].data = absenceData;
            charts.absence.update();
            
            // Update resource chart
            charts.resource.data.datasets[0].data = [75, 88, 65, 92];
            charts.resource.update();
        }
        
        // Show loading spinner
        function showLoading() {
            document.querySelectorAll('.loading-spinner').forEach(spinner => {
                spinner.style.display = 'block';
            });
        }
        
        // Hide loading spinner
        function hideLoading() {
            document.querySelectorAll('.loading-spinner').forEach(spinner => {
                spinner.style.display = 'none';
            });
        }
        
        // Refresh dashboard
        function refreshDashboard() {
            loadDashboardData();
        }
        
        // Export report
        function exportReport() {
            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Metric,Value,Trend\n";
            csvContent += "Staffing Demand,45,Increasing\n";
            csvContent += "Fatigue Alerts,7,Action Required\n";
            csvContent += "Success Rate,78%,Decreasing\n";
            csvContent += "Resource Utilization,75%,Stable\n";
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "predictive_analytics_report.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Auto-refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    </script>
</body>
</html>
