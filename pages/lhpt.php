<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator');

$page_title = 'LHPT — Laporan Hasil Pelaksanaan Tugas';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>LHPT — Laporan Hasil Pelaksanaan Tugas</h4>
            <small class="text-muted">Laporan wajib setelah operasi kepolisian selesai dilaksanakan</small>
        </div>
        <button class="btn btn-primary" onclick="openTambahLHPT()">
            <i class="fas fa-plus me-1"></i> Tambah LHPT
        </button>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" id="filterCari" class="form-control form-control-sm" placeholder="Cari nama operasi / nomor LHPT...">
                </div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="month" id="filterBulan" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="loadLHPT()">
                        <i class="fas fa-sync me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary" id="statTotal">0</div>
                <div class="text-muted small">Total LHPT</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-secondary" id="statDraft">0</div>
                <div class="text-muted small">Draft</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning" id="statSubmitted">0</div>
                <div class="text-muted small">Submitted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success" id="statApproved">0</div>
                <div class="text-muted small">Approved</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="lhptTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">No</th>
                            <th style="width:210px">Nomor LHPT</th>
                            <th>Operasi</th>
                            <th style="width:120px">Tgl Laporan</th>
                            <th style="width:110px" class="text-center">Status</th>
                            <th style="width:150px">Pelapor</th>
                            <th style="width:130px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="lhptBody">
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat data...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small" id="lhptFooter">Menampilkan 0 LHPT</div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT -->
<div class="modal fade" id="lhptModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="lhptModalTitle"><i class="fas fa-plus me-2"></i>Tambah LHPT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="lhptForm">
                    <input type="hidden" id="lhpt_id">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Operasi Kepolisian <span class="text-danger">*</span></label>
                            <select class="form-select" id="lhpt_operation" required>
                                <option value="">-- Pilih Operasi --</option>
                            </select>
                            <div class="form-text">Pilih operasi yang telah dilaksanakan</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Laporan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="lhpt_tanggal" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Isi Laporan / Uraian Pelaksanaan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="lhpt_isi" rows="5" required
                                  placeholder="Uraikan pelaksanaan operasi: waktu, tempat, kegiatan yang dilaksanakan, jumlah personil yang bertugas..."></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hasil Pelaksanaan</label>
                            <textarea class="form-control" id="lhpt_hasil" rows="3"
                                      placeholder="Capaian operasi: jumlah penindakan, pelanggaran terdeteksi, barang bukti, dll..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kendala / Hambatan</label>
                            <textarea class="form-control" id="lhpt_kendala" rows="3"
                                      placeholder="Kendala yang dihadapi selama pelaksanaan operasi..."></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rekomendasi / Saran</label>
                        <textarea class="form-control" id="lhpt_rekomendasi" rows="3"
                                  placeholder="Saran dan rekomendasi untuk pelaksanaan operasi selanjutnya..."></textarea>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pelapor</label>
                            <input type="text" class="form-control" id="lhpt_pelapor" placeholder="Nama pelapor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jabatan Pelapor</label>
                            <input type="text" class="form-control" id="lhpt_jabatan_pelapor" placeholder="Jabatan pelapor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="lhpt_status">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanLHPT()">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VIEW -->
<div class="modal fade" id="viewLhptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail LHPT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLhptBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-outline-dark" onclick="cetakLHPT()"><i class="fa-solid fa-print me-1"></i>Cetak</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '<?= url("api/lhpt_api.php") ?>';
const statusBadge = {
    draft: '<span class="badge bg-secondary">Draft</span>',
    submitted: '<span class="badge bg-warning text-dark">Submitted</span>',
    approved: '<span class="badge bg-success">Approved</span>'
};

let allLhpt = [];
let _currentLhpt = null;

document.addEventListener('DOMContentLoaded', loadLHPT);

function loadLHPT() {
    fetch(API + '?action=get_all')
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);
            allLhpt = data.data;
            renderTable();
        })
        .catch(err => {
            document.getElementById('lhptBody').innerHTML =
                `<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>${err.message}</td></tr>`;
        });
}

function renderTable() {
    const cari   = document.getElementById('filterCari').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const bulan  = document.getElementById('filterBulan').value;

    const filtered = allLhpt.filter(l => {
        const matchCari = !cari || (l.operation_name||'').toLowerCase().includes(cari) || (l.nomor_lhpt||'').toLowerCase().includes(cari);
        const matchStatus = !status || l.status_lhpt === status;
        const matchBulan = !bulan || (l.tanggal_laporan||'').startsWith(bulan);
        return matchCari && matchStatus && matchBulan;
    });

    // Stats
    document.getElementById('statTotal').textContent     = allLhpt.length;
    document.getElementById('statDraft').textContent      = allLhpt.filter(l => l.status_lhpt === 'draft').length;
    document.getElementById('statSubmitted').textContent   = allLhpt.filter(l => l.status_lhpt === 'submitted').length;
    document.getElementById('statApproved').textContent    = allLhpt.filter(l => l.status_lhpt === 'approved').length;

    if (filtered.length === 0) {
        document.getElementById('lhptBody').innerHTML =
            '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data LHPT.</td></tr>';
        document.getElementById('lhptFooter').textContent = 'Menampilkan 0 LHPT';
        return;
    }

    let html = '';
    filtered.forEach((l, i) => {
        const tgl = l.tanggal_laporan ? new Date(l.tanggal_laporan).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';
        html += `<tr>
            <td class="text-muted">${i+1}</td>
            <td class="small"><code>${l.nomor_lhpt || '-'}</code></td>
            <td>
                <div class="fw-semibold">${l.operation_name || '-'}</div>
                <div class="text-muted small">${l.nomor_sprint || ''}</div>
            </td>
            <td class="text-nowrap">${tgl}</td>
            <td class="text-center">${statusBadge[l.status_lhpt] || l.status_lhpt}</td>
            <td class="small">${l.pelapor || '-'}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" title="Lihat" onclick="viewLHPT(${l.id})"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-outline-warning" title="Edit" onclick="editLHPT(${l.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger" title="Hapus" onclick="hapusLHPT(${l.id},'${(l.nomor_lhpt||'').replace(/'/g,"\\'")}')"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    document.getElementById('lhptBody').innerHTML = html;
    document.getElementById('lhptFooter').textContent = `Menampilkan ${filtered.length} LHPT`;
}

// Filters
['filterCari','filterStatus','filterBulan'].forEach(id => {
    document.getElementById(id).addEventListener('input', renderTable);
    document.getElementById(id).addEventListener('change', renderTable);
});

function openTambahLHPT() {
    document.getElementById('lhptForm').reset();
    document.getElementById('lhpt_id').value = '';
    document.getElementById('lhptModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah LHPT';
    document.getElementById('lhpt_tanggal').value = new Date().toISOString().split('T')[0];
    loadOperasiDropdown();
    new bootstrap.Modal(document.getElementById('lhptModal')).show();
}

function loadOperasiDropdown(selectedId) {
    fetch(API + '?action=get_operations')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const sel = document.getElementById('lhpt_operation');
            sel.innerHTML = '<option value="">-- Pilih Operasi --</option>';
            data.data.forEach(op => {
                const lbl = `${op.operation_name} ${op.nomor_sprint ? '('+op.nomor_sprint+')' : ''} ${op.lhpt_count > 0 ? '['+op.lhpt_count+' LHPT]' : ''}`;
                sel.innerHTML += `<option value="${op.id}" ${op.id == selectedId ? 'selected' : ''}>${lbl}</option>`;
            });
        });
}

function simpanLHPT() {
    const form = document.getElementById('lhptForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const id = document.getElementById('lhpt_id').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('operation_id', document.getElementById('lhpt_operation').value);
    fd.append('tanggal_laporan', document.getElementById('lhpt_tanggal').value);
    fd.append('isi_laporan', document.getElementById('lhpt_isi').value);
    fd.append('hasil', document.getElementById('lhpt_hasil').value);
    fd.append('kendala', document.getElementById('lhpt_kendala').value);
    fd.append('rekomendasi', document.getElementById('lhpt_rekomendasi').value);
    fd.append('pelapor', document.getElementById('lhpt_pelapor').value);
    fd.append('jabatan_pelapor', document.getElementById('lhpt_jabatan_pelapor').value);
    fd.append('status_lhpt', document.getElementById('lhpt_status').value);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('lhptModal')).hide();
                showToast('success', data.message);
                loadLHPT();
            } else {
                showToast('danger', 'Gagal: ' + data.message);
            }
        })
        .catch(err => showToast('danger', 'Error: ' + err.message));
}

function editLHPT(id) {
    fetch(API + '?action=get_one&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast('danger', data.message); return; }
            const l = data.data;
            document.getElementById('lhpt_id').value = l.id;
            document.getElementById('lhptModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit LHPT';
            loadOperasiDropdown(l.operation_id);
            document.getElementById('lhpt_tanggal').value = l.tanggal_laporan;
            document.getElementById('lhpt_isi').value = l.isi_laporan || '';
            document.getElementById('lhpt_hasil').value = l.hasil || '';
            document.getElementById('lhpt_kendala').value = l.kendala || '';
            document.getElementById('lhpt_rekomendasi').value = l.rekomendasi || '';
            document.getElementById('lhpt_pelapor').value = l.pelapor || '';
            document.getElementById('lhpt_jabatan_pelapor').value = l.jabatan_pelapor || '';
            document.getElementById('lhpt_status').value = l.status_lhpt || 'draft';
            new bootstrap.Modal(document.getElementById('lhptModal')).show();
        })
        .catch(err => showToast('danger', err.message));
}

function viewLHPT(id) {
    fetch(API + '?action=get_one&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast('danger', data.message); return; }
            _currentLhpt = data.data;
            const l = data.data;
            const tgl = l.tanggal_laporan ? new Date(l.tanggal_laporan).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : '-';
            const stBadge = statusBadge[l.status_lhpt] || l.status_lhpt;

            document.getElementById('viewLhptBody').innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-1">${l.nomor_lhpt}</h5>
                        ${stBadge}
                    </div>
                    <div class="text-muted small">Operasi: <strong>${l.operation_name || '-'}</strong> ${l.nomor_sprint ? '('+l.nomor_sprint+')' : ''}</div>
                    <div class="text-muted small">Tanggal: ${tgl}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-bold"><i class="fas fa-align-left me-1"></i>Isi Laporan / Uraian Pelaksanaan</label>
                    <div class="border rounded p-3 bg-light">${(l.isi_laporan || '-').replace(/\n/g,'<br>')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold"><i class="fas fa-trophy me-1"></i>Hasil Pelaksanaan</label>
                        <div class="border rounded p-3 bg-light">${(l.hasil || '-').replace(/\n/g,'<br>')}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Kendala</label>
                        <div class="border rounded p-3 bg-light">${(l.kendala || '-').replace(/\n/g,'<br>')}</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-bold"><i class="fas fa-lightbulb me-1"></i>Rekomendasi</label>
                    <div class="border rounded p-3 bg-light">${(l.rekomendasi || '-').replace(/\n/g,'<br>')}</div>
                </div>
                <div class="text-muted small">
                    Pelapor: <strong>${l.pelapor || '-'}</strong> — ${l.jabatan_pelapor || '-'}
                </div>
            `;
            new bootstrap.Modal(document.getElementById('viewLhptModal')).show();
        })
        .catch(err => showToast('danger', err.message));
}

function cetakLHPT() {
    if (!_currentLhpt) return;
    const l = _currentLhpt;
    const tgl = l.tanggal_laporan ? new Date(l.tanggal_laporan).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : '-';
    const w = window.open('', '_blank');
    w.document.write(`<!DOCTYPE html><html><head><title>${l.nomor_lhpt}</title>
    <style>body{font-family:'Times New Roman',serif;font-size:12pt;padding:40px;max-width:800px;margin:auto;}
    h3{text-align:center;text-decoration:underline;} .section{margin-bottom:15px;} .section-title{font-weight:bold;margin-bottom:5px;}
    table{width:100%;border-collapse:collapse;} td{padding:4px 0;vertical-align:top;} .sign{display:flex;justify-content:space-between;margin-top:40px;}
    @media print{body{padding:20px;}}</style></head><body>
    <div style="text-align:center;border-bottom:3px double #000;padding-bottom:10px;margin-bottom:15px;">
        <div style="font-weight:bold;">KEPOLISIAN NEGARA REPUBLIK INDONESIA</div>
        <div>DAERAH SUMATERA UTARA / RESOR SAMOSIR</div>
    </div>
    <h3>LAPORAN HASIL PELAKSANAAN TUGAS</h3>
    <p style="text-align:center;">Nomor: ${l.nomor_lhpt}</p>
    <table><tr><td style="width:160px">Operasi</td><td>: ${l.operation_name || '-'} ${l.nomor_sprint ? '('+l.nomor_sprint+')' : ''}</td></tr>
    <tr><td>Tanggal Laporan</td><td>: ${tgl}</td></tr></table>
    <hr>
    <div class="section"><div class="section-title">I. URAIAN PELAKSANAAN</div><div>${(l.isi_laporan||'-').replace(/\n/g,'<br>')}</div></div>
    <div class="section"><div class="section-title">II. HASIL PELAKSANAAN</div><div>${(l.hasil||'-').replace(/\n/g,'<br>')}</div></div>
    <div class="section"><div class="section-title">III. KENDALA / HAMBATAN</div><div>${(l.kendala||'-').replace(/\n/g,'<br>')}</div></div>
    <div class="section"><div class="section-title">IV. REKOMENDASI / SARAN</div><div>${(l.rekomendasi||'-').replace(/\n/g,'<br>')}</div></div>
    <div class="sign">
        <div style="text-align:center;"><div>Mengetahui,</div><div style="margin-top:60px;"><strong>KABAG OPS</strong></div></div>
        <div style="text-align:center;"><div>Samosir, ${tgl}</div><div>Pelapor,</div><div style="margin-top:40px;"><strong>${l.pelapor||'.....................'}</strong></div><div>${l.jabatan_pelapor||''}</div></div>
    </div></body></html>`);
    w.document.close();
    setTimeout(() => w.print(), 300);
}

function hapusLHPT(id, nomor) {
    if (!confirm('Hapus LHPT "' + nomor + '"?\nTindakan ini tidak dapat dibatalkan.')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { showToast('success', data.message); loadLHPT(); }
            else showToast('danger', data.message);
        })
        .catch(err => showToast('danger', err.message));
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
