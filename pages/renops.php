<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator');

$page_title = 'Renops — Rencana Operasi';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Renops — Rencana Operasi</h4>
            <small class="text-muted">Rencana Operasi sebelum pelaksanaan — Polres Samosir</small>
        </div>
        <button class="btn btn-primary" onclick="openTambahRenops()">
            <i class="fas fa-plus me-1"></i> Tambah Renops
        </button>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" id="filterRenops" placeholder="Cari renops...">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="approved">Disetujui</option>
                        <option value="executed">Dieksekusi</option>
                        <option value="batal">Batal</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadRenops()">
                        <i class="fas fa-search me-1"></i> Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="renopsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Nomor</th>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Operasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" class="text-center py-3">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Renops -->
<div class="modal fade" id="renopsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renopsModalTitle">Tambah Renops</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="renopsForm">
                    <input type="hidden" id="renopsId">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nomor Renops</label>
                            <input type="text" class="form-control" id="nomorRenops" required>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="generateNomor()">
                                <i class="fas fa-magic me-1"></i> Generate
                            </button>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Judul Renops</label>
                            <input type="text" class="form-control" id="judulRenops" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sasaran</label>
                            <textarea class="form-control" id="sasaran" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wilayah</label>
                            <input type="text" class="form-control" id="wilayah">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kekuatan</label>
                            <input type="text" class="form-control" id="kekuatan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Anggaran (Rp)</label>
                            <input type="number" class="form-control" id="anggaran" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tglMulai" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="tglSelesai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="statusRenops">
                                <option value="draft">Draft</option>
                                <option value="approved">Disetujui</option>
                                <option value="executed">Dieksekusi</option>
                                <option value="batal">Batal</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanRenops()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/api/renops_api.php';

function loadRenops() {
    fetch(API + '?action=get_all_renops')
        .then(r => r.json())
        .then(d => {
            const tbody = document.querySelector('#renopsTable tbody');
            if (!d.success || !d.data.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3">Tidak ada data</td></tr>';
                return;
            }
            tbody.innerHTML = d.data.map(r => `
                <tr>
                    <td><code>${r.nomor_renops}</code></td>
                    <td>${r.judul_renops}</td>
                    <td>${r.tanggal_mulai ? r.tanggal_mulai + ' - ' + r.tanggal_selesai : '-'}</td>
                    <td><span class="badge bg-${r.status === 'approved' ? 'success' : r.status === 'executed' ? 'primary' : r.status === 'batal' ? 'danger' : 'secondary'}">${r.status}</span></td>
                    <td>${r.nama_operasi ? '<a href="operasi.php" class="text-decoration-none">' + r.nama_operasi + '</a>' : '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editRenops(${r.id})"><i class="fas fa-edit"></i></button>
                        ${!r.operation_id ? `<button class="btn btn-sm btn-outline-success" onclick="convertToOperation(${r.id})" title="Konversi ke Operasi"><i class="fas fa-exchange-alt"></i></button>` : ''}
                        <button class="btn btn-sm btn-outline-danger" onclick="hapusRenops(${r.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        });
}

function openTambahRenops() {
    document.getElementById('renopsForm').reset();
    document.getElementById('renopsId').value = '';
    document.getElementById('renopsModalTitle').textContent = 'Tambah Renops';
    new bootstrap.Modal(document.getElementById('renopsModal')).show();
}

function editRenops(id) {
    fetch(API + '?action=get_renops_by_id&id=' + id)
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const r = d.data;
            document.getElementById('renopsId').value = r.id;
            document.getElementById('nomorRenops').value = r.nomor_renops;
            document.getElementById('judulRenops').value = r.judul_renops;
            document.getElementById('sasaran').value = r.sasaran || '';
            document.getElementById('wilayah').value = r.wilayah || '';
            document.getElementById('kekuatan').value = r.kekuatan || '';
            document.getElementById('anggaran').value = r.anggaran || '';
            document.getElementById('tglMulai').value = r.tanggal_mulai || '';
            document.getElementById('tglSelesai').value = r.tanggal_selesai || '';
            document.getElementById('statusRenops').value = r.status;
            document.getElementById('renopsModalTitle').textContent = 'Edit Renops';
            new bootstrap.Modal(document.getElementById('renopsModal')).show();
        });
}

function generateNomor() {
    fetch(API + '?action=get_nomor_renops')
        .then(r => r.json())
        .then(d => {
            if (d.success) document.getElementById('nomorRenops').value = d.nomor;
        });
}

function simpanRenops() {
    const fd = new FormData(document.getElementById('renopsForm'));
    fd.append('action', document.getElementById('renopsId').value ? 'update_renops' : 'create_renops');
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('renopsModal')).hide();
                loadRenops();
            } else alert(d.message);
        });
}

function hapusRenops(id) {
    if (!confirm('Hapus renops ini?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_renops');
    fd.append('id', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) loadRenops();
            else alert(d.message);
        });
}

function convertToOperation(id) {
    if (!confirm('Konversi renops ini menjadi operasi?')) return;
    const fd = new FormData();
    fd.append('action', 'convert_to_operation');
    fd.append('renops_id', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('Renops berhasil dikonversi menjadi operasi!');
                loadRenops();
            } else alert(d.message);
        });
}

loadRenops();
</script>
