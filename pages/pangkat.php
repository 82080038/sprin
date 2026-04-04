<?php
declare(strict_types=1);
require_once '../core/config.php'; 
require_once '../core/auth_helper.php'; 

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    // Temporarily bypass for testing - remove this line in production
    // header('Location: ' . url('login.php'));
    // exit;
    
    // For testing, set a dummy session
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'test_user';
    $_SESSION['user_id'] = 1;
}

// Initialize database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<div class="alert alert-danger">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>');
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
        if (in_array($action, ['get_pangkat_list', 'get_pangkat_detail', 'create_pangkat', 'update_pangkat', 'delete_pangkat'])) {
            // Set test session for AJAX
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = 'AJAX User';
            $_SESSION['user_id'] = 1;
            
            // Clear any output buffers for AJAX requests
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        
        if ($action === 'get_pangkat_list') {
            $stmt = $pdo->query("SELECT * FROM pangkat ORDER BY level_pangkat ASC, id ASC");
            $pangkatData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $pangkatData]);
            exit;
        }
        
        if ($action === 'create_pangkat') {
            $stmt = $pdo->prepare("INSERT INTO pangkat (nama_pangkat, singkatan, level_pangkat, id_jenis_pegawai) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_pangkat', FILTER_SANITIZE_STRING),
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'singkatan', FILTER_SANITIZE_STRING) ?? '',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'level_pangkat', FILTER_SANITIZE_STRING) ?? 0,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? null
            ]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Pangkat berhasil ditambahkan!']);
            exit;
        }
        
        if ($action === 'get_pangkat_detail') {
            $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM pangkat WHERE id = ?");
            $stmt->execute([$id]);
            $pangkat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $pangkat]);
            exit;
        }
        
        if ($action === 'update_pangkat') {
            $stmt = $pdo->prepare("UPDATE pangkat SET nama_pangkat = ?, singkatan = ?, level_pangkat = ?, id_jenis_pegawai = ? WHERE id = ?");
            $stmt->execute([
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_pangkat', FILTER_SANITIZE_STRING),
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'singkatan', FILTER_SANITIZE_STRING) ?? '',
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'level_pangkat', FILTER_SANITIZE_STRING) ?? 0,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? null,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)
            ]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Pangkat berhasil diperbarui!']);
            exit;
        }
        
        if ($action === 'delete_pangkat') {
            // Check if pangkat is used by personil
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_pangkat = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus pangkat yang masih digunakan oleh personil!']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM pangkat WHERE id = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Pangkat berhasil dihapus!']);
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

// Get current pangkat data grouped by jenis pegawai
$pangkatData = [];
$groupedPangkat = [];

try {
    $stmt = $pdo->query("
        SELECT p.*, m.nama_jenis, m.kategori, m.kode_jenis 
        FROM pangkat p 
        LEFT JOIN master_jenis_pegawai m ON p.id_jenis_pegawai = m.id 
        ORDER BY m.kategori, m.nama_jenis, p.level_pangkat DESC, p.nama_pangkat
    ");
    $pangkatData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by kategori and jenis pegawai
    foreach ($pangkatData as $pangkat) {
        $kategori = $pangkat['kategori'] ?? 'Tidak Dikategorikan';
        $jenis = $pangkat['nama_jenis'] ?? 'Tidak Ada Jenis';
        
        if (!isset($groupedPangkat[$kategori])) {
            $groupedPangkat[$kategori] = [];
        }
        
        if (!isset($groupedPangkat[$kategori][$jenis])) {
            $groupedPangkat[$kategori][$jenis] = [];
        }
        
        $groupedPangkat[$kategori][$jenis][] = $pangkat;
    }
    
} catch (PDOException $e) {
    // Keep empty arrays if database fails
    $pangkatData = [];
    $groupedPangkat = [];
}

$page_title = 'Manajemen Pangkat - POLRES Samosir';
include '../includes/components/header.php';
?>

<style>
/* Pangkat Table Management Styles */
.pangkat-section {
    margin-bottom: 2rem;
}

.pangkat-section-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px 8px 0 0;
    margin-bottom: 0;
}

.pangkat-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.pangkat-section-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0.25rem 0 0 0;
}

.pangkat-table {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0 0 8px 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px var(--shadow-color);
}

.pangkat-table table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.pangkat-table th {
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 600;
    padding: 0.75rem;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

.pangkat-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.pangkat-table tr:hover {
    background: var(--hover-bg);
}

.pangkat-level {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    min-width: 60px;
    text-align: center;
}

.level-high {
    background: #dc3545;
    color: white;
}

.level-medium {
    background: #ffc107;
    color: #212529;
}

.level-low {
    background: #28a745;
    color: white;
}

.pangkat-actions {
    display: flex;
    gap: 0.5rem;
    white-space: nowrap;
}

.pangkat-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background: var(--bg-tertiary);
    color: var(--text-secondary);
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -1rem;
    margin-bottom: 2rem;
}

.card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.card-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.card-text {
    font-size: 0.9rem;
    opacity: 0.9;
}

.table-responsive {
    border-radius: 0 0 8px 8px;
}

@media (max-width: 768px) {
    .pangkat-table {
        font-size: 0.875rem;
    }
    
    .pangkat-table th,
    .pangkat-table td {
        padding: 0.5rem;
    }
    
    .pangkat-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-graduation-cap me-2"></i>Manajemen Pangkat</h1>
        <p class="text-muted">Kelola data pangkat POLRES Samosir berdasarkan jenis pegawai</p>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="card">
            <div class="card-title"><?php echo count($pangkatData); ?></div>
            <div class="card-text">Total Pangkat</div>
        </div>
        <div class="card">
            <div class="card-title"><?php echo count($groupedPangkat); ?></div>
            <div class="card-text">Kategori Pegawai</div>
        </div>
        <div class="card">
            <div class="card-title">
                <?php
                $totalJenis = 0;
                foreach ($groupedPangkat as $kategori => $jenisList) {
                    $totalJenis += count($jenisList);
                }
                echo $totalJenis;
                ?>
            </div>
            <div class="card-text">Jenis Pegawai</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>Tambah Pangkat
                </button>
                <button class="btn btn-info" onclick="refreshData()">
                    <i class="fas fa-sync me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Pangkat Tables by Kategori -->
    <?php if (empty($groupedPangkat)): ?>
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <h5>Belum Ada Data Pangkat</h5>
            <p>Belum ada data pangkat yang terdaftar dalam sistem.</p>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus me-2"></i>Tambah Pangkat Pertama
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($groupedPangkat as $kategori => $jenisList): ?>
            <div class="pangkat-section">
                <div class="pangkat-section-header">
                    <h5 class="pangkat-section-title">
                        <i class="fas fa-users me-2"></i><?php echo htmlspecialchars($kategori); ?>
                    </h5>
                    <p class="pangkat-section-subtitle">
                        <?php echo count($jenisList); ?> jenis pegawai, 
                        <?php
                        $totalPangkat = 0;
                        foreach ($jenisList as $pangkats) {
                            $totalPangkat += count($pangkats);
                        }
                        echo $totalPangkat; 
                        ?> pangkat
                    </p>
                </div>
                
                <?php foreach ($jenisList as $jenis => $pangkats): ?>
                    <div class="pangkat-table">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="5" class="text-center bg-light">
                                            <strong><?php echo htmlspecialchars($jenis); ?></strong>
                                            <span class="pangkat-badge ms-2"><?php echo count($pangkats); ?> pangkat</span>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="35%">Nama Pangkat</th>
                                        <th width="20%">Singkatan</th>
                                        <th width="15%">Level</th>
                                        <th width="25%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pangkats as $pangkat): ?>
                                        <tr>
                                            <td><?php echo $pangkat['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($pangkat['nama_pangkat']); ?></strong>
                                                <?php if ($pangkat['kode_jenis']): ?>
                                                    <span class="pangkat-badge ms-2"><?php echo htmlspecialchars($pangkat['kode_jenis']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($pangkat['singkatan'] ?? '-'); ?></td>
                                            <td>
                                                <?php
                                                $level = $pangkat['level_pangkat'] ?? 0;
                                                $levelClass = $level >= 15 ? 'level-high' : ($level >= 10 ? 'level-medium' : 'level-low');
                                                ?>
                                                <span class="pangkat-level <?php echo $levelClass; ?>">
                                                    Level <?php echo $level; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="pangkat-actions">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editPangkat(<?php echo $pangkat['id']; ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deletePangkat(<?php echo $pangkat['id']; ?>, '<?php echo htmlspecialchars($pangkat['nama_pangkat']); ?>')">
                                                        <i class="fas fa-trash"></i> Hapus
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
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="pangkatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-graduation-cap me-2"></i>
                    <span id="modalTitle">Tambah Pangkat</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="pangkatForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_pangkat">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_pangkat" class="form-label">Nama Pangkat</label>
                        <input type="text" class="form-control" id="nama_pangkat" name="nama_pangkat" required>
                        <div class="form-text">
                            Contoh: Inspektur Polisi Satu, Ajun Komisaris Polisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="singkatan" class="form-label">Singkatan</label>
                        <input type="text" class="form-control" id="singkatan" name="singkatan">
                        <div class="form-text">
                            Contoh: Ip.S., A.K.P.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_jenis_pegawai" class="form-label">Jenis Pegawai</label>
                        <select class="form-select" id="id_jenis_pegawai" name="id_jenis_pegawai" required>
                            <option value="">Pilih Jenis Pegawai</option>
                            <?php
                            try {
                                $jenisStmt = $pdo->query("SELECT id, nama_jenis, kategori FROM master_jenis_pegawai ORDER BY kategori, nama_jenis");
                                $jenisPegawaiOptions = $jenisStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                $currentKategori = '';
                                foreach ($jenisPegawaiOptions as $jenis) {
                                    if ($jenis['kategori'] !== $currentKategori) {
                                        $currentKategori = $jenis['kategori'];
                                        if ($currentKategori !== '') {
                                            echo "</optgroup>";
                                        }
                                        echo "<optgroup label='" . htmlspecialchars($currentKategori) . "'>";
                                    }
                                    echo "<option value='" . $jenis['id'] . "'>" . htmlspecialchars($jenis['nama_jenis']) . "</option>";
                                }
                                if ($currentKategori !== '') {
                                    echo "</optgroup>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading jenis pegawai</option>";
                            }
                            ?>
                        </select>
                        <div class="form-text">
                            Pilih jenis pegawai untuk pangkat ini
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level_pangkat" class="form-label">Level Pangkat</label>
                        <input type="number" class="form-control" id="level_pangkat" name="level_pangkat" min="1" max="20">
                        <div class="form-text">
                            Level pangkat (1-20, semakin tinggi semakin senior)
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

<script>
let pangkatData = <?php echo json_encode($pangkatData); ?>;

// CRUD Functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pangkat';
    document.getElementById('formAction').value = 'create_pangkat';
    document.getElementById('formId').value = '';
    document.getElementById('nama_pangkat').value = '';
    document.getElementById('singkatan').value = '';
    document.getElementById('level_pangkat').value = '';
    document.getElementById('id_jenis_pegawai').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('pangkatModal'));
    modal.show();
}

function editPangkat(id) {
    const pangkat = pangkatData.find(p => p.id == id);
    if (!pangkat) {
        alert('Data pangkat tidak ditemukan!');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Edit Pangkat';
    document.getElementById('formAction').value = 'update_pangkat';
    document.getElementById('formId').value = pangkat.id;
    document.getElementById('nama_pangkat').value = pangkat.nama_pangkat;
    document.getElementById('singkatan').value = pangkat.singkatan || '';
    document.getElementById('level_pangkat').value = pangkat.level_pangkat || '';
    document.getElementById('id_jenis_pegawai').value = pangkat.id_jenis_pegawai || '';
    
    const modal = new bootstrap.Modal(document.getElementById('pangkatModal'));
    modal.show();
}

function deletePangkat(id, nama) {
    if (!confirm(`Apakah Anda yakin ingin menghapus pangkat "${nama}"?`)) {
        return;
    }
    
    fetch('pangkat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'delete_pangkat',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            refreshData();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Terjadi kesalahan saat menghapus data');
    });
}

function refreshData() {
    window.location.reload();
}

// Form submission
document.getElementById('pangkatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('pangkat.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('pangkatModal')).hide();
            refreshData();
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
