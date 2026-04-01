<?php
// Start output buffering if not already started
if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Start session using SessionManager
SessionManager::start();

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
        
        .simple-calendar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 8px;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px;
            background: white;
            min-height: 80px;
            overflow-y: auto;
        }
        
        .calendar-day:hover {
            background: #f8f9fa;
            cursor: pointer;
        }
        
        .calendar-day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .calendar-event {
            font-size: 0.7rem;
            padding: 2px 4px;
            margin: 1px 0;
            border-radius: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .shift-pagi { background-color: #4285F4; color: white; }
        .shift-siang { background-color: #EA4335; color: white; }
        .shift-malam { background-color: #FBBC04; color: black; }
        .shift-full_day { background-color: #34A853; color: white; }
        .shift-cuti { background-color: #FF6F00; color: white; }
        .shift-lebur { background-color: #9E9E9E; color: white; }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
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
        
        .schedule-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 8px;
            border-left: 4px solid;
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .stats-card .number {
                font-size: 2rem;
            }
            
            .quick-actions {
                justify-content: center;
            }
            
            .calendar-grid {
                gap: 5px;
            }
            
            .calendar-day {
                min-height: 60px;
                padding: 5px;
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
                <div class="number"><?php echo $dataSource['total_personil']; ?></div>
                <div class="label">Total Personil</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo $dataSource['total_bagian']; ?></div>
                <div class="label">Total Bagian</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo count($upcomingSchedules['schedules'] ?? []); ?></div>
                <div class="label">Jadwal 7 Hari</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo date('d'); ?></div>
                <div class="label">Hari Ini</div>
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
        <button class="quick-action-btn" onclick="refreshCalendar()">
            <i class="fas fa-sync me-1"></i> Refresh
        </button>
        <button class="quick-action-btn" onclick="exportSchedule()">
            <i class="fas fa-download me-1"></i> Export
        </button>
    </div>

    <div class="row">
        <!-- Simple Calendar -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar me-2"></i>
                    Kalender Jadwal (Simplified)
                </div>
                <div class="card-body">
                    <div class="simple-calendar">
                        <div class="calendar-header">
                            <button class="btn btn-sm btn-light" onclick="changeMonth(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <h4 id="currentMonth">November 2025</h4>
                            <button class="btn btn-sm btn-light" onclick="changeMonth(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar will be rendered here by JavaScript -->
                        </div>
                    </div>
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
                        <?php if (!empty($upcomingSchedules['schedules'])): ?>
                            <?php foreach (array_slice($upcomingSchedules['schedules'], 0, 5) as $schedule): ?>
                                <div class="schedule-item" style="border-left-color: <?php echo getShiftColor($schedule['shift_type'] ?? 'PAGI'); ?>">
                                    <div class="fw-bold"><?php echo htmlspecialchars($schedule['personil_name'] ?? 'Unknown'); ?></div>
                                    <small class="text-muted">
                                        <?php echo date('d M', strtotime($schedule['shift_date'] ?? 'today')); ?> - 
                                        <?php echo htmlspecialchars($schedule['shift_type'] ?? 'Unknown'); ?>
                                    </small>
                                    <?php if (!empty($schedule['location'])): ?>
                                        <br><small><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($schedule['location']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada jadwal mendatang</p>
                        <?php endif; ?>
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
                    <div id="shiftStats">
                        <?php
                        $shiftCounts = [];
                        if (!empty($upcomingSchedules['schedules'])) {
                            foreach ($upcomingSchedules['schedules'] as $schedule) {
                                $shiftType = $schedule['shift_type'] ?? 'Unknown';
                                $shiftCounts[$shiftType] = ($shiftCounts[$shiftType] ?? 0) + 1;
                            }
                        }
                        
                        foreach ($shiftCounts as $shift => $count):
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background-color: <?php echo getShiftColor($shift); ?>">
                                <?php echo htmlspecialchars($shift); ?>
                            </span>
                            <span class="fw-bold"><?php echo $count; ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($shiftCounts)): ?>
                            <p class="text-muted">Tidak ada data shift</p>
                        <?php endif; ?>
                    </div>
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
                                        <?php if (!empty($personilData['personil'])): ?>
                                            <?php foreach ($personilData['personil'] as $personil): ?>
                                                <option value="<?php echo $personil['id']; ?>">
                                                    <?php echo htmlspecialchars($personil['name'] ?? 'Unknown'); ?> - 
                                                    <?php echo htmlspecialchars($personil['pangkat'] ?? 'N/A'); ?> 
                                                    (<?php echo htmlspecialchars($personil['bagian'] ?? 'N/A'); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

    <script>
        // Global variables
        let currentDate = new Date();
        let schedulesData = <?php echo safe_json_encode($upcomingSchedules); ?>;
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar();
            initializeModal();
        });
        
        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update month header
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('currentMonth').textContent = monthNames[month] + ' ' + year;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            // Build calendar grid
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';
            
            // Day headers
            const dayHeaders = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            dayHeaders.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day-header';
                dayHeader.textContent = day;
                calendarGrid.appendChild(dayHeader);
            });
            
            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                calendarGrid.appendChild(emptyDay);
            }
            
            // Days of month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                
                const dayNumber = document.createElement('div');
                dayNumber.className = 'calendar-day-number';
                dayNumber.textContent = day;
                dayElement.appendChild(dayNumber);
                
                // Add schedules for this day
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const daySchedules = (schedulesData.schedules || []).filter(s => s.shift_date === dateStr);
                
                daySchedules.forEach(schedule => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = `calendar-event shift-${(schedule.shift_type || 'pagi').toLowerCase()}`;
                    eventDiv.textContent = `${schedule.personil_name || 'Unknown'} - ${schedule.shift_type || 'Unknown'}`;
                    eventDiv.title = `${schedule.personil_name || 'Unknown'} - ${schedule.shift_type || 'Unknown'}`;
                    dayElement.appendChild(eventDiv);
                });
                
                // Highlight today
                const today = new Date();
                if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                    dayElement.style.border = '2px solid var(--primary-color)';
                    dayElement.style.background = '#f0f4ff';
                }
                
                calendarGrid.appendChild(dayElement);
            }
        }
        
        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            renderCalendar();
        }
        
        function initializeModal() {
            // Set default date to today
            document.getElementById('scheduleDate').value = new Date().toISOString().split('T')[0];
        }
        
        function openScheduleModal() {
            document.getElementById('scheduleModalTitle').textContent = 'Tambah Jadwal';
            document.getElementById('scheduleForm').reset();
            document.getElementById('scheduleId').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        }
        
        function saveSchedule() {
            const form = document.getElementById('scheduleForm');
            const formData = new FormData(form);
            
            formData.append('action', 'create_schedule');
            
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
                    showAlert('success', 'Jadwal berhasil disimpan!');
                    refreshCalendar();
                } else {
                    showAlert('danger', 'Error: ' + data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error: ' + error);
            });
        }
        
        function refreshCalendar() {
            location.reload();
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

<?php
function getShiftColor($shiftType) {
    $colors = [
        'PAGI' => '#4285F4',
        'SIANG' => '#EA4335',
        'MALAM' => '#FBBC04',
        'FULL_DAY' => '#34A853',
        'CUTI' => '#FF6F00',
        'LEBUR' => '#9E9E9E'
    ];
    return $colors[$shiftType] ?? '#4285F4';
}
?>
