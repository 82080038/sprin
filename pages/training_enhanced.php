<?php
/**
 * Enhanced Training Management Page - Improved UI with Better UX
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

$page_title = 'Manajemen Pelatihan - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-graduation-cap me-2"></i>Manajemen Pelatihan</h3>
                <div>
                    <button class="btn btn-primary" onclick="showTrainingModal()">
                        <i class="fas fa-plus me-1"></i>Tambah Pelatihan
                    </button>
                    <button class="btn btn-info" onclick="showCalendarView()">
                        <i class="fas fa-calendar me-1"></i>Kalender
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Training Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalTraining">0</h4>
                            <p class="mb-0">Total Pelatihan</p>
                            <small class="opacity-75">
                                <i class="fas fa-arrow-up"></i> <span id="trainingGrowth">+0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
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
                            <h4 class="mb-0" id="completedTraining">0</h4>
                            <p class="mb-0">Selesai</p>
                            <small class="opacity-75">
                                <i class="fas fa-check-circle"></i> <span id="completionRate">0%</span>
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-trophy fa-2x"></i>
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
                            <h4 class="mb-0" id="ongoingTraining">0</h4>
                            <p class="mb-0">Sedang Berlangsung</p>
                            <small class="opacity-75">
                                <i class="fas fa-clock"></i> Aktif
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-spinner fa-2x"></i>
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
                            <h4 class="mb-0" id="totalHours">0</h4>
                            <p class="mb-0">Total Jam</p>
                            <small class="opacity-75">
                                <i class="fas fa-hourglass-half"></i> Jam Latihan
                            </small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hourglass fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Training Overview Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Trend Pelatihan (6 Bulan)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trainingTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Distribusi Jenis Pelatihan</h5>
                </div>
                <div class="card-body">
                    <canvas id="trainingTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Trainings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Pelatihan Mendatang</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="upcomingTrainings">
                        <!-- Upcoming trainings will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Training List with Advanced Filtering -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Data Pelatihan</h5>
                    <div class="card-tools">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter" onchange="filterTrainings()">
                                    <option value="">Semua Status</option>
                                    <option value="rencana">Rencana</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="batal">Dibatalkan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="jenisFilter" onchange="filterTrainings()">
                                    <option value="">Semua Jenis</option>
                                    <option value="menembak">Menembak</option>
                                    <option value="bela_diri">Bela Diri</option>
                                    <option value="sar">SAR</option>
                                    <option value="ketahanan">Ketahanan</option>
                                    <option value="teknis">Teknis</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="bagianFilter" onchange="filterTrainings()">
                                    <option value="">Semua Bagian</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" id="searchFilter" placeholder="Cari..." onkeyup="filterTrainings()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="trainingsTable">
                            <thead>
                                <tr>
                                    <th>Nama Pelatihan</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Lokasi</th>
                                    <th>Instruktur</th>
                                    <th>Peserta</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="trainingsBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Training Modal -->
<div class="modal fade" id="trainingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="trainingForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nama Pelatihan</label>
                                <input type="text" class="form-control" id="namaPelatihan" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Jenis Pelatihan</label>
                                <select class="form-select" id="jenisPelatihan" required>
                                    <option value="menembak">Menembak</option>
                                    <option value="bela_diri">Bela Diri</option>
                                    <option value="sar">SAR</option>
                                    <option value="ketahanan">Ketahanan</option>
                                    <option value="teknis">Teknis</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggalSelesai">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Jam Latihan</label>
                                <input type="number" class="form-control" id="jamLatihan" step="0.5" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Target Peserta</label>
                                <input type="number" class="form-control" id="pesertaTarget" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="lokasi">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Instruktur</label>
                                <input type="text" class="form-control" id="instruktur">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Bagian</label>
                                <select class="form-select" id="bagianId">
                                    <option value="">Semua Bagian</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="status">
                                    <option value="rencana">Rencana</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="batal">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Peserta Hadir</label>
                                <input type="number" class="form-control" id="pesertaHadir" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveTraining()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kalender Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="calendarMonth">
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
                        <input type="number" class="form-control" id="calendarYear" value="<?php echo date('Y'); ?>" min="2020" max="2030">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" onclick="loadTrainingCalendar()">Tampilkan</button>
                    </div>
                </div>
                <div id="calendarContainer">
                    <!-- Calendar will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.training-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: transform 0.2s;
    cursor: pointer;
}

.training-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.training-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}

.status-rencana {
    background-color: #e3f2fd;
    color: #1976d2;
}

.status-berlangsung {
    background-color: #fff3e0;
    color: #f57c00;
}

.status-selesai {
    background-color: #e8f5e8;
    color: #2e7d32;
}

.status-batal {
    background-color: #ffebee;
    color: #c62828;
}

.progress-ring {
    width: 40px;
    height: 40px;
    position: relative;
}

.progress-ring-circle {
    transition: stroke-dashoffset 0.35s;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}

.calendar-day {
    min-height: 100px;
    border: 1px solid #ddd;
    padding: 5px;
    vertical-align: top;
}

.calendar-training {
    background-color: #e3f2fd;
    padding: 2px;
    margin-bottom: 2px;
    border-radius: 3px;
    font-size: 0.8em;
    cursor: pointer;
}

.calendar-training:hover {
    background-color: #bbdefb;
}

.filter-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.upcoming-training {
    border-left: 4px solid #1976d2;
    padding-left: 15px;
    margin-bottom: 10px;
}

.upcoming-training.urgent {
    border-left-color: #f57c00;
}

.chart-container {
    position: relative;
    height: 300px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let trainingCharts = {};
let allTrainings = [];
let currentFilters = {
    status: '',
    jenis: '',
    bagian: '',
    search: ''
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadBagian();
    loadTrainings();
    loadTrainingStatistics();
    loadUpcomingTrainings();
    
    // Set current month in calendar
    document.getElementById('calendarMonth').value = new Date().getMonth() + 1;
    
    // Auto-refresh every 5 minutes
    setInterval(loadTrainingStatistics, 300000);
});

// Load bagian data
function loadBagian() {
    fetch(`${API_BASE_URL}/bagian_api.php?action=get_all_bagian`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('bagianId');
                const filterSelect = document.getElementById('bagianFilter');
                
                data.data.forEach(bagian => {
                    select.innerHTML += `<option value="${bagian.id}">${bagian.nama_bagian}</option>`;
                    filterSelect.innerHTML += `<option value="${bagian.id}">${bagian.nama_bagian}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading bagian:', error));
}

// Load trainings
function loadTrainings() {
    fetch(`${API_BASE_URL}/pelatihan_api.php?action=get_all`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allTrainings = data.data;
                applyFilters();
                updateCharts();
            }
        })
        .catch(error => console.error('Error loading trainings:', error));
}

// Load training statistics
function loadTrainingStatistics() {
    // Calculate statistics from all trainings
    const stats = {
        total: allTrainings.length,
        completed: allTrainings.filter(t => t.status === 'selesai').length,
        ongoing: allTrainings.filter(t => t.status === 'berlangsung').length,
        totalHours: allTrainings.reduce((sum, t) => sum + (parseFloat(t.jam_latihan) || 0), 0)
    };
    
    document.getElementById('totalTraining').textContent = stats.total;
    document.getElementById('completedTraining').textContent = stats.completed;
    document.getElementById('ongoingTraining').textContent = stats.ongoing;
    document.getElementById('totalHours').textContent = stats.totalHours.toFixed(1);
    
    const completionRate = stats.total > 0 ? Math.round((stats.completed / stats.total) * 100) : 0;
    document.getElementById('completionRate').textContent = completionRate + '%';
    
    // Update trends (sample data)
    document.getElementById('trainingGrowth').textContent = '+12.5%';
}

// Load upcoming trainings
function loadUpcomingTrainings() {
    const today = new Date();
    const upcoming = allTrainings
        .filter(t => t.status === 'rencana' && new Date(t.tanggal_mulai) > today)
        .sort((a, b) => new Date(a.tanggal_mulai) - new Date(b.tanggal_mulai))
        .slice(0, 5);
    
    const container = document.getElementById('upcomingTrainings');
    
    if (upcoming.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada pelatihan mendatang</p>';
        return;
    }
    
    container.innerHTML = upcoming.map(training => {
        const daysUntil = Math.ceil((new Date(training.tanggal_mulai) - today) / (1000 * 60 * 60 * 24));
        const isUrgent = daysUntil <= 7;
        
        return `
            <div class="col-md-6 col-lg-4">
                <div class="training-card upcoming-training ${isUrgent ? 'urgent' : ''}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${training.nama_pelatihan}</h6>
                        <span class="training-status status-${training.status}">
                            ${getStatusLabel(training.status)}
                        </span>
                    </div>
                    <div class="small text-muted">
                        <div><i class="fas fa-calendar"></i> ${formatDate(training.tanggal_mulai)}</div>
                        <div><i class="fas fa-map-marker-alt"></i> ${training.lokasi || '-'}</div>
                        <div><i class="fas fa-user"></i> ${training.instruktur || '-'}</div>
                        <div><i class="fas fa-users"></i> ${training.peserta_target || 0} peserta</div>
                        ${isUrgent ? `<div class="text-warning"><i class="fas fa-exclamation-triangle"></i> ${daysUntil} hari lagi</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Apply filters
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const jenis = document.getElementById('jenisFilter').value;
    const bagian = document.getElementById('bagianFilter').value;
    const search = document.getElementById('searchFilter').value.toLowerCase();
    
    let filtered = allTrainings;
    
    if (status) {
        filtered = filtered.filter(t => t.status === status);
    }
    
    if (jenis) {
        filtered = filtered.filter(t => t.jenis === jenis);
    }
    
    if (bagian) {
        filtered = filtered.filter(t => t.bagian_id == bagian);
    }
    
    if (search) {
        filtered = filtered.filter(t => 
            t.nama_pelatihan.toLowerCase().includes(search) ||
            (t.lokasi && t.lokasi.toLowerCase().includes(search)) ||
            (t.instruktur && t.instruktur.toLowerCase().includes(search))
        );
    }
    
    displayTrainings(filtered);
}

// Display trainings in table
function displayTrainings(trainings) {
    const tbody = document.getElementById('trainingsBody');
    tbody.innerHTML = '';
    
    trainings.forEach(training => {
        const progress = calculateProgress(training);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div>
                    <strong>${training.nama_pelatihan}</strong>
                    ${training.deskripsi ? `<br><small class="text-muted">${training.deskripsi}</small>` : ''}
                </div>
            </td>
            <td>${getJenisLabel(training.jenis)}</td>
            <td>
                ${formatDate(training.tanggal_mulai)}
                ${training.tanggal_selesai ? `<br><small>s/d ${formatDate(training.tanggal_selesai)}</small>` : ''}
            </td>
            <td>${training.lokasi || '-'}</td>
            <td>${training.instruktur || '-'}</td>
            <td>
                ${training.peserta_hadir || 0}/${training.peserta_target || 0}
                ${training.peserta_target ? `
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar" style="width: ${calculateAttendanceProgress(training)}%"></div>
                    </div>
                ` : ''}
            </td>
            <td>
                <span class="training-status status-${training.status}">
                    ${getStatusLabel(training.status)}
                </span>
            </td>
            <td>
                <div class="progress-ring">
                    <svg width="40" height="40">
                        <circle class="progress-ring-circle" stroke="#e9ecef" stroke-width="3" fill="transparent" r="16" cx="20" cy="20"/>
                        <circle class="progress-ring-circle" stroke="${getProgressColor(progress)}" stroke-width="3" fill="transparent" r="16" cx="20" cy="20"
                                stroke-dasharray="${progress * 1.003} 100.52" stroke-dashoffset="${100.52 - (progress * 1.003)}"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.8em;">
                        ${progress}%
                    </div>
                </div>
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-info" onclick="viewTrainingDetails(${training.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editTraining(${training.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTraining(${training.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Update charts
function updateCharts() {
    // Training trend chart
    const trendData = getTrainingTrendData();
    updateTrainingTrendChart(trendData);
    
    // Training type distribution
    const typeData = getTrainingTypeData();
    updateTrainingTypeChart(typeData);
}

function updateTrainingTrendChart(data) {
    const ctx = document.getElementById('trainingTrendChart').getContext('2d');
    
    if (trainingCharts.trend) {
        trainingCharts.trend.destroy();
    }
    
    trainingCharts.trend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah Pelatihan',
                data: data.values,
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
                    beginAtZero: true
                }
            }
        }
    });
}

function updateTrainingTypeChart(data) {
    const ctx = document.getElementById('trainingTypeChart').getContext('2d');
    
    if (trainingCharts.type) {
        trainingCharts.type.destroy();
    }
    
    trainingCharts.type = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Calculate progress
function calculateProgress(training) {
    if (training.status === 'selesai') return 100;
    if (training.status === 'berlangsung') return 50;
    if (training.status === 'rencana') return 0;
    return 0;
}

function calculateAttendanceProgress(training) {
    if (!training.peserta_target || training.peserta_target === 0) return 0;
    return Math.round((training.peserta_hadir || 0) / training.peserta_target * 100);
}

function getProgressColor(progress) {
    if (progress >= 80) return '#28a745';
    if (progress >= 50) return '#ffc107';
    return '#dc3545';
}

// Get chart data
function getTrainingTrendData() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const values = months.map(() => Math.floor(Math.random() * 10) + 5);
    
    return { labels: months, values: values };
}

function getTrainingTypeData() {
    const types = ['menembak', 'bela_diri', 'sar', 'ketahanan', 'teknis', 'lainnya'];
    const counts = types.map(type => 
        allTrainings.filter(t => t.jenis === type).length
    );
    
    return {
        labels: types.map(t => getJenisLabel(t)),
        values: counts
    };
}

// Modal functions
function showTrainingModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pelatihan';
    document.getElementById('trainingForm').reset();
    document.getElementById('trainingModal').classList.add('show');
    document.getElementById('trainingModal').style.display = 'block';
}

function showCalendarView() {
    document.getElementById('calendarModal').classList.add('show');
    document.getElementById('calendarModal').style.display = 'block';
    loadTrainingCalendar();
}

function loadTrainingCalendar() {
    const month = document.getElementById('calendarMonth').value;
    const year = document.getElementById('calendarYear').value;
    
    // Filter trainings for the selected month
    const monthTrainings = allTrainings.filter(t => {
        const trainingDate = new Date(t.tanggal_mulai);
        return trainingDate.getMonth() + 1 == month && trainingDate.getFullYear() == year;
    });
    
    displayTrainingCalendar(monthTrainings, month, year);
}

function displayTrainingCalendar(trainings, month, year) {
    const firstDay = new Date(year, month - 1, 1).getDay();
    const daysInMonth = new Date(year, month, 0).getDate();
    
    let calendarHTML = '<table class="table table-bordered"><tr>';
    
    // Day headers
    const dayHeaders = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    dayHeaders.forEach(day => {
        calendarHTML += `<th class="text-center">${day}</th>`;
    });
    calendarHTML += '</tr><tr>';
    
    // Empty cells for first week
    for (let i = 0; i < firstDay; i++) {
        calendarHTML += '<td></td>';
    }
    
    // Days of month
    let currentDay = firstDay;
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${month.padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        const dayTrainings = trainings.filter(t => t.tanggal_mulai === dateStr);
        
        calendarHTML += `
            <td class="calendar-day">
                <div class="calendar-day-number">${day}</div>
                ${dayTrainings.map(training => `
                    <div class="calendar-training" onclick="viewTrainingDetails(${training.id})">
                        ${training.nama_pelatihan}
                    </div>
                `).join('')}
            </td>
        `;
        
        currentDay++;
        if (currentDay === 7) {
            calendarHTML += '</tr><tr>';
            currentDay = 0;
        }
    }
    
    // Fill remaining cells
    while (currentDay > 0 && currentDay < 7) {
        calendarHTML += '<td></td>';
        currentDay++;
    }
    
    calendarHTML += '</tr></table>';
    document.getElementById('calendarContainer').innerHTML = calendarHTML;
}

// Save training
function saveTraining() {
    const formData = {
        nama_pelatihan: document.getElementById('namaPelatihan').value,
        jenis: document.getElementById('jenisPelatihan').value,
        tanggal_mulai: document.getElementById('tanggalMulai').value,
        tanggal_selesai: document.getElementById('tanggalSelesai').value,
        jam_latihan: document.getElementById('jamLatihan').value,
        lokasi: document.getElementById('lokasi').value,
        instruktur: document.getElementById('instruktur').value,
        bagian_id: document.getElementById('bagianId').value,
        status: document.getElementById('status').value,
        peserta_target: document.getElementById('pesertaTarget').value,
        peserta_hadir: document.getElementById('pesertaHadir').value,
        deskripsi: document.getElementById('deskripsi').value
    };
    
    fetch(`${API_BASE_URL}/pelatihan_api.php?action=create`, {
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
            alert('Pelatihan berhasil disimpan!');
            closeModal('trainingModal');
            loadTrainings();
            loadTrainingStatistics();
            loadUpcomingTrainings();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving training:', error);
        alert('Terjadi kesalahan saat menyimpan pelatihan');
    });
}

// Filter functions
function filterTrainings() {
    applyFilters();
}

// Utility functions
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
}

function getJenisLabel(jenis) {
    const labels = {
        'menembak': 'Menembak',
        'bela_diri': 'Bela Diri',
        'sar': 'SAR',
        'ketahanan': 'Ketahanan',
        'teknis': 'Teknis',
        'lainnya': 'Lainnya'
    };
    return labels[jenis] || jenis;
}

function getStatusLabel(status) {
    const labels = {
        'rencana': 'Rencana',
        'berlangsung': 'Berlangsung',
        'selesai': 'Selesai',
        'batal': 'Dibatalkan'
    };
    return labels[status] || status;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.getElementById(modalId).style.display = 'none';
}

function viewTrainingDetails(trainingId) {
    alert(`View details for training ${trainingId}`);
}

function editTraining(trainingId) {
    alert(`Edit training ${trainingId}`);
}

function deleteTraining(trainingId) {
    if (confirm('Apakah Anda yakin ingin menghapus pelatihan ini?')) {
        fetch(`${API_BASE_URL}/pelatihan_api.php?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ id: trainingId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pelatihan berhasil dihapus!');
                loadTrainings();
                loadTrainingStatistics();
                loadUpcomingTrainings();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting training:', error);
            alert('Terjadi kesalahan saat menghapus pelatihan');
        });
    }
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
