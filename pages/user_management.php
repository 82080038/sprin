<?php
/**
 * User Management Page
 * Manage system users, roles, and permissions
 */

session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

// Check admin role (only admin can access)
// For now, allow all logged in users (role checking will be added after migration)

$pageTitle = 'Manajemen User';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-users-cog text-primary me-2"></i>Manajemen User
            </h2>
            <p class="text-muted mb-0">Kelola pengguna sistem, role, dan hak akses</p>
        </div>
        <button class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fas fa-plus me-2"></i>Tambah User
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total User</h6>
                            <h3 class="mb-0" id="totalUsers">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-50"></i>
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
                            <h6 class="mb-0">User Aktif</h6>
                            <h3 class="mb-0" id="activeUsers">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x opacity-50"></i>
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
                            <h6 class="mb-0">Administrator</h6>
                            <h3 class="mb-0" id="adminUsers">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x opacity-50"></i>
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
                            <h6 class="mb-0">Login Hari Ini</h6>
                            <h3 class="mb-0" id="todayLogins">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sign-in-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar User
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" id="searchUser" placeholder="Cari user..." style="width: 250px;">
                    <select class="form-select" id="filterRole" style="width: 150px;">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memuat data user...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required minlength="3">
                        <div class="form-text">Minimal 3 karakter, unik</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Pilih Role</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Pilih Role</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Non-aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                        <div class="form-text">Minimal 6 karakter jika diisi</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">
                    <i class="fas fa-save me-2"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Ubah Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="savePassword()">
                    <i class="fas fa-save me-2"></i>Ubah Password
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadRoles();
});

// Load users from API
async function loadUsers() {
    try {
        const response = await fetch('../api/user_management.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            renderUsersTable(data.data.users);
            updateStats(data.data.users);
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showAlert('danger', 'Gagal memuat data user');
    }
}

// Render users table
function renderUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p>Belum ada user yang terdaftar</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td><strong>${escapeHtml(user.username)}</strong></td>
            <td>${escapeHtml(user.full_name)}</td>
            <td>${user.email ? escapeHtml(user.email) : '-'}</td>
            <td>
                <span class="badge bg-${getRoleBadgeColor(user.role)}">
                    ${getRoleLabel(user.role)}
                </span>
            </td>
            <td>
                <span class="badge bg-${user.is_active ? 'success' : 'danger'}">
                    ${user.is_active ? 'Aktif' : 'Non-aktif'}
                </span>
            </td>
            <td>${user.last_login ? formatDate(user.last_login) : '-'}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="openEditUserModal(${user.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id}, '${escapeHtml(user.username)}')" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Update statistics
function updateStats(users) {
    const active = users.filter(u => u.is_active).length;
    const admins = users.filter(u => u.role === 'admin').length;
    
    document.getElementById('totalUsers').textContent = users.length;
    document.getElementById('activeUsers').textContent = active;
    document.getElementById('adminUsers').textContent = admins;
    document.getElementById('todayLogins').textContent = '-'; // Would need separate query
}

// Load roles for dropdown
async function loadRoles() {
    try {
        const response = await fetch('../api/user_management.php?action=get_roles');
        const data = await response.json();
        
        if (data.success) {
            const roleOptions = data.data.roles.map(role => 
                `<option value="${role.value}">${role.label} - ${role.description}</option>`
            ).join('');
            
            document.querySelectorAll('select[name="role"]').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Pilih Role</option>' + roleOptions;
                select.value = currentValue;
            });
        }
    } catch (error) {
        console.error('Error loading roles:', error);
    }
}

// Open add user modal
function openAddUserModal() {
    document.getElementById('addUserForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}

// Save new user
async function saveUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    formData.append('action', 'create');
    
    try {
        const response = await fetch('../api/user_management.php', {
            credentials: 'same-origin',
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            loadUsers();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error saving user:', error);
        showAlert('danger', 'Gagal menyimpan user');
    }
}

// Open edit user modal
async function openEditUserModal(userId) {
    try {
        const response = await fetch(`../api/user_management.php?action=get&id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            const user = data.data.user;
            const form = document.getElementById('editUserForm');
            
            form.querySelector('[name="id"]').value = user.id;
            form.querySelector('[name="username"]').value = user.username;
            form.querySelector('[name="full_name"]').value = user.full_name;
            form.querySelector('[name="email"]').value = user.email || '';
            form.querySelector('[name="role"]').value = user.role;
            form.querySelector('[name="is_active"]').value = user.is_active;
            form.querySelector('[name="password"]').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error loading user:', error);
        showAlert('danger', 'Gagal memuat data user');
    }
}

// Update user
async function updateUser() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    formData.append('action', 'update');
    
    try {
        const response = await fetch('../api/user_management.php', {
            credentials: 'same-origin',
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            loadUsers();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error updating user:', error);
        showAlert('danger', 'Gagal mengupdate user');
    }
}

// Delete user
async function deleteUser(userId, username) {
    if (!confirm(`Apakah Anda yakin ingin menonaktifkan user "${username}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', userId);
    
    try {
        const response = await fetch('../api/user_management.php', {
            credentials: 'same-origin',
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            loadUsers();
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showAlert('danger', 'Gagal menonaktifkan user');
    }
}

// Helper functions
function getRoleBadgeColor(role) {
    const colors = {
        'admin': 'danger',
        'operator': 'warning',
        'viewer': 'info'
    };
    return colors[role] || 'secondary';
}

function getRoleLabel(role) {
    const labels = {
        'admin': 'Administrator',
        'operator': 'Operator',
        'viewer': 'Viewer'
    };
    return labels[role] || role;
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

function showAlert(type, message) {
    // Simple alert - in production, use a toast notification system
    alert(message);
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
