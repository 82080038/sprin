<?php
require_once 'includes/header.php';
require_once 'includes/auth_check.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Operasional BAGOPS</h5>
                    <div>
                        <button class="btn btn-info me-2" onclick="showBagOpsStructure()">
                            <i class="fas fa-sitemap"></i> Struktur BAGOPS
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOperasiModal">
                            <i class="fas fa-plus"></i> Buat Operasi Baru
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchOperasi" placeholder="Cari operasi...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="rencana">Rencana</option>
                                <option value="berlangsung">Berlangsung</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterJenis">
                                <option value="">Semua Jenis</option>
                                <option value="rutin">Rutin</option>
                                <option value="khusus">Khusus</option>
                                <option value="terpadu">Terpadu</option>
                                <option value="kamtibmas">Kamtibmas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary" onclick="loadOperasi()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    
                    <!-- Operations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="operasiTable">
                            <thead>
                                <tr>
                                    <th>Kode Operasi</th>
                                    <th>Nama Operasi</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Komandan</th>
                                    <th>Personil</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="operasiTableBody">
                                <!-- Data akan dimuat via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination akan dimuat via JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Operation Modal -->
<div class="modal fade" id="createOperasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Operasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOperasiForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Operasi *</label>
                                <input type="text" class="form-control" name="nama_operasi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Operasi *</label>
                                <select class="form-select" name="jenis_operasi" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="rutin">Rutin</option>
                                    <option value="khusus">Khusus</option>
                                    <option value="terpadu">Terpadu</option>
                                    <option value="kamtibmas">Kamtibmas</option>
                                    <option value="penegakan_hukum">Penegakan Hukum</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai *</label>
                                <input type="datetime-local" class="form-control" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tanggal_selesai">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Operasi *</label>
                                <input type="text" class="form-control" name="lokasi_operasi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Komandan Operasi *</label>
                                <select class="form-select" name="komandan_ops" required>
                                    <option value="">Pilih Komandan</option>
                                    <!-- Load personil via JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Operasi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target dan Sasaran</label>
                        <textarea class="form-control" name="target_sasaran" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cara Bertindak</label>
                        <textarea class="form-control" name="cara_bertindak" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kekuatan yang Dilibatkan</label>
                        <textarea class="form-control" name="kekuatan_dilibatkan" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dukungan Anggaran</label>
                                <input type="number" class="form-control" name="dukungan_anggaran" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wakil Komandan</label>
                                <select class="form-select" name="wakil_komandan">
                                    <option value="">Pilih Wakil Komandan</option>
                                    <!-- Load personil via JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="createOperasi()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Operation Detail Modal -->
<div class="modal fade" id="operasiDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Operasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="operasiDetailContent">
                    <!-- Content akan dimuat via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BAGOPS Structure Modal -->
<div class="modal fade" id="bagopsStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Struktur Organisasi BAGOPS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bagopsStructureContent">
                    <!-- Content akan dimuat via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const limit = 10;

// Load operations on page load
$(document).ready(function() {
    loadPersonilOptions();
    loadOperasi();
});

function loadPersonilOptions() {
    $.ajax({
        url: 'api/bagops_structure_api.php?action=get_personil_by_jabatan',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Pilih Personil</option>';
                response.data.forEach(personil => {
                    options += `<option value="${personil.nrp}">${personil.nama} - ${personil.nama_pangkat}</option>`;
                });
                $('select[name="komandan_ops"]').html(options);
                $('select[name="wakil_komandan"]').html(options);
            }
        }
    });
}

function loadOperasi(page = 1) {
    currentPage = page;
    const search = $('#searchOperasi').val();
    const status = $('#filterStatus').val();
    const jenis = $('#filterJenis').val();
    
    $.ajax({
        url: `api/operasional_api.php?action=get_operasi_list&page=${page}&limit=${limit}&search=${search}&status=${status}&jenis=${jenis}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayOperasiTable(response.data);
                displayPagination(response.pagination);
            }
        }
    });
}

function displayOperasiTable(operasi) {
    let html = '';
    operasi.forEach(op => {
        const statusBadge = getStatusBadge(op.status);
        const jenisBadge = getJenisBadge(op.jenis_operasi);
        
        html += `
            <tr>
                <td>${op.kode_operasi}</td>
                <td>${op.nama_operasi}</td>
                <td>${jenisBadge}</td>
                <td>${statusBadge}</td>
                <td>${formatDateTime(op.tanggal_mulai)}</td>
                <td>${op.nama_komandan || '-'}</td>
                <td>${op.total_personil || 0}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewOperasiDetail(${op.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editOperasi(${op.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="generateSprint(${op.id})">
                        <i class="fas fa-file-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteOperasi(${op.id}, '${op.kode_operasi}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    $('#operasiTableBody').html(html);
}

function getStatusBadge(status) {
    const badges = {
        'rencana': '<span class="badge bg-primary">Rencana</span>',
        'berlangsung': '<span class="badge bg-success">Berlangsung</span>',
        'selesai': '<span class="badge bg-secondary">Selesai</span>',
        'dibatalkan': '<span class="badge bg-danger">Dibatalkan</span>'
    };
    return badges[status] || status;
}

function getJenisBadge(jenis) {
    const badges = {
        'rutin': '<span class="badge bg-info">Rutin</span>',
        'khusus': '<span class="badge bg-warning">Khusus</span>',
        'terpadu': '<span class="badge bg-primary">Terpadu</span>',
        'kamtibmas': '<span class="badge bg-success">Kamtibmas</span>',
        'penegakan_hukum': '<span class="badge bg-danger">Penegakan Hukum</span>'
    };
    return badges[jenis] || jenis;
}

function createOperasi() {
    const formData = $('#createOperasiForm').serialize();
    
    $.ajax({
        url: 'api/operasional_api.php?action=create_operasi',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#createOperasiModal').modal('hide');
                $('#createOperasiForm')[0].reset();
                loadOperasi();
                showAlert('success', response.message);
            } else {
                showAlert('error', response.message);
            }
        }
    });
}

function viewOperasiDetail(operasiId) {
    $.ajax({
        url: `api/operasional_api.php?action=get_operasi_detail&operasi_id=${operasiId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayOperasiDetail(response.data);
                $('#operasiDetailModal').modal('show');
            }
        }
    });
}

function displayOperasiDetail(data) {
    const operasi = data.operasi;
    const personil = data.personil;
    const dokumentasi = data.dokumentasi;
    
    let html = `
        <div class="row">
            <div class="col-md-8">
                <h6>Informasi Operasi</h6>
                <table class="table table-sm">
                    <tr><td><strong>Kode Operasi:</strong></td><td>${operasi.kode_operasi}</td></tr>
                    <tr><td><strong>Nama Operasi:</strong></td><td>${operasi.nama_operasi}</td></tr>
                    <tr><td><strong>Jenis:</strong></td><td>${getJenisBadge(operasi.jenis_operasi)}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${getStatusBadge(operasi.status)}</td></tr>
                    <tr><td><strong>Lokasi:</strong></td><td>${operasi.lokasi_operasi}</td></tr>
                    <tr><td><strong>Tanggal Mulai:</strong></td><td>${formatDateTime(operasi.tanggal_mulai)}</td></tr>
                    <tr><td><strong>Komandan:</strong></td><td>${operasi.nama_komandan || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <h6>Aksi Cepat</h6>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="generateSprint('${operasi.id}')">
                        <i class="fas fa-file-alt"></i> Generate Sprint
                    </button>
                    <button class="btn btn-info" onclick="editOperasi('${operasi.id}')">
                        <i class="fas fa-edit"></i> Edit Operasi
                    </button>
                    <button class="btn btn-success" onclick="addPersonilModal('${operasi.id}')">
                        <i class="fas fa-user-plus"></i> Tambah Personil
                    </button>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h6>Personil Operasi (${personil.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Pangkat</th>
                                <th>Peran</th>
                                <th>Unit Kerja</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    personil.forEach(p => {
        html += `
            <tr>
                <td>${p.nama}</td>
                <td>${p.nama_pangkat}</td>
                <td>${p.peran}</td>
                <td>${p.unit_kerja || '-'}</td>
                <td><span class="badge bg-success">${p.status_kehadiran}</span></td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#operasiDetailContent').html(html);
}

function generateSprint(operasiId) {
    if (confirm('Apakah Anda yakin ingin membuat Sprint untuk operasi ini?')) {
        $.ajax({
            url: 'api/operasional_api.php?action=generate_sprint',
            method: 'POST',
            data: {
                operasi_id: operasiId,
                tipe_sprint: 'tugas'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Sprint berhasil dibuat');
                    // Tampilkan sprint content
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(response.data.sprint_content);
                } else {
                    showAlert('error', response.message);
                }
            }
        });
    }
}

function deleteOperasi(operasiId, kodeOperasi) {
    if (confirm(`Apakah Anda yakin ingin menghapus operasi ${kodeOperasi}?`)) {
        $.ajax({
            url: 'api/operasional_api.php?action=delete_operasi',
            method: 'POST',
            data: { operasi_id: operasiId },
            success: function(response) {
                if (response.success) {
                    loadOperasi();
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            }
        });
    }
}

function showBagOpsStructure() {
    $.ajax({
        url: 'api/bagops_structure_api.php?action=get_structure',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayBagOpsStructure(response.data);
                $('#bagopsStructureModal').modal('show');
            }
        }
    });
}

function displayBagOpsStructure(structures) {
    let html = '<div class="table-responsive"><table class="table table-striped">';
    html += '<thead><tr><th>Jabatan</th><th>Pangkat</th><th>Eselon</th><th>Atasan</th><th>Deskripsi</th></tr></thead><tbody>';
    
    structures.forEach(struct => {
        html += `
            <tr>
                <td><strong>${struct.jabatan}</strong></td>
                <td>${struct.pangkat}</td>
                <td>${struct.eselon || '-'}</td>
                <td>${struct.atasan || '-'}</td>
                <td>${struct.deskripsi || '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#bagopsStructureContent').html(html);
}

function formatDateTime(dateTime) {
    if (!dateTime) return '-';
    const date = new Date(dateTime);
    return date.toLocaleString('id-ID');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alertHtml);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
