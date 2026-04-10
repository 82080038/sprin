<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Schedule Management - BAGOPS POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

require __DIR__ . '/../core/schedule_manager.php';
require __DIR__ . '/../api/google_calendar_api.php';

$scheduleManager = new ScheduleManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean output buffer to prevent any HTML/whitespace before JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    
    // All CRUD operations are now handled by the API
    // Redirect to API for all actions
    exit;
}

// Get data for dashboard with error handling
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

// Safe JSON encoding function
function safe_json_encode($data) {
    $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        // Fallback if JSON encoding fails
        return json_encode([
            'success' => false,
            'error' => 'JSON encoding failed: ' . json_last_error_msg(),
            'data' => []
        ]);
    }
    return $json;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - BAGOPS Polres Samosir</title>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
    
    <!-- Bootstrap CSS -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
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
        
        .fc-event { cursor: pointer; }

        /* ── View Tahunan (multiMonthYear) ── */
        .fc-multimonth-header-table { font-size: 0.8rem; }
        .fc-multimonth-title { font-weight: 700; font-size: 0.9rem; color: #1a237e; }
        .fc-multimonth-daygrid-table .fc-daygrid-day-number { font-size: 0.75rem; }
        .fc .fc-multimonth { border: none; }
        .fc .fc-multimonth-month { border: 1px solid #dee2e6; border-radius: 6px; margin: 4px; }

        /* ── Toolbar buttons ── */
        .fc .fc-button { font-size: 0.82rem; padding: 4px 10px; }
        .fc .fc-button-primary { background-color: #1a237e; border-color: #1a237e; }
        .fc .fc-button-primary:hover { background-color: #3949ab; border-color: #3949ab; }
        .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #0d47a1; border-color: #0d47a1; }
        
        .detail-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .operation-card {
            border-left: 4px solid var(--primary-color);
        }
        
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
<body>
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

    <!-- Schedule Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i> Detail Jadwal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="detailTitle" class="fw-bold mb-3"></h6>
                    <div class="mb-2" id="detailShift"></div>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:120px"><i class="fas fa-calendar me-1"></i>Tanggal</td>
                                <td id="detailDate" class="fw-semibold"></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-clock me-1"></i>Waktu</td>
                                <td id="detailTime"></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Lokasi</td>
                                <td id="detailLocation"></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-align-left me-1"></i>Deskripsi</td>
                                <td id="detailDescription"></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-tag me-1"></i>Status</td>
                                <td id="detailStatus"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" id="detailEditBtn">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-danger" id="detailDeleteBtn">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
    
    <script>
        // Global variables with safe JSON parsing
        let calendar;
        let personilData;
        let upcomingData;
        let bagianData;
        
        try {
            personilData = <?php echo safe_json_encode($personilData); ?>;
        } catch(e) {
            console.error('Error parsing personilData:', e);
            personilData = {success: false, personil: []};
        }
        
        try {
            upcomingData = <?php echo safe_json_encode($upcomingSchedules); ?>;
        } catch(e) {
            console.error('Error parsing upcomingData:', e);
            upcomingData = {success: false, schedules: []};
        }
        
        try {
            bagianData = <?php echo safe_json_encode($bagianData); ?>;
        } catch(e) {
            console.error('Error parsing bagianData:', e);
            bagianData = {success: false, bagian: []};
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeStats();
            initializeCalendar();
            populatePersonilSelect();
            loadUpcomingSchedules();
            initializeShiftChart();
            refreshLiveStats();
        });
        
        function initializeStats() {
            if (personilData && personilData.success) {
                document.getElementById('totalPersonil').textContent = personilData.personil ? personilData.personil.length : 0;
            }
            
            if (bagianData && bagianData.success) {
                document.getElementById('totalBagian').textContent = bagianData.bagian ? bagianData.bagian.length : 0;
            }
            
            if (upcomingData && upcomingData.success) {
                const today = new Date().toISOString().split('T')[0];
                const todaySchedules = upcomingData.schedules ? upcomingData.schedules.filter(s => s.shift_date === today) : [];
                document.getElementById('schedulesToday').textContent = todaySchedules.length;
                document.getElementById('schedulesWeek').textContent = upcomingData.schedules ? upcomingData.schedules.length : 0;
            }
        }
        
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listYear'
                },
                views: {
                    multiMonthYear: {
                        buttonText: 'Tahun',
                        multiMonthMaxColumns: 3
                    },
                    dayGridMonth: { buttonText: 'Bulan' },
                    timeGridWeek: { buttonText: 'Minggu' },
                    timeGridDay:  { buttonText: 'Hari' },
                    listYear:     { buttonText: 'Agenda Tahun' }
                },
                locale: 'id',
                timeZone: 'Asia/Jakarta',
                editable: true,
                eventDidMount: function(info) {
                    info.el.setAttribute('title',
                        info.event.title + (info.event.extendedProps.location ? '\nLokasi: ' + info.event.extendedProps.location : '')
                    );
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('../api/calendar_api_public.php', {
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
                    .then(response => {
                        // Check if response is actually JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Invalid response format');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            const events = (data.schedules || []).map(schedule => {
                                const startDateTime = (schedule.shift_date || '') + 'T' + (schedule.start_time || '00:00');
                                let endDateTime = (schedule.shift_date || '') + 'T' + (schedule.end_time || '23:59');
                                
                                // Handle overnight shifts
                                if ((schedule.end_time || '23:59') < (schedule.start_time || '00:00')) {
                                    const endDate = new Date(schedule.shift_date || new Date());
                                    endDate.setDate(endDate.getDate() + 1);
                                    endDateTime = endDate.toISOString().split('T')[0] + 'T' + (schedule.end_time || '23:59');
                                }
                                
                                return {
                                    id: schedule.id || Date.now(),
                                    title: (schedule.personil_name || 'Unknown') + ' - ' + (schedule.shift_type || 'Unknown'),
                                    start: startDateTime,
                                    end: endDateTime,
                                    backgroundColor: getShiftColor(schedule.shift_type),
                                    extendedProps: {
                                        personil_id: schedule.personil_id,
                                        bagian: schedule.bagian,
                                        location: schedule.location,
                                        description: schedule.description,
                                        google_event_id: schedule.google_event_id,
                                        shift_type: schedule.shift_type,
                                        status: schedule.status
                                    }
                                };
                            });
                            successCallback(events);
                        } else {
                            failureCallback(data?.error || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Calendar fetch error:', error);
                        failureCallback(error.message || 'Failed to load calendar data');
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
            if (personilData && personilData.success && personilData.personil) {
                const select = document.getElementById('personilSelect');
                personilData.personil.forEach(personil => {
                    const option = document.createElement('option');
                    option.value = personil.id;
                    option.textContent = `${personil.name || 'Unknown'} - ${personil.pangkat || 'N/A'} (${personil.bagian || 'N/A'})`;
                    option.dataset.name = personil.name || 'Unknown';
                    option.dataset.bagian = personil.bagian || 'N/A';
                    select.appendChild(option);
                });
            }
        }
        
        function loadUpcomingSchedules() {
            const container = document.getElementById('upcomingSchedules');
            
            if (!upcomingData || !upcomingData.success || !upcomingData.schedules || upcomingData.schedules.length === 0) {
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
                            <div class="fw-bold">${schedule.personil_name || 'Unknown'}</div>
                            <small class="text-muted">${dateStr} - ${schedule.shift_type || 'Unknown'}</small>
                        </div>
                        <span class="badge" style="background-color: ${getShiftColor(schedule.shift_type)}">
                            ${schedule.shift_type || 'Unknown'}
                        </span>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function initializeShiftChart() {
            const ctx = document.getElementById('shiftChart').getContext('2d');
            
            if (!upcomingData || !upcomingData.success || !upcomingData.schedules) {
                return;
            }
            
            const shiftCounts = {};
            upcomingData.schedules.forEach(schedule => {
                const shiftType = schedule.shift_type || 'Unknown';
                shiftCounts[shiftType] = (shiftCounts[shiftType] || 0) + 1;
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
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const formData = new FormData(form);
            
            const personilSelect = document.getElementById('personilSelect');
            const selectedOption = personilSelect.options[personilSelect.selectedIndex];
            
            const scheduleId = document.getElementById('scheduleId').value;
            formData.append('action', scheduleId ? 'update_schedule' : 'create_schedule');
            if (scheduleId) formData.append('schedule_id', scheduleId);
            formData.append('personil_name', selectedOption.dataset.name || '');
            formData.append('bagian', selectedOption.dataset.bagian || '');
            
            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                    calendar.refetchEvents();
                    refreshLiveStats();
                    showAlert('success', scheduleId ? 'Jadwal berhasil diupdate!' : 'Jadwal berhasil disimpan!');
                } else {
                    showAlert('danger', 'Error: ' + (data.error || data.message));
                }
            })
            .catch(error => {
                showAlert('danger', 'Error: ' + error);
            });
        }
        
        function showScheduleDetails(event) {
            const props = event.extendedProps;
            const shiftColors = {
                'PAGI': '#4285F4', 'SIANG': '#EA4335', 'MALAM': '#FBBC04',
                'FULL_DAY': '#34A853', 'CUTI': '#FF6F00', 'LEBUR': '#9E9E9E'
            };
            const shiftType = props.shift_type || event.title.split(' - ').pop();
            const color = shiftColors[shiftType] || '#4285F4';

            document.getElementById('detailTitle').textContent = event.title;
            document.getElementById('detailShift').innerHTML =
                `<span class="badge detail-badge" style="background:${color}">${shiftType}</span>`;
            document.getElementById('detailDate').textContent =
                new Date(event.start).toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
            document.getElementById('detailTime').textContent =
                event.start.toTimeString().slice(0,5) + ' - ' + (event.end ? event.end.toTimeString().slice(0,5) : '-');
            document.getElementById('detailLocation').textContent = props.location || '-';
            document.getElementById('detailDescription').textContent = event.extendedProps.description || '-';
            document.getElementById('detailStatus').textContent = props.status || 'scheduled';

            const editBtn = document.getElementById('detailEditBtn');
            const deleteBtn = document.getElementById('detailDeleteBtn');
            editBtn.onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                openEditScheduleModal(event.id);
            };
            deleteBtn.onclick = function() {
                if (confirm('Hapus jadwal "' + event.title + '"?')) {
                    deleteSchedule(event.id);
                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                }
            };

            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }

        function openEditScheduleModal(scheduleId) {
            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'get_schedule', schedule_id: scheduleId})
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showAlert('danger', data.error || 'Gagal memuat data'); return; }
                const s = data.schedule;
                document.getElementById('scheduleModalTitle').textContent = 'Edit Jadwal';
                document.getElementById('scheduleId').value = s.id;
                document.getElementById('personilSelect').value = s.personil_id;
                document.getElementById('shiftType').value = s.shift_type;
                document.getElementById('scheduleDate').value = s.shift_date;
                document.getElementById('location').value = s.location || '';
                document.getElementById('description').value = s.description || '';
                new bootstrap.Modal(document.getElementById('scheduleModal')).show();
            })
            .catch(() => showAlert('danger', 'Gagal memuat data jadwal'));
        }

        function deleteSchedule(scheduleId) {
            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'delete_schedule', schedule_id: scheduleId})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    refreshLiveStats();
                    showAlert('success', 'Jadwal berhasil dihapus');
                } else {
                    showAlert('danger', 'Gagal menghapus: ' + (data.error || data.message));
                }
            });
        }

        function updateScheduleDate(event) {
            const newDate = event.start.toISOString().split('T')[0];
            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'update_schedule',
                    schedule_id: event.id,
                    shift_date: newDate
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Jadwal dipindahkan ke ' + new Date(newDate).toLocaleDateString('id-ID'));
                    refreshLiveStats();
                } else {
                    showAlert('danger', 'Gagal memindahkan jadwal: ' + (data.error || data.message));
                    calendar.refetchEvents();
                }
            })
            .catch(() => { showAlert('danger', 'Terjadi kesalahan jaringan'); calendar.refetchEvents(); });
        }

        function openOperationModal() {
            window.location.href = '../pages/operasi.php?tambah=1';
        }

        function syncWithGoogle() {
            showAlert('info', 'Google Calendar sync akan segera tersedia...');
        }

        function exportSchedule() {
            const now = new Date();
            const firstDay = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-01';
            const lastDay  = new Date(now.getFullYear(), now.getMonth()+1, 0).toISOString().split('T')[0];

            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'export_csv', date_from: firstDay, date_to: lastDay})
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showAlert('danger', 'Gagal export: ' + (data.error || data.message)); return; }
                const blob = new Blob([data.csv], {type: 'text/csv;charset=utf-8;'});
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = data.filename;
                link.click();
                URL.revokeObjectURL(link.href);
                showAlert('success', 'Export berhasil: ' + data.filename);
            })
            .catch(err => showAlert('danger', 'Error export: ' + err));
        }

        function refreshLiveStats() {
            fetch('../api/calendar_api_public.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'get_live_stats'})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('schedulesToday').textContent = data.stats.today_schedules;
                    document.getElementById('schedulesWeek').textContent  = data.stats.week_schedules;
                }
            });
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
