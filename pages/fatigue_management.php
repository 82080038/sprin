<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../includes/components/header.php';
require_once __DIR__ . '/../includes/components/footer.php';

// Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Fatigue Management System';
$activeMenu = 'fatigue';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SPRIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/assets/css/main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .fatigue-critical { background-color: #dc3545; color: white; }
        .fatigue-high { background-color: #fd7e14; color: white; }
        .fatigue-medium { background-color: #ffc107; color: black; }
        .fatigue-low { background-color: #28a745; color: white; }
        .wellness-excellent { background-color: #28a745; color: white; }
        .wellness-good { background-color: #17a2b8; color: white; }
        .wellness-fair { background-color: #ffc107; color: black; }
        .wellness-poor { background-color: #dc3545; color: white; }
        .stat-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .personil-card {
            border-left: 4px solid;
            margin-bottom: 10px;
        }
        .personil-card.critical { border-left-color: #dc3545; }
        .personil-card.high { border-left-color: #fd7e14; }
        .personil-card.medium { border-left-color: #ffc107; }
        .personil-card.low { border-left-color: #28a745; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/components/header.php'; ?>
    
    <main class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-exclamation-triangle"></i> Fatigue Management System</h2>
                <p class="text-muted">Monitor dan kelola kelelahan personil untuk menjaga kesehatan dan kinerja optimal</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h3 class="mt-2" id="totalPersonil">-</h3>
                        <p class="mb-0">Total Personil</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
                        <h3 class="mt-2" id="criticalFatigue">-</h3>
                        <p class="mb-0">Critical Fatigue</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="bi bi-heart-pulse fs-1 text-info"></i>
                        <h3 class="mt-2" id="avgWellness">-</h3>
                        <p class="mb-0">Avg Wellness Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history fs-1 text-warning"></i>
                        <h3 class="mt-2" id="violationsToday">-</h3>
                        <p class="mb-0">Violations Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> Fatigue Trends (7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="fatigueTrendsChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart"></i> Fatigue Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="fatigueDistributionChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-people"></i> Personnel Fatigue Status</h5>
                        <div>
                            <select class="form-select form-select-sm d-inline-block w-auto" id="filterBagian">
                                <option value="">Semua Bagian</option>
                            </select>
                            <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="filterFatigue">
                                <option value="">Semua Level</option>
                                <option value="critical">Critical</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="personilTable">
                                <thead>
                                    <tr>
                                        <th>NRP</th>
                                        <th>Nama</th>
                                        <th>Bagian</th>
                                        <th>Wellness Score</th>
                                        <th>Fatigue Level</th>
                                        <th>Hours Today</th>
                                        <th>Consecutive Days</th>
                                        <th>Violations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="personilTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fatigue Tracking Modal -->
        <div class="modal fade" id="fatigueTrackingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fatigue Tracking Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Personnel Information</h6>
                                <p><strong>NRP:</strong> <span id="modalNRP"></span></p>
                                <p><strong>Nama:</strong> <span id="modalNama"></span></p>
                                <p><strong>Bagian:</strong> <span id="modalBagian"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Current Status</h6>
                                <p><strong>Wellness Score:</strong> <span id="modalWellness"></span></p>
                                <p><strong>Fatigue Level:</strong> <span id="modalFatigueLevel"></span></p>
                                <p><strong>Last Check:</strong> <span id="modalLastCheck"></span></p>
                            </div>
                        </div>
                        
                        <h6>Tracking History (30 Days)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="trackingHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Hours Worked</th>
                                        <th>Rest Hours</th>
                                        <th>Fatigue Score</th>
                                        <th>Violations</th>
                                    </tr>
                                </thead>
                                <tbody id="trackingHistoryBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="updateFatigueTracking()">Update Tracking</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Fatigue Modal -->
        <div class="modal fade" id="updateFatigueModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Fatigue Tracking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="updateFatigueForm">
                            <input type="hidden" id="updatePersonilId">
                            <div class="mb-3">
                                <label for="trackingDate" class="form-label">Tracking Date</label>
                                <input type="date" class="form-control" id="trackingDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="hoursWorked" class="form-label">Hours Worked</label>
                                <input type="number" step="0.5" min="0" max="24" class="form-control" id="hoursWorked" required>
                            </div>
                            <div class="mb-3">
                                <label for="restHours" class="form-label">Rest Hours</label>
                                <input type="number" step="0.5" min="0" max="24" class="form-control" id="restHours" required>
                            </div>
                            <div class="mb-3">
                                <label for="fatigueNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="fatigueNotes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveFatigueUpdate()">Save Update</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let fatigueTrendsChart, fatigueDistributionChart;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadFatigueStats();
            loadPersonilList();
            loadBagianOptions();
            initCharts();
            
            // Set up event listeners
            document.getElementById('filterBagian').addEventListener('change', loadPersonilList);
            document.getElementById('filterFatigue').addEventListener('change', loadPersonilList);
            
            // Auto-refresh every 5 minutes
            setInterval(loadFatigueStats, 300000);
            setInterval(loadPersonilList, 300000);
        });

        // Load fatigue statistics
        function loadFatigueStats() {
            fetch('../api/unified-api.php?resource=fatigue&action=get_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('totalPersonil').textContent = stats.total_personil || 0;
                        document.getElementById('criticalFatigue').textContent = stats.critical_fatigue || 0;
                        document.getElementById('avgWellness').textContent = Math.round(stats.avg_wellness_score || 0);
                        document.getElementById('violationsToday').textContent = stats.violations_today || 0;
                        
                        updateCharts(stats);
                    }
                })
                .catch(error => console.error('Error loading fatigue stats:', error));
        }

        // Load personnel list
        function loadPersonilList() {
            const bagianId = document.getElementById('filterBagian').value;
            const fatigueLevel = document.getElementById('filterFatigue').value;
            
            let url = '../api/unified-api.php?resource=personil&action=list';
            const params = new URLSearchParams();
            
            if (bagianId) params.append('bagian_id', bagianId);
            if (fatigueLevel) params.append('fatigue_level', fatigueLevel);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPersonilTable(data.data);
                    }
                })
                .catch(error => console.error('Error loading personnel:', error));
        }

        // Display personnel table
        function displayPersonilTable(personil) {
            const tbody = document.getElementById('personilTableBody');
            tbody.innerHTML = '';
            
            personil.forEach(p => {
                const row = document.createElement('tr');
                row.className = `personil-card ${p.fatigue_level || 'low'}`;
                
                const wellnessClass = getWellnessClass(p.wellness_score || 100);
                const fatigueClass = getFatigueClass(p.fatigue_level || 'low');
                
                row.innerHTML = `
                    <td>${p.nrp}</td>
                    <td>${p.nama}</td>
                    <td>${p.nama_bagian || '-'}</td>
                    <td><span class="badge ${wellnessClass}">${p.wellness_score || 100}</span></td>
                    <td><span class="badge ${fatigueClass}">${p.fatigue_level || 'Low'}</span></td>
                    <td>${p.hours_today || 0}</td>
                    <td>${p.consecutive_days || 1}</td>
                    <td>${p.violations_count || 0}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewFatigueDetails('${p.nrp}')">
                            <i class="bi bi-eye"></i> View
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Get wellness class
        function getWellnessClass(score) {
            if (score >= 90) return 'wellness-excellent';
            if (score >= 75) return 'wellness-good';
            if (score >= 60) return 'wellness-fair';
            return 'wellness-poor';
        }

        // Get fatigue class
        function getFatigueClass(level) {
            switch(level) {
                case 'critical': return 'fatigue-critical';
                case 'high': return 'fatigue-high';
                case 'medium': return 'fatigue-medium';
                default: return 'fatigue-low';
            }
        }

        // Load bagian options
        function loadBagianOptions() {
            fetch('../api/unified-api.php?resource=bagian&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('filterBagian');
                        data.data.forEach(b => {
                            const option = document.createElement('option');
                            option.value = b.id;
                            option.textContent = b.nama_bagian;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading bagian:', error));
        }

        // Initialize charts
        function initCharts() {
            // Fatigue Trends Chart
            const trendsCtx = document.getElementById('fatigueTrendsChart').getContext('2d');
            fatigueTrendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Average Fatigue Score',
                        data: [],
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.4
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

            // Fatigue Distribution Chart
            const distributionCtx = document.getElementById('fatigueDistributionChart').getContext('2d');
            fatigueDistributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Critical'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Update charts with data
        function updateCharts(stats) {
            // Update distribution chart
            fatigueDistributionChart.data.datasets[0].data = [
                stats.low_fatigue || 0,
                stats.medium_fatigue || 0,
                stats.high_fatigue || 0,
                stats.critical_fatigue || 0
            ];
            fatigueDistributionChart.update();

            // Load trends data
            loadFatigueTrends();
        }

        // Load fatigue trends
        function loadFatigueTrends() {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 7);

            fetch(`../api/analytics_api.php?action=get_predictive_analytics&analytics_type=fatigue&start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.fatigue_data) {
                        const trends = data.data.fatigue_data;
                        const labels = trends.map(t => new Date(t.tracking_date).toLocaleDateString());
                        const scores = trends.map(t => t.avg_fatigue_score);

                        fatigueTrendsChart.data.labels = labels;
                        fatigueTrendsChart.data.datasets[0].data = scores;
                        fatigueTrendsChart.update();
                    }
                })
                .catch(error => console.error('Error loading trends:', error));
        }

        // View fatigue details
        function viewFatigueDetails(nrp) {
            const modal = new bootstrap.Modal(document.getElementById('fatigueTrackingModal'));
            
            // Load personnel details
            fetch(`../api/unified-api.php?resource=personil&action=get&id=${nrp}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const p = data.data;
                        document.getElementById('modalNRP').textContent = p.nrp;
                        document.getElementById('modalNama').textContent = p.nama;
                        document.getElementById('modalBagian').textContent = p.nama_bagian || '-';
                        document.getElementById('modalWellness').textContent = p.wellness_score || 100;
                        document.getElementById('modalFatigueLevel').textContent = p.fatigue_level || 'Low';
                        document.getElementById('modalLastCheck').textContent = p.last_fatigue_check || '-';
                        
                        // Load tracking history
                        loadTrackingHistory(nrp);
                    }
                })
                .catch(error => console.error('Error loading personnel:', error));
            
            modal.show();
        }

        // Load tracking history
        function loadTrackingHistory(nrp) {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);

            fetch(`../api/unified-api.php?resource=fatigue&action=get_tracking&personil_id=${nrp}&start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTrackingHistory(data.data);
                    }
                })
                .catch(error => console.error('Error loading tracking history:', error));
        }

        // Display tracking history
        function displayTrackingHistory(tracking) {
            const tbody = document.getElementById('trackingHistoryBody');
            tbody.innerHTML = '';
            
            tracking.forEach(t => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${new Date(t.tracking_date).toLocaleDateString()}</td>
                    <td>${t.hours_worked}</td>
                    <td>${t.rest_hours}</td>
                    <td><span class="badge ${getWellnessClass(t.fatigue_score)}">${t.fatigue_score}</span></td>
                    <td>${t.violations ? JSON.parse(t.violations).length : 0}</td>
                `;
                tbody.appendChild(row);
            });
        }

        // Update fatigue tracking
        function updateFatigueTracking() {
            const nrp = document.getElementById('modalNRP').textContent;
            document.getElementById('updatePersonilId').value = nrp;
            document.getElementById('trackingDate').value = new Date().toISOString().split('T')[0];
            
            const trackingModal = bootstrap.Modal.getInstance(document.getElementById('fatigueTrackingModal'));
            trackingModal.hide();
            
            const updateModal = new bootstrap.Modal(document.getElementById('updateFatigueModal'));
            updateModal.show();
        }

        // Save fatigue update
        function saveFatigueUpdate() {
            const formData = new FormData(document.getElementById('updateFatigueForm'));
            
            fetch('../api/fatigue_api.php?action=update_fatigue_tracking', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Fatigue tracking updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('updateFatigueModal')).hide();
                        loadFatigueStats();
                        loadPersonilList();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error updating fatigue:', error);
                    alert('Error updating fatigue tracking');
                });
        }
    </script>
</body>
</html>
