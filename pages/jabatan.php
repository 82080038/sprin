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

$page_title = 'Manajemen Jabatan - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

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
    
    if ($action === 'get_jabatan_list') {
        $unsurId = $_POST['id_unsur'] ?? null;
        
        $sql = "
            SELECT 
                j.id,
                j.nama_jabatan,
                j.id_unsur,
                j.id_bagian,
                j.urutan,
                COALESCE(u.nama_unsur, 'BELUM DISET') as nama_unsur,
                COALESCE(u.urutan, 99) as urutan_unsur,
                (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = FALSE AND p.is_active = TRUE) as personil_count
            FROM jabatan j
            LEFT JOIN unsur u ON j.id_unsur = u.id
            WHERE 1=1
        ";
        
        $params = [];
        if ($unsurId) {
            $sql .= " AND j.id_unsur = ?";
            $params[] = $unsurId;
        }
        
        $sql .= " ORDER BY COALESCE(u.urutan, 99) ASC, j.urutan ASC, j.nama_jabatan ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jabatanData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $jabatanData]);
        exit;
    }
    
    if ($action === 'get_unsur_list') {
        $stmt = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur ORDER BY urutan");
        $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $unsurData]);
        exit;
    }
    
    if ($action === 'get_jabatan_detail') {
        $jabatanId = $_POST['id'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM jabatan WHERE id = ?");
        $stmt->execute([$jabatanId]);
        $jabatan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $jabatan]);
        exit;
    }
    
    // All other CRUD operations are now handled by the API
    // Redirect to API for all other actions
    exit;
}

// Get data for display
$bagianStmt = $pdo->query("SELECT * FROM bagian ORDER BY id_unsur, urutan, nama_bagian ASC");
$bagianData = $bagianStmt->fetchAll(PDO::FETCH_ASSOC);

$jabatanStmt = $pdo->query("
    SELECT 
        j.id,
        j.nama_jabatan,
        j.id_unsur,
        j.id_bagian,
        j.urutan,
        COALESCE(u.nama_unsur, 'BELUM DISET') as nama_unsur,
        COALESCE(u.urutan, 99) as urutan_unsur,
        COALESCE(b.nama_bagian, 'BELUM DISET') as nama_bagian,
        COALESCE(b.urutan, 99) as urutan_bagian,
        (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = FALSE AND p.is_active = TRUE) as personil_count
    FROM jabatan j
    LEFT JOIN unsur u ON j.id_unsur = u.id
    LEFT JOIN bagian b ON j.id_bagian = b.id
    ORDER BY COALESCE(u.urutan, 99), COALESCE(b.nama_bagian, 'ZZZ'), COALESCE(b.urutan, 99), j.urutan ASC, j.nama_jabatan ASC
");
$jabatanData = $jabatanStmt->fetchAll(PDO::FETCH_ASSOC);

$unsurStmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan ASC");
$unsurData = $unsurStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script>
if (window.top !== window.self) {
    window.top.location = window.self.location;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* Card-based Jabatan Management Styles */
.jabatan-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.jabatan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.unsur-header {
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px 12px 0 0;
}

.unsur-header h6 {
    margin: 0;
    font-weight: 600;
}

.unsur-header small {
    opacity: 0.8;
    font-size: 0.8rem;
}

.bagian-container {
    background: #f8f9fa;
    min-height: 200px;
    padding: 1rem;
    border-radius: 0 0 12px 12px;
}

.bagian-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    cursor: move;
    transition: all 0.3s ease;
    position: relative;
}

.bagian-item::before {
    content: '---';
    position: absolute;
    left: -1.5rem;
    color: #6c757d;
    font-size: 0.8rem;
}

.bagian-item:hover {
    background: #e3f2fd;
    border-color: #007bff;
}

.bagian-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.bagian-item.drag-over {
    background: #fff3cd;
    border-color: #ffc107;
}

.drag-handle {
    color: #6c757d;
    margin-right: 0.75rem;
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.bagian-item:hover .drag-handle {
    opacity: 1;
}

.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
}

.sortable-chosen {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

.sortable-dragging {
    transform: rotate(5deg);
    box-shadow: 0 10px 30px rgba(0,123,255,0.4);
}

.bagian-actions {
    display: flex;
    gap: 0.25rem;
}

.jabatan-count {
    background: var(--primary-color, #1a237e);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
}

.btn-save {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.btn-cancel {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
    margin-left: 0.5rem;
}

.btn-cancel:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.stats-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: none;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-card .number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

.unsur-header {
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px 12px 0 0;
}

.unsur-header h5 {
    margin: 0;
    font-weight: 600;
}

.unsur-header small {
    opacity: 0.8;
    font-size: 0.9rem;
}

.unsur-container {
    background: #f8f9fa;
    min-height: 200px;
    padding: 1rem;
    border-radius: 0 0 12px 12px;
}

.bagian-section {
    background: white;
    border-radius: 8px;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
}

.bagian-header {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.bagian-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 0.9rem;
}

.jabatan-list {
    min-height: 50px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .bagian-item {
        padding: 0.5rem;
        flex-direction: column;
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
</style>

<div class="page-header">
    <h1><i class="fas fa-user-tie me-2"></i>Manajemen Jabatan</h1>
    <p class="text-muted text-center">Kelola dan atur jabatan dalam setiap unsur organisasi POLRES Samosir</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="number"><?php echo count($jabatanData); ?></div>
                <div class="text-muted">Total Jabatan</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="number"><?php echo count(array_filter($jabatanData, fn($j) => $j['personil_count'] > 0)); ?></div>
                <div class="text-muted">Jabatan Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="number"><?php echo count(array_filter($jabatanData, fn($j) => $j['personil_count'] == 0)); ?></div>
                <div class="text-muted">Jabatan Kosong</div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>Tambah Jabatan
                </button>
                <button class="btn btn-success" id="saveOrderBtn" onclick="saveOrder()" style="display: none;">
                    <i class="fas fa-save me-2"></i>Simpan Urutan
                </button>
                <button class="btn btn-warning" id="cancelOrderBtn" onclick="cancelOrder()" style="display: none;">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button class="btn btn-info" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <button class="btn btn-success" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
            <div class="d-flex align-items-center">
                <select class="form-select me-2" id="unsurFilter" onchange="filterByUnsur()">
                    <option value="">Semua Unsur</option>
                    <?php foreach ($unsurData as $unsur): ?>
                        <option value="<?php echo $unsur['id']; ?>"><?php echo htmlspecialchars($unsur['nama_unsur']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Jabatan Container -->
<div id="jabatanContainer">
    <!-- Content will be populated by JavaScript -->
</div>

<!-- Jabatan Modal -->
<div class="modal fade" id="jabatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="jabatanForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_jabatan">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kode_jabatan" class="form-label">Kode Jabatan</label>
                        <input type="text" class="form-control" id="kode_jabatan" name="kode_jabatan">
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="id_unsur" name="id_unsur" required>
                            <option value="">Pilih Unsur</option>
                            <?php foreach ($unsurData as $unsur): ?>
                                <option value="<?php echo $unsur['id']; ?>"><?php echo htmlspecialchars($unsur['nama_unsur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewJabatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Jabatan</label>
                    <p class="form-control-plaintext" id="viewJabatanNama"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unsur</label>
                    <p class="form-control-plaintext" id="viewJabatanUnsur"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Personil</label>
                    <div id="viewJabatanPersonil"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>

<script>
let jabatanData = <?php echo json_encode($jabatanData); ?>;
let bagianData = <?php echo json_encode($bagianData); ?>;
let unsurData = <?php echo json_encode($unsurData); ?>;
let originalOrder = [...jabatanData]; // Store original order for cancel functionality
let sortableInstances = [];
let changes = [];

// Update jabatan counts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateJabatanCounts();
    initializeSortable();
});

// Initialize Sortable
function initializeSortable() {
    // Destroy existing instances
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
    
    // Initialize new instances for each jabatan container
    document.querySelectorAll('.sortable-container').forEach(container => {
        const sortable = new Sortable(container, {
            group: 'jabatan', // Allow dragging between containers
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-dragging',
            handle: '.drag-handle',
            
            onEnd: function(evt) {
                handleJabatanMove(evt);
            }
        });
        
        sortableInstances.push(sortable);
    });
}

// Handle jabatan movement between containers
function handleJabatanMove(evt) {
    const jabatanElement = evt.item;
    const jabatanId = jabatanElement.dataset.id;
    const oldBagianId = evt.from.dataset.bagianId;
    const oldUnsurId = evt.from.dataset.unsurId;
    const newBagianId = evt.to.dataset.bagianId;
    const newUnsurId = evt.to.dataset.unsurId;
    const newIndex = evt.newIndex;
    
    // Update visual state
    jabatanElement.dataset.bagianId = newBagianId;
    jabatanElement.dataset.unsurId = newUnsurId;
    jabatanElement.dataset.urutan = newIndex + 1;
    
    // Track change
    const change = {
        jabatan_id: jabatanId,
        old_bagian_id: oldBagianId,
        old_unsur_id: oldUnsurId,
        new_bagian_id: newBagianId,
        new_unsur_id: newUnsurId,
        new_urutan: newIndex + 1
    };
    
    // Remove existing change for this jabatan if any
    changes = changes.filter(c => c.jabatan_id !== jabatanId);
    changes.push(change);
    
    // Show save/cancel buttons
    showSaveButton();
    
    // Update counts
    updateJabatanCounts();
}

// Show save/cancel buttons
function showSaveButton() {
    document.getElementById('saveOrderBtn').style.display = 'inline-block';
    document.getElementById('cancelOrderBtn').style.display = 'inline-block';
}

// Hide save/cancel buttons
function hideSaveButton() {
    document.getElementById('saveOrderBtn').style.display = 'none';
    document.getElementById('cancelOrderBtn').style.display = 'none';
}

// Save order function
function saveOrder() {
    const containers = document.querySelectorAll('.sortable-container');
    const orders = [];
    
    containers.forEach(container => {
        const bagianId = container.dataset.bagianId;
        const unsurId = container.dataset.unsurId;
        const items = container.querySelectorAll('.bagian-item');
        
        items.forEach((item, index) => {
            orders.push({
                id: item.dataset.id,
                id_bagian: bagianId === 'unassigned' ? null : bagianId,
                id_unsur: unsurId,
                urutan: index + 1
            });
        });
    });
    
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    fetch('../api/jabatan_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({
            action: 'update_order',
            orders: JSON.stringify(orders)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            hideSaveButton();
            changes = [];
            
            // Update original order to reflect saved changes
            originalOrder = [...jabatanData];
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan urutan');
    });
}

// Cancel order function
function cancelOrder() {
    // Restore original order
    restoreOriginalOrder();
    hideSaveButton();
    changes = [];
}

// Restore original order
function restoreOriginalOrder() {
    // Reload page to restore original order
    window.location.reload();
}

function updateJabatanCounts() {
    // Group jabatan by unsur, then by bagian
    const jabatanByUnsur = {};
    
    // Initialize unsur structure with all bagians
    unsurData.forEach(unsur => {
        jabatanByUnsur[unsur.id] = {
            nama_unsur: unsur.nama_unsur,
            urutan: unsur.urutan,
            bagians: {}
        };
        
        // Add all bagians that belong to this unsur
        bagianData.forEach(bagian => {
            if (bagian.id_unsur == unsur.id) {
                jabatanByUnsur[unsur.id].bagians[bagian.id] = {
                    nama_bagian: bagian.nama_bagian,
                    urutan: bagian.urutan,
                    jabatans: []
                };
            }
        });
    });
    
    // Group jabatan by unsur and bagian
    jabatanData.forEach(jabatan => {
        const unsurId = jabatan.id_unsur;
        let bagianId = jabatan.id_bagian;
        
        // If jabatan has no bagian, assign to first bagian in the same unsur
        if (!bagianId) {
            const bagianInUnsur = bagianData.find(b => b.id_unsur == unsurId);
            if (bagianInUnsur) {
                bagianId = bagianInUnsur.id;
            }
        }
        
        // Create unsur if not exists
        if (!jabatanByUnsur[unsurId]) {
            jabatanByUnsur[unsurId] = {
                nama_unsur: jabatan.nama_unsur,
                urutan: jabatan.urutan_unsur,
                bagians: {}
            };
        }
        
        // Create bagian within unsur if not exists
        if (!jabatanByUnsur[unsurId].bagians[bagianId]) {
            const bagianInfo = bagianData.find(b => b.id == bagianId);
            jabatanByUnsur[unsurId].bagians[bagianId] = {
                nama_bagian: bagianInfo ? bagianInfo.nama_bagian : 'Unassigned Jabatan',
                urutan: bagianInfo ? bagianInfo.urutan : 999,
                jabatans: []
            };
        }
        
        // Add jabatan to bagian
        jabatanByUnsur[unsurId].bagians[bagianId].jabatans.push(jabatan);
    });
    
    // Count total bagians
    let totalBagians = 0;
    Object.values(jabatanByUnsur).forEach(unsur => {
        totalBagians += Object.keys(unsur.bagians).length;
    });
    // Sort unsur by order, then bagian by order, then jabatan by order
    const sortedUnsurIds = Object.keys(jabatanByUnsur).sort((a, b) => {
        return jabatanByUnsur[a].urutan - jabatanByUnsur[b].urutan;
    });
    
    // Display jabatan by unsur -> bagian
    let html = '';
    sortedUnsurIds.forEach(unsurId => {
        const unsur = jabatanByUnsur[unsurId];
        const totalJabatan = Object.values(unsur.bagians).reduce((sum, bagian) => sum + bagian.jabatans.length, 0);
        const totalPersonil = Object.values(unsur.bagians).reduce((sum, bagian) => 
            sum + bagian.jabatans.reduce((sum2, jabatan) => sum2 + jabatan.personil_count, 0), 0);
        
        html += `
            <div class="row mb-4">
                <div class="col-12">
                    <div class="jabatan-card">
                        <div class="unsur-header">
                            <h5><i class="fas fa-sitemap me-2"></i>${unsur.nama_unsur}</h5>
                            <small>${totalJabatan} jabatan</small>
                            <span class="jabatan-count float-end">${totalPersonil} personil</span>
                        </div>
                        <div class="unsur-container">
        `;
        
        // Sort bagian by order, then by name
        const sortedBagianIds = Object.keys(unsur.bagians).sort((a, b) => {
            const bagianA = unsur.bagians[a];
            const bagianB = unsur.bagians[b];
            
            // First sort by urutan
            if (bagianA.urutan !== bagianB.urutan) {
                return bagianA.urutan - bagianB.urutan;
            }
            
            // Then sort by name
            return bagianA.nama_bagian.localeCompare(bagianB.nama_bagian);
        });
        
        sortedBagianIds.forEach(bagianId => {
            const bagian = unsur.bagians[bagianId];
            const bagianPersonil = bagian.jabatans.reduce((sum, jabatan) => sum + jabatan.personil_count, 0);
            
            html += `
                <div class="bagian-section">
                    <div class="bagian-header">
                        <h6><i class="fas fa-building me-2"></i>${bagian.nama_bagian}</h6>
                        <small>${bagian.jabatans.length} jabatan</small>
                        <span class="badge bg-info float-end">${bagianPersonil} personil</span>
                    </div>
                    <div class="jabatan-list sortable-container" data-unsur-id="${unsurId}" data-bagian-id="${bagianId}">
                        ${bagian.jabatans.length > 0 ? bagian.jabatans.map(jabatan => `
                            <div class="bagian-item" data-id="${jabatan.id}" data-unsur-id="${unsurId}" data-bagian-id="${bagianId}" data-urutan="${jabatan.urutan || 0}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div>
                                            <strong>${jabatan.nama_jabatan}</strong>
                                            <small class="text-muted d-block">${jabatan.personil_count} personil</small>
                                        </div>
                                    </div>
                                    <div class="bagian-actions">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewJabatan(${jabatan.id})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editJabatan(${jabatan.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteJabatan(${jabatan.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('') : '<div class="text-muted text-center p-3">Belum ada jabatan</div>'}
                    </div>
                </div>
            `;
        });
        
        html += `
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('jabatanContainer').innerHTML = html;
}

function filterByUnsur() {
    const unsurId = document.getElementById('unsurFilter').value;
    
    if (unsurId) {
        const filteredJabatan = jabatanData.filter(j => j.id_unsur == unsurId);
        displayFilteredJabatan(filteredJabatan);
    } else {
        updateJabatanCounts();
    }
}

function displayFilteredJabatan(filteredJabatan) {
    let html = '';
    if (filteredJabatan.length > 0) {
        html = `
            <div class="row">
                <div class="col-12">
                    <div class="jabatan-card">
                        <div class="unsur-header">
                            <h6>Hasil Filter</h6>
                            <small>${filteredJabatan.length} jabatan</small>
                            <span class="jabatan-count float-end">${filteredJabatan.reduce((sum, j) => sum + j.personil_count, 0)} personil</span>
                        </div>
                        <div class="bagian-container">
                            ${filteredJabatan.map(jabatan => `
                                <div class="bagian-item" data-jabatan-id="${jabatan.id}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${jabatan.nama_jabatan}</strong>
                                            <small class="text-muted d-block">${jabatan.personil_count} personil</small>
                                        </div>
                                        <div class="bagian-actions">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewJabatan(${jabatan.id})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editJabatan(${jabatan.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteJabatan(${jabatan.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        html = '<div class="alert alert-info">Tidak ada jabatan yang ditemukan untuk filter ini.</div>';
    }
    
    document.getElementById('jabatanContainer').innerHTML = html;
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Jabatan';
    document.getElementById('formAction').value = 'create_jabatan';
    document.getElementById('formId').value = '';
    document.getElementById('nama_jabatan').value = '';
    document.getElementById('kode_jabatan').value = '';
    document.getElementById('id_unsur').value = '';
    
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

function editJabatan(jabatanId) {
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    fetch('../api/jabatan_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({
            action: 'get_jabatan_detail',
            id: jabatanId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const jabatan = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Jabatan';
            document.getElementById('formAction').value = 'update_jabatan';
            document.getElementById('formId').value = jabatan.id;
            document.getElementById('nama_jabatan').value = jabatan.nama_jabatan || '';
            document.getElementById('kode_jabatan').value = jabatan.kode_jabatan || '';
            document.getElementById('id_unsur').value = jabatan.id_unsur || '';
            
            new bootstrap.Modal(document.getElementById('jabatanModal')).show();
        } else {
            showAlert('danger', 'Error: Jabatan tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat mengambil data jabatan');
    });
}

function deleteJabatan(jabatanId) {
    if (confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) {
        const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
        fetch('../api/jabatan_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                action: 'delete_jabatan',
                id: jabatanId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat menghapus data');
        });
    }
}

function viewJabatan(jabatanId) {
    const jabatan = jabatanData.find(j => j.id == jabatanId);
    
    document.getElementById('viewJabatanNama').textContent = jabatan ? jabatan.nama_jabatan : '';
    document.getElementById('viewJabatanUnsur').textContent = jabatan ? jabatan.nama_unsur : '';
    
    // Get personil data for this jabatan
    fetch('<?php echo API_BASE_URL; ?>/personil_simple.php?limit=1000')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const personilList = data.data.personil.filter(p => p.id_jabatan == jabatanId);
                
                let personilHtml = '';
                if (personilList.length > 0) {
                    personilHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NRP</th>
                                        <th>Pangkat</th>
                                        <th>Bagian</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    personilList.forEach((personil, i) => {
                        personilHtml += `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${personil.nama || ''}</td>
                                <td>${personil.nrp || ''}</td>
                                <td>${personil.pangkat || ''}</td>
                                <td>${personil.bagian || ''}</td>
                            </tr>
                        `;
                    });
                    
                    personilHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    personilHtml = '<p class="text-muted">Belum ada personil untuk jabatan ini.</p>';
                }
                
                document.getElementById('viewJabatanPersonil').innerHTML = personilHtml;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('viewJabatanModal')).show();
            } else {
                showAlert('danger', 'Gagal mengambil data personil');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat mengambil data personil');
        });
}

function exportData() {
    // Simple export to text
    let text = "DAFTAR JABATAN POLRES SAMOSIR\n\n";
    
    jabatanData.forEach((jabatan, index) => {
        text += `${index + 1}. ${jabatan.nama_jabatan}\n`;
        text += `   Unsur: ${jabatan.nama_unsur}\n`;
        text += `   Jumlah Personil: ${jabatan.personil_count}\n`;
        text += `   Status: ${jabatan.personil_count > 0 ? 'Aktif' : 'Kosong'}\n\n`;
    });
    
    // Create blob and download
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'daftar_jabatan_polres_samosir.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function refreshData() {
    window.location.reload();
}

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Handle form submission
document.getElementById('jabatanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    formData.append('csrf_token', csrfToken);

    fetch('../api/jabatan_api.php', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan data');
    });
});
</script>
