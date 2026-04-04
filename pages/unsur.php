<?php
declare(strict_types=1);
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
/* Sortable Styles with Theme Variables */
.sortable-list {
    min-height: 100px;
}

.sortable-item {
    cursor: move;
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 8px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.sortable-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 4px var(--shadow-color);
}

.drag-handle {
    cursor: grab;
    color: var(--text-secondary);
    font-size: 18px;
    padding: 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.drag-handle:hover {
    background: var(--hover-bg);
    color: var(--primary-color);
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.4;
    background: var(--bg-tertiary) !important;
    border: 2px dashed var(--primary-color) !important;
}

.sortable-chosen {
    background: var(--bg-secondary) !important;
    border-color: var(--primary-color) !important;
    box-shadow: 0 4px 8px var(--shadow-color) !important;
    transform: scale(1.02);
}

.sortable-dragging {
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px var(--shadow-color) !important;
    z-index: 1000;
}

/* Animation for order update */
.sortable-item.order-updated {
    animation: highlightOrder 0.5s ease;
}

@keyframes highlightOrder {
    0% { background: var(--bg-tertiary); }
    100% { background: var(--bg-primary); }
}

/* Improved form and table styling */
.form-label {
    color: var(--text-primary) !important;
    font-weight: 600;
}

.form-control {
    background: var(--bg-primary) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color) !important;
}

.form-control:focus {
    background: var(--bg-primary) !important;
    color: var(--text-primary) !important;
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25) !important;
}

.form-text {
    color: var(--text-secondary) !important;
}

.text-muted {
    color: var(--text-secondary) !important;
}

.btn-primary {
    background: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: var(--text-light) !important;
}

.btn-primary:hover {
    background: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: var(--text-light) !important;
}

.btn-outline-secondary {
    border-color: var(--border-color) !important;
    color: var(--text-secondary) !important;
}

.btn-outline-secondary:hover {
    background: var(--hover-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

.input-group-text {
    background: var(--bg-secondary) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color) !important;
}

.modal-content {
    background: var(--bg-primary) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color) !important;
}

.modal-header {
    background: var(--bg-secondary) !important;
    color: var(--text-primary) !important;
    border-bottom: 1px solid var(--border-color) !important;
}

.modal-footer {
    background: var(--bg-secondary) !important;
    border-top: 1px solid var(--border-color) !important;
}

.table {
    color: var(--text-primary) !important;
}

.table th {
    background: var(--bg-secondary) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
    font-weight: 600;
}

.table td {
    background: var(--bg-primary) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

.alert {
    color: var(--text-primary) !important;
    border: 1px solid var(--border-color) !important;
}

.alert-info {
    background: var(--bg-secondary) !important;
    border-color: var(--primary-color) !important;
}

.alert-success {
    background: #d4edda !important;
    border-color: #c3e6cb !important;
    color: #155724 !important;
}

.alert-danger {
    background: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24 !important;
}

/* Additional styling for better contrast */
.card {
    background: var(--bg-primary) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
}

.card-header {
    background: var(--bg-secondary) !important;
    color: var(--text-primary) !important;
    border-bottom: 1px solid var(--border-color) !important;
}

.card-body {
    background: var(--bg-primary) !important;
    color: var(--text-primary) !important;
}

.page-header h1 {
    color: var(--text-primary) !important;
    font-weight: bold;
}

/* Nama Unsur specific styling */
.sortable-item strong {
    color: var(--text-primary) !important;
    font-weight: 700;
    font-size: 1.1em;
}

.sortable-item .badge {
    background: var(--primary-color) !important;
    color: var(--text-light) !important;
    font-weight: 600;
}

.sortable-item code {
    background: var(--bg-tertiary) !important;
    color: var(--text-primary) !important;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 500;
}

.btn-outline-primary {
    border-color: var(--primary-color) !important;
    color: var(--primary-color) !important;
}

.btn-outline-primary:hover {
    background: var(--primary-color) !important;
    color: var(--text-light) !important;
}

.btn-outline-danger {
    border-color: #dc3545 !important;
    color: #dc3545 !important;
}

.btn-outline-danger:hover {
    background: #dc3545 !important;
    color: white !important;
}

.btn-info {
    background: #17a2b8 !important;
    border-color: #17a2b8 !important;
    color: white !important;
}

.btn-success {
    background: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

.btn-warning {
    background: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #212529 !important;
}

/* Better contrast for drag handle */
.drag-handle {
    background: var(--bg-tertiary) !important;
    border-radius: 4px;
}

.drag-handle:hover {
    background: var(--hover-bg) !important;
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
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_unsur_list', 'get_unsur_detail', 'create_unsur', 'update_unsur', 'delete_unsur', 'force_delete_unsur', 'update_order'])) {
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
        $orders = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'orders', FILTER_SANITIZE_STRING) ?? [];
        
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
        $nama_unsur = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_unsur', FILTER_SANITIZE_STRING);
        $kode_unsur = preg_replace('/[^a-zA-Z0-9\s]/', '', strtoupper($nama_unsur));
        
        // Validate and truncate kode_unsur to fit varchar(20)
        if (strlen($kode_unsur) > 20) {
            $kode_unsur = substr($kode_unsur, 0, 20);
            error_log("CREATE UNSUR: Kode truncated to 20 chars: '$kode_unsur'");
        }
        
        // Get the highest current urutan and add 1
        $stmt = $pdo->query("SELECT MAX(urutan) as max_urutan FROM unsur");
        $maxUrutan = $stmt->fetch()['max_urutan'];
        $newUrutan = ($maxUrutan ?? 0) + 1;
        
        $stmt = $pdo->prepare("INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, urutan) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $kode_unsur, // Use truncated kode_unsur
            $nama_unsur,
            filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'deskripsi', FILTER_SANITIZE_STRING) ?? '',
            $newUrutan
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil ditambahkan!']);
        exit;
    }
    
    if ($action === 'get_unsur_detail') {
        $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
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
        try {
            // Debug: Log received data
            error_log("UPDATE UNSUR DEBUG: " . print_r($_POST, true));
            
            // Validate and truncate kode_unsur to fit varchar(20)
            $kodeUnsur = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_unsur', FILTER_SANITIZE_STRING) ?? '';
            if (strlen($kodeUnsur) > 20) {
                $kodeUnsur = substr($kodeUnsur, 0, 20);
                error_log("KODE_UNSUR TRUNCATED: Original length " . strlen(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kode_unsur', FILTER_SANITIZE_STRING)) . " -> Truncated to 20: '$kodeUnsur'");
            }
            
            // Get current urutan from database (don't change it)
            $stmt = $pdo->prepare("SELECT urutan FROM unsur WHERE id = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $currentUrutan = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("UPDATE unsur SET kode_unsur = ?, nama_unsur = ?, deskripsi = ?, urutan = ? WHERE id = ?");
            $result = $stmt->execute([
                $kodeUnsur, // Use truncated kode_unsur
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_unsur', FILTER_SANITIZE_STRING),
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'deskripsi', FILTER_SANITIZE_STRING) ?? '',
                $currentUrutan, // Use existing urutan
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)
            ]);
            
            error_log("UPDATE RESULT: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            // Update pimpinan assignment
            if (!empty(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_pimpinan', FILTER_SANITIZE_STRING))) {
                // Remove existing assignments
                $delStmt = $pdo->prepare("DELETE FROM unsur_pimpinan WHERE unsur_id = ? AND tanggal_selesai IS NULL");
                $delStmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
                
                // Add new assignment
                $pimpinanStmt = $pdo->prepare("SELECT id FROM personil WHERE nama = ?");
                $pimpinanStmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_pimpinan', FILTER_SANITIZE_STRING)]);
                $pimpinanId = $pimpinanStmt->fetchColumn();
                
                if ($pimpinanId) {
                    $relStmt = $pdo->prepare("INSERT INTO unsur_pimpinan (unsur_id, personil_id) VALUES (?, ?)");
                    $relStmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING), $pimpinanId]);
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Unsur berhasil diperbarui!']);
            exit;
        } catch (Exception $e) {
            error_log("UPDATE UNSUR ERROR: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($action === 'delete_unsur') {
        // Check if unsur has bagian
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bagian WHERE id_unsur = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
        $bagianCount = $stmt->fetchColumn();
        
        if ($bagianCount > 0) {
            // Get details for better error message
            $stmt = $pdo->prepare("SELECT nama_unsur FROM unsur WHERE id = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $unsurName = $stmt->fetchColumn();
            
            // Get bagian details
            $stmt = $pdo->prepare("SELECT nama_bagian FROM bagian WHERE id_unsur = ? LIMIT 5");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $bagianList = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => "Tidak dapat menghapus unsur '$unsurName' karena masih memiliki $bagianCount bagian!", 
                'details' => [
                    'unsur_name' => $unsurName,
                    'bagian_count' => $bagianCount,
                    'bagian_list' => $bagianList,
                    'suggestion' => 'Pindahkan atau hapus semua bagian terlebih dahulu'
                ]
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Unsur berhasil dihapus!']);
        exit;
    }
    
    if ($action === 'force_delete_unsur') {
        try {
            $pdo->beginTransaction();
            
            $unsurId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING);
            $reassignToUnsurId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'reassign_to_unsur_id', FILTER_SANITIZE_STRING) ?? null;
            
            // Get unsur name for logging
            $stmt = $pdo->prepare("SELECT nama_unsur FROM unsur WHERE id = ?");
            $stmt->execute([$unsurId]);
            $unsurName = $stmt->fetchColumn();
            
            // If reassign_to_unsur_id is provided, move bagian to that unsur
            if ($reassignToUnsurId) {
                $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ? WHERE id_unsur = ?");
                $stmt->execute([$reassignToUnsurId, $unsurId]);
                
                // Get reassign unsur name
                $stmt = $pdo->prepare("SELECT nama_unsur FROM unsur WHERE id = ?");
                $stmt->execute([$reassignToUnsurId]);
                $reassignUnsurName = $stmt->fetchColumn();
                
                $message = "Unsur '$unsurName' berhasil dihapus dan $stmt->rowCount() bagian dipindahkan ke '$reassignUnsurName'!";
            } else {
                // Delete all bagian in this unsur
                $stmt = $pdo->prepare("DELETE FROM bagian WHERE id_unsur = ?");
                $deletedBagians = $stmt->rowCount();
                
                $message = "Unsur '$unsurName' berhasil dihapus beserta $deletedBagians bagian terkait!";
            }
            
            // Now delete the unsur
            $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
            $stmt->execute([$unsurId]);
            
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus unsur: ' . $e->getMessage()]);
            exit;
        }
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

    <!-- Sortable Unsur Table -->
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-2"></i>Urutan Unsur Organisasi
                <small class="text-muted ms-2">(Drag & drop untuk mengatur urutan)</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                    <i class="fas fa-plus me-1"></i>Tambah
                </button>
                <button class="btn btn-info btn-sm" onclick="refreshData()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
                <button class="btn btn-success btn-sm" id="saveOrderBtn" onclick="saveOrder()">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
                <button class="btn btn-warning btn-sm" id="cancelOrderBtn" onclick="cancelOrder()" style="display: none;">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama unsur..." autocomplete="off">
                        <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
            <!-- Table Header -->
            <div class="row mb-3 text-muted">
                <div class="col-1"><strong>Urutan</strong></div>
                <div class="col-7"><strong>Nama Unsur</strong></div>
                <div class="col-4"><strong>Aksi</strong></div>
            </div>
            
            <div id="sortable-container" class="sortable-list">
                <?php foreach ($unsurData as $unsur): ?>
                <div class="sortable-item" data-id="<?php echo $unsur['id']; ?>" data-urutan="<?php echo $unsur['urutan']; ?>">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="row align-items-center g-2">
                                <div class="col-1">
                                    <span class="badge bg-primary order-badge flex-shrink-0"><?php echo $unsur['urutan']; ?></span>
                                </div>
                                <div class="col-7">
                                    <strong><?php echo htmlspecialchars($unsur['nama_unsur']); ?></strong>
                                    <br>
                                    <small class="text-muted">Order: <?php echo $unsur['urutan']; ?></small>
                                </div>
                                <div class="col-4">
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
<div class="modal fade" id="unsurModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-modal="true" role="dialog">
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
        const urutanDisplay = item.querySelector('.col-7 small');
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
                        <div class="row align-items-center g-2">
                            <div class="col-1">
                                <span class="badge bg-primary order-badge flex-shrink-0">${unsur.urutan}</span>
                            </div>
                            <div class="col-7">
                                <strong>${unsur.nama_unsur}</strong>
                                <br>
                                <small class="text-muted">Urutan: ${unsur.urutan}</small>
                            </div>
                            <div class="col-4">
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
                    nama_unsur: item.querySelector('.col-7 strong').textContent
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
        .replace(/[^A-Z0-9\s]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    
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
    // First check if unsur has bagian
    fetch('unsur.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'delete_unsur',
            id: id
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
    // Get all other unsur options for reassigning
    fetch('unsur.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_unsur_list'
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
    const formData = new FormData();
    formData.append('action', 'force_delete_unsur');
    formData.append('id', unsurId);
    
    if (reassignToUnsurId) {
        formData.append('reassign_to_unsur_id', reassignToUnsurId);
    }
    
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

// Search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('btnClearSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterUnsur(searchTerm);
        });
        
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
    }
    
    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearch);
    }
}

function filterUnsur(searchTerm) {
    const sortableItems = document.querySelectorAll('.sortable-item');
    
    if (searchTerm === '') {
        // Show all
        sortableItems.forEach(item => {
            item.style.display = 'block';
        });
        return;
    }
    
    // Filter unsur items
    sortableItems.forEach(item => {
        const namaUnsur = item.querySelector('strong')?.textContent.toLowerCase() || '';
        const kodeUnsur = item.querySelector('code')?.textContent.toLowerCase() || '';
        const urutan = item.querySelector('.order-badge')?.textContent.toLowerCase() || '';
        
        const matches = namaUnsur.includes(searchTerm) || 
                       kodeUnsur.includes(searchTerm) || 
                       urutan.includes(searchTerm);
        
        item.style.display = matches ? 'block' : 'none';
    });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    filterUnsur('');
}

// Initialize search on page load
document.addEventListener('DOMContentLoaded', function() {
    setupSearch();
});

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
        
        // Debug: Log form data
        console.log('Form Data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        
        fetch('unsur.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
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
