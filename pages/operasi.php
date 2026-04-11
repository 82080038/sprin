<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator', 'viewer');

// Setup page header
$page_header = [
    'title' => 'Daftar Operasi',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => BASE_URL . '/pages/main.php'],
        ['text' => 'Daftar Operasi', 'active' => true]
    ]
];

// Include Bootstrap layout
include __DIR__ . '/../includes/components/bootstrap_layout.php';

// Fetch operations from DB
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->query("SELECT * FROM operations ORDER BY operation_month DESC, operation_date DESC, created_at DESC");
    $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $operations = [];
    $dbError = $e->getMessage();
}

$labelTingkat = [
    'terpusat'           => ['label' => 'Terpusat',          'class' => 'bg-danger'],
    'kewilayahan_polda'  => ['label' => 'Kewilayahan Polda', 'class' => 'bg-warning text-dark'],
    'kewilayahan_polres' => ['label' => 'Kewilayahan Polres','class' => 'bg-primary'],
    'imbangan'           => ['label' => 'Imbangan',          'class' => 'bg-secondary'],
];
$labelJenis = [
    'intelijen'            => ['label' => 'Intelijen',             'class' => 'bg-dark'],
    'pengamanan_kegiatan'  => ['label' => 'Pengamanan Kegiatan',   'class' => 'bg-info text-dark'],
    'pemeliharaan_keamanan'=> ['label' => 'Pemeliharaan Keamanan', 'class' => 'bg-primary'],
    'penegakan_hukum'      => ['label' => 'Penegakan Hukum',       'class' => 'bg-danger'],
    'pemulihan_keamanan'   => ['label' => 'Pemulihan Keamanan',    'class' => 'bg-warning text-dark'],
    'kontinjensi'          => ['label' => 'Kontinjensi',           'class' => 'bg-secondary'],
    'lainnya'              => ['label' => 'Lainnya',               'class' => 'bg-light text-dark'],
];
$labelStatus = [
    'planned'   => ['label' => 'Masih Rencana',      'class' => 'bg-secondary'],
    'active'    => ['label' => 'Sedang Berlangsung',  'class' => 'bg-warning text-dark'],
    'completed' => ['label' => 'Selesai',             'class' => 'bg-success'],
];

// Add sample operations data for testing
$sample_operations = [
    [
        'id' => 1,
        'sprint_number' => 'Sprint/001/OPS/IV/2024',
        'operation_name' => 'Operasi Kewilayahan Rutin',
        'tingkat_operasi' => 'kewilayahan_polres',
        'jenis_operasi' => 'pemeliharaan_keamanan',
        'operation_date' => '2024-04-01',
        'end_date' => '2024-04-03',
        'status' => 'completed',
        'location' => 'Wilayah Hukum Polres Samosir',
        'personnel_count' => 25,
        'budget' => 5000000,
        'description' => 'Operasi kewilayahan rutin untuk menjaga kamtibmas di wilayah Polres Samosir'
    ],
    [
        'id' => 2,
        'sprint_number' => 'Sprint/002/OPS/IV/2024',
        'operation_name' => 'Operasi Pemberantasan Miras',
        'tingkat_operasi' => 'kewilayahan_polres',
        'jenis_operasi' => 'penegakan_hukum',
        'operation_date' => '2024-04-10',
        'end_date' => '2024-04-15',
        'status' => 'active',
        'location' => 'Kecamatan Pangururan',
        'personnel_count' => 30,
        'budget' => 7500000,
        'description' => 'Operasi pemberantasan minuman keras di wilayah Kecamatan Pangururan'
    ],
    [
        'id' => 3,
        'sprint_number' => 'Sprint/003/OPS/IV/2024',
        'operation_name' => 'Operasi Pengamanan Acara',
        'tingkat_operasi' => 'kewilayahan_polres',
        'jenis_operasi' => 'pengamanan_kegiatan',
        'operation_date' => '2024-04-20',
        'end_date' => '2024-04-22',
        'status' => 'planned',
        'location' => 'Pantai Parbaba',
        'personnel_count' => 20,
        'budget' => 3000000,
        'description' => 'Pengamanan acara festival budaya di Pantai Parbaba'
    ],
    [
        'id' => 4,
        'sprint_number' => 'Sprint/004/OPS/IV/2024',
        'operation_name' => 'Operasi Patroli Blue Light',
        'tingkat_operasi' => 'kewilayahan_polres',
        'jenis_operasi' => 'pemeliharaan_keamanan',
        'operation_date' => '2024-04-25',
        'end_date' => '2024-04-25',
        'status' => 'planned',
        'location' => 'Kota Pangururan',
        'personnel_count' => 15,
        'budget' => 2000000,
        'description' => 'Operasi patroli blue light untuk cipta kondisi aman'
    ]
];

// Use sample data if no real data
$operations = !empty($operations) ? $operations : $sample_operations;

// Calculate statistics
$stats = [
    'total' => count($operations),
    'active' => count(array_filter($operations, fn($op) => $op['status'] === 'active')),
    'completed' => count(array_filter($operations, fn($op) => $op['status'] === 'completed')),
    'planned' => count(array_filter($operations, fn($op) => $op['status'] === 'planned')),
];
?>

<style>
.operations-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    text-align: center;
}

.operations-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.stat-card p {
    margin: 0;
    color: var(--secondary-color);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.operations-table {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.operations-table .table {
    margin: 0;
}

.operations-table .table th {
    background: var(--light-color);
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: var(--dark-color);
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.operations-table .table td {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--secondary-color);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

@media (max-width: 768px) {
    .operations-header h2 {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .operations-table .table th,
    .operations-table .table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>

<div class="container">
    <!-- Operations Header -->
    <div class="operations-header">
        <h2><i class="fas fa-shield-alt me-3"></i>Daftar Operasi Kepolisian</h2>
        <p class="mb-0">Manajemen dan tracking operasi kepolisian POLRES Samosir</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Operasi</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['active']; ?></h3>
            <p>Sedang Berlangsung</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['completed']; ?></h3>
            <p>Selesai</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['planned']; ?></h3>
            <p>Direncanakan</p>
        </div>
    </div>

    <!-- Add Operation Button -->
    <div class="mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOperationModal">
            <i class="fas fa-plus me-2"></i>Tambah Operasi Baru
        </button>
    </div>

    <!-- Operations Table -->
    <div class="operations-table">
        <?php if (!empty($operations)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Sprint</th>
                            <th>Nama Operasi</th>
                            <th>Tingkat</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($operations as $op): ?>
                            <tr>
                                <td>
                                    <code><?php echo htmlspecialchars($op['sprint_number'] ?? '-'); ?></code>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($op['operation_name']); ?></strong>
                                    <?php if (!empty($op['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($op['description'], 0, 100)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $labelTingkat[$op['tingkat_operasi']]['class'] ?? 'bg-secondary'; ?>">
                                        <?php echo $labelTingkat[$op['tingkat_operasi']]['label'] ?? $op['tingkat_operasi']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $labelJenis[$op['jenis_operasi']]['class'] ?? 'bg-secondary'; ?>">
                                        <?php echo $labelJenis[$op['jenis_operasi']]['label'] ?? $op['jenis_operasi']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $startDate = !empty($op['operation_date']) ? date('d M Y', strtotime($op['operation_date'])) : '-';
                                    $endDate = !empty($op['end_date']) ? date('d M Y', strtotime($op['end_date'])) : '';
                                    echo $startDate . ($endDate ? ' - ' . $endDate : '');
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $labelStatus[$op['status']]['class'] ?? 'bg-secondary'; ?>">
                                        <?php echo $labelStatus[$op['status']]['label'] ?? $op['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewOperation(<?php echo $op['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editOperation(<?php echo $op['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteOperation(<?php echo $op['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shield-alt"></i>
                <h4>Belum Ada Data Operasi</h4>
                <p>Belum ada data operasi yang tercatat dalam sistem.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOperationModal">
                    <i class="fas fa-plus me-2"></i>Tambah Operasi Pertama
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Operation Modal -->
<div class="modal fade" id="addOperationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Operasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addOperationForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="operation_name" class="form-label">Nama Operasi</label>
                            <input type="text" class="form-control" id="operation_name" name="operation_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tingkat_operasi" class="form-label">Tingkat Operasi</label>
                            <select class="form-select" id="tingkat_operasi" name="tingkat_operasi" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="terpusat">Terpusat</option>
                                <option value="kewilayahan_polda">Kewilayahan Polda</option>
                                <option value="kewilayahan_polres">Kewilayahan Polres</option>
                                <option value="imbangan">Imbangan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jenis_operasi" class="form-label">Jenis Operasi</label>
                            <select class="form-select" id="jenis_operasi" name="jenis_operasi" required>
                                <option value="">Pilih Jenis</option>
                                <option value="intelijen">Intelijen</option>
                                <option value="pengamanan_kegiatan">Pengamanan Kegiatan</option>
                                <option value="pemeliharaan_keamanan">Pemeliharaan Keamanan</option>
                                <option value="penegakan_hukum">Penegakan Hukum</option>
                                <option value="pemulihan_keamanan">Pemulihan Keamanan</option>
                                <option value="kontinjensi">Kontinjensi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="operation_month" class="form-label">Bulan Operasi</label>
                            <input type="month" class="form-control" id="operation_month" name="operation_month" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="operation_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="operation_date" name="operation_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="Wilayah Hukum Polres Samosir">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="personil_count" class="form-label">Jumlah Personil</label>
                            <input type="number" class="form-control" id="personil_count" name="personil_count" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dukungan" class="form-label">Dukungan (Rp)</label>
                            <input type="number" class="form-control" id="dukungan" name="dukungan" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveOperationBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/bootstrap_layout_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Save operation
    document.getElementById('saveOperationBtn').addEventListener('click', function() {
        saveOperation();
    });
});

function saveOperation() {
    const form = document.getElementById('addOperationForm');
    const formData = new FormData(form);
    
    // Add action
    formData.append('action', 'create');
    
    fetch('<?php echo API_BASE_URL; ?>/operasi_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Operasi berhasil ditambahkan');
            bootstrap.Modal.getInstance(document.getElementById('addOperationModal')).hide();
            form.reset();
            location.reload();
        } else {
            toastr.error(data.message || 'Gagal menambah operasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Terjadi kesalahan saat menambah operasi');
    });
}

function viewOperation(id) {
    // Implement view operation functionality
    window.open(`operasi_detail.php?id=${id}`, '_blank');
}

function editOperation(id) {
    // Implement edit operation functionality
    window.location.href = `operasi_edit.php?id=${id}`;
}

function deleteOperation(id) {
    if (confirm('Apakah Anda yakin ingin menghapus operasi ini?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch('<?php echo API_BASE_URL; ?>/operasi_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Operasi berhasil dihapus');
                location.reload();
            } else {
                toastr.error(data.message || 'Gagal menghapus operasi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Terjadi kesalahan saat menghapus operasi');
        });
    }
}
</script>
