<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /sprint/login.php');
    exit;
}

$page_title = 'Schedule Management - BAGOPS POLRES Samosir';
include '../includes/components/header.php';

require '../core/schedule_manager.php';
require '../api/google_calendar_api.php';

$scheduleManager = new ScheduleManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_schedule':
                $result = $scheduleManager->createSchedule($_POST);
                echo json_encode($result);
                exit;
                
            case 'update_schedule':
                $result = $scheduleManager->updateSchedule($_POST['schedule_id'], $_POST);
                echo json_encode($result);
                exit;
                
            case 'delete_schedule':
                $result = $scheduleManager->deleteSchedule($_POST['schedule_id']);
                echo json_encode($result);
                exit;
                
            case 'get_schedules':
                $result = $scheduleManager->getSchedules($_POST);
                echo json_encode($result);
                exit;
                
            case 'sync_to_google':
                // Handle Google Calendar sync
                echo json_encode(['success' => false, 'error' => 'Google Calendar not configured yet']);
                exit;
        }
    }
}

// Get data for dashboard
$personilData = $scheduleManager->getPersonilFromAPI();
$bagianData = $scheduleManager->getBagianList();
$upcomingSchedules = $scheduleManager->getSchedules([
    'date_from' => date('Y-m-d'),
    'date_to' => date('Y-m-d', strtotime('+7 days'))
]);

// Add data source info
$dataSource = [
    'personil_source' => $personilData['success'] ? 'API (Database)' : 'JSON File (Fallback)',
    'bagian_source' => $bagianData['success'] ? 'API (Database)' : 'JSON File (Fallback)',
    'total_personil' => $personilData['total'] ?? count($personilData['personil'] ?? []),
    'total_bagian' => $bagianData['total'] ?? count($bagianData['bagian'] ?? [])
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - BAGOPS Polres Samosir</title>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    <!-- Bootstrap CSS -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    
    <!-- Font Awesome -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3949ab;
            --accent-color: #ffd700;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8eaf6 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stats-card .label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .fc-event-title {
            font-weight: 600;
        }
        
        .shift-pagi { background-color: #4285F4 !important; border-color: #4285F4 !important; }
        .shift-siang { background-color: #EA4335 !important; border-color: #EA4335 !important; }
        .shift-malam { background-color: #FBBC04 !important; border-color: #FBBC04 !important; color: #000 !important; }
        .shift-full_day { background-color: #34A853 !important; border-color: #34A853 !important; }
        .shift-cuti { background-color: #FF6F00 !important; border-color: #FF6F00 !important; }
        .shift-lebur { background-color: #9E9E9E !important; border-color: #9E9E9E !important; }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .calendar-sync-btn {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .calendar-sync-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 133, 243, 0.3);
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .quick-action-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-action-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .stats-card .number {
                font-size: 2rem;
            }
            
            .quick-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body?>
<div class="container-fluid mt-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number" id="totalPersonil">0</div>
                <div class="label">Total Personil</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number" id="totalBagian">0</div>
                <div class="label">Total Bagian</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number" id="schedulesToday">0</div>
                <div class="label">Jadwal Hari Ini</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number" id="schedulesWeek">0</div>
                <div class="label">Jadwal 7 Hari</div>
            </div>
        </div>
    </div>

    <!-- Data Source Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Sumber Data:</strong><br>
                Personil: <?php echo $dataSource['personil_source']; ?> (<?php echo $dataSource['total_personil']; ?> personil)<br>
                Bagian: <?php echo $dataSource['bagian_source']; ?> (<?php echo $dataSource['total_bagian']; ?> bagian)<br>
                <small class="text-muted">Data diambil langsung dari database real-time</small>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="quick-action-btn" onclick="openScheduleModal()">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
        </button>
        <button class="quick-action-btn" onclick="openOperationModal()">
            <i class="fas fa-tasks me-1"></i> Tambah Operasi
        </button>
        <button class="quick-action-btn" onclick="exportSchedule()">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button class="calendar-sync-btn" onclick="syncWithGoogle()">
            <i class="fab fa-google me-1"></i> Sync Google Calendar
        </button>
    </div>

    <div class="row">
        <!-- Calendar -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar me-2"></i>
                    Kalender Jadwal
                </div>
                <div class="card-body">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Upcoming Schedules -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i>
                    Jadwal Mendatang
                </div>
                <div class="card-body">
                    <div id="upcomingSchedules">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>
                    Statistik Shift
                </div>
                <div class="card-body">
                    <canvas id="shiftChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/components/footer.php'; ?>

    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <span id="scheduleModalTitle">Tambah Jadwal</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <input type="hidden" id="scheduleId" name="schedule_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="personilSelect" class="form-label">Personil</label>
                                    <select class="form-select" id="personilSelect" name="personil_id" required>
                                        <option value="">Pilih Personil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shiftType" class="form-label">Shift</label>
                                    <select class="form-select" id="shiftType" name="shift_type" required>
                                        <option value="">Pilih Shift</option>
                                        <option value="PAGI">Pagi (06:00-14:00)</option>
                                        <option value="SIANG">Siang (14:00-22:00)</option>
                                        <option value="MALAM">Malam (22:00-06:00)</option>
                                        <option value="FULL_DAY">Full Day</option>
                                        <option value="CUTI">Cuti</option>
                                        <option value="LEBUR">Lembur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduleDate" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="scheduleDate" name="shift_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Mako Polres, Patrol Route, etc.">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
    
    <script>
        // Global variables
        let calendar;
        let personilData = <?php echo json_encode($personilData); ?>;
        let upcomingData = <?php echo json_encode($upcomingSchedules); ?>;
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeStats();
            initializeCalendar();
            populatePersonilSelect();
            loadUpcomingSchedules();
            initializeShiftChart();
        });
        
        function initializeStats() {
            if (personilData.success) {
                document.getElementById('totalPersonil').textContent = personilData.personil.length;
            }
            
            const bagianData = <?php echo json_encode($bagianData); ?>;
            if (bagianData.success) {
                document.getElementById('totalBagian').textContent = bagianData.bagian.length;
            }
            
            if (upcomingData.success) {
                const today = new Date().toISOString().split('T')[0];
                const todaySchedules = upcomingData.schedules.filter(s => s.shift_date === today);
                document.getElementById('schedulesToday').textContent = todaySchedules.length;
                document.getElementById('schedulesWeek').textContent = upcomingData.schedules.length;
            }
        }
        
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'id',
                timeZone: 'Asia/Jakarta',
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('calendar_dashboard.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'get_schedules',
                            date_from: fetchInfo.startStr,
                            date_to: fetchInfo.endStr
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const events = data.schedules.map(schedule => {
                                const startDateTime = schedule.shift_date + 'T' + schedule.start_time;
                                let endDateTime = schedule.shift_date + 'T' + schedule.end_time;
                                
                                // Handle overnight shifts
                                if (schedule.end_time < schedule.start_time) {
                                    const endDate = new Date(schedule.shift_date);
                                    endDate.setDate(endDate.getDate() + 1);
                                    endDateTime = endDate.toISOString().split('T')[0] + 'T' + schedule.end_time;
                                }
                                
                                return {
                                    id: schedule.id,
                                    title: schedule.personil_name + ' - ' + schedule.shift_type,
                                    start: startDateTime,
                                    end: endDateTime,
                                    backgroundColor: getShiftColor(schedule.shift_type),
                                    extendedProps: {
                                        personil_id: schedule.personil_id,
                                        bagian: schedule.bagian,
                                        location: schedule.location,
                                        description: schedule.description,
                                        google_event_id: schedule.google_event_id
                                    }
                                };
                            });
                            successCallback(events);
                        } else {
                            failureCallback(data.error);
                        }
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
                },
                eventClick: function(info) {
                    showScheduleDetails(info.event);
                },
                eventDrop: function(info) {
                    updateScheduleDate(info.event);
                }
            });
            
            calendar.render();
        }
        
        function getShiftColor(shiftType) {
            const colors = {
                'PAGI': '#4285F4',
                'SIANG': '#EA4335',
                'MALAM': '#FBBC04',
                'FULL_DAY': '#34A853',
                'CUTI': '#FF6F00',
                'LEBUR': '#9E9E9E'
            };
            return colors[shiftType] || '#4285F4';
        }
        
        function populatePersonilSelect() {
            if (personilData.success) {
                const select = document.getElementById('personilSelect');
                personilData.personil.forEach(personil => {
                    const option = document.createElement('option');
                    option.value = personil.id;
                    option.textContent = `${personil.name} - ${personil.pangkat} (${personil.bagian})`;
                    option.dataset.name = personil.name;
                    option.dataset.bagian = personil.bagian;
                    select.appendChild(option);
                });
            }
        }
        
        function loadUpcomingSchedules() {
            const container = document.getElementById('upcomingSchedules');
            
            if (!upcomingData.success || upcomingData.schedules.length === 0) {
                container.innerHTML = '<p class="text-muted">Tidak ada jadwal mendatang</p>';
                return;
            }
            
            let html = '';
            upcomingData.schedules.slice(0, 5).forEach(schedule => {
                const date = new Date(schedule.shift_date);
                const dateStr = date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
                
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <div class="fw-bold">${schedule.personil_name}</div>
                            <small class="text-muted">${dateStr} - ${schedule.shift_type}</small>
                        </div>
                        <span class="badge" style="background-color: ${getShiftColor(schedule.shift_type)}">
                            ${schedule.shift_type}
                        </span>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function initializeShiftChart() {
            const ctx = document.getElementById('shiftChart').getContext('2d');
            
            if (!upcomingData.success) {
                return;
            }
            
            const shiftCounts = {};
            upcomingData.schedules.forEach(schedule => {
                shiftCounts[schedule.shift_type] = (shiftCounts[schedule.shift_type] || 0) + 1;
            });
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(shiftCounts),
                    datasets: [{
                        data: Object.values(shiftCounts),
                        backgroundColor: Object.keys(shiftCounts).map(shift => getShiftColor(shift)),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function openScheduleModal() {
            document.getElementById('scheduleModalTitle').textContent = 'Tambah Jadwal';
            document.getElementById('scheduleForm').reset();
            document.getElementById('scheduleId').value = '';
            
            // Set default date to today
            document.getElementById('scheduleDate').value = new Date().toISOString().split('T')[0];
            
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        }
        
        function saveSchedule() {
            const form = document.getElementById('scheduleForm');
            const formData = new FormData(form);
            
            // Get personil details
            const personilSelect = document.getElementById('personilSelect');
            const selectedOption = personilSelect.options[personilSelect.selectedIndex];
            
            formData.append('action', 'create_schedule');
            formData.append('personil_name', selectedOption.dataset.name);
            formData.append('bagian', selectedOption.dataset.bagian);
            
            // Set times based on shift type
            const shiftType = formData.get('shift_type');
            const shiftTimes = {
                'PAGI': ['06:00', '14:00'],
                'SIANG': ['14:00', '22:00'],
                'MALAM': ['22:00', '06:00'],
                'FULL_DAY': ['00:00', '23:59'],
                'CUTI': ['00:00', '23:59'],
                'LEBUR': ['18:00', '23:59']
            };
            
            if (shiftTimes[shiftType]) {
                formData.append('start_time', shiftTimes[shiftType][0]);
                formData.append('end_time', shiftTimes[shiftType][1]);
            }
            
            fetch('calendar_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                    calendar.refetchEvents();
                    loadUpcomingSchedules();
                    initializeStats();
                    showAlert('success', 'Jadwal berhasil disimpan!');
                } else {
                    showAlert('danger', 'Error: ' + data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error: ' + error);
            });
        }
        
        function showScheduleDetails(event) {
            // Implement schedule details modal
            console.log('Show details for:', event.title);
        }
        
        function updateScheduleDate(event) {
            // Implement schedule date update
            console.log('Update schedule date:', event.id, event.start);
        }
        
        function syncWithGoogle() {
            showAlert('info', 'Google Calendar sync akan segera tersedia...');
        }
        
        function exportSchedule() {
            showAlert('info', 'Export fitur akan segera tersedia...');
        }
        
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
