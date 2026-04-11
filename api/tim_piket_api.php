<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

// CSRF protection for POST (skip read-only GET actions)
$readOnlyActions = [
    'get_all_tim','get_piket_hari_ini','get_personil_all','get_anggota',
    'get_siklus','dashboard_hari_ini','statistik_personil','calendar_data',
    'fatigue_check','get_rotasi_log','cetak_sprin_data','get_notifikasi_piket'
];
CSRFHelper::applyProtection($readOnlyActions);

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
            SELECT t.id, t.nama_tim, t.jenis, t.shift_default, t.id_bagian, t.id_unsur,
                   t.fase_siklus_id, t.jam_mulai_aktif, t.durasi_jam, t.keterangan, t.is_active,
                   b.nama_bagian, COUNT(a.id) AS jml_anggota
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
        $bagianId = $_GET['id_bagian'] ?? '';
        $isUmum    = isset($_GET['is_umum']) && $_GET['is_umum'] === '1';
        
        if ($isUmum) {
            // Ambil siklus umum
            $stmt = $pdo->prepare("SELECT * FROM siklus_piket_fase WHERE id_bagian IS NULL ORDER BY urutan");
            $stmt->execute();
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
        } elseif ($bagianId === 'umum') {
            // Ambil siklus umum (via string 'umum')
            $stmt = $pdo->prepare("SELECT * FROM siklus_piket_fase WHERE id_bagian IS NULL ORDER BY urutan");
            $stmt->execute();
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
        } else {
            // Ambil siklus khusus per bagian
            $bagianId = (int)$bagianId;
            if (!$bagianId) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
            $stmt = $pdo->prepare("SELECT * FROM siklus_piket_fase WHERE id_bagian=? ORDER BY urutan");
            $stmt->execute([$bagianId]);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
        }
    }

    // ── GET: kandidat cover (personil satuan sama, tidak bertugas hari itu) ─────
    if ($action === 'get_cover_candidates') {
        $schedId = (int)($_GET['schedule_id'] ?? 0);
        if (!$schedId) throw new Exception('schedule_id wajib');
        // Ambil info jadwal
        $orig = $pdo->prepare("
            SELECT s.*, t.id_bagian FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id WHERE s.id = ?
        ");
        $orig->execute([$schedId]);
        $sched = $orig->fetch(PDO::FETCH_ASSOC);
        if (!$sched) throw new Exception('Jadwal tidak ditemukan');
        // Personil satuan yang sama dan tidak ada jadwal di hari itu
        $stmt = $pdo->prepare("
            SELECT p.nrp, p.nama, pk.nama_pangkat
            FROM personil p
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            WHERE p.id_bagian = ?
              AND p.is_active = 1
              AND p.nrp != ?
              AND p.nrp NOT IN (
                  SELECT personil_id FROM schedules
                  WHERE shift_date = ? AND id != ?
              )
            ORDER BY p.nama
        ");
        $stmt->execute([$sched['id_bagian'], $sched['personil_id'], $sched['shift_date'], $schedId]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC),'sched'=>$sched]); exit;
    }

    // ── POST: simpan cover (ganti personil di jadwal + catat absensi tidak_hadir) ─
    if ($action === 'save_cover') {
        $schedId    = (int)($_POST['schedule_id'] ?? 0);
        $newNrp     = trim($_POST['new_personil_id'] ?? '');
        $newName    = trim($_POST['new_personil_name'] ?? '');
        $catatan    = trim($_POST['catatan'] ?? '');
        if (!$schedId || !$newNrp) throw new Exception('schedule_id & new_personil_id wajib');
        $pdo->beginTransaction();
        // Ambil data jadwal asli
        $orig = $pdo->prepare("SELECT * FROM schedules WHERE id=?");
        $orig->execute([$schedId]);
        $sched = $orig->fetch(PDO::FETCH_ASSOC);
        // Tandai absensi personil asli sebagai tidak_hadir
        $pdo->prepare("
            INSERT INTO piket_absensi (schedule_id,personil_id,tim_id,tanggal,status,catatan,input_oleh)
            VALUES (?,?,?,?,'tidak_hadir',?,?)
            ON DUPLICATE KEY UPDATE status='tidak_hadir',catatan=VALUES(catatan),updated_at=NOW()
        ")->execute([$schedId,$sched['personil_id'],$sched['tim_id'],$sched['shift_date'],$catatan,$_SESSION['user_id']]);
        // Buat jadwal baru untuk personil pengganti (clone)
        $bagian = $pdo->prepare("SELECT b.nama_bagian FROM tim_piket t JOIN bagian b ON b.id=t.id_bagian WHERE t.id=?");
        $bagian->execute([$sched['tim_id']]);
        $bagianName = $bagian->fetchColumn() ?: '';
        $pdo->prepare("
            INSERT INTO schedules (personil_id,personil_name,bagian,shift_type,shift_date,
                start_time,end_time,location,description,tim_id,recurrence_type)
            VALUES (?,?,?,?,?,?,?,?,?,?,'none')
        ")->execute([
            $newNrp, $newName, $bagianName,
            $sched['shift_type'], $sched['shift_date'],
            $sched['start_time'], $sched['end_time'],
            $sched['location'], 'Cover: '.$sched['personil_name'],
            $sched['tim_id']
        ]);
        $newSchedId = $pdo->lastInsertId();
        // Absensi personil cover = hadir
        $pdo->prepare("
            INSERT INTO piket_absensi (schedule_id,personil_id,tim_id,tanggal,status,catatan,input_oleh)
            VALUES (?,?,?,?,'hadir','Cover pengganti',?)
        ")->execute([$newSchedId,$newNrp,$sched['tim_id'],$sched['shift_date'],$_SESSION['user_id']]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Cover berhasil disimpan']); exit;
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
        $bagianId  = $_POST['id_bagian'] ?? '';
        $isUmum    = isset($_POST['is_umum']) && $_POST['is_umum'] === '1';
        $fasesJson = $_POST['fases'] ?? '[]';
        
        // Jika siklus umum, id_bagian = NULL
        if ($isUmum) {
            $bagianId = null;
        } else {
            $bagianId = (int)$bagianId;
            if (!$bagianId) throw new Exception('id_bagian tidak valid untuk siklus khusus');
        }
        
        $fases = json_decode($fasesJson, true);
        if (!is_array($fases) || !count($fases)) throw new Exception('Minimal 1 fase harus ada');

        $pdo->beginTransaction();
        // Hapus fase lama yang tidak ada di list baru
        $existingIds = array_filter(array_column($fases, 'id'));
        if ($bagianId === null) {
            // Siklus umum
            if ($existingIds) {
                $ph = implode(',', array_fill(0, count($existingIds), '?'));
                $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian IS NULL AND id NOT IN ($ph)")
                    ->execute($existingIds);
            } else {
                $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian IS NULL")->execute();
            }
        } else {
            // Siklus khusus per bagian
            if ($existingIds) {
                $ph = implode(',', array_fill(0, count($existingIds), '?'));
                $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian=? AND id NOT IN ($ph)")
                    ->execute(array_merge([$bagianId], $existingIds));
            } else {
                $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian=?")->execute([$bagianId]);
            }
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
        $msg = $isUmum ? 'Siklus umum ('.count($fases).' fase) disimpan' : count($fases).' fase disimpan';
        echo json_encode(['success'=>true,'message'=>$msg]); exit;
    }

    // ─── DELETE SIKLUS BAGIAN (hapus semua fase per bagian) ───────────────
    if ($action === 'delete_siklus_bagian') {
        $bagianId = (int)($_POST['id_bagian'] ?? 0);
        if (!$bagianId) throw new Exception('id_bagian tidak valid');
        
        $pdo->beginTransaction();
        // Reset fase_siklus_id pada tim di bagian ini
        $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=NULL WHERE id_bagian=?")->execute([$bagianId]);
        // Hapus semua fase untuk bagian ini
        $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id_bagian=?")->execute([$bagianId]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Siklus berhasil dihapus']); exit;
    }

    // ─── DELETE FASES (hapus fase-fase tertentu) ─────────────────────────
    if ($action === 'delete_fases') {
        $faseIdsJson = $_POST['fase_ids'] ?? '[]';
        $faseIds = json_decode($faseIdsJson, true);
        if (!is_array($faseIds) || !count($faseIds)) throw new Exception('fase_ids tidak valid');
        
        $pdo->beginTransaction();
        // Reset fase_siklus_id pada tim yang berada di fase yang dihapus
        $ph = implode(',', array_fill(0, count($faseIds), '?'));
        $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=NULL WHERE fase_siklus_id IN ($ph)")
            ->execute($faseIds);
        // Hapus fase-fase yang dipilih
        $pdo->prepare("DELETE FROM siklus_piket_fase WHERE id IN ($ph)")->execute($faseIds);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>count($faseIds).' fase berhasil dihapus']); exit;
    }

    // ─── GESER FASE (pindahkan tim ke fase lain) ──────────────────────────
    if ($action === 'geser_fase') {
        $timId  = (int)($_POST['tim_id'] ?? 0);
        $faseId = (int)($_POST['fase_siklus_id'] ?? 0) ?: null;
        if (!$timId) throw new Exception('tim_id tidak valid');
        $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=? WHERE id=?")->execute([$faseId, $timId]);
        echo json_encode(['success'=>true,'message'=>'Posisi fase diupdate']); exit;
    }

    // ── POST: rotasi fase semua tim per bagian (geser ke fase berikutnya) ────────
    if ($action === 'rotasi_fase_semua') {
        $bagianId = (int)($_POST['id_bagian'] ?? 0);
        if (!$bagianId) throw new Exception('id_bagian wajib');
        // Ambil urutan fase untuk bagian ini
        $faseList = $pdo->prepare("SELECT id, urutan FROM siklus_piket_fase WHERE id_bagian=? ORDER BY urutan");
        $faseList->execute([$bagianId]);
        $fases = $faseList->fetchAll(PDO::FETCH_ASSOC);
        if (empty($fases)) throw new Exception('Tidak ada fase terdaftar');
        $faseMap = array_column($fases, 'urutan', 'id');
        $maxUrutan = max(array_values($faseMap));
        // Ambil semua tim di bagian ini
        $timList = $pdo->prepare("SELECT id, fase_siklus_id FROM tim_piket WHERE id_bagian=? AND is_active=1");
        $timList->execute([$bagianId]);
        $tims = $timList->fetchAll(PDO::FETCH_ASSOC);
        $pdo->beginTransaction();
        $rotated = 0;
        $timIds = [];
        $dariFase = null; $keFase = null;
        foreach ($tims as $tim) {
            $curFaseId = $tim['fase_siklus_id'];
            $curUrutan = $curFaseId && isset($faseMap[$curFaseId]) ? $faseMap[$curFaseId] : 0;
            $nextUrutan = $curUrutan >= $maxUrutan ? 1 : $curUrutan + 1;
            $nextFaseId = array_search($nextUrutan, $faseMap);
            if ($nextFaseId !== false) {
                $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=? WHERE id=?")->execute([$nextFaseId, $tim['id']]);
                $rotated++;
                $timIds[] = $tim['id'];
                if ($dariFase === null) { $dariFase = $curFaseId; $keFase = $nextFaseId; }
            }
        }
        // Log rotasi
        $pdo->prepare("INSERT INTO rotasi_log (id_bagian, dari_fase_id, ke_fase_id, tim_ids, jumlah_tim, tipe, oleh) VALUES (?,?,?,?,?,?,?)")
            ->execute([$bagianId, $dariFase, $keFase, json_encode($timIds), $rotated, 'manual', $_SESSION['user_id'] ?? null]);
        // Notifikasi in-app
        $pdo->prepare("INSERT INTO notifikasi_piket (tipe, judul, pesan) VALUES (?,?,?)")
            ->execute(['rotasi', 'Rotasi Manual', $rotated.' tim dirotasi ke fase berikutnya']);
        $pdo->commit();
        echo json_encode(['success'=>true,'rotated'=>$rotated,'message'=>$rotated.' tim dirotasi ke fase berikutnya']); exit;
    }

    // ─── DASHBOARD PIKET HARI INI (ringkasan lengkap) ──────────────────
    if ($action === 'dashboard_hari_ini') {
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT s.id, s.personil_id, s.personil_name, s.shift_type,
                   s.start_time, s.end_time, s.location, s.status,
                   t.nama_tim, t.id AS tim_id, b.nama_bagian,
                   pk.nama_pangkat,
                   pa.status AS absensi_status, pa.jam_hadir
            FROM schedules s
            JOIN tim_piket t ON t.id = s.tim_id
            LEFT JOIN bagian b ON b.id = t.id_bagian
            LEFT JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id AND pa.personil_id = s.personil_id
            WHERE s.shift_date = ? AND s.tim_id IS NOT NULL
            ORDER BY b.urutan, s.start_time, s.personil_name
        ");
        $stmt->execute([$today]);
        $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalJadwal = count($jadwal);
        $hadir = count(array_filter($jadwal, fn($j) => ($j['absensi_status'] ?? '') === 'hadir'));
        $belumCheckin = $totalJadwal - $hadir;
        $tidakHadir = count(array_filter($jadwal, fn($j) => in_array($j['absensi_status'] ?? '', ['tidak_hadir','sakit','ijin'])));
        echo json_encode([
            'success' => true,
            'tanggal' => $today,
            'jadwal'  => $jadwal,
            'stats'   => [
                'total_jadwal'  => $totalJadwal,
                'hadir'         => $hadir,
                'belum_checkin' => $belumCheckin - $tidakHadir,
                'tidak_hadir'   => $tidakHadir,
            ]
        ]); exit;
    }

    // ─── STATISTIK PERSONIL (jam piket per orang) ────────────────────────
    if ($action === 'statistik_personil') {
        $bulan = (int)($_GET['bulan'] ?? date('m'));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate   = date('Y-m-t', strtotime($startDate));
        $stmt = $pdo->prepare("
            SELECT s.personil_id, s.personil_name, b.nama_bagian,
                   COUNT(*) AS jumlah_jadwal,
                   SUM(TIMESTAMPDIFF(HOUR, s.start_time, 
                       CASE WHEN s.end_time < s.start_time 
                            THEN ADDTIME(s.end_time, '24:00:00') 
                            ELSE s.end_time END
                   )) AS total_jam,
                   SUM(CASE WHEN pa.status='hadir' THEN 1 ELSE 0 END) AS hadir,
                   SUM(CASE WHEN pa.status IN ('tidak_hadir','sakit','ijin') THEN 1 ELSE 0 END) AS absen
            FROM schedules s
            LEFT JOIN tim_piket t ON t.id = s.tim_id
            LEFT JOIN bagian b ON b.id = t.id_bagian
            LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id AND pa.personil_id = s.personil_id
            WHERE s.shift_date BETWEEN ? AND ? AND s.tim_id IS NOT NULL
            GROUP BY s.personil_id, s.personil_name, b.nama_bagian
            ORDER BY total_jam DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC),'bulan'=>$bulan,'tahun'=>$tahun]); exit;
    }

    // ─── CALENDAR DATA (jadwal per bulan) ────────────────────────────────
    if ($action === 'calendar_data') {
        $bulan = (int)($_GET['bulan'] ?? date('m'));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate   = date('Y-m-t', strtotime($startDate));
        $stmt = $pdo->prepare("
            SELECT s.shift_date, s.shift_type, s.personil_name, s.start_time, s.end_time,
                   t.nama_tim, b.nama_bagian
            FROM schedules s
            LEFT JOIN tim_piket t ON t.id = s.tim_id
            LEFT JOIN bagian b ON b.id = t.id_bagian
            WHERE s.shift_date BETWEEN ? AND ? AND s.tim_id IS NOT NULL
            ORDER BY s.shift_date, s.start_time
        ");
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $calendar = [];
        foreach ($rows as $r) {
            $calendar[$r['shift_date']][] = $r;
        }
        echo json_encode(['success'=>true,'data'=>$calendar,'bulan'=>$bulan,'tahun'=>$tahun]); exit;
    }

    // ─── FATIGUE CHECK (cek jeda istirahat personil) ─────────────────────
    if ($action === 'fatigue_check') {
        $personilId = trim($_GET['personil_id'] ?? '');
        $tanggal    = trim($_GET['tanggal'] ?? date('Y-m-d'));
        $minJeda    = (float)($_GET['min_jeda_jam'] ?? 12);
        if (!$personilId) throw new Exception('personil_id wajib');
        $stmt = $pdo->prepare("
            SELECT shift_date, start_time, end_time, shift_type
            FROM schedules 
            WHERE personil_id=? AND shift_date BETWEEN DATE_SUB(?,INTERVAL 1 DAY) AND DATE_ADD(?,INTERVAL 1 DAY)
            ORDER BY shift_date, start_time
        ");
        $stmt->execute([$personilId, $tanggal, $tanggal]);
        $jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $warnings = [];
        for ($i = 1; $i < count($jadwals); $i++) {
            $prev = $jadwals[$i-1];
            $curr = $jadwals[$i];
            $prevEnd   = new DateTime($prev['shift_date'].' '.$prev['end_time']);
            $currStart = new DateTime($curr['shift_date'].' '.$curr['start_time']);
            if ($prev['end_time'] < $prev['start_time']) $prevEnd->modify('+1 day');
            $diffHours = ($currStart->getTimestamp() - $prevEnd->getTimestamp()) / 3600;
            if ($diffHours < $minJeda && $diffHours >= 0) {
                $warnings[] = [
                    'tanggal'   => $curr['shift_date'],
                    'jeda_jam'  => round($diffHours, 1),
                    'min_jeda'  => $minJeda,
                    'shift_1'   => $prev['shift_type'].' ('.$prev['shift_date'].')',
                    'shift_2'   => $curr['shift_type'].' ('.$curr['shift_date'].')',
                    'pesan'     => 'Jeda istirahat hanya '.round($diffHours,1).' jam (minimal '.$minJeda.' jam)'
                ];
            }
        }
        echo json_encode(['success'=>true,'warnings'=>$warnings,'total_jadwal'=>count($jadwals)]); exit;
    }

    // ─── GET ROTASI LOG ──────────────────────────────────────────────────
    if ($action === 'get_rotasi_log') {
        $bagianId = (int)($_GET['id_bagian'] ?? 0);
        $limit    = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $where = $bagianId ? "WHERE r.id_bagian=?" : "";
        $params = $bagianId ? [$bagianId] : [];
        $stmt = $pdo->prepare("
            SELECT r.*, b.nama_bagian,
                   f1.nama_fase AS dari_fase, f2.nama_fase AS ke_fase,
                   u.username AS oleh_nama
            FROM rotasi_log r
            LEFT JOIN bagian b ON b.id = r.id_bagian
            LEFT JOIN siklus_piket_fase f1 ON f1.id = r.dari_fase_id
            LEFT JOIN siklus_piket_fase f2 ON f2.id = r.ke_fase_id
            LEFT JOIN users u ON u.id = r.oleh
            $where
            ORDER BY r.created_at DESC LIMIT $limit
        ");
        $stmt->execute($params);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ─── CETAK SPRIN DATA ────────────────────────────────────────────────
    if ($action === 'cetak_sprin_data') {
        $bagianId = (int)($_GET['id_bagian'] ?? 0);
        $tanggal  = trim($_GET['tanggal'] ?? date('Y-m-d'));
        if (!$bagianId) throw new Exception('id_bagian wajib');
        $bagian = $pdo->prepare("SELECT b.*, u.nama_unsur FROM bagian b LEFT JOIN unsur u ON u.id=b.id_unsur WHERE b.id=?");
        $bagian->execute([$bagianId]); $bagianInfo = $bagian->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("
            SELECT t.id, t.nama_tim, t.jenis, f.nama_fase, f.jam_mulai_default, f.durasi_jam,
                   a.personil_id, a.peran, a.urutan,
                   p.nama AS personil_nama, pk.nama_pangkat, j.nama_jabatan
            FROM tim_piket t
            LEFT JOIN siklus_piket_fase f ON f.id = t.fase_siklus_id
            LEFT JOIN tim_piket_anggota a ON a.tim_id = t.id
            LEFT JOIN personil p ON p.nrp = a.personil_id
            LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
            LEFT JOIN jabatan j ON j.id = p.id_jabatan
            WHERE t.id_bagian=? AND t.is_active=1
            ORDER BY t.nama_tim, a.urutan
        ");
        $stmt->execute([$bagianId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tims = [];
        foreach ($rows as $r) {
            $tid = $r['id'];
            if (!isset($tims[$tid])) {
                $tims[$tid] = [
                    'nama_tim'   => $r['nama_tim'],
                    'jenis'      => $r['jenis'],
                    'nama_fase'  => $r['nama_fase'],
                    'jam_mulai'  => $r['jam_mulai_default'],
                    'durasi_jam' => $r['durasi_jam'],
                    'anggota'    => []
                ];
            }
            if ($r['personil_id']) {
                $tims[$tid]['anggota'][] = [
                    'nrp'     => $r['personil_id'],
                    'nama'    => $r['personil_nama'],
                    'pangkat' => $r['nama_pangkat'],
                    'jabatan' => $r['nama_jabatan'],
                    'peran'   => $r['peran'],
                ];
            }
        }
        echo json_encode([
            'success' => true,
            'bagian'  => $bagianInfo,
            'tanggal' => $tanggal,
            'tims'    => array_values($tims)
        ]); exit;
    }

    // ─── GET NOTIFIKASI PIKET (unread) ─────────────────────────────────
    if ($action === 'get_notifikasi_piket') {
        $stmt = $pdo->prepare("SELECT * FROM notifikasi_piket WHERE is_read=0 ORDER BY created_at DESC LIMIT 20");
        $stmt->execute();
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
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
        $newId = $pdo->lastInsertId();
        ActivityLog::logCreate('tim_piket', $newId, "Created tim: $nama (jenis: $jenis)");
        echo json_encode(['success'=>true,'id'=>$newId,'message'=>'Tim berhasil dibuat']); exit;
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
        ActivityLog::logUpdate('tim_piket', $id, "Updated tim: $nama (jenis: $jenis)");
        echo json_encode(['success'=>true,'message'=>'Tim berhasil diupdate']); exit;
    }

    // ─── DELETE TIM ───────────────────────────────────────────────────────
    if ($action === 'delete_tim') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID tidak valid');
        // Get tim name before deletion for logging
        $stmt = $pdo->prepare("SELECT nama_tim FROM tim_piket WHERE id=?");
        $stmt->execute([$id]);
        $tim = $stmt->fetch(PDO::FETCH_ASSOC);
        $timName = $tim ? $tim['nama_tim'] : 'Unknown';
        $pdo->prepare("DELETE FROM tim_piket_anggota WHERE tim_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM tim_piket WHERE id=?")->execute([$id]);
        ActivityLog::logDelete('tim_piket', $id, "Deleted tim: $timName");
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

    // ─── SWAP SHIFT (ajukan tukar jadwal antar 2 personil) ───────────────
    if ($action === 'swap_shift') {
        $schedId1 = (int)($_POST['schedule_id_1'] ?? 0);
        $schedId2 = (int)($_POST['schedule_id_2'] ?? 0);
        if (!$schedId1 || !$schedId2) throw new Exception('Dua schedule_id diperlukan');

        $pdo->beginTransaction();
        // Ambil data kedua jadwal
        $s1 = $pdo->prepare("SELECT * FROM schedules WHERE id=?"); $s1->execute([$schedId1]); $sched1 = $s1->fetch(PDO::FETCH_ASSOC);
        $s2 = $pdo->prepare("SELECT * FROM schedules WHERE id=?"); $s2->execute([$schedId2]); $sched2 = $s2->fetch(PDO::FETCH_ASSOC);
        if (!$sched1 || !$sched2) throw new Exception('Jadwal tidak ditemukan');

        // Tukar personil
        $pdo->prepare("UPDATE schedules SET personil_id=?, personil_name=?, swap_with_schedule_id=?, swap_status='approved' WHERE id=?")
            ->execute([$sched2['personil_id'], $sched2['personil_name'], $schedId2, $schedId1]);
        $pdo->prepare("UPDATE schedules SET personil_id=?, personil_name=?, swap_with_schedule_id=?, swap_status='approved' WHERE id=?")
            ->execute([$sched1['personil_id'], $sched1['personil_name'], $schedId1, $schedId2]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Swap berhasil: '.$sched1['personil_name'].' ↔ '.$sched2['personil_name']]); exit;
    }

    // ─── SAVE ANGGOTA DENGAN PERAN ──────────────────────────────────────
    if ($action === 'save_anggota_peran') {
        $timId = (int)($_POST['tim_id'] ?? 0);
        if (!$timId) throw new Exception('tim_id tidak valid');
        $anggotaJson = $_POST['anggota'] ?? '[]';
        $anggota = json_decode($anggotaJson, true);
        if (!is_array($anggota)) throw new Exception('Format anggota tidak valid');

        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM tim_piket_anggota WHERE tim_id=?")->execute([$timId]);
        $ins = $pdo->prepare("INSERT INTO tim_piket_anggota (tim_id,personil_id,peran,urutan) VALUES (?,?,?,?)");
        foreach ($anggota as $i => $a) {
            $peran = in_array($a['peran'] ?? '', ['ketua','wakil','anggota']) ? $a['peran'] : 'anggota';
            $ins->execute([$timId, $a['personil_id'], $peran, $i+1]);
        }
        $pdo->commit();
        echo json_encode(['success'=>true,'count'=>count($anggota),'message'=>count($anggota).' anggota disimpan']); exit;
    }

    // ─── MARK NOTIFIKASI AS READ ──────────────────────────────────────
    if ($action === 'read_notifikasi') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE notifikasi_piket SET is_read=1 WHERE id=?")->execute([$id]);
        }
        echo json_encode(['success'=>true]); exit;
    }

    throw new Exception('Action tidak dikenal: '.$action);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
