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

$page_title = 'Manajemen Jabatan - POLRES Samosir';
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
        $stmt = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur WHERE is_active = 1 ORDER BY urutan");
        $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $unsurData]);
        exit;
    }
    
    if ($action === 'get_jabatan_list') {
        $unsurId = $_POST['id_unsur'] ?? null;
        
        $sql = "
            SELECT 
                j.id,
                j.nama_jabatan,
                j.id_unsur,
                u.nama_unsur,
                u.urutan as urutan_unsur,
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
        
        $sql .= " ORDER BY u.urutan ASC, j.nama_jabatan ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jabatanData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $jabatanData]);
        exit;
    }
    
    if ($action === 'get_jabatan_detail') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.nama_jabatan,
                j.id_unsur,
                u.nama_unsur,
                u.urutan as urutan_unsur
            FROM jabatan j
            LEFT JOIN unsur u ON j.id_unsur = u.id
            WHERE j.id = ?
        ");
        $stmt->execute([$id]);
        $jabatan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $jabatan]);
        exit;
    }
    
    if ($action === 'create_jabatan') {
        $nama_jabatan = trim($_POST['nama_jabatan'] ?? '');
        $id_unsur = $_POST['id_unsur'] ?? null;
        
        if (empty($nama_jabatan)) {
            echo json_encode(['success' => false, 'message' => 'Nama jabatan wajib diisi']);
            exit;
        }
        
        if (empty($id_unsur)) {
            echo json_encode(['success' => false, 'message' => 'Unsur wajib dipilih']);
            exit;
        }
        
        // Check for duplicate
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ?");
        $checkStmt->execute([$nama_jabatan, $id_unsur]);
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Jabatan dengan nama tersebut sudah ada di unsur ini']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO jabatan (nama_jabatan, id_unsur) VALUES (?, ?)");
        $stmt->execute([$nama_jabatan, $id_unsur]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Jabatan berhasil ditambahkan',
            'id' => $pdo->lastInsertId()
        ]);
        exit;
    }
    
    if ($action === 'update_jabatan') {
        $id = $_POST['id'] ?? 0;
        $nama_jabatan = trim($_POST['nama_jabatan'] ?? '');
        $id_unsur = $_POST['id_unsur'] ?? null;
        
        if (empty($nama_jabatan)) {
            echo json_encode(['success' => false, 'message' => 'Nama jabatan wajib diisi']);
            exit;
        }
        
        if (empty($id_unsur)) {
            echo json_encode(['success' => false, 'message' => 'Unsur wajib dipilih']);
            exit;
        }
        
        // Check for duplicate (excluding current record)
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan = ? AND id_unsur = ? AND id != ?");
        $checkStmt->execute([$nama_jabatan, $id_unsur, $id]);
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Jabatan dengan nama tersebut sudah ada di unsur ini']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE jabatan SET nama_jabatan = ?, id_unsur = ? WHERE id = ?");
        $stmt->execute([$nama_jabatan, $id_unsur, $id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Jabatan berhasil diperbarui',
            'rows_affected' => $stmt->rowCount()
        ]);
        exit;
    }
    
    if ($action === 'delete_jabatan') {
        $id = $_POST['id'] ?? 0;
        
        // Check if jabatan has personil
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_deleted = FALSE");
        $checkStmt->execute([$id]);
        $personilCount = $checkStmt->fetchColumn();
        
        if ($personilCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Tidak dapat menghapus jabatan yang masih memiliki $personilCount personil!"
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Jabatan berhasil dihapus',
            'rows_affected' => $stmt->rowCount()
        ]);
        exit;
    }
}

// Get current data
$stmt = $pdo->query("
    SELECT 
        j.id,
        j.nama_jabatan,
        j.id_unsur,
        u.nama_unsur,
        u.urutan as urutan_unsur,
        (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = FALSE AND p.is_active = TRUE) as personil_count
    FROM jabatan j
    LEFT JOIN unsur u ON j.id_unsur = u.id
    ORDER BY u.urutan ASC, j.nama_jabatan ASC
");
$jabatanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unsur data for dropdown
$stmt = $pdo->query("SELECT id, nama_unsur, urutan FROM unsur WHERE is_active = 1 ORDER BY urutan");
$unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-tie me-2"></i>Manajemen Jabatan</h1>
        <p class="text-muted">Kelola data jabatan struktural POLRES Samosir</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo count($jabatanData); ?></div>
                <div class="label">Total Jabatan</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php echo count($unsurData); ?></div>
                <div class="label">Total Unsur</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $totalPersonil = 0;
                    foreach ($jabatanData as $jabatan) {
                        $totalPersonil += $jabatan['personil_count'];
                    }
                    echo $totalPersonil;
                ?></div>
                <div class="label">Total Personil</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="number"><?php 
                    $avgPersonil = count($jabatanData) > 0 ? round($totalPersonil / count($jabatanData), 1) : 0;
                    echo $avgPersonil;
                ?></div>
                <div class="label">Rata-rata Personil/Jabatan</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mb-4">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Tambah Jabatan
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
        <button class="btn btn-success" onclick="exportData()">
            <i class="fas fa-download me-2"></i>Export
        </button>
    </div>

    <!-- Filter by Unsur -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-2"></i>Filter
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="filterUnsur" class="form-label">Filter berdasarkan Unsur:</label>
                    <select class="form-select" id="filterUnsur" onchange="filterByUnsur()">
                        <option value="">-- Semua Unsur --</option>
                        <?php foreach ($unsurData as $unsur): ?>
                            <option value="<?php echo $unsur['id']; ?>">
                                <?php echo htmlspecialchars($unsur['nama_unsur']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Jabatan Table Grouped by Unsur -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i>Daftar Jabatan per Unsur
        </div>
        <div class="card-body">
            <div id="jabatanContainer">
                <?php
                // Group jabatan by unsur
                $jabatanByUnsur = [];
                foreach ($jabatanData as $jabatan) {
                    $unsurId = $jabatan['id_unsur'] ?? 0;
                    $unsurName = $jabatan['nama_unsur'] ?? 'Unknown';
                    $urutanUnsur = $jabatan['urutan_unsur'] ?? 999;
                    
                    if (!isset($jabatanByUnsur[$unsurId])) {
                        $jabatanByUnsur[$unsurId] = [
                            'nama_unsur' => $unsurName,
                            'urutan_unsur' => $urutanUnsur,
                            'jabatans' => []
                        ];
                    }
                    $jabatanByUnsur[$unsurId]['jabatans'][] = $jabatan;
                }
                
                // Sort by unsur order
                uasort($jabatanByUnsur, function($a, $b) {
                    return $a['urutan_unsur'] - $b['urutan_unsur'];
                });
                
                foreach ($jabatanByUnsur as $unsurId => $unsurGroup):
                ?>
                <div class="unsur-section mb-4">
                    <div class="unsur-header d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>
                            <?php echo htmlspecialchars($unsurGroup['nama_unsur']); ?>
                        </h4>
                        <span class="badge bg-primary">
                            <?php echo count($unsurGroup['jabatans']); ?> Jabatan
                        </span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Jabatan</th>
                                    <th width="150">Jumlah Personil</th>
                                    <th width="100">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($unsurGroup['jabatans'] as $jabatan): ?>
                                <tr id="jabatan-row-<?php echo $jabatan['id']; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($jabatan['nama_jabatan']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $jabatan['personil_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $jabatan['personil_count'] > 0 ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $jabatan['personil_count'] > 0 ? 'Aktif' : 'Kosong'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewJabatan(<?php echo $jabatan['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editJabatan(<?php echo $jabatan['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteJabatan(<?php echo $jabatan['id']; ?>)">
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
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="jabatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-tie me-2"></i>
                    <span id="modalTitle">Tambah Jabatan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="jabatanForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_jabatan">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" required>
                        <div class="form-text">
                            Contoh: KASAT RESKRIM, KANIT RESNARKOBA, PS. INTELKAM, dll
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_unsur" class="form-label">Unsur</label>
                        <select class="form-select" id="id_unsur" name="id_unsur" required>
                            <option value="">-- Pilih Unsur --</option>
                            <?php foreach ($unsurData as $unsur): ?>
                                <option value="<?php echo $unsur['id']; ?>">
                                    <?php echo htmlspecialchars($unsur['nama_unsur']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Pilih unsur organisasi untuk jabatan ini
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
                    Detail Personil Jabatan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nama Jabatan:</strong>
                        <span id="viewJabatanNama"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Unsur:</strong>
                        <span id="viewJabatanUnsur"></span>
                    </div>
                </div>
                <hr>
                <h6>Daftar Personil:</h6>
                <div id="viewJabatanPersonil">
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
let jabatanData = <?php echo json_encode($jabatanData); ?>;
let unsurData = <?php echo json_encode($unsurData); ?>;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Jabatan';
    document.getElementById('formAction').value = 'create_jabatan';
    document.getElementById('formId').value = '';
    document.getElementById('nama_jabatan').value = '';
    document.getElementById('id_unsur').value = '';
    
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

function editJabatan(jabatanId) {
    fetch('jabatan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
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
    const jabatan = jabatanData.find(j => j.id == jabatanId);
    const jabatanName = jabatan ? jabatan.nama_jabatan : 'jabatan ini';
    
    if (confirm(`Apakah Anda yakin ingin menghapus "${jabatanName}"?`)) {
        fetch('jabatan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
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
            showAlert('danger', 'Terjadi kesalahan saat menghapus jabatan');
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
                    personilHtml = '<p class="text-muted">Tidak ada personil di jabatan ini.</p>';
                }
                
                document.getElementById('viewJabatanPersonil').innerHTML = personilHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('viewJabatanPersonil').innerHTML = '<p class="text-danger">Gagal memuat data personil.</p>';
        });
    
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function filterByUnsur() {
    const unsurId = document.getElementById('filterUnsur').value;
    
    fetch('jabatan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'get_jabatan_list',
            id_unsur: unsurId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderJabatanTable(data.data);
        } else {
            showAlert('danger', 'Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memfilter data');
    });
}

function renderJabatanTable(jabatanList) {
    // Group by unsur
    const jabatanByUnsur = {};
    jabatanList.forEach(jabatan => {
        const unsurId = jabatan.id_unsur || 0;
        if (!jabatanByUnsur[unsurId]) {
            jabatanByUnsur[unsurId] = {
                nama_unsur: jabatan.nama_unsur || 'Unknown',
                urutan_unsur: jabatan.urutan_unsur || 999,
                jabatans: []
            };
        }
        jabatanByUnsur[unsurId].jabatans.push(jabatan);
    });
    
    // Sort by unsur order
    const sortedUnsurIds = Object.keys(jabatanByUnsur).sort((a, b) => {
        return jabatanByUnsur[a].urutan_unsur - jabatanByUnsur[b].urutan_unsur;
    });
    
    let html = '';
    sortedUnsurIds.forEach(unsurId => {
        const unsurGroup = jabatanByUnsur[unsurId];
        html += `
            <div class="unsur-section mb-4">
                <div class="unsur-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        ${unsurGroup.nama_unsur}
                    </h4>
                    <span class="badge bg-primary">
                        ${unsurGroup.jabatans.length} Jabatan
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Jabatan</th>
                                <th width="150">Jumlah Personil</th>
                                <th width="100">Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        unsurGroup.jabatans.forEach((jabatan, index) => {
            html += `
                <tr id="jabatan-row-${jabatan.id}">
                    <td>${index + 1}</td>
                    <td>
                        <strong>${jabatan.nama_jabatan}</strong>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            ${jabatan.personil_count}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${jabatan.personil_count > 0 ? 'bg-success' : 'bg-warning'}">
                            ${jabatan.personil_count > 0 ? 'Aktif' : 'Kosong'}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
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
                    </td>
                </tr>
            `;
        });
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    });
    
    document.getElementById('jabatanContainer').innerHTML = html;
}

function refreshData() {
    window.location.reload();
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
    
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'daftar_jabatan_polres_samosir.txt';
    a.click();
    window.URL.revokeObjectURL(url);
}

function showAlert(type, message) {
    // Simple alert using Bootstrap
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Form submission
document.getElementById('jabatanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = formData.get('action');
    
    fetch('jabatan.php', {
        method: 'POST',
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
