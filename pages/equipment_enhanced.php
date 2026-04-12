<?php
/**
 * Enhanced Equipment Management Page - Improved UI with Better UX
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

$page_title = 'Manajemen Peralatan - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-tools me-2"></i>Manajemen Peralatan</h3>
                <div>
                    <button class="btn btn-primary" onclick="showEquipmentModal()">
                        <i class="fas fa-plus me-1"></i>Tambah Peralatan
                    </button>
                    <button class="btn btn-info" onclick="showMaintenanceModal()">
                        <i class="fas fa-wrench me-1"></i>Jadwal Maintenance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalEquipment">0</h4>
                            <p class="mb-0">Total Peralatan</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="equipmentGrowth">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
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
                            <h4 class="mb-0" id="assignedEquipment">0</h4>
                            <p class="mb-0">Ditugaskan</p>
                            <small class="opacity-75">
                                <i class="fas fa-user-check"></i> <span id="assignmentRate">0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-handshake fa-2x"></i>
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
                            <h4 class="mb-0" id="maintenanceDue">0</h4>
                            <p class="mb-0">Maintenance Jatuh Tempo</p>
                            <small class="opacity-75">
                                <i class="fas fa-exclamation-triangle"></i> 7 Hari
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tools fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="overdueMaintenance">0</h4>
                            <p class="mb-0">Maintenance Terlambat</p>
                            <small class="opacity-75">
                                <i class="fas fa-clock"></i> Overdue
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Overview Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Distribusi Peralatan per Jenis</h5>
                </div>
                <div class="card-body">
                    <canvas id="equipmentTypeChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Status Peralatan</h5>
                </div>
                <div class="card-body">
                    <canvas id="equipmentStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-wrench me-2"></i>Alert Maintenance</h5>
                </div>
                <div class="card-body">
                    <div id="maintenanceAlerts">
                        <!-- Maintenance alerts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment List with Advanced Filtering -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Data Peralatan</h5>
                    <div class="card-tools">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="typeFilter" onchange="filterEquipment()">
                                    <option value="">Semua Jenis</option>
                                    <option value="weapon">Senjata</option>
                                    <option value="vehicle">Kendaraan</option>
                                    <option value="radio">Radio</option>
                                    <option value="protective">Alat Pelindung</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter" onchange="filterEquipment()">
                                    <option value="">Semua Status</option>
                                    <option value="available">Tersedia</option>
                                    <option value="assigned">Ditugaskan</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Ditarik</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="bagianFilter" onchange="filterEquipment()">
                                    <option value="">Semua Bagian</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" id="searchFilter" placeholder="Cari..." onkeyup="filterEquipment()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="equipmentTable">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Peralatan</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Ditugaskan Kepada</th>
                                    <th>Bagian</th>
                                    <th>Maintenance</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Peralatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="equipmentForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kode Peralatan</label>
                                <input type="text" class="form-control" id="equipmentCode" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Peralatan</label>
                                <input type="text" class="form-control" id="equipmentName" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Jenis Peralatan</label>
                                <select class="form-select" id="equipmentType" required>
                                    <option value="weapon">Senjata</option>
                                    <option value="vehicle">Kendaraan</option>
                                    <option value="radio">Radio</option>
                                    <option value="protective">Alat Pelindung</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nomor Seri</label>
                                <input type="text" class="form-control" id="serialNumber">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="equipmentStatus">
                                    <option value="available">Tersedia</option>
                                    <option value="assigned">Ditugaskan</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Ditarik</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ditugaskan Kepada</label>
                                <select class="form-select" id="assignedTo">
                                    <option value="">Tidak ada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Pembelian</label>
                                <input type="date" class="form-control" id="purchaseDate">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Maintenance Berikutnya</label>
                                <input type="date" class="form-control" id="nextMaintenance">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Penyimpanan</label>
                                <input type="text" class="form-control" id="storageLocation">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kondisi</label>
                                <select class="form-select" id="condition">
                                    <option value="excellent">Sangat Baik</option>
                                    <option value="good">Baik</option>
                                    <option value="fair">Cukup</option>
                                    <option value="poor">Buruk</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveEquipment()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Jadwal Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="maintenanceMonth">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" id="maintenanceYear" value="<?php echo date('Y'); ?>" min="2020" max="2030">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" onclick="loadMaintenanceSchedule()">Tampilkan</button>
                    </div>
                </div>
                <div id="maintenanceSchedule">
                    <!-- Maintenance schedule will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.equipment-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: transform 0.2s;
    cursor: pointer;
}

.equipment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.equipment-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}

.status-available {
    background-color: #d4edda;
    color: #155724;
}

.status-assigned {
    background-color: #cce5ff;
    color: #004085;
}

.status-maintenance {
    background-color: #fff3cd;
    color: #856404;
}

.status-retired {
    background-color: #f8d7da;
    color: #721c24;
}

.maintenance-alert {
    border-left: 4px solid;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 4px;
}

.alert-overdue {
    background-color: #f8d7da;
    border-left-color: #dc3545;
}

.alert-due-soon {
    background-color: #fff3cd;
    border-left-color: #ffc107;
}

.alert-scheduled {
    background-color: #d1ecf1;
    border-left-color: #17a2b8;
}

.maintenance-item {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
}

.maintenance-item.overdue {
    border-left: 4px solid #dc3545;
    background-color: #f8d7da;
}

.maintenance-item.due-soon {
    border-left: 4px solid #ffc107;
    background-color: #fff3cd;
}

.chart-container {
    position: relative;
    height: 300px;
}

.equipment-type-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}

.type-weapon { background-color: #dc3545; color: white; }
.type-vehicle { background-color: #28a745; color: white; }
.type-radio { background-color: #17a2b8; color: white; }
.type-protective { background-color: #ffc107; color: black; }
.type-other { background-color: #6c757d; color: white; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let equipmentCharts = {};
let allEquipment = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadPersonil();
    loadBagian();
    loadEquipment();
    loadEquipmentStatistics();
    loadMaintenanceAlerts();
    
    // Set current month in maintenance
    document.getElementById('maintenanceMonth').value = new Date().getMonth() + 1;
    
    // Auto-refresh every 5 minutes
    setInterval(loadEquipmentStatistics, 300000);
});

// Load personil data
function loadPersonil() {
    fetch(`${API_BASE_URL}/personil_simple.php?limit=1000`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('assignedTo');
                select.innerHTML = '<option value="">Tidak ada</option>';
                
                data.data.forEach(personil => {
                    select.innerHTML += `<option value="${personil.nrp}">${personil.nama} - ${personil.pangkat}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading personil:', error));
}

// Load bagian data
function loadBagian() {
    fetch(`${API_BASE_URL}/bagian_api.php?action=get_all_bagian`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const filterSelect = document.getElementById('bagianFilter');
                
                data.data.forEach(bagian => {
                    filterSelect.innerHTML += `<option value="${bagian.id}">${bagian.nama_bagian}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading bagian:', error));
}

// Load equipment
function loadEquipment() {
    fetch(`${API_BASE_URL}/equipment_api.php?action=get_equipment`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allEquipment = data.data;
                applyFilters();
                updateCharts();
            }
        })
        .catch(error => console.error('Error loading equipment:', error));
}

// Load equipment statistics
function loadEquipmentStatistics() {
    const stats = {
        total: allEquipment.length,
        assigned: allEquipment.filter(e => e.current_assignment).length,
        maintenanceDue: allEquipment.filter(e => {
            if (!e.next_maintenance) return false;
            const daysUntil = Math.ceil((new Date(e.next_maintenance) - new Date()) / (1000 * 60 * 60 * 24));
            return daysUntil <= 7 && daysUntil >= 0;
        }).length,
        overdue: allEquipment.filter(e => {
            if (!e.next_maintenance) return false;
            return new Date(e.next_maintenance) < new Date();
        }).length
    };
    
    document.getElementById('totalEquipment').textContent = stats.total;
    document.getElementById('assignedEquipment').textContent = stats.assigned;
    document.getElementById('maintenanceDue').textContent = stats.maintenanceDue;
    document.getElementById('overdueMaintenance').textContent = stats.overdue;
    
    const assignmentRate = stats.total > 0 ? Math.round((stats.assigned / stats.total) * 100) : 0;
    document.getElementById('assignmentRate').textContent = assignmentRate + '%';
    
    // Update trends (sample data)
    document.getElementById('equipmentGrowth').textContent = '+8.3%';
}

// Load maintenance alerts
function loadMaintenanceAlerts() {
    const today = new Date();
    const alerts = [];
    
    // Overdue maintenance
    const overdue = allEquipment.filter(e => {
        if (!e.next_maintenance) return false;
        return new Date(e.next_maintenance) < today;
    });
    
    // Due soon (within 7 days)
    const dueSoon = allEquipment.filter(e => {
        if (!e.next_maintenance) return false;
        const daysUntil = Math.ceil((new Date(e.next_maintenance) - today) / (1000 * 60 * 60 * 24));
        return daysUntil <= 7 && daysUntil >= 0;
    });
    
    // Scheduled (within 30 days)
    const scheduled = allEquipment.filter(e => {
        if (!e.next_maintenance) return false;
        const daysUntil = Math.ceil((new Date(e.next_maintenance) - today) / (1000 * 60 * 60 * 24));
        return daysUntil <= 30 && daysUntil > 7;
    });
    
    const container = document.getElementById('maintenanceAlerts');
    
    if (overdue.length === 0 && dueSoon.length === 0 && scheduled.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada alert maintenance aktif</p>';
        return;
    }
    
    let alertsHTML = '';
    
    if (overdue.length > 0) {
        alertsHTML += `
            <div class="maintenance-alert alert-overdue">
                <h6><i class="fas fa-exclamation-circle"></i> Maintenance Terlambat (${overdue.length})</h6>
                <div class="small">
                    ${overdue.slice(0, 3).map(e => `
                        <div>${e.equipment_name} - ${e.equipment_code}</div>
                    `).join('')}
                    ${overdue.length > 3 ? `<div>... dan ${overdue.length - 3} lainnya</div>` : ''}
                </div>
            </div>
        `;
    }
    
    if (dueSoon.length > 0) {
        alertsHTML += `
            <div class="maintenance-alert alert-due-soon">
                <h6><i class="fas fa-clock"></i> Maintenance Jatuh Tempo (${dueSoon.length})</h6>
                <div class="small">
                    ${dueSoon.slice(0, 3).map(e => {
                        const daysUntil = Math.ceil((new Date(e.next_maintenance) - today) / (1000 * 60 * 60 * 24));
                        return `<div>${e.equipment_name} - ${daysUntil} hari lagi</div>`;
                    }).join('')}
                    ${dueSoon.length > 3 ? `<div>... dan ${dueSoon.length - 3} lainnya</div>` : ''}
                </div>
            </div>
        `;
    }
    
    if (scheduled.length > 0) {
        alertsHTML += `
            <div class="maintenance-alert alert-scheduled">
                <h6><i class="fas fa-calendar"></i> Maintenance Terjadwal (${scheduled.length})</h6>
                <div class="small">
                    ${scheduled.slice(0, 3).map(e => {
                        const daysUntil = Math.ceil((new Date(e.next_maintenance) - today) / (1000 * 60 * 60 * 24));
                        return `<div>${e.equipment_name} - ${daysUntil} hari lagi</div>`;
                    }).join('')}
                    ${scheduled.length > 3 ? `<div>... dan ${scheduled.length - 3} lainnya</div>` : ''}
                </div>
            </div>
        `;
    }
    
    container.innerHTML = alertsHTML;
}

// Apply filters
function applyFilters() {
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const bagian = document.getElementById('bagianFilter').value;
    const search = document.getElementById('searchFilter').value.toLowerCase();
    
    let filtered = allEquipment;
    
    if (type) {
        filtered = filtered.filter(e => e.equipment_type === type);
    }
    
    if (status) {
        filtered = filtered.filter(e => e.current_status === status);
    }
    
    if (bagian) {
        filtered = filtered.filter(e => e.assigned_bagian_id == bagian);
    }
    
    if (search) {
        filtered = filtered.filter(e => 
            e.equipment_name.toLowerCase().includes(search) ||
            e.equipment_code.toLowerCase().includes(search) ||
            (e.serial_number && e.serial_number.toLowerCase().includes(search))
        );
    }
    
    displayEquipment(filtered);
}

// Display equipment in table
function displayEquipment(equipment) {
    const tbody = document.getElementById('equipmentBody');
    tbody.innerHTML = '';
    
    equipment.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="equipment-type-icon type-${item.equipment_type}">
                        ${getTypeIcon(item.equipment_type)}
                    </div>
                    <span>${item.equipment_code}</span>
                </div>
            </td>
            <td>
                <strong>${item.equipment_name}</strong>
                ${item.serial_number ? `<br><small class="text-muted">S/N: ${item.serial_number}</small>` : ''}
            </td>
            <td>${getTypeLabel(item.equipment_type)}</td>
            <td>
                <span class="equipment-status status-${item.current_status}">
                    ${getStatusLabel(item.current_status)}
                </span>
            </td>
            <td>${item.assigned_name || '-'}</td>
            <td>${item.assigned_bagian || '-'}</td>
            <td>
                ${item.next_maintenance ? `
                    <div>
                        ${formatDate(item.next_maintenance)}
                        <br>
                        <small class="${getMaintenanceClass(item.days_to_maintenance)}">
                            ${getMaintenanceStatus(item.days_to_maintenance)}
                        </small>
                    </div>
                ` : '-'}
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-info" onclick="viewEquipmentDetails(${item.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editEquipment(${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="assignEquipment(${item.id})">
                        <i class="fas fa-user-plus"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Update charts
function updateCharts() {
    // Equipment type distribution
    const typeData = getEquipmentTypeData();
    updateEquipmentTypeChart(typeData);
    
    // Equipment status distribution
    const statusData = getEquipmentStatusData();
    updateEquipmentStatusChart(statusData);
}

function updateEquipmentTypeChart(data) {
    const ctx = document.getElementById('equipmentTypeChart').getContext('2d');
    
    if (equipmentCharts.type) {
        equipmentCharts.type.destroy();
    }
    
    equipmentCharts.type = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah Peralatan',
                data: data.values,
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
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
}

function updateEquipmentStatusChart(data) {
    const ctx = document.getElementById('equipmentStatusChart').getContext('2d');
    
    if (equipmentCharts.status) {
        equipmentCharts.status.destroy();
    }
    
    equipmentCharts.status = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
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

// Get chart data
function getEquipmentTypeData() {
    const types = ['weapon', 'vehicle', 'radio', 'protective', 'other'];
    const counts = types.map(type => 
        allEquipment.filter(e => e.equipment_type === type).length
    );
    
    return {
        labels: types.map(t => getTypeLabel(t)),
        values: counts
    };
}

function getEquipmentStatusData() {
    const statuses = ['available', 'assigned', 'maintenance', 'retired'];
    const counts = statuses.map(status => 
        allEquipment.filter(e => e.current_status === status).length
    );
    
    return {
        labels: statuses.map(s => getStatusLabel(s)),
        values: counts
    };
}

// Modal functions
function showEquipmentModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Peralatan';
    document.getElementById('equipmentForm').reset();
    document.getElementById('equipmentModal').classList.add('show');
    document.getElementById('equipmentModal').style.display = 'block';
}

function showMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.add('show');
    document.getElementById('maintenanceModal').style.display = 'block';
    loadMaintenanceSchedule();
}

function loadMaintenanceSchedule() {
    const month = document.getElementById('maintenanceMonth').value;
    const year = document.getElementById('maintenanceYear').value;
    
    // Filter equipment for maintenance in selected month
    const monthEquipment = allEquipment.filter(e => {
        if (!e.next_maintenance) return false;
        const maintenanceDate = new Date(e.next_maintenance);
        return maintenanceDate.getMonth() + 1 == month && maintenanceDate.getFullYear() == year;
    });
    
    displayMaintenanceSchedule(monthEquipment, month, year);
}

function displayMaintenanceSchedule(equipment, month, year) {
    const container = document.getElementById('maintenanceSchedule');
    
    if (equipment.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada jadwal maintenance untuk bulan ini</p>';
        return;
    }
    
    const scheduleHTML = equipment.map(item => {
        const daysUntil = Math.ceil((new Date(item.next_maintenance) - new Date()) / (1000 * 60 * 60 * 24));
        const isOverdue = daysUntil < 0;
        const isDueSoon = daysUntil >= 0 && daysUntil <= 7;
        
        return `
            <div class="maintenance-item ${isOverdue ? 'overdue' : isDueSoon ? 'due-soon' : ''}">
                <div class="row">
                    <div class="col-md-4">
                        <strong>${item.equipment_name}</strong><br>
                        <small class="text-muted">${item.equipment_code}</small>
                    </div>
                    <div class="col-md-3">
                        <strong>Tanggal:</strong><br>
                        ${formatDate(item.next_maintenance)}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="${isOverdue ? 'text-danger' : isDueSoon ? 'text-warning' : 'text-success'}">
                            ${isOverdue ? 'Terlambat' : isDueSoon ? 'Jatuh Tempo' : 'Terjadwal'}
                        </span>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary" onclick="scheduleMaintenance(${item.id})">
                            <i class="fas fa-wrench"></i> Schedule
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = scheduleHTML;
}

// Save equipment
function saveEquipment() {
    const formData = {
        equipment_code: document.getElementById('equipmentCode').value,
        equipment_name: document.getElementById('equipmentName').value,
        equipment_type: document.getElementById('equipmentType').value,
        serial_number: document.getElementById('serialNumber').value,
        current_status: document.getElementById('equipmentStatus').value,
        current_assignment: document.getElementById('assignedTo').value || null,
        purchase_date: document.getElementById('purchaseDate').value,
        next_maintenance: document.getElementById('nextMaintenance').value,
        storage_location: document.getElementById('storageLocation').value,
        condition: document.getElementById('condition').value,
        description: document.getElementById('description').value
    };
    
    fetch(`${API_BASE_URL}/equipment_api.php?action=create`, {
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
            alert('Peralatan berhasil disimpan!');
            closeModal('equipmentModal');
            loadEquipment();
            loadEquipmentStatistics();
            loadMaintenanceAlerts();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving equipment:', error);
        alert('Terjadi kesalahan saat menyimpan peralatan');
    });
}

// Filter functions
function filterEquipment() {
    applyFilters();
}

// Utility functions
function getTypeIcon(type) {
    const icons = {
        'weapon': 'fas fa-gun',
        'vehicle': 'fas fa-car',
        'radio': 'fas fa-walkie-talkie',
        'protective': 'fas fa-shield-alt',
        'other': 'fas fa-tools'
    };
    return `<i class="${icons[type] || 'fas fa-box'}"></i>`;
}

function getTypeLabel(type) {
    const labels = {
        'weapon': 'Senjata',
        'vehicle': 'Kendaraan',
        'radio': 'Radio',
        'protective': 'Alat Pelindung',
        'other': 'Lainnya'
    };
    return labels[type] || type;
}

function getStatusLabel(status) {
    const labels = {
        'available': 'Tersedia',
        'assigned': 'Ditugaskan',
        'maintenance': 'Maintenance',
        'retired': 'Ditarik'
    };
    return labels[status] || status;
}

function getMaintenanceClass(days) {
    if (days < 0) return 'text-danger';
    if (days <= 7) return 'text-warning';
    return 'text-success';
}

function getMaintenanceStatus(days) {
    if (days < 0) return `Terlambat ${Math.abs(days)} hari`;
    if (days <= 7) return `${days} hari lagi`;
    return `${days} hari lagi`;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.getElementById(modalId).style.display = 'none';
}

function viewEquipmentDetails(equipmentId) {
    alert(`View details for equipment ${equipmentId}`);
}

function editEquipment(equipmentId) {
    alert(`Edit equipment ${equipmentId}`);
}

function assignEquipment(equipmentId) {
    alert(`Assign equipment ${equipmentId}`);
}

function scheduleMaintenance(equipmentId) {
    alert(`Schedule maintenance for equipment ${equipmentId}`);
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
