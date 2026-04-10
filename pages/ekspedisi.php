<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Ekspedisi Surat — Agenda Surat Masuk & Keluar';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Ekspedisi Surat</h4>
            <small class="text-muted">Buku Agenda Surat Masuk & Keluar — BAGOPS Polres Samosir</small>
        </div>
        <?php if (AuthHelper::canEdit()): ?>
        <div class="btn-group">
            <button class="btn btn-primary" onclick="openTambah('masuk')">
                <i class="fas fa-inbox me-1"></i> Surat Masuk
            </button>
            <button class="btn btn-success" onclick="openTambah('keluar')">
                <i class="fas fa-paper-plane me-1"></i> Surat Keluar
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" id="filterCari" class="form-control form-control-sm" placeholder="Cari perihal / nomor surat...">
                </div>
                <div class="col-md-2">
                    <select id="filterJenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        <option value="masuk">Surat Masuk</option>
                        <option value="keluar">Surat Keluar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterKategori" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        <option value="biasa">Biasa</option>
                        <option value="penting">Penting</option>
                        <option value="rahasia">Rahasia</option>
                        <option value="segera">Segera</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="diterima">Diterima</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="diarsipkan">Diarsipkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" id="filterBulan" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="loadData()"><i class="fas fa-sync"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-primary" id="statTotal">0</div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-info" id="statMasuk">0</div>
                <div class="text-muted small">Masuk</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-success" id="statKeluar">0</div>
                <div class="text-muted small">Keluar</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-warning" id="statDiproses">0</div>
                <div class="text-muted small">Diproses</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-success" id="statSelesai">0</div>
                <div class="text-muted small">Selesai</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-secondary" id="statArsip">0</div>
                <div class="text-muted small">Diarsipkan</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">No</th>
                            <th style="width:130px">No. Agenda</th>
                            <th style="width:80px" class="text-center">Jenis</th>
                            <th>Perihal</th>
                            <th style="width:160px">Pengirim / Tujuan</th>
                            <th style="width:100px">Tgl Surat</th>
                            <th style="width:90px" class="text-center">Kategori</th>
                            <th style="width:100px" class="text-center">Status</th>
                            <th style="width:120px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="suratBody">
                        <tr><td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small" id="suratFooter">Menampilkan 0 surat</div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal fade" id="suratModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="suratModalTitle"><i class="fas fa-plus me-2"></i>Tambah Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="suratForm">
                    <input type="hidden" id="surat_id">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Surat <span class="text-danger">*</span></label>
                            <select class="form-select" id="surat_jenis" onchange="updateJenisUI()">
                                <option value="masuk">Surat Masuk</option>
                                <option value="keluar">Surat Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select class="form-select" id="surat_kategori">
                                <option value="biasa">Biasa</option>
                                <option value="penting">Penting</option>
                                <option value="rahasia">Rahasia</option>
                                <option value="segera">Segera</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="surat_status">
                                <option value="diterima">Diterima</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                                <option value="diarsipkan">Diarsipkan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Surat</label>
                            <input type="text" class="form-control" id="surat_nomor" placeholder="Nomor surat asli">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Surat</label>
                            <input type="date" class="form-control" id="surat_tgl_surat">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" id="lblTglTerima">Tanggal Diterima</label>
                            <input type="date" class="form-control" id="surat_tgl_terima">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Perihal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="surat_perihal" required placeholder="Perihal / tentang surat">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" id="lblPengirim">Pengirim</label>
                            <input type="text" class="form-control" id="surat_pengirim" placeholder="Instansi / pihak pengirim">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" id="lblTujuan">Tujuan</label>
                            <input type="text" class="form-control" id="surat_tujuan" placeholder="Instansi / pihak tujuan">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Disposisi</label>
                        <textarea class="form-control" id="surat_disposisi" rows="2" placeholder="Disposisi pimpinan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea class="form-control" id="surat_keterangan" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanSurat()"><i class="fas fa-save me-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '<?= url("api/ekspedisi_api.php") ?>';
let allSurat = [];

const jenisBadge   = { masuk:'<span class="badge bg-info text-dark">Masuk</span>', keluar:'<span class="badge bg-success">Keluar</span>' };
const kategoriBadge= { biasa:'<span class="badge bg-light text-dark border">Biasa</span>', penting:'<span class="badge bg-warning text-dark">Penting</span>', rahasia:'<span class="badge bg-danger">Rahasia</span>', segera:'<span class="badge bg-dark">Segera</span>' };
const statusBadge  = { diterima:'<span class="badge bg-info text-dark">Diterima</span>', diproses:'<span class="badge bg-warning text-dark">Diproses</span>', selesai:'<span class="badge bg-success">Selesai</span>', diarsipkan:'<span class="badge bg-secondary">Diarsipkan</span>' };

document.addEventListener('DOMContentLoaded', loadData);

function loadData() {
    fetch(API + '?action=get_all')
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);
            allSurat = data.data;
            renderTable();
        })
        .catch(err => {
            document.getElementById('suratBody').innerHTML =
                `<tr><td colspan="9" class="text-center text-danger py-4">${err.message}</td></tr>`;
        });
}

function renderTable() {
    const cari     = document.getElementById('filterCari').value.toLowerCase();
    const jenis    = document.getElementById('filterJenis').value;
    const kategori = document.getElementById('filterKategori').value;
    const status   = document.getElementById('filterStatus').value;
    const bulan    = document.getElementById('filterBulan').value;

    const filtered = allSurat.filter(s => {
        return (!cari || (s.perihal||'').toLowerCase().includes(cari) || (s.nomor_surat||'').toLowerCase().includes(cari) || (s.nomor_agenda||'').toLowerCase().includes(cari))
            && (!jenis || s.jenis === jenis)
            && (!kategori || s.kategori === kategori)
            && (!status || s.status === status)
            && (!bulan || (s.tanggal_surat||'').startsWith(bulan));
    });

    // Stats
    document.getElementById('statTotal').textContent    = allSurat.length;
    document.getElementById('statMasuk').textContent     = allSurat.filter(s => s.jenis==='masuk').length;
    document.getElementById('statKeluar').textContent    = allSurat.filter(s => s.jenis==='keluar').length;
    document.getElementById('statDiproses').textContent  = allSurat.filter(s => s.status==='diproses').length;
    document.getElementById('statSelesai').textContent   = allSurat.filter(s => s.status==='selesai').length;
    document.getElementById('statArsip').textContent     = allSurat.filter(s => s.status==='diarsipkan').length;

    if (filtered.length === 0) {
        document.getElementById('suratBody').innerHTML =
            '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data surat.</td></tr>';
        document.getElementById('suratFooter').textContent = 'Menampilkan 0 surat';
        return;
    }

    let html = '';
    filtered.forEach((s, i) => {
        const tgl = s.tanggal_surat ? new Date(s.tanggal_surat).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '-';
        const contact = s.jenis === 'masuk' ? (s.pengirim||'-') : (s.tujuan||'-');
        html += `<tr>
            <td class="text-muted">${i+1}</td>
            <td class="small"><code>${s.nomor_agenda||'-'}</code></td>
            <td class="text-center">${jenisBadge[s.jenis]||s.jenis}</td>
            <td>
                <div class="fw-semibold">${s.perihal||'-'}</div>
                ${s.nomor_surat ? '<div class="text-muted small">No: '+s.nomor_surat+'</div>' : ''}
            </td>
            <td class="small">${contact}</td>
            <td class="text-nowrap small">${tgl}</td>
            <td class="text-center">${kategoriBadge[s.kategori]||s.kategori}</td>
            <td class="text-center">${statusBadge[s.status]||s.status}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    ${SPRIN_CAN_EDIT ? `<button class="btn btn-outline-warning" title="Edit" onclick="editSurat(${s.id})"><i class="fas fa-edit"></i></button>` : ''}
                    ${SPRIN_IS_ADMIN ? `<button class="btn btn-outline-danger" title="Hapus" onclick="hapusSurat(${s.id},'${(s.nomor_agenda||'').replace(/'/g,"\\'")}')"><i class="fas fa-trash"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    });
    document.getElementById('suratBody').innerHTML = html;
    document.getElementById('suratFooter').textContent = `Menampilkan ${filtered.length} surat`;
}

['filterCari','filterJenis','filterKategori','filterStatus','filterBulan'].forEach(id => {
    document.getElementById(id).addEventListener('input', renderTable);
    document.getElementById(id).addEventListener('change', renderTable);
});

function openTambah(jenis) {
    document.getElementById('suratForm').reset();
    document.getElementById('surat_id').value = '';
    document.getElementById('surat_jenis').value = jenis || 'masuk';
    document.getElementById('surat_tgl_terima').value = new Date().toISOString().split('T')[0];
    document.getElementById('suratModalTitle').innerHTML = jenis === 'keluar'
        ? '<i class="fas fa-paper-plane me-2"></i>Tambah Surat Keluar'
        : '<i class="fas fa-inbox me-2"></i>Tambah Surat Masuk';
    updateJenisUI();
    new bootstrap.Modal(document.getElementById('suratModal')).show();
}

function updateJenisUI() {
    const j = document.getElementById('surat_jenis').value;
    document.getElementById('lblPengirim').textContent = j === 'masuk' ? 'Pengirim' : 'Dari (internal)';
    document.getElementById('lblTujuan').textContent   = j === 'masuk' ? 'Disposisi Ke' : 'Tujuan';
    document.getElementById('lblTglTerima').textContent = j === 'masuk' ? 'Tanggal Diterima' : 'Tanggal Dikirim';
}

function simpanSurat() {
    const form = document.getElementById('suratForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const id = document.getElementById('surat_id').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('jenis',         document.getElementById('surat_jenis').value);
    fd.append('nomor_surat',   document.getElementById('surat_nomor').value);
    fd.append('tanggal_surat', document.getElementById('surat_tgl_surat').value);
    fd.append('tanggal_terima',document.getElementById('surat_tgl_terima').value);
    fd.append('perihal',       document.getElementById('surat_perihal').value);
    fd.append('pengirim',      document.getElementById('surat_pengirim').value);
    fd.append('tujuan',        document.getElementById('surat_tujuan').value);
    fd.append('kategori',      document.getElementById('surat_kategori').value);
    fd.append('status',        document.getElementById('surat_status').value);
    fd.append('disposisi',     document.getElementById('surat_disposisi').value);
    fd.append('keterangan',    document.getElementById('surat_keterangan').value);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('suratModal')).hide();
                showToast('success', data.message);
                loadData();
            } else {
                showToast('danger', data.message);
            }
        })
        .catch(err => showToast('danger', err.message));
}

function editSurat(id) {
    fetch(API + '?action=get_one&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast('danger', data.message); return; }
            const s = data.data;
            document.getElementById('surat_id').value = s.id;
            document.getElementById('surat_jenis').value = s.jenis;
            document.getElementById('surat_nomor').value = s.nomor_surat || '';
            document.getElementById('surat_tgl_surat').value = s.tanggal_surat || '';
            document.getElementById('surat_tgl_terima').value = s.tanggal_terima || '';
            document.getElementById('surat_perihal').value = s.perihal || '';
            document.getElementById('surat_pengirim').value = s.pengirim || '';
            document.getElementById('surat_tujuan').value = s.tujuan || '';
            document.getElementById('surat_kategori').value = s.kategori || 'biasa';
            document.getElementById('surat_status').value = s.status || 'diterima';
            document.getElementById('surat_disposisi').value = s.disposisi || '';
            document.getElementById('surat_keterangan').value = s.keterangan || '';
            document.getElementById('suratModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Surat';
            updateJenisUI();
            new bootstrap.Modal(document.getElementById('suratModal')).show();
        })
        .catch(err => showToast('danger', err.message));
}

function hapusSurat(id, nomor) {
    if (!confirm('Hapus surat "' + nomor + '"?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { showToast('success', data.message); loadData(); }
            else showToast('danger', data.message);
        })
        .catch(err => showToast('danger', err.message));
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
