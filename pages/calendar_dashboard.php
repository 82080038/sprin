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

// Setup page header
$page_header = [
    'title' => 'Dashboard Kalender',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => BASE_URL . '/pages/main.php'],
        ['text' => 'Dashboard Kalender', 'active' => true]
    ]
];

// Include Bootstrap layout
include __DIR__ . '/../includes/components/bootstrap_layout.php';

// Mock schedule manager for testing
$scheduleManager = new stdClass();
$scheduleManager->getEventsForMonth = function($month) {
    return [
        [
            'id' => 1,
            'title' => 'Operasi Kewilayahan',
            'start' => date('Y-m-d', strtotime('first day of this month')),
            'end' => date('Y-m-d', strtotime('first day of this month + 3 days')),
            'type' => 'operasi',
            'description' => 'Operasi kewilayahan rutin'
        ],
        [
            'id' => 2,
            'title' => 'Piket Siang',
            'start' => date('Y-m-d', strtotime('first day of this month + 5 days')),
            'end' => date('Y-m-d', strtotime('first day of this month + 5 days')),
            'type' => 'piket',
            'description' => 'Jadwal piket siang'
        ]
    ];
};

$scheduleManager->getPiketSchedule = function($month) {
    return [
        [
            'date' => date('Y-m-d'),
            'members' => [
                ['nama' => 'Personil A'],
                ['nama' => 'Personil B']
            ]
        ]
    ];
};

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'get_events':
                $month = $_POST['month'] ?? date('Y-m');
                $events = $scheduleManager->getEventsForMonth($month);
                echo json_encode(['success' => true, 'events' => $events]);
                break;
                
            case 'add_event':
                $eventData = [
                    'title' => $_POST['title'] ?? '',
                    'start' => $_POST['start'] ?? '',
                    'end' => $_POST['end'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'type' => $_POST['type'] ?? 'operasi'
                ];
                $result = $scheduleManager->addEvent($eventData);
                echo json_encode($result);
                break;
                
            case 'update_event':
                $eventId = $_POST['id'] ?? 0;
                $eventData = [
                    'title' => $_POST['title'] ?? '',
                    'start' => $_POST['start'] ?? '',
                    'end' => $_POST['end'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'type' => $_POST['type'] ?? 'operasi'
                ];
                $result = $scheduleManager->updateEvent($eventId, $eventData);
                echo json_encode($result);
                break;
                
            case 'delete_event':
                $eventId = $_POST['id'] ?? 0;
                $result = $scheduleManager->deleteEvent($eventId);
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get current month data
$currentMonth = date('Y-m');
$events = $scheduleManager->getEventsForMonth($currentMonth);
$piketSchedule = $scheduleManager->getPiketSchedule($currentMonth);
?>

<style>
.calendar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    text-align: center;
}

.calendar-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.calendar-controls {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.calendar-container {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

#calendar {
    height: 600px;
}

.piket-schedule {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.piket-schedule .card-header {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
    font-weight: 600;
    border: none;
}

.piket-team {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.piket-team:last-child {
    border-bottom: none;
}

.piket-date {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.piket-members {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.member-badge {
    background: var(--light-color);
    color: var(--dark-color);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
}

.event-legend {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .calendar-header h2 {
        font-size: 2rem;
    }
    
    #calendar {
        height: 400px;
    }
    
    .piket-members {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<div class="container">
    <!-- Calendar Header -->
    <div class="calendar-header">
        <h2><i class="fas fa-calendar-alt me-3"></i>Dashboard Kalender</h2>
        <p class="mb-0">Manajemen jadwal operasi dan piket kepolisian</p>
    </div>

    <!-- Calendar Controls -->
    <div class="calendar-controls">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-primary" id="currentMonth">
                        <?php echo date('F Y'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus me-2"></i>Tambah Event
                </button>
                <button type="button" class="btn btn-info" id="refreshCalendar">
                    <i class="fas fa-sync me-2"></i>Refresh
                </button>
            </div>
        </div>
        
        <!-- Event Legend -->
        <div class="event-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #dc3545;"></div>
                <span>Operasi</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #198754;"></div>
                <span>Piket</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #0dcaf0;"></div>
                <span>Latihan</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ffc107;"></div>
                <span>Rapat</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6c757d;"></div>
                <span>Lainnya</span>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>

    <!-- Piket Schedule -->
    <div class="piket-schedule">
        <div class="card-header">
            <i class="fas fa-users me-2"></i>Jadwal Piket Bulan Ini
        </div>
        <div class="card-body">
            <?php if (!empty($piketSchedule)): ?>
                <?php foreach ($piketSchedule as $schedule): ?>
                    <div class="piket-team">
                        <div class="piket-date">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo date('d F Y', strtotime($schedule['date'])); ?>
                        </div>
                        <div class="piket-members">
                            <?php foreach ($schedule['members'] as $member): ?>
                                <span class="member-badge">
                                    <?php echo htmlspecialchars($member['nama']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <p>Belum ada jadwal piket untuk bulan ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Event Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEventForm">
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">Judul Event</label>
                        <input type="text" class="form-control" id="eventTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="eventType" class="form-label">Tipe Event</label>
                        <select class="form-select" id="eventType" name="type" required>
                            <option value="operasi">Operasi</option>
                            <option value="piket">Piket</option>
                            <option value="latihan">Latihan</option>
                            <option value="rapat">Rapat</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventStart" class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" class="form-control" id="eventStart" name="start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventEnd" class="form-label">Tanggal Selesai</label>
                            <input type="datetime-local" class="form-control" id="eventEnd" name="end" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/bootstrap_layout_footer.php'; ?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    let currentMonth = '<?php echo $currentMonth; ?>';
    
    // Initialize calendar with proper configuration
    if (typeof FullCalendar !== 'undefined') {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'id',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 600,
            events: [],
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            eventDrop: function(info) {
                updateEvent(info.event);
            },
            eventResize: function(info) {
                updateEvent(info.event);
            },
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            nowIndicator: true
        });
        
        calendar.render();
        console.log('Calendar initialized successfully');
    } else {
        console.error('FullCalendar not loaded');
        // Fallback: show simple message
        calendarEl.innerHTML = '<div class="alert alert-warning">Calendar loading failed. Please refresh the page.</div>';
    }
    
    // Load events for current month
    loadEvents(currentMonth);
    
    // Navigation controls
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth = getPreviousMonth(currentMonth);
        updateMonthDisplay();
        loadEvents(currentMonth);
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth = getNextMonth(currentMonth);
        updateMonthDisplay();
        loadEvents(currentMonth);
    });
    
    document.getElementById('currentMonth').addEventListener('click', function() {
        currentMonth = '<?php echo date('Y-m'); ?>';
        updateMonthDisplay();
        loadEvents(currentMonth);
    });
    
    document.getElementById('refreshCalendar').addEventListener('click', function() {
        loadEvents(currentMonth);
    });
    
    // Save event
    document.getElementById('saveEventBtn').addEventListener('click', function() {
        saveEvent();
    });
    
    function loadEvents(month) {
        const formData = new FormData();
        formData.append('action', 'get_events');
        formData.append('month', month);
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const events = data.events.map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    backgroundColor: getEventColor(event.type),
                    borderColor: getEventColor(event.type),
                    extendedProps: {
                        description: event.description,
                        type: event.type
                    }
                }));
                calendar.removeAllEvents();
                calendar.addEventSource(events);
            }
        })
        .catch(error => {
            console.error('Error loading events:', error);
            toastr.error('Gagal memuat events');
        });
    }
    
    function getEventColor(type) {
        const colors = {
            'operasi': '#dc3545',
            'piket': '#198754',
            'latihan': '#0dcaf0',
            'rapat': '#ffc107',
            'lainnya': '#6c757d'
        };
        return colors[type] || '#6c757d';
    }
    
    function getPreviousMonth(month) {
        const date = new Date(month + '-01');
        date.setMonth(date.getMonth() - 1);
        return date.toISOString().slice(0, 7);
    }
    
    function getNextMonth(month) {
        const date = new Date(month + '-01');
        date.setMonth(date.getMonth() + 1);
        return date.toISOString().slice(0, 7);
    }
    
    function updateMonthDisplay() {
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const [year, month] = currentMonth.split('-');
        document.getElementById('currentMonth').textContent = monthNames[parseInt(month) - 1] + ' ' + year;
    }
    
    function saveEvent() {
        const form = document.getElementById('addEventForm');
        const formData = new FormData(form);
        formData.append('action', 'add_event');
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Event berhasil ditambahkan');
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                form.reset();
                loadEvents(currentMonth);
            } else {
                toastr.error(data.message || 'Gagal menambah event');
            }
        })
        .catch(error => {
            console.error('Error saving event:', error);
            toastr.error('Terjadi kesalahan saat menambah event');
        });
    }
    
    function updateEvent(event) {
        const formData = new FormData();
        formData.append('action', 'update_event');
        formData.append('id', event.id);
        formData.append('title', event.title);
        formData.append('start', event.start.toISOString());
        formData.append('end', event.end.toISOString());
        formData.append('type', event.extendedProps.type);
        formData.append('description', event.extendedProps.description || '');
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                toastr.error(data.message || 'Gagal mengupdate event');
                event.revert();
            }
        })
        .catch(error => {
            console.error('Error updating event:', error);
            toastr.error('Terjadi kesalahan saat mengupdate event');
            event.revert();
        });
    }
    
    function showEventDetails(event) {
        const title = event.title;
        const start = event.start.toLocaleString('id-ID');
        const end = event.end ? event.end.toLocaleString('id-ID') : '';
        const description = event.extendedProps.description || 'Tidak ada deskripsi';
        const type = event.extendedProps.type;
        
        Swal.fire({
            title: title,
            html: `
                <div style="text-align: left;">
                    <p><strong>Tipe:</strong> ${type}</p>
                    <p><strong>Mulai:</strong> ${start}</p>
                    ${end ? `<p><strong>Selesai:</strong> ${end}</p>` : ''}
                    <p><strong>Deskripsi:</strong> ${description}</p>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Tutup'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteEvent(event.id);
            }
        });
    }
    
    function deleteEvent(eventId) {
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('id', eventId);
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Event berhasil dihapus');
                loadEvents(currentMonth);
            } else {
                toastr.error(data.message || 'Gagal menghapus event');
            }
        })
        .catch(error => {
            console.error('Error deleting event:', error);
            toastr.error('Terjadi kesalahan saat menghapus event');
        });
    }
});
</script>
