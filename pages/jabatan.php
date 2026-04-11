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
/* Jabatan Management Styles (Development Mode) */
.jabatan-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.unsur-header {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 1rem;
    border-radius: 8px 8px 0 0;
}

.unsur-header h5,
.unsur-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
    color: var(--text-light);
}

.unsur-header small {
    font-size: 0.85rem;
    color: var(--text-light);
}

.bagian-container {
    background: var(--bg-hover);
    min-height: 200px;
    padding: 0.75rem;
    border-radius: 0 0 8px 8px;
}

.bagian-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 0.5rem;
    margin-bottom: 0.25rem;
    cursor: grab;
    position: relative;
    font-size: 0.85rem;
}

.bagian-item strong {
    display: block;
    font-size: 0.85rem;
    line-height: 1.25;
}

.bagian-item::before {
    content: '---';
    position: absolute;
    left: -1rem;
    font-size: 0.75rem;
}

.bagian-item.dragging {
    opacity: 0.5;
}

.bagian-item.drag-over {
    background: #fff3cd;
    border-color: var(--bs-warning);
}

.sortable-ghost {
    opacity: 0.4;
    background: var(--bg-hover);
}

.bagian-actions {
    display: flex;
    gap: 0.25rem;
}

.bagian-actions .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.drag-handle {
    display: inline-flex;
    align-items: center;
    cursor: grab;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-right: 0.5rem;
    padding: 0.25rem;
    border-radius: 4px;
}

.drag-handle:hover {
    color: var(--bs-info);
}

.drag-handle:active {
    cursor: grabbing;
}

.jabatan-count {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-save {
    background: var(--bs-success);
    border: none;
    color: var(--text-light);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 600;
}

.btn-cancel {
    background: var(--text-muted);
    border: none;
    color: var(--text-light);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 600;
    margin-left: 0.5rem;
}

.stats-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.stats-card .number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
}

.unsur-container {
    background: #f8f9fa;
    min-height: 100px;
    max-height: 400px;
    overflow-y: auto;
    padding: 0.5rem;
    border-radius: 0 0 8px 8px;
}

.bagian-section {
    background: white;
    border-radius: 4px;
    padding: 0.25rem;
    margin-bottom: 0.25rem;
    border: 1px solid #e9ecef;
}

.bagian-header {
    background: #6c757d;
    color: white;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.bagian-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.bagian-header small {
    font-size: 0.85rem;
}

.jabatan-list {
    min-height: 50px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .bagian-item {
        padding: 0.5rem;
    }
    
    .bagian-item::before {
        left: -0.8rem;
        font-size: 0.7rem;
    }
    
    .bagian-actions {
        margin-top: 0.25rem;
    }
    
    .bagian-container {
        max-height: 300px;
    }
    
    .drag-handle {
        font-size: 1rem;
        padding: 0.25rem;
    }
}

/* Grid layout */
#jabatanContainer > .row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.5rem;
}

#jabatanContainer > .row > .col-lg-6,
#jabatanContainer > .row > .col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    width: 50%;
    padding: 0.5rem;
    box-sizing: border-box;
}

/* Mobile: 1 column */
@media (max-width: 767px) {
    #jabatanContainer > .row > .col-lg-6,
    #jabatanContainer > .row > .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
        width: 100%;
        padding: 0.5rem;
    }
}
</style>

<div class="page-header">
    <h1>Manajemen Jabatan</h1>
    <p class="text-muted">Kelola dan atur jabatan dalam setiap unsur organisasi POLRES Samosir</p>
</div>

<!-- Statistics -->
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
<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <button class="btn btn-primary" onclick="openAddModal()">Tambah Jabatan</button>
    <button class="btn btn-success" id="saveOrderBtn" onclick="saveOrder()" style="display: none;">Simpan Urutan</button>
    <button class="btn btn-warning" id="cancelOrderBtn" onclick="cancelOrder()" style="display: none;">Batal</button>
    <button class="btn btn-info" onclick="refreshData()">Refresh</button>
    <button class="btn btn-success" onclick="exportData()">Export</button>
    <select class="form-select" id="unsurFilter" onchange="filterByUnsur()" style="width: auto;">
        <option value="">Semua Unsur</option>
        <?php foreach ($unsurData as $unsur): ?>
            <option value="<?php echo $unsur['id']; ?>"><?php echo htmlspecialchars($unsur['nama_unsur']); ?></option>
        <?php endforeach; ?>
    </select>
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
                        <div class="form-text">Kode jabatan akan dibuat otomatis dari nama jabatan</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="id_unsur" name="id_unsur" required onchange="updateBagianOptions()">
                            <option value="">Pilih Unsur</option>
                            <?php foreach ($unsurData as $unsur): ?>
                                <option value="<?php echo $unsur['id']; ?>"><?php echo htmlspecialchars($unsur['nama_unsur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_bagian" class="form-label">Bagian</label>
                        <select class="form-select" id="id_bagian" name="id_bagian">
                            <option value="">Pilih Unsur terlebih dahulu</option>
                        </select>
                        <div class="form-text">Pilih bagian dalam unsur (opsional)</div>
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
// Ensure window.APP_CONFIG exists (fallback if header.php doesn't define it)
if (!window.APP_CONFIG) {
    window.APP_CONFIG = {
        baseUrl: '<?php echo BASE_URL; ?>',
        apiUrl: '<?php echo API_BASE_URL; ?>',
        csrfToken: '',
        debugMode: true
    };
}

let jabatanData = <?php echo json_encode($jabatanData); ?>;
let bagianData = <?php echo json_encode($bagianData); ?>;
let unsurData = <?php echo json_encode($unsurData); ?>;
let originalOrder = [...jabatanData]; // Store original order for cancel functionality
let sortableInstances = [];
let changes = [];

// Update jabatan counts on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Initialize CSRF token if not set
    if (!window.APP_CONFIG.csrfToken) {
        showToast('info', '⏳ Inisialisasi session...', 3000);
        const token = await refreshCSRFToken();
        if (token) {
            showToast('success', '✅ Session siap!', 2000);
        } else {
            showToast('warning', '⚠️ Gagal inisialisasi session. Refresh halaman.', 5000);
        }
    }
    
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
            group: {
                name: 'jabatan',
                pull: true,
                put: true
            },
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            delay: 0,
            touchStartThreshold: 5,
            
            onStart: function(evt) {
                evt.item.classList.add('dragging');
                document.body.style.cursor = 'grabbing';
            },
            
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                document.body.style.cursor = '';
                handleJabatanMove(evt);
            },
            
            onAdd: function(evt) {
                console.log('Jabatan moved to new bagian:', evt.to.dataset.bagianId);
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
    
    // Check if moved to different bagian
    const isCrossBagianMove = oldBagianId !== newBagianId;
    
    // Visual feedback for cross-bagian move
    if (isCrossBagianMove) {
        jabatanElement.style.border = '2px dashed #007bff';
        setTimeout(() => {
            jabatanElement.style.border = '';
        }, 1000);
        
        // Show toast notification
        const bagianName = evt.to.closest('.bagian-section')?.querySelector('.bagian-header h6')?.textContent || 'Bagian baru';
        showToast('info', `📦 Jabatan dipindahkan ke ${bagianName}`, 3000);
    }
    
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
        new_urutan: newIndex + 1,
        is_cross_bagian: isCrossBagianMove
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
async function saveOrder() {
    const containers = document.querySelectorAll('.sortable-container');
    const orders = [];
    let crossBagianCount = 0;
    
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
    
    // Check if any cross-bagian moves
    crossBagianCount = changes.filter(c => c.is_cross_bagian).length;
    
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    const options = {
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
    };
    
    // Show loading toast
    showToast('info', '⏳ Menyimpan urutan jabatan...', 3000);
    
    const { data } = await apiCallWithTokenRefresh('../api/jabatan_api.php', options);
    
    if (data.success) {
        let message = '✅ Urutan jabatan berhasil disimpan!';
        if (crossBagianCount > 0) {
            message += `<br><small>(${crossBagianCount} jabatan dipindahkan antar bagian)</small>`;
        }
        showToast('success', message, 8000);
        hideSaveButton();
        changes = [];
        originalOrder = [...jabatanData];
    } else {
        if (data.csrf_expired && !data.retry_with_fresh_token) {
            showToast('warning', '⚠️ Session expired. Please refresh the page.', 5000);
        } else {
            showPersistentToast('danger', '❌ ' + (data.message || 'Gagal menyimpan urutan'));
        }
    }
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
            <div class="col-lg-6 col-md-6">
                <div class="jabatan-card">
                    <div class="unsur-header">
                        <h5>${unsur.nama_unsur}</h5>
                        <small>${totalJabatan} jabatan</small>
                        <span class="jabatan-count float-end">${totalPersonil} personil</span>
                    </div>
                    <div class="unsur-container">
        `;
        
        // Sort bagian by order, then by name
        const sortedBagianIds = Object.keys(unsur.bagians).sort((a, b) => {
            const bagianA = unsur.bagians[a];
            const bagianB = unsur.bagians[b];
            
            if (bagianA.urutan !== bagianB.urutan) {
                return bagianA.urutan - bagianB.urutan;
            }
            
            return bagianA.nama_bagian.localeCompare(bagianB.nama_bagian);
        });
        
        sortedBagianIds.forEach(bagianId => {
            const bagian = unsur.bagians[bagianId];
            const bagianPersonil = bagian.jabatans.reduce((sum, jabatan) => sum + jabatan.personil_count, 0);
            
            html += `
                <div class="bagian-section">
                    <div class="bagian-header">
                        <h6>${bagian.nama_bagian}</h6>
                        <small>${bagian.jabatans.length} jabatan</small>
                        <span class="badge bg-info float-end">${bagianPersonil} personil</span>
                    </div>
                    <div class="jabatan-list sortable-container" data-unsur-id="${unsurId}" data-bagian-id="${bagianId}">
                        ${bagian.jabatans.length > 0 ? bagian.jabatans.map(jabatan => `
                            <div class="bagian-item" data-id="${jabatan.id}" data-unsur-id="${unsurId}" data-bagian-id="${bagianId}" data-urutan="${jabatan.urutan || 0}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="drag-handle">:::</div>
                                        <div>
                                            <strong>${jabatan.nama_jabatan}</strong>
                                            <small class="text-muted d-block">${jabatan.personil_count} personil</small>
                                        </div>
                                    </div>
                                    <div class="bagian-actions">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewJabatan(${jabatan.id})">Lihat</button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editJabatan(${jabatan.id})">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteJabatan(${jabatan.id})">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        `).join('') : '<div class="text-muted text-center p-3">Belum ada jabatan</div>'}
                    </div>
                </div>
            </div>
        </div>
        `;
        });
        
        html += `
                </div>
            </div>
        </div>
        `;
    });
    
    // Wrap in row
    html = `<div class="row">${html}</div>`;
    
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
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewJabatan(${jabatan.id})">Lihat</button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editJabatan(${jabatan.id})">Edit</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteJabatan(${jabatan.id})">Hapus</button>
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
    document.getElementById('id_unsur').value = '';
    document.getElementById('id_bagian').innerHTML = '<option value="">Pilih Unsur terlebih dahulu</option>';
    
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

// Update bagian options based on selected unsur
function updateBagianOptions() {
    const unsurId = document.getElementById('id_unsur').value;
    const bagianSelect = document.getElementById('id_bagian');
    
    if (!unsurId) {
        bagianSelect.innerHTML = '<option value="">Pilih Unsur terlebih dahulu</option>';
        return;
    }
    
    // Filter bagian data by unsur
    const filteredBagian = bagianData.filter(b => b.id_unsur == unsurId);
    
    if (filteredBagian.length === 0) {
        bagianSelect.innerHTML = '<option value="">Tidak ada bagian untuk unsur ini</option>';
        return;
    }
    
    let html = '<option value="">-- Pilih Bagian (Opsional) --</option>';
    filteredBagian.forEach(bagian => {
        html += `<option value="${bagian.id}">${bagian.nama_bagian}</option>`;
    });
    
    bagianSelect.innerHTML = html;
}

function editJabatan(jabatanId) {
    fetch('../api/jabatan_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
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
            document.getElementById('id_unsur').value = jabatan.id_unsur || '';
            
            // Update bagian options based on selected unsur
            updateBagianOptions();
            
            // Set bagian value after options are populated
            setTimeout(() => {
                document.getElementById('id_bagian').value = jabatan.id_bagian || '';
            }, 100);
            
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

async function deleteJabatan(jabatanId) {
    if (confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) {
        const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                action: 'delete_jabatan',
                id: jabatanId,
                csrf_token: csrfToken
            })
        };
        
        // Show loading toast
        showToast('info', '⏳ Menghapus jabatan...', 3000);
        
        const { data } = await apiCallWithTokenRefresh('../api/jabatan_api.php', options);
        
        if (data.success) {
            showToast('success', '✅ Jabatan berhasil dihapus!', 8000);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            if (data.csrf_expired && !data.retry_with_fresh_token) {
                showToast('warning', '⚠️ Session expired. Please refresh the page.', 5000);
            } else {
                showPersistentToast('danger', '❌ ' + (data.message || 'Gagal menghapus jabatan'));
            }
        }
    }
}

function viewJabatan(jabatanId) {
    const jabatan = jabatanData.find(j => j.id == jabatanId);
    
    document.getElementById('viewJabatanNama').textContent = jabatan ? jabatan.nama_jabatan : '';
    document.getElementById('viewJabatanUnsur').textContent = jabatan ? jabatan.nama_unsur : '';
    
    // Get personil data for this jabatan
    fetch('../api/personil_api.php?action=get_all&limit=1000')
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

// Auto-refresh CSRF token utility
async function refreshCSRFToken() {
    try {
        const response = await fetch('../api/jabatan_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'same-origin', // Important: send cookies
            body: new URLSearchParams({
                action: 'get_csrf_token'
            })
        });
        const data = await response.json();
        if (data.success && data.csrf_token) {
            // Ensure window.APP_CONFIG exists before setting
            if (!window.APP_CONFIG) {
                window.APP_CONFIG = {};
            }
            window.APP_CONFIG.csrfToken = data.csrf_token;
            return data.csrf_token;
        }
        throw new Error('Failed to get new token');
    } catch (error) {
        console.error('Error refreshing CSRF token:', error);
        return null;
    }
}

// Helper to make API calls with auto-refresh token
async function apiCallWithTokenRefresh(url, options, maxRetries = 1) {
    let retries = 0;
    
    // Ensure credentials are sent
    if (!options.credentials) {
        options.credentials = 'same-origin';
    }
    
    while (retries <= maxRetries) {
        const response = await fetch(url, options);
        const data = await response.json();
        
        // Check if CSRF token expired
        if (response.status === 403 && data.csrf_expired && retries < maxRetries) {
            console.log('CSRF token expired, refreshing...');
            const newToken = await refreshCSRFToken();
            
            if (newToken) {
                // Update token in request body if it's FormData/URLSearchParams
                if (options.body instanceof URLSearchParams) {
                    options.body.set('csrf_token', newToken);
                }
                // Update header
                if (options.headers && options.headers['X-CSRF-TOKEN']) {
                    options.headers['X-CSRF-TOKEN'] = newToken;
                }
                retries++;
                continue;
            }
        }
        
        return { response, data };
    }
    
    return { response: { status: 403 }, data: { success: false, message: 'Max retries exceeded' } };
}

// Handle form submission
document.getElementById('jabatanForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const csrfToken = window.APP_CONFIG ? window.APP_CONFIG.csrfToken : '';
    const formData = new URLSearchParams(new FormData(this));
    formData.append('csrf_token', csrfToken);

    const options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    };

    // Show loading toast (short duration for loading)
    const actionType = formData.get('formAction') === 'create_jabatan' ? 'menambah' : 'memperbarui';
    showToast('info', `⏳ Sedang ${actionType} jabatan...`, 3000);
    
    try {
        const { data } = await apiCallWithTokenRefresh('../api/jabatan_api.php', options);
        
        if (data.success) {
            const action = formData.get('formAction') === 'create_jabatan' ? 'ditambahkan' : 'diperbarui';
            const kodeInfo = data.kode_jabatan ? `<br><small>Kode: <strong>${data.kode_jabatan}</strong></small>` : '';
            // Success toast stays longer so user can see the result
            showToast('success', `✅ Jabatan berhasil ${action}!${kodeInfo}`, 8000);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            console.error('API Error:', data);
            if (data.csrf_expired) {
                showToast('warning', '⚠️ Session expired. Refreshing page...', 5000);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                // Error toast - persistent (user must click X to close)
                showPersistentToast('danger', '❌ ' + (data.message || 'Gagal menyimpan data jabatan'));
            }
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        // Network error - persistent so user can read and retry
        showPersistentToast('danger', '❌ Network error. Check connection and try again.');
    }
});
</script>
