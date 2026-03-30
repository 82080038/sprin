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

$page_title = 'Data Personil - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Get API base URL for JavaScript
$api_base = API_BASE_URL;
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
/* Force Font Awesome 5 and suppress Font Awesome 6 */
@font-face {
    font-family: 'Font Awesome 5 Free';
    font-display: block;
    font-weight: 900;
    font-style: normal;
}

@font-face {
    font-family: 'Font Awesome 5 Brands';
    font-display: block;
    font-weight: 400;
    font-style: normal;
}

@font-face {
    font-family: 'Font Awesome 5 Free';
    font-display: block;
    font-weight: 400;
    font-style: normal;
}

/* Override Font Awesome 6 */
.fa, .fas, .far, .fab, .fal {
    font-family: 'Font Awesome 5 Free' !important;
    font-weight: 900 !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.fab {
    font-family: 'Font Awesome 5 Brands' !important;
    font-weight: 400 !important;
}

.far {
    font-weight: 400 !important;
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Bootstrap Datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

<style>
.container { max-width: 1400px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
h1 { color: #2c3e50; margin-bottom: 10px; font-size: 2em; text-align: center; }
.search-box { max-width: 500px; margin: 20px auto; }
.search-box input { border-radius: 25px; padding: 12px 20px; border: 2px solid #007bff; }
.stats { display: flex; gap: 20px; margin: 30px 0; justify-content: center; flex-wrap: wrap; }
.stat-box { background: #007bff; color: white; padding: 25px; border-radius: 10px; text-align: center; min-width: 180px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
.stat-box h3 { margin: 0; font-size: 2.5em; font-weight: bold; }
.stat-box p { margin: 8px 0 0 0; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; }
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
.loading { text-align: center; padding: 50px; font-size: 1.2em; color: #666; }
.no-data { text-align: center; padding: 50px; color: #666; }
@media (max-width: 768px) {
    .container { padding: 15px; }
    .stats { flex-direction: column; align-items: center; }
    .stat-box { width: 100%; max-width: 300px; }
}
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>DATA PERSONIL POLRES SAMOSIR</h1>
        <div>
            <a href="jabatan.php" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-user-tie me-1"></i> Manajemen Jabatan
            </a>
            <a href="bagian.php" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-building me-1"></i> Manajemen Bagian
            </a>
            <a href="unsur.php" class="btn btn-outline-info btn-sm">
                <i class="fas fa-sitemap me-1"></i> Manajemen Unsur
            </a>
        </div>
    </div>
    
    <!-- Search -->
    <div class="search-box">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, NRP, pangkat, jabatan..." autocomplete="off">
            <button class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i> Cari</button>
            <button class="btn btn-success" id="btnRefresh"><i class="fas fa-sync"></i></button>
            <button class="btn btn-info" id="btnAdd" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah</button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="stats" id="statsContainer">
        <div class="stat-box" onclick="filterByGender('')" style="cursor: pointer;" title="Klik untuk tampilkan semua">
            <h3 id="totalPersonil">-</h3>
            <p>TOTAL PERSONIL</p>
        </div>
        <div class="stat-box" onclick="filterByGender('L')" style="background: #28a745; cursor: pointer;" title="Klik untuk filter Laki-laki">
            <h3 id="totalLaki">-</h3>
            <p>LAKI-LAKI</p>
        </div>
        <div class="stat-box" onclick="filterByGender('P')" style="background: #dc3545; cursor: pointer;" title="Klik untuk filter Perempuan">
            <h3 id="totalPerempuan">-</h3>
            <p>PEREMPUAN</p>
        </div>
    </div>
    
    <!-- Status Stats -->
    <div class="stats" id="statusStatsContainer" style="margin-top: 10px; position: relative;">
        <div class="stat-box" onclick="filterByStatus('aktif')" style="background: #28a745; cursor: pointer; min-width: 150px;" title="Klik untuk filter Aktif">
            <h3 id="totalAktif" style="font-size: 2em;">-</h3>
            <p style="font-size: 0.85em;">AKTIF</p>
        </div>
        <div class="stat-box" id="statNonAktif" onclick="filterByStatus('nonaktif')" style="background: #dc3545; cursor: pointer; min-width: 150px; position: relative;" title="Klik untuk filter Non-Aktif">
            <h3 id="totalNonAktif" style="font-size: 2em;">-</h3>
            <p style="font-size: 0.85em;">NON-AKTIF</p>
            
            <!-- Tooltip Alasan Nonaktif -->
            <div id="alasanNonaktifTooltip" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 10px; padding: 15px; background: #f8d7da; border-radius: 8px; border: 1px solid #f5c6cb; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 250px; max-width: 350px; z-index: 1000; display: none;">
                <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 8px solid #f8d7da;"></div>
                <h6 style="color: #721c24; margin: 0 0 10px 0; font-size: 0.9em;"><i class="fas fa-info-circle"></i> Alasan Non-Aktif:</h6>
                <div id="alasanNonaktifList" style="color: #721c24; font-size: 0.85em;"></div>
            </div>
        </div>
    </div>
    
    <!-- Pangkat Stats -->
    <div class="stats" id="pangkatStatsContainer" style="margin-top: 10px;">
        <!-- Pangkat stat boxes will be inserted here -->
    </div>
    
    <!-- Loading -->
    <div id="loadingIndicator" class="loading">
        <i class="fas fa-spinner fa-spin"></i> Memuat data personil...
    </div>
    
    <!-- Content -->
    <div id="personilContent"></div>
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
const API_BASE = '<?php echo $api_base; ?>';
let personilData = [];
let modalInstance = null;

// Global variables to store dropdown data for cascading
let dropdownData = {
    unsur: [],
    bagian: [],
    jabatan: [],
    pangkat: []
};

let currentFilter = { gender: '', pangkat: '', status: '' };

document.addEventListener('DOMContentLoaded', function() {
    loadPersonil();
    setupEventListeners();
    loadDropdownData();
    
    // Initialize custom date dropdowns
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

function setupEventListeners() {
    $('#btnSearch').click(function() {
        loadPersonil($('#searchInput').val());
    });
    
    $('#searchInput').keypress(function(e) {
        if (e.which === 13) {
            loadPersonil($(this).val());
        }
    });
    
    $('#btnRefresh').click(function() {
        $('#searchInput').val('');
        loadPersonil();
    });
    
    $('#personilForm').submit(function(e) {
        e.preventDefault();
        savePersonil();
    });
    
    // Tooltip handlers for NON-AKTIF stat box
    const $statNonAktif = $('#statNonAktif');
    const $tooltip = $('#alasanNonaktifTooltip');
    let tooltipTimeout;
    
    // Show on mouseenter
    $statNonAktif.on('mouseenter', function() {
        clearTimeout(tooltipTimeout);
        // Only show if there are nonaktif personil
        const nonaktifCount = parseInt($('#totalNonAktif').text()) || 0;
        if (nonaktifCount > 0) {
            $tooltip.fadeIn(200);
        }
    });
    
    // Hide on mouseleave
    $statNonAktif.on('mouseleave', function() {
        tooltipTimeout = setTimeout(function() {
            $tooltip.fadeOut(200);
        }, 300);
    });
    
    // Keep visible when hovering tooltip itself
    $tooltip.on('mouseenter', function() {
        clearTimeout(tooltipTimeout);
    });
    
    $tooltip.on('mouseleave', function() {
        $tooltip.fadeOut(200);
    });
    
    // Toggle on click
    $statNonAktif.on('click', function(e) {
        // Don't prevent default - let filterByStatus run
        const nonaktifCount = parseInt($('#totalNonAktif').text()) || 0;
        if (nonaktifCount > 0) {
            if ($tooltip.is(':visible')) {
                $tooltip.hide();
            } else {
                $tooltip.show();
            }
        }
    });
}

function loadPersonil(search = '') {
    $('#loadingIndicator').show();
    $('#personilContent').html('');
    
    let url = API_BASE + '/personil_list.php';
    if (search) {
        url += '?search=' + encodeURIComponent(search);
    }
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#loadingIndicator').hide();
            
            if (response.success) {
                personilData = response.data.personil_grouped;
                updateStats(response.data.statistics);
                updateAlasanNonaktif(); // Update tooltip dengan data lengkap
                renderPersonil(personilData);
                
                // Scroll to content if search has results
                if (search && Object.keys(personilData).length > 0) {
                    document.getElementById('personilContent').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                showError('Gagal memuat data: ' + (response.error?.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            $('#loadingIndicator').hide();
            showError('Error loading data: ' + error);
        }
    });
}

function updateStats(stats) {
    $('#totalPersonil').text(stats.total || 0);
    $('#totalLaki').text(stats.by_jk?.L || 0);
    $('#totalPerempuan').text(stats.by_jk?.P || 0);
    
    // Update status stats
    const aktifCount = stats.by_status?.aktif || 0;
    const nonAktifCount = stats.by_status?.nonaktif || 0;
    $('#totalAktif').text(aktifCount);
    $('#totalNonAktif').text(nonAktifCount);
    
    // Update pangkat stats
    let pangkatHtml = '';
    if (stats.by_pangkat && Object.keys(stats.by_pangkat).length > 0) {
        Object.entries(stats.by_pangkat).forEach(([pangkat, count]) => {
            const isActive = currentFilter.pangkat === pangkat;
            const activeClass = isActive ? 'border: 3px solid #ffd700;' : '';
            pangkatHtml += '<div class="stat-box" onclick="filterByPangkat(\'' + pangkat.replace(/'/g, "\\'") + '\')" style="cursor: pointer; min-width: 120px; padding: 15px; ' + activeClass + '" title="Klik untuk filter ' + pangkat + '">';
            pangkatHtml += '<h3 style="font-size: 1.8em;">' + count + '</h3>';
            pangkatHtml += '<p style="font-size: 0.8em;">' + pangkat + '</p>';
            pangkatHtml += '</div>';
        });
    }
    $('#pangkatStatsContainer').html(pangkatHtml);
}

function renderPersonil(data) {
    let html = '';
    
    if (Object.keys(data).length === 0) {
        $('#personilContent').html('<div class="no-data"><i class="fas fa-inbox fa-3x"></i><br>Tidak ada data personil</div>');
        return;
    }
    
    Object.values(data).forEach(function(unsur) {
        html += '<div class="unsur-section">';
        html += '<h2><i class="fas fa-layer-group"></i> ' + escapeHtml(unsur.nama_unsur) + '</h2>';
        
        Object.values(unsur.bagian).forEach(function(bagian) {
            html += '<div class="bagian-section">';
            html += '<h3><i class="fas fa-building"></i> ' + escapeHtml(bagian.nama_bagian) + ' <span class="badge bg-primary">' + bagian.personil.length + ' personil</span></h3>';
            
            html += '<div class="table-responsive">';
            html += '<table class="personil-table">';
            html += '<thead><tr><th>No</th><th>Nama</th><th>NRP</th><th>Pangkat</th><th>Jabatan</th><th>Status</th><th>Alasan</th><th>JK</th><th>Aksi</th></tr></thead>';
            html += '<tbody>';
            
            bagian.personil.forEach(function(p, index) {
                html += '<tr>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td>' + escapeHtml(p.nama) + '</td>';
                html += '<td>' + escapeHtml(p.nrp) + '</td>';
                html += '<td>' + escapeHtml(p.pangkat_singkatan || p.nama_pangkat || '-') + '</td>';
                html += '<td>' + escapeHtml(p.nama_jabatan || '-') + '</td>';
                html += '<td>';
                html += '<span class="badge ' + (p.status_ket === 'aktif' ? 'bg-success' : 'bg-danger') + '" style="cursor: pointer;" onclick="toggleStatus(' + p.id + ', \'' + (p.status_ket || 'aktif') + '\', this)" title="Klik untuk ubah status">' + escapeHtml(p.status_ket || 'aktif') + '</span>';
                if (p.status_ket === 'nonaktif' && p.alasan_status) {
                    html += '<br><small class="text-muted" style="font-size: 0.7em; line-height: 1;">' + escapeHtml(p.alasan_status) + '</small>';
                }
                html += '</td>';
                html += '<td>' + (p.JK === 'L' ? '<i class="fas fa-male text-primary"></i>' : '<i class="fas fa-female text-danger"></i>') + '</td>';
                html += '<td class="action-btns">';
                html += '<button class="btn btn-sm btn-outline-primary" onclick="editPersonil(' + p.id + ')"><i class="fas fa-edit"></i></button>';
                html += '<button class="btn btn-sm btn-outline-danger" onclick="deletePersonil(' + p.id + ', \'' + escapeHtml(p.nama).replace(/'/g, "\\'") + '\')"><i class="fas fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
    });
    
    $('#personilContent').html(html);
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
                
                // Set bagian dan trigger cascading (after a small delay to ensure bagian is populated)
                setTimeout(function() {
                    $('#id_bagian').val(p.id_bagian).trigger('change');
                    
                    // Set jabatan (after a small delay to ensure jabatan is populated)
                    setTimeout(function() {
                        $('#id_jabatan').val(p.id_jabatan);
                    }, 100);
                }, 100);
                
                modalInstance = new bootstrap.Modal(document.getElementById('personilModal'));
                modalInstance.show();
            }
        }
    });
}

function savePersonil() {
    const formData = $('#personilForm').serialize();
    
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                modalInstance.hide();
                loadPersonil();
            } else {
                toastr.error(response.message || 'Gagal menyimpan data');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error: ' + error);
        }
    });
}

function deletePersonil(id, nama) {
    Swal.fire({
        title: 'Hapus Personil?',
        text: 'Anda yakin ingin menghapus "' + nama + '"?\n\nData akan disembunyikan dan tidak dihitung dalam statistik.',
        icon: 'warning',
        input: 'text',
        inputLabel: 'Alasan Penghapusan (wajib):',
        inputPlaceholder: 'Contoh: Pensiun, Pindah Tugas, Meninggal, Dipecat...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return 'Alasan wajib diisi!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const alasan = result.value;
            $.ajax({
                url: API_BASE + '/personil_crud.php',
                method: 'POST',
                data: { 
                    action: 'delete_personil', 
                    id: id,
                    alasan: alasan
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadPersonil();
                    } else {
                        toastr.error(response.message || 'Gagal menghapus');
                    }
                }
            });
        }
    });
}

function filterByGender(gender) {
    currentFilter.gender = gender;
    // If clearing gender filter (showing total), also clear other filters
    if (!gender) {
        currentFilter.pangkat = '';
        currentFilter.status = '';
    }
    applyFilters();
}

function filterByPangkat(pangkat) {
    currentFilter.pangkat = currentFilter.pangkat === pangkat ? '' : pangkat;
    applyFilters();
}

function applyFilters() {
    let filtered = JSON.parse(JSON.stringify(personilData));
    
    if (currentFilter.gender) {
        Object.values(filtered).forEach(unsur => {
            Object.values(unsur.bagian).forEach(bagian => {
                bagian.personil = bagian.personil.filter(p => p.JK === currentFilter.gender);
            });
            Object.keys(unsur.bagian).forEach(key => {
                if (unsur.bagian[key].personil.length === 0) delete unsur.bagian[key];
            });
        });
        Object.keys(filtered).forEach(key => {
            if (Object.keys(filtered[key].bagian).length === 0) delete filtered[key];
        });
    }
    
    if (currentFilter.status) {
        Object.values(filtered).forEach(unsur => {
            Object.values(unsur.bagian).forEach(bagian => {
                bagian.personil = bagian.personil.filter(p => (p.status_ket || 'aktif') === currentFilter.status);
            });
            Object.keys(unsur.bagian).forEach(key => {
                if (unsur.bagian[key].personil.length === 0) delete unsur.bagian[key];
            });
        });
        Object.keys(filtered).forEach(key => {
            if (Object.keys(filtered[key].bagian).length === 0) delete filtered[key];
        });
    }
    
    if (currentFilter.pangkat) {
        Object.values(filtered).forEach(unsur => {
            Object.values(unsur.bagian).forEach(bagian => {
                bagian.personil = bagian.personil.filter(p => {
                    const pPangkat = (p.pangkat_singkatan || p.nama_pangkat || 'TANPA PANGKAT');
                    return pPangkat === currentFilter.pangkat;
                });
            });
            Object.keys(unsur.bagian).forEach(key => {
                if (unsur.bagian[key].personil.length === 0) delete unsur.bagian[key];
            });
        });
        Object.keys(filtered).forEach(key => {
            if (Object.keys(filtered[key].bagian).length === 0) delete filtered[key];
        });
    }
    
    renderPersonil(filtered);
    
    // Scroll to content if there are results
    const totalPersonil = Object.values(filtered).reduce((sum, unsur) => {
        return sum + Object.values(unsur.bagian).reduce((bagSum, bag) => bagSum + bag.personil.length, 0);
    }, 0);
    
    if (totalPersonil > 0) {
        document.getElementById('personilContent').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    $('#pangkatStatsContainer .stat-box').css('border', 'none');
    if (currentFilter.pangkat) {
        $('#pangkatStatsContainer .stat-box').each(function() {
            if ($(this).find('p').text() === currentFilter.pangkat) {
                $(this).css('border', '3px solid #ffd700');
            }
        });
    }
}

function filterByStatus(status) {
    currentFilter.status = currentFilter.status === status ? '' : status;
    applyFilters();
}

function toggleStatus(id, currentStatus, element) {
    const newStatus = (currentStatus === 'aktif') ? 'nonaktif' : 'aktif';
    
    // If changing to nonaktif, show prompt for alasan
    if (newStatus === 'nonaktif') {
        Swal.fire({
            title: 'Nonaktifkan Personil?',
            text: 'Masukkan alasan menonaktifkan personil ini:',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Contoh: Pensiun, Mutasi, Cuti Panjang...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Nonaktifkan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Alasan wajib diisi!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const alasan = result.value;
                executeToggleStatus(id, currentStatus, newStatus, element, alasan);
            }
        });
    } else {
        // Reactivating - no alasan needed
        executeToggleStatus(id, currentStatus, newStatus, element, null);
    }
}

function executeToggleStatus(id, currentStatus, newStatus, element, alasan) {
    const data = { 
        action: 'toggle_status', 
        id: id,
        current_status: currentStatus
    };
    
    if (alasan) {
        data.alasan = alasan;
    }
    
    $.ajax({
        url: API_BASE + '/personil_crud.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                // Update badge visually
                $(element)
                    .removeClass('bg-success bg-danger')
                    .addClass(newStatus === 'aktif' ? 'bg-success' : 'bg-danger')
                    .text(newStatus)
                    .attr('onclick', 'toggleStatus(' + id + ', \'' + newStatus + '\', this)');
                // Reload data and stats
                loadPersonil();
            } else {
                toastr.error(response.message || 'Gagal mengubah status');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error: ' + error);
        }
    });
}

function updateAlasanNonaktif() {
    // Collect all alasan from nonaktif personil using full data (personilData)
    const alasanCount = {};
    let totalNonaktif = 0;
    
    // Always use personilData (full data from API) to ensure tooltip shows all alasan
    Object.values(personilData).forEach(function(unsur) {
        Object.values(unsur.bagian).forEach(function(bagian) {
            bagian.personil.forEach(function(p) {
                if (p.status_ket === 'nonaktif') {
                    totalNonaktif++;
                    const alasan = p.alasan_status || 'Tidak ada alasan';
                    alasanCount[alasan] = (alasanCount[alasan] || 0) + 1;
                }
            });
        });
    });
    
    // Update tooltip content
    const list = document.getElementById('alasanNonaktifList');
    
    if (totalNonaktif === 0) {
        list.innerHTML = '<p style="margin: 0; color: #721c24;">Tidak ada personil non-aktif</p>';
        return;
    }
    
    // Build alasan list HTML
    let html = '<ul style="margin: 0; padding-left: 20px; color: #721c24;">';
    Object.entries(alasanCount).forEach(([alasan, count]) => {
        html += '<li><strong>' + escapeHtml(alasan) + '</strong>: ' + count + ' personil</li>';
    });
    html += '</ul>';
    
    list.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    toastr.error(message);
}

// Jabatan Modal Functions
function openJabatanModal() {
    // Get current selected unsur
    const selectedUnsurId = $('#id_unsur').val();
    
    // Populate unsur dropdown in jabatan modal
    const unsurSelect = $('#new_id_unsur');
    unsurSelect.empty();
    unsurSelect.append('<option value="">-- Pilih Unsur --</option>');
    
    dropdownData.unsur.forEach(function(unsur) {
        const selected = unsur.id == selectedUnsurId ? 'selected' : '';
        unsurSelect.append(`<option value="${unsur.id}" ${selected}>${escapeHtml(unsur.nama_unsur)}</option>`);
    });
    
    // Reset form
    $('#addJabatanForm')[0].reset();
    if (selectedUnsurId) {
        $('#new_id_unsur').val(selectedUnsurId);
    }
    
    // Show modal
    const jabatanModal = new bootstrap.Modal(document.getElementById('addJabatanModal'));
    jabatanModal.show();
}

function saveNewJabatan() {
    const formData = $('#addJabatanForm').serialize();
    
    $.ajax({
        url: API_BASE + '/jabatan_crud.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('addJabatanModal')).hide();
                
                // Reload dropdown data
                loadDropdownData();
                
                // If we have selected unsur and bagian, refresh jabatan dropdown
                const unsurId = $('#id_unsur').val();
                const bagianId = $('#id_bagian').val();
                if (unsurId && bagianId) {
                    setTimeout(function() {
                        $('#id_bagian').trigger('change');
                    }, 500);
                }
            } else {
                toastr.error(response.message || 'Gagal menyimpan jabatan');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error: ' + error);
        }
    });
}

// Form submission for new jabatan
$('#addJabatanForm').submit(function(e) {
    e.preventDefault();
    saveNewJabatan();
});
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
