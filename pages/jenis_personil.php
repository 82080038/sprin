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
        if (in_array($action, ['get_jenis_list', 'get_jenis_detail', 'create_jenis', 'update_jenis', 'toggle_jenis', 'delete_jenis'])) {
            // Set test session for AJAX
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = 'AJAX User';
            $_SESSION['user_id'] = 1;
            
            // Clear any output buffers for AJAX requests
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        
        if ($action === 'get_jenis_list') {
            $stmt = $pdo->query("SELECT * FROM master_jenis_pegawai ORDER BY kategori, nama_jenis");
            $jenisData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $jenisData]);
            exit;
        }
        
        if ($action === 'create_jenis') {
            // Auto-generate kode_jenis from kategori
            $kategori = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kategori', FILTER_SANITIZE_STRING);
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM master_jenis_pegawai WHERE kategori = ?");
            $stmt->execute([$kategori]);
            $count = $stmt->fetch()['count'] + 1;
            
            $kode_jenis = $kategori . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("INSERT INTO master_jenis_pegawai (nama_jenis, kategori, kode_jenis) VALUES (?, ?, ?)");
            $stmt->execute([
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_jenis', FILTER_SANITIZE_STRING),
                $kategori,
                $kode_jenis
            ]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Jenis pegawai berhasil ditambahkan!']);
            exit;
        }
        
        if ($action === 'get_jenis_detail') {
            $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM master_jenis_pegawai WHERE id = ?");
            $stmt->execute([$id]);
            $jenis = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $jenis]);
            exit;
        }
        
        if ($action === 'update_jenis') {
            // Check if kategori changed, if so, regenerate kode_jenis
            $stmt = $pdo->prepare("SELECT kategori FROM master_jenis_pegawai WHERE id = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $oldKategori = $stmt->fetch()['kategori'];
            
            $newKategori = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'kategori', FILTER_SANITIZE_STRING);
            $kode_jenis = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'existing_kode_jenis', FILTER_SANITIZE_STRING) ?? null;
            
            if ($oldKategori !== $newKategori) {
                // Kategori changed, regenerate kode_jenis
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM master_jenis_pegawai WHERE kategori = ? AND id != ?");
                $stmt->execute([$newKategori, filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
                $count = $stmt->fetch()['count'] + 1;
                
                $kode_jenis = $newKategori . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
            
            $stmt = $pdo->prepare("UPDATE master_jenis_pegawai SET nama_jenis = ?, kategori = ?, kode_jenis = ? WHERE id = ?");
            $stmt->execute([
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama_jenis', FILTER_SANITIZE_STRING),
                $newKategori,
                $kode_jenis,
                filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)
            ]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Jenis pegawai berhasil diperbarui!']);
            exit;
        }
        
        if ($action === 'toggle_jenis') {
            $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
            $status = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status', FILTER_SANITIZE_STRING) ?? 0;
            
            $stmt = $pdo->prepare("UPDATE master_jenis_pegawai SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $statusText = $status ? 'diaktifkan' : 'dinonaktifkan';
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "Jenis pegawai berhasil $statusText!"]);
            exit;
        }
        
        if ($action === 'delete_jenis') {
            // Check if jenis is used by personil
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jenis_pegawai = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus jenis pegawai yang masih digunakan oleh personil!']);
                exit;
            }
            
            // Check if jenis is used by pangkat
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pangkat WHERE id_jenis_pegawai = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            $pangkatCount = $stmt->fetchColumn();
            
            if ($pangkatCount > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus jenis pegawai yang masih digunakan oleh pangkat!']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM master_jenis_pegawai WHERE id = ?");
            $stmt->execute([filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING)]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Jenis pegawai berhasil dihapus!']);
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

// Get current jenis pegawai data grouped by kategori
try {
    $stmt = $pdo->query("SELECT * FROM master_jenis_pegawai ORDER BY kategori, nama_jenis");
    $jenisData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by kategori
    $groupedJenis = [];
    foreach ($jenisData as $jenis) {
        $kategori = $jenis['kategori'] ?? 'TIDAK DIKATEGORIKAN';
        
        if (!isset($groupedJenis[$kategori])) {
            $groupedJenis[$kategori] = [];
        }
        
        $groupedJenis[$kategori][] = $jenis;
    }
    
} catch (PDOException $e) {
    $jenisData = [];
    $groupedJenis = [];
}

$page_title = 'Manajemen Jenis Personil - POLRES Samosir';
include '../includes/components/header.php';
?>

<style>
/* Jenis Personil Management Styles */
.jenis-section {
    margin-bottom: 1.5rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.jenis-section-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 8px 8px 0 0;
    margin-bottom: 0;
}

.jenis-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.jenis-section-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0.25rem 0 0 0;
}

.jenis-table {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0 0 8px 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px var(--shadow-color);
}

.jenis-table table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.jenis-table th {
    background: var(--bg-secondary);
    color: #000000;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

.jenis-table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    color: #000000;
}

.jenis-table tr:hover {
    background: var(--hover-bg);
}

.jenis-actions {
    display: flex;
    gap: 0.5rem;
    white-space: nowrap;
    align-items: center;
}

.toggle-btn {
    padding: 0.25rem 0.5rem;
    border: 1px solid;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 60px;
    text-align: center;
}

.toggle-btn.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.toggle-btn.active:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.toggle-btn.inactive {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.toggle-btn.inactive:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.action-btn {
    padding: 0.25rem 0.5rem;
    border: 1px solid;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn.edit {
    background-color: transparent;
    border-color: #007bff;
    color: #007bff;
}

.action-btn.edit:hover {
    background-color: #007bff;
    color: white;
}

.action-btn.delete {
    background-color: transparent;
    border-color: #dc3545;
    color: #dc3545;
}

.action-btn.delete:hover {
    background-color: #dc3545;
    color: white;
}

.jenis-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background: var(--bg-tertiary);
    color: #000000;
}

.kode-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--primary-color);
    color: white;
    min-width: 60px;
    text-align: center;
}

.no-column {
    text-align: center;
    font-weight: 600;
    color: #000000;
    width: 50px;
}

.jenis-pers-column {
    font-weight: 500;
    color: #000000;
}

.jenis-pers-column strong {
    color: #000000;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.stats-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.table-responsive {
    border-radius: 0 0 8px 8px;
}

@media (max-width: 768px) {
    .jenis-table {
        font-size: 0.875rem;
    }
    
    .jenis-table th,
    .jenis-table td {
        padding: 0.5rem;
    }
    
    .jenis-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-users-cog me-2"></i>Manajemen Jenis Personil</h1>
        <p class="text-muted">Kelola data jenis pegawai POLRES Samosir</p>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title"><?php echo count($jenisData); ?></h3>
                    <p class="card-text">Total Jenis Pegawai</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title"><?php echo count($groupedJenis); ?></h3>
                    <p class="card-text">Kategori</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title"><?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM personil WHERE id_jenis_pegawai IS NOT NULL AND id_jenis_pegawai > 0");
                    echo $stmt->fetch()['total'];
                } catch (PDOException $e) {
                    echo '0';
                }
                ?>
                    </h3>
                    <p class="card-text">Personil Terdaftar</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>Tambah Jenis Pegawai
                </button>
                <button class="btn btn-info" onclick="refreshData()">
                    <i class="fas fa-sync me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Jenis Pegawai Tables by Kategori -->
    <?php if (empty($groupedJenis)): ?>
        <div class="empty-state">
            <i class="fas fa-users-cog"></i>
            <h5>Belum Ada Data Jenis Pegawai</h5>
            <p>Belum ada data jenis pegawai yang terdaftar dalam sistem.</p>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus me-2"></i>Tambah Jenis Pegawai Pertama
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($groupedJenis as $kategori => $jenisList): ?>
            <div class="jenis-section">
                <div class="jenis-section-header">
                    <h5 class="jenis-section-title">
                        <i class="fas fa-layer-group me-2"></i><?php echo htmlspecialchars($kategori); ?>
                    </h5>
                    <p class="jenis-section-subtitle">
                        <?php echo count($jenisList); ?> jenis pegawai
                    </p>
                </div>
                
                <div class="jenis-table">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="8%">No</th>
                                    <th width="55%">Jenis Personil</th>
                                    <th width="15%">Status</th>
                                    <th width="22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($jenisList as $jenis): 
                                ?>
                                    <tr>
                                        <td class="no-column"><?php echo $no++; ?></td>
                                        <td class="jenis-pers-column">
                                            <strong><?php echo htmlspecialchars($jenis['nama_jenis']); ?></strong>
                                            <?php if ($jenis['kode_jenis']): ?>
                                                <span class="kode-badge ms-2"><?php echo htmlspecialchars($jenis['kode_jenis']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="toggle-btn <?php echo ($jenis['is_active'] ?? 1) ? 'active' : 'inactive'; ?>" 
                                                    onclick="toggleStatus(<?php echo $jenis['id']; ?>, <?php echo ($jenis['is_active'] ?? 1) ? 0 : 1; ?>)">
                                                <?php echo ($jenis['is_active'] ?? 1) ? 'Aktif' : 'Non Aktif'; ?>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="jenis-actions">
                                                <button class="action-btn edit" onclick="editJenis(<?php echo $jenis['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="action-btn delete" onclick="deleteJenis(<?php echo $jenis['id']; ?>, '<?php echo htmlspecialchars($jenis['nama_jenis']); ?>')">
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
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="jenisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog me-2"></i>
                    <span id="modalTitle">Tambah Jenis Pegawai</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="jenisForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_jenis">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_jenis" class="form-label">Nama Jenis Pegawai</label>
                        <input type="text" class="form-control" id="nama_jenis" name="nama_jenis" required>
                        <div class="form-text">
                            Contoh: Aparatur Sipil Negara, Pegawai Pemerintah dengan Perjanjian Kerja
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori" name="kategori" required onchange="updateKodePreview()">
                            <option value="">Pilih Kategori</option>
                            <option value="POLRI">POLRI - Kepolisian Negara Republik Indonesia</option>
                            <option value="ASN">ASN - Aparatur Sipil Negara</option>
                            <option value="P3K">P3K - Pegawai Pemerintah dengan Perjanjian Kerja</option>
                            <option value="PPNS">PPNS - Pegawai Negeri Sipil</option>
                            <option value="LAINNYA">LAINNYA - Lain-lain</option>
                        </select>
                        <div class="form-text">
                            Pilih kategori untuk jenis pegawai ini
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Jenis (Auto-generated)</label>
                        <div class="form-control bg-light" id="kode_preview">
                            <span class="text-muted">Pilih kategori untuk melihat kode yang akan dibuat</span>
                        </div>
                        <div class="form-text">
                            Kode akan dibuat otomatis berdasarkan kategori (contoh: ASN_001)
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
let jenisData = <?php echo json_encode($jenisData); ?>;

// CRUD Functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Jenis Pegawai';
    document.getElementById('formAction').value = 'create_jenis';
    document.getElementById('formId').value = '';
    document.getElementById('nama_jenis').value = '';
    document.getElementById('kategori').value = '';
    document.getElementById('kode_preview').innerHTML = '<span class="text-muted">Pilih kategori untuk melihat kode yang akan dibuat</span>';
    
    const modal = new bootstrap.Modal(document.getElementById('jenisModal'));
    modal.show();
}

function editJenis(id) {
    const jenis = jenisData.find(j => j.id == id);
    if (!jenis) {
        alert('Data jenis pegawai tidak ditemukan!');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Edit Jenis Pegawai';
    document.getElementById('formAction').value = 'update_jenis';
    document.getElementById('formId').value = jenis.id;
    document.getElementById('nama_jenis').value = jenis.nama_jenis;
    document.getElementById('kategori').value = jenis.kategori;
    updateKodePreview(jenis.kode_jenis);
    
    const modal = new bootstrap.Modal(document.getElementById('jenisModal'));
    modal.show();
}

function updateKodePreview(existingKode = null) {
    const kategori = document.getElementById('kategori').value;
    const kodePreview = document.getElementById('kode_preview');
    
    if (!kategori) {
        kodePreview.innerHTML = '<span class="text-muted">Pilih kategori untuk melihat kode yang akan dibuat</span>';
        return;
    }
    
    if (existingKode) {
        // For edit mode, show existing kode
        kodePreview.innerHTML = `<strong>${existingKode}</strong> <span class="text-muted">(akan diperbarui jika kategori berubah)</span>`;
    } else {
        // For add mode, show preview of what will be generated
        // Count existing items in this category
        const existingCount = jenisData.filter(j => j.kategori === kategori).length;
        const nextNumber = existingCount + 1;
        const previewKode = kategori + '_' + String(nextNumber).padStart(3, '0');
        
        kodePreview.innerHTML = `<strong>${previewKode}</strong> <span class="text-muted">(akan dibuat otomatis)</span>`;
    }
}

function toggleStatus(id, newStatus) {
    console.log('Toggle status called for ID:', id, 'New status:', newStatus);
    
    fetch('jenis_personil.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'toggle_jenis',
            id: id,
            status: newStatus
        })
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        if (data.success) {
            showToast(data.message, 'success');
            // Delay refresh to allow user to see the toast
            setTimeout(() => {
                refreshData();
            }, 1000);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error: Terjadi kesalahan saat mengubah status', 'error');
    });
}

// Test function - call this from browser console to test toast
function testToast() {
    showToast('Test toast notification!', 'success');
    console.log('Test toast called - check top-right corner');
}

function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        min-width: 250px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: auto;
        cursor: pointer;
    `;
    
    // Set background color based on type
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    toast.style.backgroundColor = colors[type] || colors.info;
    
    // Add icon based on type
    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ',
        warning: '⚠'
    };
    
    toast.innerHTML = `
        <span style="font-size: 18px; flex-shrink: 0;">${icons[type] || icons.info}</span>
        <span style="flex: 1;">${message}</span>
    `;
    
    // Add click to dismiss
    toast.addEventListener('click', () => {
        removeToast(toast);
    });
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        removeToast(toast);
    }, 4000);
}

function removeToast(toast) {
    if (!toast || !toast.parentNode) return;
    
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

function deleteJenis(id, nama) {
    if (!confirm(`Apakah Anda yakin ingin menghapus jenis pegawai "${nama}"?`)) {
        return;
    }
    
    fetch('jenis_personil.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'delete_jenis',
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
document.getElementById('jenisForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('jenis_personil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('jenisModal')).hide();
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
