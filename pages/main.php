<?php
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
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Dashboard - Sistem Manajemen POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>
<?php
$user = AuthHelper::getCurrentUser();
$role = $user['role'] ?? 'viewer';
$roleLabels = ['admin'=>'Administrator','operator'=>'Operator','viewer'=>'Pimpinan'];
$greetHour = (int)date('H');
$greeting = $greetHour < 12 ? 'Selamat Pagi' : ($greetHour < 15 ? 'Selamat Siang' : ($greetHour < 18 ? 'Selamat Sore' : 'Selamat Malam'));

// Fetch operational stats from DB
require_once __DIR__ . '/../core/Database.php';
$_db = Database::getInstance()->getConnection();
$_opsActive    = (int)$_db->query("SELECT COUNT(*) FROM operations WHERE status='active'")->fetchColumn();
$_opsPlanned   = (int)$_db->query("SELECT COUNT(*) FROM operations WHERE status='planned'")->fetchColumn();
$_opsTotal     = (int)$_db->query("SELECT COUNT(*) FROM operations")->fetchColumn();
$_lhptDraft    = (int)$_db->query("SELECT COUNT(*) FROM lhpt WHERE status_lhpt='draft'")->fetchColumn();
$_lhptTotal    = (int)$_db->query("SELECT COUNT(*) FROM lhpt")->fetchColumn();
$_suratMasuk   = (int)$_db->query("SELECT COUNT(*) FROM surat_ekspedisi WHERE jenis='masuk' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$_suratKeluar  = (int)$_db->query("SELECT COUNT(*) FROM surat_ekspedisi WHERE jenis='keluar' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$_suratProses  = (int)$_db->query("SELECT COUNT(*) FROM surat_ekspedisi WHERE status='diproses'")->fetchColumn();
?>

<div class="container-fluid py-4">
    <!-- Hero / Greeting -->
    <div class="card border-0 shadow-sm mb-4 hero-section">
        <div class="card-body py-4 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1"><?= $greeting ?>, <?= htmlspecialchars($user['username'] ?? 'User') ?></h4>
                    <p class="mb-0 opacity-75"><?= $roleLabels[$role] ?? $role ?> — BAGOPS Polres Samosir | <?= date('l, d F Y') ?></p>
                </div>
                <div class="text-end d-none d-md-block">
                    <div class="fs-1 fw-bold"><?= date('H:i') ?></div>
                    <small class="opacity-75">SPRIN v<?= APP_VERSION ?></small>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- Operational Summary — 8 cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
            <i class="fas fa-bullhorn text-danger fs-4 mb-1"></i>
            <div class="fs-2 fw-bold text-danger"><?= $_opsActive ?></div>
            <div class="text-muted small">Operasi Aktif</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
            <i class="fas fa-clipboard-list text-primary fs-4 mb-1"></i>
            <div class="fs-2 fw-bold text-primary"><?= $_opsPlanned ?></div>
            <div class="text-muted small">Operasi Rencana</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
            <i class="fas fa-file-alt text-warning fs-4 mb-1"></i>
            <div class="fs-2 fw-bold text-warning"><?= $_lhptDraft ?></div>
            <div class="text-muted small">LHPT Draft</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
            <i class="fas fa-envelope text-info fs-4 mb-1"></i>
            <div class="fs-2 fw-bold text-info"><?= $_suratProses ?></div>
            <div class="text-muted small">Surat Diproses</div>
        </div></div></div>
    </div>

    <div class="row g-4">
        <!-- LEFT: Piket + Quick Actions -->
        <div class="col-lg-8">
            <!-- Piket Hari Ini Widget -->
            <div id="piketWidget" class="card border-0 shadow-sm mb-4" style="display:none;">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Piket Hari Ini — <span id="piketTanggal"></span></h6>
                <a href="jadwal_piket.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-calendar-week me-1"></i>Lengkap</a>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                      <tr><th>Satuan</th><th>Nama</th><th>Pangkat</th><th>Shift</th><th>Jam</th><th>Tim</th></tr>
                    </thead>
                    <tbody id="piketTodayBody">
                      <tr><td colspan="6" class="text-center text-muted py-3">Memuat...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div id="piketEmptyMsg" class="alert alert-info" style="display:none;">
              <i class="fa-solid fa-info-circle me-2"></i>Tidak ada jadwal piket terdaftar hari ini.
              <a href="tim_piket.php" class="alert-link">Generate jadwal dari Tim Piket.</a>
            </div>

            <!-- Stats Grid -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Statistik Personil</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f0f4ff"><div class="fs-3 fw-bold text-primary" id="totalPersonil">-</div><small class="text-muted">Total Personil</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f0f4ff"><div class="fs-3 fw-bold text-primary" id="polriCount">-</div><small class="text-muted">POLRI</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f0f4ff"><div class="fs-3 fw-bold text-primary" id="asnCount">-</div><small class="text-muted">ASN/P3K</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f0f4ff"><div class="fs-3 fw-bold text-primary" id="schedulesToday">-</div><small class="text-muted">Jadwal Hari Ini</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f8f9fa"><div class="fs-4 fw-bold" id="maleCount">-</div><small class="text-muted">Laki-laki</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f8f9fa"><div class="fs-4 fw-bold" id="femaleCount">-</div><small class="text-muted">Perempuan</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f8f9fa"><div class="fs-4 fw-bold" id="withGelarCount">-</div><small class="text-muted">Bergelar</small></div></div>
                        <div class="col-md-3 col-6"><div class="text-center p-2 rounded" style="background:#f8f9fa"><div class="fs-4 fw-bold" id="totalBagian">-</div><small class="text-muted">Bagian</small></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6></div>
                <div class="list-group list-group-flush">
                    <?php if (AuthHelper::canEdit()): ?>
                    <a href="operasi.php?tambah=1" class="list-group-item list-group-item-action"><i class="fas fa-plus-circle text-success me-2"></i>Tambah Operasi Baru</a>
                    <a href="ekspedisi.php" class="list-group-item list-group-item-action"><i class="fas fa-envelope text-info me-2"></i>Catat Surat Masuk/Keluar</a>
                    <a href="lhpt.php" class="list-group-item list-group-item-action"><i class="fas fa-file-alt text-warning me-2"></i>Buat LHPT</a>
                    <?php endif; ?>
                    <a href="personil.php" class="list-group-item list-group-item-action"><i class="fas fa-users text-primary me-2"></i>Data Personil</a>
                    <a href="calendar_dashboard.php" class="list-group-item list-group-item-action"><i class="fas fa-calendar text-danger me-2"></i>Kalender Jadwal</a>
                    <a href="laporan_operasi.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-bar text-dark me-2"></i>Laporan Operasi</a>
                </div>
            </div>

            <!-- Operational Recap -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Rekap Operasional</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><td class="ps-3"><i class="fas fa-bullhorn text-danger me-2"></i>Operasi Aktif</td><td class="fw-bold text-end pe-3"><?= $_opsActive ?></td></tr>
                        <tr><td class="ps-3"><i class="fas fa-clipboard text-primary me-2"></i>Operasi Rencana</td><td class="fw-bold text-end pe-3"><?= $_opsPlanned ?></td></tr>
                        <tr><td class="ps-3"><i class="fas fa-tasks text-success me-2"></i>Total Operasi</td><td class="fw-bold text-end pe-3"><?= $_opsTotal ?></td></tr>
                        <tr><td class="ps-3"><i class="fas fa-file-alt text-warning me-2"></i>LHPT (total)</td><td class="fw-bold text-end pe-3"><?= $_lhptTotal ?></td></tr>
                        <tr><td class="ps-3"><i class="fas fa-inbox text-info me-2"></i>Surat Masuk (bln ini)</td><td class="fw-bold text-end pe-3"><?= $_suratMasuk ?></td></tr>
                        <tr><td class="ps-3"><i class="fas fa-paper-plane text-success me-2"></i>Surat Keluar (bln ini)</td><td class="fw-bold text-end pe-3"><?= $_suratKeluar ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- System Info (admin only) -->
            <?php if (AuthHelper::isAdmin()): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-server me-2 text-secondary"></i>Sistem</h6></div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">PHP</span><span><?= PHP_VERSION ?></span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Server</span><span><?= php_uname('n') ?></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Versi App</span><span class="fw-bold">v<?= APP_VERSION ?></span></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/components/footer.php'; ?>

<script>
    // Load statistics
    document.addEventListener('DOMContentLoaded', function() {
        loadStatistics();
    });
    
    function loadStatistics() {
        // Load personil statistics from updated API
        fetch('../api/personil_simple.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data.statistics;
                    
                    // Update basic statistics
                    document.getElementById('totalPersonil').textContent = stats.total_personil;
                    document.getElementById('polriCount').textContent = stats.polri_count;
                    document.getElementById('asnCount').textContent = stats.total_personil - stats.polri_count;
                    document.getElementById('totalBagian').textContent = Object.keys(stats.unsur_distribution).length;
                    
                    // Load detailed statistics
                    loadDetailedStatistics();
                }
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
                // Set default values on error
                document.getElementById('totalPersonil').textContent = '0';
                document.getElementById('polriCount').textContent = '0';
                document.getElementById('asnCount').textContent = '0';
                document.getElementById('totalBagian').textContent = '0';
            });
            
        // Load schedule statistics (existing functionality)
        loadScheduleStatistics();
        // Load piket hari ini
        loadPiketHariIni();
    }
    
    function loadDetailedStatistics() {
        // Load detailed statistics from unsur_stats API
        fetch('../api/unsur_stats.php')
            .then(response => {
                // Check if response is valid JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Invalid response format: ' + contentType);
                }
                return response.text();
            })
            .then(text => {
                try {
                    // Parse JSON manually
                    const data = JSON.parse(text);
                    if (data.success) {
                        // API returns overall_statistics directly in data
                        const overall = data.data.overall_statistics;
                        
                        // Update gender statistics
                        document.getElementById('maleCount').textContent = overall.by_jk.L || 0;
                        document.getElementById('femaleCount').textContent = overall.by_jk.P || 0;
                        
                        // Update gelar statistics
                        if (overall.data_completeness) {
                            document.getElementById('withGelarCount').textContent = overall.data_completeness.with_gelar || 0;
                        } else {
                            document.getElementById('withGelarCount').textContent = 0;
                        }
                    } else {
                        console.error('API returned error:', data.message);
                    }
                } catch (parseError) {
                    console.error('JSON parsing error:', parseError, 'Response text:', text.substring(0, 200));
                    throw parseError;
                }
            })
            .catch(error => {
                console.error('Error loading detailed statistics:', error);
                // Set default values
                document.getElementById('maleCount').textContent = '0';
                document.getElementById('femaleCount').textContent = '0';
                document.getElementById('withGelarCount').textContent = '0';
            });
    }
    
    function loadScheduleStatistics() {
        // Load schedule statistics (existing functionality)
        fetch('../api/calendar_api.php?action=getStats')
            .then(response => {
                // Check if response is HTML error
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    console.warn('Calendar API returned HTML, using fallback');
                    // Set default values
                    document.getElementById('schedulesToday').textContent = '0';
                    const weekElement = document.getElementById('schedulesWeek');
                    if (weekElement) {
                        weekElement.textContent = '0';
                    }
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    document.getElementById('schedulesToday').textContent = data.data.today || 0;
                    // Update schedulesWeek if element exists
                    const weekElement = document.getElementById('schedulesWeek');
                    if (weekElement) {
                        weekElement.textContent = data.data.week || 0;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading schedule statistics:', error);
                // Set default values
                document.getElementById('schedulesToday').textContent = '0';
                const weekElement = document.getElementById('schedulesWeek');
                if (weekElement) {
                    weekElement.textContent = '0';
                }
            });
    }
    
    // Add animation for statistics
    function animateNumber(element, target, duration = 1000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }
    
    // Animate statistics when loaded
    function animateStatistics() {
        const elements = [
            { id: 'totalPersonil', target: parseInt(document.getElementById('totalPersonil').textContent) },
            { id: 'polriCount', target: parseInt(document.getElementById('polriCount').textContent) },
            { id: 'asnCount', target: parseInt(document.getElementById('asnCount').textContent) },
            { id: 'maleCount', target: parseInt(document.getElementById('maleCount').textContent) },
            { id: 'femaleCount', target: parseInt(document.getElementById('femaleCount').textContent) },
            { id: 'withGelarCount', target: parseInt(document.getElementById('withGelarCount').textContent) }
        ];
        
        elements.forEach((item, index) => {
            setTimeout(() => {
                animateNumber(document.getElementById(item.id), item.target);
            }, index * 100);
        });
    }
    
    // Trigger animation after statistics are loaded
    setTimeout(animateStatistics, 500);

    function loadPiketHariIni() {
        const today = new Date();
        const hariNm = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][today.getDay()];
        const tglFmt = today.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
        document.getElementById('piketTanggal').textContent = hariNm + ', ' + tglFmt;
        fetch('../api/tim_piket_api.php?action=get_piket_hari_ini')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.data || !data.data.length) {
                    document.getElementById('piketEmptyMsg').style.display = 'block';
                    return;
                }
                document.getElementById('piketWidget').style.display = 'block';
                const shiftColors = {PAGI:'#fff3cd',SIANG:'#cfe2ff',MALAM:'#d1ecf1',FULL_DAY:'#d4edda',ROTASI:'#f8d7da'};
                const rows = data.data.map(r => `
                    <tr>
                        <td><small class="fw-bold">${r.nama_bagian||'-'}</small></td>
                        <td>${r.personil_name||r.personil_id}</td>
                        <td><small class="text-muted">${r.nama_pangkat||'-'}</small></td>
                        <td><span style="background:${shiftColors[r.shift_type]||'#eee'};padding:2px 8px;border-radius:20px;font-size:.75rem;font-weight:600">${r.shift_type}</span></td>
                        <td><small>${(r.start_time||'').substring(0,5)} – ${(r.end_time||'').substring(0,5)}</small></td>
                        <td><small>${r.nama_tim||'-'}</small></td>
                    </tr>`);
                document.getElementById('piketTodayBody').innerHTML = rows.join('');
            })
            .catch(() => {
                document.getElementById('piketEmptyMsg').style.display = 'block';
            });
    }
</script>
