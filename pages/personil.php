<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

// Setup page header
$page_header = [
    'title' => 'Data Personil',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => BASE_URL . '/pages/main.php'],
        ['text' => 'Data Personil', 'active' => true]
    ]
];

// Get API base URL for JavaScript
$api_base = API_BASE_URL;

// Include Bootstrap layout
include __DIR__ . '/../includes/components/bootstrap_layout.php';
?>

<!-- Additional CSS for personil page -->
<style>
.personil-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    text-align: center;
}

.personil-stats h2 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.search-section {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.unsur-card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 0.75rem;
    overflow: hidden;
    transition: transform 0.3s ease;
    margin-bottom: 1.5rem;
}

.unsur-card:hover {
    transform: translateY(-5px);
}

.unsur-card .card-header {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
    font-weight: 600;
    border: none;
}

.personil-table {
    font-size: 0.875rem;
}

.personil-table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .personil-stats h2 {
        font-size: 2rem;
    }
    
    .personil-table {
        font-size: 0.75rem;
    }
}
</style>

<div class="container">
    <!-- Personil Stats -->
    <div class="personil-stats">
        <h2 id="totalPersonil">256</h2>
        <p class="mb-0">Total Personil POLRES Samosir</p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Cari personil berdasarkan nama, NRP, atau jabatan...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonilModal">
                    <i class="fas fa-plus me-2"></i>Tambah Personil
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data personil...</p>
    </div>

    <!-- Personil Data by Unsur -->
    <div id="personilData">
        <!-- Sample data loaded via JavaScript -->
    </div>
    
    <!-- Sample Personil Table -->
    <div class="mt-4">
        <h5 class="mb-3">Data Personil Aktif</h5>
        <div class="table-responsive">
            <table class="table table-hover" id="personilTable">
                <thead class="table-light">
                    <tr>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Pangkat</th>
                        <th>Jabatan</th>
                        <th>Unsur</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>123456</code></td>
                        <td>Ahmad Wijaya</td>
                        <td>Aiptu</td>
                        <td>Kanit Reskrim</td>
                        <td><span class="badge bg-primary">Reserse</span></td>
                        <td><span class="badge bg-success">Aktif</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(1)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(1)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code>123457</code></td>
                        <td>Budi Santoso</td>
                        <td>Aipda</td>
                        <td>Ba Intel</td>
                        <td><span class="badge bg-info">Intelijen</span></td>
                        <td><span class="badge bg-success">Aktif</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(2)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(2)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code>123458</code></td>
                        <td>Chandra Dewi</td>
                        <td>Bripka</td>
                        <td>Ba Samapta</td>
                        <td><span class="badge bg-warning text-dark">Samapta</span></td>
                        <td><span class="badge bg-success">Aktif</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(3)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(3)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code>123459</code></td>
                        <td>Dedi Kurniawan</td>
                        <td>Bripda</td>
                        <td>Ba Lantas</td>
                        <td><span class="badge bg-secondary">Lalu Lintas</span></td>
                        <td><span class="badge bg-warning text-dark">Cuti</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(4)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(5)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code>123460</code></td>
                        <td>Eka Pratiwi</td>
                        <td>Bripda</td>
                        <td>Ba Sabhara</td>
                        <td><span class="badge bg-success">Sabhara</span></td>
                        <td><span class="badge bg-success">Aktif</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(5)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(5)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <nav aria-label="Personil pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
</div>

<!-- Add Personil Modal -->
<div class="modal fade" id="addPersonilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Personil Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPersonilForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nrp" class="form-label">NRP</label>
                            <input type="text" class="form-control" id="nrp" name="nrp" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_pangkat" class="form-label">Pangkat</label>
                            <select class="form-select" id="id_pangkat" name="id_pangkat" required>
                                <option value="">Pilih Pangkat</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_jabatan" class="form-label">Jabatan</label>
                            <select class="form-select" id="id_jabatan" name="id_jabatan" required>
                                <option value="">Pilih Jabatan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_bagian" class="form-label">Bagian</label>
                            <select class="form-select" id="id_bagian" name="id_bagian">
                                <option value="">Pilih Bagian</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_unsur" class="form-label">Unsur</label>
                            <select class="form-select" id="id_unsur" name="id_unsur" required>
                                <option value="">Pilih Unsur</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="JK" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="JK" name="JK" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status_ket" class="form-label">Status</label>
                            <select class="form-select" id="status_ket" name="status_ket" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non Aktif</option>
                                <option value="BKO">BKO</option>
                                <option value="cuti">Cuti</option>
                                <option value="sakit">Sakit</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="savePersonilBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Personil Modal -->
<div class="modal fade" id="editPersonilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Personil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPersonilForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nrp" class="form-label">NRP</label>
                            <input type="text" class="form-control" id="edit_nrp" name="nrp" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_id_pangkat" class="form-label">Pangkat</label>
                            <select class="form-select" id="edit_id_pangkat" name="id_pangkat" required>
                                <option value="">Pilih Pangkat</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_id_jabatan" class="form-label">Jabatan</label>
                            <select class="form-select" id="edit_id_jabatan" name="id_jabatan" required>
                                <option value="">Pilih Jabatan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_id_bagian" class="form-label">Bagian</label>
                            <select class="form-select" id="edit_id_bagian" name="id_bagian">
                                <option value="">Pilih Bagian</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_id_unsur" class="form-label">Unsur</label>
                            <select class="form-select" id="edit_id_unsur" name="id_unsur" required>
                                <option value="">Pilih Unsur</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_JK" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="edit_JK" name="JK" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status_ket" class="form-label">Status</label>
                            <select class="form-select" id="edit_status_ket" name="status_ket" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non Aktif</option>
                                <option value="BKO">BKO</option>
                                <option value="cuti">Cuti</option>
                                <option value="sakit">Sakit</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="edit_tanggal_lahir" name="tanggal_lahir">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="updatePersonilBtn">Update</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/bootstrap_layout_footer.php'; ?>

<script>
// Personil Management JavaScript
const API_BASE = '<?php echo $api_base; ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadPersonilData();
    loadDropdownData();
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        loadPersonilData(this.value);
    });
    
    document.getElementById('clearSearch').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        loadPersonilData();
    });
    
    // Save personil
    document.getElementById('savePersonilBtn').addEventListener('click', function() {
        savePersonil();
    });
    
    // Update personil
    document.getElementById('updatePersonilBtn').addEventListener('click', function() {
        updatePersonil();
    });
});

function loadPersonilData(search = '') {
    showLoading();
    
    fetch(`${API_BASE}/personil_api.php?action=list&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPersonilByUnsur(data.data);
                updateStats(data.stats);
            } else {
                toastr.error('Gagal memuat data personil');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Terjadi kesalahan saat memuat data');
        })
        .finally(() => {
            hideLoading();
        });
}

function displayPersonilByUnsur(personilData) {
    const container = document.getElementById('personilData');
    container.innerHTML = '';
    
    // Group by unsur
    const groupedByUnsur = {};
    personilData.forEach(personil => {
        if (!groupedByUnsur[personil.nama_unsur]) {
            groupedByUnsur[personil.nama_unsur] = [];
        }
        groupedByUnsur[personil.nama_unsur].push(personil);
    });
    
    // Create cards for each unsur
    Object.keys(groupedByUnsur).forEach(unsurName => {
        const unsurCard = createUnsurCard(unsurName, groupedByUnsur[unsurName]);
        container.appendChild(unsurCard);
    });
}

function createUnsurCard(unsurName, personilList) {
    const card = document.createElement('div');
    card.className = 'unsur-card';
    
    card.innerHTML = `
        <div class="card-header">
            <i class="fas fa-sitemap me-2"></i>${unsurName}
            <span class="badge bg-light text-dark float-end">${personilList.length}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover personil-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NRP</th>
                            <th>Pangkat</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${personilList.map(personil => `
                            <tr>
                                <td>${personil.nama}</td>
                                <td>${personil.nrp}</td>
                                <td>${personil.singkatan || '-'}</td>
                                <td>${personil.nama_jabatan || '-'}</td>
                                <td>
                                    <span class="badge bg-${getStatusColor(personil.status_ket)}">
                                        ${personil.status_ket || 'aktif'}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(${personil.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(${personil.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    return card;
}

function getStatusColor(status) {
    const colors = {
        'aktif': 'success',
        'nonaktif': 'secondary',
        'BKO': 'warning',
        'cuti': 'info',
        'sakit': 'danger'
    };
    return colors[status] || 'secondary';
}

function updateStats(stats) {
    document.getElementById('totalPersonil').textContent = stats.total || 0;
}

function loadDropdownData() {
    fetch(`${API_BASE}/personil_crud.php?action=get_dropdown_data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDropdowns(data.data);
            }
        })
        .catch(error => console.error('Error loading dropdown data:', error));
}

function populateDropdowns(data) {
    // Populate pangkat
    const pangkatSelect = document.getElementById('id_pangkat');
    const editPangkatSelect = document.getElementById('edit_id_pangkat');
    
    data.pangkat.forEach(pangkat => {
        const option = new Option(pangkat.nama_pangkat, pangkat.id);
        pangkatSelect.add(option.cloneNode(true));
        editPangkatSelect.add(option);
    });
    
    // Populate jabatan
    const jabatanSelect = document.getElementById('id_jabatan');
    const editJabatanSelect = document.getElementById('edit_id_jabatan');
    
    data.jabatan.forEach(jabatan => {
        const option = new Option(jabatan.nama_jabatan, jabatan.id);
        jabatanSelect.add(option.cloneNode(true));
        editJabatanSelect.add(option);
    });
    
    // Populate bagian
    const bagianSelect = document.getElementById('id_bagian');
    const editBagianSelect = document.getElementById('edit_id_bagian');
    
    data.bagian.forEach(bagian => {
        const option = new Option(bagian.nama_bagian, bagian.id);
        bagianSelect.add(option.cloneNode(true));
        editBagianSelect.add(option);
    });
    
    // Populate unsur
    const unsurSelect = document.getElementById('id_unsur');
    const editUnsurSelect = document.getElementById('edit_id_unsur');
    
    data.unsur.forEach(unsur => {
        const option = new Option(unsur.nama_unsur, unsur.id);
        unsurSelect.add(option.cloneNode(true));
        editUnsurSelect.add(option);
    });
}

function savePersonil() {
    const form = document.getElementById('addPersonilForm');
    const formData = new FormData(form);
    
    fetch(`${API_BASE}/personil_crud.php?action=create_personil`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Personil berhasil ditambahkan');
            bootstrap.Modal.getInstance(document.getElementById('addPersonilModal')).hide();
            form.reset();
            loadPersonilData();
        } else {
            toastr.error(data.message || 'Gagal menambah personil');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Terjadi kesalahan saat menambah personil');
    });
}

function editPersonil(id) {
    fetch(`${API_BASE}/personil_crud.php?action=get_personil&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const personil = data.data;
                
                // Fill form
                document.getElementById('edit_id').value = personil.id;
                document.getElementById('edit_nama').value = personil.nama;
                document.getElementById('edit_nrp').value = personil.nrp;
                document.getElementById('edit_id_pangkat').value = personil.id_pangkat || '';
                document.getElementById('edit_id_jabatan').value = personil.id_jabatan || '';
                document.getElementById('edit_id_bagian').value = personil.id_bagian || '';
                document.getElementById('edit_id_unsur').value = personil.id_unsur || '';
                document.getElementById('edit_JK').value = personil.JK || 'L';
                document.getElementById('edit_status_ket').value = personil.status_ket || 'aktif';
                document.getElementById('edit_tanggal_lahir').value = personil.tanggal_lahir || '';
                
                // Show modal
                new bootstrap.Modal(document.getElementById('editPersonilModal')).show();
            } else {
                toastr.error('Gagal memuat data personil');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Terjadi kesalahan saat memuat data');
        });
}

function updatePersonil() {
    const form = document.getElementById('editPersonilForm');
    const formData = new FormData(form);
    
    fetch(`${API_BASE}/personil_crud.php?action=update_personil`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Personil berhasil diupdate');
            bootstrap.Modal.getInstance(document.getElementById('editPersonilModal')).hide();
            loadPersonilData();
        } else {
            toastr.error(data.message || 'Gagal mengupdate personil');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Terjadi kesalahan saat mengupdate personil');
    });
}

function deletePersonil(id) {
    if (confirm('Apakah Anda yakin ingin menghapus personil ini?')) {
        const alasan = prompt('Alasan penghapusan:');
        if (alasan) {
            const formData = new FormData();
            formData.append('action', 'delete_personil');
            formData.append('id', id);
            formData.append('alasan', alasan);
            
            fetch(`${API_BASE}/personil_crud.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Personil berhasil dihapus');
                    loadPersonilData();
                } else {
                    toastr.error(data.message || 'Gagal menghapus personil');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Terjadi kesalahan saat menghapus personil');
            });
        }
    }
}

function showLoading() {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('personilData').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loadingIndicator').style.display = 'none';
    document.getElementById('personilData').style.display = 'block';
}
</script>
