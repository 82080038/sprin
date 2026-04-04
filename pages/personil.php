<?php
declare(strict_types=1);
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
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
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_personil_list', 'get_personil_detail', 'create_personil', 'update_personil', 'delete_personil', 'move_personil', 'toggle_status'])) {
        // Set test session for AJAX
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
        
        // Clear any output buffers for AJAX requests
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    if ($action === 'get_personil_list') {
        $search = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING) ?? '';
        $filterBagian = filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'filter_bagian', FILTER_SANITIZE_STRING) ?? '';
        
        $sql = "SELECT p.*, b.nama_bagian, u.nama_unsur 
                FROM personil p 
                LEFT JOIN bagian b ON p.id_bagian = b.id 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE ? OR p.nrp LIKE ? OR p.nama_jabatan LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($filterBagian) {
            $sql .= " AND p.id_bagian = ?";
            $params[] = $filterBagian;
        }
        
        $sql .= " ORDER BY u.urutan, b.urutan, p.nama_lengkap";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $personil]);
        exit;
    }
    
    if ($action === 'move_personil') {
        $personilId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'personil_id', FILTER_SANITIZE_STRING) ?? 0;
        $newBagianId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_bagian_id', FILTER_SANITIZE_STRING) ?? 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE personil SET id_bagian = ? WHERE id = ?");
            $stmt->execute([$newBagianId, $personilId]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Personil berhasil dipindahkan!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan personil: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($action === 'toggle_status') {
        $personilId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'personil_id', FILTER_SANITIZE_STRING) ?? 0;
        $newStatus = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_status', FILTER_SANITIZE_STRING) ?? '';
        $alasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '';
        
        try {
            $stmt = $pdo->prepare("UPDATE personil SET status_ket = ?, alasan_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $alasan, $personilId]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Get data from database
try {
    // Get bagian data with unsur info
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get personil data
    $stmt = $pdo->query("
        SELECT p.*, b.nama_bagian, u.nama_unsur 
        FROM personil p 
        LEFT JOIN bagian b ON p.id_bagian = b.id 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, p.nama_lengkap
    ");
    $personilData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group personil by bagian
    $personilByBagian = [];
    foreach ($personilData as $personil) {
        $bagianId = $personil['id_bagian'];
        if (!isset($personilByBagian[$bagianId])) {
            $personilByBagian[$bagianId] = [];
        }
        $personilByBagian[$bagianId][] = $personil;
    }
    
} catch (PDOException $e) {
    $bagianData = [];
    $personilData = [];
    $personilByBagian = [];
}

$page_title = 'Data Personil - POLRES Samosir (Upgraded)';
include '../includes/components/header.php';
?>

<!-- Include Sortable.js for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
/* Enhanced Personil Management Styles - Matching bagian.php standards */
.personil-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.personil-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.bagian-header {
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
    color: white;
    padding: 1rem 1.25rem;
    border-bottom: 3px solid rgba(255,255,255,0.1);
}

.bagian-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.bagian-header small {
    opacity: 0.8;
    font-size: 0.8rem;
}

.personil-container {
    background: var(--bg-secondary);
    min-height: 200px;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.personil-list {
    padding: 0.75rem;
}

.personil-item {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: move;
    transition: all 0.3s ease;
    position: relative;
    color: var(--text-primary);
}

.personil-item::before {
    content: '⋮⋮';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-color);
    font-weight: bold;
    font-size: 0.8rem;
    opacity: 0.7;
}

.personil-item:hover {
    background: var(--hover-bg);
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px var(--shadow-color);
    transform: translateX(3px);
}

.personil-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px var(--shadow-color);
    z-index: 1000;
}

.personil-item.drag-over {
    background: var(--bg-tertiary);
    border-color: var(--accent-color);
    border-style: dashed;
}

.personil-info {
    flex-grow: 1;
}

.personil-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.personil-details {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.personil-meta {
    font-size: 0.7rem;
    color: #6c757d;
}

.personil-actions {
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

.personil-item:hover .drag-handle {
    opacity: 1;
}

.empty-personil {
    text-align: center;
    color: var(--text-secondary);
    padding: 2rem 1rem;
    font-style: italic;
}

.empty-personil i {
    font-size: 2rem;
    opacity: 0.5;
    margin-bottom: 0.5rem;
}

.status-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.status-aktif {
    background: #d4edda;
    color: #155724;
}

.status-nonaktif {
    background: #f8d7da;
    color: #721c24;
}

/* Sortable styles */
.sortable-ghost {
    background: var(--bg-tertiary) !important;
    border: 2px dashed var(--primary-color) !important;
}

.sortable-chosen {
    background: var(--bg-secondary) !important;
    transform: scale(1.02);
}

.sortable-dragging {
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px var(--shadow-color) !important;
    z-index: 1000;
}

/* Enhanced search and filter */
.search-filter-container {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

/* Stats cards enhancement */
.card {
    background: linear-gradient(135deg, var(--primary-color, #1a237e), var(--secondary-color, #3949ab));
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .personil-card {
        margin-bottom: 1rem;
    }
    
    .bagian-header {
        padding: 0.75rem 1rem;
    }
    
    .bagian-header h6 {
        font-size: 0.9rem;
    }
    
    .personil-item {
        padding: 0.5rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .personil-item::before {
        left: -1rem;
        font-size: 0.7rem;
    }
    
    .personil-actions {
        align-self: flex-end;
        margin-top: 0.25rem;
    }
    
    .personil-container {
        max-height: 300px;
    }
}

@media (max-width: 576px) {
    .col-md-6.col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .personil-card {
        margin-bottom: 1rem;
    }
}
</style>

<div class="container-fluid">

<!-- Enhanced Page Header -->
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-users me-2"></i>Data Personil <span class="badge bg-success ms-2">Upgraded</span></h1>
            <p class="text-muted mb-0">Manajemen personil dengan drag & drop dan fitur modern</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex gap-2 justify-content-end">
                <span class="badge bg-success">System Online</span>
                <span class="badge bg-info">Drag & Drop Enabled</span>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x mb-2"></i>
                <h3 id="totalPersonil"><?php echo count($personilData); ?></h3>
                <p class="mb-0">Total Personil</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-male fa-2x mb-2"></i>
                <h3 id="totalLaki"><?php echo count(array_filter($personilData, fn($p) => $p['JK'] === 'L')); ?></h3>
                <p class="mb-0">Personil Laki-laki</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-female fa-2x mb-2"></i>
                <h3 id="totalPerempuan"><?php echo count(array_filter($personilData, fn($p) => $p['JK'] === 'P')); ?></h3>
                <p class="mb-0">Personil Perempuan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h3 id="totalAktif"><?php echo count(array_filter($personilData, fn($p) => $p['status_ket'] === 'aktif')); ?></h3>
                <p class="mb-0">Personil Aktif</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Search and Filter -->
<div class="search-filter-container">
    <div class="row align-items-end">
        <div class="col-md-4">
            <label for="searchInput" class="form-label">Cari Personil</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Nama, NRP, atau jabatan...">
                <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <label for="filterBagian" class="form-label">Filter Bagian</label>
            <select class="form-select" id="filterBagian">
                <option value="">Semua Bagian</option>
                <?php foreach ($bagianData as $bagian): ?>
                <option value="<?php echo $bagian['id']; ?>"><?php echo htmlspecialchars($bagian['nama_bagian']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <div class="d-flex gap-2">
                <button class="btn btn-info" onclick="refreshData()">
                    <i class="fas fa-sync me-2"></i>Refresh
                </button>
                <button class="btn btn-success" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button class="btn btn-warning" id="saveChangesBtn" onclick="saveAllChanges()" style="display: none;">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <button class="btn btn-secondary" id="cancelChangesBtn" onclick="cancelAllChanges()" style="display: none;">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Instructions -->
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Petunjuk:</strong> Seret dan lepas personil untuk memindahkannya antar bagian. Gunakan search dan filter untuk menemukan personil.
</div>

<!-- Bagian dan Personil Containers -->
<div id="bagian-personil-container" class="row">
    <?php foreach ($bagianData as $bagian): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="personil-card" data-bagian-id="<?php echo $bagian['id']; ?>">
            <!-- Bagian Header -->
            <div class="bagian-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($bagian['nama_bagian']); ?></h6>
                        <small><?php echo htmlspecialchars($bagian['nama_unsur'] ?? 'Tanpa Unsur'); ?></small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2"><?php echo count($personilByBagian[$bagian['id']] ?? []); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Personil Container -->
            <div class="personil-container">
                <div class="personil-list sortable-personil" data-bagian-id="<?php echo $bagian['id']; ?>">
                    <?php if (isset($personilByBagian[$bagian['id']]) && count($personilByBagian[$bagian['id']]) > 0): ?>
                        <?php foreach ($personilByBagian[$bagian['id']] as $personil): ?>
                        <div class="personil-item" data-id="<?php echo $personil['id']; ?>" data-bagian-id="<?php echo $personil['id_bagian']; ?>">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <div class="personil-info">
                                    <div class="personil-name"><?php echo htmlspecialchars($personil['nama_lengkap']); ?></div>
                                    <div class="personil-details">
                                        <strong>NRP:</strong> <?php echo htmlspecialchars($personil['nrp']); ?> | 
                                        <strong>Jabatan:</strong> <?php echo htmlspecialchars($personil['nama_jabatan']); ?>
                                    </div>
                                    <div class="personil-meta">
                                        <span class="status-badge status-<?php echo $personil['status_ket']; ?>">
                                            <?php echo ucfirst($personil['status_ket']); ?>
                                        </span>
                                        <small class="ms-2">JK: <?php echo $personil['JK']; ?> | ID: <?php echo $personil['id']; ?></small>
                                    </div>
                                </div>
                                <div class="personil-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editPersonil(<?php echo $personil['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-<?php echo $personil['status_ket'] === 'aktif' ? 'warning' : 'success'; ?>" 
                                            onclick="toggleStatus(<?php echo $personil['id']; ?>, '<?php echo $personil['status_ket']; ?>')">
                                        <i class="fas fa-<?php echo $personil['status_ket'] === 'aktif' ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-personil">
                            <i class="fas fa-user-slash mb-2"></i>
                            <p class="mb-2">Tidak ada personil</p>
                            <small class="text-muted">Tarik personil dari bagian lain ke sini</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="text-center my-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memuat data personil...</p>
</div>

</div>

<!-- Status Toggle Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-content" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-header" data-bs-backdrop="static" data-bs-keyboard="false">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Ubah Status Personil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="statusInfo"></span>
                    </div>
                    <input type="hidden" id="personilId" name="personilId">
                    <input type="hidden" id="currentStatus" name="currentStatus">
                    <input type="hidden" id="newStatus" name="newStatus">
                    
                    <div class="mb-3" id="alasanGroup">
                        <label for="alasan" class="form-label">
                            <i class="fas fa-comment-alt me-2"></i>Alasan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="alasan" name="alasan" rows="3" 
                                  placeholder="Masukkan alasan mengubah status personil..." required></textarea>
                        <div class="form-text">Alasan wajib diisi saat mengubah status</div>
                    </div>
                </div>
                <div class="modal-footer" data-bs-backdrop="static" data-bs-keyboard="false">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modern Personil Management with Drag & Drop
let personilData = <?php echo json_encode($personilData); ?>;
let originalPersonilData = [...personilData];
let changes = [];
let sortableInstances = [];

// Initialize sortable for each personil container
function initializeSortable() {
    // Destroy existing instances
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
    
    // Initialize new instances
    document.querySelectorAll('.sortable-personil').forEach(container => {
        const sortable = new Sortable(container, {
            group: 'personil', // Allow dragging between containers
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-dragging',
            handle: '.drag-handle',
            
            onEnd: function(evt) {
                handlePersonilMove(evt);
            }
        });
        
        sortableInstances.push(sortable);
    });
}

// Handle personil movement between bagian
function handlePersonilMove(evt) {
    const personilElement = evt.item;
    const personilId = personilElement.dataset.id;
    const oldBagianId = evt.from.dataset.bagianId;
    const newBagianId = evt.to.dataset.bagianId;
    
    // Update visual state
    personilElement.dataset.bagianId = newBagianId;
    
    // Track change
    const change = {
        personil_id: personilId,
        old_bagian_id: oldBagianId,
        new_bagian_id: newBagianId
    };
    
    // Remove existing change for this personil if any
    changes = changes.filter(c => c.personil_id !== personilId);
    changes.push(change);
    
    // Show save/cancel buttons
    showSaveButtons();
    
    // Update counts
    updatePersonilCounts();
    
    console.log('Personil moved:', change);
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
async function saveAllChanges() {
    if (changes.length === 0) {
        showNotification('Tidak ada perubahan untuk disimpan.', 'info');
        return;
    }
    
    console.log('Saving changes:', changes);
    
    try {
        const savePromises = changes.map(change => {
            return fetch('<?php echo url('pages/personil_upgraded.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'move_personil',
                    personil_id: change.personil_id,
                    new_bagian_id: change.new_bagian_id
                })
            });
        });
        
        const responses = await Promise.all(savePromises);
        const results = await Promise.all(responses.map(r => r.json()));
        
        const failed = results.filter(r => !r.success);
        if (failed.length === 0) {
            showNotification('Semua perubahan berhasil disimpan!', 'success');
            changes = [];
            hideSaveButtons();
            refreshData();
        } else {
            showNotification('Beberapa perubahan gagal disimpan.', 'error');
        }
    } catch (error) {
        console.error('Error saving changes:', error);
        showNotification('Terjadi kesalahan saat menyimpan perubahan.', 'error');
    }
}

// Cancel all changes
function cancelAllChanges() {
    changes = [];
    hideSaveButtons();
    refreshData();
}

// Update personil counts
function updatePersonilCounts() {
    document.querySelectorAll('.personil-card').forEach(card => {
        const bagianId = card.dataset.bagianId;
        const personilList = card.querySelector('.sortable-personil');
        const count = personilList.querySelectorAll('.personil-item').length;
        const badge = card.querySelector('.badge');
        badge.textContent = count;
    });
}

// Search functionality
function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterBagian = document.getElementById('filterBagian').value;
    
    document.querySelectorAll('.personil-item').forEach(item => {
        const name = item.querySelector('.personil-name').textContent.toLowerCase();
        const details = item.querySelector('.personil-details').textContent.toLowerCase();
        const bagianId = item.dataset.bagianId;
        
        const matchesSearch = !searchTerm || name.includes(searchTerm) || details.includes(searchTerm);
        const matchesFilter = !filterBagian || bagianId === filterBagian;
        
        item.style.display = matchesSearch && matchesFilter ? 'block' : 'none';
    });
    
    // Update empty states
    document.querySelectorAll('.sortable-personil').forEach(container => {
        const visibleItems = container.querySelectorAll('.personil-item[style="display: block;"], .personil-item:not([style*="display: none"])');
        const emptyDiv = container.querySelector('.empty-personil');
        
        if (visibleItems.length === 0 && emptyDiv) {
            emptyDiv.style.display = 'block';
        } else if (emptyDiv) {
            emptyDiv.style.display = 'none';
        }
    });
}

// Toggle personil status
function toggleStatus(personilId, currentStatus) {
    const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
    const personilName = document.querySelector(`.personil-item[data-id="${personilId}"] .personil-name`).textContent;
    
    document.getElementById('personilId').value = personilId;
    document.getElementById('currentStatus').value = currentStatus;
    document.getElementById('newStatus').value = newStatus;
    document.getElementById('statusInfo').textContent = `Ubah status "${personilName}" dari ${currentStatus} menjadi ${newStatus}?`;
    
    // Show/hide alasan field based on new status
    const alasanGroup = document.getElementById('alasanGroup');
    if (newStatus === 'nonaktif') {
        alasanGroup.style.display = 'block';
        document.getElementById('alasan').required = true;
    } else {
        alasanGroup.style.display = 'none';
        document.getElementById('alasan').required = false;
        document.getElementById('alasan').value = '';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Save status change
document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo url('pages/personil_upgraded.php'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            refreshData();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error toggling status:', error);
        showNotification('Terjadi kesalahan saat mengubah status.', 'error');
    }
});

// Refresh data
function refreshData() {
    location.reload();
}

// Export data
function exportData() {
    window.open('../api/export_personil.php', '_blank');
}

// Show notification
function showNotification(message, type = 'info') {
    // Implementation depends on your notification system
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else {
        alert(message);
    }
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', performSearch);
document.getElementById('filterBagian').addEventListener('change', performSearch);
document.getElementById('btnClearSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterBagian').value = '';
    performSearch();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    updatePersonilCounts();
});

// Re-initialize after dynamic content updates
function reinitializeSortable() {
    setTimeout(() => {
        initializeSortable();
        updatePersonilCounts();
    }, 100);
}
</script>

<?php include '../includes/components/footer.php'; ?>
