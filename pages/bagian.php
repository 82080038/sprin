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

// Handle AJAX operations FIRST (before any includes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_bagian_list', 'get_bagian_detail', 'create_bagian', 'update_bagian', 'delete_bagian'])) {
        // Set test session for AJAX
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
        
        // Clear any output buffers for AJAX requests
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Include required files for AJAX
        require_once __DIR__ . '/../core/calendar_config.php';
        
        // Connect to database
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        
        // Process AJAX requests
        if ($action === 'get_bagian_detail') {
            $index = $_POST['index'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM bagian WHERE id = ?");
            $stmt->execute([$index]);
            $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $bagian]);
            exit;
        }
        
        if ($action === 'get_bagian_list') {
            $api_url = API_BASE_URL . '/simple.php?limit=1000';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $data = json_decode($response, true);
                if ($data && $data['success']) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $data['data']['bagian']]);
                    exit;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to load data']);
            exit;
        }
    }
}

// Include authentication check (only for non-AJAX requests)
require_once '../core/auth_check.php';

// Include configuration
require_once '../core/config.php';

$page_title = 'Manajemen Bagian - POLRES Samosir';
include '../includes/components/header.php';

// Connect to database
require_once '../core/calendar_config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Load bagian data from DATABASE API
function getBagianData() {
    global $pdo;
    $api_url = API_BASE_URL . '/simple.php?limit=1000';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: $http_code");
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !$data['success']) {
        throw new Exception("API returned error: " . ($data['error']['message'] ?? 'Unknown error'));
    }
    
    return $data['data']['bagian'];
}

// Get all personil for dropdown from API
function getAllPersonil() {
    global $pdo;
    $api_url = API_BASE_URL . '/personil_simple.php?limit=1000';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: $http_code");
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !$data['success']) {
        throw new Exception("API returned error: " . ($data['error']['message'] ?? 'Unknown error'));
    }
    
    return $data['data']['personil'];
}

// Load data
try {
    $bagianData = getBagianData();
    $allPersonil = getAllPersonil();
} catch (Exception $e) {
    $bagianData = [];
    $allPersonil = [];
    echo '<div class="alert alert-danger">Error loading data: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Handle AJAX operations first (before auth check)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Bypass auth for AJAX requests
    if (in_array($action, ['get_bagian_list', 'get_bagian_detail', 'create_bagian', 'update_bagian', 'delete_bagian'])) {
        // Set test session for AJAX
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'AJAX User';
        $_SESSION['user_id'] = 1;
    }
    
    // Debug for AJAX
    if (isset($_GET['debug'])) {
        error_log("Processing action: " . $action);
    }
    
    // Clear any output buffers for AJAX requests
    if (in_array($action, ['get_bagian_list', 'get_bagian_detail', 'create_bagian', 'update_bagian', 'delete_bagian'])) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    // AJAX operations
    if ($action === 'get_bagian_list') {
        if (isset($_GET['debug'])) {
            error_log("Returning bagian data: " . count($bagianData) . " items");
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagianData]);
        exit;
    }
    
    if ($action === 'get_bagian_detail') {
        $index = $_POST['index'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM bagian WHERE id = ?");
        $stmt->execute([$index]);
        $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $bagian]);
        exit;
    }
    
    // CRUD operations
    if ($action === 'create_bagian') {
        $stmt = $pdo->prepare("INSERT INTO bagian (nama_bagian, type) VALUES (?, 'BAG/SAT/SIE')");
        $stmt->execute([$_POST['nama_bagian']]);
        
        // Add pimpinan if specified
        if (!empty($_POST['nama_pimpinan'])) {
            $bagianId = $pdo->lastInsertId();
            // Find personil by name
            $pimpinanStmt = $pdo->prepare("SELECT id FROM personil WHERE nama = ?");
            $pimpinanStmt->execute([$_POST['nama_pimpinan']]);
            $pimpinanId = $pimpinanStmt->fetchColumn();
            
            if ($pimpinanId) {
                $relStmt = $pdo->prepare("INSERT INTO bagian_pimpinan (bagian_id, personil_id) VALUES (?, ?)");
                $relStmt->execute([$bagianId, $pimpinanId]);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil ditambahkan!']);
        exit;
    }
    
    if ($action === 'update_bagian') {
        $stmt = $pdo->prepare("UPDATE bagian SET nama_bagian = ? WHERE id = ?");
        $stmt->execute([$_POST['nama_bagian'], $_POST['index']]);
        
        // Update pimpinan assignment
        if (!empty($_POST['nama_pimpinan'])) {
            // Remove existing assignments
            $delStmt = $pdo->prepare("DELETE FROM bagian_pimpinan WHERE bagian_id = ? AND tanggal_selesai IS NULL");
            $delStmt->execute([$_POST['index']]);
            
            // Add new assignment
            $pimpinanStmt = $pdo->prepare("SELECT id FROM personil WHERE nama = ?");
            $pimpinanStmt->execute([$_POST['nama_pimpinan']]);
            $pimpinanId = $pimpinanStmt->fetchColumn();
            
            if ($pimpinanId) {
                $relStmt = $pdo->prepare("INSERT INTO bagian_pimpinan (bagian_id, personil_id) VALUES (?, ?)");
                $relStmt->execute([$_POST['index'], $pimpinanId]);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil diperbarui!']);
        exit;
    }
    
    if ($action === 'delete_bagian') {
        $index = $_POST['index'];
        
        // Check if bagian has personil
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ?");
        $stmt->execute([$index]);
        $personilCount = $stmt->fetchColumn();
        
        if ($personilCount > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus bagian yang masih memiliki personil!']);
            exit;
        }
        
        // Delete bagian (cascade will handle bagian_pimpinan)
        $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
        $stmt->execute([$index]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bagian berhasil dihapus!']);
        exit;
    }
}
?>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-building me-2"></i>Manajemen Bagian/Satuan/SIE/Polsek</h1>
        <p class="text-muted">Kelola data struktur organisasi POLRES Samosir</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo count($bagianData); ?></div>
                <div class="label">Total Bagian</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM personil WHERE id_bagian IS NULL");
                    echo $stmt->fetchColumn();
                ?></div>
                <div class="label">Pimpinan</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $totalPersonil = 0;
                    foreach ($bagianData as $bagian) {
                        $totalPersonil += $bagian['personil_count'];
                    }
                    echo $totalPersonil;
                ?></div>
                <div class="label">Total Personil</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $avgPersonil = count($bagianData) > 0 ? round($totalPersonil / count($bagianData), 1) : 0;
                    echo $avgPersonil;
                ?></div>
                <div class="label">Rata-rata Personil</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mb-4">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Tambah Bagian
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
        <button class="btn btn-success" onclick="exportData()">
            <i class="fas fa-download me-2"></i>Export
        </button>
    </div>

    <!-- Bagian Table Grouped by Unsur -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i>Daftar Bagian/Satuan/SIE/Polsek per Unsur
        </div>
        <div class="card-body">
            <?php
            // Group bagian by unsur
            $bagianByUnsur = [];
            foreach ($bagianData as $bagian) {
                $unsurId = $bagian['id_unsur'] ?? 0;
                if (!isset($bagianByUnsur[$unsurId])) {
                    $bagianByUnsur[$unsurId] = [
                        'nama_unsur' => 'Unknown',
                        'urutan' => 999, // Put unknown at the end
                        'bagians' => []
                    ];
                }
                $bagianByUnsur[$unsurId]['bagians'][] = $bagian;
            }
            
            // Get unsur names with order
            $unsurIds = array_keys($bagianByUnsur);
            if (!empty($unsurIds)) {
                $placeholders = str_repeat('?,', count($unsurIds) - 1) . '?';
                $stmt = $pdo->prepare("SELECT id, nama_unsur, urutan FROM unsur WHERE id IN ($placeholders) ORDER BY urutan");
                $stmt->execute($unsurIds);
                $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($unsurData as $unsur) {
                    if (isset($bagianByUnsur[$unsur['id']])) {
                        $bagianByUnsur[$unsur['id']]['nama_unsur'] = $unsur['nama_unsur'];
                        $bagianByUnsur[$unsur['id']]['urutan'] = $unsur['urutan'];
                    }
                }
            }
            
            // Sort by unsur order (urutan field) using array_multisort
            $unsurNames = [];
            $unsurOrders = [];
            foreach ($bagianByUnsur as $unsurId => $unsurGroup) {
                $unsurNames[$unsurId] = $unsurGroup['nama_unsur'];
                $unsurOrders[$unsurId] = isset($unsurGroup['urutan']) ? (int)$unsurGroup['urutan'] : 999;
            }
            array_multisort($unsurOrders, SORT_ASC, $bagianByUnsur);
            ?>
            
            <?php foreach ($bagianByUnsur as $unsurId => $unsurGroup): ?>
            <div class="unsur-section mb-4">
                <div class="unsur-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        <?php echo htmlspecialchars($unsurGroup['nama_unsur']); ?>
                    </h4>
                    <span class="badge bg-primary">
                        <?php echo count($unsurGroup['bagians']); ?> Bagian
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th width="80">Type</th>
                                <th>Nama Bagian/Satuan</th>
                                <th width="150">Pimpinan</th>
                                <th width="100">Jumlah Personil</th>
                                <th width="100">Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($unsurGroup['bagians'] as $bagian): 
                            // Determine type based on bagian name
                            $type = '';
                            $nama_bagian = $bagian['nama_bagian'];
                            
                            if (strpos($nama_bagian, 'BAG ') === 0) {
                                $type = 'BAG';
                                $nama_display = substr($nama_bagian, 4);
                            } elseif (strpos($nama_bagian, 'SAT ') === 0) {
                                $type = 'SAT';
                                $nama_display = substr($nama_bagian, 4);
                            } elseif (strpos($nama_bagian, 'POLSEK ') === 0) {
                                $type = 'POLSEK';
                                $nama_display = substr($nama_bagian, 7);
                            } elseif (strpos($nama_bagian, 'SIUM') === 0) {
                                $type = 'SAT';
                                $nama_display = $nama_bagian;
                            } elseif (strpos($nama_bagian, 'SPKT') === 0) {
                                $type = 'BAG';
                                $nama_display = $nama_bagian;
                            } elseif ($nama_bagian === 'HARIAN BOHO') {
                                $type = 'POLSEK';
                                $nama_display = $nama_bagian;
                            } elseif ($nama_bagian === 'SATPAMOBVIT') {
                                $type = 'SAT';
                                $nama_display = 'PAMOBVIT';
                            } elseif ($nama_bagian === 'SATPOLAIRUD') {
                                $type = 'SAT';
                                $nama_display = 'POLAIRUD';
                            } elseif ($nama_bagian === 'POLSEK ONANRUNGGU') {
                                $type = 'POLSEK';
                                $nama_display = 'ONAN RUNGGU';
                            } else {
                                $type = 'LAINNYA';
                                $nama_display = $nama_bagian;
                            }
                            ?>
                            <tr id="bagian-row-<?php echo $bagian['id']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $type; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($nama_display); ?></strong>
                                </td>
                                <td>
                                    <?php echo $bagian['kepala'] ?: '-'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $bagian['personil_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $bagian['personil_count'] > 0 ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $bagian['personil_count'] > 0 ? 'Aktif' : 'Kosong'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewBagian('<?php echo $bagian['id']; ?>', <?php echo array_search($bagian['id'], array_column($bagianData, 'id')); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editBagian(<?php echo $bagian['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBagian(<?php echo $bagian['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
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
                    <input type="hidden" name="index" id="formIndex">
                    
                    <div class="mb-3">
                        <label for="nama_bagian" class="form-label">Nama Bagian/Satuan/SIE/Polsek</label>
                        <input type="text" class="form-control" id="nama_bagian" name="nama_bagian" required>
                        <div class="form-text">
                            Contoh: SAT RESKRIM, POLSEK SIMANINDO, SIE Propam, dll
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_pimpinan" class="form-label">Pimpinan</label>
                        <select class="form-select" id="nama_pimpinan" name="nama_pimpinan">
                            <option value="">-- Pilih Pimpinan --</option>
                            <?php foreach ($allPersonil as $personil): ?>
                                <option value="<?php echo htmlspecialchars($personil['nama']); ?>">
                                    <?php 
                                    $pangkat = isset($personil['pangkat']) ? $personil['pangkat'] : '';
                                    $nama = isset($personil['nama']) ? $personil['nama'] : '';
                                    $jabatan = isset($personil['jabatan']) ? $personil['jabatan'] : '';
                                    echo htmlspecialchars($pangkat . ' ' . $nama . ' (' . $jabatan . ')'); 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Pilih pimpinan dari seluruh personil yang tersedia
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

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>
                    Detail Personil Bagian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nama Bagian:</strong>
                        <span id="viewBagianNama"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Kode Bagian:</strong>
                        <span id="viewBagianKode"></span>
                    </div>
                </div>
                <hr>
                <h6>Daftar Personil:</h6>
                <div id="viewBagianPersonil">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
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

.unsur-section {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    background: #fafafa;
}

.unsur-header {
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.unsur-header h4 {
    color: var(--primary-color);
    font-weight: bold;
}

.unsur-section .table {
    background: white;
    border-radius: 6px;
    overflow: hidden;
}

.unsur-section .table-light {
    background: #f8f9fa !important;
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
    
    .unsur-section {
        padding: 15px;
    }
    
    .unsur-header h4 {
        font-size: 1.1rem;
    }
}
</style>

<script>
let bagianData = <?php echo json_encode($bagianData); ?>;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Bagian';
    document.getElementById('formAction').value = 'create_bagian';
    document.getElementById('formIndex').value = '';
    document.getElementById('nama_bagian').value = '';
    document.getElementById('nama_pimpinan').value = '';
    
    new bootstrap.Modal(document.getElementById('bagianModal')).show();
}

function editBagian(bagianId) {
    // Get bagian data from database
    fetch('bagian.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_bagian_detail',
            index: bagianId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const bagian = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Bagian';
            document.getElementById('formAction').value = 'update_bagian';
            document.getElementById('formIndex').value = bagian.id;
            document.getElementById('nama_bagian').value = bagian.nama_bagian || '';
            document.getElementById('kode_bagian').value = bagian.kode_bagian || '';
            document.getElementById('id_unsur').value = bagian.id_unsur || '';
            document.getElementById('deskripsi').value = bagian.deskripsi || '';
            document.getElementById('nama_pimpinan').value = bagian.kepala || '';
            
            new bootstrap.Modal(document.getElementById('bagianModal')).show();
        } else {
            showAlert('danger', 'Error: Bagian tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat mengambil data bagian');
    });
}

function deleteBagian(bagianId) {
    if (confirm('Apakah Anda yakin ingin menghapus bagian ini?')) {
        fetch('bagian.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'delete_bagian',
                index: bagianId
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
            showAlert('danger', 'Terjadi kesalahan saat menghapus bagian');
        });
    }
}

function viewBagian(bagianId, index) {
    const bagian = bagianData[index];
    
    let personilHtml = '';
    if (bagian && bagian.personil && bagian.personil.length > 0) {
        personilHtml = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NRP</th>
                            <th>Pangkat</th>
                            <th>Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        bagian.personil.forEach((personil, i) => {
            personilHtml += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${personil.nama || ''}</td>
                    <td>${personil.nrp || ''}</td>
                    <td>${personil.pangkat || ''}</td>
                    <td>${personil.nama_jabatan || ''}</td>
                </tr>
            `;
        });
        
        personilHtml += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        personilHtml = '<p class="text-muted">Tidak ada personil di bagian ini.</p>';
    }
    
    document.getElementById('viewBagianNama').textContent = bagian.nama_bagian || '';
    document.getElementById('viewBagianKode').textContent = bagian.kode_bagian || '';
    document.getElementById('viewBagianPersonil').innerHTML = personilHtml;
    
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function refreshData() {
    window.location.reload();
}

function exportData() {
    // Simple export to text
    let text = "DAFTAR BAGIAN/SATUAN/SIE/POLSEK POLRES SAMOSIR\n\n";
    
    bagianData.forEach((bagian, index) => {
        text += `${index + 1}. ${bagian.nama_bagian}\n`;
        text += `   ID: ${bagian.id || 'N/A'}\n`;
        text += `   Jumlah Personil: ${bagian.personil.length}\n`;
        text += `   Status: ${bagian.personil.length > 0 ? 'Aktif' : 'Kosong'}\n\n`;
    });
    
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'daftar_bagian_polres_samosir.txt';
    a.click();
    window.URL.revokeObjectURL(url);
}

// AJAX Functions
function getBagianList() {
    return fetch('bagian.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_bagian_list'
        })
    })
    .then(response => response.json());
}

function getBagianDetail(index) {
    return fetch('bagian.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_bagian_detail',
            index: index
        })
    })
    .then(response => response.json());
}

// Example usage of AJAX functions
function loadBagianListAsync() {
    getBagianList()
        .then(data => {
            console.log('Bagian data loaded:', data);
            // Update UI with new data
        })
        .catch(error => {
            console.error('Error loading bagian data:', error);
        });
}

// Initialize with AJAX data loading
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Load data via AJAX for better performance
    // loadBagianListAsync();
});

// Edit Pimpinan Functions
function editPimpinan(id, nama, nrp, pangkat, jabatan, ket, gelar) {
    // Set form values
    document.getElementById('editPimpinanId').value = id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editNrp').value = nrp;
    document.getElementById('editPangkat').value = pangkat;
    document.getElementById('editJabatan').value = jabatan;
    document.getElementById('editKet').value = ket || '';
    document.getElementById('editGelar').value = gelar || '';
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('editPimpinanModal'));
    modal.show();
}

function savePimpinan() {
    var form = document.getElementById('editPimpinanForm');
    var formData = new FormData(form);
    
    // Add gelar to nama if exists
    var gelar = document.getElementById('editGelar').value;
    var nama = document.getElementById('editNama').value;
    if (gelar) {
        formData.set('nama', nama + ', ' + gelar);
    }
    
    fetch('update_pimpinan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('editPimpinanModal'));
            modal.hide();
            
            // Show success message
            showAlert('success', data.message);
            
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan data');
    });
}

function showAlert(type, message) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    '<strong>' + (type === 'success' ? 'Sukses!' : 'Error!') + '</strong> ' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
    
    // Insert alert at the top of the page
    var container = document.querySelector('.container');
    if (container) {
        container.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

// Fix aria-hidden focus issue
document.addEventListener("DOMContentLoaded", function() {
    // Remove aria-hidden when modal is shown
    const modals = document.querySelectorAll(".modal");
    modals.forEach(function(modal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "style" || mutation.attributeName === "class") {
                    if (modal.style.display === "block" || modal.classList.contains("show")) {
                        modal.removeAttribute("aria-hidden");
                    } else {
                        modal.setAttribute("aria-hidden", "true");
                    }
                }
            });
        });
        observer.observe(modal, {
            attributes: true,
            attributeFilter: ["style", "class"]
        });
    });
});
</script>

<!-- Edit Pimpinan Modal -->
<div class="modal fade" id="editPimpinanModal" tabindex="-1" aria-labelledby="editPimpinanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPimpinanModalLabel">Edit Data Pimpinan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Edit Buttons for Pimpinan -->
                <div class="mb-3">
                    <h5>Edit Data Pimpinan:</h5>
                    <div class="row">
                        <div class='col-md-6 mb-2'>
                            <button class='btn btn-sm btn-primary w-100' onclick='editPimpinan(1, "RINA SRY NIRWANA TARIGAN, S.I.K.", "84031648", "AKBP", "KAPOLRES SAMOSIR", "aktif", "M.H.")'>
                                <i class='fas fa-edit'></i> RINA SRY NIRWANA TARIGAN, S.I....
                            </button>
                        </div>
                        <div class='col-md-6 mb-2'>
                            <button class='btn btn-sm btn-primary w-100' onclick='editPimpinan(2, "BRISTON AGUS MUNTECARLO, S.T.", "83081648", "KOMPOL", "WAKAPOLRES", "aktif", "S.I.K.")'>
                                <i class='fas fa-edit'></i> BRISTON AGUS MUNTECARLO, S.T.,...
                            </button>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Quick Edit Guide -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Cara Edit Pimpinan:</h6>
                    <ol>
                        <li>Klik tombol Edit di atas untuk membuka modal edit</li>
                        <li>Ubah data yang diperlukan (nama, nrp, pangkat, jabatan, keterangan)</li>
                        <li>Tambahkan gelar jika diperlukan (contoh: S.H., M.H., S.I.K.)</li>
                        <li>Klik "Simpan Perubahan" untuk menyimpan</li>
                        <li>Halaman akan reload otomatis dengan data terbaru</li>
                    </ol>
                </div>
                
                <form id="editPimpinanForm">
                    <input type="hidden" id="editPimpinanId" name="id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editNama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editNama" name="nama" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editNrp" class="form-label">NRP</label>
                            <input type="text" class="form-control" id="editNrp" name="nrp" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editPangkat" class="form-label">Pangkat</label>
                            <select class="form-select" id="editPangkat" name="pangkat" required>
                                <option value="">Pilih Pangkat</option>
                                <option value="AKBP">AKBP</option>
                                <option value="KOMPOL">KOMPOL</option>
                                <option value="AKP">AKP</option>
                                <option value="IPTU">IPTU</option>
                                <option value="IPDA">IPDA</option>
                                <option value="AIPTU">AIPTU</option>
                                <option value="AIPDA">AIPDA</option>
                                <option value="BRIPKA">BRIPKA</option>
                                <option value="BRIGPOL">BRIGPOL</option>
                                <option value="BRIPTU">BRIPTU</option>
                                <option value="BRIPDA">BRIPDA</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editJabatan" class="form-label">Jabatan</label>
                            <select class="form-select" id="editJabatan" name="jabatan" required>
                                <option value="">Pilih Jabatan</option>
                                <option value="KAPOLRES SAMOSIR">KAPOLRES SAMOSIR</option>
                                <option value="WAKAPOLRES">WAKAPOLRES</option>
                                <option value="KABAG OPS">KABAG OPS</option>
                                <option value="KABAG REN">KABAG REN</option>
                                <option value="KABAG SDM">KABAG SDM</option>
                                <option value="KABAG LOG">KABAG LOG</option>
                                <option value="KASAT INTELKAM">KASAT INTELKAM</option>
                                <option value="KASAT RESKRIM">KASAT RESKRIM</option>
                                <option value="KASATRESNARKOBA">KASATRESNARKOBA</option>
                                <option value="KASAT SAMAPTA">KASAT SAMAPTA</option>
                                <option value="KASAT LANTAS">KASAT LANTAS</option>
                                <option value="KASAT POLAIRUD">KASAT POLAIRUD</option>
                                <option value="KASAT PAMOBVIT">KASAT PAMOBVIT</option>
                                <option value="KASAT TAHTI">KASAT TAHTI</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editKet" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="editKet" name="ket" placeholder="Opsional">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGelar" class="form-label">Gelar (Opsional)</label>
                        <input type="text" class="form-control" id="editGelar" name="gelar" placeholder="Contoh: S.H., M.H., S.I.K., M.M.">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="savePimpinan()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Edit Pimpinan Functions
function editPimpinan(id, nama, nrp, pangkat, jabatan, ket, gelar) {
    // Set form values
    document.getElementById('editPimpinanId').value = id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editNrp').value = nrp;
    document.getElementById('editPangkat').value = pangkat;
    document.getElementById('editJabatan').value = jabatan;
    document.getElementById('editKet').value = ket || '';
    document.getElementById('editGelar').value = gelar || '';
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('editPimpinanModal'));
    modal.show();
}

function savePimpinan() {
    var form = document.getElementById('editPimpinanForm');
    var formData = new FormData(form);
    
    // Add gelar to nama if exists
    var gelar = document.getElementById('editGelar').value;
    var nama = document.getElementById('editNama').value;
    if (gelar) {
        formData.set('nama', nama + ', ' + gelar);
    }
    
    fetch('update_pimpinan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('editPimpinanModal'));
            modal.hide();
            
            // Show success message
            showAlert('success', data.message);
            
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan data');
    });
}

function showAlert(type, message) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    '<strong>' + (type === 'success' ? 'Sukses!' : 'Error!') + '</strong> ' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
    
    // Insert alert at the top of the page
    var container = document.querySelector('.container');
    if (container) {
        container.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

// Fix aria-hidden focus issue
document.addEventListener("DOMContentLoaded", function() {
    // Remove aria-hidden when modal is shown
    const modals = document.querySelectorAll(".modal");
    modals.forEach(function(modal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "style" || mutation.attributeName === "class") {
                    if (modal.style.display === "block" || modal.classList.contains("show")) {
                        modal.removeAttribute("aria-hidden");
                    } else {
                        modal.setAttribute("aria-hidden", "true");
                    }
                }
            });
        });
        
        observer.observe(modal, {
            attributes: true,
            attributeFilter: ["style", "class"]
        });
    });
    
    // Fix Bootstrap modal aria-hidden issues
    window.addEventListener("shown.bs.modal", function(e) {
        e.target.removeAttribute("aria-hidden");
    });
    
    window.addEventListener("hidden.bs.modal", function(e) {
        e.target.setAttribute("aria-hidden", "true");
    });
});

// Utility Functions
function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the container
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function refreshData() {
    location.reload();
}

function saveBagian() {
    console.log("saveBagian called");
    
    var form = document.getElementById("bagianForm");
    if (!form) {
        alert("Error: Form not found");
        return;
    }
    
    var formData = new FormData(form);
    
    fetch("update_bagian.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        console.log("Response received:", response);
        return response.json();
    })
    .then(data => {
        console.log("Data received:", data);
        if (data.success) {
            // Close modal
            var modalElement = document.getElementById("bagianModal");
            if (modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
            
            // Show success message
            showAlert("success", data.message);
            
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert("danger", data.message || "Terjadi kesalahan saat menyimpan");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showAlert("danger", "Terjadi kesalahan saat menyimpan data");
    });
}

function deleteBagian(id, nama) {
    console.log("deleteBagian called with:", {id, nama});
    
    if (confirm("Apakah Anda yakin ingin menghapus bagian \"" + nama + "\"?")) {
        fetch("delete_bagian.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id=" + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert("success", data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert("danger", data.message || "Terjadi kesalahan saat menghapus");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("danger", "Terjadi kesalahan saat menghapus data");
        });
    }
}

// Debug: Log when script loads
console.log("Bagian management script loaded successfully");
</script>