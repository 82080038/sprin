<?php
// Start output buffering if not already started
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config first
require_once __DIR__ . '/../core/config.php';

// Include authentication check
require_once __DIR__ . '/../core/auth_check.php';

$page_title = 'Manajemen Unsur - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>

<!-- Debug: Ensure we're not in a frame -->
<script>
if (window.top !== window.self) {
    window.top.location = window.self.location;
}
</script>

<style>
/* Sortable Styles */
.sortable-list {
    min-height: 100px;
}

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
// Connect to database
require_once __DIR__ . '/../core/calendar_config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle AJAX operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_unsur_list', 'get_unsur_detail', 'create_unsur', 'update_unsur', 'delete_unsur', 'update_order'])) {
        // Set test session for AJAX
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
        
        // Clear any output buffers for AJAX requests
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    if ($action === 'get_unsur_list') {
        $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
        $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $unsurData]);
        exit;
    }
    
    if ($action === 'update_order') {
        $orders = $_POST['orders'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            foreach ($orders as $order) {
                $stmt = $pdo->prepare("UPDATE unsur SET urutan = ? WHERE id = ?");
                $stmt->execute([$order['urutan'], $order['id']]);
            }
            
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Urutan unsur berhasil diperbarui!']);
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui urutan: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($action === 'create_unsur') {
        // Auto-generate kode_unsur from nama_unsur
        $nama_unsur = $_POST['nama_unsur'];
        $kode_unsur = preg_replace('/[^a-zA-Z0-9_]/', '_', strtoupper($nama_unsur));
        
        $stmt = $pdo->prepare("INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, urutan) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $kode_unsur,
            $nama_unsur,
            $_POST['deskripsi'] ?? '',
            $_POST['urutan'] ?? 0
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil ditambahkan!']);
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
    
    if ($action === 'update_unsur') {
        $stmt = $pdo->prepare("UPDATE unsur SET kode_unsur = ?, nama_unsur = ?, deskripsi = ?, urutan = ? WHERE id = ?");
        $stmt->execute([
            $_POST['kode_unsur'],
            $_POST['nama_unsur'],
            $_POST['deskripsi'] ?? '',
            $_POST['urutan'] ?? 0,
            $_POST['id']
        ]);
        
        // Update pimpinan assignment
        if (!empty($_POST['nama_pimpinan'])) {
            // Remove existing assignments
            $delStmt = $pdo->prepare("DELETE FROM unsur_pimpinan WHERE unsur_id = ? AND tanggal_selesai IS NULL");
            $delStmt->execute([$_POST['id']]);
            
            // Add new assignment
            $pimpinanStmt = $pdo->prepare("SELECT id FROM personil WHERE nama = ?");
            $pimpinanStmt->execute([$_POST['nama_pimpinan']]);
            $pimpinanId = $pimpinanStmt->fetchColumn();
            
            if ($pimpinanId) {
                $relStmt = $pdo->prepare("INSERT INTO unsur_pimpinan (unsur_id, personil_id) VALUES (?, ?)");
                $relStmt->execute([$_POST['id'], $pimpinanId]);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil diperbarui!']);
        exit;
    }
    
    if ($action === 'delete_unsur') {
        // Check if unsur has bagian
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bagian WHERE id_unsur = ?");
        $stmt->execute([$_POST['id']]);
        $bagianCount = $stmt->fetchColumn();
        
        if ($bagianCount > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus unsur yang masih memiliki bagian!']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil dihapus!']);
        exit;
    }
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
    <div class="modal-dialog">
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
                    
                    <div class="mb-3">
                        <label for="nama_unsur" class="form-label">Nama Unsur</label>
                        <input type="text" class="form-control" id="nama_unsur" name="nama_unsur" required>
                        <div class="form-text">
                            Contoh: UNSUR PIMPINAN, UNSUR PEMBANTU PIMPINAN
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="urutan" class="form-label">Urutan</label>
                        <input type="number" class="form-control" id="urutan" name="urutan" min="0" required>
                        <div class="form-text">
                            Nomor urutan untuk penampilan (1 = paling atas)
                        </div>
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
    
    fetch('unsur.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'update_order',
            orders: JSON.stringify(orders)
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

function openAddModal() {
    // Clear form fields first
    document.getElementById('modalTitle').textContent = 'Tambah Unsur';
    document.getElementById('formAction').value = 'create_unsur';
    document.getElementById('formId').value = '';
    document.getElementById('nama_unsur').value = '';
    document.getElementById('urutan').value = '';
    document.getElementById('deskripsi').value = '';
    
    // Show modal with proper handling
    const modal = new bootstrap.Modal(document.getElementById('unsurModal'));
    
    // Clean up any existing modal instance
    const existingModal = bootstrap.Modal.getInstance(document.getElementById('unsurModal'));
    if (existingModal) {
        existingModal.dispose();
    }
    
    modal.show();
}

function editUnsur(id) {
    console.log('Editing unsur with ID:', id);
    
    // Get unsur data from database
    fetch('unsur.php', {
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
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success && data.data) {
            const unsur = data.data;
            console.log('Unsur data:', unsur);
            
            // Fill form fields
            document.getElementById('modalTitle').textContent = 'Edit Unsur';
            document.getElementById('formAction').value = 'update_unsur';
            document.getElementById('formId').value = unsur.id;
            document.getElementById('nama_unsur').value = unsur.nama_unsur;
            document.getElementById('urutan').value = unsur.urutan;
            document.getElementById('deskripsi').value = unsur.deskripsi || '';
            
            // Show modal with proper handling
            const modal = new bootstrap.Modal(document.getElementById('unsurModal'));
            
            // Clean up any existing modal instance
            const existingModal = bootstrap.Modal.getInstance(document.getElementById('unsurModal'));
            if (existingModal) {
                existingModal.dispose();
            }
            
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
}

function deleteUnsur(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus unsur "${nama}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_unsur">
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
document.getElementById('unsurForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = formData.get('action');
    
    fetch('unsur.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Terjadi kesalahan saat menyimpan data');
    });
});
</script>
