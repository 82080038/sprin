<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/config.php'; 
require_once __DIR__ . '/../core/auth_check.php'; 
$page_title = 'Manajemen Jabatan & Data Personil - POLRES Samosir'; 

// Get unsur data for modal
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET, 
        DB_USER, 
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $unsurStmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan, nama_unsur");
    $unsurData = $unsurStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unsurData = [];
}

// Get personnel data from database
$sectionsData = null;
$jsonError = null;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get sections and personnel data
    $sectionsQuery = "
        SELECT
            b.nama_bagian as section_name,
            COUNT(p.id) as personnel_count,
            GROUP_CONCAT(
                JSON_OBJECT(
                    'no', ROW_NUMBER() OVER (PARTITION BY b.id ORDER BY p.nama),
                    'nama', p.nama,
                    'pangkat', COALESCE(pg.nama_pangkat, '-'),
                    'nrp', COALESCE(p.nrp, '-'),
                    'jabatan', COALESCE(j.nama_jabatan, '-'),
                    'ket', COALESCE(p.status_ket, '')
                )
                SEPARATOR '|||'
            ) as personnel_json
        FROM bagian b
        LEFT JOIN jabatan j ON j.id_bagian = b.id
        LEFT JOIN personil p ON p.jabatan_id = j.id AND p.is_deleted = 0 AND p.is_active = 1
        LEFT JOIN pangkat pg ON p.pangkat_id = pg.id
        GROUP BY b.id, b.nama_bagian
        ORDER BY b.urutan, b.nama_bagian
    ";

    $stmt = $pdo->query($sectionsQuery);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sections = [];
    $totalPersonnel = 0;
    
    foreach ($results as $index => $result) {
        $personnel = [];
        if (!empty($result['personnel_json'])) {
            $personnelArray = explode('|||', $result['personnel_json']);
            foreach ($personnelArray as $personJson) {
                if (!empty($personJson)) {
                    $person = json_decode($personJson, true);
                    if ($person) {
                        $personnel[] = $person;
                    }
                }
            }
        }
        
        $sections[] = [
            'section_name' => $result['section_name'],
            'row_number' => $index + 1,
            'personnel_count' => $result['personnel_count'],
            'personnel' => $personnel
        ];
        
        $totalPersonnel += $result['personnel_count'];
    }
    
    $sectionsData = [
        'metadata' => [
            'source_file' => 'Database MySQL',
            'sheet_name' => 'Personnel Database',
            'total_sections' => count($sections),
            'total_personnel' => $totalPersonnel,
            'created_at' => date('Y-m-d\TH:i:s'),
            'description' => 'Personnel data organized by sections from database'
        ],
        'sections' => $sections
    ];
    
} catch (PDOException $e) {
    $jsonError = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $jsonError = $e->getMessage();
}

include __DIR__ . '/../includes/components/header.php'; ?>

<!-- Modal -->
<div class="modal fade" id="jabatanModal" tabindex="-1" aria-labelledby="jabatanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jabatanModalLabel">Tambah Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="jabatanForm">
                    <input type="hidden" id="formAction" name="action" value="create">
                    <input type="hidden" id="formId" name="id" value="">
                    
                    <div class="mb-3">
                        <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" required>
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
                    
                    <div class="mb-3">
                        <label for="id_bagian" class="form-label">Bagian</label>
                        <select class="form-select" id="id_bagian" name="id_bagian" required>
                            <option value="">Pilih Bagian</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Non-aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveJabatan()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-users me-2"></i>Data Personil POLRES Samosir</h2>
            <p class="text-muted mb-4">Data personil terorganisir berdasarkan bagian/bidang</p>

            <!-- Metadata Info -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Data</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Sumber Data:</strong> Database MySQL</p>
                                    <p><strong>Total Bagian:</strong> <?php echo $personnelStats['total_sections']; ?> bagian</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Personil:</strong> <?php echo $personnelStats['total_personnel']; ?> orang</p>
                                    <p><strong>Terakhir Update:</strong> <?php echo date('d F Y H:i:s'); ?></p>
                                </div>
                            </div>
                            <p class="text-muted mt-2">Data personil dari database sistem POLRES Samosir</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 text-success"><?php echo $personnelStats['total_sections']; ?></div>
                                    <small class="text-muted">Bagian</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-primary"><?php echo $personnelStats['total_personnel']; ?></div>
                                    <small class="text-muted">Personil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Data Personil per Bagian</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="expandAll()">
                        <i class="fas fa-expand-alt me-1"></i>Expand All
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="collapseAll()">
                        <i class="fas fa-compress-alt me-1"></i>Collapse All
                    </button>
                </div>
            </div>

            <!-- Personnel Accordion -->
            <div class="accordion" id="personnelAccordion">
                <?php if (empty($personnelData)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Belum ada data personil di database.
                    </div>
                <?php else: ?>
                    <?php foreach ($personnelData as $index => $section): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-personnel-<?php echo $index; ?>">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-personnel-<?php echo $index; ?>"
                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                        aria-controls="collapse-personnel-<?php echo $index; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="text-start">
                                            <i class="fas fa-building me-2"></i>
                                            <strong><?php echo htmlspecialchars($section['section_name']); ?></strong>
                                            <div class="small text-muted mt-1">
                                                <?php echo $section['personnel_count']; ?> Personil
                                            </div>
                                        </div>
                                        <div class="badge bg-primary me-3">
                                            <?php echo $section['personnel_count']; ?> orang
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-personnel-<?php echo $index; ?>"
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                 aria-labelledby="heading-personnel-<?php echo $index; ?>">
                                <div class="accordion-body p-0">
                                    <?php if (!empty($section['personnel'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="35%">Nama</th>
                                                        <th width="15%">Pangkat</th>
                                                        <th width="15%">NRP</th>
                                                        <th width="25%">Jabatan</th>
                                                        <th width="5%">Ket</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($section['personnel'] as $person): ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo $counter++; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($person['nama'] ?? '-'); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($person['pangkat'] ?? '-'); ?></span>
                                                            </td>
                                                            <td class="font-monospace small"><?php echo htmlspecialchars($person['nrp'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($person['jabatan'] ?? '-'); ?></td>
                                                            <td>
                                                                <?php if (!empty($person['ket'])): ?>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($person['ket']); ?></small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning m-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Tidak ada data personil untuk bagian ini.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Accordion control functions
function expandAll() {
    const buttons = document.querySelectorAll('#personnelAccordion .accordion-button.collapsed');
    buttons.forEach(button => {
        const collapse = new bootstrap.Collapse(button.getAttribute('data-bs-target'));
        collapse.show();
    });
}

function collapseAll() {
    const buttons = document.querySelectorAll('#personnelAccordion .accordion-button:not(.collapsed)');
    buttons.forEach(button => {
        const collapse = new bootstrap.Collapse(button.getAttribute('data-bs-target'));
        collapse.hide();
    });
}
</script>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('jabatanModalLabel').textContent = 'Tambah Jabatan';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('jabatanForm').reset();
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

function openAddModalForUnsur(unsurId) {
    document.getElementById('jabatanModalLabel').textContent = 'Tambah Jabatan';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('id_unsur').value = unsurId;
    updateBagianOptions();
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

function openAddModalForBagian(bagianId) {
    document.getElementById('jabatanModalLabel').textContent = 'Tambah Jabatan';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('id_bagian').value = bagianId;
    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
}

function editJabatan(id) {
    // Load jabatan data and open modal
    fetch('<?php echo API_BASE_URL; ?>/v1/jabatan.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('jabatanModalLabel').textContent = 'Edit Jabatan';
                document.getElementById('formAction').value = 'update';
                document.getElementById('formId').value = id;
                document.getElementById('nama_jabatan').value = data.jabatan.nama_jabatan;
                document.getElementById('id_unsur').value = data.jabatan.id_unsur;
                updateBagianOptions(() => {
                    document.getElementById('id_bagian').value = data.jabatan.id_bagian;
                });
                document.getElementById('is_active').value = data.jabatan.is_active;
                new bootstrap.Modal(document.getElementById('jabatanModal')).show();
            }
        });
}

function deleteJabatan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) {
        fetch('<?php echo API_BASE_URL; ?>/v1/jabatan.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function saveJabatan() {
    const form = document.getElementById('jabatanForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const id = formData.get('id');
    
    const data = {
        action: action,
        id: id,
        nama_jabatan: formData.get('nama_jabatan'),
        id_unsur: formData.get('id_unsur'),
        id_bagian: formData.get('id_bagian'),
        is_active: formData.get('is_active')
    };
    
    const url = '<?php echo API_BASE_URL; ?>/v1/jabatan.php';
    const method = action === 'create' ? 'POST' : 'PUT';
    
    fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('jabatanModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function updateBagianOptions(callback) {
    const unsurId = document.getElementById('id_unsur').value;
    const bagianSelect = document.getElementById('id_bagian');
    
    bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
    
    if (unsurId) {
        fetch('<?php echo API_BASE_URL; ?>/v1/bagian.php?unsur_id=' + unsurId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.bagians.forEach(bagian => {
                        const option = document.createElement('option');
                        option.value = bagian.id;
                        option.textContent = bagian.nama_bagian;
                        bagianSelect.appendChild(option);
                    });
                    if (callback) callback();
                }
            });
    }
}

// Update bagian options when unsur changes
document.getElementById('id_unsur').addEventListener('change', updateBagianOptions);
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
