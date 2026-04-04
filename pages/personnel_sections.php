<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_check.php';
$page_title = 'Data Personil per Bagian - POLRES Samosir';

// Get personnel data from database organized by sections
$sectionsData = null;
$error = null;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all personnel with their bagian information
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.nama,
            p.nrp,
            p.status_ket as ket,
            b.nama_bagian as section_name,
            pg.nama_pangkat as pangkat,
            j.nama_jabatan as jabatan,
            ROW_NUMBER() OVER (PARTITION BY b.nama_bagian ORDER BY p.nama) as row_num
        FROM personil p
        LEFT JOIN bagian b ON p.bagian_id = b.id
        LEFT JOIN pangkat pg ON p.pangkat_id = pg.id
        LEFT JOIN jabatan j ON p.jabatan_id = j.id
        WHERE p.is_deleted = 0 AND p.is_active = 1
        ORDER BY b.urutan, p.nama
    ");
    
    $personnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group personnel by sections
    $sections = [];
    foreach ($personnelData as $person) {
        $sectionName = $person['section_name'] ?: 'TIDAK ADA BAGIAN';
        
        if (!isset($sections[$sectionName])) {
            $sections[$sectionName] = [
                'section_name' => $sectionName,
                'row_number' => count($sections) + 1,
                'personnel_count' => 0,
                'personnel' => []
            ];
        }
        
        $sections[$sectionName]['personnel'][] = [
            'no' => $person['row_num'],
            'nama' => $person['nama'],
            'pangkat' => $person['pangkat'] ?: '-',
            'nrp' => $person['nrp'],
            'jabatan' => $person['jabatan'] ?: '-',
            'ket' => $person['ket'] ?: ''
        ];
        $sections[$sectionName]['personnel_count']++;
    }
    
    // Convert to indexed array
    $sectionsData = [
        'metadata' => [
            'source_file' => 'Database MySQL',
            'sheet_name' => 'Personnel Database',
            'total_sections' => count($sections),
            'total_personnel' => array_sum(array_column($sections, 'personnel_count')),
            'created_at' => date('Y-m-d\TH:i:s'),
            'description' => 'Personnel data organized by sections from database'
        ],
        'sections' => array_values($sections)
    ];
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/components/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-users me-2"></i>Data Personil per Bagian</h2>
                    <p class="text-muted mb-0">Data personil POLRES Samosir berdasarkan struktur organisasi</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="expandAll()">
                        <i class="fas fa-expand-alt me-1"></i>Expand All
                    </button>
                    <button class="btn btn-outline-secondary" onclick="collapseAll()">
                        <i class="fas fa-compress-alt me-1"></i>Collapse All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($sectionsData): ?>

        <!-- Metadata Display -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Sumber File:</strong> <?php echo htmlspecialchars($sectionsData['metadata']['source_file']); ?></p>
                                <p><strong>Total Bagian:</strong> <?php echo $sectionsData['metadata']['total_sections']; ?> bagian</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Personil:</strong> <?php echo $sectionsData['metadata']['total_personnel']; ?> orang</p>
                                <p><strong>Tanggal Dibuat:</strong> <?php echo date('d F Y H:i:s', strtotime($sectionsData['metadata']['created_at'])); ?></p>
                            </div>
                        </div>
                        <p class="text-muted mt-2"><?php echo htmlspecialchars($sectionsData['metadata']['description']); ?></p>
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
                                <div class="h4 text-success"><?php echo $sectionsData['metadata']['total_sections']; ?></div>
                                <small class="text-muted">Bagian</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-primary"><?php echo $sectionsData['metadata']['total_personnel']; ?></div>
                                <small class="text-muted">Personil</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion Structure -->
        <div class="accordion" id="sectionsAccordion">
            <?php foreach ($sectionsData['sections'] as $index => $section): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-section-<?php echo $index; ?>">
                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-section-<?php echo $index; ?>"
                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                aria-controls="collapse-section-<?php echo $index; ?>">
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
                    <div id="collapse-section-<?php echo $index; ?>"
                         class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                         aria-labelledby="heading-section-<?php echo $index; ?>">
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
                                            <?php foreach ($section['personnel'] as $person): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo htmlspecialchars($person['no']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($person['nama']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($person['pangkat']); ?></span>
                                                    </td>
                                                    <td class="font-monospace small"><?php echo htmlspecialchars($person['nrp']); ?></td>
                                                    <td><?php echo htmlspecialchars($person['jabatan']); ?></td>
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
        </div>

    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Data tidak tersedia.
        </div>
    <?php endif; ?>
</div>

<script>
// Accordion control functions
function expandAll() {
    const buttons = document.querySelectorAll('.accordion-button.collapsed');
    buttons.forEach(button => {
        const collapse = new bootstrap.Collapse(button.getAttribute('data-bs-target'));
        collapse.show();
    });
}

function collapseAll() {
    const buttons = document.querySelectorAll('.accordion-button:not(.collapsed)');
    buttons.forEach(button => {
        const collapse = new bootstrap.Collapse(button.getAttribute('data-bs-target'));
        collapse.hide();
    });
}

// Add smooth scrolling for better UX
document.querySelectorAll('.accordion-button').forEach(button => {
    button.addEventListener('click', function() {
        setTimeout(() => {
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 350);
    });
});
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
