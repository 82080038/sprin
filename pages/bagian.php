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
// if (!AuthHelper::validateSession()) {
//     header('Location: ' . url('login.php'));
//     exit;
// }

$page_title = 'Manajemen Bagian - Sistem Manajemen POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container">

<?php
// Initialize database connection
require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

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
    
    if ($action === 'get_bagian_list') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagianData]);
        exit;
    }
    
    // All other CRUD operations are now handled by the API
    // Redirect to API for all other actions
    exit;
}

// Get data from database
try {
    // Get unsur data
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if bagian table has urutan column
    $columnCheck = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
    $hasUrutanColumn = $columnCheck->rowCount() > 0;
    
    // Get bagian data with unsur info using proper ordering
    if ($hasUrutanColumn) {
        $stmt = $pdo->query("
            SELECT b.*, u.nama_unsur 
            FROM bagian b 
            LEFT JOIN unsur u ON b.id_unsur = u.id 
            ORDER BY u.urutan, b.urutan, b.nama_bagian
        ");
    } else {
        $stmt = $pdo->query("
            SELECT b.*, u.nama_unsur 
            FROM bagian b 
            LEFT JOIN unsur u ON b.id_unsur = u.id 
            ORDER BY u.urutan, b.nama_bagian
        ");
    }
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add type field based on bagian name - FIXED VERSION
    $bagianDataWithType = [];
    foreach ($bagianData as $bagian) {
        $bagianWithType = $bagian;
        
        if (strpos($bagian['nama_bagian'], 'PIMPINAN') !== false) {
            $bagianWithType['type'] = 'PIMPINAN';
        } elseif (strpos($bagian['nama_bagian'], 'BAG_') !== false) {
            $bagianWithType['type'] = 'BAG';
        } elseif (strpos($bagian['nama_bagian'], 'SAT_') !== false) {
            $bagianWithType['type'] = 'SAT';
        } elseif (strpos($bagian['nama_bagian'], 'POLSEK') !== false) {
            $bagianWithType['type'] = 'POLSEK';
        } elseif (strpos($bagian['nama_bagian'], 'SPKT') !== false) {
            $bagianWithType['type'] = 'SPKT';
        } elseif (strpos($bagian['nama_bagian'], 'SIUM') !== false) {
            $bagianWithType['type'] = 'SIUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIKEU') !== false) {
            $bagianWithType['type'] = 'SIKEU';
        } elseif (strpos($bagian['nama_bagian'], 'SIDOKKES') !== false) {
            $bagianWithType['type'] = 'SIDOKKES';
        } elseif (strpos($bagian['nama_bagian'], 'SIWAS') !== false) {
            $bagianWithType['type'] = 'SIWAS';
        } elseif (strpos($bagian['nama_bagian'], 'SITIK') !== false) {
            $bagianWithType['type'] = 'SITIK';
        } elseif (strpos($bagian['nama_bagian'], 'SIKUM') !== false) {
            $bagianWithType['type'] = 'SIKUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIPROPAM') !== false) {
            $bagianWithType['type'] = 'SIPROPAM';
        } elseif (strpos($bagian['nama_bagian'], 'SIHUMAS') !== false) {
            $bagianWithType['type'] = 'SIHUMAS';
        } elseif (strpos($bagian['nama_bagian'], 'BKO') !== false) {
            $bagianWithType['type'] = 'BKO';
        } else {
            $bagianWithType['type'] = 'LAINNYA';
        }
        
        $bagianDataWithType[] = $bagianWithType;
    }
    
    // Replace original with processed data
    $bagianData = $bagianDataWithType;
    
    // Group bagian by unsur
    $bagianByUnsur = [];
    foreach ($bagianData as $bagian) {
        $unsurId = $bagian['id_unsur'];
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
        }
        $bagianByUnsur[$unsurId][] = $bagian;
    }
    
} catch (PDOException $e) {
    $unsurData = [];
    $bagianData = [];
    $bagianByUnsur = [];
}
?>

<style>
/* Card-based Hierarchical Bagian Management Styles */
.unsur-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.unsur-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.unsur-header {
    background: var(--gradient-primary);
    color: white;
    padding: 1rem 1.25rem;
    border-bottom: 3px solid rgba(255,255,255,0.1);
}

.unsur-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.unsur-header small {
    opacity: 0.8;
    font-size: 0.8rem;
}

.add-bagian-btn {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.add-bagian-btn:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    color: white;
}

.bagian-container {
    background: #f8f9fa;
    min-height: 200px;
    max-height: 400px;
    overflow-y: auto;
}

.bagian-list {
    padding: 0.75rem;
}

.bagian-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: move;
    transition: all 0.3s ease;
    position: relative;
}

.bagian-item::before {
    content: '---';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: #007bff;
    font-weight: bold;
    font-size: 1rem;
    opacity: 0.7;
}

.bagian-item:hover {
    background: #e3f2fd;
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    transform: translateX(3px);
}

.bagian-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    z-index: 1000;
}

.bagian-item.drag-over {
    background: #fff3cd;
    border-color: #ffc107;
    border-style: dashed;
}

.bagian-info {
    flex-grow: 1;
}

.bagian-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.bagian-meta {
    font-size: 0.75rem;
    color: #6c757d;
}

.bagian-actions {
    display: flex;
    gap: 0.25rem;
}

.drag-handle {
    color: #6c757d;
    margin-right: 0.75rem;
    cursor: grab;
    font-size: 0.9rem;
    opacity: 0.6;
    transition: opacity 0.3s ease;
}

.drag-handle:active {
    cursor: grabbing;
}

.bagian-item:hover .drag-handle {
    opacity: 1;
}

.empty-bagian {
    text-align: center;
    color: #6c757d;
    padding: 2rem 1rem;
    font-style: italic;
}

.empty-bagian i {
    font-size: 2rem;
    opacity: 0.5;
    margin-bottom: 0.5rem;
}

.empty-bagian p {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

/* Sortable styles */
.sortable-ghost {
    background: #e3f2fd !important;
    border: 2px dashed #007bff !important;
}

.sortable-chosen {
    background: #f8f9fa !important;
    transform: scale(1.02);
}

/* Modal styles */
.modal-header {
    background: var(--gradient-primary);
    color: white;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .unsur-card {
        margin-bottom: 1rem;
    }
    
    .unsur-header {
        padding: 0.75rem 1rem;
    }
    
    .unsur-header h6 {
        font-size: 0.9rem;
    }
    
    .bagian-item {
        padding: 0.5rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .bagian-item::before {
        left: -1rem;
        font-size: 0.8rem;
    }
    
    .bagian-actions {
        align-self: flex-end;
        margin-top: 0.25rem;
    }
    
    .bagian-container {
        max-height: 300px;
    }
}

@media (max-width: 576px) {
    .col-md-6.col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Badge colors for different types */
.badge.bg-info { background: #17a2b8 !important; }
.badge.bg-primary { background: #007bff !important; }
.badge.bg-success { background: #28a745 !important; }
.badge.bg-warning { background: #ffc107 !important; color: #212529 !important; }
.badge.bg-danger { background: #dc3545 !important; }
.badge.bg-secondary { background: #6c757d !important; }
</style>

<div class="page-header">
    <h1><i class="fas fa-building me-2"></i>Manajemen Bagian</h1>
    <p class="text-muted text-center">Kelola dan atur bagian dalam setiap unsur organisasi POLRES Samosir</p>
</div>

<!-- Action Buttons -->
<div class="action-buttons mb-4">
    <button class="btn btn-info" onclick="refreshData()">
        <i class="fas fa-sync me-2"></i>Refresh
    </button>
    <button class="btn btn-success" id="saveChangesBtn" onclick="saveAllChanges()" style="display: none;">
        <i class="fas fa-save me-2"></i>Simpan Perubahan
    </button>
    <button class="btn btn-warning" id="cancelChangesBtn" onclick="cancelAllChanges()" style="display: none;">
        <i class="fas fa-times me-2"></i>Batal Perubahan
    </button>
</div>

<!-- Instructions -->
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Petunjuk:</strong> Seret dan lepas bagian untuk memindahkannya antar unsur. Unsur tidak dapat dipindahkan.
</div>

<!-- Unsur dan Bagian Containers -->
<div id="unsur-bagian-container" class="row">
    <?php foreach ($unsurData as $unsur): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="unsur-card" data-unsur-id="<?php echo $unsur['id']; ?>">
            <!-- Unsur Header -->
            <div class="unsur-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><i class="fas fa-layer-group me-2"></i><?php echo htmlspecialchars($unsur['nama_unsur']); ?></h6>
                        <small>Urutan: <?php echo $unsur['urutan']; ?></small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2"><?php echo isset($bagianByUnsur[$unsur['id']]) && is_array($bagianByUnsur[$unsur['id']]) ? count($bagianByUnsur[$unsur['id']]) : 0; ?></span>
                        <button class="btn btn-sm add-bagian-btn" onclick="openAddModalForUnsur(<?php echo $unsur['id']; ?>, '<?php echo htmlspecialchars($unsur['nama_unsur']); ?>')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Bagian Container -->
            <div class="bagian-container">
                <div class="bagian-list sortable-bagian" data-unsur-id="<?php echo $unsur['id']; ?>">
                    <?php if (isset($bagianByUnsur[$unsur['id']]) && is_array($bagianByUnsur[$unsur['id']]) && count($bagianByUnsur[$unsur['id']]) > 0): ?>
                        <?php foreach ($bagianByUnsur[$unsur['id']] as $bagian): ?>
                        <div class="bagian-item" data-id="<?php echo isset($bagian['id']) ? $bagian['id'] : ''; ?>" data-urutan="<?php echo isset($bagian['urutan']) ? $bagian['urutan'] : 0; ?>" data-unsur-id="<?php echo isset($bagian['id_unsur']) ? $bagian['id_unsur'] : ''; ?>">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <div class="bagian-info">
                                    <div class="bagian-name"><?php echo isset($bagian['nama_bagian']) ? htmlspecialchars($bagian['nama_bagian']) : 'Unknown'; ?></div>
                                    <div class="bagian-meta">
                                        <span class="badge bg-info me-2"><?php echo isset($bagian['type']) ? htmlspecialchars($bagian['type']) : 'Unknown'; ?></span>
                                        <small>ID: <?php echo isset($bagian['id']) ? $bagian['id'] : 'N/A'; ?></small>
                                    </div>
                                </div>
                                <div class="bagian-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editBagian(<?php echo isset($bagian['id']) ? $bagian['id'] : 0; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBagian(<?php echo isset($bagian['id']) ? $bagian['id'] : 0; ?>, '<?php echo isset($bagian['nama_bagian']) ? htmlspecialchars($bagian['nama_bagian']) : 'Unknown'; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-bagian">
                            <i class="fas fa-inbox mb-2"></i>
                            <p class="mb-2">Belum ada bagian</p>
                            <button class="btn btn-sm btn-primary" onclick="openAddModalForUnsur(<?php echo $unsur['id']; ?>, '<?php echo htmlspecialchars($unsur['nama_unsur']); ?>')">
                                <i class="fas fa-plus me-1"></i>Tambah Bagian
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Bagian Modal -->
<div class="modal fade" id="bagianModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-building me-2"></i>
                    <span id="modalTitle">Tambah Bagian</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="bagianForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_bagian">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_bagian" class="form-label">Nama Bagian</label>
                        <input type="text" class="form-control" id="nama_bagian" name="nama_bagian" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="id_unsur" name="id_unsur" required>
                            <option value="">-- Pilih Unsur --</option>
                            <?php foreach ($unsurData as $unsur): ?>
                            <option value="<?php echo $unsur['id']; ?>"><?php echo htmlspecialchars($unsur['nama_unsur']); ?></option>
                            <?php endforeach; ?>
                        </select>
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

<script>
// Fallback notification system if window.SPRINT is not available
if (!window.SPRINT) {
    window.SPRINT = {
        showSuccess: function(message) {
            try {
                if (typeof toastr !== 'undefined' && toastr.success) {
                    toastr.success(message);
                console.log('SUCCESS: ' + message);
                alert(message);
            }
        },
        showError: function(message) {
            if (typeof showToast === 'function') {
                showToast('danger', '❌ ' + message);
            } else if (window.SPRINT && window.SPRINT.showToast) {
                window.SPRINT.showToast(message, 'error');
            } else {
                console.error('ERROR: ' + message);
                alert('Error: ' + message);
            }
        }
    };
}

// Page-specific JavaScript for Hierarchical Bagian Management
let bagianData = <?php echo json_encode($bagianData); ?>;
let originalBagianData = [...bagianData];
let changes = [];
let sortableInstances = [];

// Debug: Check if libraries are loaded
console.log('Libraries check:');
console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
console.log('Toastr available:', typeof toastr !== 'undefined');
console.log('SPRINT available:', typeof window.SPRINT !== 'undefined');

// Initialize toastr if available but not configured
if (typeof toastr !== 'undefined' && !toastr.options) {
    console.log('Configuring toastr...');
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
}

// Initialize sortable for each bagian container
function initializeSortable() {
    // Destroy existing instances
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
    
    // Initialize new instances
    document.querySelectorAll('.sortable-bagian').forEach(container => {
        const sortable = new Sortable(container, {
            group: 'bagian', // Allow dragging between containers
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-dragging',
            handle: '.drag-handle',
            
            onEnd: function(evt) {
                handleBagianMove(evt);
            }
        });
        
        sortableInstances.push(sortable);
    });
}

// Handle bagian movement between unsur
function handleBagianMove(evt) {
    const bagianElement = evt.item;
    const bagianId = bagianElement.dataset.id; // Fixed: use data-id instead of data-bagianId
    const oldUnsurId = evt.from.dataset.unsurId;
    const newUnsurId = evt.to.dataset.unsurId;
    const newIndex = evt.newIndex;
    
    // Update visual state
    bagianElement.dataset.unsurId = newUnsurId;
    bagianElement.dataset.urutan = newIndex + 1; // Update urutan attribute
    
    // Track change
    const change = {
        bagian_id: bagianId,
        old_unsur_id: oldUnsurId,
        new_unsur_id: newUnsurId,
        new_urutan: newIndex + 1
    };
    
    // Remove existing change for this bagian if any
    changes = changes.filter(c => c.bagian_id !== bagianId);
    changes.push(change);
    
    // Show save/cancel buttons
    showSaveButtons();
    
    // Update counts
    updateBagianCounts();
    
}

// Show save/cancel buttons
function showSaveButtons() {
    document.getElementById('saveChangesBtn').style.display = 'inline-block';
    document.getElementById('cancelChangesBtn').style.display = 'inline-block';
}

// Hide save/cancel buttons
function hideSaveButtons() {
    document.getElementById('saveChangesBtn').style.display = 'none';
    document.getElementById('cancelChangesBtn').style.display = 'none';
}

// Save all changes
function saveAllChanges() {
    if (changes.length === 0) {
        try {
            window.SPRINT.showSuccess('Tidak ada perubahan untuk disimpan.');
        } catch (error) {
            console.log('Tidak ada perubahan untuk disimpan.');
        }
        return;
    }
    
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    const savePromises = changes.map(change => {
        return fetch('../api/bagian_api.php', {
            credentials: 'same-origin',
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                action: 'move_bagian',
                bagian_id: change.bagian_id,
                new_unsur_id: change.new_unsur_id,
                new_urutan: change.new_urutan
            })
        });
    });
    
    Promise.all(savePromises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
            const allSuccess = results.every(r => r.success);
            if (allSuccess) {
                window.SPRINT.showSuccess('Semua perubahan berhasil disimpan!');
                changes = [];
                hideSaveButtons();
                refreshData();
            } else {
                const failed = results.filter(r => !r.success);
                const errorMessage = 'Beberapa perubahan gagal: ' + failed.map(r => r.message).join(', ');
                window.SPRINT.showError(errorMessage);
            }
        })
        .catch(error => {
            console.error('Error saving changes:', error);
            const errorMessage = 'Terjadi kesalahan saat menyimpan perubahan.';
            try {
                window.SPRINT.showError(errorMessage);
            } catch (notificationError) {
                console.error('Notification error:', notificationError);
                console.error('Original error:', error);
                showToast('danger', '❌ ' + errorMessage);
            }
        });
}

// Cancel all changes
function cancelAllChanges() {
    if (confirm('Apakah Anda yakin ingin membatalkan semua perubahan?')) {
        changes = [];
        hideSaveButtons();
        refreshData();
    }
}

// Update bagian counts
function updateBagianCounts() {
    document.querySelectorAll('.unsur-container').forEach(container => {
        const unsurId = container.dataset.unsurId;
        const bagianList = container.querySelector('.sortable-bagian');
        const count = bagianList.querySelectorAll('.bagian-item').length;
        const badge = container.querySelector('.badge');
        if (badge) {
            badge.textContent = `${count} bagian`;
        }
    });
}

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Bagian';
    document.getElementById('formAction').value = 'create_bagian';
    document.getElementById('formId').value = '';
    document.getElementById('nama_bagian').value = '';
    document.getElementById('id_unsur').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
    modal.show();
}

function openAddModalForUnsur(unsurId, unsurName) {
    document.getElementById('modalTitle').textContent = `Tambah Bagian - ${unsurName}`;
    document.getElementById('formAction').value = 'create_bagian';
    document.getElementById('formId').value = '';
    document.getElementById('nama_bagian').value = '';
    document.getElementById('id_unsur').value = unsurId;
    
    const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
    modal.show();
}

function editBagian(id) {
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    fetch('../api/bagian_api.php', {
            credentials: 'same-origin',
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({ action: 'get_bagian_detail', id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const bagian = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Bagian';
            document.getElementById('formAction').value = 'update_bagian';
            document.getElementById('formId').value = bagian.id;
            document.getElementById('nama_bagian').value = bagian.nama_bagian;
            document.getElementById('id_unsur').value = bagian.id_unsur;
            
            const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
            modal.show();
        } else {
            try {
                window.SPRINT.showError('Error: Bagian tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        try {
            window.SPRINT.showError('Error: ' + error.message);
        } catch (notificationError) {
            console.error('Error: ' + error.message);
            showToast('danger', '❌ ' + error.message);
        }
    });
}

function deleteBagian(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus bagian "${nama}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_bagian">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function refreshData() {
    window.location.reload();
}

// Form submission
document.getElementById('bagianForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = formData.get('action');
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    formData.append('csrf_token', csrfToken);
    
    fetch('../api/bagian_api.php', {
            credentials: 'same-origin',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            try {
                window.SPRINT.showSuccess(data.message);
            } catch (error) {
                console.log('Success: ' + data.message);
            }
            bootstrap.Modal.getInstance(document.getElementById('bagianModal')).hide();
            refreshData();
        } else {
            try {
                window.SPRINT.showError('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        try {
            window.SPRINT.showError('Error: Terjadi kesalahan saat menyimpan data');
        } catch (notificationError) {
            console.error('Error: Terjadi kesalahan saat menyimpan data');
            showToast('danger', '❌ Terjadi kesalahan saat menyimpan data');
        }
    });
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load SortableJS if not already loaded
    if (!window.Sortable) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        script.onload = initializeSortable;
        document.head.appendChild(script);
    } else {
        initializeSortable();
    }
});
</script>

</div> <!-- End container -->

<?php include '../includes/components/footer.php'; ?>
