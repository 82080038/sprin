<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';
if (!AuthHelper::validateSession()) { header('Location: ' . url('login.php')); exit; }

$page_title = 'Manajemen Tim Piket - BAGOPS POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $bagian_list = $pdo->query("SELECT id, nama_bagian FROM bagian WHERE is_active=1 ORDER BY urutan,nama_bagian")->fetchAll(PDO::FETCH_ASSOC);
    $unsur_list  = $pdo->query("SELECT id, nama_unsur  FROM unsur  WHERE is_active=1 ORDER BY urutan,nama_unsur")->fetchAll(PDO::FETCH_ASSOC);

    // Tim beserta jumlah anggota
    $tim_list = $pdo->query("
        SELECT t.*, b.nama_bagian, u.nama_unsur,
               COUNT(a.id) AS jml_anggota
        FROM tim_piket t
        LEFT JOIN bagian b ON b.id = t.id_bagian
        LEFT JOIN unsur  u ON u.id = t.id_unsur
        LEFT JOIN tim_piket_anggota a ON a.tim_id = t.id
        GROUP BY t.id
        ORDER BY t.is_active DESC, b.nama_bagian, t.nama_tim
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $dbError = $e->getMessage();
    $bagian_list = $unsur_list = $tim_list = [];
}

$labelJenis = ['piket'=>'Piket Harian','satuan_tugas'=>'Satuan Tugas','kegiatan'=>'Rencana Kegiatan'];
$labelShift = ['PAGI'=>'Pagi','SIANG'=>'Siang','MALAM'=>'Malam','FULL_DAY'=>'Full Day','ROTASI'=>'Rotasi',''=>'-'];
$badgeJenis = ['piket'=>'bg-primary','satuan_tugas'=>'bg-warning text-dark','kegiatan'=>'bg-info text-dark'];
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-users-cog me-2 text-primary"></i>Manajemen Tim / Regu Piket</h4>
            <small class="text-muted">Kelola tim piket, satuan tugas, dan regu kegiatan per fungsi/bagian</small>
        </div>
        <button class="btn btn-primary" onclick="openTambahTim()">
            <i class="fas fa-plus me-1"></i> Tambah Tim
        </button>
    </div>

    <?php if (!empty($dbError)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($dbError); ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" id="filterNama" class="form-control form-control-sm" placeholder="Cari nama tim...">
                </div>
                <div class="col-md-3">
                    <select id="filterBagian" class="form-select form-select-sm">
                        <option value="">Semua Bagian</option>
                        <?php foreach ($bagian_list as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nama_bagian']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterJenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        <option value="piket">Piket Harian</option>
                        <option value="satuan_tugas">Satuan Tugas</option>
                        <option value="kegiatan">Rencana Kegiatan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterAktif" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilter()">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <?php
        $totalTim   = count($tim_list);
        $timAktif   = count(array_filter($tim_list, fn($t) => $t['is_active']));
        $totalAngg  = array_sum(array_column($tim_list, 'jml_anggota'));
        $timPiket   = count(array_filter($tim_list, fn($t) => $t['jenis']==='piket'));
        ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?php echo $totalTim; ?></div>
                <div class="text-muted small">Total Tim</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success"><?php echo $timAktif; ?></div>
                <div class="text-muted small">Tim Aktif</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-info"><?php echo $totalAngg; ?></div>
                <div class="text-muted small">Total Anggota</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning"><?php echo $timPiket; ?></div>
                <div class="text-muted small">Tim Piket Harian</div>
            </div>
        </div>
    </div>

    <!-- Tim Cards -->
    <div class="row g-3" id="timGrid">
    <?php if (empty($tim_list)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3 d-block"></i>
            Belum ada tim. Klik <strong>Tambah Tim</strong> untuk mulai.
        </div>
    <?php else: ?>
        <?php foreach ($tim_list as $tim): ?>
        <?php $jb = $badgeJenis[$tim['jenis']] ?? 'bg-secondary'; ?>
        <div class="col-md-4 col-lg-3 tim-card"
             data-nama="<?php echo strtolower(htmlspecialchars($tim['nama_tim'])); ?>"
             data-bagian="<?php echo $tim['id_bagian']; ?>"
             data-jenis="<?php echo $tim['jenis']; ?>"
             data-aktif="<?php echo $tim['is_active']; ?>">
            <div class="card shadow-sm h-100 <?php echo !$tim['is_active'] ? 'opacity-50' : ''; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge <?php echo $jb; ?>"><?php echo $labelJenis[$tim['jenis']] ?? $tim['jenis']; ?></span>
                        <?php if (!$tim['is_active']): ?>
                        <span class="badge bg-secondary">Non-Aktif</span>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($tim['nama_tim']); ?></h6>
                    <div class="text-muted small mb-2">
                        <?php if ($tim['nama_bagian']): ?>
                        <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($tim['nama_bagian']); ?><br>
                        <?php endif; ?>
                        <?php if ($tim['nama_unsur']): ?>
                        <i class="fas fa-layer-group me-1"></i><?php echo htmlspecialchars($tim['nama_unsur']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="text-center">
                            <div class="fw-bold text-primary"><?php echo (int)$tim['jml_anggota']; ?></div>
                            <div class="text-muted" style="font-size:0.7rem">Anggota</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-success"><?php echo $labelShift[$tim['shift_default'] ?? ''] ?? '-'; ?></div>
                            <div class="text-muted" style="font-size:0.7rem">Shift</div>
                        </div>
                        <?php if ($tim['shift_default'] === 'ROTASI' && $tim['pola_rotasi']): ?>
                        <div class="text-center">
                            <div class="fw-bold text-warning small"><?php echo htmlspecialchars($tim['pola_rotasi']); ?></div>
                            <div class="text-muted" style="font-size:0.7rem">Pola Rotasi</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($tim['keterangan']): ?>
                    <div class="text-muted small text-truncate mb-3" title="<?php echo htmlspecialchars($tim['keterangan']); ?>">
                        <?php echo htmlspecialchars($tim['keterangan']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <div class="d-grid gap-1">
                        <button class="btn btn-sm btn-outline-primary" onclick="kelolaAnggota(<?php echo $tim['id']; ?>, '<?php echo htmlspecialchars(addslashes($tim['nama_tim'])); ?>')">
                            <i class="fas fa-users me-1"></i> Kelola Anggota
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="buatJadwalDariTim(<?php echo htmlspecialchars(json_encode($tim), ENT_QUOTES); ?>)">
                            <i class="fas fa-calendar-plus me-1"></i> Buat Jadwal
                        </button>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-warning" onclick="editTim(<?php echo htmlspecialchars(json_encode($tim), ENT_QUOTES); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger" onclick="hapusTim(<?php echo $tim['id']; ?>, '<?php echo htmlspecialchars(addslashes($tim['nama_tim'])); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<!-- ══ MODAL TAMBAH/EDIT TIM ══ -->
<div class="modal fade" id="timModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="timModalTitle"><i class="fas fa-users-cog me-2"></i>Tambah Tim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="timForm">
                    <input type="hidden" id="tim_id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Tim / Regu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tim_nama" name="nama_tim"
                                   placeholder="Contoh: Regu A, Tim Piket Lantas 1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                            <select class="form-select" id="tim_jenis" name="jenis" required>
                                <option value="piket">Piket Harian</option>
                                <option value="satuan_tugas">Satuan Tugas</option>
                                <option value="kegiatan">Rencana Kegiatan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bagian / Fungsi</label>
                            <select class="form-select" id="tim_bagian" name="id_bagian">
                                <option value="">-- Pilih Bagian --</option>
                                <?php foreach ($bagian_list as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nama_bagian']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unsur</label>
                            <select class="form-select" id="tim_unsur" name="id_unsur">
                                <option value="">-- Pilih Unsur --</option>
                                <?php foreach ($unsur_list as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nama_unsur']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shift Default</label>
                            <select class="form-select" id="tim_shift" name="shift_default" onchange="toggleRotasi(this.value)">
                                <option value="">-- Pilih --</option>
                                <option value="PAGI">Pagi (06:00–14:00)</option>
                                <option value="SIANG">Siang (14:00–22:00)</option>
                                <option value="MALAM">Malam (22:00–06:00)</option>
                                <option value="FULL_DAY">Full Day (07:00–16:00)</option>
                                <option value="ROTASI">Rotasi (bergilir)</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="rotasiGroup" style="display:none">
                            <label class="form-label fw-semibold">Pola Rotasi</label>
                            <input type="text" class="form-control" id="tim_rotasi" name="pola_rotasi"
                                   placeholder="Contoh: PAGI,SIANG,MALAM">
                            <div class="form-text">Urutan shift yang bergilir, pisahkan dengan koma. Rotasi per minggu.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea class="form-control" id="tim_keterangan" name="keterangan" rows="2"
                                  placeholder="Deskripsi singkat tim ini..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="tim_aktif" name="is_active" value="1" checked>
                        <label class="form-check-label" for="tim_aktif">Tim Aktif</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanTim()">
                    <i class="fas fa-save me-1"></i> Simpan Tim
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL KELOLA ANGGOTA ══ -->
<div class="modal fade" id="anggotaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Anggota Tim: <span id="anggotaTimNama"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Kiri: personil belum masuk tim -->
                    <div class="col-md-5">
                        <h6 class="fw-semibold text-muted mb-2">Personil Tersedia</h6>
                        <input type="text" class="form-control form-control-sm mb-2" id="filterPersonil"
                               placeholder="Cari nama / NRP..." oninput="filterPersonilList()">
                        <div id="personilTersedia" class="border rounded p-2"
                             style="height:380px;overflow-y:auto"></div>
                    </div>
                    <!-- Tengah: tombol pindah -->
                    <div class="col-md-2 d-flex flex-column align-items-center justify-content-center gap-3">
                        <button class="btn btn-success btn-sm px-3" onclick="tambahTerpilih()" title="Tambah ke tim">
                            <i class="fas fa-angle-right"></i><i class="fas fa-angle-right"></i>
                        </button>
                        <button class="btn btn-danger btn-sm px-3" onclick="hapusTerpilih()" title="Keluarkan dari tim">
                            <i class="fas fa-angle-left"></i><i class="fas fa-angle-left"></i>
                        </button>
                    </div>
                    <!-- Kanan: anggota tim -->
                    <div class="col-md-5">
                        <h6 class="fw-semibold text-success mb-2">Anggota Tim <span id="jumlahAnggota" class="badge bg-success ms-1">0</span></h6>
                        <div id="anggotaTim" class="border rounded p-2"
                             style="height:380px;overflow-y:auto"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="simpanAnggota()">
                    <i class="fas fa-save me-1"></i> Simpan Anggota
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL BUAT JADWAL DARI TIM ══ -->
<div class="modal fade" id="jadwalTimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Buat Jadwal dari Tim: <span id="jadwalTimNama"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jadwalTimForm">
                    <input type="hidden" id="jt_tim_id" name="tim_id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
                            <select class="form-select" id="jt_shift" name="shift_type" required>
                                <option value="PAGI">Pagi (06:00–14:00)</option>
                                <option value="SIANG">Siang (14:00–22:00)</option>
                                <option value="MALAM">Malam (22:00–06:00)</option>
                                <option value="FULL_DAY">Full Day (07:00–16:00)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" class="form-control" id="jt_lokasi" name="location"
                                   placeholder="Lokasi piket / tugas">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="jt_tgl_mulai" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="jt_tgl_selesai" name="end_date" required>
                            <div class="form-text" id="jt_dateInfo"></div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-semibold mb-3"><i class="fas fa-redo me-1 text-primary"></i>Pola Pengulangan</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Pengulangan</label>
                            <select class="form-select" id="jt_recurrence" name="recurrence_type"
                                    onchange="toggleRecurrenceOptions(this.value)">
                                <option value="none">Tidak Berulang (sekali saja)</option>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="jt_intervalGroup" style="display:none">
                            <label class="form-label fw-semibold">Setiap</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="jt_interval" name="recurrence_interval" min="1" value="1">
                                <span class="input-group-text" id="jt_intervalLabel">hari</span>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3" id="jt_daysGroup" style="display:none">
                            <label class="form-label fw-semibold">Hari dalam Seminggu</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php
                                $hari = ['1'=>'Sen','2'=>'Sel','3'=>'Rab','4'=>'Kam','5'=>'Jum','6'=>'Sab','0'=>'Min'];
                                foreach ($hari as $val => $lbl): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="recurrence_days[]"
                                           value="<?php echo $val; ?>" id="day_<?php echo $val; ?>">
                                    <label class="form-check-label fw-semibold" for="day_<?php echo $val; ?>"><?php echo $lbl; ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 mb-0" id="jt_preview" style="display:none">
                        <i class="fas fa-info-circle me-1"></i> <span id="jt_previewText"></span>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold">Keterangan / Deskripsi</label>
                        <textarea class="form-control" id="jt_keterangan" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning fw-bold" onclick="simpanJadwalTim()">
                    <i class="fas fa-calendar-check me-1"></i> Generate Jadwal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '../api/tim_piket_api.php';

// ── Filter ──────────────────────────────────────────────────────────────────
function resetFilter() {
    ['filterNama','filterBagian','filterJenis','filterAktif'].forEach(id => {
        document.getElementById(id).value = '';
    });
    applyFilter();
}
function applyFilter() {
    const nm = document.getElementById('filterNama').value.toLowerCase();
    const bg = document.getElementById('filterBagian').value;
    const jn = document.getElementById('filterJenis').value;
    const ak = document.getElementById('filterAktif').value;
    document.querySelectorAll('.tim-card').forEach(c => {
        const ok = (!nm || c.dataset.nama.includes(nm))
                && (!bg || c.dataset.bagian === bg)
                && (!jn || c.dataset.jenis  === jn)
                && (ak === '' || c.dataset.aktif  === ak);
        c.style.display = ok ? '' : 'none';
    });
}
['filterNama','filterBagian','filterJenis','filterAktif'].forEach(id => {
    document.getElementById(id).addEventListener('input',  applyFilter);
    document.getElementById(id).addEventListener('change', applyFilter);
});

// ── Modal Tim ───────────────────────────────────────────────────────────────
function toggleRotasi(val) {
    document.getElementById('rotasiGroup').style.display = val === 'ROTASI' ? '' : 'none';
}

function openTambahTim() {
    document.getElementById('timForm').reset();
    document.getElementById('tim_id').value = '';
    document.getElementById('tim_aktif').checked = true;
    document.getElementById('rotasiGroup').style.display = 'none';
    document.getElementById('timModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Tim';
    new bootstrap.Modal(document.getElementById('timModal')).show();
}

function editTim(tim) {
    document.getElementById('tim_id').value         = tim.id;
    document.getElementById('tim_nama').value        = tim.nama_tim;
    document.getElementById('tim_jenis').value       = tim.jenis;
    document.getElementById('tim_bagian').value      = tim.id_bagian || '';
    document.getElementById('tim_unsur').value       = tim.id_unsur  || '';
    document.getElementById('tim_shift').value       = tim.shift_default || '';
    document.getElementById('tim_rotasi').value      = tim.pola_rotasi || '';
    document.getElementById('tim_keterangan').value  = tim.keterangan || '';
    document.getElementById('tim_aktif').checked     = tim.is_active == 1;
    toggleRotasi(tim.shift_default);
    document.getElementById('timModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Tim';
    new bootstrap.Modal(document.getElementById('timModal')).show();
}

function simpanTim() {
    const form = document.getElementById('timForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const fd = new FormData(form);
    fd.append('action', document.getElementById('tim_id').value ? 'update_tim' : 'create_tim');
    if (!document.getElementById('tim_aktif').checked) fd.set('is_active', '0');
    fetch(API, { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { bootstrap.Modal.getInstance(document.getElementById('timModal')).hide(); location.reload(); }
            else alert('Gagal: ' + (d.error || d.message));
        }).catch(e => alert('Error: '+e));
}

function hapusTim(id, nama) {
    if (!confirm('Hapus tim "'+nama+'"?\nSemua data anggota tim ini juga akan dihapus.')) return;
    const fd = new FormData(); fd.append('action','delete_tim'); fd.append('id',id);
    fetch(API, {method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) location.reload(); else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Modal Anggota ────────────────────────────────────────────────────────────
let currentTimId = null;
let allPersonil  = [];
let anggotaSet   = new Set();

function kelolaAnggota(timId, timNama) {
    currentTimId = timId;
    document.getElementById('anggotaTimNama').textContent = timNama;
    document.getElementById('personilTersedia').innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    document.getElementById('anggotaTim').innerHTML = '';
    document.getElementById('jumlahAnggota').textContent = '0';

    Promise.all([
        fetch(API+'?action=get_personil_all').then(r=>r.json()),
        fetch(API+'?action=get_anggota&tim_id='+timId).then(r=>r.json())
    ]).then(([pData, aData]) => {
        allPersonil = pData.data || [];
        const anggota = aData.data || [];
        anggotaSet = new Set(anggota.map(a => a.personil_id));
        renderPersonilLists();
        new bootstrap.Modal(document.getElementById('anggotaModal')).show();
    }).catch(e => alert('Error: '+e));
}

function renderPersonilLists() {
    const filter = document.getElementById('filterPersonil').value.toLowerCase();
    const tersedia = allPersonil.filter(p => !anggotaSet.has(p.nrp) &&
        (p.nama.toLowerCase().includes(filter) || p.nrp.includes(filter)));
    const anggota  = allPersonil.filter(p =>  anggotaSet.has(p.nrp));

    document.getElementById('personilTersedia').innerHTML = tersedia.length
        ? tersedia.map(p => `
            <div class="d-flex align-items-center gap-2 p-2 border-bottom personil-item" style="cursor:pointer"
                 data-nrp="${p.nrp}" onclick="toggleSelect(this)">
                <div class="flex-grow-1">
                    <div class="fw-semibold small">${p.nama}</div>
                    <div class="text-muted" style="font-size:0.7rem">${p.nrp} · ${p.pangkat||''} · ${p.bagian||''}</div>
                </div>
            </div>`).join('')
        : '<div class="text-center text-muted py-3 small">Semua personil sudah masuk tim</div>';

    document.getElementById('anggotaTim').innerHTML = anggota.length
        ? anggota.map(p => `
            <div class="d-flex align-items-center gap-2 p-2 border-bottom personil-item bg-success bg-opacity-10" style="cursor:pointer"
                 data-nrp="${p.nrp}" onclick="toggleSelect(this)">
                <div class="flex-grow-1">
                    <div class="fw-semibold small">${p.nama}</div>
                    <div class="text-muted" style="font-size:0.7rem">${p.nrp} · ${p.pangkat||''} · ${p.bagian||''}</div>
                </div>
                <i class="fas fa-check-circle text-success"></i>
            </div>`).join('')
        : '<div class="text-center text-muted py-3 small">Belum ada anggota</div>';

    document.getElementById('jumlahAnggota').textContent = anggota.length;
}

function toggleSelect(el) {
    el.classList.toggle('table-active');
    el.style.outline = el.classList.contains('table-active') ? '2px solid #0d6efd' : '';
}

function filterPersonilList() { renderPersonilLists(); }

function tambahTerpilih() {
    document.querySelectorAll('#personilTersedia .personil-item.table-active').forEach(el => {
        anggotaSet.add(el.dataset.nrp);
    });
    renderPersonilLists();
}
function hapusTerpilih() {
    document.querySelectorAll('#anggotaTim .personil-item.table-active').forEach(el => {
        anggotaSet.delete(el.dataset.nrp);
    });
    renderPersonilLists();
}

function simpanAnggota() {
    const fd = new FormData();
    fd.append('action', 'save_anggota');
    fd.append('tim_id', currentTimId);
    anggotaSet.forEach(nrp => fd.append('personil_ids[]', nrp));
    fetch(API, {method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('anggotaModal')).hide();
            location.reload();
        } else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Modal Jadwal dari Tim ────────────────────────────────────────────────────
function buatJadwalDariTim(tim) {
    document.getElementById('jt_tim_id').value   = tim.id;
    document.getElementById('jadwalTimNama').textContent = tim.nama_tim;
    document.getElementById('jadwalTimForm').reset();
    document.getElementById('jt_tim_id').value   = tim.id;
    if (tim.shift_default && tim.shift_default !== 'ROTASI') {
        document.getElementById('jt_shift').value = tim.shift_default;
    }
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('jt_tgl_mulai').value  = today;
    document.getElementById('jt_tgl_selesai').value = today;
    document.getElementById('jt_intervalGroup').style.display = 'none';
    document.getElementById('jt_daysGroup').style.display     = 'none';
    document.getElementById('jt_preview').style.display       = 'none';
    new bootstrap.Modal(document.getElementById('jadwalTimModal')).show();
}

function toggleRecurrenceOptions(val) {
    const ig = document.getElementById('jt_intervalGroup');
    const dg = document.getElementById('jt_daysGroup');
    const lb = document.getElementById('jt_intervalLabel');
    ig.style.display = val === 'none' ? 'none' : '';
    dg.style.display = val === 'weekly' ? '' : 'none';
    const labels = {daily:'hari', weekly:'minggu', monthly:'bulan', yearly:'tahun'};
    if (lb) lb.textContent = labels[val] || 'hari';
    updatePreview();
}

function updatePreview() {
    const rec  = document.getElementById('jt_recurrence').value;
    const s    = document.getElementById('jt_tgl_mulai').value;
    const e    = document.getElementById('jt_tgl_selesai').value;
    const prev = document.getElementById('jt_preview');
    const txt  = document.getElementById('jt_previewText');
    if (!s || !e) { prev.style.display='none'; return; }
    const days = Math.round((new Date(e)-new Date(s))/86400000)+1;
    const n    = parseInt(document.getElementById('jt_interval')?.value||1);
    let msg;
    if (rec==='none')    msg = `Jadwal 1x pada ${fmtDate(s)}`;
    else if (rec==='daily')   msg = `Setiap ${n} hari dari ${fmtDate(s)} s/d ${fmtDate(e)} (±${days} hari)`;
    else if (rec==='weekly') {
        const checked = [...document.querySelectorAll('input[name="recurrence_days[]"]:checked')].map(x=>x.value);
        const nm = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        msg = `Setiap ${n} minggu, hari: ${checked.map(d=>nm[d]).join(', ')||'(pilih hari)'}, s/d ${fmtDate(e)}`;
    }
    else if (rec==='monthly') msg = `Setiap ${n} bulan tanggal ${new Date(s).getDate()}, s/d ${fmtDate(e)}`;
    else                      msg = `Setiap ${n} tahun tanggal ${fmtDate(s).slice(0,5)}, s/d ${fmtDate(e)}`;
    txt.textContent = msg;
    prev.style.display = '';
}

function fmtDate(d) {
    if (!d) return '-';
    const p = d.split('-'); return p[2]+'/'+p[1]+'/'+p[0];
}

['jt_tgl_mulai','jt_tgl_selesai','jt_interval'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', updatePreview);
});
document.querySelectorAll('input[name="recurrence_days[]"]').forEach(el =>
    el.addEventListener('change', updatePreview));

// Date range info
document.getElementById('jt_tgl_selesai').addEventListener('change', function() {
    const s = document.getElementById('jt_tgl_mulai').value;
    const e = this.value;
    const info = document.getElementById('jt_dateInfo');
    if (s && e && e < s) {
        info.className = 'form-text text-danger';
        info.textContent = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    } else if (s && e) {
        const d = Math.round((new Date(e)-new Date(s))/86400000)+1;
        info.className = 'form-text text-success';
        info.textContent = 'Rentang: '+d+' hari';
    }
    updatePreview();
});
document.getElementById('jt_tgl_mulai').addEventListener('change', updatePreview);

function simpanJadwalTim() {
    const form = document.getElementById('jadwalTimForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const s = document.getElementById('jt_tgl_mulai').value;
    const e = document.getElementById('jt_tgl_selesai').value;
    if (s && e && e < s) { alert('Tanggal selesai tidak boleh sebelum tanggal mulai.'); return; }

    const fd = new FormData(form);
    fd.append('action', 'generate_jadwal_tim');
    // Kumpulkan hari yang dicheck
    const days = [...document.querySelectorAll('input[name="recurrence_days[]"]:checked')].map(x=>x.value);
    fd.set('recurrence_days', days.join(','));

    const btn = document.querySelector('#jadwalTimModal .btn-warning');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';

    fetch(API, {method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-calendar-check me-1"></i> Generate Jadwal';
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('jadwalTimModal')).hide();
                alert('✅ Berhasil generate '+d.count+' jadwal!\n'+d.message);
                window.location.href = '../pages/calendar_dashboard.php';
            } else alert('Gagal: '+(d.error||d.message));
        }).catch(e=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-calendar-check me-1"></i> Generate Jadwal';
            alert('Error: '+e);
        });
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
