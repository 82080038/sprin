<?php
/**
 * Leave Management Page - Sistem Cuti dengan Approval Workflow
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

$page_title = 'Manajemen Cuti - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-calendar-alt me-2"></i>Manajemen Cuti</h3>
                <div>
                    <button class="btn btn-primary" onclick="showLeaveRequestModal()">
                        <i class="fas fa-plus me-1"></i>Ajukan Cuti
                    </button>
                    <button class="btn btn-info" onclick="showLeaveCalendar()">
                        <i class="fas fa-calendar me-1"></i>Kalender Cuti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalRequests">0</h4>
                            <p class="mb-0">Total Pengajuan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
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
                            <h4 class="mb-0" id="approvedRequests">0</h4>
                            <p class="mb-0">Disetujui</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h4 class="mb-0" id="pendingRequests">0</h4>
                            <p class="mb-0">Menunggu Persetujuan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h4 class="mb-0" id="rejectedRequests">0</h4>
                            <p class="mb-0">Ditolak</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bell me-2"></i>Notifikasi</h5>
                </div>
                <div class="card-body">
                    <div id="notificationsContainer">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Data Pengajuan Cuti</h5>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="filterByStatus('all')">Semua</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filterByStatus('pending')">Menunggu</button>
                            <button class="btn btn-sm btn-outline-success" onclick="filterByStatus('approved')">Disetujui</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="filterByStatus('rejected')">Ditolak</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="leaveRequestsTable">
                            <thead>
                                <tr>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Nama</th>
                                    <th>Pangkat</th>
                                    <th>Bagian</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Cuti</th>
                                    <th>Lama</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="leaveRequestsBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajukan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="leaveRequestForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Personil</label>
                                <select class="form-select" id="personilSelect" required>
                                    <option value="">Pilih Personil</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Cuti</label>
                                <select class="form-select" id="leaveType" required>
                                    <option value="annual">Cuti Tahunan</option>
                                    <option value="sick">Cuti Sakit</option>
                                    <option value="personal">Cuti Pribadi</option>
                                    <option value="maternity">Cuti Hamil</option>
                                    <option value="unpaid">Cuti Tanpa Gaji</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="startDate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="endDate" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kontak Selama Cuti</label>
                                <input type="text" class="form-control" id="contactInfo" placeholder="No. HP/Email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kontak Darurat</label>
                                <input type="text" class="form-control" id="emergencyContact" placeholder="Nama & No. HP">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Cuti</label>
                        <textarea class="form-control" id="reason" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Saldo Cuti</label>
                        <div id="leaveBalanceInfo">
                            <!-- Leave balance will be loaded here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitLeaveRequest()">Ajukan Cuti</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proses Persetujuan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="approvalDetails">
                    <!-- Leave details will be loaded here -->
                </div>
                <form id="approvalForm">
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea class="form-control" id="approvalReason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="processApproval('reject')">Tolak</button>
                <button type="button" class="btn btn-success" onclick="processApproval('approve')">Setujui</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kalender Cuti</h5>
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
                        <button class="btn btn-primary" onclick="loadCalendar()">Tampilkan</button>
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
.notification-item {
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    border-left: 4px solid;
}

.notification-high {
    background-color: #f8d7da;
    border-left-color: #dc3545;
}

.notification-medium {
    background-color: #fff3cd;
    border-left-color: #ffc107;
}

.notification-low {
    background-color: #d1ecf1;
    border-left-color: #17a2b8;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-approved {
    background-color: #d4edda;
    color: #155724;
}

.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.status-cancelled {
    background-color: #e2e3e5;
    color: #383d41;
}

.calendar-day {
    height: 80px;
    border: 1px solid #ddd;
    padding: 5px;
    vertical-align: top;
}

.calendar-day-number {
    font-weight: bold;
    margin-bottom: 5px;
}

.calendar-leave-item {
    font-size: 0.8em;
    background-color: #e3f2fd;
    padding: 2px;
    margin-bottom: 2px;
    border-radius: 3px;
}
</style>

<script>
let currentFilter = 'all';
let notifications = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadPersonil();
    loadLeaveRequests();
    loadStatistics();
    loadNotifications();
    
    // Set current month in calendar
    document.getElementById('calendarMonth').value = new Date().getMonth() + 1;
    
    // Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});

// Load personil data
function loadPersonil() {
    fetch(`${API_BASE_URL}/personil_simple.php?limit=1000`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('personilSelect');
                select.innerHTML = '<option value="">Pilih Personil</option>';
                
                data.data.forEach(personil => {
                    select.innerHTML += `<option value="${personil.nrp}">${personil.nama} - ${personil.pangkat}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading personil:', error));
}

// Load leave requests
function loadLeaveRequests() {
    const params = new URLSearchParams();
    if (currentFilter !== 'all') {
        params.append('status', currentFilter);
    }
    
    fetch(`${API_BASE_URL}/leave_management_api.php?action=get_leave_requests&${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaveRequests(data.data);
            }
        })
        .catch(error => console.error('Error loading leave requests:', error));
}

// Display leave requests in table
function displayLeaveRequests(requests) {
    const tbody = document.getElementById('leaveRequestsBody');
    tbody.innerHTML = '';
    
    requests.forEach(request => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(request.created_at)}</td>
            <td>${request.personil_name}</td>
            <td>${request.nama_pangkat}</td>
            <td>${request.nama_bagian}</td>
            <td>${getLeaveTypeLabel(request.leave_type)}</td>
            <td>${formatDate(request.start_date)} - ${formatDate(request.end_date)}</td>
            <td>${request.total_days} hari</td>
            <td><span class="status-badge status-${request.approval_status}">${request.status_display}</span></td>
            <td>
                <div class="btn-group">
                    ${request.approval_status === 'pending' ? `
                        <button class="btn btn-sm btn-success" onclick="showApprovalModal(${request.id})">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-info" onclick="viewLeaveDetails(${request.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${request.approval_status === 'pending' || request.approval_status === 'approved' ? `
                        <button class="btn btn-sm btn-warning" onclick="cancelLeaveRequest(${request.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Load statistics
function loadStatistics() {
    fetch(`${API_BASE_URL}/leave_management_api.php?action=get_leave_statistics&year=${new Date().getFullYear()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalRequests').textContent = data.data.total_requests;
                document.getElementById('approvedRequests').textContent = data.data.approved;
                document.getElementById('pendingRequests').textContent = data.data.pending;
                document.getElementById('rejectedRequests').textContent = data.data.rejected;
            }
        })
        .catch(error => console.error('Error loading statistics:', error));
}

// Load notifications
function loadNotifications() {
    // Simulate notifications (in real implementation, this would come from API)
    notifications = [
        {
            id: 1,
            type: 'leave_approval',
            title: 'Pengajuan Cuti Menunggu Persetujuan',
            message: 'Budi Santoso - Cuti Tahunan (3 hari)',
            priority: 'medium',
            action_required: true,
            created_at: new Date().toISOString()
        },
        {
            id: 2,
            type: 'leave_balance',
            title: 'Peringatan Saldo Cuti',
            message: '5 personil dengan saldo cuti tahunan rendah',
            priority: 'low',
            action_required: false,
            created_at: new Date(Date.now() - 3600000).toISOString()
        }
    ];
    
    displayNotifications();
}

// Display notifications
function displayNotifications() {
    const container = document.getElementById('notificationsContainer');
    
    if (notifications.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada notifikasi</p>';
        return;
    }
    
    container.innerHTML = notifications.map(notification => `
        <div class="notification-item notification-${notification.priority}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${notification.title}</h6>
                    <p class="mb-1">${notification.message}</p>
                    <small class="text-muted">${formatDateTime(notification.created_at)}</small>
                </div>
                ${notification.action_required ? `
                    <button class="btn btn-sm btn-primary" onclick="handleNotificationAction(${notification.id})">
                        Proses
                    </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

// Show leave request modal
function showLeaveRequestModal() {
    document.getElementById('leaveRequestModal').classList.add('show');
    document.getElementById('leaveRequestModal').style.display = 'block';
}

// Show approval modal
function showApprovalModal(leaveId) {
    // Load leave details
    fetch(`${API_BASE_URL}/leave_management_api.php?action=get_leave_requests`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.data.find(r => r.id === leaveId);
                if (request) {
                    document.getElementById('approvalDetails').innerHTML = `
                        <div class="mb-3">
                            <strong>Nama:</strong> ${request.personil_name}<br>
                            <strong>Pangkat:</strong> ${request.nama_pangkat}<br>
                            <strong>Bagian:</strong> ${request.nama_bagian}<br>
                            <strong>Jenis Cuti:</strong> ${getLeaveTypeLabel(request.leave_type)}<br>
                            <strong>Tanggal:</strong> ${formatDate(request.start_date)} - ${formatDate(request.end_date)}<br>
                            <strong>Lama:</strong> ${request.total_days} hari<br>
                            <strong>Alasan:</strong> ${request.reason || '-'}
                        </div>
                    `;
                    
                    document.getElementById('approvalModal').classList.add('show');
                    document.getElementById('approvalModal').style.display = 'block';
                    document.getElementById('approvalModal').setAttribute('data-leave-id', leaveId);
                }
            }
        });
}

// Show calendar modal
function showLeaveCalendar() {
    document.getElementById('calendarModal').classList.add('show');
    document.getElementById('calendarModal').style.display = 'block';
    loadCalendar();
}

// Load calendar
function loadCalendar() {
    const month = document.getElementById('calendarMonth').value;
    const year = document.getElementById('calendarYear').value;
    
    fetch(`${API_BASE_URL}/leave_management_api.php?action=get_leave_calendar&month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCalendar(data.data, month, year);
            }
        })
        .catch(error => console.error('Error loading calendar:', error));
}

// Display calendar
function displayCalendar(events, month, year) {
    const container = document.getElementById('calendarContainer');
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
        const dayEvents = events.filter(e => e.date === dateStr);
        
        calendarHTML += `
            <td class="calendar-day">
                <div class="calendar-day-number">${day}</div>
                ${dayEvents.map(event => `
                    <div class="calendar-leave-item">
                        ${event.personil_name}
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
    container.innerHTML = calendarHTML;
}

// Submit leave request
function submitLeaveRequest() {
    const formData = {
        personil_id: document.getElementById('personilSelect').value,
        leave_type: document.getElementById('leaveType').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        reason: document.getElementById('reason').value,
        contact_info: document.getElementById('contactInfo').value,
        emergency_contact: document.getElementById('emergencyContact').value
    };
    
    fetch(`${API_BASE_URL}/leave_management_api.php?action=create_leave_request`, {
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
            alert('Pengajuan cuti berhasil dikirim!');
            closeModal('leaveRequestModal');
            loadLeaveRequests();
            loadStatistics();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error submitting leave request:', error);
        alert('Terjadi kesalahan saat mengirim pengajuan cuti');
    });
}

// Process approval
function processApproval(action) {
    const leaveId = document.getElementById('approvalModal').getAttribute('data-leave-id');
    const reason = document.getElementById('approvalReason').value;
    
    fetch(`${API_BASE_URL}/leave_management_api.php?action=process_approval`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            leave_id: leaveId,
            approval_action: action,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Pengajuan cuti berhasil ${action === 'approve' ? 'disetujui' : 'ditolak'}!`);
            closeModal('approvalModal');
            loadLeaveRequests();
            loadStatistics();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing approval:', error);
        alert('Terjadi kesalahan saat memproses persetujuan');
    });
}

// Cancel leave request
function cancelLeaveRequest(leaveId) {
    if (confirm('Apakah Anda yakin ingin membatalkan pengajuan cuti ini?')) {
        const reason = prompt('Alasan pembatalan:');
        
        if (reason !== null) {
            fetch(`${API_BASE_URL}/leave_management_api.php?action=cancel_leave_request`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    leave_id: leaveId,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pengajuan cuti berhasil dibatalkan!');
                    loadLeaveRequests();
                    loadStatistics();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error cancelling leave request:', error);
                alert('Terjadi kesalahan saat membatalkan pengajuan cuti');
            });
        }
    }
}

// Filter by status
function filterByStatus(status) {
    currentFilter = status;
    loadLeaveRequests();
}

// Utility functions
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
}

function formatDateTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID');
}

function getLeaveTypeLabel(type) {
    const labels = {
        'annual': 'Cuti Tahunan',
        'sick': 'Cuti Sakit',
        'personal': 'Cuti Pribadi',
        'maternity': 'Cuti Hamil',
        'unpaid': 'Cuti Tanpa Gaji'
    };
    return labels[type] || type;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.getElementById(modalId).style.display = 'none';
}

function handleNotificationAction(notificationId) {
    // Handle notification action based on type
    const notification = notifications.find(n => n.id === notificationId);
    if (notification && notification.type === 'leave_approval') {
        // Redirect to pending approvals or show approval modal
        filterByStatus('pending');
    }
}

// Load leave balance when personil is selected
document.getElementById('personilSelect').addEventListener('change', function() {
    const personilId = this.value;
    if (personilId) {
        fetch(`${API_BASE_URL}/leave_management_api.php?action=get_leave_balance&personil_id=${personilId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const balance = data.data;
                    document.getElementById('leaveBalanceInfo').innerHTML = `
                        <div class="row">
                            <div class="col-md-3">
                                <small>Tahunan: ${balance.annual_remaining}/${balance.annual_balance}</small>
                            </div>
                            <div class="col-md-3">
                                <small>Sakit: ${balance.sick_remaining}/${balance.sick_balance}</small>
                            </div>
                            <div class="col-md-3">
                                <small>Pribadi: ${balance.personal_remaining}/${balance.personal_balance}</small>
                            </div>
                            <div class="col-md-3">
                                <small>Hamil: ${balance.maternity_remaining}/${balance.maternity_balance}</small>
                            </div>
                        </div>
                    `;
                }
            });
    }
});
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
