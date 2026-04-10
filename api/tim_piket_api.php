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

    // ── GET: semua tim aktif (untuk dropdown kalender) ───────────────────────
    if ($action === 'get_all_tim') {
        $PIKET_UNSUR = [3, 4];
        $PIKET_EXTRA = [20];
        $uph = implode(',', array_fill(0, count($PIKET_UNSUR), '?'));
        $eph = implode(',', array_fill(0, count($PIKET_EXTRA), '?'));
        $stmt = $pdo->prepare("
            SELECT t.id, t.nama_tim, t.shift_default, b.nama_bagian,
                   COUNT(a.id) AS jml_anggota
            FROM tim_piket t
            LEFT JOIN bagian b ON b.id = t.id_bagian
            LEFT JOIN tim_piket_anggota a ON a.tim_id = t.id
            WHERE (b.id_unsur IN ($uph) OR b.id IN ($eph)) AND t.is_active = 1
            GROUP BY t.id
            ORDER BY b.urutan, t.nama_tim
        ");
        $stmt->execute(array_merge($PIKET_UNSUR, $PIKET_EXTRA));
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ── GET: jadwal piket hari ini ───────────────────────────────────────────
    if ($action === 'get_piket_hari_ini') {
        $today = date('Y-m-d');
        $stmt  = $pdo->prepare("
            SELECT s.id, s.personil_id, s.personil_name, s.shift_type,
                   s.start_time, s.end_time, s.location, s.status,
                   t.nama_tim, t.id AS tim_id,
                   b.nama_bagian,
                   pk.nama_pangkat
            FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id
            LEFT JOIN bagian b ON b.id = t.id_bagian
            LEFT JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            WHERE s.shift_date = ? AND s.tim_id IS NOT NULL
            ORDER BY b.urutan, s.start_time, s.personil_name
        ");
        $stmt->execute([$today]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    if ($action === 'get_siklus') {
        $bagianId = (int)($_GET['id_bagian'] ?? 0);
        if (!$bagianId) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
        $stmt = $pdo->prepare("SELECT * FROM siklus_piket_fase WHERE id_bagian=? ORDER BY urutan");
        $stmt->execute([$bagianId]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

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

    // ─── SAVE SIKLUS (upsert semua fase per bagian) ───────────────────────
    if ($action === 'save_siklus') {
        $bagianId  = (int)($_POST['id_bagian'] ?? 0);
        $fasesJson = $_POST['fases'] ?? '[]';
        if (!$bagianId) throw new Exception('id_bagian tidak valid');
        $fases = json_decode($fasesJson, true);
        if (!is_array($fases) || !count($fases)) throw new Exception('Minimal 1 fase harus ada');

        $pdo->beginTransaction();
        // Hapus fase lama yang tidak ada di list baru
        $existingIds = array_filter(array_column($fases, 'id'));
        if ($existingIds) {
            $ph = implode(',', array_fill(0, count($existingIds), '?'));
            $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian=? AND id NOT IN ($ph)")
                ->execute(array_merge([$bagianId], $existingIds));
        } else {
            $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian=?")->execute([$bagianId]);
        }

        $upsert = $pdo->prepare("
            INSERT INTO siklus_piket_fase (id, id_bagian, nama_fase, urutan, durasi_jam, jam_mulai_default, jam_mulai_mode, is_wajib, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                nama_fase=VALUES(nama_fase), urutan=VALUES(urutan),
                durasi_jam=VALUES(durasi_jam), jam_mulai_default=VALUES(jam_mulai_default),
                jam_mulai_mode=VALUES(jam_mulai_mode), is_wajib=VALUES(is_wajib),
                keterangan=VALUES(keterangan)
        ");
        foreach ($fases as $i => $f) {
            $fId   = !empty($f['id']) ? (int)$f['id'] : null;
            $jam   = substr(trim($f['jam_mulai_default'] ?? '07:00:00'), 0, 8);
            if (strlen($jam) === 5) $jam .= ':00';
            $upsert->execute([
                $fId,
                $bagianId,
                trim($f['nama_fase'] ?? 'Fase '.($i+1)),
                $i + 1,
                max(0.5, (float)($f['durasi_jam'] ?? 8)),
                $jam,
                ($f['jam_mulai_mode'] ?? 'auto') === 'manual' ? 'manual' : 'auto',
                isset($f['is_wajib']) ? (int)$f['is_wajib'] : 1,
                trim($f['keterangan'] ?? '')
            ]);
        }
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>count($fases).' fase disimpan']); exit;
    }

    // ─── GESER FASE (pindahkan tim ke fase lain) ──────────────────────────
    if ($action === 'geser_fase') {
        $timId  = (int)($_POST['tim_id'] ?? 0);
        $faseId = (int)($_POST['fase_siklus_id'] ?? 0) ?: null;
        if (!$timId) throw new Exception('tim_id tidak valid');
        $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=? WHERE id=?")->execute([$faseId, $timId]);
        echo json_encode(['success'=>true,'message'=>'Posisi fase diupdate']); exit;
    }

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
        $faseId  = (int)($_POST['fase_siklus_id'] ?? 0) ?: null;
        $jamMul  = trim($_POST['jam_mulai_aktif'] ?? '') ?: null;
        $durasi  = is_numeric($_POST['durasi_jam'] ?? '') ? (float)$_POST['durasi_jam'] : null;
        $stmt = $pdo->prepare("INSERT INTO tim_piket (nama_tim,id_bagian,id_unsur,jenis,shift_default,pola_rotasi,keterangan,is_active,fase_siklus_id,jam_mulai_aktif,durasi_jam) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nama,$bagian,$unsur,$jenis,$shift,$rotasi,$ket,$aktif,$faseId,$jamMul,$durasi]);
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
        $faseId  = (int)($_POST['fase_siklus_id'] ?? 0) ?: null;
        $jamMul  = trim($_POST['jam_mulai_aktif'] ?? '') ?: null;
        $durasi  = is_numeric($_POST['durasi_jam'] ?? '') ? (float)$_POST['durasi_jam'] : null;
        $stmt = $pdo->prepare("UPDATE tim_piket SET nama_tim=?,id_bagian=?,id_unsur=?,jenis=?,shift_default=?,pola_rotasi=?,keterangan=?,is_active=?,fase_siklus_id=?,jam_mulai_aktif=?,durasi_jam=? WHERE id=?");
        $stmt->execute([$nama,$bagian,$unsur,$jenis,$shift,$rotasi,$ket,$aktif,$faseId,$jamMul,$durasi,$id]);
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

    // ─── SAVE ABSENSI ─────────────────────────────────────────────────────
    if ($action === 'save_absensi') {
        $schedId    = (int)($_POST['schedule_id'] ?? 0);
        $personilId = trim($_POST['personil_id'] ?? '');
        $timId      = (int)($_POST['tim_id'] ?? 0) ?: null;
        $tanggal    = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $status     = in_array($_POST['status']??'', ['hadir','tidak_hadir','sakit','ijin','terlambat'])
                        ? $_POST['status'] : 'hadir';
        $jamHadir   = trim($_POST['jam_hadir'] ?? '') ?: null;
        $catatan    = trim($_POST['catatan'] ?? '') ?: null;
        if (!$schedId || !$personilId) throw new Exception('schedule_id & personil_id wajib');
        $pdo->prepare("
            INSERT INTO piket_absensi (schedule_id, personil_id, tim_id, tanggal, status, jam_hadir, catatan, input_oleh)
            VALUES (?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE status=VALUES(status), jam_hadir=VALUES(jam_hadir),
                catatan=VALUES(catatan), input_oleh=VALUES(input_oleh), updated_at=NOW()
        ")->execute([$schedId,$personilId,$timId,$tanggal,$status,$jamHadir,$catatan,$_SESSION['user_id']]);
        echo json_encode(['success'=>true,'message'=>'Absensi disimpan']); exit;
    }

    // ─── DELETE JADWAL SERIES ─────────────────────────────────────────────
    if ($action === 'delete_jadwal_series') {
        $timId = (int)($_POST['tim_id'] ?? 0);
        $bulan = (int)($_POST['bulan'] ?? 0);
        $tahun = (int)($_POST['tahun'] ?? 0);
        if (!$timId || !$bulan || !$tahun) throw new Exception('tim_id, bulan, tahun wajib');
        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate   = date('Y-m-t', strtotime($startDate));
        $pdo->beginTransaction();
        // Hapus absensi dulu
        $pdo->prepare("DELETE pa FROM piket_absensi pa
            JOIN schedules s ON s.id = pa.schedule_id
            WHERE s.tim_id=? AND s.shift_date BETWEEN ? AND ?")->execute([$timId,$startDate,$endDate]);
        // Hapus jadwal
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE tim_id=? AND shift_date BETWEEN ? AND ?");
        $stmt->execute([$timId,$startDate,$endDate]);
        $count = $stmt->rowCount();
        $pdo->commit();
        echo json_encode(['success'=>true,'count'=>$count,'message'=>$count.' jadwal dihapus']); exit;
    }

    throw new Exception('Action tidak dikenal: '.$action);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
