<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator', 'viewer');

$page_title = 'Training Management — Pelatihan Praoperasi';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-dumbbell me-2 text-primary"></i>Training Management</h4>
            <small class="text-muted">Jadwal & Rekap Pelatihan Praoperasi — Polres Samosir</small>
        </div>
        <?php if (AuthHelper::canEdit()): ?>
        <button class="btn btn-primary" onclick="openTambah()"><i class="fas fa-plus me-1"></i>Tambah Pelatihan</button>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" id="filterCari" class="form-control form-control-sm" placeholder="Cari nama pelatihan...">
                </div>
                <div class="col-md-2">
                    <select id="filterJenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        <option value="menembak">Menembak</option>
                        <option value="bela_diri">Bela Diri</option>
                        <option value="sar">SAR</option>
                        <option value="ketahanan">Ketahanan</option>
                        <option value="teknis">Teknis</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="rencana">Rencana</option>
                        <option value="berlangsung">Berlangsung</option>
                        <option value="selesai">Selesai</option>
                        <option value="batal">Batal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="month" id="filterBulan" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="loadData()"><i class="fas fa-sync me-1"></i>Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-primary" id="stTotal">0</div><div class="text-muted small">Total</div></div></div>
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-success" id="stSelesai">0</div><div class="text-muted small">Selesai</div></div></div>
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-info" id="stRencana">0</div><div class="text-muted small">Rencana</div></div></div>
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-warning" id="stBerlangsung">0</div><div class="text-muted small">Berlangsung</div></div></div>
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-dark" id="stJam">0</div><div class="text-muted small">Total Jam</div></div></div>
        <div class="col-6 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-3 fw-bold text-secondary" id="stPeserta">0</div><div class="text-muted small">Total Peserta</div></div></div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">No</th>
                            <th>Nama Pelatihan</th>
                            <th style="width:110px" class="text-center">Jenis</th>
                            <th style="width:110px">Tgl Mulai</th>
                            <th style="width:80px" class="text-center">Jam</th>
                            <th style="width:100px" class="text-center">Peserta</th>
                            <th style="width:100px" class="text-center">Status</th>
                            <th style="width:120px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pelBody">
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small" id="pelFooter">Menampilkan 0 pelatihan</div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="pelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="pelModalTitle"><i class="fas fa-plus me-2"></i>Tambah Pelatihan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pelForm">
                    <input type="hidden" id="pel_id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pel_nama" required placeholder="Latihan Menembak Laras Panjang">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis</label>
                            <select class="form-select" id="pel_jenis">
                                <option value="menembak">Menembak</option>
                                <option value="bela_diri">Bela Diri</option>
                                <option value="sar">SAR</option>
                                <option value="ketahanan">Ketahanan</option>
                                <option value="teknis">Teknis</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tgl Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="pel_tgl_mulai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tgl Selesai</label>
                            <input type="date" class="form-control" id="pel_tgl_selesai">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jam Latihan</label>
                            <input type="number" step="0.5" min="0" class="form-control" id="pel_jam" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="pel_status">
                                <option value="rencana">Rencana</option>
                                <option value="berlangsung">Berlangsung</option>
                                <option value="selesai">Selesai</option>
                                <option value="batal">Batal</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" class="form-control" id="pel_lokasi" placeholder="Lapangan tembak">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Instruktur</label>
                            <input type="text" class="form-control" id="pel_instruktur" placeholder="Nama instruktur">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Target</label>
                            <input type="number" min="0" class="form-control" id="pel_target" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Hadir</label>
                            <input type="number" min="0" class="form-control" id="pel_hadir" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" id="pel_deskripsi" rows="3" placeholder="Uraian pelatihan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpan()"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '<?= url("api/pelatihan_api.php") ?>';
let allData = [];
const jenisBadge = {menembak:'<span class="badge bg-danger">Menembak</span>',bela_diri:'<span class="badge bg-dark">Bela Diri</span>',sar:'<span class="badge bg-warning text-dark">SAR</span>',ketahanan:'<span class="badge bg-info text-dark">Ketahanan</span>',teknis:'<span class="badge bg-primary">Teknis</span>',lainnya:'<span class="badge bg-secondary">Lainnya</span>'};
const statusBadge = {rencana:'<span class="badge bg-info text-dark">Rencana</span>',berlangsung:'<span class="badge bg-warning text-dark">Berlangsung</span>',selesai:'<span class="badge bg-success">Selesai</span>',batal:'<span class="badge bg-secondary">Batal</span>'};

document.addEventListener('DOMContentLoaded', loadData);

function loadData() {
    fetch(API + '?action=get_all').then(r=>r.json()).then(d => {
        if (!d.success) throw new Error(d.message);
        allData = d.data;
        renderTable();
        loadStats();
    }).catch(e => {
        document.getElementById('pelBody').innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${e.message}</td></tr>`;
    });
}

function loadStats() {
    fetch(API + '?action=get_stats').then(r=>r.json()).then(d => {
        if (!d.success) return;
        document.getElementById('stTotal').textContent = d.data.total || 0;
        document.getElementById('stSelesai').textContent = d.data.selesai || 0;
        document.getElementById('stRencana').textContent = d.data.rencana || 0;
        document.getElementById('stBerlangsung').textContent = d.data.berlangsung || 0;
        document.getElementById('stJam').textContent = parseFloat(d.data.total_jam || 0).toFixed(0);
        document.getElementById('stPeserta').textContent = d.data.total_peserta || 0;
    });
}

function renderTable() {
    const cari = document.getElementById('filterCari').value.toLowerCase();
    const jenis = document.getElementById('filterJenis').value;
    const status = document.getElementById('filterStatus').value;
    const bulan = document.getElementById('filterBulan').value;

    const filtered = allData.filter(p => {
        return (!cari || (p.nama_pelatihan||'').toLowerCase().includes(cari))
            && (!jenis || p.jenis === jenis)
            && (!status || p.status === status)
            && (!bulan || (p.tanggal_mulai||'').startsWith(bulan));
    });

    if (!filtered.length) {
        document.getElementById('pelBody').innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data pelatihan.</td></tr>';
        document.getElementById('pelFooter').textContent = 'Menampilkan 0 pelatihan';
        return;
    }

    let html = '';
    filtered.forEach((p, i) => {
        const tgl = p.tanggal_mulai ? new Date(p.tanggal_mulai).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '-';
        html += `<tr>
            <td class="text-muted">${i+1}</td>
            <td>
                <div class="fw-semibold">${p.nama_pelatihan}</div>
                ${p.lokasi ? '<div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>'+p.lokasi+'</div>' : ''}
                ${p.instruktur ? '<div class="text-muted small"><i class="fas fa-user-tie me-1"></i>'+p.instruktur+'</div>' : ''}
            </td>
            <td class="text-center">${jenisBadge[p.jenis]||p.jenis}</td>
            <td class="small text-nowrap">${tgl}</td>
            <td class="text-center">${parseFloat(p.jam_latihan||0).toFixed(1)}</td>
            <td class="text-center">${p.peserta_hadir||0}/${p.peserta_target||0}</td>
            <td class="text-center">${statusBadge[p.status]||p.status}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    ${SPRIN_CAN_EDIT ? `<button class="btn btn-outline-warning" onclick="editPel(${p.id})"><i class="fas fa-edit"></i></button>` : ''}
                    ${SPRIN_IS_ADMIN ? `<button class="btn btn-outline-danger" onclick="hapusPel(${p.id},'${(p.nama_pelatihan||'').replace(/'/g,"\\'")}')"><i class="fas fa-trash"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    });
    document.getElementById('pelBody').innerHTML = html;
    document.getElementById('pelFooter').textContent = `Menampilkan ${filtered.length} pelatihan`;
}

['filterCari','filterJenis','filterStatus','filterBulan'].forEach(id => {
    document.getElementById(id).addEventListener('input', renderTable);
    document.getElementById(id).addEventListener('change', renderTable);
});

function openTambah() {
    document.getElementById('pelForm').reset();
    document.getElementById('pel_id').value = '';
    document.getElementById('pel_tgl_mulai').value = new Date().toISOString().split('T')[0];
    document.getElementById('pelModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Pelatihan';
    new bootstrap.Modal(document.getElementById('pelModal')).show();
}

function simpan() {
    const form = document.getElementById('pelForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const id = document.getElementById('pel_id').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('nama_pelatihan', document.getElementById('pel_nama').value);
    fd.append('jenis', document.getElementById('pel_jenis').value);
    fd.append('tanggal_mulai', document.getElementById('pel_tgl_mulai').value);
    fd.append('tanggal_selesai', document.getElementById('pel_tgl_selesai').value);
    fd.append('jam_latihan', document.getElementById('pel_jam').value);
    fd.append('lokasi', document.getElementById('pel_lokasi').value);
    fd.append('instruktur', document.getElementById('pel_instruktur').value);
    fd.append('peserta_target', document.getElementById('pel_target').value);
    fd.append('peserta_hadir', document.getElementById('pel_hadir').value);
    fd.append('status', document.getElementById('pel_status').value);
    fd.append('deskripsi', document.getElementById('pel_deskripsi').value);

    fetch(API, {method:'POST', body:fd}).then(r=>r.json()).then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('pelModal')).hide();
            showToast('success', d.message); loadData();
        } else showToast('danger', d.message);
    }).catch(e => showToast('danger', e.message));
}

function editPel(id) {
    fetch(API + '?action=get_one&id=' + id).then(r=>r.json()).then(d => {
        if (!d.success) { showToast('danger', d.message); return; }
        const p = d.data;
        document.getElementById('pel_id').value = p.id;
        document.getElementById('pel_nama').value = p.nama_pelatihan;
        document.getElementById('pel_jenis').value = p.jenis;
        document.getElementById('pel_tgl_mulai').value = p.tanggal_mulai;
        document.getElementById('pel_tgl_selesai').value = p.tanggal_selesai || '';
        document.getElementById('pel_jam').value = p.jam_latihan;
        document.getElementById('pel_lokasi').value = p.lokasi || '';
        document.getElementById('pel_instruktur').value = p.instruktur || '';
        document.getElementById('pel_target').value = p.peserta_target;
        document.getElementById('pel_hadir').value = p.peserta_hadir;
        document.getElementById('pel_status').value = p.status;
        document.getElementById('pel_deskripsi').value = p.deskripsi || '';
        document.getElementById('pelModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Pelatihan';
        new bootstrap.Modal(document.getElementById('pelModal')).show();
    });
}

function hapusPel(id, nama) {
    if (!confirm('Hapus pelatihan "' + nama + '"?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fetch(API, {method:'POST', body:fd}).then(r=>r.json()).then(d => {
        if (d.success) { showToast('success', d.message); loadData(); }
        else showToast('danger', d.message);
    });
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
