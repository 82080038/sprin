<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../includes/components/header.php';
require_once __DIR__ . '/../includes/components/footer.php';

// Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Certification & Training Compliance';
$activeMenu = 'certification';
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
        .status-valid { background-color: #28a745; color: white; }
        .status-expired { background-color: #dc3545; color: white; }
        .status-expiring { background-color: #ffc107; color: black; }
        .status-suspended { background-color: #6c757d; color: white; }
        .status-required { background-color: #17a2b8; color: white; }
        .status-in_progress { background-color: #007bff; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-failed { background-color: #dc3545; color: white; }
        .expiry-critical { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white; 
            border: none;
        }
        .expiry-warning { 
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: black; 
            border: none;
        }
        .expiry-safe { 
            background: linear-gradient(135deg, #28a745, #218838);
            color: white; 
            border: none;
        }
        .cert-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .cert-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .cert-card.valid { border-left-color: #28a745; }
        .cert-card.expired { border-left-color: #dc3545; }
        .cert-card.expiring { border-left-color: #ffc107; }
        .cert-card.suspended { border-left-color: #6c757d; }
        .stats-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-3px);
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.35s;
            stroke: #28a745;
            stroke-dasharray: 126;
        }
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        .file-upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .file-upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/components/header.php'; ?>
    
    <main class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-award-fill text-primary"></i> Certification & Training Compliance</h2>
                <p class="text-muted">Monitor dan kelola sertifikasi dan pelatihan personil untuk memastikan kepatuhan standar</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h3 class="mt-2" id="totalPersonil">-</h3>
                        <p class="mb-0">Total Personnel</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-patch-check fs-1 text-success"></i>
                        <h3 class="mt-2" id="validCerts">-</h3>
                        <p class="mb-0">Valid Certs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                        <h3 class="mt-2" id="expiringSoon">-</h3>
                        <p class="mb-0">Expiring Soon</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle fs-1 text-danger"></i>
                        <h3 class="mt-2" id="expiredCerts">-</h3>
                        <p class="mb-0">Expired</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Overview -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> Compliance Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="complianceTrendsChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart"></i> Compliance Rate</h5>
                    </div>
                    <div class="card-body text-center">
                        <svg width="150" height="150" class="progress-ring">
                            <circle class="progress-ring-circle" 
                                    stroke-width="8" 
                                    fill="transparent" 
                                    r="56" 
                                    cx="75" 
                                    cy="75"/>
                        </svg>
                        <h3 class="mt-2" id="complianceRate">-</h3>
                        <p class="mb-0">Overall Compliance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <button class="btn btn-primary" onclick="showAddCertificationModal()">
                        <i class="bi bi-plus-circle"></i> Add Certification
                    </button>
                    <button class="btn btn-info" onclick="showAddTrainingModal()">
                        <i class="bi bi-plus-circle"></i> Add Training
                    </button>
                    <button class="btn btn-warning" onclick="showExpiringAlerts()">
                        <i class="bi bi-bell"></i> Expiring Alerts
                    </button>
                    <button class="btn btn-success" onclick="generateComplianceReport()">
                        <i class="bi bi-file-earmark-text"></i> Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs for Certifications and Training -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="complianceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="certifications-tab" data-bs-toggle="tab" data-bs-target="#certifications" type="button" role="tab">
                            <i class="bi bi-award"></i> Certifications
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="training-tab" data-bs-toggle="tab" data-bs-target="#training" type="button" role="tab">
                            <i class="bi bi-mortarboard"></i> Training
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="complianceTabContent">
                    <!-- Certifications Tab -->
                    <div class="tab-pane fade show active" id="certifications" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="bi bi-award"></i> Certification Records</h5>
                                <div>
                                    <select class="form-select form-select-sm d-inline-block w-auto" id="certFilterBagian">
                                        <option value="">All Bagian</option>
                                    </select>
                                    <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="certFilterStatus">
                                        <option value="">All Status</option>
                                        <option value="valid">Valid</option>
                                        <option value="expired">Expired</option>
                                        <option value="expiring">Expiring Soon</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="certificationsTable">
                                        <thead>
                                            <tr>
                                                <th>NRP</th>
                                                <th>Nama</th>
                                                <th>Bagian</th>
                                                <th>Certification</th>
                                                <th>Issuing Authority</th>
                                                <th>Issue Date</th>
                                                <th>Expiry Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="certificationsTableBody">
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
                    
                    <!-- Training Tab -->
                    <div class="tab-pane fade" id="training" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="bi bi-mortarboard"></i> Training Records</h5>
                                <div>
                                    <select class="form-select form-select-sm d-inline-block w-auto" id="trainingFilterBagian">
                                        <option value="">All Bagian</option>
                                    </select>
                                    <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="trainingFilterStatus">
                                        <option value="">All Status</option>
                                        <option value="required">Required</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="expired">Expired</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="trainingTable">
                                        <thead>
                                            <tr>
                                                <th>NRP</th>
                                                <th>Nama</th>
                                                <th>Bagian</th>
                                                <th>Training</th>
                                                <th>Provider</th>
                                                <th>Training Date</th>
                                                <th>Status</th>
                                                <th>Hours</th>
                                                <th>Next Due</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trainingTableBody">
                                            <tr>
                                                <td colspan="10" class="text-center">
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
                    
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade" id="dashboard" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5><i class="bi bi-speedometer2"></i> Compliance Dashboard</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Compliance by Bagian</h6>
                                        <canvas id="bagianComplianceChart" height="200"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Upcoming Expirations</h6>
                                        <div id="upcomingExpirations">
                                            <div class="text-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Certification Modal -->
        <div class="modal fade" id="addCertificationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Certification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCertificationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="certPersonil" class="form-label">Personnel *</label>
                                        <select class="form-select" id="certPersonil" required>
                                            <option value="">Select Personnel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="certType" class="form-label">Certification Type *</label>
                                        <input type="text" class="form-control" id="certType" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="certName" class="form-label">Certification Name *</label>
                                        <input type="text" class="form-control" id="certName" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="certAuthority" class="form-label">Issuing Authority</label>
                                        <input type="text" class="form-control" id="certAuthority">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="certNumber" class="form-label">Certificate Number</label>
                                        <input type="text" class="form-control" id="certNumber">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="certIssueDate" class="form-label">Issue Date</label>
                                        <input type="date" class="form-control" id="certIssueDate">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="certExpiryDate" class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" id="certExpiryDate">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="certNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="certNotes" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Certificate Attachment</label>
                                <div class="file-upload-area" id="certFileUpload">
                                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                                    <p class="mb-0">Drop file here or click to browse</p>
                                    <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                                </div>
                                <input type="file" id="certFile" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="addCertification()">Add Certification</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Training Modal -->
        <div class="modal fade" id="addTrainingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Training Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addTrainingForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="trainingPersonil" class="form-label">Personnel *</label>
                                        <select class="form-select" id="trainingPersonil" required>
                                            <option value="">Select Personnel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="trainingType" class="form-label">Training Type *</label>
                                        <input type="text" class="form-control" id="trainingType" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="trainingName" class="form-label">Training Name *</label>
                                        <input type="text" class="form-control" id="trainingName" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="trainingProvider" class="form-label">Provider</label>
                                        <input type="text" class="form-control" id="trainingProvider">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="trainingDate" class="form-label">Training Date</label>
                                        <input type="date" class="form-control" id="trainingDate">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="completionDate" class="form-label">Completion Date</label>
                                        <input type="date" class="form-control" id="completionDate">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nextDue" class="form-label">Next Due</label>
                                        <input type="date" class="form-control" id="nextDue">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="hoursCompleted" class="form-label">Hours Completed</label>
                                        <input type="number" step="0.5" class="form-control" id="hoursCompleted" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="requiredHours" class="form-label">Required Hours</label>
                                        <input type="number" step="0.5" class="form-control" id="requiredHours" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="trainingStatus" class="form-label">Status *</label>
                                        <select class="form-select" id="trainingStatus" required>
                                            <option value="">Select Status</option>
                                            <option value="required">Required</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="expired">Expired</option>
                                            <option value="failed">Failed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="trainingNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="trainingNotes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="addTraining()">Add Training</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let complianceTrendsChart, bagianComplianceChart;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadComplianceStats();
            loadCertifications();
            loadTraining();
            loadPersonnelOptions();
            loadBagianOptions();
            initCharts();
            
            // Set up event listeners
            document.getElementById('certFilterBagian').addEventListener('change', loadCertifications);
            document.getElementById('certFilterStatus').addEventListener('change', loadCertifications);
            document.getElementById('trainingFilterBagian').addEventListener('change', loadTraining);
            document.getElementById('trainingFilterStatus').addEventListener('change', loadTraining);
            
            // File upload handlers
            setupFileUpload('certFileUpload', 'certFile');
            
            // Auto-refresh every 5 minutes
            setInterval(loadComplianceStats, 300000);
            setInterval(loadCertifications, 300000);
            setInterval(loadTraining, 300000);
        });

        // Load compliance statistics
        function loadComplianceStats() {
            fetch('../api/unified-api.php?resource=certification&action=get_compliance_dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('totalPersonil').textContent = stats.total_personil || 0;
                        document.getElementById('validCerts').textContent = stats.valid_certifications || 0;
                        document.getElementById('expiringSoon').textContent = stats.expiring_soon || 0;
                        document.getElementById('expiredCerts').textContent = stats.expired_certifications || 0;
                        
                        const complianceRate = stats.total_personil > 0 ? 
                            Math.round((stats.valid_certifications / stats.total_personil) * 100) : 0;
                        document.getElementById('complianceRate').textContent = complianceRate + '%';
                        
                        updateComplianceRing(complianceRate);
                        updateCharts(stats);
                    }
                })
                .catch(error => console.error('Error loading compliance stats:', error));
        }

        // Load certifications
        function loadCertifications() {
            const bagianId = document.getElementById('certFilterBagian').value;
            const status = document.getElementById('certFilterStatus').value;
            
            let url = '../api/unified-api.php?resource=certification&action=get_certifications';
            const params = new URLSearchParams();
            
            if (bagianId) params.append('bagian_id', bagianId);
            if (status) params.append('status', status);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCertifications(data.data);
                    }
                })
                .catch(error => console.error('Error loading certifications:', error));
        }

        // Display certifications
        function displayCertifications(certs) {
            const tbody = document.getElementById('certificationsTableBody');
            tbody.innerHTML = '';
            
            if (certs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No certifications found</td></tr>';
                return;
            }
            
            certs.forEach(cert => {
                const row = document.createElement('tr');
                row.className = `cert-card ${cert.status}`;
                
                const statusClass = `status-${cert.status}`;
                const expiryClass = getExpiryClass(cert.days_to_expiry);
                
                row.innerHTML = `
                    <td>${cert.personil_id}</td>
                    <td>${cert.personil_name}</td>
                    <td>${cert.nama_bagian || '-'}</td>
                    <td>${cert.certification_name}</td>
                    <td>${cert.issuing_authority || '-'}</td>
                    <td>${formatDate(cert.issue_date)}</td>
                    <td><span class="badge ${expiryClass}">${formatDate(cert.expiry_date)}</span></td>
                    <td><span class="badge ${statusClass}">${cert.status.toUpperCase()}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewCertification(${cert.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editCertification(${cert.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Load training
        function loadTraining() {
            const bagianId = document.getElementById('trainingFilterBagian').value;
            const status = document.getElementById('trainingFilterStatus').value;
            
            let url = '../api/unified-api.php?resource=certification&action=get_training_records';
            const params = new URLSearchParams();
            
            if (bagianId) params.append('bagian_id', bagianId);
            if (status) params.append('status', status);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTraining(data.data);
                    }
                })
                .catch(error => console.error('Error loading training:', error));
        }

        // Display training
        function displayTraining(training) {
            const tbody = document.getElementById('trainingTableBody');
            tbody.innerHTML = '';
            
            if (training.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No training records found</td></tr>';
                return;
            }
            
            training.forEach(t => {
                const row = document.createElement('tr');
                
                const statusClass = `status-${t.status}`;
                
                row.innerHTML = `
                    <td>${t.personil_id}</td>
                    <td>${t.personil_name}</td>
                    <td>${t.nama_bagian || '-'}</td>
                    <td>${t.training_name}</td>
                    <td>${t.provider || '-'}</td>
                    <td>${formatDate(t.training_date)}</td>
                    <td><span class="badge ${statusClass}">${t.status.replace('_', ' ').toUpperCase()}</span></td>
                    <td>${t.hours_completed || 0}/${t.required_hours || 0}</td>
                    <td>${formatDate(t.next_due)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewTraining(${t.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editTraining(${t.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Load personnel options
        function loadPersonnelOptions() {
            fetch('../api/unified-api.php?resource=personil&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const selects = ['certPersonil', 'trainingPersonil'];
                        selects.forEach(selectId => {
                            const select = document.getElementById(selectId);
                            data.data.forEach(p => {
                                const option = document.createElement('option');
                                option.value = p.nrp;
                                option.textContent = `${p.nama} (${p.nrp})`;
                                select.appendChild(option);
                            });
                        });
                    }
                })
                .catch(error => console.error('Error loading personnel:', error));
        }

        // Load bagian options
        function loadBagianOptions() {
            fetch('../api/unified-api.php?resource=bagian&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const selects = ['certFilterBagian', 'trainingFilterBagian'];
                        selects.forEach(selectId => {
                            const select = document.getElementById(selectId);
                            data.data.forEach(b => {
                                const option = document.createElement('option');
                                option.value = b.id;
                                option.textContent = b.nama_bagian;
                                select.appendChild(option);
                            });
                        });
                    }
                })
                .catch(error => console.error('Error loading bagian:', error));
        }

        // Initialize charts
        function initCharts() {
            // Compliance Trends Chart
            const trendsCtx = document.getElementById('complianceTrendsChart').getContext('2d');
            complianceTrendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Valid Certifications',
                        data: [],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Expired Certifications',
                        data: [],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
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

            // Bagian Compliance Chart
            const bagianCtx = document.getElementById('bagianComplianceChart').getContext('2d');
            bagianComplianceChart = new Chart(bagianCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Compliance Rate (%)',
                        data: [],
                        backgroundColor: '#17a2b8'
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

        // Update compliance ring
        function updateComplianceRing(percentage) {
            const circle = document.querySelector('.progress-ring-circle');
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            const offset = circumference - (percentage / 100) * circumference;
            
            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = offset;
        }

        // Update charts
        function updateCharts(stats) {
            // Update bagian compliance chart
            loadBagianCompliance();
            loadUpcomingExpirations();
        }

        // Load bagian compliance
        function loadBagianCompliance() {
            fetch('../api/analytics_api.php?action=get_performance_metrics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // This would need actual bagian compliance data
                        // For now, using placeholder data
                        bagianComplianceChart.data.labels = ['Bagian A', 'Bagian B', 'Bagian C'];
                        bagianComplianceChart.data.datasets[0].data = [85, 92, 78];
                        bagianComplianceChart.update();
                    }
                })
                .catch(error => console.error('Error loading bagian compliance:', error));
        }

        // Load upcoming expirations
        function loadUpcomingExpirations() {
            fetch('../api/unified-api.php?resource=certification&action=get_certifications&expiring_days=30')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUpcomingExpirations(data.data);
                    }
                })
                .catch(error => console.error('Error loading expirations:', error));
        }

        // Display upcoming expirations
        function displayUpcomingExpirations(expirations) {
            const container = document.getElementById('upcomingExpirations');
            
            if (expirations.length === 0) {
                container.innerHTML = '<p class="text-muted">No upcoming expirations</p>';
                return;
            }
            
            container.innerHTML = expirations.slice(0, 5).map(cert => `
                <div class="alert alert-warning alert-sm">
                    <strong>${cert.personil_name}</strong> - ${cert.certification_name}
                    <br><small>Expires in ${cert.days_to_expiry} days</small>
                </div>
            `).join('');
        }

        // Get expiry class
        function getExpiryClass(daysToExpiry) {
            if (daysToExpiry === null || daysToExpiry < 0) return 'expiry-critical';
            if (daysToExpiry <= 30) return 'expiry-warning';
            return 'expiry-safe';
        }

        // Format date
        function formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        }

        // Setup file upload
        function setupFileUpload(areaId, inputId) {
            const area = document.getElementById(areaId);
            const input = document.getElementById(inputId);
            
            area.addEventListener('click', () => input.click());
            
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    updateFileDisplay(area, files[0]);
                }
            });
            
            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    updateFileDisplay(area, e.target.files[0]);
                }
            });
        }

        // Update file display
        function updateFileDisplay(area, file) {
            area.innerHTML = `
                <i class="bi bi-file-earmark-check fs-1 text-success"></i>
                <p class="mb-0">${file.name}</p>
                <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
            `;
        }

        // Show add certification modal
        function showAddCertificationModal() {
            const modal = new bootstrap.Modal(document.getElementById('addCertificationModal'));
            modal.show();
        }

        // Add certification
        function addCertification() {
            const formData = new FormData(document.getElementById('addCertificationForm'));
            
            fetch('../api/certification_api.php?action=add_certification', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Certification added successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('addCertificationModal')).hide();
                        document.getElementById('addCertificationForm').reset();
                        loadCertifications();
                        loadComplianceStats();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error adding certification:', error);
                    alert('Error adding certification');
                });
        }

        // Show add training modal
        function showAddTrainingModal() {
            const modal = new bootstrap.Modal(document.getElementById('addTrainingModal'));
            modal.show();
        }

        // Add training
        function addTraining() {
            const formData = new FormData(document.getElementById('addTrainingForm'));
            
            fetch('../api/certification_api.php?action=add_training_compliance', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Training record added successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('addTrainingModal')).hide();
                        document.getElementById('addTrainingForm').reset();
                        loadTraining();
                        loadComplianceStats();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error adding training:', error);
                    alert('Error adding training record');
                });
        }

        // Show expiring alerts
        function showExpiringAlerts() {
            fetch('../api/unified-api.php?resource=certification&action=get_certifications&expiring_days=30')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.length === 0) {
                            alert('No certifications expiring in the next 30 days!');
                        } else {
                            let alertList = data.data.map(cert => 
                                `${cert.personil_name} - ${cert.certification_name} (Expires in ${cert.days_to_expiry} days)`
                            ).join('\n');
                            alert(`Expiring certifications:\n${alertList}`);
                        }
                    }
                })
                .catch(error => console.error('Error loading expirations:', error));
        }

        // Generate compliance report
        function generateComplianceReport() {
            // This would generate a PDF report
            alert('Compliance report generation feature coming soon!');
        }

        // View certification details
        function viewCertification(id) {
            // Load and display certification details
            alert('View certification details feature coming soon!');
        }

        // Edit certification
        function editCertification(id) {
            // Load certification into edit modal
            alert('Edit certification feature coming soon!');
        }

        // View training details
        function viewTraining(id) {
            // Load and display training details
            alert('View training details feature coming soon!');
        }

        // Edit training
        function editTraining(id) {
            // Load training into edit modal
            alert('Edit training feature coming soon!');
        }
    </script>
</body>
</html>
