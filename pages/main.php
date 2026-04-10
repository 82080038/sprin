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
<div class="container">
    <div class="hero-section">
        <div class="container">
            <h1>Sistem Manajemen Polres Samosir</h1>
            <p>Platform terintegrasi untuk pengelolaan data personil dan penjadwalan operasional</p>
        </div>
    </div>

    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Data Personil</h3>
                    <p class="feature-description">
                        Kelola data personil POLRES Samosir secara lengkap, termasuk pimpinan, bagian, dan detail personil dengan sistem yang sudah terintegrasi.
                    </p>
                    <a href="personil.php" class="btn btn-feature">
                        <i class="fas fa-arrow-right me-2"></i>Buka Data Personil
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="feature-title">Schedule Management</h3>
                    <p class="feature-description">
                        Sistem penjadwalan modern untuk BAGOPS dengan kalender interaktif, integrasi Google Calendar, dan manajemen shift otomatis.
                    </p>
                    <a href="calendar_dashboard.php" class="btn btn-feature">
                        <i class="fas fa-arrow-right me-2"></i>Buka Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- Piket Hari Ini Widget -->
        <div id="piketWidget" class="mb-4" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Piket Hari Ini — <span id="piketTanggal"></span></h5>
            <a href="jadwal_piket.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-calendar-week me-1"></i>Jadwal Lengkap</a>
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle bg-white rounded shadow-sm">
              <thead class="table-primary small">
                <tr><th>Satuan</th><th>Nama</th><th>Pangkat</th><th>Shift</th><th>Jam</th><th>Status</th></tr>
              </thead>
              <tbody id="piketTodayBody">
                <tr><td colspan="6" class="text-center text-muted">Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div id="piketEmptyMsg" class="alert alert-info" style="display:none;">
          <i class="fa-solid fa-info-circle me-2"></i>Tidak ada jadwal piket terdaftar hari ini.
          <a href="tim_piket.php" class="alert-link">Generate jadwal dari Tim Piket.</a>
        </div>

        <div class="stats-section">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="totalPersonil">-</div>
                        <div class="stat-label">Total Personil</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="polriCount">-</div>
                        <div class="stat-label">POLRI</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="asnCount">-</div>
                        <div class="stat-label">ASN/P3K</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="schedulesToday">-</div>
                        <div class="stat-label">Jadwal Hari Ini</div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Statistics Row -->
            <div class="row mt-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="maleCount">-</div>
                        <div class="stat-label">Laki-laki</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="femaleCount">-</div>
                        <div class="stat-label">Perempuan</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="withGelarCount">-</div>
                        <div class="stat-label">Dengan Gelar</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number" id="totalBagian">-</div>
                        <div class="stat-label">Total Bagian</div>
                    </div>
                </div>
            </div>
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
