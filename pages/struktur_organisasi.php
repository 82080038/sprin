<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

SessionManager::start();

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Struktur Organisasi - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Fetch data from database
try {
    require_once __DIR__ . '/../core/Database.php';
    $pdo = Database::getInstance()->getConnection();
    // Get unsur with bagian and personil count
    $unsurData = $pdo->query("
        SELECT u.id, u.nama_unsur,
            (SELECT COUNT(*) FROM bagian b WHERE b.id_unsur = u.id AND (b.is_active = 1 OR b.is_active IS NULL)) as jml_bagian,
            (SELECT COUNT(*) FROM personil p WHERE p.id_unsur = u.id AND p.is_active = 1 AND p.is_deleted = 0) as jml_personil
        FROM unsur u
        ORDER BY u.urutan ASC, u.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get bagian per unsur with pimpinan
    $bagianData = $pdo->query("
        SELECT b.id, b.nama_bagian, b.id_unsur, b.kode_bagian,
            (SELECT COUNT(*) FROM personil p WHERE p.id_bagian = b.id AND p.is_active = 1 AND p.is_deleted = 0) as jml_personil,
            (SELECT p.nama FROM personil p 
             LEFT JOIN jabatan j ON p.id_jabatan = j.id
             WHERE p.id_bagian = b.id AND p.is_active = 1 AND p.is_deleted = 0
             AND (j.nama_jabatan LIKE 'KA%' OR j.nama_jabatan LIKE 'KEPALA%' OR j.nama_jabatan LIKE 'KAPOLSEK%' OR j.nama_jabatan LIKE 'KASAT%' OR j.nama_jabatan LIKE 'KABAG%' OR j.nama_jabatan LIKE 'KASI%')
             LIMIT 1) as pimpinan_nama,
            (SELECT pk.nama_pangkat FROM personil p
             LEFT JOIN jabatan j ON p.id_jabatan = j.id
             LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
             WHERE p.id_bagian = b.id AND p.is_active = 1 AND p.is_deleted = 0
             AND (j.nama_jabatan LIKE 'KA%' OR j.nama_jabatan LIKE 'KEPALA%' OR j.nama_jabatan LIKE 'KAPOLSEK%' OR j.nama_jabatan LIKE 'KASAT%' OR j.nama_jabatan LIKE 'KABAG%' OR j.nama_jabatan LIKE 'KASI%')
             LIMIT 1) as pimpinan_pangkat
        FROM bagian b
        WHERE b.is_active = 1 OR b.is_active IS NULL
        ORDER BY b.urutan ASC, b.nama_bagian ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get Kapolres & Wakapolres
    $pimpinan = $pdo->query("
        SELECT p.nama, pk.nama_pangkat, j.nama_jabatan, b.nama_bagian
        FROM personil p
        LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        WHERE u.nama_unsur LIKE '%PIMPINAN%' AND u.nama_unsur NOT LIKE '%PEMBANTU%'
        AND p.is_active = 1 AND p.is_deleted = 0
        ORDER BY pk.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Total stats
    $totalPersonil = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_active = 1 AND is_deleted = 0")->fetchColumn();

} catch (Exception $e) {
    $unsurData = [];
    $bagianData = [];
    $pimpinan = [];
    $totalPersonil = 0;
}

// Group bagian by unsur
$bagianByUnsur = [];
foreach ($bagianData as $b) {
    $bagianByUnsur[$b['id_unsur']][] = $b;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-sitemap me-2 text-primary"></i>Struktur Organisasi</h4>
            <p class="text-muted mb-0">POLRES Samosir &mdash; <?= $totalPersonil ?> Personil Aktif</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Pimpinan Card -->
    <?php if (!empty($pimpinan)): ?>
    <div class="row justify-content-center mb-4">
        <?php foreach ($pimpinan as $p): ?>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm text-center pimpinan-card">
                <div class="card-body py-4">
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark px-3 py-1" style="font-size:.85rem;"><?= htmlspecialchars($p['nama_jabatan']) ?></span>
                    </div>
                    <div class="pimpinan-avatar mb-2">
                        <i class="fa-solid fa-user-tie fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($p['nama']) ?></h5>
                    <p class="text-muted mb-0"><?= htmlspecialchars($p['nama_pangkat'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mb-4">
        <div class="org-line-down" style="width:2px;height:30px;background:var(--primary-color);margin:0 auto;"></div>
    </div>
    <?php endif; ?>

    <!-- Unsur Sections -->
    <?php foreach ($unsurData as $unsur): ?>
    <div class="card border-0 shadow-sm mb-4 unsur-section">
        <div class="card-header bg-white border-bottom-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-layer-group me-2"></i><?= htmlspecialchars($unsur['nama_unsur']) ?>
                    </h5>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary rounded-pill"><?= $unsur['jml_bagian'] ?> Bagian</span>
                    <span class="badge bg-secondary rounded-pill"><?= $unsur['jml_personil'] ?> Personil</span>
                </div>
            </div>
        </div>
        <div class="card-body pt-2">
            <?php
            $bagianList = $bagianByUnsur[$unsur['id']] ?? [];
            if (empty($bagianList)):
            ?>
                <p class="text-muted text-center py-3 mb-0"><em>Belum ada bagian terdaftar</em></p>
            <?php else: ?>
            <div class="row">
                <?php foreach ($bagianList as $bag): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100 bagian-card border" style="border-left: 4px solid var(--primary-color) !important;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0" style="font-size:.9rem;"><?= htmlspecialchars($bag['nama_bagian']) ?></h6>
                                <span class="badge bg-light text-dark" style="font-size:.7rem;"><?= $bag['jml_personil'] ?></span>
                            </div>
                            <?php if ($bag['pimpinan_nama']): ?>
                            <div class="mt-2" style="font-size:.8rem;">
                                <i class="fa-solid fa-user text-muted me-1"></i>
                                <span class="text-muted"><?= htmlspecialchars($bag['pimpinan_pangkat'] ?? '') ?></span><br>
                                <strong><?= htmlspecialchars($bag['pimpinan_nama']) ?></strong>
                            </div>
                            <?php else: ?>
                            <div class="mt-2 text-muted" style="font-size:.8rem;">
                                <i class="fa-solid fa-user-slash me-1"></i> <em>Pimpinan belum diset</em>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($bag['kode_bagian'])): ?>
                            <div class="mt-2">
                                <span class="badge bg-outline-secondary" style="font-size:.65rem;border:1px solid #ccc;color:#666;"><?= htmlspecialchars($bag['kode_bagian']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.pimpinan-card {
    border-radius: 15px;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef0ff 100%);
    border: 2px solid var(--primary-color) !important;
}
.pimpinan-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(26, 35, 126, 0.1);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto;
}
.unsur-section .card-header { border-radius: 12px 12px 0 0; }
.unsur-section { border-radius: 12px; overflow: hidden; }
.bagian-card { border-radius: 8px; transition: all 0.2s; }
.bagian-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
@media print {
    .navbar, .footer, .btn, .user-menu { display: none !important; }
    body { padding-top: 0 !important; }
    .card { break-inside: avoid; }
}
</style>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
