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

$page_title = 'Manajemen Unsur - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Initialize database connection
require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();
?>

<script>
if (window.top !== window.self) {
    window.top.location = window.self.location;
}
</script>

<style>
/* Sortable Styles */
.sortable-item {
    cursor: move;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 8px;
    background: white;
}

.sortable-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
}

.drag-handle {
    cursor: grab;
    color: #6c757d;
    font-size: 18px;
    padding: 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.drag-handle:hover {
    background: #f8f9fa;
    color: #007bff;
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.4;
    background: #e3f2fd !important;
    border: 2px dashed #007bff !important;
}

.sortable-chosen {
    background: #f8f9fa !important;
    border-color: #007bff !important;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2) !important;
    transform: scale(1.02);
}

.sortable-dragging {
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    z-index: 1000;
}

/* Animation for order update */
.sortable-item.order-updated {
    animation: highlightOrder 0.5s ease;
}

@keyframes highlightOrder {
    0% { background: #d4edda; }
    100% { background: white; }
}
</style>

<?php
// Handle AJAX operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Check authentication for all AJAX requests
    if (!AuthHelper::validateSession()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized - Please login to access this resource',
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    // Clear any output buffers for AJAX requests
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    if ($action === 'get_unsur_list') {
        $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
        $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $unsurData]);
        exit;
    }
    
    if ($action === 'get_unsur_detail') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM unsur WHERE id = ?");
        $stmt->execute([$id]);
        $unsur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get current pimpinan from unsur_pimpinan table
        if ($unsur) {
            $pimpinanStmt = $pdo->prepare("
                SELECT p.nama 
                FROM unsur_pimpinan up 
                JOIN personil p ON up.personil_id = p.id 
                WHERE up.unsur_id = ? AND up.tanggal_selesai IS NULL 
                LIMIT 1
            ");
            $pimpinanStmt->execute([$id]);
            $unsur['kepala'] = $pimpinanStmt->fetchColumn();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $unsur]);
        exit;
    }
    
    // All other CRUD operations are now handled by the API
    // Redirect to API for all other actions
    exit;
}

// Get current unsur data only (simple and clean)
try {
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unsurData = [];
}

// Ensure $unsurData is always an array
if ($unsurData === false) {
    $unsurData = [];
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-sitemap me-2"></i>Manajemen Unsur</h1>
        <p class="text-muted text-center">Atur urutan dan kelola data unsur organisasi POLRES Samosir</p>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mb-4">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Tambah Unsur
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
        <button class="btn btn-success" id="saveOrderBtn" onclick="saveOrder()">
            <i class="fas fa-save me-2"></i>Simpan Urutan
        </button>
        <button class="btn btn-warning" id="cancelOrderBtn" onclick="cancelOrder()" style="display: none;">
            <i class="fas fa-times me-2"></i>Batal Perubahan
        </button>
    </div>

    <!-- Sortable Unsur Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i>Urutan Unsur Organisasi
            <small class="text-muted ms-2">(Drag & drop untuk mengatur urutan)</small>
        </div>
        <div class="card-body">
            <!-- Table Header -->
            <div class="row mb-3 text-muted">
                <div class="col-md-5"><strong>Nama Unsur</strong></div>
                <div class="col-md-3"><strong>Kode</strong></div>
                <div class="col-md-4"><strong>Aksi</strong></div>
            </div>
            
            <div id="sortable-container" class="sortable-list">
                <?php foreach ($unsurData as $unsur): ?>
                <div class="sortable-item" data-id="<?php echo $unsur['id']; ?>" data-urutan="<?php echo $unsur['urutan']; ?>">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <strong><?php echo htmlspecialchars($unsur['nama_unsur']); ?></strong>
                                    <br>
                                    <small class="text-muted">Urutan: <?php echo $unsur['urutan']; ?></small>
                                </div>
                                <div class="col-md-3">
                                    <code><?php echo htmlspecialchars($unsur['kode_unsur']); ?></code>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editUnsur(<?php echo $unsur['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUnsur(<?php echo $unsur['id']; ?>, '<?php echo htmlspecialchars($unsur['nama_unsur']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    </div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="unsurModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sitemap me-2"></i>
                    <span id="modalTitle">Tambah Unsur</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="unsurForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_unsur">
                    <input type="hidden" name="id" id="formId">
                    <input type="hidden" name="kode_unsur" id="kode_unsur">
                    
                    <div class="mb-3">
                        <label for="nama_unsur" class="form-label">Nama Unsur</label>
                        <input type="text" class="form-control" id="nama_unsur" name="nama_unsur" required onchange="generateKodeUnsur()">
                        <div class="form-text">
                            Contoh: UNSUR PIMPINAN, UNSUR PEMBANTU PIMPINAN
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Urutan Otomatis:</strong> Unsur akan ditambahkan di urutan paling bawah dan dapat diatur menggunakan drag & drop.
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        <div class="form-text">
                            Deskripsi atau penjelasan singkat tentang unsur (opsional)
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/components/footer.php'; ?>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let unsurData = <?php echo json_encode($unsurData); ?>;
let originalOrder = [...unsurData]; // Store original order for cancel functionality

// Initialize Sortable
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('sortable-container');
    
    new Sortable(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-dragging',
        handle: '.drag-handle',
        onEnd: function(evt) {
            updateOrderNumbers();
            showSaveButton();
        }
    });
});

function updateOrderNumbers() {
    const items = document.querySelectorAll('.sortable-item');
    items.forEach((item, index) => {
        item.dataset.urutan = index + 1;
        const urutanDisplay = item.querySelector('.col-md-5 small');
        if (urutanDisplay) {
            urutanDisplay.textContent = `Urutan: ${index + 1}`;
        }
        
        // Add visual feedback
        item.classList.add('order-updated');
        setTimeout(() => {
            item.classList.remove('order-updated');
        }, 500);
    });
    
    // Show save button
    showSaveButton();
}

function showSaveButton() {
    const saveBtn = document.getElementById('saveOrderBtn');
    const cancelBtn = document.getElementById('cancelOrderBtn');
    
    if (saveBtn) {
        saveBtn.classList.remove('btn-success');
        saveBtn.classList.add('btn-warning');
        saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Simpan Perubahan';
    }
    
    if (cancelBtn) {
        cancelBtn.style.display = 'inline-block';
    }
}

function cancelOrder() {
    // Restore original order
    restoreOriginalOrder();
    
    // Reset buttons to initial state
    resetButtons();
}

function restoreOriginalOrder() {
    const container = document.getElementById('sortable-container');
    
    // Clear current items
    container.innerHTML = '';
    
    // Rebuild items in original order
    originalOrder.forEach((unsur, index) => {
        const itemHtml = `
            <div class="sortable-item" data-id="${unsur.id}" data-urutan="${unsur.urutan}">
                <div class="d-flex align-items-center">
                    <div class="drag-handle me-3">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <strong>${unsur.nama_unsur}</strong>
                                <br>
                                <small class="text-muted">Urutan: ${unsur.urutan}</small>
                            </div>
                            <div class="col-md-3">
                                <code>${unsur.kode_unsur}</code>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editUnsur(${unsur.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUnsur(${unsur.id}, '${unsur.nama_unsur}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
    });
    
    // Update unsurData to match original order
    unsurData = [...originalOrder];
}

function resetButtons() {
    const saveBtn = document.getElementById('saveOrderBtn');
    const cancelBtn = document.getElementById('cancelOrderBtn');
    
    if (saveBtn) {
        saveBtn.classList.remove('btn-warning');
        saveBtn.classList.add('btn-success');
        saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Urutan';
    }
    
    if (cancelBtn) {
        cancelBtn.style.display = 'none';
    }
}

function saveOrder() {
    const items = document.querySelectorAll('.sortable-item');
    const orders = [];
    
    items.forEach((item, index) => {
        orders.push({
            id: item.dataset.id,
            urutan: index + 1
        });
    });
    
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    fetch('../api/unsur_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({
            action: 'update_order',
            orders: JSON.stringify(orders),
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            
            // Update original order to reflect saved changes
            const container = document.getElementById('sortable-container');
            const items = container.querySelectorAll('.sortable-item');
            originalOrder = [];
            items.forEach((item, index) => {
                originalOrder.push({
                    id: item.dataset.id,
                    urutan: index + 1,
                    nama_unsur: item.querySelector('.col-md-5 strong').textContent,
                    kode_unsur: item.querySelector('.col-md-3 code').textContent
                });
            });
            
            // Reset buttons to initial state
            resetButtons();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Terjadi kesalahan saat menyimpan urutan');
    });
}

function generateKodeUnsur() {
    const namaUnsur = document.getElementById('nama_unsur').value;
    const kodeUnsur = namaUnsur.toUpperCase()
        .replace(/[^A-Z0-9_]/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');
    
    document.getElementById('kode_unsur').value = kodeUnsur;
}

function openAddModal() {
    try {
        // Clear form fields first
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const formId = document.getElementById('formId');
        const namaUnsur = document.getElementById('nama_unsur');
        const deskripsi = document.getElementById('deskripsi');
        const kodeUnsur = document.getElementById('kode_unsur');
        
        if (!modalTitle || !formAction || !formId || !namaUnsur || !deskripsi || !kodeUnsur) {
            console.error('Modal form elements not found');
            alert('Error: Form elements not found');
            return;
        }
        
        modalTitle.textContent = 'Tambah Unsur';
        formAction.value = 'create_unsur';
        formId.value = '';
        namaUnsur.value = '';
        deskripsi.value = '';
        kodeUnsur.value = '';
        
        // Show modal with proper handling
        const modalElement = document.getElementById('unsurModal');
        if (!modalElement) {
            console.error('Modal element not found');
            alert('Error: Modal not found');
            return;
        }
        
        // Clean up any existing modal instance
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } catch (error) {
        console.error('Error opening add modal:', error);
        alert('Error: Failed to open modal - ' + error.message);
    }
}

function editUnsur(id) {
    try {
        // Get unsur data from database using new API
        fetch('../api/unsur_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'get_unsur_detail',
                id: id
            })
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const unsur = data.data;
                
                // Get form elements safely
                const modalTitle = document.getElementById('modalTitle');
                const formAction = document.getElementById('formAction');
                const formId = document.getElementById('formId');
                const namaUnsur = document.getElementById('nama_unsur');
                const deskripsi = document.getElementById('deskripsi');
                const kodeUnsur = document.getElementById('kode_unsur');
                
                if (!modalTitle || !formAction || !formId || !namaUnsur || !deskripsi || !kodeUnsur) {
                    console.error('Modal form elements not found');
                    alert('Error: Form elements not found');
                    return;
                }
                
                // Fill form fields
                modalTitle.textContent = 'Edit Unsur';
                formAction.value = 'update_unsur';
                formId.value = unsur.id;
                namaUnsur.value = unsur.nama_unsur;
                deskripsi.value = unsur.deskripsi || '';
                kodeUnsur.value = unsur.kode_unsur;
                
                // Show modal with proper handling
                const modalElement = document.getElementById('unsurModal');
                if (!modalElement) {
                    console.error('Modal element not found');
                    alert('Error: Modal not found');
                    return;
                }
                
                // Clean up any existing modal instance
                const existingModal = bootstrap.Modal.getInstance(modalElement);
                if (existingModal) {
                    existingModal.dispose();
                }
                
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Error in response:', data);
                alert('Error: Unsur tidak ditemukan');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error: ' + error.message);
        });
    } catch (error) {
        console.error('Error in editUnsur:', error);
        alert('Error: Failed to edit unsur - ' + error.message);
    }
}

function deleteUnsur(id, nama) {
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    fetch('../api/unsur_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({
            action: 'delete_unsur',
            id: id,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Unsur deleted successfully
            if (data.message) {
                alert(data.message);
            }
            location.reload();
        } else {
            // Unsur has bagian, show detailed error and options
            if (data.details) {
                const details = data.details;
                let message = data.message + '\n\n';
                
                if (details.bagian_list && details.bagian_list.length > 0) {
                    message += 'Bagian terkait:\n';
                    details.bagian_list.forEach((bagian, index) => {
                        message += `${index + 1}. ${bagian}\n`;
                    });
                    
                    if (details.bagian_count > details.bagian_list.length) {
                        message += `... dan ${details.bagian_count - details.bagian_list.length} lainnya\n`;
                    }
                }
                
                message += '\n' + details.suggestion;
                
                // Show options
                const userChoice = confirm(message + '\n\nKlik OK untuk mencoba lagi, atau Cancel untuk batal.');
                if (userChoice) {
                    // User wants to proceed with force delete options
                    showForceDeleteOptions(id, details);
                }
            } else {
                alert(data.message || 'Gagal menghapus unsur');
            }
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error: ' + error.message);
    });
}

function showForceDeleteOptions(unsurId, details) {
    // Get all other unsur options for reassigning (read-only, no CSRF needed)
    fetch('../api/unsur_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_all_unsur'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const otherUnsurs = data.data.filter(u => u.id != unsurId);
            
            if (otherUnsurs.length === 0) {
                // No other unsur to reassign to
                if (confirm(`Tidak ada unsur lain untuk dipindahkan. Apakah Anda yakin ingin menghapus unsur "${details.unsur_name}" beserta semua ${details.bagian_count} bagian terkait?`)) {
                    forceDeleteUnsur(unsurId, null);
                }
            } else {
                // Create a simple choice dialog
                const options = otherUnsurs.map((unsur, index) => 
                    `${index + 1}. Pindahkan ke: ${unsur.nama_unsur}`
                ).join('\n');
                
                const choice = prompt(
                    `Pilih opsi untuk menghapus unsur "${details.unsur_name}":\n\n` +
                    options + '\n' +
                    `\n${otherUnsurs.length + 1}. Hapus beserta semua bagian\n` +
                    `\nMasukkan nomor pilihan (1-${otherUnsurs.length + 1}):`
                );
                
                if (choice) {
                    const choiceNum = parseInt(choice);
                    
                    if (choiceNum >= 1 && choiceNum <= otherUnsurs.length) {
                        // Reassign to selected unsur
                        const selectedUnsur = otherUnsurs[choiceNum - 1];
                        if (confirm(`Pindahkan ${details.bagian_count} bagian ke "${selectedUnsur.nama_unsur}" dan hapus unsur "${details.unsur_name}"?`)) {
                            forceDeleteUnsur(unsurId, selectedUnsur.id);
                        }
                    } else if (choiceNum === otherUnsurs.length + 1) {
                        // Delete with bagians
                        if (confirm(`Hapus unsur "${details.unsur_name}" beserta semua ${details.bagian_count} bagian terkait?`)) {
                            forceDeleteUnsur(unsurId, null);
                        }
                    } else {
                        alert('Pilihan tidak valid');
                    }
                }
            }
        } else {
            alert('Gagal mengambil data unsur');
        }
    })
    .catch(error => {
        console.error('Get unsur list error:', error);
        alert('Error: ' + error.message);
    });
}

function forceDeleteUnsur(unsurId, reassignToUnsurId) {
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    const formData = new FormData();
    formData.append('action', 'force_delete_unsur');
    formData.append('id', unsurId);
    formData.append('csrf_token', csrfToken);
    
    if (reassignToUnsurId) {
        formData.append('reassign_to_unsur_id', reassignToUnsurId);
    }
    
    fetch('../api/unsur_api.php', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Gagal menghapus unsur: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Force delete error:', error);
        alert('Error: ' + error.message);
    });
}

function refreshData() {
    window.location.reload();
}

// Form submission
document.getElementById('unsurForm').addEventListener('submit', function(e) {
    try {
        // Check if event exists and prevent default
        if (e && e.preventDefault) {
            e.preventDefault();
        } else {
            // Fallback for older browsers or if event is null
            console.warn('Event or preventDefault not available');
            return false;
        }
        
        const formData = new FormData(this);
        const action = formData.get('action');
        const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
        formData.append('csrf_token', csrfToken);
        
        fetch('../api/unsur_api.php', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            alert('Error: ' + error.message);
        });
    } catch (error) {
        console.error('Form submission handler error:', error);
        alert('Error: Failed to submit form - ' + error.message);
    }
    
    return false;
});
</script>
