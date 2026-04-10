<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // ── GET: semua personil aktif ─────────────────────────────────────────
    if ($action === 'get_personil_all') {
        $rows = $pdo->query("
            SELECT p.nrp AS nrp, p.nama,
                   pk.nama_pangkat AS pangkat,
                   b.nama_bagian  AS bagian
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN bagian  b  ON b.id  = p.id_bagian
            WHERE p.is_active=1 AND p.is_deleted=0
            ORDER BY b.nama_bagian, p.nama
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'data'=>$rows]); exit;
    }

    // ── GET: anggota tim ──────────────────────────────────────────────────
    if ($action === 'get_anggota') {
        $timId = (int)($_GET['tim_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT a.personil_id, a.peran, a.urutan, p.nama,
                   pk.nama_pangkat AS pangkat, b.nama_bagian AS bagian
            FROM tim_piket_anggota a
            LEFT JOIN personil p  ON p.nrp = a.personil_id
            LEFT JOIN pangkat pk  ON pk.id = p.id_pangkat
            LEFT JOIN bagian  b   ON b.id  = p.id_bagian
            WHERE a.tim_id = ?
            ORDER BY a.urutan, p.nama
        ");
        $stmt->execute([$timId]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ── POST actions ──────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
    }

    $validJenis  = ['piket','satuan_tugas','kegiatan'];
    $validShift  = ['PAGI','SIANG','MALAM','FULL_DAY','ROTASI'];
    $shiftTimes  = [
        'PAGI'     => ['06:00:00','14:00:00'],
        'SIANG'    => ['14:00:00','22:00:00'],
        'MALAM'    => ['22:00:00','06:00:00'],
        'FULL_DAY' => ['07:00:00','16:00:00'],
        'ROTASI'   => ['07:00:00','16:00:00'],
    ];

    // ─── CREATE TIM ───────────────────────────────────────────────────────
    if ($action === 'create_tim') {
        $nama   = trim($_POST['nama_tim'] ?? '');
        $jenis  = in_array($_POST['jenis']??'', $validJenis) ? $_POST['jenis'] : 'piket';
        $bagian = (int)($_POST['id_bagian'] ?? 0) ?: null;
        $unsur  = (int)($_POST['id_unsur']  ?? 0) ?: null;
        $shift  = in_array($_POST['shift_default']??'', $validShift) ? $_POST['shift_default'] : null;
        $rotasi = trim($_POST['pola_rotasi'] ?? '') ?: null;
        $ket    = trim($_POST['keterangan'] ?? '');
        $aktif  = isset($_POST['is_active']) ? 1 : 0;
        if (!$nama) throw new Exception('Nama tim wajib diisi');
        $stmt = $pdo->prepare("INSERT INTO tim_piket (nama_tim,id_bagian,id_unsur,jenis,shift_default,pola_rotasi,keterangan,is_active) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$nama,$bagian,$unsur,$jenis,$shift,$rotasi,$ket,$aktif]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId(),'message'=>'Tim berhasil dibuat']); exit;
    }

    // ─── UPDATE TIM ───────────────────────────────────────────────────────
    if ($action === 'update_tim') {
        $id     = (int)($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama_tim'] ?? '');
        $jenis  = in_array($_POST['jenis']??'', $validJenis) ? $_POST['jenis'] : 'piket';
        $bagian = (int)($_POST['id_bagian'] ?? 0) ?: null;
        $unsur  = (int)($_POST['id_unsur']  ?? 0) ?: null;
        $shift  = in_array($_POST['shift_default']??'', $validShift) ? $_POST['shift_default'] : null;
        $rotasi = trim($_POST['pola_rotasi'] ?? '') ?: null;
        $ket    = trim($_POST['keterangan'] ?? '');
        $aktif  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
        if (!$id || !$nama) throw new Exception('ID dan nama tim wajib diisi');
        $stmt = $pdo->prepare("UPDATE tim_piket SET nama_tim=?,id_bagian=?,id_unsur=?,jenis=?,shift_default=?,pola_rotasi=?,keterangan=?,is_active=? WHERE id=?");
        $stmt->execute([$nama,$bagian,$unsur,$jenis,$shift,$rotasi,$ket,$aktif,$id]);
        echo json_encode(['success'=>true,'message'=>'Tim berhasil diupdate']); exit;
    }

    // ─── DELETE TIM ───────────────────────────────────────────────────────
    if ($action === 'delete_tim') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID tidak valid');
        $pdo->prepare("DELETE FROM tim_piket_anggota WHERE tim_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM tim_piket WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true,'message'=>'Tim berhasil dihapus']); exit;
    }

    // ─── SAVE ANGGOTA ────────────────────────────────────────────────────
    if ($action === 'save_anggota') {
        $timId = (int)($_POST['tim_id'] ?? 0);
        if (!$timId) throw new Exception('tim_id tidak valid');
        $ids = array_unique(array_filter($_POST['personil_ids'] ?? []));
        // Hapus semua dulu, insert ulang
        $pdo->prepare("DELETE FROM tim_piket_anggota WHERE tim_id=?")->execute([$timId]);
        if ($ids) {
            $ins = $pdo->prepare("INSERT INTO tim_piket_anggota (tim_id,personil_id,peran,urutan) VALUES (?,?,'anggota',?)");
            foreach (array_values($ids) as $i => $nrp) {
                $ins->execute([$timId, $nrp, $i+1]);
            }
        }
        echo json_encode(['success'=>true,'count'=>count($ids),'message'=>count($ids).' anggota disimpan']); exit;
    }

    // ─── GENERATE JADWAL DARI TIM ────────────────────────────────────────
    if ($action === 'generate_jadwal_tim') {
        $timId      = (int)($_POST['tim_id'] ?? 0);
        $shiftType  = strtoupper(trim($_POST['shift_type'] ?? 'PAGI'));
        $startDate  = $_POST['start_date'] ?? '';
        $endDate    = $_POST['end_date']   ?? '';
        $recType    = in_array($_POST['recurrence_type']??'', ['none','daily','weekly','monthly','yearly'])
                        ? $_POST['recurrence_type'] : 'none';
        $recInt     = max(1, (int)($_POST['recurrence_interval'] ?? 1));
        $recDays    = trim($_POST['recurrence_days'] ?? ''); // "1,3,5"
        $location   = trim($_POST['location'] ?? '');
        $desc       = trim($_POST['description'] ?? '');

        if (!$timId)     throw new Exception('tim_id tidak valid');
        if (!$startDate) throw new Exception('Tanggal mulai wajib diisi');
        if (!$endDate)   throw new Exception('Tanggal selesai wajib diisi');
        if ($endDate < $startDate) throw new Exception('Tanggal selesai tidak boleh sebelum tanggal mulai');

        // Safety: max 366 hari ke depan
        $maxEnd = (new DateTime($startDate))->modify('+365 days')->format('Y-m-d');
        if ($endDate > $maxEnd) $endDate = $maxEnd;

        // Ambil anggota tim
        $aStmt = $pdo->prepare("SELECT personil_id, p.nama AS personil_name, b.nama_bagian AS bagian
            FROM tim_piket_anggota a
            LEFT JOIN personil p ON p.nrp = a.personil_id
            LEFT JOIN bagian   b ON b.id  = p.id_bagian
            WHERE a.tim_id=? ORDER BY a.urutan");
        $aStmt->execute([$timId]);
        $anggota = $aStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$anggota) throw new Exception('Tim tidak memiliki anggota. Tambahkan anggota dahulu.');

        // Tentukan waktu shift
        $times     = $shiftTimes[$shiftType] ?? ['07:00:00','16:00:00'];
        $startTime = $times[0]; $endTime = $times[1];

        // Buat daftar tanggal sesuai recurrence
        $dates   = [];
        $current = new DateTime($startDate);
        $end     = new DateTime($endDate);
        $dayNums = $recDays ? array_map('intval', explode(',', $recDays)) : [];

        if ($recType === 'none') {
            $dates[] = $startDate;
        } elseif ($recType === 'daily') {
            while ($current <= $end) {
                $dates[] = $current->format('Y-m-d');
                $current->modify("+{$recInt} days");
            }
        } elseif ($recType === 'weekly') {
            if (!$dayNums) throw new Exception('Pilih minimal satu hari untuk pengulangan mingguan');
            while ($current <= $end) {
                if (in_array((int)$current->format('w'), $dayNums)) {
                    $dates[] = $current->format('Y-m-d');
                }
                $current->modify('+1 day');
            }
        } elseif ($recType === 'monthly') {
            $dayOfMonth = (int)(new DateTime($startDate))->format('d');
            while ($current <= $end) {
                if ((int)$current->format('d') === $dayOfMonth) {
                    $dates[] = $current->format('Y-m-d');
                    $current->modify("+{$recInt} months");
                    $current->setDate((int)$current->format('Y'), (int)$current->format('m'), $dayOfMonth);
                } else {
                    $current->modify('+1 day');
                }
            }
        } elseif ($recType === 'yearly') {
            $md = (new DateTime($startDate))->format('m-d');
            while ($current <= $end) {
                if ($current->format('m-d') === $md) {
                    $dates[] = $current->format('Y-m-d');
                    $current->modify("+{$recInt} years");
                } else {
                    $current->modify('+1 day');
                }
            }
        }

        if (!$dates) throw new Exception('Tidak ada tanggal yang dihasilkan dari pola pengulangan ini');

        // Insert jadwal
        $ins = $pdo->prepare("
            INSERT INTO schedules
                (personil_id, personil_name, bagian, shift_type, shift_date,
                 start_time, end_time, location, description,
                 tim_id, recurrence_type, recurrence_interval, recurrence_days, recurrence_end, status)
            VALUES (?,?,?,?,?, ?,?,?,?, ?,?,?,?,?, 'scheduled')
        ");

        $count = 0;
        $parentId = null;
        $pdo->beginTransaction();
        try {
            foreach ($dates as $di => $date) {
                foreach ($anggota as $ang) {
                    $ins->execute([
                        $ang['personil_id'], $ang['personil_name'] ?? '', $ang['bagian'] ?? '',
                        $shiftType, $date, $startTime, $endTime, $location, $desc,
                        $timId, $recType, $recInt, $recDays ?: null, $endDate
                    ]);
                    $count++;
                }
            }
            $pdo->commit();
        } catch (Exception $ex) {
            $pdo->rollBack(); throw $ex;
        }

        echo json_encode([
            'success' => true,
            'count'   => $count,
            'message' => count($anggota).' anggota × '.count($dates).' tanggal = '.$count.' jadwal dibuat'
        ]); exit;
    }

    throw new Exception('Action tidak dikenal: '.$action);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
