<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator', 'viewer');

$page_title = 'Daftar Operasi - BAGOPS POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Fetch operations from DB
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->query("SELECT * FROM operations ORDER BY operation_month DESC, operation_date DESC, created_at DESC");
    $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $operations = [];
    $dbError = $e->getMessage();
}

$labelTingkat = [
    'terpusat'           => ['label' => 'Terpusat',          'class' => 'bg-danger'],
    'kewilayahan_polda'  => ['label' => 'Kewilayahan Polda', 'class' => 'bg-warning text-dark'],
    'kewilayahan_polres' => ['label' => 'Kewilayahan Polres','class' => 'bg-primary'],
    'imbangan'           => ['label' => 'Imbangan',          'class' => 'bg-secondary'],
];
$labelJenis = [
    'intelijen'            => ['label' => 'Intelijen',             'class' => 'bg-dark'],
    'pengamanan_kegiatan'  => ['label' => 'Pengamanan Kegiatan',   'class' => 'bg-info text-dark'],
    'pemeliharaan_keamanan'=> ['label' => 'Pemeliharaan Keamanan', 'class' => 'bg-primary'],
    'penegakan_hukum'      => ['label' => 'Penegakan Hukum',       'class' => 'bg-danger'],
    'pemulihan_keamanan'   => ['label' => 'Pemulihan Keamanan',    'class' => 'bg-warning text-dark'],
    'kontinjensi'          => ['label' => 'Kontinjensi',           'class' => 'bg-secondary'],
    'lainnya'              => ['label' => 'Lainnya',               'class' => 'bg-light text-dark'],
];
$labelStatus = [
    'planned'   => ['label' => 'Masih Rencana',      'class' => 'bg-secondary'],
    'active'    => ['label' => 'Sedang Berlangsung',  'class' => 'bg-warning text-dark'],
    'completed' => ['label' => 'Selesai',             'class' => 'bg-success'],
    'cancelled' => ['label' => 'Dibatalkan',          'class' => 'bg-danger'],
];

function fmtDate($d) {
    if (!$d) return '-';
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d/m/Y') : $d;
}
function fmtMonth($m) {
    if (!$m) return '-';
    $dt = DateTime::createFromFormat('Y-m', $m);
    if (!$dt) return $m;
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    return $bulan[(int)$dt->format('m')] . ' ' . $dt->format('Y');
}
function fmtRupiah($n) {
    if (!$n && $n !== 0) return '-';
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Daftar Operasi Kepolisian</h4>
            <small class="text-muted">Berdasarkan Perkap No. 9 Tahun 2011 tentang Manajemen Operasi Kepolisian</small>
        </div>
        <button class="btn btn-primary" onclick="openTambahModal()">
            <i class="fas fa-plus me-1"></i> Tambah Operasi
        </button>
    </div>

    <?php if (!empty($dbError)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($dbError); ?></div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" id="filterNama" class="form-control form-control-sm" placeholder="Cari nama operasi...">
                </div>
                <div class="col-md-2">
                    <select id="filterTingkat" class="form-select form-select-sm">
                        <option value="">Semua Tingkat</option>
                        <option value="terpusat">Terpusat</option>
                        <option value="kewilayahan_polda">Kewilayahan Polda</option>
                        <option value="kewilayahan_polres">Kewilayahan Polres</option>
                        <option value="imbangan">Imbangan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterJenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        <option value="intelijen">Intelijen</option>
                        <option value="pengamanan_kegiatan">Pengamanan Kegiatan</option>
                        <option value="pemeliharaan_keamanan">Pemeliharaan Keamanan</option>
                        <option value="penegakan_hukum">Penegakan Hukum</option>
                        <option value="pemulihan_keamanan">Pemulihan Keamanan</option>
                        <option value="kontinjensi">Kontinjensi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="planned">Masih Rencana</option>
                        <option value="active">Sedang Berlangsung</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" id="filterBulan" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilter()">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary" id="statTotal"><?php echo count($operations); ?></div>
                <div class="text-muted small">Total Operasi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning" id="statActive"><?php echo count(array_filter($operations, fn($o) => $o['status'] === 'active')); ?></div>
                <div class="text-muted small">Sedang Berlangsung</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-secondary" id="statPlanned"><?php echo count(array_filter($operations, fn($o) => $o['status'] === 'planned')); ?></div>
                <div class="text-muted small">Masih Rencana</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success" id="statDone"><?php echo count(array_filter($operations, fn($o) => $o['status'] === 'completed')); ?></div>
                <div class="text-muted small">Selesai</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="operasiTable" style="table-layout:auto">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">No</th>
                            <th style="width:200px">No. Sprint</th>
                            <th>Nama Operasi</th>
                            <th style="width:160px">Tingkat</th>
                            <th style="width:180px">Jenis</th>
                            <th style="width:120px">Bulan</th>
                            <th style="width:160px">Tgl Awal – Akhir</th>
                            <th style="width:80px" class="text-center">Personil</th>
                            <th style="width:150px" class="text-end">Dukgra</th>
                            <th style="width:140px" class="text-center">Status</th>
                            <th style="width:120px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="operasiBody">
                    <?php if (empty($operations)): ?>
                        <tr><td colspan="11" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data operasi.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($operations as $i => $op): ?>
                        <?php
                            $tk = $labelTingkat[$op['tingkat_operasi'] ?? ''] ?? ['label' => $op['tingkat_operasi'] ?? '-', 'class' => 'bg-light text-dark'];
                            $jn = $labelJenis[$op['jenis_operasi'] ?? ''] ?? ['label' => $op['jenis_operasi'] ?? '-', 'class' => 'bg-light text-dark'];
                            $st = $labelStatus[$op['status'] ?? ''] ?? ['label' => $op['status'] ?? '-', 'class' => 'bg-light text-dark'];
                            $tglRange = fmtDate($op['operation_date']);
                            if (!empty($op['operation_date_end'])) {
                                $tglRange .= ' – ' . fmtDate($op['operation_date_end']);
                            }
                        ?>
                        <tr data-tingkat="<?php echo htmlspecialchars($op['tingkat_operasi'] ?? ''); ?>"
                            data-jenis="<?php echo htmlspecialchars($op['jenis_operasi'] ?? ''); ?>"
                            data-status="<?php echo htmlspecialchars($op['status'] ?? ''); ?>"
                            data-bulan="<?php echo htmlspecialchars($op['operation_month'] ?? ''); ?>"
                            data-nama="<?php echo strtolower(htmlspecialchars($op['operation_name'] ?? '')); ?>">
                            <td class="text-muted"><?php echo $i + 1; ?></td>
                            <td class="small text-nowrap"><code><?php echo htmlspecialchars($op['nomor_sprint'] ?? '-'); ?></code></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($op['operation_name']); ?></div>
                                <?php if (!empty($op['description'])): ?>
                                <div class="text-muted small text-truncate" style="max-width:260px" title="<?php echo htmlspecialchars($op['description']); ?>">
                                    <?php echo htmlspecialchars($op['description']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($op['location'])): ?>
                                <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($op['location']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?php echo $tk['class']; ?>"><?php echo $tk['label']; ?></span></td>
                            <td><span class="badge <?php echo $jn['class']; ?>"><?php echo $jn['label']; ?></span></td>
                            <td class="text-nowrap"><?php echo fmtMonth($op['operation_month']); ?></td>
                            <td class="text-nowrap small"><?php echo $tglRange; ?></td>
                            <td class="text-center"><?php echo (int)($op['kuat_personil'] ?? 0); ?> <small class="text-muted">org</small></td>
                            <td class="text-end text-nowrap"><?php echo fmtRupiah($op['dukgra']); ?></td>
                            <td class="text-center"><span class="badge <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" title="Lihat Detail"
                                            onclick="viewOperasi(<?php echo htmlspecialchars(json_encode($op), ENT_QUOTES); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" title="Edit"
                                            onclick="editOperasi(<?php echo htmlspecialchars(json_encode($op), ENT_QUOTES); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Hapus"
                                            onclick="hapusOperasi(<?php echo (int)$op['id']; ?>, '<?php echo htmlspecialchars(addslashes($op['operation_name'])); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small d-flex justify-content-between">
            <span id="rowCount">Menampilkan <?php echo count($operations); ?> operasi</span>
            <span>Total Dukgra: <strong id="totalDukgra"><?php echo fmtRupiah(array_sum(array_column($operations, 'dukgra'))); ?></strong></span>
        </div>
    </div>
</div>

<!-- ══════════════ MODAL TAMBAH ══════════════ -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Operasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tambahForm">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tingkat Operasi <small class="text-muted">(Perkap No. 9/2011)</small></label>
                            <select class="form-select" id="t_tingkat" name="tingkat_operasi" onchange="updateTingkatHelp(this.value)">
                                <option value="kewilayahan_polres" selected>Kewilayahan Tingkat Polres</option>
                                <option value="kewilayahan_polda">Kewilayahan Tingkat Polda</option>
                                <option value="terpusat">Terpusat (Mabes Polri)</option>
                                <option value="imbangan">Imbangan</option>
                            </select>
                            <div class="form-text text-info" id="t_tingkatHelp">Diselenggarakan oleh Polres secara mandiri atau bekerja sama dengan Polsek dan Polda.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis / Bentuk Operasi <small class="text-muted">(Perkap No. 9/2011)</small></label>
                            <select class="form-select" id="t_jenis" name="jenis_operasi" onchange="updateJenisHelp(this.value)">
                                <option value="pemeliharaan_keamanan" selected>Operasi Pemeliharaan Keamanan</option>
                                <option value="pengamanan_kegiatan">Operasi Pengamanan Kegiatan</option>
                                <option value="penegakan_hukum">Operasi Penegakan Hukum</option>
                                <option value="intelijen">Operasi Intelijen</option>
                                <option value="pemulihan_keamanan">Operasi Pemulihan Keamanan</option>
                                <option value="kontinjensi">Operasi Kontinjensi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            <div class="form-text text-info" id="t_jenisHelp">Menjaga &amp; memelihara Kamtibmas, contoh: Operasi Zebra, Operasi Lilin.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Operasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="t_nama" name="operation_name"
                               placeholder="Contoh: Operasi Zebra 2026, Operasi Lilin Toba 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan Operasi</label>
                        <textarea class="form-control" id="t_keterangan" name="description" rows="3"
                                  placeholder="Uraian singkat tujuan dan sasaran operasi..."></textarea>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Bulan / Tahun <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="t_bulan" name="operation_month" required>
                            <div class="form-text">Pilih bulan sebelum tanggal pasti ditentukan.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Awal <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="date" class="form-control" id="t_tgl_awal" name="operation_date"
                                   onchange="validateTambahDates()">
                            <div class="form-text">Tanggal mulai pelaksanaan.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Akhir <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="date" class="form-control" id="t_tgl_akhir" name="operation_date_end"
                                   onchange="validateTambahDates()">
                            <div class="form-text" id="t_dateInfo"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" class="form-control" id="t_lokasi" name="location"
                                   placeholder="Lokasi pelaksanaan operasi">
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="fas fa-users me-1 text-primary"></i>Kuat / Jml Personil</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="t_personil" name="kuat_personil" min="0" value="0">
                                <span class="input-group-text">orang</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold"><i class="fas fa-money-bill-wave me-1 text-success"></i>Dukgra (Dukungan Anggaran)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="t_dukgra" name="dukgra"
                                       inputmode="numeric" oninput="formatRupiahTambah(this)">
                            </div>
                            <div class="form-text" id="t_dukgraPreview"></div>
                        </div>
                    </div>

                    <hr>
                    <div>
                        <label class="form-label fw-semibold">Status Operasi</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="t_planned" value="planned" checked>
                                <label class="form-check-label" for="t_planned"><span class="badge bg-secondary me-1">●</span>Masih Rencana</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="t_active" value="active">
                                <label class="form-check-label" for="t_active"><span class="badge bg-warning text-dark me-1">●</span>Sedang Berlangsung</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="t_completed" value="completed">
                                <label class="form-check-label" for="t_completed"><span class="badge bg-success me-1">●</span>Selesai</label>
                            </div>
                        </div>
                        <div id="t_statusAutoInfo" class="form-text mt-1"></div>
                        <div class="form-text text-muted">Status otomatis terdeteksi dari tanggal awal &amp; akhir. Anda tetap bisa ubah manual.</div>
                    </div>

                    <hr>
                    <!-- Pengulangan Operasi -->
                    <div class="border rounded p-3" style="background:#f8f9ff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small"><i class="fa-solid fa-repeat me-1 text-primary"></i>Pengulangan Operasi</span>
                            <span class="badge" id="t_recPreview" style="background:#6c757d;">Tidak Berulang</span>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="t_recType" name="recurrence_type" onchange="updateOpRecUI('t')">
                                    <option value="none">Tidak Berulang</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="t_colInterval" style="display:none;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Setiap</span>
                                    <input type="number" class="form-control" id="t_recInterval" name="recurrence_interval" value="1" min="1" max="30">
                                </div>
                            </div>
                            <div class="col-md-5" id="t_colEnd" style="display:none;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">s/d</span>
                                    <input type="date" class="form-control" id="t_recEnd" name="recurrence_end">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanTambah()">
                    <i class="fas fa-save me-1"></i> Simpan Operasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════ MODAL VIEW ══════════════ -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Operasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <h5 id="vm_nama" class="fw-bold mb-1"></h5>
                        <div id="vm_badges" class="mb-3"></div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:130px"><i class="fas fa-hashtag me-1"></i>No. Sprint</td><td id="vm_sprint" class="fw-semibold"></td></tr>
                            <tr><td class="text-muted" style="width:130px"><i class="fas fa-layer-group me-1"></i>Tingkat</td><td id="vm_tingkat"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-tag me-1"></i>Jenis</td><td id="vm_jenis"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-calendar me-1"></i>Bulan</td><td id="vm_bulan"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-calendar-day me-1"></i>Tgl Awal</td><td id="vm_tgl_awal"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-calendar-check me-1"></i>Tgl Akhir</td><td id="vm_tgl_akhir"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:130px"><i class="fas fa-map-marker-alt me-1"></i>Lokasi</td><td id="vm_lokasi"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-users me-1"></i>Kuat Personil</td><td id="vm_personil"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-money-bill me-1"></i>Dukgra</td><td id="vm_dukgra" class="fw-semibold text-success"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-flag me-1"></i>Status</td><td id="vm_status"></td></tr>
                            <tr><td class="text-muted"><i class="fas fa-clock me-1"></i>Dibuat</td><td id="vm_created"></td></tr>
                        </table>
                    </div>
                    <div class="col-12" id="vm_desc_wrap">
                        <label class="text-muted small"><i class="fas fa-align-left me-1"></i>Keterangan</label>
                        <div id="vm_desc" class="border rounded p-3 bg-light small"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" id="vm_editBtn"><i class="fas fa-edit me-1"></i>Edit</button>
                <button type="button" class="btn btn-outline-dark" id="vm_cetakBtn" onclick="openCetakST()"><i class="fa-solid fa-file-lines me-1"></i>Cetak ST</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════ MODAL CETAK ST ══════════════ -->
<div class="modal fade" id="stModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-file-lines me-2"></i>Surat Perintah Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="stBody">
                <!-- Generated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Cetak</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════ MODAL EDIT ══════════════ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Operasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tingkat Operasi</label>
                            <select class="form-select" id="edit_tingkat" name="tingkat_operasi">
                                <option value="kewilayahan_polres">Kewilayahan Tingkat Polres</option>
                                <option value="kewilayahan_polda">Kewilayahan Tingkat Polda</option>
                                <option value="terpusat">Terpusat (Mabes Polri)</option>
                                <option value="imbangan">Imbangan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis / Bentuk Operasi</label>
                            <select class="form-select" id="edit_jenis" name="jenis_operasi">
                                <option value="pemeliharaan_keamanan">Operasi Pemeliharaan Keamanan</option>
                                <option value="pengamanan_kegiatan">Operasi Pengamanan Kegiatan</option>
                                <option value="penegakan_hukum">Operasi Penegakan Hukum</option>
                                <option value="intelijen">Operasi Intelijen</option>
                                <option value="pemulihan_keamanan">Operasi Pemulihan Keamanan</option>
                                <option value="kontinjensi">Operasi Kontinjensi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Operasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="operation_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan Operasi</label>
                        <textarea class="form-control" id="edit_keterangan" name="description" rows="3"></textarea>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Bulan / Tahun <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="edit_bulan" name="operation_month" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Awal</label>
                            <input type="date" class="form-control" id="edit_tgl_awal" name="operation_date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="edit_tgl_akhir" name="operation_date_end">
                            <div class="form-text" id="edit_dateInfo"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" class="form-control" id="edit_lokasi" name="location">
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="fas fa-users me-1 text-primary"></i>Kuat / Jml Personil</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_personil" name="kuat_personil" min="0">
                                <span class="input-group-text">orang</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold"><i class="fas fa-money-bill-wave me-1 text-success"></i>Dukgra (Dukungan Anggaran)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="edit_dukgra" name="dukgra"
                                       inputmode="numeric" oninput="formatRupiahEdit(this)">
                            </div>
                            <div class="form-text" id="edit_dukgraPreview"></div>
                        </div>
                    </div>

                    <hr>
                    <div>
                        <label class="form-label fw-semibold">Status Operasi</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="edit_planned" value="planned">
                                <label class="form-check-label" for="edit_planned"><span class="badge bg-secondary me-1">●</span>Masih Rencana</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="edit_active" value="active">
                                <label class="form-check-label" for="edit_active"><span class="badge bg-warning text-dark me-1">●</span>Sedang Berlangsung</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="edit_completed" value="completed">
                                <label class="form-check-label" for="edit_completed"><span class="badge bg-success me-1">●</span>Selesai</label>
                            </div>
                        </div>
                        <div id="edit_statusAutoInfo" class="form-text mt-1"></div>
                        <div class="form-text text-muted">Status otomatis terdeteksi dari tanggal awal &amp; akhir. Anda tetap bisa ubah manual.</div>
                    </div>

                    <hr>
                    <!-- Pengulangan Operasi Edit -->
                    <div class="border rounded p-3" style="background:#f8f9ff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small"><i class="fa-solid fa-repeat me-1 text-primary"></i>Pengulangan Operasi</span>
                            <span class="badge" id="edit_recPreview" style="background:#6c757d;">Tidak Berulang</span>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="edit_recType" name="recurrence_type" onchange="updateOpRecUI('edit')">
                                    <option value="none">Tidak Berulang</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="edit_colInterval" style="display:none;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Setiap</span>
                                    <input type="number" class="form-control" id="edit_recInterval" name="recurrence_interval" value="1" min="1" max="30">
                                </div>
                            </div>
                            <div class="col-md-5" id="edit_colEnd" style="display:none;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">s/d</span>
                                    <input type="date" class="form-control" id="edit_recEnd" name="recurrence_end">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="simpanEdit()">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function resetFilter() {
        document.getElementById('filterNama').value    = '';
        document.getElementById('filterTingkat').value = '';
        document.getElementById('filterJenis').value   = '';
        document.getElementById('filterStatus').value  = '';
        document.getElementById('filterBulan').value   = '';
        applyFilter();
    }

    function applyFilter() {
        const nama    = document.getElementById('filterNama').value.toLowerCase();
        const tingkat = document.getElementById('filterTingkat').value;
        const jenis   = document.getElementById('filterJenis').value;
        const status  = document.getElementById('filterStatus').value;
        const bulan   = document.getElementById('filterBulan').value;

        const rows = document.querySelectorAll('#operasiBody tr[data-nama]');
        let visible = 0;
        rows.forEach(row => {
            const match =
                (!nama    || row.dataset.nama.includes(nama)) &&
                (!tingkat || row.dataset.tingkat === tingkat) &&
                (!jenis   || row.dataset.jenis   === jenis)   &&
                (!status  || row.dataset.status  === status)  &&
                (!bulan   || row.dataset.bulan   === bulan);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('rowCount').textContent = 'Menampilkan ' + visible + ' operasi';
    }

    ['filterNama','filterTingkat','filterJenis','filterStatus','filterBulan'].forEach(id => {
        document.getElementById(id).addEventListener('input', applyFilter);
        document.getElementById(id).addEventListener('change', applyFilter);
    });

    // ── Auto-buka modal tambah jika ?tambah=1 ──
    <?php if (!empty($_GET['tambah'])): ?>
    document.addEventListener('DOMContentLoaded', () => openTambahModal());
    <?php endif; ?>

    function openTambahModal() {
        document.getElementById('tambahForm').reset();
        const now = new Date();
        document.getElementById('t_bulan').value =
            now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
        document.getElementById('t_dukgraPreview').textContent = '';
        document.getElementById('t_dateInfo').textContent = '';
        document.getElementById('t_statusAutoInfo').textContent = '';
        document.getElementById('t_planned').checked = true;
        new bootstrap.Modal(document.getElementById('tambahModal')).show();
    }

    function simpanTambah() {
        const form = document.getElementById('tambahForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        if (!validateTambahDates()) return;

        const fd = new FormData(form);
        fd.append('action', 'create_operation');
        const raw = document.getElementById('t_dukgra').value.replace(/\./g,'').replace(',','.');
        fd.set('dukgra', raw || '0');

        fetch('../api/calendar_api_public.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('tambahModal')).hide();
                    location.reload();
                } else {
                    alert('Gagal: ' + (data.error || data.message));
                }
            })
            .catch(err => alert('Error: ' + err));
    }

    function validateTambahDates() {
        const s = document.getElementById('t_tgl_awal').value;
        const e = document.getElementById('t_tgl_akhir').value;
        const info = document.getElementById('t_dateInfo');
        if (s && e && e < s) {
            info.className = 'form-text text-danger';
            info.textContent = 'Tanggal akhir tidak boleh sebelum tanggal awal.';
            return false;
        }
        if (s && e) {
            const days = Math.round((new Date(e)-new Date(s))/86400000)+1;
            info.className = 'form-text text-success';
            info.textContent = 'Durasi: ' + days + ' hari';
        } else { info.textContent = ''; }
        autoDetectTambahStatus(s, e);
        return true;
    }

    function autoDetectTambahStatus(start, end) {
        const infoEl = document.getElementById('t_statusAutoInfo');
        if (!start || !end) { infoEl.textContent = ''; return; }
        const today = new Date(); today.setHours(0,0,0,0);
        const s = new Date(start); s.setHours(0,0,0,0);
        const e = new Date(end);   e.setHours(0,0,0,0);
        let id, msg, cls;
        if (e < today)              { id='t_completed'; cls='form-text text-success'; msg='✔ Otomatis: Selesai'; }
        else if (s<=today&&today<=e){ id='t_active';    cls='form-text text-warning'; msg='▶ Otomatis: Sedang Berlangsung'; }
        else                        { id='t_active';    cls='form-text text-primary'; msg='📅 Otomatis: Agenda (belum dimulai)'; }
        document.getElementById(id).checked = true;
        infoEl.textContent = msg; infoEl.className = cls;
    }

    function formatRupiahTambah(input) {
        let val = input.value.replace(/[^0-9]/g,'');
        if (!val) { input.value=''; document.getElementById('t_dukgraPreview').textContent=''; return; }
        input.value = parseInt(val,10).toLocaleString('id-ID');
        document.getElementById('t_dukgraPreview').textContent =
            'Terbilang: ' + terbilang(parseInt(val,10)) + ' rupiah';
    }

    function updateTingkatHelp(val) {
        const desc = {
            'kewilayahan_polres':'Diselenggarakan oleh Polres secara mandiri atau bekerja sama dengan Polsek dan Polda.',
            'kewilayahan_polda' :'Diselenggarakan oleh Polda secara mandiri atau mengikutsertakan personel Mabes Polri dan Polres.',
            'terpusat'          :'Diselenggarakan oleh Mabes Polri secara mandiri atau dengan melibatkan personel kewilayahan.',
            'imbangan'          :'Operasi dukungan kewilayahan sebagai pendamping/pengimbang operasi terpusat dari Mabes Polri.'
        };
        const el = document.getElementById('t_tingkatHelp') || document.getElementById('edit_tingkatHelp');
        if (el) el.textContent = desc[val] || '';
    }

    function updateJenisHelp(val) {
        const desc = {
            'pemeliharaan_keamanan':'Menjaga & memelihara Kamtibmas. Contoh: Operasi Zebra, Operasi Lilin, Operasi Ketupat.',
            'pengamanan_kegiatan'  :'Pengamanan kegiatan masyarakat, pejabat, atau event tertentu (pilkada, hari besar, dll).',
            'penegakan_hukum'      :'Penindakan pelanggaran & kejahatan. Contoh: Operasi Nila (narkoba), Operasi Bersih.',
            'intelijen'            :'Pengumpulan informasi & deteksi dini ancaman keamanan di wilayah.',
            'pemulihan_keamanan'   :'Pemulihan situasi pasca konflik, kerusuhan, atau bencana sosial.',
            'kontinjensi'          :'Operasi darurat menghadapi ancaman/situasi luar biasa yang tidak terduga.',
            'lainnya'              :'Jenis operasi lain di luar klasifikasi di atas.'
        };
        const el = document.getElementById('t_jenisHelp') || document.getElementById('edit_jenisHelp');
        if (el) el.textContent = desc[val] || '';
    }

    function terbilang(n) {
        const sat = ['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan',
                     'sepuluh','sebelas','dua belas','tiga belas','empat belas','lima belas',
                     'enam belas','tujuh belas','delapan belas','sembilan belas'];
        if (n===0) return 'nol';
        if (n<0)   return 'minus '+terbilang(-n);
        if (n<20)  return sat[n];
        const rb = (x) => {
            if (x<20) return sat[x];
            if (x<100) return ['','','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan'][Math.floor(x/10)]+' puluh'+(x%10?' '+sat[x%10]:'');
            const h=Math.floor(x/100),r=x%100;
            return (h===1?'seratus':sat[h]+' ratus')+(r?' '+rb(r):'');
        };
        if (n<1000) return rb(n);
        if (n<1e6)  { const k=Math.floor(n/1000),r=n%1000; return (k===1?'seribu':rb(k)+' ribu')+(r?' '+rb(r):''); }
        if (n<1e9)  { const k=Math.floor(n/1e6),r=n%1e6;   return rb(k)+' juta'+(r?' '+terbilang(r):''); }
        if (n<1e12) { const k=Math.floor(n/1e9),r=n%1e9;   return rb(k)+' miliar'+(r?' '+terbilang(r):''); }
        const k=Math.floor(n/1e12),r=n%1e12; return rb(k)+' triliun'+(r?' '+terbilang(r):'');
    }

    const labelTingkat = {
        'terpusat'           : 'Terpusat (Mabes Polri)',
        'kewilayahan_polda'  : 'Kewilayahan Polda',
        'kewilayahan_polres' : 'Kewilayahan Polres',
        'imbangan'           : 'Imbangan'
    };
    const labelJenis = {
        'intelijen'            : 'Operasi Intelijen',
        'pengamanan_kegiatan'  : 'Operasi Pengamanan Kegiatan',
        'pemeliharaan_keamanan': 'Operasi Pemeliharaan Keamanan',
        'penegakan_hukum'      : 'Operasi Penegakan Hukum',
        'pemulihan_keamanan'   : 'Operasi Pemulihan Keamanan',
        'kontinjensi'          : 'Operasi Kontinjensi',
        'lainnya'              : 'Lainnya'
    };
    const badgeTingkat = {
        'terpusat':'bg-danger','kewilayahan_polda':'bg-warning text-dark',
        'kewilayahan_polres':'bg-primary','imbangan':'bg-secondary'
    };
    const badgeJenis = {
        'intelijen':'bg-dark','pengamanan_kegiatan':'bg-info text-dark',
        'pemeliharaan_keamanan':'bg-primary','penegakan_hukum':'bg-danger',
        'pemulihan_keamanan':'bg-warning text-dark','kontinjensi':'bg-secondary','lainnya':'bg-light text-dark'
    };
    const badgeStatus = {
        'planned':'bg-secondary','active':'bg-warning text-dark','completed':'bg-success','cancelled':'bg-danger'
    };
    const labelStatus2 = {
        'planned':'Masih Rencana','active':'Sedang Berlangsung','completed':'Selesai','cancelled':'Dibatalkan'
    };

    function fmtDateJS(d) {
        if (!d) return '-';
        const p = d.split('-');
        return p[2]+'/'+p[1]+'/'+p[0];
    }
    function fmtBulanJS(m) {
        if (!m) return '-';
        const bln = ['','Januari','Februari','Maret','April','Mei','Juni',
                     'Juli','Agustus','September','Oktober','November','Desember'];
        const p = m.split('-');
        return bln[parseInt(p[1])] + ' ' + p[0];
    }
    function fmtRupiahJS(n) {
        if (!n) return '-';
        return 'Rp ' + parseInt(n).toLocaleString('id-ID');
    }

    const labelJenis2   = {
        'pemeliharaan_keamanan':'Operasi Pemeliharaan Keamanan','pengamanan_kegiatan':'Operasi Pengamanan Kegiatan',
        'penegakan_hukum':'Operasi Penegakan Hukum','intelijen':'Operasi Intelijen',
        'pemulihan_keamanan':'Operasi Pemulihan Keamanan','kontinjensi':'Operasi Kontinjensi','lainnya':'Lainnya'
    };
    const labelTingkat2 = {
        'kewilayahan_polres':'Kewilayahan Tingkat Polres','kewilayahan_polda':'Kewilayahan Tingkat Polda',
        'terpusat':'Terpusat (Mabes Polri)','imbangan':'Imbangan'
    };

    function viewOperasi(op) {
        _currentOp = op;
        document.getElementById('vm_nama').textContent    = op.operation_name;
        document.getElementById('vm_sprint').textContent  = op.nomor_sprint || '-';
        document.getElementById('vm_tingkat').textContent = labelTingkat[op.tingkat_operasi] || op.tingkat_operasi || '-';
        document.getElementById('vm_jenis').textContent   = labelJenis[op.jenis_operasi]     || op.jenis_operasi   || '-';
        document.getElementById('vm_bulan').textContent   = fmtBulanJS(op.operation_month);
        document.getElementById('vm_tgl_awal').textContent  = fmtDateJS(op.operation_date);
        document.getElementById('vm_tgl_akhir').textContent = fmtDateJS(op.operation_date_end);
        document.getElementById('vm_lokasi').textContent  = op.location    || '-';
        document.getElementById('vm_personil').textContent = (op.kuat_personil || 0) + ' orang';
        document.getElementById('vm_dukgra').textContent  = fmtRupiahJS(op.dukgra);
        document.getElementById('vm_created').textContent = op.created_at  || '-';

        const stBadge = badgeStatus[op.status] || 'bg-secondary';
        document.getElementById('vm_status').innerHTML = `<span class="badge ${stBadge}">${labelStatus2[op.status]||op.status}</span>`;

        const tkBadge = badgeTingkat[op.tingkat_operasi] || 'bg-secondary';
        const jnBadge = badgeJenis[op.jenis_operasi]     || 'bg-secondary';
        document.getElementById('vm_badges').innerHTML =
            `<span class="badge ${tkBadge} me-1">${labelTingkat[op.tingkat_operasi]||op.tingkat_operasi}</span>` +
            `<span class="badge ${jnBadge}">${labelJenis[op.jenis_operasi]||op.jenis_operasi}</span>`;

        const descWrap = document.getElementById('vm_desc_wrap');
        if (op.description) {
            document.getElementById('vm_desc').textContent = op.description;
            descWrap.style.display = '';
        } else {
            descWrap.style.display = 'none';
        }

        document.getElementById('vm_editBtn').onclick = function() {
            bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide();
            editOperasi(op);
        };
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }

    function editOperasi(op) {
        document.getElementById('edit_id').value         = op.id;
        document.getElementById('edit_tingkat').value    = op.tingkat_operasi    || 'kewilayahan_polres';
        document.getElementById('edit_jenis').value      = op.jenis_operasi      || 'pemeliharaan_keamanan';
        document.getElementById('edit_nama').value       = op.operation_name     || '';
        document.getElementById('edit_keterangan').value = op.description        || '';
        document.getElementById('edit_bulan').value      = op.operation_month    || '';
        document.getElementById('edit_tgl_awal').value   = op.operation_date     || '';
        document.getElementById('edit_tgl_akhir').value  = op.operation_date_end || '';
        document.getElementById('edit_lokasi').value     = op.location           || '';
        document.getElementById('edit_personil').value   = op.kuat_personil      || 0;

        // Format dukgra
        const dukgra = parseInt(op.dukgra || 0);
        document.getElementById('edit_dukgra').value = dukgra ? dukgra.toLocaleString('id-ID') : '';
        document.getElementById('edit_dukgraPreview').textContent = dukgra ? 'Terbilang: Rp ' + dukgra.toLocaleString('id-ID') : '';

        // Set radio status
        const radios = document.querySelectorAll('input[name="status"]');
        radios.forEach(r => r.checked = (r.value === (op.status || 'planned')));

        // Reset date info then auto-detect
        document.getElementById('edit_dateInfo').textContent = '';
        autoDetectStatusEdit(op.operation_date || '', op.operation_date_end || '');

        // Fill recurrence fields
        document.getElementById('edit_recType').value     = op.recurrence_type     || 'none';
        document.getElementById('edit_recInterval').value = op.recurrence_interval || 1;
        document.getElementById('edit_recEnd').value      = op.recurrence_end       || '';
        updateOpRecUI('edit');

        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function formatRupiahEdit(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (!val) { input.value = ''; document.getElementById('edit_dukgraPreview').textContent = ''; return; }
        input.value = parseInt(val, 10).toLocaleString('id-ID');
        document.getElementById('edit_dukgraPreview').textContent = 'Rp ' + parseInt(val, 10).toLocaleString('id-ID');
    }

    document.getElementById('edit_tgl_awal').addEventListener('change', checkEditDates);
    document.getElementById('edit_tgl_akhir').addEventListener('change', checkEditDates);
    function checkEditDates() {
        const s = document.getElementById('edit_tgl_awal').value;
        const e = document.getElementById('edit_tgl_akhir').value;
        const info = document.getElementById('edit_dateInfo');
        if (s && e && e < s) {
            info.className = 'form-text text-danger';
            info.textContent = 'Tanggal akhir tidak boleh sebelum tanggal awal.';
        } else if (s && e) {
            const days = Math.round((new Date(e)-new Date(s))/86400000)+1;
            info.className = 'form-text text-success';
            info.textContent = 'Durasi: ' + days + ' hari';
        } else {
            info.textContent = '';
        }
        autoDetectStatusEdit(s, e);
    }

    function autoDetectStatusEdit(start, end) {
        const infoEl = document.getElementById('edit_statusAutoInfo');
        if (!start || !end) {
            infoEl.textContent = ''; infoEl.className = 'form-text'; return;
        }
        const today = new Date(); today.setHours(0,0,0,0);
        const s = new Date(start); s.setHours(0,0,0,0);
        const e = new Date(end);   e.setHours(0,0,0,0);

        let statusId, msg, cls;
        if (e < today) {
            statusId = 'edit_completed'; cls = 'form-text text-success';
            msg = '✔ Otomatis: Selesai (tanggal akhir sudah lewat)';
        } else if (s <= today && today <= e) {
            statusId = 'edit_active'; cls = 'form-text text-warning';
            msg = '▶ Otomatis: Sedang Berlangsung (dalam rentang tanggal)';
        } else {
            statusId = 'edit_active'; cls = 'form-text text-primary';
            msg = '📅 Otomatis: Agenda (tanggal sudah ditetapkan, belum dimulai)';
        }
        document.getElementById(statusId).checked = true;
        infoEl.textContent = msg;
        infoEl.className = cls;
    }

    function simpanEdit() {
        const form = document.getElementById('editForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const s = document.getElementById('edit_tgl_awal').value;
        const e = document.getElementById('edit_tgl_akhir').value;
        if (s && e && e < s) { alert('Tanggal akhir tidak boleh sebelum tanggal awal.'); return; }

        const fd = new FormData(form);
        fd.append('action', 'update_operation');
        // strip format rupiah
        const raw = document.getElementById('edit_dukgra').value.replace(/\./g,'').replace(',','.');
        fd.set('dukgra', raw || '0');

        fetch('../api/calendar_api_public.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    location.reload();
                } else {
                    alert('Gagal simpan: ' + (data.error || data.message));
                }
            })
            .catch(err => alert('Error: ' + err));
    }

    function updateOpRecUI(prefix) {
        const type     = document.getElementById(prefix + '_recType').value;
        const interval = document.getElementById(prefix + '_recInterval').value || 1;
        const endDate  = document.getElementById(prefix + '_recEnd').value;
        document.getElementById(prefix + '_colInterval').style.display = type !== 'none' ? '' : 'none';
        document.getElementById(prefix + '_colEnd').style.display      = type !== 'none' ? '' : 'none';
        const badge    = document.getElementById(prefix + '_recPreview');
        if (type === 'none') { badge.textContent = 'Tidak Berulang'; badge.style.background = '#6c757d'; return; }
        const labels   = {daily:'Hari',weekly:'Minggu',monthly:'Bulan',yearly:'Tahun'};
        const until    = endDate ? ' s/d ' + new Date(endDate).toLocaleDateString('id-ID',{day:'numeric',month:'short'}) : '';
        badge.textContent  = 'Setiap ' + interval + ' ' + labels[type] + until;
        badge.style.background = '#1a237e';
    }

    let _currentOp = null;

    function openCetakST() {
        if (!_currentOp) return;
        const op   = _currentOp;
        const tgl  = new Date().toLocaleDateString('id-ID',{year:'numeric',month:'long',day:'numeric'});
        const tglAwal  = op.operation_date  ? fmtDateJS(op.operation_date)  : '—';
        const tglAkhir = op.operation_date_end ? fmtDateJS(op.operation_date_end) : tglAwal;
        const dukgra   = fmtRupiahJS(op.dukgra);
        const personil = op.kuat_personil || '—';
        document.getElementById('stBody').innerHTML = `
        <div style="font-family:Times New Roman,serif;font-size:12pt;padding:20px;" id="stPrint">
          <div style="text-align:center;border-bottom:3px double #000;padding-bottom:10px;margin-bottom:15px;">
            <div style="font-size:10pt;font-weight:bold;">KEPOLISIAN NEGARA REPUBLIK INDONESIA</div>
            <div style="font-size:10pt;">DAERAH SUMATERA UTARA / RESOR SAMOSIR</div>
            <div style="font-size:9pt;">Jl. ................................. No. .......</div>
          </div>
          <h4 style="text-align:center;text-decoration:underline;font-size:13pt;">SURAT PERINTAH TUGAS</h4>
          <p style="text-align:center;">Nomor: <strong>${op.nomor_sprint || '...................'}</strong></p>
          <p><strong>Dasar:</strong> Surat Perintah / Laporan Rencana Operasi Kepolisian</p>
          <table style="width:100%;font-size:11pt;">
            <tr><td style="width:30%">Nama Operasi</td><td>: <strong>${op.operation_name}</strong></td></tr>
            <tr><td>Jenis / Tingkat</td><td>: ${(labelJenis2[op.jenis_operasi]||op.jenis_operasi||'—')} / ${(labelTingkat2[op.tingkat_operasi]||op.tingkat_operasi||'—')}</td></tr>
            <tr><td>Tanggal Pelaksanaan</td><td>: ${tglAwal} s/d ${tglAkhir}</td></tr>
            <tr><td>Lokasi</td><td>: ${op.location || 'Wilayah Hukum Polres Samosir'}</td></tr>
            <tr><td>Kuat Personil</td><td>: ${personil} orang</td></tr>
            <tr><td>Dukgra</td><td>: ${dukgra}</td></tr>
          </table>
          <br>
          <p>${op.description ? op.description : 'Melaksanakan tugas operasi kepolisian sesuai rencana yang telah ditetapkan, dengan memperhatikan norma hukum, norma sosial, dan HAM.'}</p>
          <br><br>
          <div style="display:flex;justify-content:space-between;">
            <div style="text-align:center;">
              <div>Mengetahui,</div>
              <div style="margin-top:70px;"><strong>KAPOLRES SAMOSIR</strong></div>
            </div>
            <div style="text-align:center;">
              <div>Dikeluarkan di Samosir</div>
              <div>Pada tanggal ${tgl}</div>
              <div style="margin-top:45px;"><strong>KASAT OPS</strong></div>
            </div>
          </div>
        </div>`;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('stModal')).show();
    }

    function hapusOperasi(id, nama) {
        if (!confirm('Hapus operasi "' + nama + '"?\nTindakan ini tidak dapat dibatalkan.')) return;
        const fd = new FormData();
        fd.append('action', 'delete_operation');
        fd.append('id', id);
        fetch('../api/calendar_api_public.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal hapus: ' + (data.error || data.message));
                }
            })
            .catch(err => alert('Error: ' + err));
    }
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
