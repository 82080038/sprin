<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) { die('DB error: '.$e->getMessage()); }

$bulan     = (int)($_GET['bulan']    ?? date('n'));
$tahun     = (int)($_GET['tahun']    ?? date('Y'));
$bagianId  = (int)($_GET['bagian_id'] ?? 0);
$bulan     = max(1, min(12, $bulan));
$tahun     = max(2020, min(2099, $tahun));

$PIKET_UNSUR = [3, 4];
$PIKET_EXTRA = [20];
$uph = implode(',', array_fill(0, count($PIKET_UNSUR), '?'));
$eph = implode(',', array_fill(0, count($PIKET_EXTRA), '?'));

// Daftar bagian
$stmtB = $pdo->prepare("
    SELECT b.id, b.nama_bagian, u.nama_unsur
    FROM bagian b
    LEFT JOIN unsur u ON u.id = b.id_unsur
    WHERE (b.id_unsur IN ($uph) OR b.id IN ($eph)) AND b.is_active=1
    ORDER BY u.id, b.urutan
");
$stmtB->execute(array_merge($PIKET_UNSUR, $PIKET_EXTRA));
$daftarBagian = $stmtB->fetchAll(PDO::FETCH_ASSOC);

$startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
$endDate   = date('Y-m-t', strtotime($startDate));
$totalHari = (int)date('t', strtotime($startDate));

// Rekap absensi per personil dalam bulan ini
$whereB = $bagianId ? 'AND t.id_bagian = ?' : '';
$params  = [$startDate, $endDate];
if ($bagianId) $params[] = $bagianId;

$stmtRekap = $pdo->prepare("
    SELECT
        s.personil_id,
        s.personil_name,
        b.nama_bagian,
        t.nama_tim,
        COUNT(DISTINCT s.id) AS total_jadwal,
        SUM(CASE WHEN pa.status = 'hadir'         THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN pa.status = 'sakit'         THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN pa.status = 'ijin'          THEN 1 ELSE 0 END) AS ijin,
        SUM(CASE WHEN pa.status = 'terlambat'     THEN 1 ELSE 0 END) AS terlambat,
        SUM(CASE WHEN pa.status = 'tidak_hadir'   THEN 1 ELSE 0 END) AS tidak_hadir,
        SUM(CASE WHEN pa.id IS NULL               THEN 1 ELSE 0 END) AS belum_absen
    FROM schedules s
    JOIN tim_piket t ON t.id = s.tim_id
    JOIN bagian b    ON b.id = t.id_bagian
    LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id AND pa.personil_id = s.personil_id
    WHERE s.shift_date BETWEEN ? AND ? AND s.tim_id IS NOT NULL $whereB
    GROUP BY s.personil_id, t.id
    ORDER BY b.nama_bagian, s.personil_name
");
$stmtRekap->execute($params);
$rekapRows = $stmtRekap->fetchAll(PDO::FETCH_ASSOC);

// Rekap per satuan
$rekapSatuan = [];
foreach ($rekapRows as $r) {
    $sat = $r['nama_bagian'];
    if (!isset($rekapSatuan[$sat])) {
        $rekapSatuan[$sat] = ['total_jadwal'=>0,'hadir'=>0,'sakit'=>0,'ijin'=>0,'terlambat'=>0,'tidak_hadir'=>0,'belum_absen'=>0,'orang'=>0];
    }
    foreach (['total_jadwal','hadir','sakit','ijin','terlambat','tidak_hadir','belum_absen'] as $k) {
        $rekapSatuan[$sat][$k] += (int)$r[$k];
    }
    $rekapSatuan[$sat]['orang']++;
}

$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$current_page = basename(__FILE__);
include __DIR__ . '/../includes/components/header.php';
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Rekap Absensi Piket</h4>
            <p class="text-muted mb-0"><?= $namaBulan[$bulan] . ' ' . $tahun ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" onclick="exportCSV()">
                <i class="fa-solid fa-file-csv me-1"></i>Export CSV
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="card card-body mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Bulan</label>
                <select class="form-select" name="bulan">
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m==$bulan?'selected':'' ?>><?= $namaBulan[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Tahun</label>
                <input type="number" class="form-control" name="tahun" value="<?= $tahun ?>" min="2020" max="2099">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Satuan</label>
                <select class="form-select" name="bagian_id">
                    <option value="">Semua Satuan</option>
                    <?php foreach ($daftarBagian as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id']==$bagianId?'selected':'' ?>><?= htmlspecialchars($b['nama_bagian']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search me-1"></i>Tampilkan</button>
            </div>
        </div>
    </form>

    <!-- Summary Cards per Satuan -->
    <?php if (!empty($rekapSatuan)): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($rekapSatuan as $sat => $rs):
            $pct = $rs['total_jadwal'] > 0 ? round(($rs['hadir'] + $rs['terlambat']) / $rs['total_jadwal'] * 100) : 0;
            $cls = $pct >= 90 ? 'success' : ($pct >= 70 ? 'warning' : 'danger');
        ?>
        <div class="col-md-3">
            <div class="card border-<?= $cls ?> h-100">
                <div class="card-body py-2 px-3">
                    <div class="fw-bold small text-truncate" title="<?= htmlspecialchars($sat) ?>"><?= htmlspecialchars($sat) ?></div>
                    <div class="fs-4 fw-bold text-<?= $cls ?>"><?= $pct ?>%</div>
                    <div class="small text-muted"><?= $rs['orang'] ?> personil · <?= $rs['total_jadwal'] ?> jadwal</div>
                    <div class="progress mt-1" style="height:4px;">
                        <div class="progress-bar bg-<?= $cls ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($rekapRows)): ?>
    <div class="alert alert-info"><i class="fa-solid fa-info-circle me-1"></i>Tidak ada data absensi untuk periode ini.</div>
    <?php else: ?>

    <!-- Tabel Rekap Per Personil -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fa-solid fa-table me-1"></i>Detail Per Personil</span>
            <span class="badge bg-primary"><?= count($rekapRows) ?> personil</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0" id="rekapTable">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Satuan</th>
                        <th>Tim</th>
                        <th>Personil</th>
                        <th class="text-center">Jadwal</th>
                        <th class="text-center text-success">Hadir</th>
                        <th class="text-center text-warning">Terlambat</th>
                        <th class="text-center text-info">Sakit</th>
                        <th class="text-center text-secondary">Ijin</th>
                        <th class="text-center text-danger">Tdk Hadir</th>
                        <th class="text-center text-muted">Blm Input</th>
                        <th class="text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=0; $prevSat=''; foreach ($rekapRows as $r):
                        $no++;
                        $pct = $r['total_jadwal'] > 0 ? round(($r['hadir'] + $r['terlambat']) / $r['total_jadwal'] * 100) : 0;
                        $cls = $pct >= 90 ? 'success' : ($pct >= 70 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td class="text-muted small"><?= $no ?></td>
                        <td class="small"><?= htmlspecialchars($r['nama_bagian']) ?></td>
                        <td class="small"><?= htmlspecialchars($r['nama_tim']) ?></td>
                        <td class="fw-semibold small"><?= htmlspecialchars($r['personil_name']) ?></td>
                        <td class="text-center"><?= $r['total_jadwal'] ?></td>
                        <td class="text-center text-success fw-bold"><?= $r['hadir'] ?></td>
                        <td class="text-center text-warning"><?= $r['terlambat'] ?></td>
                        <td class="text-center text-info"><?= $r['sakit'] ?></td>
                        <td class="text-center text-secondary"><?= $r['ijin'] ?></td>
                        <td class="text-center text-danger"><?= $r['tidak_hadir'] ?></td>
                        <td class="text-center text-muted"><?= $r['belum_absen'] ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $cls ?>"><?= $pct ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .navbar, form, .btn, .breadcrumb { display: none !important; }
    .container-fluid { padding: 0 !important; }
    .card { border: 1px solid #ccc !important; }
}
</style>

<script>
function exportCSV() {
    const rows = [['Satuan','Tim','Personil','Jadwal','Hadir','Terlambat','Sakit','Ijin','Tdk Hadir','Blm Input','% Hadir']];
    document.querySelectorAll('#rekapTable tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length) rows.push([...cells].map(c => c.textContent.trim()));
    });
    const csv = rows.map(r => r.map(c => '"'+c.replace(/"/g,'""')+'"').join(',')).join('\n');
    const blob = new Blob(['\uFEFF'+csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'rekap_absensi_<?= $namaBulan[$bulan] ?>_<?= $tahun ?>.csv';
    a.click();
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
