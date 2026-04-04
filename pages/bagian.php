<?php
declare(strict_types=1);
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
    
    // Set up error handler for AJAX requests
    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    
    try {
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
        try {
            $bagianId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'bagian_id', FILTER_SANITIZE_STRING) ?? 0;
            $newUnsurId = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_unsur_id', FILTER_SANITIZE_STRING) ?? 0;
            $newUrutan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'new_urutan', FILTER_SANITIZE_STRING) ?? 0;
            
            if (empty($bagianId) || empty($newUnsurId)) {
                throw new Exception('Invalid parameters: bagian_id and new_unsur_id are required');
            }
            
            $pdo->beginTransaction();
            
            // Check if urutan column exists
            $columnCheck = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
            $hasUrutanColumn = $columnCheck->rowCount() > 0;
            
            if ($hasUrutanColumn) {
                // Update bagian's unsur and urutan
                $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ?, urutan = ? WHERE id = ?");
                $result = $stmt->execute([$newUnsurId, $newUrutan, $bagianId]);
                
                if (!$result) {
                    throw new Exception('Failed to update bagian record');
                }
                
                // Reorder other bagian in the same unsur to maintain sequence
                $stmt = $pdo->prepare("SELECT id, urutan FROM bagian WHERE id_unsur = ? AND id != ? ORDER BY urutan");
                $stmt->execute([$newUnsurId, $bagianId]);
                $otherBagians = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $urutan = 1;
                foreach ($otherBagians as $other) {
                    if ($urutan == $newUrutan) $urutan++; // Skip the moved position
                    $updateStmt = $pdo->prepare("UPDATE bagian SET urutan = ? WHERE id = ?");
                    $updateResult = $updateStmt->execute([$urutan, $other['id']]);
                    if (!$updateResult) {
                        throw new Exception('Failed to reorder bagian with ID: ' . $other['id']);
                    }
                    $urutan++;
                }
                
                $message = 'Bagian berhasil dipindahkan dan urutan diperbarui!';
            } else {
                // Fallback: only update unsur if urutan column doesn't exist
                $stmt = $pdo->prepare("UPDATE bagian SET id_unsur = ? WHERE id = ?");
                $result = $stmt->execute([$newUnsurId, $bagianId]);
                if (!$result) {
                    throw new Exception('Failed to update bagian unsur');
                }
                $message = 'Bagian berhasil dipindahkan (urutan tidak disimpan karena column tidak ada)';
            }
            
            $pdo->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollback();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan bagian: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($action === 'get_bagian_detail') {
        $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM bagian WHERE id = ?");
        $stmt->execute([$id]);
        $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagian]);
        exit;
    }
    
    if ($action === 'create_bagian') {
        // Get next urutan for the unsur
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 as next_urutan FROM bagian WHERE id_unsur = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING)]);
        $nextUrutan = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian, id_unsur, urutan) VALUES (?, ?, ?)");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_bagian', FILTER_SANITIZE_STRING), filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING), $nextUrutan]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil ditambahkan!']);
        exit;
    }
    
    if ($action === 'update_bagian') {
        $stmt = $pdo->prepare("UPDATE bagian SET nama_bagian = ?, id_unsur = ? WHERE id = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_bagian', FILTER_SANITIZE_STRING), filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING), filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil diperbarui!']);
        exit;
    }
    
    if ($action === 'delete_bagian') {
        // Check if bagian has personil
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
        $personilCount = $stmt->fetchColumn();
        
        if ($personilCount > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus bagian yang masih memiliki personil!']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
        $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil dihapus!']);
        exit;
    }
    
    } catch (Exception $e) {
        // Restore original error handler
        restore_error_handler();
        
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
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
    
    // DEBUG: Output data loading ke HTML
    echo "<!-- DEBUG: Total bagian records loaded: " . count($bagianData) . " -->";
    foreach ($bagianData as $bagian) {
        if ($bagian['nama_bagian'] === 'BKO') {
            echo "<!-- DEBUG: BKO found in bagianData: " . htmlspecialchars(json_encode($bagian)) . " -->";
        }
    }
    
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
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.card:hover {
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
    background: var(--bg-secondary);
    min-height: 200px;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.bagian-list {
    padding: 0.75rem;
}

.bagian-item {
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

.bagian-item::before {
    content: '---';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-color);
    font-weight: bold;
    font-size: 1rem;
    opacity: 0.7;
}

.bagian-item:hover {
    background: var(--hover-bg);
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px var(--shadow-color);
    transform: translateX(3px);
}

.bagian-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px var(--shadow-color);
    z-index: 1000;
}

.bagian-item.drag-over {
    background: var(--bg-tertiary);
    border-color: var(--accent-color);
    border-style: dashed;
}

.bagian-info {
    flex-grow: 1;
}

.bagian-name {
    font-weight: 600;
    color: var(--text-primary, #333);
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.bagian-meta {
    font-size: 0.75rem;
    color: var(--text-secondary, #6c757d);
}

.bagian-actions {
    display: flex;
    gap: 0.25rem;
}

.drag-handle {
    color: var(--text-secondary, #6c757d);
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
    color: var(--text-secondary);
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
    .card {
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
    
    .card {
        margin-bottom: 1rem;
    }
}

/* Badge colors for different types */
.badge.bg-info { background: var(--primary-color) !important; }
.badge.bg-primary { background: var(--primary-color) !important; }
.badge.bg-success { background: #28a745 !important; }
.badge.bg-warning { background: var(--accent-color) !important; color: var(--text-primary) !important; }
.badge.bg-danger { background: #dc3545 !important; }
.badge.bg-secondary { background: var(--text-secondary) !important; }
</style>

<div class="page-header">
    <h1><i class="fas fa-building me-2"></i>Manajemen Bagian</h1>
    <p class="text-muted text-center">Kelola dan atur bagian dalam setiap unsur organisasi POLRES Samosir</p>
</div>

<!-- Search and Action Buttons -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama bagian, unsur, atau type..." autocomplete="off">
            <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>
    <div class="col-md-4">
        <div class="d-flex gap-2">
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
    </div>
</div>

<!-- Instructions -->
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Petunjuk:</strong> Seret dan lepas bagian untuk memindahkannya antar unsur. Gunakan search untuk memfilter bagian.
</div>

<!-- Unsur dan Bagian Containers -->
<div id="unsur-bagian-container" class="row">
    <?php foreach ($unsurData as $unsur): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card" data-unsur-id="<?php echo $unsur['id']; ?>">
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
                    <?php
                    // DEBUG: Output langsung ke HTML untuk UNSUR LAINNYA
                    if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
                        echo "<!-- DEBUG: UNSUR LAINNYA ID={$unsur['id']}, isset=" . (isset($bagianByUnsur[$unsur['id']]) ? 'true' : 'false') . ", count=" . (isset($bagianByUnsur[$unsur['id']]) ? count($bagianByUnsur[$unsur['id']]) : 'N/A') . " -->";
                        if (isset($bagianByUnsur[$unsur['id']])) {
                            echo "<!-- DEBUG: bagians=" . htmlspecialchars(json_encode($bagianByUnsur[$unsur['id']])) . " -->";
                        }
                    }
                    ?>
                    <?php if (isset($bagianByUnsur[$unsur['id']]) && count($bagianByUnsur[$unsur['id']]) > 0): ?>
                        <?php foreach ($bagianByUnsur[$unsur['id']] as $bagian): ?>
                        <div class="bagian-item" data-id="<?php echo $bagian['id']; ?>" data-urutan="<?php echo $bagian['urutan']; ?>" data-unsur-id="<?php echo $bagian['id_unsur']; ?>">
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
<div class="modal fade" id="bagianModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-content" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-header" data-bs-backdrop="static" data-bs-keyboard="false">
                <h5 class="modal-title">
                    <i class="fas fa-building me-2"></i>
                    <span id="modalTitle">Tambah Bagian</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="bagianForm">
                <div class="modal-body" data-bs-backdrop="static" data-bs-keyboard="false">
                    <input type="hidden" name="action" id="formAction" value="create_bagian">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_bagian" class="form-label">Nama Bagian</label>
                        <input type="text" class="form-control" id="nama_bagian" name="nama_bagian" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="id_unsur" name="id_unsur" required onchange="onUnsurChange()">
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
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Type akan otomatis disesuaikan berdasarkan unsur yang dipilih
                        </div>
                    </div>
                </div>
                <div class="modal-footer" data-bs-backdrop="static" data-bs-keyboard="false">
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
                } else {
                    console.log('SUCCESS: ' + message);
                    // Create a simple notification div
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                    notification.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(notification);
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 3000);
                }
            } catch (error) {
                console.log('SUCCESS: ' + message);
                alert(message);
            }
        },
        showError: function(message) {
            try {
                if (typeof toastr !== 'undefined' && toastr.error) {
                    toastr.error(message);
                } else {
                    console.error('ERROR: ' + message);
                    // Create a simple notification div
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                    notification.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(notification);
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 5000);
                }
            } catch (error) {
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
        try {
            window.SPRINT.showSuccess('Tidak ada perubahan untuk disimpan.');
        } catch (error) {
            console.log('Tidak ada perubahan untuk disimpan.');
        }
        return;
    }
    
    console.log('Saving changes:', changes);
    
    const savePromises = changes.map(change => {
        return fetch('<?php echo url('pages/bagian.php'); ?>', {
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
        .then(responses => {
            // Check if all responses are OK
            const allOk = responses.every(r => r.ok);
            if (!allOk) {
                throw new Error('Some requests failed');
            }
            
            // Check content type before parsing JSON
            const contentTypePromises = responses.map(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    });
                }
            });
            
            return Promise.all(contentTypePromises);
        })
        .then(results => {
            const failed = results.filter(r => !r.success);
            if (failed.length === 0) {
                try {
                    window.SPRINT.showSuccess('Semua perubahan berhasil disimpan!');
                } catch (error) {
                    console.log('Success: Semua perubahan berhasil disimpan!');
                }
                changes = [];
                hideSaveButtons();
                refreshData();
            } else {
                try {
                    window.SPRINT.showError('Beberapa perubahan gagal disimpan.');
                } catch (error) {
                    console.log('Error: Beberapa perubahan gagal disimpan.');
                }
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
                alert(errorMessage + ' Check console for details.');
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
    
    // Auto-set type based on unsur
    const autoType = getBagianTypeByUnsur(unsurId, unsurName);
    document.getElementById('type').value = autoType;
    
    const modal = new bootstrap.Modal(document.getElementById('bagianModal'));
    modal.show();
}

function getBagianTypeByUnsur(unsurId, unsurName) {
    // Mapping unsur ke default bagian type
    const unsurTypeMapping = {
        // UNSUR PIMPINAN
        '1': 'PIMPINAN',
        'UNSUR PIMPINAN': 'PIMPINAN',
        
        // PEMBANTU PIMPINAN DAN STAFF
        '8': 'BAG',
        'PEMBANTU PIMPINAN DAN STAFF': 'BAG',
        
        // UNSUR PELAKSANA TUGAS POKOK
        '3': 'SAT',
        'UNSUR PELAKSANA TUGAS POKOK': 'SAT',
        
        // UNSUR PELAKSANA KEWILAYAHAN  
        '4': 'POLSEK',
        'UNSUR PELAKSANA KEWILAYAHAN': 'POLSEK',
        
        // UNSUR PENDUKUNG
        '5': 'SIUM',
        'UNSUR PENDUKUNG': 'SIUM',
        
        // UNSUR LAINNYA
        '6': 'BKO',
        'UNSUR LAINNYA': 'BKO'
    };
    
    // Try to find by ID first, then by name
    return unsurTypeMapping[unsurId] || 
           unsurTypeMapping[unsurName] || 
           'BAG/SAT/SIE'; // Default fallback
}

function onUnsurChange() {
    const unsurSelect = document.getElementById('id_unsur');
    const typeSelect = document.getElementById('type');
    
    if (unsurSelect.value) {
        const selectedOption = unsurSelect.options[unsurSelect.selectedIndex];
        const unsurName = selectedOption.textContent;
        const unsurId = unsurSelect.value;
        
        const autoType = getBagianTypeByUnsur(unsurId, unsurName);
        typeSelect.value = autoType;
    }
}

function editBagian(id) {
    fetch('<?php echo url('pages/bagian.php'); ?>', {
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
            try {
                window.SPRINT.showError('Error: Bagian tidak ditemukan');
            } catch (error) {
                console.error('Error: Bagian tidak ditemukan');
                alert('Error: Bagian tidak ditemukan');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        try {
            window.SPRINT.showError('Error: ' + error.message);
        } catch (notificationError) {
            console.error('Error: ' + error.message);
            alert('Error: ' + error.message);
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
    
    fetch('<?php echo url('pages/bagian.php'); ?>', {
        method: 'POST',
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
            } catch (error) {
                console.error('Error: ' + data.message);
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        try {
            window.SPRINT.showError('Error: Terjadi kesalahan saat menyimpan data');
        } catch (notificationError) {
            console.error('Error: Terjadi kesalahan saat menyimpan data');
            alert('Error: Terjadi kesalahan saat menyimpan data');
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
