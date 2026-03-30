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
        $stmt = $pdo->prepare("INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, urutan) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['kode_unsur'],
            $_POST['nama_unsur'],
            $_POST['deskripsi'] ?? '',
            $_POST['urutan'] ?? 0
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil ditambahkan!']);
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

// Get current unsur data
$stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
$unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-sitemap me-2"></i>Manajemen Unsur</h1>
        <p class="text-muted">Atur urutan dan kelola data unsur organisasi POLRES Samosir</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo count($unsurData); ?></div>
                <div class="label">Total Unsur</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM bagian");
                    echo $stmt->fetchColumn();
                ?></div>
                <div class="label">Total Bagian</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_deleted = FALSE AND is_active = TRUE");
                    echo $stmt->fetchColumn();
                ?></div>
                <div class="label">Total Personil</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $avgBagian = count($unsurData) > 0 ? round($stmt->fetchColumn() / count($unsurData), 1) : 0;
                    echo $avgBagian;
                ?></div>
                <div class="label">Rata-rata Bagian/Unsur</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mb-4">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Tambah Unsur
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
        <button class="btn btn-success" onclick="saveOrder()">
            <i class="fas fa-save me-2"></i>Simpan Urutan
        </button>
    </div>

    <!-- Sortable Unsur Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i>Urutan Unsur Organisasi
            <small class="text-muted ms-2">(Drag & drop untuk mengatur urutan)</small>
        </div>
        <div class="card-body">
            <div id="sortable-container" class="sortable-list">
                <?php foreach ($unsurData as $unsur): ?>
                <div class="sortable-item" data-id="<?php echo $unsur['id']; ?>" data-urutan="<?php echo $unsur['urutan']; ?>">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($unsur['nama_unsur']); ?></strong>
                                    <br>
                                    <small class="text-muted">Kode: <?php echo htmlspecialchars($unsur['kode_unsur']); ?> | Urutan: <?php echo $unsur['urutan']; ?></small>
                                </div>
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
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bagian Summary -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-building me-2"></i>Ringkasan Bagian per Unsur
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Unsur</th>
                            <th>Jumlah Bagian</th>
                            <th>Daftar Bagian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($unsurData as $unsur): 
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total, GROUP_CONCAT(nama_bagian SEPARATOR ', ') as bagians FROM bagian WHERE id_unsur = ?");
                            $stmt->execute([$unsur['id']]);
                            $bagianInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($unsur['nama_unsur']); ?></strong>
                                <br>
                                <small class="text-muted">Urutan: <?php echo $unsur['urutan']; ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $bagianInfo['total']; ?></span>
                            </td>
                            <td>
                                <small><?php echo $bagianInfo['bagians'] ?: '-'; ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                        <label for="kode_unsur" class="form-label">Kode Unsur</label>
                        <input type="text" class="form-control" id="kode_unsur" name="kode_unsur" required>
                        <div class="form-text">
                            Contoh: UNSUR_PIMPINAN, UNSUR_PEMBANTU_PIMPINAN
                        </div>
                    </div>
                    
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
                            Opsional: Deskripsi singkat tentang unsur ini
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

<style>
.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: var(--primary-color);
    font-weight: bold;
    margin-bottom: 10px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}

.stats-card .number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
}

.stats-card .label {
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

.sortable-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sortable-item {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 10px;
    padding: 15px;
    cursor: move;
    transition: all 0.3s ease;
}

.sortable-item:hover {
    background: #f8f9fa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.sortable-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.drag-handle {
    color: #999;
    font-size: 1.2rem;
    cursor: grab;
}

.drag-handle:hover {
    color: #666;
}

.drag-handle:active {
    cursor: grabbing;
}

.table th {
    background: var(--primary-color);
    color: white;
    border: none;
}

.table td {
    vertical-align: middle;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .action-buttons {
        justify-content: center;
    }
    
    .stats-card .number {
        font-size: 1.5rem;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .sortable-item {
        padding: 10px;
    }
    
    .drag-handle {
        font-size: 1rem;
    }
}
</style>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let unsurData = <?php echo json_encode($unsurData); ?>;

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
        const urutanDisplay = item.querySelector('small');
        if (urutanDisplay) {
            urutanDisplay.textContent = `Kode: ${item.dataset.kode} | Urutan: ${index + 1}`;
        }
    });
}

function showSaveButton() {
    const saveBtn = document.querySelector('[onclick="saveOrder()"]');
    if (saveBtn) {
        saveBtn.classList.remove('btn-success');
        saveBtn.classList.add('btn-warning');
        saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Simpan Perubahan';
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
            location.reload();
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
    document.getElementById('modalTitle').textContent = 'Tambah Unsur';
    document.getElementById('formAction').value = 'create_unsur';
    document.getElementById('formId').value = '';
    document.getElementById('kode_unsur').value = '';
    document.getElementById('nama_unsur').value = '';
    document.getElementById('urutan').value = '';
    document.getElementById('deskripsi').value = '';
    
    new bootstrap.Modal(document.getElementById('unsurModal')).show();
}

function editUnsur(id) {
    const unsur = unsurData.find(u => u.id == id);
    
    if (unsur) {
        document.getElementById('modalTitle').textContent = 'Edit Unsur';
        document.getElementById('formAction').value = 'update_unsur';
        document.getElementById('formId').value = unsur.id;
        document.getElementById('kode_unsur').value = unsur.kode_unsur;
        document.getElementById('nama_unsur').value = unsur.nama_unsur;
        document.getElementById('urutan').value = unsur.urutan;
        document.getElementById('deskripsi').value = unsur.deskripsi || '';
        
        new bootstrap.Modal(document.getElementById('unsurModal')).show();
    }
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
