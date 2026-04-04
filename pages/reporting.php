<?php
declare(strict_types=1);
/**
 * Reporting Module Page
 * Generate and export various reports
 */

session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$pageTitle = 'Laporan & Statistik';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-bar text-primary me-2"></i>Laporan & Statistik
            </h2>
            <p class="text-muted mb-0">Generate laporan dan analisis data personil</p>
        </div>
    </div>

    <!-- Report Types -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-users text-primary me-2"></i>Ringkasan Personil
                    </h5>
                    <p class="card-text text-muted">Laporan ringkasan personil berdasarkan unsur dan bagian.</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="generateReport('personil_summary')">
                            <i class="fas fa-eye me-2"></i>Lihat
                        </button>
                        <button class="btn btn-outline-success" onclick="exportReport('personil_summary', 'csv')">
                            <i class="fas fa-download me-2"></i>CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie text-info me-2"></i>Demografi
                    </h5>
                    <p class="card-text text-muted">Analisis demografi berdasarkan usia, jenis kelamin, dan pendidikan.</p>
                    <button class="btn btn-info text-white" onclick="generateReport('demographic')">
                        <i class="fas fa-eye me-2"></i>Lihat Demografi
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-sitemap text-success me-2"></i>Struktur Organisasi
                    </h5>
                    <p class="card-text text-muted">Laporan personil berdasarkan struktur organisasi.</p>
                    <button class="btn btn-success" onclick="generateReport('organizational')">
                        <i class="fas fa-eye me-2"></i>Lihat Struktur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="card shadow-sm" id="reportContainer" style="display: none;">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="reportTitle">Laporan</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="exportCurrentReport('json')">
                        <i class="fas fa-code me-2"></i>JSON
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="exportCurrentReport('csv')">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="exportCurrentReport('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body" id="reportContent">
            <!-- Report content will be loaded here -->
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3">Statistik Cepat</h5>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Personil</h6>
                    <h2 class="mb-0" id="statTotal">-</h2>
                    <small>Seluruh instansi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">POLRI</h6>
                    <h2 class="mb-0" id="statPolri">-</h2>
                    <small>Personil POLRI</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">ASN</h6>
                    <h2 class="mb-0" id="statAsn">-</h2>
                    <small>Aparatur Sipil Negara</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">P3K</h6>
                    <h2 class="mb-0" id="statP3k">-</h2>
                    <small>Pegawai P3K</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentReport = null;
let currentReportType = null;

// Load quick stats on page load
document.addEventListener('DOMContentLoaded', function() {
    loadQuickStats();
});

// Load quick statistics
async function loadQuickStats() {
    try {
        const response = await fetch('../api/unsur_stats.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data.statistics;
            document.getElementById('statTotal').textContent = stats.total_personil;
            document.getElementById('statPolri').textContent = stats.polri_count;
            document.getElementById('statAsn').textContent = stats.asn_count;
            document.getElementById('statP3k').textContent = stats.p3k_count || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Generate report
async function generateReport(type) {
    currentReportType = type;
    
    const titles = {
        'personil_summary': 'Ringkasan Personil',
        'demographic': 'Laporan Demografi',
        'organizational': 'Struktur Organisasi'
    };
    
    document.getElementById('reportTitle').textContent = titles[type] || 'Laporan';
    document.getElementById('reportContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat laporan...</p></div>';
    document.getElementById('reportContainer').style.display = 'block';
    
    try {
        const response = await fetch(`../api/report_api.php?action=${type}`);
        const data = await response.json();
        
        if (data.success) {
            currentReport = data.data;
            renderReport(type, data.data);
        } else {
            document.getElementById('reportContent').innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    } catch (error) {
        console.error('Error generating report:', error);
        document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Gagal memuat laporan</div>';
    }
}

// Render report based on type
function renderReport(type, data) {
    let html = '';
    
    switch (type) {
        case 'personil_summary':
            html = renderPersonilSummary(data);
            break;
        case 'demographic':
            html = renderDemographicReport(data);
            break;
        case 'organizational':
            html = renderOrganizationalReport(data);
            break;
        default:
            html = '<div class="alert alert-info">Pilih jenis laporan</div>';
    }
    
    document.getElementById('reportContent').innerHTML = html;
}

// Render personil summary
function renderPersonilSummary(data) {
    const summary = data.summary;
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Total Personil: ${summary.total_personil}</h6>
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-primary" style="width: ${(summary.type_distribution.polri / summary.total_personil * 100).toFixed(1)}%">
                        POLRI: ${summary.type_distribution.polri}
                    </div>
                </div>
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-info" style="width: ${(summary.type_distribution.asn / summary.total_personil * 100).toFixed(1)}%">
                        ASN: ${summary.type_distribution.asn}
                    </div>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-warning" style="width: ${(summary.type_distribution.p3k / summary.total_personil * 100).toFixed(1)}%">
                        P3K: ${summary.type_distribution.p3k}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Gender Distribution</h6>
                <p>Laki-laki: ${summary.gender_distribution.male} | Perempuan: ${summary.gender_distribution.female}</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Unsur</th>
                        <th>Bagian</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">POLRI</th>
                        <th class="text-end">ASN</th>
                        <th class="text-end">P3K</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.by_bagian.forEach(row => {
        html += `
            <tr>
                <td>${escapeHtml(row.unsur || '-')}</td>
                <td>${escapeHtml(row.bagian || '-')}</td>
                <td class="text-end"><strong>${row.total}</strong></td>
                <td class="text-end">${row.polri}</td>
                <td class="text-end">${row.asn}</td>
                <td class="text-end">${row.p3k}</td>
            </tr>
        `;
    });
    
    html += `</tbody></table></div>`;
    return html;
}

// Render demographic report
function renderDemographicReport(data) {
    let html = '<div class="row">';
    
    // Age distribution
    html += `
        <div class="col-md-6">
            <h6 class="mb-3">Distribusi Usia</h6>
            <table class="table table-sm">
                <thead><tr><th>Kelompok Usia</th><th class="text-end">Jumlah</th><th class="text-end">POLRI</th><th class="text-end">ASN</th></tr></thead>
                <tbody>
    `;
    
    data.age_distribution.forEach(row => {
        html += `
            <tr>
                <td>${row.age_group}</td>
                <td class="text-end">${row.count}</td>
                <td class="text-end">${row.polri}</td>
                <td class="text-end">${row.asn}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    
    // Education distribution
    html += `
        <div class="col-md-6">
            <h6 class="mb-3">Tingkat Pendidikan</h6>
            <table class="table table-sm">
                <thead><tr><th>Pendidikan</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
    `;
    
    data.education_distribution.forEach(row => {
        html += `
            <tr>
                <td>${escapeHtml(row.education)}</td>
                <td class="text-end">${row.count}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div></div>';
    return html;
}

// Render organizational report
function renderOrganizationalReport(data) {
    let html = `
        <p class="text-muted">Total Records: ${data.total_records}</p>
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NRP/NIP</th>
                        <th>Unsur</th>
                        <th>Bagian</th>
                        <th>Jabatan</th>
                        <th>Pangkat</th>
                        <th>Jenis</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.personil.forEach(row => {
        html += `
            <tr>
                <td>${escapeHtml(row.nama)}</td>
                <td>${row.nrp || row.nip || '-'}</td>
                <td>${escapeHtml(row.nama_unsur || '-')}</td>
                <td>${escapeHtml(row.nama_bagian || '-')}</td>
                <td>${escapeHtml(row.nama_jabatan || '-')}</td>
                <td>${escapeHtml(row.nama_pangkat || '-')}</td>
                <td>${escapeHtml(row.jenis_pegawai || '-')}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    return html;
}

// Export report
function exportReport(type, format) {
    if (format === 'csv') {
        window.open(`../api/report_api.php?action=export&type=${type}&format=csv`, '_blank');
    } else {
        window.open(`../api/report_api.php?action=${type}`, '_blank');
    }
}

// Export current report
function exportCurrentReport(format) {
    if (!currentReportType) {
        alert('Pilih laporan terlebih dahulu');
        return;
    }
    
    if (format === 'csv') {
        window.open(`../api/report_api.php?action=export&type=${currentReportType}&format=csv`, '_blank');
    } else if (format === 'json') {
        window.open(`../api/report_api.php?action=${currentReportType}`, '_blank');
    } else {
        alert('Export PDF akan segera tersedia');
    }
}

// Helper function
function escapeHtml(text) {
    if (!text) return '-';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
