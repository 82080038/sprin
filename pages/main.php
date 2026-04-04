<?php
declare(strict_types=1);
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
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title">Data Personil</h3>
                        <p class="card-text">
                            Kelola data personil POLRES Samosir secara lengkap, termasuk pimpinan, bagian, dan detail personil dengan sistem yang sudah terintegrasi.
                        </p>
                        <a href="personil.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>Buka Data Personil
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-calendar-alt fa-3x text-success"></i>
                        </div>
                        <h3 class="card-title">Schedule Management</h3>
                        <p class="card-text">
                            Sistem penjadwalan modern untuk BAGOPS dengan kalender interaktif, integrasi Google Calendar, dan manajemen shift otomatis.
                        </p>
                        <a href="calendar_dashboard.php" class="btn btn-success">
                            <i class="fas fa-arrow-right me-2"></i>Buka Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="totalPersonil">-</h3>
                            <p class="card-text">Total Personil</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="polriCount">-</h3>
                            <p class="card-text">POLRI</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="asnCount">-</h3>
                            <p class="card-text">ASN/P3K</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="schedulesToday">-</h3>
                            <p class="card-text">Jadwal Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Statistics Row -->
            <div class="row mt-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="maleCount">-</h3>
                            <p class="card-text">Laki-laki</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="femaleCount">-</h3>
                            <p class="card-text">Perempuan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="withGelarCount">-</h3>
                            <p class="card-text">Dengan Gelar</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <h3 class="card-title" id="totalBagian">-</h3>
                            <p class="card-text">Total Bagian</p>
                        </div>
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
        fetch('<?php echo url('api/personil_simple.php'); ?>')
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
    }
    
    function loadDetailedStatistics() {
        // Load detailed statistics from unsur_stats API
        fetch('<?php echo url('api/unsur_stats.php'); ?>')
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
        fetch('<?php echo url('api/calendar_api.php?action=getStats'); ?>')
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
</script>
