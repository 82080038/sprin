<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

SessionManager::start();

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin');

$page_title = 'Pengaturan Sistem - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Database info
try {
    require_once __DIR__ . '/../core/Database.php';
    $pdo = Database::getInstance()->getConnection();

    $dbVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
    $dbSize = $pdo->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
        FROM information_schema.tables WHERE table_schema = '".DB_NAME."'
    ")->fetchColumn();
    $tableCount = $pdo->query("
        SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '".DB_NAME."'
    ")->fetchColumn();
    $totalPersonil = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_active=1 AND is_deleted=0")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();

    // Last backup
    $lastBackup = $pdo->query("SELECT created_at FROM backups ORDER BY created_at DESC LIMIT 1")->fetchColumn();
} catch (Exception $e) {
    $dbVersion = 'N/A';
    $dbSize = 0;
    $tableCount = 0;
    $totalPersonil = 0;
    $totalUsers = 0;
    $lastBackup = null;
}

$phpVersion = phpversion();
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$diskFree = function_exists('disk_free_space') ? round(disk_free_space('/') / 1024 / 1024 / 1024, 1) : 'N/A';
$diskTotal = function_exists('disk_total_space') ? round(disk_total_space('/') / 1024 / 1024 / 1024, 1) : 'N/A';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-sliders me-2 text-primary"></i>Pengaturan Sistem</h4>
            <p class="text-muted mb-0">Informasi sistem dan konfigurasi aplikasi SPRIN</p>
        </div>
    </div>

    <div class="row">
        <!-- System Info -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa-solid fa-server me-2"></i>Informasi Server</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="fw-bold ps-3" style="width:40%">PHP Version</td><td><?= htmlspecialchars($phpVersion) ?></td></tr>
                            <tr><td class="fw-bold ps-3">Web Server</td><td><?= htmlspecialchars($serverSoftware) ?></td></tr>
                            <tr><td class="fw-bold ps-3">MySQL Version</td><td><?= htmlspecialchars($dbVersion) ?></td></tr>
                            <tr><td class="fw-bold ps-3">Disk Space</td><td><?= $diskFree ?> GB free / <?= $diskTotal ?> GB total</td></tr>
                            <tr><td class="fw-bold ps-3">Environment</td><td><span class="badge bg-warning text-dark"><?= ENVIRONMENT ?></span></td></tr>
                            <tr><td class="fw-bold ps-3">Debug Mode</td><td><span class="badge <?= DEBUG_MODE ? 'bg-danger' : 'bg-success' ?>"><?= DEBUG_MODE ? 'ON' : 'OFF' ?></span></td></tr>
                            <tr><td class="fw-bold ps-3">Session Lifetime</td><td><?= SESSION_LIFETIME / 60 ?> menit</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Database Info -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa-solid fa-database me-2"></i>Informasi Database</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="fw-bold ps-3" style="width:40%">Database Name</td><td><code><?= DB_NAME ?></code></td></tr>
                            <tr><td class="fw-bold ps-3">Host</td><td><?= DB_HOST ?></td></tr>
                            <tr><td class="fw-bold ps-3">Ukuran Database</td><td><?= $dbSize ?> MB</td></tr>
                            <tr><td class="fw-bold ps-3">Jumlah Tabel</td><td><?= $tableCount ?> tabel</td></tr>
                            <tr><td class="fw-bold ps-3">Total Personil</td><td><strong><?= $totalPersonil ?></strong></td></tr>
                            <tr><td class="fw-bold ps-3">Total User</td><td><?= $totalUsers ?></td></tr>
                            <tr><td class="fw-bold ps-3">Backup Terakhir</td><td><?= $lastBackup ? date('d M Y H:i', strtotime($lastBackup)) : '<span class="text-muted">Belum ada</span>' ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Info -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fa-solid fa-info-circle me-2"></i>Informasi Aplikasi</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td class="fw-bold ps-3" style="width:40%">Nama Aplikasi</td><td>SPRIN</td></tr>
                            <tr><td class="fw-bold ps-3">Deskripsi</td><td>Sistem Personil & Informasi</td></tr>
                            <tr><td class="fw-bold ps-3">Versi</td><td><span class="badge bg-info">v1.5.0-dev</span></td></tr>
                            <tr><td class="fw-bold ps-3">Instansi</td><td>POLRES Samosir</td></tr>
                            <tr><td class="fw-bold ps-3">Unit</td><td>Bagian Operasional (BAGOPS)</td></tr>
                            <tr><td class="fw-bold ps-3">Base URL</td><td><code><?= BASE_URL ?></code></td></tr>
                            <tr><td class="fw-bold ps-3">Root Path</td><td><code style="font-size:.75rem"><?= ROOT_PATH ?></code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('pages/user_management.php') ?>" class="btn btn-outline-primary text-start">
                            <i class="fa-solid fa-users-cog me-2"></i> Manajemen User
                        </a>
                        <a href="<?= url('pages/backup_management.php') ?>" class="btn btn-outline-success text-start">
                            <i class="fa-solid fa-database me-2"></i> Manajemen Backup
                        </a>
                        <button class="btn btn-outline-info text-start" onclick="testDatabase()">
                            <i class="fa-solid fa-stethoscope me-2"></i> Test Koneksi Database
                        </button>
                        <button class="btn btn-outline-warning text-start" onclick="clearCache()">
                            <i class="fa-solid fa-broom me-2"></i> Bersihkan Cache
                        </button>
                        <button class="btn btn-outline-secondary text-start" onclick="checkPhpInfo()">
                            <i class="fa-solid fa-code me-2"></i> PHP Extensions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="card border-0 shadow-sm mb-4" id="phpExtensions" style="display:none;">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="fa-solid fa-puzzle-piece me-2"></i>PHP Extensions Terinstall</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                $required = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl', 'gd', 'zip', 'openssl', 'session', 'fileinfo'];
                foreach ($required as $ext):
                    $loaded = extension_loaded($ext);
                ?>
                <div class="col-md-3 col-6 mb-2">
                    <span class="badge <?= $loaded ? 'bg-success' : 'bg-danger' ?> w-100 py-2">
                        <i class="fa-solid <?= $loaded ? 'fa-check' : 'fa-times' ?> me-1"></i> <?= $ext ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function testDatabase() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Testing...';
    
    fetch('../api/unified-api.php?resource=stats&action=dashboard')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Koneksi database berhasil! Response time OK.');
            } else {
                showToast('danger', 'Koneksi database gagal: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            showToast('danger', 'Koneksi database gagal: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-stethoscope me-2"></i> Test Koneksi Database';
        });
}

function clearCache() {
    showToast('info', 'Cache browser dibersihkan.');
    if ('caches' in window) {
        caches.keys().then(names => {
            names.forEach(name => caches.delete(name));
        });
    }
}

function checkPhpInfo() {
    const el = document.getElementById('phpExtensions');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (el.style.display === 'block') {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
