<?php
/**
 * Backup Management Page
 * Manage database backups and restore
 */

session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$pageTitle = 'Manajemen Backup';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-database text-primary me-2"></i>Manajemen Backup
            </h2>
            <p class="text-muted mb-0">Backup dan restore database sistem</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="createBackup()">
                <i class="fas fa-plus me-2"></i>Buat Backup
            </button>
            <button class="btn btn-outline-primary" onclick="runScheduledBackups()">
                <i class="fas fa-clock me-2"></i>Run Scheduled
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total Backup</h6>
                            <h3 class="mb-0" id="totalBackups">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-archive fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Backup Berhasil</h6>
                            <h3 class="mb-0" id="completedBackups">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total Size</h6>
                            <h3 class="mb-0" id="totalSize">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hdd fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Backup Terakhir</h6>
                            <h5 class="mb-0" id="latestBackup">-</h5>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar Backup
                </h5>
                <div class="d-flex gap-2">
                    <select class="form-select" id="filterStatus" style="width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="completed">Berhasil</option>
                        <option value="failed">Gagal</option>
                        <option value="running">Running</option>
                    </select>
                    <button class="btn btn-outline-secondary" onclick="loadBackups()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="backupsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Filename</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="backupsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memuat data backup...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-database me-2"></i>Buat Backup Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createBackupForm">
                    <div class="mb-3">
                        <label class="form-label">Tipe Backup</label>
                        <select class="form-select" name="type">
                            <option value="full">Full Backup (Semua Tabel)</option>
                            <option value="partial">Partial Backup (Tabel Tertentu)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="tablesField" style="display: none;">
                        <label class="form-label">Tabel yang di-backup (pisahkan dengan koma)</label>
                        <input type="text" class="form-control" name="tables" placeholder="personil, bagian, jabatan">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmCreateBackup()">
                    <i class="fas fa-play me-2"></i>Mulai Backup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load backups on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBackups();
    loadStats();
    
    // Show/hide tables field based on type
    document.querySelector('[name="type"]').addEventListener('change', function() {
        document.getElementById('tablesField').style.display = this.value === 'partial' ? 'block' : 'none';
    });
});

// Load backups from API
async function loadBackups() {
    try {
        const response = await fetch('../api/backup_api.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            renderBackupsTable(data.data.backups);
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error loading backups:', error);
        showAlert('danger', 'Gagal memuat data backup');
    }
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('../api/backup_api.php?action=stats');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data.stats;
            document.getElementById('totalBackups').textContent = stats.total;
            document.getElementById('completedBackups').textContent = stats.completed;
            
            // Format size
            const size = formatBytes(stats.total_size);
            document.getElementById('totalSize').textContent = size;
            
            // Latest backup
            if (data.data.latest_backup) {
                const date = new Date(data.data.latest_backup.created_at);
                document.getElementById('latestBackup').textContent = date.toLocaleDateString('id-ID');
            } else {
                document.getElementById('latestBackup').textContent = 'Belum ada';
            }
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Render backups table
function renderBackupsTable(backups) {
    const tbody = document.getElementById('backupsTableBody');
    const filterStatus = document.getElementById('filterStatus').value;
    
    // Filter by status
    if (filterStatus) {
        backups = backups.filter(b => b.status === filterStatus);
    }
    
    if (backups.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p>Belum ada backup yang dibuat</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = backups.map(backup => `
        <tr>
            <td>${backup.id}</td>
            <td>
                <i class="fas fa-file-code text-primary me-2"></i>
                ${escapeHtml(backup.filename)}
            </td>
            <td>
                <span class="badge bg-${backup.backup_type === 'full' ? 'primary' : 'info'}">
                    ${backup.backup_type === 'full' ? 'Full' : 'Partial'}
                </span>
                ${backup.is_auto ? '<span class="badge bg-secondary ms-1">Auto</span>' : ''}
            </td>
            <td>${backup.file_size_formatted}</td>
            <td>
                <span class="badge bg-${getStatusColor(backup.status)}">
                    ${getStatusLabel(backup.status)}
                </span>
            </td>
            <td>${formatDate(backup.created_at)}</td>
            <td>${backup.created_by_name || 'System'}</td>
            <td>
                ${backup.status === 'completed' ? `
                    <button class="btn btn-sm btn-success" onclick="downloadBackup(${backup.id})" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="restoreBackup(${backup.id})" title="Restore">
                        <i class="fas fa-undo"></i>
                    </button>
                ` : ''}
                <button class="btn btn-sm btn-danger" onclick="deleteBackup(${backup.id})" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Create backup
function createBackup() {
    document.getElementById('createBackupForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('createBackupModal'));
    modal.show();
}

async function confirmCreateBackup() {
    const form = document.getElementById('createBackupForm');
    const formData = new FormData(form);
    formData.append('action', 'create');
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('createBackupModal')).hide();
    
    // Show loading
    showAlert('info', 'Sedang membuat backup, mohon tunggu...');
    
    try {
        const response = await fetch('../api/backup_api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', `Backup berhasil dibuat: ${data.data.filename} (${data.data.file_size})`);
            loadBackups();
            loadStats();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error creating backup:', error);
        showAlert('danger', 'Gagal membuat backup');
    }
}

// Download backup
function downloadBackup(backupId) {
    window.open(`../api/backup_api.php?action=download&backup_id=${backupId}`, '_blank');
}

// Restore backup
async function restoreBackup(backupId) {
    if (!confirm('⚠️ PERINGATAN: Restore akan mengganti seluruh data saat ini dengan data dari backup.\n\nApakah Anda yakin ingin melanjutkan?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('backup_id', backupId);
    
    showAlert('info', 'Sedang melakukan restore, mohon tunggu...');
    
    try {
        const response = await fetch('../api/backup_api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error restoring backup:', error);
        showAlert('danger', 'Gagal melakukan restore');
    }
}

// Delete backup
async function deleteBackup(backupId) {
    if (!confirm('Apakah Anda yakin ingin menghapus backup ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('backup_id', backupId);
    
    try {
        const response = await fetch('../api/backup_api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            loadBackups();
            loadStats();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error deleting backup:', error);
        showAlert('danger', 'Gagal menghapus backup');
    }
}

// Run scheduled backups
async function runScheduledBackups() {
    if (!confirm('Jalankan semua scheduled backup yang sudah jatuh tempo?')) {
        return;
    }
    
    showAlert('info', 'Menjalankan scheduled backups...');
    
    try {
        const response = await fetch('../api/backup_api.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'run_scheduled' })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const results = data.data.results;
            const success = results.filter(r => r.success).length;
            showAlert('success', `${success} dari ${results.length} scheduled backup berhasil dijalankan`);
            loadBackups();
            loadStats();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error running scheduled:', error);
        showAlert('danger', 'Gagal menjalankan scheduled backups');
    }
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        'completed': 'success',
        'failed': 'danger',
        'running': 'warning',
        'pending': 'secondary'
    };
    return colors[status] || 'secondary';
}

function getStatusLabel(status) {
    const labels = {
        'completed': 'Berhasil',
        'failed': 'Gagal',
        'running': 'Running',
        'pending': 'Pending'
    };
    return labels[status] || status;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID');
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    let unitIndex = 0;
    
    while (bytes >= 1024 && unitIndex < units.length - 1) {
        bytes /= 1024;
        unitIndex++;
    }
    
    return Math.round(bytes * 100) / 100 + ' ' + units[unitIndex];
}

function showAlert(type, message) {
    alert(message);
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
