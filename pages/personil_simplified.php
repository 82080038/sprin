<?php
// Start output buffering if not already started
if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Start session using SessionManager
SessionManager::start();

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

// Get personil data directly from API
$personil_api_url = API_BASE_URL . '/personil_list.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build API URL with parameters
$api_url = $personil_api_url;
if (!empty($search)) {
    $api_url .= '?search=' . urlencode($search);
}

// Fetch data from API
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

$response = file_get_contents($api_url, false, $context);
$personil_data = json_decode($response, true) ?: [];

// Extract data for server-side rendering
$personil_grouped = $personil_data['data']['personil_grouped'] ?? [];
$statistics = $personil_data['data']['statistics'] ?? [];
$total_personil = $personil_data['data']['total_count'] ?? 0;

$page_title = 'Data Personil - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- Bootstrap Datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

<style>
.container { max-width: 1400px; margin: 0 auto; padding: 15px; font-family: Arial, sans-serif; }
.search-box { max-width: 500px; margin: 15px auto; }
.search-box input { border-radius: 25px; padding: 10px 18px; border: 2px solid #007bff; }
.stats { display: flex; gap: 12px; margin: 15px 0; justify-content: center; flex-wrap: wrap; }
.stat-box { background: #007bff; color: white; padding: 1px; border-radius: 6px; text-align: center; min-width: 120px; max-width: 160px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.stat-box h3 { margin: 0; font-size: 1.8em; font-weight: bold; }
.stat-box p { margin: 4px 0 0 0; font-size: 0.75em; text-transform: uppercase; letter-spacing: 0.5px; }
.unsur-section { margin: 30px 0; border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
.unsur-section h2 { margin: 0 0 20px 0; color: #2c3e50; font-size: 1.5em; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
.bagian-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #007bff; }
.bagian-section h3 { margin: 0 0 15px 0; color: #495057; font-size: 1.2em; }
.personil-table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.personil-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; color: #495057; border-bottom: 2px solid #dee2e6; }
.personil-table td { padding: 10px 12px; border-bottom: 1px solid #e9ecef; }
.personil-table tr:hover { background: #f8f9fa; }
.action-btns { display: flex; gap: 5px; }
.btn-sm { padding: 5px 10px; font-size: 0.875rem; }
.no-data { text-align: center; padding: 50px; color: #666; }
@media (max-width: 768px) {
    .container { padding: 10px; }
    .stats { flex-direction: row; gap: 8px; justify-content: space-around; }
    .stat-box { min-width: 90px; max-width: 110px; padding: 1px; }
    .stat-box h3 { font-size: 1.4em; }
    .stat-box p { font-size: 0.65em; }
    .search-box { margin: 10px auto; }
    .search-box input { padding: 8px 15px; }
}
</style>

<div class="container">
    <h1>DATA PERSONIL POLRES SAMOSIR</h1>
    
    <!-- Search -->
    <div class="search-box">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, NRP, pangkat, jabatan..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
            <button class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i> Cari</button>
            <button class="btn btn-success" id="btnRefresh"><i class="fas fa-sync"></i></button>
            <button class="btn btn-info" id="btnAdd" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah</button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="stats" id="statsContainer">
        <div class="stat-box" onclick="filterByGender('')" style="cursor: pointer;" title="Klik untuk tampilkan semua">
            <h3><?php echo $total_personil; ?></h3>
            <p>TOTAL PERSONIL</p>
        </div>
        <div class="stat-box" onclick="filterByGender('L')" style="background: #28a745; cursor: pointer;" title="Klik untuk filter Laki-laki">
            <h3><?php echo $statistics['by_jk']['L'] ?? 0; ?></h3>
            <p>LAKI-LAKI</p>
        </div>
        <div class="stat-box" onclick="filterByGender('P')" style="background: #dc3545; cursor: pointer;" title="Klik untuk filter Perempuan">
            <h3><?php echo $statistics['by_jk']['P'] ?? 0; ?></h3>
            <p>PEREMPUAN</p>
        </div>
    </div>
    
    <!-- Status Stats -->
    <div class="stats" id="statusStatsContainer" style="margin-top: 10px; position: relative;">
        <div class="stat-box" onclick="filterByStatus('aktif')" style="background: #28a745; cursor: pointer; min-width: 110px; padding: 1px;" title="Klik untuk filter Aktif">
            <h3 style="font-size: 1.6em;"><?php echo $statistics['by_status']['aktif'] ?? 0; ?></h3>
            <p style="font-size: 0.7em;">AKTIF</p>
        </div>
        <div class="stat-box" id="statNonAktif" onclick="filterByStatus('nonaktif')" style="background: #dc3545; cursor: pointer; min-width: 110px; padding: 1px; position: relative;" title="Klik untuk filter Non-Aktif">
            <h3 style="font-size: 1.6em;"><?php echo $statistics['by_status']['nonaktif'] ?? 0; ?></h3>
            <p style="font-size: 0.7em;">NON-AKTIF</p>
        </div>
    </div>
    
    <!-- Pangkat Stats -->
    <div class="stats" id="pangkatStatsContainer" style="margin-top: 10px;">
        <?php if (!empty($statistics['by_pangkat'])): ?>
            <?php foreach ($statistics['by_pangkat'] as $pangkat => $count): ?>
                <div class="stat-box" onclick="filterByPangkat('<?php echo addslashes($pangkat); ?>')" style="cursor: pointer; min-width: 90px; max-width: 120px; padding: 1px;" title="Klik untuk filter <?php echo htmlspecialchars($pangkat); ?>">
                    <h3 style="font-size: 1.4em;"><?php echo $count; ?></h3>
                    <p style="font-size: 0.7em;"><?php echo htmlspecialchars($pangkat); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Server-Side Rendered Personil Content -->
    <div id="personilContent">
        <?php if (empty($personil_grouped)): ?>
            <div class="no-data">
                <i class="fas fa-inbox fa-3x"></i><br>
                <?php if (!empty($search)): ?>
                    Tidak ada hasil pencarian untuk "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    Tidak ada data personil
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($personil_grouped as $unsur): ?>
                <div class="unsur-section">
                    <h2><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($unsur['nama_unsur']); ?></h2>
                    
                    <?php foreach ($unsur['bagian'] as $bagian): ?>
                        <div class="bagian-section">
                            <h3><i class="fas fa-building"></i> <?php echo htmlspecialchars($bagian['nama_bagian']); ?> <span class="badge bg-primary"><?php echo count($bagian['personil']); ?> personil</span></h3>
                            
                            <div class="table-responsive">
                                <table class="personil-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>NRP</th>
                                            <th>Pangkat</th>
                                            <th>Jabatan</th>
                                            <th>Status</th>
                                            <th>Alasan</th>
                                            <th>JK</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bagian['personil'] as $index => $personil): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($personil['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($personil['nrp']); ?></td>
                                                <td><?php echo htmlspecialchars($personil['pangkat_singkatan'] ?? $personil['nama_pangkat'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($personil['nama_jabatan'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $personil['status_ket'] === 'aktif' ? 'bg-success' : 'bg-danger'; ?>" style="cursor: pointer;" onclick="toggleStatus(<?php echo $personil['id']; ?>, '<?php echo $personil['status_ket'] ?? 'aktif'; ?>', this)" title="Klik untuk ubah status">
                                                        <?php echo htmlspecialchars($personil['status_ket'] ?? 'aktif'); ?>
                                                    </span>
                                                    <?php if ($personil['status_ket'] === 'nonaktif' && !empty($personil['alasan_status'])): ?>
                                                        <br><small class="text-muted" style="font-size: 0.7em; line-height: 1;"><?php echo htmlspecialchars($personil['alasan_status']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $personil['JK'] === 'L' ? '<i class="fas fa-male text-primary"></i>' : '<i class="fas fa-female text-danger"></i>'; ?></td>
                                                <td class="action-btns">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(<?php echo $personil['id']; ?>)"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(<?php echo $personil['id']; ?>, '<?php echo addslashes($personil['nama']); ?>')"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Personil Modal -->
<div class="modal fade" id="personilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Personil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="personilForm">
                <div class="modal-body">
                    <input type="hidden" id="personilId" name="id">
                    <input type="hidden" id="formAction" name="action" value="create_personil">
                    
                    <!-- Row 1: Nama dan NRP -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NRP</label>
                            <input type="text" class="form-control" id="nrp" name="nrp" required>
                        </div>
                    </div>
                    
                    <!-- Row 2: Jenis Kelamin dan Tanggal Lahir -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="JK" name="JK">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <select class="form-select" id="tgl_lahir" name="tgl_lahir">
                                        <option value="">Tgl</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select class="form-select" id="bln_lahir" name="bln_lahir">
                                        <option value="">Bln</option>
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="08">Agustus</option>
                                        <option value="09">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select class="form-select" id="thn_lahir" name="thn_lahir">
                                        <option value="">Thn</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="tanggal_lahir" name="tanggal_lahir">
                        </div>
                    </div>
                    
                    <!-- Row 3: Pangkat -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pangkat</label>
                            <select class="form-select" id="id_pangkat" name="id_pangkat">
                                <option value="">-- Pilih Pangkat --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Row 4: Unsur (Parent) -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Unsur</label>
                            <select class="form-select" id="id_unsur" name="id_unsur">
                                <option value="">-- Pilih Unsur --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Row 5: Bagian (Child of Unsur) -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bagian</label>
                            <select class="form-select" id="id_bagian" name="id_bagian" disabled>
                                <option value="">-- Pilih Unsur Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Row 6: Jabatan (Child of Bagian) -->
                    <div class="row">
                        <div class="col-md-10 mb-3">
                            <label class="form-label">Jabatan</label>
                            <select class="form-select" id="id_jabatan" name="id_jabatan" disabled>
                                <option value="">-- Pilih Bagian Terlebih Dahulu --</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="openJabatanModal()" title="Tambah Jabatan Baru">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Jabatan Modal -->
<div class="modal fade" id="addJabatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-tie me-2"></i>
                    Tambah Jabatan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="addJabatanForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_jabatan">
                    
                    <div class="mb-3">
                        <label for="new_nama_jabatan" class="form-label">Nama Jabatan</label>
                        <input type="text" class="form-control" id="new_nama_jabatan" name="nama_jabatan" required>
                        <div class="form-text">
                            Contoh: KASAT RESKRIM, KANIT RESNARKOBA, PS. INTELKAM, dll
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="new_id_unsur" name="id_unsur" required>
                            <option value="">-- Pilih Unsur --</option>
                        </select>
                        <div class="form-text">
                            Pilih unsur organisasi untuk jabatan ini
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Jabatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_BASE = '<?php echo API_BASE_URL; ?>';
let modalInstance = null;

// Global variables to store dropdown data for cascading
let dropdownData = {
    unsur: [],
    bagian: [],
    jabatan: [],
    pangkat: []
};

document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadDropdownData();
    initDateDropdowns();
});

function initDateDropdowns() {
    const today = new Date();
    const maxYear = today.getFullYear() - 17;  // 17 tahun yang lalu
    const minYear = maxYear - 60;  // 60 tahun sebelumnya
    
    // Populate tanggal (1-31)
    const tglSelect = document.getElementById('tgl_lahir');
    for (let i = 1; i <= 31; i++) {
        const option = document.createElement('option');
        option.value = String(i).padStart(2, '0');
        option.textContent = i;
        tglSelect.appendChild(option);
    }
    
    // Populate tahun (minYear to maxYear, descending)
    const thnSelect = document.getElementById('thn_lahir');
    for (let y = maxYear; y >= minYear; y--) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        thnSelect.appendChild(option);
    }
    
    // Combine on change
    function updateHiddenDate() {
        const tgl = document.getElementById('tgl_lahir').value;
        const bln = document.getElementById('bln_lahir').value;
        const thn = document.getElementById('thn_lahir').value;
        if (tgl && bln && thn) {
            document.getElementById('tanggal_lahir').value = `${tgl}/${bln}/${thn}`;
        }
    }
    
    document.getElementById('tgl_lahir').addEventListener('change', updateHiddenDate);
    document.getElementById('bln_lahir').addEventListener('change', updateHiddenDate);
    document.getElementById('thn_lahir').addEventListener('change', updateHiddenDate);
}

function setupEventListeners() {
    $('#btnSearch').click(function() {
        const searchValue = $('#searchInput').val();
        // Redirect with search parameter for server-side rendering
        window.location.href = '?search=' + encodeURIComponent(searchValue);
    });
    
    $('#searchInput').keypress(function(e) {
        if (e.which === 13) {
            $('#btnSearch').click();
        }
    });
    
    $('#btnRefresh').click(function() {
        window.location.href = '?';
    });
    
    $('#personilForm').submit(function(e) {
        e.preventDefault();
        savePersonil();
    });
}

function loadDropdownData() {
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: { action: 'get_dropdown_data' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Store all data for cascading
                dropdownData.pangkat = response.data.pangkat;
                dropdownData.unsur = response.data.unsur;
                dropdownData.bagian = response.data.bagian;
                dropdownData.jabatan = response.data.jabatan;
                
                // Populate static dropdowns
                populateSelect('id_pangkat', dropdownData.pangkat, 'singkatan');
                populateSelect('id_unsur', dropdownData.unsur, 'nama_unsur');
                
                // Setup cascading handlers
                setupCascadingHandlers();
            }
        }
    });
}

function setupCascadingHandlers() {
    // When Unsur changes → filter Bagian
    $('#id_unsur').on('change', function() {
        const unsurId = $(this).val();
        const bagianSelect = $('#id_bagian');
        const jabatanSelect = $('#id_jabatan');
        
        // Reset bagian dan jabatan
        bagianSelect.html('<option value="">-- Pilih Bagian --</option>');
        jabatanSelect.html('<option value="">-- Pilih Bagian Terlebih Dahulu --</option>').prop('disabled', true);
        
        if (unsurId) {
            // Filter bagian by unsur_id
            const filteredBagian = dropdownData.bagian.filter(b => b.id_unsur == unsurId);
            populateSelect('id_bagian', filteredBagian, 'nama_bagian');
            bagianSelect.prop('disabled', false);
        } else {
            bagianSelect.prop('disabled', true);
            bagianSelect.html('<option value="">-- Pilih Unsur Terlebih Dahulu --</option>');
        }
    });
    
    // When Bagian changes → filter Jabatan
    $('#id_bagian').on('change', function() {
        const bagianId = $(this).val();
        const unsurId = $('#id_unsur').val();
        const jabatanSelect = $('#id_jabatan');
        
        // Reset jabatan
        jabatanSelect.html('<option value="">-- Pilih Jabatan --</option>');
        
        if (bagianId && unsurId) {
            // Get selected bagian name
            const selectedBagian = dropdownData.bagian.find(b => b.id == bagianId);
            const bagianName = selectedBagian ? selectedBagian.nama_bagian : '';
            
            // Filter jabatan by id_unsur AND bagian name matching
            const filteredJabatan = dropdownData.jabatan.filter(j => {
                return j.id_unsur == unsurId && (
                    j.nama_jabatan.toLowerCase().includes(bagianName.toLowerCase()) ||
                    j.nama_jabatan.toLowerCase().includes('bag ops') && bagianName.toLowerCase().includes('ops') ||
                    j.nama_jabatan.toLowerCase().includes('bag ren') && bagianName.toLowerCase().includes('ren') ||
                    j.nama_jabatan.toLowerCase().includes('bag sdm') && bagianName.toLowerCase().includes('sdm') ||
                    j.nama_jabatan.toLowerCase().includes('bag log') && bagianName.toLowerCase().includes('log') ||
                    j.nama_jabatan.toLowerCase().includes('intelkam') && bagianName.toLowerCase().includes('intelkam') ||
                    j.nama_jabatan.toLowerCase().includes('reskrim') && bagianName.toLowerCase().includes('reskrim') ||
                    j.nama_jabatan.toLowerCase().includes('resnarkoba') && bagianName.toLowerCase().includes('resnarkoba') ||
                    j.nama_jabatan.toLowerCase().includes('lantas') && bagianName.toLowerCase().includes('lantas') ||
                    j.nama_jabatan.toLowerCase().includes('samapta') && bagianName.toLowerCase().includes('samapta') ||
                    j.nama_jabatan.toLowerCase().includes('pamobvit') && bagianName.toLowerCase().includes('pamobvit') ||
                    j.nama_jabatan.toLowerCase().includes('polairud') && bagianName.toLowerCase().includes('polairud') ||
                    j.nama_jabatan.toLowerCase().includes('tahti') && bagianName.toLowerCase().includes('tahti') ||
                    j.nama_jabatan.toLowerCase().includes('binmas') && bagianName.toLowerCase().includes('binmas') ||
                    j.nama_jabatan.toLowerCase().includes('spkt') && bagianName.toLowerCase().includes('spkt') ||
                    j.nama_jabatan.toLowerCase().includes('sium') && bagianName.toLowerCase().includes('sium') ||
                    j.nama_jabatan.toLowerCase().includes('sikeu') && bagianName.toLowerCase().includes('sikeu') ||
                    j.nama_jabatan.toLowerCase().includes('sidokkes') && bagianName.toLowerCase().includes('sidokkes') ||
                    j.nama_jabatan.toLowerCase().includes('siwas') && bagianName.toLowerCase().includes('siwas') ||
                    j.nama_jabatan.toLowerCase().includes('sitik') && bagianName.toLowerCase().includes('sitik') ||
                    j.nama_jabatan.toLowerCase().includes('sikum') && bagianName.toLowerCase().includes('sikum') ||
                    j.nama_jabatan.toLowerCase().includes('sipropam') && bagianName.toLowerCase().includes('sipropam') ||
                    j.nama_jabatan.toLowerCase().includes('sihumas') && bagianName.toLowerCase().includes('sihumas') ||
                    j.nama_jabatan.toLowerCase().includes('polsek') && bagianName.toLowerCase().includes('polsek') ||
                    // General matches for common patterns
                    (j.nama_jabatan.toLowerCase().includes('kasat') || j.nama_jabatan.toLowerCase().includes('kanit') || j.nama_jabatan.toLowerCase().includes('ps.')) && 
                    (bagianName.toLowerCase().includes('sat') || bagianName.toLowerCase().includes('polsek') || bagianName.toLowerCase().includes('spkt'))
                );
            });
            
            populateSelect('id_jabatan', filteredJabatan, 'nama_jabatan');
            jabatanSelect.prop('disabled', false);
        } else {
            jabatanSelect.prop('disabled', true);
            jabatanSelect.html('<option value="">-- Pilih Bagian Terlebih Dahulu --</option>');
        }
    });
}

function populateSelect(id, data, textField) {
    const select = $('#' + id);
    select.find('option:not(:first)').remove();
    data.forEach(function(item) {
        select.append('<option value="' + item.id + '">' + escapeHtml(item[textField]) + '</option>');
    });
}

function openAddModal() {
    $('#modalTitle').text('Tambah Personil');
    $('#formAction').val('create_personil');
    $('#personilId').val('');
    $('#personilForm')[0].reset();
    modalInstance = new bootstrap.Modal(document.getElementById('personilModal'));
    modalInstance.show();
}

function editPersonil(id) {
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: { action: 'get_personil', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const p = response.data;
                $('#modalTitle').text('Edit Personil');
                $('#formAction').val('update_personil');
                $('#personilId').val(p.id);
                $('#nama').val(p.nama);
                $('#nrp').val(p.nrp);
                $('#JK').val(p.JK);
                setDateFromString(p.tanggal_lahir);
                $('#id_pangkat').val(p.id_pangkat);
                
                // Set unsur dan trigger cascading
                $('#id_unsur').val(p.id_unsur).trigger('change');
                
                // Set bagian dan trigger cascading
                setTimeout(() => {
                    $('#id_bagian').val(p.id_bagian).trigger('change');
                    
                    // Set jabatan
                    setTimeout(() => {
                        $('#id_jabatan').val(p.id_jabatan);
                    }, 100);
                }, 100);
                
                modalInstance = new bootstrap.Modal(document.getElementById('personilModal'));
                modalInstance.show();
            } else {
                showError('Gagal memuat data personil');
            }
        },
        error: function() {
            showError('Error mengambil data personil');
        }
    });
}

function savePersonil() {
    const formData = new FormData($('#personilForm')[0]);
    
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                modalInstance.hide();
                // Refresh page to show updated data
                window.location.reload();
            } else {
                showError('Gagal menyimpan: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            showError('Error menyimpan data');
        }
    });
}

function deletePersonil(id, name) {
    Swal.fire({
        title: 'Hapus Personil?',
        html: 'Apakah Anda yakin ingin menghapus <strong>' + name + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_BASE + '/personil_crud.php',
                method: 'POST',
                data: { action: 'delete_personil', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Terhapus!', 'Personil berhasil dihapus', 'success');
                        window.location.reload();
                    } else {
                        showError('Gagal menghapus: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function() {
                    showError('Error menghapus data');
                }
            });
        }
    });
}

function toggleStatus(id, currentStatus, element) {
    const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
    
    if (newStatus === 'nonaktif') {
        // Ask for reason when deactivating
        Swal.fire({
            title: 'Nonaktifkan Personil?',
            text: 'Silakan masukkan alasan nonaktif:',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Alasan nonaktif...',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan harus diisi!';
                }
                return null;
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Nonaktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(id, newStatus, result.value, element);
            }
        });
    } else {
        // Reactivating - no reason needed
        Swal.fire({
            title: 'Aktifkan Personil?',
            text: 'Personil akan diaktifkan kembali.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Aktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(id, newStatus, '', element);
            }
        });
    }
}

function updateStatus(id, status, alasan, element) {
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: { 
            action: 'update_status',
            id: id,
            status_ket: status,
            alasan_status: alasan
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Refresh page to show updated status
                window.location.reload();
            } else {
                showError('Gagal mengubah status: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            showError('Error mengubah status');
        }
    });
}

function filterByGender(gender) {
    // This would require implementing server-side filtering
    console.log('Filter by gender:', gender);
}

function filterByStatus(status) {
    // This would require implementing server-side filtering
    console.log('Filter by status:', status);
}

function filterByPangkat(pangkat) {
    // This would require implementing server-side filtering
    console.log('Filter by pangkat:', pangkat);
}

function openJabatanModal() {
    // Populate unsur dropdown for jabatan modal
    const unsurSelect = $('#new_id_unsur');
    unsurSelect.find('option:not(:first)').remove();
    dropdownData.unsur.forEach(function(unsur) {
        unsurSelect.append('<option value="' + unsur.id + '">' + escapeHtml(unsur.nama_unsur) + '</option>');
    });
    
    const modal = new bootstrap.Modal(document.getElementById('addJabatanModal'));
    modal.show();
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showError(message) {
    toastr.error(message, 'Error', {
        timeOut: 5000,
        positionClass: 'toast-top-center'
    });
}

function showSuccess(message) {
    toastr.success(message, 'Success', {
        timeOut: 3000,
        positionClass: 'toast-top-center'
    });
}

// Helper to set date from dd/mm/yyyy format
function setDateFromString(dateStr) {
    if (!dateStr) return;
    const parts = dateStr.split('/');
    if (parts.length === 3) {
        document.getElementById('tgl_lahir').value = parts[0];
        document.getElementById('bln_lahir').value = parts[1];
        document.getElementById('thn_lahir').value = parts[2];
        document.getElementById('tanggal_lahir').value = dateStr;
    }
}
</script>
