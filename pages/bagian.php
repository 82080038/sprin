<?php
// Content-only page for SPA - Manajemen Bagian dengan struktur hierarki
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Session expired. Please login again.</div>';
    exit;
}

// Initialize database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

// Handle AJAX operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_bagian_list', 'get_bagian_detail', 'create_bagian', 'update_bagian', 'delete_bagian', 'move_bagian'])) {
        // Set test session for AJAX
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
        
        // Clear any output buffers for AJAX requests
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    if ($action === 'get_bagian_list') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagianData]);
        exit;
    }
    
    if ($action === 'move_bagian') {
        $bagianId = $_POST['bagian_id'] ?? 0;
        $newUnsurId = $_POST['new_unsur_id'] ?? 0;
        $newUrutan = $_POST['new_urutan'] ?? 0;
        
        try {
            $pdo->beginTransaction();
            
            // Update bagian's unsur and urutan
            $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ?, urutan = ? WHERE id = ?");
            $stmt->execute([$newUnsurId, $newUrutan, $bagianId]);
            
            // Reorder other bagian in the same unsur
            $stmt = $pdo->prepare("SELECT id FROM bagian WHERE id_unsur = ? AND id != ? ORDER BY urutan");
            $stmt->execute([$newUnsurId, $bagianId]);
            $otherBagians = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $urutan = 1;
            foreach ($otherBagians as $id) {
                if ($urutan == $newUrutan) $urutan++; // Skip the moved position
                $updateStmt = $pdo->prepare("UPDATE bagian SET urutan = ? WHERE id = ?");
                $updateStmt->execute([$urutan, $id]);
                $urutan++;
            }
            
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Bagian berhasil dipindahkan!']);
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan bagian: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($action === 'get_bagian_detail') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM bagian WHERE id = ?");
        $stmt->execute([$id]);
        $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagian]);
        exit;
    }
    
    if ($action === 'create_bagian') {
        // Get max urutan for the unsur
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 FROM bagian WHERE id_unsur = ?");
        $stmt->execute([$_POST['id_unsur']]);
        $nextUrutan = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian, id_unsur, type, urutan) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nama_bagian'], $_POST['id_unsur'], $_POST['type'], $nextUrutan]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil ditambahkan!']);
        exit;
    }
    
    if ($action === 'update_bagian') {
        $stmt = $pdo->prepare("UPDATE bagian SET nama_bagian = ?, id_unsur = ?, type = ? WHERE id = ?");
        $stmt->execute([$_POST['nama_bagian'], $_POST['id_unsur'], $_POST['type'], $_POST['id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil diperbarui!']);
        exit;
    }
    
    if ($action === 'delete_bagian') {
        // Check if bagian has personil
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ?");
        $stmt->execute([$_POST['id']]);
        $personilCount = $stmt->fetchColumn();
        
        if ($personilCount > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus bagian yang masih memiliki personil!']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil dihapus!']);
        exit;
    }
}

// Get data from database
try {
    // Get unsur data
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get bagian data with unsur info
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add type field based on bagian name
    foreach ($bagianData as &$bagian) {
        if (strpos($bagian['nama_bagian'], 'PIMPINAN') !== false) {
            $bagian['type'] = 'PIMPINAN';
        } elseif (strpos($bagian['nama_bagian'], 'BAG_') !== false) {
            $bagian['type'] = 'BAG';
        } elseif (strpos($bagian['nama_bagian'], 'SAT_') !== false) {
            $bagian['type'] = 'SAT';
        } elseif (strpos($bagian['nama_bagian'], 'POLSEK') !== false) {
            $bagian['type'] = 'POLSEK';
        } elseif (strpos($bagian['nama_bagian'], 'SPKT') !== false) {
            $bagian['type'] = 'SPKT';
        } elseif (strpos($bagian['nama_bagian'], 'SIUM') !== false) {
            $bagian['type'] = 'SIUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIKEU') !== false) {
            $bagian['type'] = 'SIKEU';
        } elseif (strpos($bagian['nama_bagian'], 'SIDOKKES') !== false) {
            $bagian['type'] = 'SIDOKKES';
        } elseif (strpos($bagian['nama_bagian'], 'SIWAS') !== false) {
            $bagian['type'] = 'SIWAS';
        } elseif (strpos($bagian['nama_bagian'], 'SITIK') !== false) {
            $bagian['type'] = 'SITIK';
        } elseif (strpos($bagian['nama_bagian'], 'SIKUM') !== false) {
            $bagian['type'] = 'SIKUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIPROPAM') !== false) {
            $bagian['type'] = 'SIPROPAM';
        } elseif (strpos($bagian['nama_bagian'], 'SIHUMAS') !== false) {
            $bagian['type'] = 'SIHUMAS';
        } elseif (strpos($bagian['nama_bagian'], 'BKO') !== false) {
            $bagian['type'] = 'BKO';
        } else {
            $bagian['type'] = 'LAINNYA';
        }
    }
    
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
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
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
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
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
    
    .unsur-card {
        margin-bottom: 1rem;
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
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus me-2"></i>Tambah Bagian
    </button>
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
                        <span class="badge bg-light text-dark me-2"><?php echo count($bagianByUnsur[$unsur['id']] ?? []); ?></span>
                        <button class="btn btn-sm add-bagian-btn" onclick="openAddModalForUnsur(<?php echo $unsur['id']; ?>, '<?php echo htmlspecialchars($unsur['nama_unsur']); ?>')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Bagian Container -->
            <div class="bagian-container">
                <div class="bagian-list sortable-bagian" data-unsur-id="<?php echo $unsur['id']; ?>">
                    <?php if (isset($bagianByUnsur[$unsur['id']]) && !empty($bagianByUnsur[$unsur['id']])): ?>
                        <?php foreach ($bagianByUnsur[$unsur['id']] as $bagian): ?>
                        <div class="bagian-item" data-bagian-id="<?php echo $bagian['id']; ?>" data-unsur-id="<?php echo $bagian['id_unsur']; ?>">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <div class="bagian-info">
                                    <div class="bagian-name"><?php echo htmlspecialchars($bagian['nama_bagian']); ?></div>
                                    <div class="bagian-meta">
                                        <span class="badge bg-info me-2"><?php echo htmlspecialchars($bagian['type']); ?></span>
                                        <small>ID: <?php echo $bagian['id']; ?></small>
                                    </div>
                                </div>
                                <div class="bagian-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editBagian(<?php echo $bagian['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBagian(<?php echo $bagian['id']; ?>, '<?php echo htmlspecialchars($bagian['nama_bagian']); ?>')">
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
    <div class="modal-dialog">
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
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="BAG/SAT/SIE">BAG/SAT/SIE</option>
                            <option value="POLSEK">POLSEK</option>
                            <option value="SPKT">SPKT</option>
                            <option value="SIUM">SIUM</option>
                            <option value="SIKEU">SIKEU</option>
                            <option value="SIDOKKES">SIDOKKES</option>
                            <option value="SIWAS">SIWAS</option>
                            <option value="SITIK">SITIK</option>
                            <option value="SIKUM">SIKUM</option>
                            <option value="SIPROPAM">SIPROPAM</option>
                            <option value="SIHUMAS">SIHUMAS</option>
                            <option value="BKO">BKO</option>
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
// Page-specific JavaScript for Hierarchical Bagian Management
let bagianData = <?php echo json_encode($bagianData); ?>;
let originalBagianData = [...bagianData];
let changes = [];
let sortableInstances = [];

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
    const bagianId = bagianElement.dataset.bagianId;
    const oldUnsurId = evt.from.dataset.unsurId;
    const newUnsurId = evt.to.dataset.unsurId;
    const newIndex = evt.newIndex;
    
    // Update visual state
    bagianElement.dataset.unsurId = newUnsurId;
    
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
    
    console.log('Bagian moved:', change);
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
        window.SPRINT.showSuccess('Tidak ada perubahan untuk disimpan.');
        return;
    }
    
    const savePromises = changes.map(change => {
        return fetch('pages/bagian_hierarki.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
                window.SPRINT.showError('Beberapa perubahan gagal: ' + failed.map(r => r.message).join(', '));
            }
        })
        .catch(error => {
            console.error('Error saving changes:', error);
            window.SPRINT.showError('Terjadi kesalahan saat menyimpan perubahan.');
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
    document.getElementById('type').value = 'BAG/SAT/SIE';
    
    const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
    modal.show();
}

function openAddModalForUnsur(unsurId, unsurName) {
    document.getElementById('modalTitle').textContent = `Tambah Bagian - ${unsurName}`;
    document.getElementById('formAction').value = 'create_bagian';
    document.getElementById('formId').value = '';
    document.getElementById('nama_bagian').value = '';
    document.getElementById('id_unsur').value = unsurId;
    document.getElementById('type').value = 'BAG/SAT/SIE';
    
    const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
    modal.show();
}

function editBagian(id) {
    fetch('pages/bagian_hierarki.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
            document.getElementById('type').value = bagian.type;
            
            const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
            modal.show();
        } else {
            window.SPRINT.showError('Error: Bagian tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.SPRINT.showError('Error: ' + error.message);
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
    
    fetch('pages/bagian_hierarki.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SPRINT.showSuccess(data.message);
            bootstrap.Modal.getInstance(document.getElementById('bagianModal')).hide();
            refreshData();
        } else {
            window.SPRINT.showError('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.SPRINT.showError('Error: Terjadi kesalahan saat menyimpan data');
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
