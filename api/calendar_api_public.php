<?php
/**
 * Calendar API - Public endpoints for calendar operations
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Shift time map
$shiftTimes = [
    'PAGI'     => ['06:00:00', '14:00:00'],
    'SIANG'    => ['14:00:00', '22:00:00'],
    'MALAM'    => ['22:00:00', '06:00:00'],
    'FULL_DAY' => ['00:00:00', '23:59:00'],
    'CUTI'     => ['00:00:00', '23:59:00'],
    'LEBUR'    => ['18:00:00', '23:59:00'],
];

try {
    switch ($action) {

        // ── GET SCHEDULES (alias for FullCalendar fetch in calendar_dashboard) ──
        case 'get_schedules':
            $date_from = $_POST['date_from'] ?? $_GET['date_from'] ?? date('Y-m-01');
            $date_to   = $_POST['date_to']   ?? $_GET['date_to']   ?? date('Y-m-t');

            $stmt = $pdo->prepare("
                SELECT s.*, p.nama as personil_nama,
                       t.nama_tim
                FROM schedules s
                LEFT JOIN personil p ON s.personil_id = p.nrp
                LEFT JOIN tim_piket t ON t.id = s.tim_id
                WHERE s.shift_date >= ? AND s.shift_date <= ?
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$date_from, $date_to]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($schedules as &$s) {
                $s['personil_name'] = $s['personil_nama'] ?: $s['personil_name'];
            }
            unset($s);

            echo json_encode(['success' => true, 'schedules' => $schedules]);
            break;

        // ── CREATE SCHEDULE ──
        case 'create_schedule':
            $personil_id   = $_POST['personil_id']   ?? '';
            $personil_name = $_POST['personil_name']  ?? '';
            $bagian        = $_POST['bagian']          ?? '';
            $shift_type    = strtoupper(trim($_POST['shift_type'] ?? ''));
            $shift_date    = $_POST['shift_date']     ?? '';
            $location      = $_POST['location']       ?? '';
            $description   = $_POST['description']    ?? '';
            $recType       = $_POST['recurrence_type']     ?? 'none';
            $recInterval   = max(1, (int)($_POST['recurrence_interval'] ?? 1));
            $recEnd        = !empty($_POST['recurrence_end']) ? $_POST['recurrence_end'] : null;
            $recDays       = trim($_POST['recurrence_days'] ?? '');

            if (!$personil_id || !$shift_type || !$shift_date) {
                throw new Exception('personil_id, shift_type, dan shift_date wajib diisi');
            }

            // Deteksi konflik: personil sudah punya jadwal di tanggal tersebut
            $conflicts = [];
            if ($recType === 'none' || $recType === '') {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE personil_id=? AND shift_date=?");
                $chk->execute([$personil_id, $shift_date]);
                if ($chk->fetchColumn() > 0) $conflicts[] = $shift_date;
            }

            $times      = $shiftTimes[$shift_type] ?? ['00:00:00', '23:59:00'];
            $start_time = $_POST['start_time'] ?? $times[0];
            $end_time   = $_POST['end_time']   ?? $times[1];

            // Build list of dates
            $dates   = [];
            $current = new DateTime($shift_date);
            $end     = $recType !== 'none' && $recEnd ? new DateTime($recEnd) : clone $current;
            $limit   = 0;
            $daysArr = $recDays !== '' ? explode(',', $recDays) : [];

            while ($current <= $end && $limit < 365) {
                $dayNum = (int)$current->format('w');
                if ($recType === 'none' || $recType === '') {
                    $dates[] = $current->format('Y-m-d'); break;
                } elseif ($recType === 'daily') {
                    $dates[] = $current->format('Y-m-d');
                    $current->modify('+' . $recInterval . ' days');
                } elseif ($recType === 'weekly') {
                    if (empty($daysArr) || in_array((string)$dayNum, $daysArr)) {
                        $dates[] = $current->format('Y-m-d');
                    }
                    $current->modify('+1 day');
                    if ($current->format('w') == '1' && $recInterval > 1) {
                        $current->modify('+' . ($recInterval - 1) . ' weeks');
                    }
                } elseif ($recType === 'monthly') {
                    $dates[] = $current->format('Y-m-d');
                    $current->modify('+' . $recInterval . ' months');
                } elseif ($recType === 'yearly') {
                    $dates[] = $current->format('Y-m-d');
                    $current->modify('+' . $recInterval . ' years');
                } else {
                    $dates[] = $current->format('Y-m-d'); break;
                }
                $limit++;
            }

            $stmt = $pdo->prepare("
                INSERT INTO schedules
                    (personil_id, personil_name, bagian, shift_type, shift_date,
                     start_time, end_time, location, description,
                     recurrence_type, recurrence_interval, recurrence_days, recurrence_end)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($dates as $d) {
                $stmt->execute([
                    $personil_id, $personil_name, $bagian,
                    $shift_type, $d, $start_time, $end_time,
                    $location, $description,
                    $recType, $recInterval, $recDays ?: null, $recEnd
                ]);
            }

            echo json_encode([
                'success'   => true,
                'count'     => count($dates),
                'message'   => count($dates) . ' jadwal berhasil disimpan',
                'conflicts' => $conflicts
            ]);
            break;

        // ── UPDATE SCHEDULE (used for drag-drop and edit) ──
        case 'update_schedule':
            $schedule_id = $_POST['schedule_id'] ?? $_GET['schedule_id'] ?? null;
            if (!$schedule_id) throw new Exception('schedule_id wajib diisi');

            $fields = [];
            $values = [];
            $allowed = ['shift_date', 'shift_type', 'start_time', 'end_time',
                        'location', 'description', 'status',
                        'personil_id', 'personil_name', 'bagian'];

            foreach ($allowed as $f) {
                if (isset($_POST[$f])) {
                    $fields[] = "$f = ?";
                    $values[] = $f === 'shift_type' ? strtoupper(trim($_POST[$f])) : $_POST[$f];
                }
            }

            // Auto-update times when shift_type changes
            if (isset($_POST['shift_type']) && !isset($_POST['start_time'])) {
                $st = strtoupper(trim($_POST['shift_type']));
                if (isset($shiftTimes[$st])) {
                    $fields[] = "start_time = ?"; $values[] = $shiftTimes[$st][0];
                    $fields[] = "end_time = ?";   $values[] = $shiftTimes[$st][1];
                }
            }

            if (empty($fields)) throw new Exception('Tidak ada field yang diupdate');

            $values[] = $schedule_id;
            $stmt = $pdo->prepare("UPDATE schedules SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?");
            $stmt->execute($values);

            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            break;

        // ── DELETE SCHEDULE ──
        case 'delete_schedule':
            $schedule_id = $_POST['schedule_id'] ?? null;
            if (!$schedule_id) throw new Exception('schedule_id wajib diisi');

            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$schedule_id]);

            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
            break;

        // ── GET SINGLE SCHEDULE DETAIL ──
        case 'get_schedule':
            $schedule_id = $_POST['schedule_id'] ?? $_GET['schedule_id'] ?? null;
            if (!$schedule_id) throw new Exception('schedule_id wajib diisi');

            $stmt = $pdo->prepare("
                SELECT s.*, p.nama as personil_nama, p.pangkat as pangkat
                FROM schedules s
                LEFT JOIN personil p ON s.personil_id = p.nrp
                WHERE s.id = ?
            ");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$schedule) throw new Exception('Jadwal tidak ditemukan');

            $schedule['personil_name'] = $schedule['personil_nama'] ?: $schedule['personil_name'];
            // Fetch tim name if tim_id set
            if (!empty($schedule['tim_id'])) {
                $stT = $pdo->prepare('SELECT nama_tim FROM tim_piket WHERE id=?');
                $stT->execute([$schedule['tim_id']]);
                $schedule['nama_tim'] = $stT->fetchColumn() ?: null;
            }
            echo json_encode(['success' => true, 'schedule' => $schedule]);
            break;

        // ── GET OPERATIONS ──
        case 'get_operations':
            $date_from = $_POST['date_from'] ?? $_GET['date_from'] ?? date('Y-m-01');
            $date_to   = $_POST['date_to']   ?? $_GET['date_to']   ?? date('Y-m-t');

            $stmt = $pdo->prepare("
                SELECT o.*, COUNT(a.id) as assigned_count
                FROM operations o
                LEFT JOIN assignments a ON o.id = a.operation_id
                WHERE o.operation_date >= ? AND o.operation_date <= ?
                GROUP BY o.id
                ORDER BY o.operation_date ASC, o.start_time ASC
            ");
            $stmt->execute([$date_from, $date_to]);
            $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'operations' => $operations]);
            break;

        // ── CREATE OPERATION ──
        case 'create_operation':
            $operation_name     = trim($_POST['operation_name']  ?? '');
            $operation_month    = trim($_POST['operation_month'] ?? '');
            $operation_date     = !empty($_POST['operation_date'])     ? $_POST['operation_date']     : null;
            $operation_date_end = !empty($_POST['operation_date_end']) ? $_POST['operation_date_end'] : null;
            $start_time         = !empty($_POST['start_time'])    ? $_POST['start_time']    : null;
            $end_time           = !empty($_POST['end_time'])      ? $_POST['end_time']      : null;
            $location           = trim($_POST['location']         ?? '');
            $description        = trim($_POST['description']      ?? '');
            $required_personnel = (int)($_POST['required_personnel'] ?? 0);
            $kuat_personil      = (int)($_POST['kuat_personil']      ?? 0);
            $dukgra             = (float)str_replace(',', '.', preg_replace('/[^0-9,]/', '', $_POST['dukgra'] ?? '0'));
            $status             = in_array($_POST['status'] ?? '', ['planned','active','completed','cancelled'])
                                    ? $_POST['status'] : 'planned';

            $valid_tingkat = ['terpusat','kewilayahan_polda','kewilayahan_polres','imbangan'];
            $tingkat_operasi = in_array($_POST['tingkat_operasi'] ?? '', $valid_tingkat)
                                ? $_POST['tingkat_operasi'] : 'kewilayahan_polres';

            $valid_jenis = ['intelijen','pengamanan_kegiatan','pemeliharaan_keamanan',
                            'penegakan_hukum','pemulihan_keamanan','kontinjensi','lainnya'];
            $jenis_operasi = in_array($_POST['jenis_operasi'] ?? '', $valid_jenis)
                              ? $_POST['jenis_operasi'] : 'pemeliharaan_keamanan';

            if (!$operation_name) {
                throw new Exception('Nama operasi wajib diisi');
            }
            if (!$operation_month && !$operation_date) {
                throw new Exception('Bulan/tahun pelaksanaan wajib diisi');
            }

            // Derive operation_month from operation_date if not provided
            if (!$operation_month && $operation_date) {
                $operation_month = substr($operation_date, 0, 7);
            }

            // Auto-derive status from dates if both are provided
            if ($operation_date && $operation_date_end) {
                $today = new DateTime('today');
                $ds    = new DateTime($operation_date);
                $de    = new DateTime($operation_date_end);
                if ($de < $today) {
                    $status = 'completed';
                } elseif ($ds <= $today && $today <= $de) {
                    $status = 'active';
                } else {
                    $status = 'active'; // agenda — sudah punya tanggal, belum mulai
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO operations
                    (operation_name, tingkat_operasi, jenis_operasi,
                     operation_month, operation_date, operation_date_end,
                     start_time, end_time,
                     location, description, required_personnel, kuat_personil, dukgra, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $operation_name, $tingkat_operasi, $jenis_operasi,
                $operation_month, $operation_date, $operation_date_end,
                $start_time, $end_time,
                $location, $description, $required_personnel, $kuat_personil, $dukgra, $status
            ]);

            echo json_encode([
                'success'      => true,
                'message'      => 'Operasi berhasil dibuat',
                'operation_id' => $pdo->lastInsertId()
            ]);
            break;

        // ── DELETE OPERATION ──
        case 'delete_operation':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID operasi tidak valid');
            $stmt = $pdo->prepare("DELETE FROM operations WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) throw new Exception('Operasi tidak ditemukan');
            echo json_encode(['success' => true, 'message' => 'Operasi berhasil dihapus']);
            break;

        // ── UPDATE OPERATION ──
        case 'update_operation':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID operasi tidak valid');

            $valid_tingkat = ['terpusat','kewilayahan_polda','kewilayahan_polres','imbangan'];
            $valid_jenis   = ['intelijen','pengamanan_kegiatan','pemeliharaan_keamanan',
                              'penegakan_hukum','pemulihan_keamanan','kontinjensi','lainnya'];

            $fields = [
                'operation_name'     => trim($_POST['operation_name']  ?? ''),
                'tingkat_operasi'    => in_array($_POST['tingkat_operasi'] ?? '', $valid_tingkat) ? $_POST['tingkat_operasi'] : 'kewilayahan_polres',
                'jenis_operasi'      => in_array($_POST['jenis_operasi']   ?? '', $valid_jenis)   ? $_POST['jenis_operasi']   : 'pemeliharaan_keamanan',
                'operation_month'    => trim($_POST['operation_month'] ?? ''),
                'operation_date'     => !empty($_POST['operation_date'])     ? $_POST['operation_date']     : null,
                'operation_date_end' => !empty($_POST['operation_date_end']) ? $_POST['operation_date_end'] : null,
                'location'           => trim($_POST['location']    ?? ''),
                'description'        => trim($_POST['description'] ?? ''),
                'kuat_personil'      => (int)($_POST['kuat_personil'] ?? 0),
                'dukgra'             => (float)str_replace(',', '.', preg_replace('/[^0-9,]/', '', $_POST['dukgra'] ?? '0')),
                'status'             => in_array($_POST['status'] ?? '', ['planned','active','completed','cancelled']) ? $_POST['status'] : 'planned',
            ];
            if (!$fields['operation_name']) throw new Exception('Nama operasi wajib diisi');
            if (!$fields['operation_month'] && !$fields['operation_date']) throw new Exception('Bulan/tahun pelaksanaan wajib diisi');
            if (!$fields['operation_month'] && $fields['operation_date']) {
                $fields['operation_month'] = substr($fields['operation_date'], 0, 7);
            }

            // Auto-derive status from dates if both are provided
            if ($fields['operation_date'] && $fields['operation_date_end']) {
                $today = new DateTime('today');
                $ds    = new DateTime($fields['operation_date']);
                $de    = new DateTime($fields['operation_date_end']);
                if ($de < $today) {
                    $fields['status'] = 'completed';
                } elseif ($ds <= $today && $today <= $de) {
                    $fields['status'] = 'active';
                } else {
                    $fields['status'] = 'active';
                }
            }

            $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
            $stmt = $pdo->prepare("UPDATE operations SET $setClauses WHERE id = ?");
            $stmt->execute([...array_values($fields), $id]);
            echo json_encode(['success' => true, 'message' => 'Operasi berhasil diupdate']);
            break;

        // ── LIVE STATS (used after save to refresh dashboard counters) ──
        case 'get_live_stats':
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) as total_schedules,
                    COUNT(CASE WHEN shift_date = CURDATE() THEN 1 END) as today_schedules,
                    COUNT(CASE WHEN shift_date >= CURDATE() AND shift_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_schedules,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count
                FROM schedules
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        // ── EXPORT SCHEDULES CSV ──
        case 'export_csv':
            $date_from = $_POST['date_from'] ?? $_GET['date_from'] ?? date('Y-m-01');
            $date_to   = $_POST['date_to']   ?? $_GET['date_to']   ?? date('Y-m-t');

            $stmt = $pdo->prepare("
                SELECT s.shift_date, s.shift_type, s.start_time, s.end_time,
                       COALESCE(p.nama, s.personil_name) as nama,
                       s.bagian, s.location, s.description, s.status
                FROM schedules s
                LEFT JOIN personil p ON s.personil_id = p.nrp
                WHERE s.shift_date >= ? AND s.shift_date <= ?
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$date_from, $date_to]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $csv = "Tanggal,Shift,Jam Mulai,Jam Selesai,Nama,Bagian,Lokasi,Deskripsi,Status\n";
            foreach ($rows as $r) {
                $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', [
                    $r['shift_date'], $r['shift_type'], $r['start_time'], $r['end_time'],
                    $r['nama'], $r['bagian'], $r['location'], $r['description'], $r['status']
                ])) . "\n";
            }

            echo json_encode(['success' => true, 'csv' => $csv, 'filename' => 'jadwal_' . $date_from . '_' . $date_to . '.csv']);
            break;

        // ── GET EVENTS (legacy FullCalendar format) ──
        case 'get_events':
            $start = $_GET['start'] ?? $_POST['start'] ?? date('Y-m-01');
            $end   = $_GET['end']   ?? $_POST['end']   ?? date('Y-m-t');

            $stmt = $pdo->prepare("
                SELECT s.*, p.nama as personil_nama
                FROM schedules s
                LEFT JOIN personil p ON s.personil_id = p.nrp
                WHERE s.shift_date >= ? AND s.shift_date <= ?
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$start, $end]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $formattedEvents = array_map(function($event) {
                return [
                    'id'          => $event['id'],
                    'title'       => ($event['personil_nama'] ?: $event['personil_name']) . ' - ' . $event['shift_type'],
                    'start'       => $event['shift_date'] . 'T' . $event['start_time'],
                    'end'         => $event['shift_date'] . 'T' . $event['end_time'],
                    'description' => $event['description'],
                    'color'       => '#007bff',
                    'extendedProps' => [
                        'personil_id'   => $event['personil_id'],
                        'personil_nama' => $event['personil_nama'] ?: $event['personil_name'],
                        'location'      => $event['location'],
                        'shift_type'    => $event['shift_type'],
                        'status'        => $event['status']
                    ]
                ];
            }, $events);

            echo json_encode($formattedEvents);
            break;

        case 'get_stats':
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_events,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_events,
                    COUNT(CASE WHEN shift_date = CURDATE() THEN 1 END) as today_events
                FROM schedules
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        case 'get_personil_list':
            $stmt = $pdo->query("
                SELECT id, nama, nrp
                FROM personil
                WHERE is_deleted = 0 AND is_active = 1
                ORDER BY nama ASC
            ");
            $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $personil]);
            break;

        default:
            echo json_encode([
                'success' => true,
                'message' => 'Calendar API is working',
                'available_actions' => [
                    'get_schedules', 'create_schedule', 'update_schedule', 'delete_schedule',
                    'get_schedule', 'get_operations', 'create_operation',
                    'get_live_stats', 'export_csv',
                    'get_events', 'get_stats', 'get_personil_list'
                ]
            ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error'   => $e->getMessage()
    ]);
}
?>
