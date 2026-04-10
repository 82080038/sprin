<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../core/config.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) { die('DB error: '.$e->getMessage()); }

$tahun     = (int)($_GET['tahun']  ?? date('Y'));
$bulan     = (int)($_GET['bulan']  ?? 0);
$tahun     = max(2020, min(2099, $tahun));
$bulan     = max(0, min(12, $bulan));

// Tanggal range
if ($bulan > 0) {
    $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
    $endDate   = date('Y-m-t', strtotime($startDate));
} else {
    $startDate = "$tahun-01-01";
    $endDate   = "$tahun-12-31";
}

// Statistik ringkasan
$stmtStat = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='active'    THEN 1 ELSE 0 END) AS aktif,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN status='planned'   THEN 1 ELSE 0 END) AS rencana,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS batal,
        COALESCE(SUM(CAST(REPLACE(REPLACE(dukgra,'.',''),',','.') AS DECIMAL(15,0))), 0) AS total_dukgra,
        SUM(COALESCE(kuat_personil,0)) AS total_personil
    FROM operations
    WHERE operation_month BETWEEN ? AND ?
");
$stmtStat->execute([$startDate, $endDate]);
$stat = $stmtStat->fetch(PDO::FETCH_ASSOC);

// Per jenis operasi
$stmtJenis = $pdo->prepare("
    SELECT jenis_operasi, COUNT(*) AS cnt,
           COALESCE(SUM(CAST(REPLACE(REPLACE(dukgra,'.',''),',','.') AS DECIMAL(15,0))),0) AS dukgra
    FROM operations WHERE operation_month BETWEEN ? AND ?
    GROUP BY jenis_operasi ORDER BY cnt DESC
");
$stmtJenis->execute([$startDate, $endDate]);
$perJenis = $stmtJenis->fetchAll(PDO::FETCH_ASSOC);

// Per tingkat
$stmtTingkat = $pdo->prepare("
    SELECT tingkat_operasi, COUNT(*) AS cnt
    FROM operations WHERE operation_month BETWEEN ? AND ?
    GROUP BY tingkat_operasi ORDER BY cnt DESC
");
$stmtTingkat->execute([$startDate, $endDate]);
$perTingkat = $stmtTingkat->fetchAll(PDO::FETCH_ASSOC);

// Per bulan (jika tampil 1 tahun)
$perBulanData = [];
if ($bulan === 0) {
    $stmtBulan = $pdo->prepare("
        SELECT DATE_FORMAT(operation_month,'%Y-%m') AS ym, COUNT(*) AS cnt,
               COALESCE(SUM(CAST(REPLACE(REPLACE(dukgra,'.',''),',','.') AS DECIMAL(15,0))),0) AS dukgra
        FROM operations WHERE operation_month BETWEEN ? AND ?
        GROUP BY ym ORDER BY ym
    ");
    $stmtBulan->execute([$startDate, $endDate]);
    $perBulanData = $stmtBulan->fetchAll(PDO::FETCH_ASSOC);
}

// Daftar operasi
$stmtList = $pdo->prepare("
    SELECT * FROM operations WHERE operation_month BETWEEN ? AND ?
    ORDER BY operation_month DESC, operation_date DESC
");
$stmtList->execute([$startDate, $endDate]);
$listOp = $stmtList->fetchAll(PDO::FETCH_ASSOC);

$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$labelJenis = [
    'pemeliharaan_keamanan'=>'Harkam','pengamanan_kegiatan'=>'Pamgiat',
    'penegakan_hukum'=>'Gakkum','intelijen'=>'Intel',
    'pemulihan_keamanan'=>'Pulkam','kontinjensi'=>'Kontinjensi','lainnya'=>'Lainnya'
];
$labelTingkat = [
    'kewilayahan_polres'=>'Polres','kewilayahan_polda'=>'Polda',
    'terpusat'=>'Terpusat','imbangan'=>'Imbangan'
];

$current_page = basename(__FILE__);
include __DIR__ . '/../includes/components/header.php';
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-chart-bar me-2 text-danger"></i>Laporan Operasi</h4>
            <p class="text-muted mb-0"><?= $bulan ? $namaBulan[$bulan].' '.$tahun : 'Tahun '.$tahun ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" onclick="exportCSV()"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Cetak</button>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="card card-body mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Bulan</label>
                <select class="form-select" name="bulan">
                    <option value="0">Semua Bulan</option>
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m==$bulan?'selected':'' ?>><?= $namaBulan[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Tahun</label>
                <input type="number" class="form-control" name="tahun" value="<?= $tahun ?>" min="2020" max="2099">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search me-1"></i>Tampilkan</button>
            </div>
        </div>
    </form>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary"><?= $stat['total'] ?></div>
                <div class="small text-muted">Total Operasi</div>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body py-3">
                <div class="fs-3 fw-bold text-warning"><?= $stat['aktif'] ?></div>
                <div class="small text-muted">Berlangsung</div>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body py-3">
                <div class="fs-3 fw-bold text-success"><?= $stat['selesai'] ?></div>
                <div class="small text-muted">Selesai</div>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body py-3">
                <div class="fs-3 fw-bold text-secondary"><?= $stat['rencana'] ?></div>
                <div class="small text-muted">Rencana</div>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body py-3">
                <div class="fs-3 fw-bold text-info"><?= number_format($stat['total_personil']) ?></div>
                <div class="small text-muted">Personil Terlibat</div>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100 border-success"><div class="card-body py-3">
                <div class="fs-6 fw-bold text-success">Rp <?= number_format((float)$stat['total_dukgra'],0,',','.') ?></div>
                <div class="small text-muted">Total Dukgra</div>
            </div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Grafik per Jenis -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-chart-pie me-1"></i>Per Jenis Operasi</div>
                <div class="card-body"><canvas id="chartJenis" height="220"></canvas></div>
            </div>
        </div>
        <!-- Grafik per Tingkat -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-layer-group me-1"></i>Per Tingkat</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <?php foreach ($perTingkat as $pt): ?>
                        <tr>
                            <td><?= htmlspecialchars($labelTingkat[$pt['tingkat_operasi']] ?? $pt['tingkat_operasi']) ?></td>
                            <td class="text-end"><span class="badge bg-primary"><?= $pt['cnt'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
        <!-- Trend bulanan -->
        <?php if (!empty($perBulanData)): ?>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-chart-line me-1"></i>Trend Bulanan</div>
                <div class="card-body"><canvas id="chartBulan" height="200"></canvas></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Daftar -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-bold"><i class="fa-solid fa-list me-1"></i>Daftar Operasi</span>
            <span class="badge bg-primary"><?= count($listOp) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0" id="opTable">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Bulan</th><th>Nama Operasi</th><th>Jenis</th><th>Tingkat</th>
                        <th>Tanggal</th><th class="text-center">Personil</th><th class="text-end">Dukgra</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=0; foreach ($listOp as $op):
                        $no++;
                        $statusCls = ['planned'=>'secondary','active'=>'warning text-dark','completed'=>'success','cancelled'=>'danger'];
                        $statusLbl = ['planned'=>'Rencana','active'=>'Berlangsung','completed'=>'Selesai','cancelled'=>'Batal'];
                    ?>
                    <tr>
                        <td class="small text-muted"><?= $no ?></td>
                        <td class="small"><?= $op['operation_month'] ?></td>
                        <td class="fw-semibold small"><?= htmlspecialchars($op['operation_name']) ?></td>
                        <td class="small"><?= htmlspecialchars($labelJenis[$op['jenis_operasi']] ?? $op['jenis_operasi'] ?? '-') ?></td>
                        <td class="small"><?= htmlspecialchars($labelTingkat[$op['tingkat_operasi']] ?? $op['tingkat_operasi'] ?? '-') ?></td>
                        <td class="small"><?= $op['operation_date'] ? date('d/m/Y', strtotime($op['operation_date'])) : '-' ?></td>
                        <td class="text-center small"><?= $op['kuat_personil'] ?? '-' ?></td>
                        <td class="text-end small">Rp <?= $op['dukgra'] ? number_format((float)str_replace(['.', ','], ['', '.'], $op['dukgra']), 0, ',', '.') : '0' ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $statusCls[$op['status']] ?? 'secondary' ?>">
                                <?= $statusLbl[$op['status']] ?? $op['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, form, .btn { display: none !important; }
    .container-fluid { padding: 0 !important; }
}
</style>

<script>
const jenisLabels = <?= json_encode(array_map(fn($r) => $labelJenis[$r['jenis_operasi']] ?? $r['jenis_operasi'], $perJenis)) ?>;
const jenisCnt   = <?= json_encode(array_column($perJenis, 'cnt')) ?>;

new Chart(document.getElementById('chartJenis'), {
    type: 'doughnut',
    data: {
        labels: jenisLabels,
        datasets: [{ data: jenisCnt,
            backgroundColor: ['#4285F4','#EA4335','#FBBC04','#34A853','#FF6F00','#9E9E9E','#673AB7'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
});

<?php if (!empty($perBulanData)): ?>
const bulanLabels = <?= json_encode(array_map(fn($r) => substr($r['ym'],5), $perBulanData)) ?>;
const bulanCnt    = <?= json_encode(array_column($perBulanData, 'cnt')) ?>;
new Chart(document.getElementById('chartBulan'), {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [{ label: 'Operasi', data: bulanCnt, backgroundColor: '#4285F4' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
<?php endif; ?>

function exportCSV() {
    const rows = [['Bulan','Nama Operasi','Jenis','Tingkat','Tanggal','Personil','Dukgra','Status']];
    document.querySelectorAll('#opTable tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length) rows.push([...cells].slice(1).map(c => c.textContent.trim()));
    });
    const csv = rows.map(r => r.map(c => '"'+c.replace(/"/g,'""')+'"').join(',')).join('\n');
    const blob = new Blob(['\uFEFF'+csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'laporan_operasi_<?= $tahun ?>.csv';
    a.click();
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
