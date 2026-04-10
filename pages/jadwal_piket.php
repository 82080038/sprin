<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../core/config.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) { die('DB error: '.$e->getMessage()); }

$bulan  = (int)($_GET['bulan'] ?? date('n'));
$tahun  = (int)($_GET['tahun'] ?? date('Y'));
$timId  = (int)($_GET['tim_id'] ?? 0);
$bulan  = max(1, min(12, $bulan));
$tahun  = max(2020, min(2099, $tahun));

// Daftar tim (filter bagian piket)
$PIKET_UNSUR  = [3, 4];
$PIKET_EXTRA  = [20];
$uph = implode(',', array_fill(0, count($PIKET_UNSUR), '?'));
$eph = implode(',', array_fill(0, count($PIKET_EXTRA), '?'));
$stmtTim = $pdo->prepare("
    SELECT t.id, t.nama_tim, b.nama_bagian, u.nama_unsur
    FROM tim_piket t
    LEFT JOIN bagian b ON b.id = t.id_bagian
    LEFT JOIN unsur  u ON u.id = b.id_unsur
    WHERE (b.id_unsur IN ($uph) OR b.id IN ($eph)) AND t.is_active=1
    ORDER BY u.id, b.urutan, t.nama_tim
");
$stmtTim->execute(array_merge($PIKET_UNSUR, $PIKET_EXTRA));
$allTim = $stmtTim->fetchAll(PDO::FETCH_ASSOC);

$currentTim = null;
$jadwalRows  = [];
$totalHadir  = 0;
$uniquePersonil = [];
$batchGroups = [];

if ($timId) {
    // Info tim terpilih
    $stmtCT = $pdo->prepare("
        SELECT t.*, b.nama_bagian, u.nama_unsur
        FROM tim_piket t
        LEFT JOIN bagian b ON b.id = t.id_bagian
        LEFT JOIN unsur  u ON u.id = b.id_unsur
        WHERE t.id = ?");
    $stmtCT->execute([$timId]);
    $currentTim = $stmtCT->fetch(PDO::FETCH_ASSOC);

    // Jadwal bulan ini
    $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
    $endDate   = date('Y-m-t', strtotime($startDate));
    $stmtJ = $pdo->prepare("
        SELECT s.id, s.shift_date, s.personil_id, s.personil_name, s.shift_type,
               s.start_time, s.end_time, s.location, s.status,
               s.recurrence_type, s.recurrence_end,
               pk.nama_pangkat, b.nama_bagian AS bagian_personil,
               pa.status AS absensi_status, pa.jam_hadir, pa.catatan AS absensi_catatan
        FROM schedules s
        LEFT JOIN personil p   ON p.nrp = s.personil_id
        LEFT JOIN pangkat  pk  ON pk.id = p.id_pangkat
        LEFT JOIN bagian   b   ON b.id  = p.id_bagian
        LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id AND pa.personil_id = s.personil_id
        WHERE s.tim_id = ? AND s.shift_date BETWEEN ? AND ?
        ORDER BY s.shift_date ASC, s.start_time ASC, s.personil_name ASC
    ");
    $stmtJ->execute([$timId, $startDate, $endDate]);
    $jadwalRows = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan per tanggal
    foreach ($jadwalRows as $row) {
        $batchGroups[$row['shift_date']][] = $row;
        $uniquePersonil[$row['personil_id']] = true;
    }

    // Hitung kehadiran
    foreach ($jadwalRows as $r) {
        if ($r['absensi_status'] === 'hadir') $totalHadir++;
    }
}

$bulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../includes/components/header.php';
?>
<style>
.jadwal-header { background: linear-gradient(135deg,#1a237e,#283593); color:#fff; border-radius:12px; padding:24px; margin-bottom:24px; }
.stat-card { background:#fff; border-radius:10px; padding:16px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.stat-card .num { font-size:2rem; font-weight:700; color:#1a237e; }
.stat-card .lbl { font-size:.8rem; color:#666; text-transform:uppercase; letter-spacing:.5px; }
.shift-badge { font-size:.75rem; padding:2px 8px; border-radius:20px; font-weight:600; }
.shift-PAGI     { background:#fff3cd; color:#856404; }
.shift-SIANG    { background:#cfe2ff; color:#0a58ca; }
.shift-MALAM    { background:#d1ecf1; color:#0c5460; }
.shift-FULL_DAY { background:#d4edda; color:#155724; }
.shift-ROTASI   { background:#f8d7da; color:#721c24; }
.absensi-hadir        { color:#198754; font-weight:600; }
.absensi-tidak_hadir  { color:#dc3545; font-weight:600; }
.absensi-sakit        { color:#fd7e14; font-weight:600; }
.absensi-ijin         { color:#6c757d; font-weight:600; }
.absensi-terlambat    { color:#ffc107; font-weight:600; }
.date-header { background:#e8eaf6; padding:8px 16px; border-radius:6px; font-weight:700; color:#1a237e; margin:16px 0 8px; }
.print-area { display:none; }
@media print {
    .no-print   { display:none !important; }
    .print-area { display:block; }
    body        { background:#fff; }
}
</style>

<div class="container-fluid py-3 no-print">
  <!-- Header -->
  <div class="jadwal-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h4 class="mb-1"><i class="fa-solid fa-calendar-week me-2"></i>Jadwal Piket</h4>
        <p class="mb-0 opacity-75">
          <?php if ($currentTim): ?>
            <strong><?= htmlspecialchars($currentTim['nama_tim']) ?></strong> —
            <?= htmlspecialchars($currentTim['nama_bagian'] ?? '-') ?> |
            <?= $bulanNama[$bulan] ?> <?= $tahun ?>
          <?php else: ?>
            Pilih tim dan bulan untuk melihat jadwal
          <?php endif; ?>
        </p>
      </div>
      <div class="d-flex gap-2">
        <a href="../pages/tim_piket.php" class="btn btn-light btn-sm">
          <i class="fa-solid fa-arrow-left me-1"></i>Tim Piket
        </a>
        <?php if ($timId): ?>
        <button class="btn btn-warning btn-sm" onclick="window.print()">
          <i class="fa-solid fa-print me-1"></i>Cetak
        </button>
        <button class="btn btn-danger btn-sm" onclick="confirmHapusSeries()">
          <i class="fa-solid fa-trash-can me-1"></i>Hapus Series
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <!-- Filter -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-bold small">Tim / Regu</label>
              <select name="tim_id" class="form-select form-select-sm" required>
                <option value="">-- Pilih Tim --</option>
                <?php
                $lastBagian = '';
                foreach ($allTim as $t):
                    if ($t['nama_bagian'] !== $lastBagian):
                        if ($lastBagian !== '') echo '</optgroup>';
                        echo '<optgroup label="'.htmlspecialchars($t['nama_bagian']).'">';
                        $lastBagian = $t['nama_bagian'];
                    endif;
                ?>
                  <option value="<?= $t['id'] ?>" <?= $timId==$t['id']?'selected':''; ?>>
                    <?= htmlspecialchars($t['nama_tim']) ?>
                  </option>
                <?php endforeach; if ($lastBagian) echo '</optgroup>'; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small">Bulan</label>
              <select name="bulan" class="form-select form-select-sm">
                <?php for ($m=1; $m<=12; $m++): ?>
                  <option value="<?= $m ?>" <?= $m==$bulan?'selected':''; ?>><?= $bulanNama[$m] ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-bold small">Tahun</label>
              <select name="tahun" class="form-select form-select-sm">
                <?php for ($y=2024; $y<=2027; $y++): ?>
                  <option value="<?= $y ?>" <?= $y==$tahun?'selected':''; ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fa-solid fa-search me-1"></i>Tampilkan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Statistik -->
    <?php if ($currentTim && $jadwalRows): ?>
    <div class="col-lg-4">
      <div class="row g-2 h-100">
        <div class="col-4">
          <div class="stat-card h-100">
            <div class="num"><?= count($jadwalRows) ?></div>
            <div class="lbl">Total Jadwal</div>
          </div>
        </div>
        <div class="col-4">
          <div class="stat-card h-100">
            <div class="num"><?= count($uniquePersonil) ?></div>
            <div class="lbl">Personil</div>
          </div>
        </div>
        <div class="col-4">
          <div class="stat-card h-100">
            <div class="num"><?= count($batchGroups) ?></div>
            <div class="lbl">Hari Aktif</div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tabel Jadwal -->
  <?php if ($timId && $jadwalRows): ?>
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>
        <i class="fa-solid fa-list me-2 text-primary"></i>
        Jadwal <?= $bulanNama[$bulan] ?> <?= $tahun ?> —
        <?= htmlspecialchars($currentTim['nama_tim']) ?>
      </strong>
      <span class="badge bg-primary"><?= count($jadwalRows) ?> jadwal</span>
    </div>
    <div class="card-body p-0">
      <?php foreach ($batchGroups as $tgl => $rows):
        $dtObj   = new DateTime($tgl);
        $hariIni = $tgl === date('Y-m-d');
        $hariNm  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][$dtObj->format('w')];
        $tglFmt  = $dtObj->format('d') . ' ' . $bulanNama[(int)$dtObj->format('n')] . ' ' . $dtObj->format('Y');
      ?>
        <div class="date-header <?= $hariIni ? 'bg-warning text-dark' : '' ?> ms-3 me-3">
          <i class="fa-solid fa-calendar-day me-2"></i>
          <?= $hariNm ?>, <?= $tglFmt ?>
          <?php if ($hariIni): ?><span class="badge bg-danger ms-2">Hari Ini</span><?php endif; ?>
        </div>
        <div class="table-responsive px-3 pb-2">
          <table class="table table-sm table-hover align-middle mb-1">
            <thead class="table-light small">
              <tr>
                <th>#</th>
                <th>Personil</th>
                <th>Shift</th>
                <th>Jam</th>
                <th>Lokasi</th>
                <th>Absensi</th>
                <th class="no-print">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $i => $r): ?>
              <tr>
                <td class="text-muted small"><?= $i+1 ?></td>
                <td>
                  <div class="fw-bold"><?= htmlspecialchars($r['personil_name'] ?? $r['personil_id']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($r['nama_pangkat'] ?? '') ?> · <?= htmlspecialchars($r['bagian_personil'] ?? '') ?></small>
                </td>
                <td>
                  <span class="shift-badge shift-<?= $r['shift_type'] ?>"><?= $r['shift_type'] ?></span>
                </td>
                <td class="small">
                  <?= substr($r['start_time'],0,5) ?> – <?= substr($r['end_time'],0,5) ?>
                </td>
                <td class="small"><?= htmlspecialchars($r['location'] ?? '-') ?></td>
                <td>
                  <?php if ($r['absensi_status']): ?>
                    <span class="absensi-<?= $r['absensi_status'] ?>">
                      <?php $icons = ['hadir'=>'✔','tidak_hadir'=>'✘','sakit'=>'🤒','ijin'=>'📋','terlambat'=>'⏰']; ?>
                      <?= $icons[$r['absensi_status']] ?? '?' ?>
                      <?= ucfirst(str_replace('_',' ',$r['absensi_status'])) ?>
                    </span>
                    <?php if ($r['jam_hadir']): ?>
                      <small class="text-muted d-block"><?= substr($r['jam_hadir'],0,5) ?></small>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted small">—</span>
                  <?php endif; ?>
                </td>
                <td class="no-print">
                  <div class="d-flex gap-1">
                  <button class="btn btn-outline-success btn-sm py-0 px-2"
                    onclick="inputAbsensi(<?= $r['id'] ?>, '<?= htmlspecialchars($r['personil_id']) ?>',
                      '<?= htmlspecialchars(addslashes($r['personil_name'] ?? $r['personil_id'])) ?>',
                      '<?= $r['absensi_status'] ?>', '<?= $r['jam_hadir'] ?>', '<?= $tgl ?>')"
                    title="Input Absensi">
                    <i class="fa-solid fa-clipboard-check"></i>
                  </button>
                  <button class="btn btn-outline-warning btn-sm py-0 px-2"
                    onclick="openCover(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['personil_name'] ?? $r['personil_id'])) ?>')"
                    title="Ganti Personil (Cover)">
                    <i class="fa-solid fa-user-gear"></i>
                  </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php elseif ($timId): ?>
  <div class="alert alert-info">
    <i class="fa-solid fa-info-circle me-2"></i>
    Tidak ada jadwal untuk tim ini di <?= $bulanNama[$bulan] ?> <?= $tahun ?>.
    <a href="tim_piket.php" class="alert-link">Generate jadwal dari halaman Tim Piket.</a>
  </div>
  <?php else: ?>
  <div class="alert alert-secondary text-center py-5">
    <i class="fa-solid fa-calendar-xmark fa-3x mb-3 d-block opacity-50"></i>
    <p class="mb-0">Pilih tim dan klik <strong>Tampilkan</strong> untuk melihat jadwal.</p>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Cover / Substitusi Personil -->
<div class="modal fade" id="modalCover" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-gear me-2"></i>Ganti Personil (Cover)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 small mb-3">
          <i class="fa-solid fa-person-walking-arrow-right me-1"></i>
          Personil <strong id="coverNamaAsli"></strong> akan dicatat <span class="badge bg-danger">Tidak Hadir</span> dan digantikan oleh personil pilihan.
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Personil Pengganti</label>
          <select class="form-select" id="coverSelect"><option value="">-- Pilih Pengganti --</option></select>
          <div class="text-muted small mt-1" id="coverLoading"></div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan</label>
          <input type="text" class="form-control" id="coverCatatan" placeholder="Alasan / keterangan">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" onclick="simpanCover()"><i class="fa-solid fa-save me-1"></i>Simpan Cover</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Absensi -->
<div class="modal fade" id="modalAbsensi" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h6 class="modal-title"><i class="fa-solid fa-clipboard-check me-2"></i>Input Absensi</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2 fw-bold" id="absNama"></p>
        <p class="mb-3 text-muted small" id="absTanggal"></p>
        <input type="hidden" id="absScheduleId">
        <input type="hidden" id="absPersonilId">
        <div class="mb-3">
          <label class="form-label fw-bold small">Status Kehadiran</label>
          <select class="form-select form-select-sm" id="absStatus">
            <option value="hadir">✔ Hadir</option>
            <option value="tidak_hadir">✘ Tidak Hadir</option>
            <option value="sakit">🤒 Sakit</option>
            <option value="ijin">📋 Ijin</option>
            <option value="terlambat">⏰ Terlambat</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold small">Jam Hadir (opsional)</label>
          <input type="time" class="form-control form-control-sm" id="absJamHadir">
        </div>
        <div class="mb-2">
          <label class="form-label fw-bold small">Catatan</label>
          <textarea class="form-control form-control-sm" id="absCatatan" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success btn-sm" onclick="saveAbsensi()">
          <i class="fa-solid fa-save me-1"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h6 class="modal-title"><i class="fa-solid fa-trash-can me-2"></i>Hapus Jadwal Series</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <strong>⚠️ Perhatian!</strong> Tindakan ini akan menghapus <strong>semua jadwal</strong> tim
          <strong><?= htmlspecialchars($currentTim['nama_tim'] ?? '') ?></strong>
          untuk bulan <strong><?= $bulanNama[$bulan] ?> <?= $tahun ?></strong>.
          <br><br>Data absensi yang sudah diinput juga akan terhapus.
        </div>
        <p>Ketik <strong>HAPUS</strong> untuk konfirmasi:</p>
        <input type="text" class="form-control" id="konfirmasiHapus" placeholder="Ketik HAPUS">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-danger" onclick="executeHapusSeries()">
          <i class="fa-solid fa-trash-can me-1"></i>Hapus Permanen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const API_URL = '../api/tim_piket_api.php';

function inputAbsensi(schedId, personilId, nama, currentStatus, jamHadir, tanggal) {
    document.getElementById('absScheduleId').value = schedId;
    document.getElementById('absPersonilId').value  = personilId;
    document.getElementById('absNama').textContent   = nama;
    document.getElementById('absTanggal').textContent = new Date(tanggal+'T00:00').toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
    document.getElementById('absStatus').value   = currentStatus || 'hadir';
    document.getElementById('absJamHadir').value = jamHadir ? jamHadir.substring(0,5) : '';
    document.getElementById('absCatatan').value  = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAbsensi')).show();
}

async function saveAbsensi() {
    const fd = new FormData();
    fd.append('action',       'save_absensi');
    fd.append('schedule_id',  document.getElementById('absScheduleId').value);
    fd.append('personil_id',  document.getElementById('absPersonilId').value);
    fd.append('tim_id',       <?= $timId ?>);
    fd.append('tanggal',      '<?= sprintf('%04d-%02d', $tahun, $bulan) ?>-01');
    fd.append('status',       document.getElementById('absStatus').value);
    fd.append('jam_hadir',    document.getElementById('absJamHadir').value);
    fd.append('catatan',      document.getElementById('absCatatan').value);
    try {
        const res  = await fetch(API_URL, { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAbsensi')).hide();
            location.reload();
        } else {
            alert('Gagal: ' + data.error);
        }
    } catch(e) { alert('Network error'); }
}

let _coverSchedId = null;
async function openCover(schedId, namaAsli) {
    _coverSchedId = schedId;
    document.getElementById('coverNamaAsli').textContent = namaAsli;
    document.getElementById('coverCatatan').value = '';
    const sel = document.getElementById('coverSelect');
    sel.innerHTML = '<option value="">Memuat...</option>';
    document.getElementById('coverLoading').textContent = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCover')).show();
    try {
        const r    = await fetch(API_URL + '?action=get_cover_candidates&schedule_id=' + schedId);
        const data = await r.json();
        sel.innerHTML = '<option value="">-- Pilih Pengganti --</option>';
        if (data.success && data.data.length) {
            data.data.forEach(p => {
                const o = document.createElement('option');
                o.value = p.nrp;
                o.textContent = (p.nama_pangkat || '') + ' ' + p.nama;
                o.dataset.nama = p.nama;
                sel.appendChild(o);
            });
            document.getElementById('coverLoading').textContent = data.data.length + ' personil tersedia';
        } else {
            sel.innerHTML = '<option value="">Tidak ada personil tersedia</option>';
        }
    } catch(e) { sel.innerHTML = '<option value="">Error memuat data</option>'; }
}

async function simpanCover() {
    const sel     = document.getElementById('coverSelect');
    const newNrp  = sel.value;
    const opt     = sel.options[sel.selectedIndex];
    const newName = opt ? opt.dataset.nama || opt.textContent : '';
    if (!newNrp) { alert('Pilih personil pengganti terlebih dahulu'); return; }
    const fd = new FormData();
    fd.append('action',           'save_cover');
    fd.append('schedule_id',      _coverSchedId);
    fd.append('new_personil_id',  newNrp);
    fd.append('new_personil_name', newName);
    fd.append('catatan',          document.getElementById('coverCatatan').value);
    try {
        const r    = await fetch(API_URL, { method:'POST', body:fd });
        const data = await r.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCover')).hide();
            location.reload();
        } else { alert('Gagal: ' + (data.error || data.message)); }
    } catch(e) { alert('Network error'); }
}

function confirmHapusSeries() {
    document.getElementById('konfirmasiHapus').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHapus')).show();
}

async function executeHapusSeries() {
    if (document.getElementById('konfirmasiHapus').value !== 'HAPUS') {
        alert('Ketik HAPUS untuk konfirmasi'); return;
    }
    const fd = new FormData();
    fd.append('action', 'delete_jadwal_series');
    fd.append('tim_id', <?= $timId ?>);
    fd.append('bulan',  <?= $bulan ?>);
    fd.append('tahun',  <?= $tahun ?>);
    try {
        const res  = await fetch(API_URL, { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHapus')).hide();
            alert(data.message);
            location.reload();
        } else {
            alert('Gagal: ' + data.error);
        }
    } catch(e) { alert('Network error'); }
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
