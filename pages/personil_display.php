<?php
session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Initialize session
SessionManager::start();

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Data Personil - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

// Include JavaScript configuration
include __DIR__ . '/../public/assets/js/config.php';

/**
 * Convert ket text to button
 */
function create_ket_button($ket_text) {
    $ket_text = trim($ket_text);
    
    if (empty($ket_text)) {
        return '-';
    }
    
    // Determine button class based on ket content
    $button_class = determine_button_class($ket_text);
    
    // Truncate text for single line display (max 12 characters)
    $display_text = truncate_text($ket_text, 12);
    
    // Create button HTML with single line constraint
    return '<button class="btn-ket ' . $button_class . '" title="' . htmlspecialchars($ket_text) . '">' . htmlspecialchars($display_text) . '</button>';
}

/**
 * Truncate text to fit in single line
 */
function truncate_text($text, $max_length) {
    if (strlen($text) <= $max_length) {
        return $text;
    }
    
    return substr($text, 0, $max_length - 3) . '...';
}

/**
 * Determine button class based on ket content
 */
function determine_button_class($ket_text) {
    $ket_upper = strtoupper($ket_text);
    
    if ($ket_upper === 'OP CALL CENTRE') {
        return 'btn-call-centre';
    } elseif ($ket_upper === 'P3K/ BKO POLDA') {
        return 'btn-bko';
    } elseif ($ket_upper === 'BELUM MENGHADAP') {
        return 'btn-belum-menghadap';
    } elseif ($ket_upper === 'AKTIF') {
        return 'btn-aktif';
    } elseif (strpos($ket_upper, 'DIK') !== false) {
        return 'btn-dik';
    } elseif (strpos($ket_upper, 'CUTI') !== false) {
        return 'btn-cuti';
    } elseif (strpos($ket_upper, 'TUGAS') !== false) {
        return 'btn-tugas';
    } elseif (strpos($ket_upper, 'LAPOR') !== false) {
        return 'btn-lapor';
    } else {
        return 'btn-other';
    }
}

// Load data from API instead of JSON file
function loadPersonilFromAPI() {
    $api_url = API_BASE_URL . '/personil_simple.php?limit=1000';
    
    // Use cURL to get API data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: $http_code");
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !$data['success']) {
        throw new Exception("API request failed: " . ($data['error']['message'] ?? 'Unknown error'));
    }
    
    return $data['data'];
}

// Process API data to match unsur-based structure
function processAPIData($api_data) {
    $personil = $api_data['personil'];
    $statistics = $api_data['statistics'];
    
    // Group personil by unsur first, then by bagian
    $unsur_data = [];
    $pimpinan_data = [];
    
    foreach ($personil as $p) {
        $personil_item = [
            'nama' => $p['nama_lengkap'] ?? $p['nama'],
            'nrp' => $p['nrp'],
            'pangkat' => $p['pangkat_singkatan'] ?? $p['nama_pangkat'],
            'jabatan' => $p['nama_jabatan'],
            'ket' => $p['status_ket'] ?? 'aktif',
            'status_kepegawaian' => $p['status_kepegawaian'],
            'unsur' => $p['nama_unsur'],
            'bagian' => $p['nama_bagian'],
            'kode_unsur' => $p['kode_unsur'],
            'no_telepon' => $p['no_telepon'],
            'email' => $p['email'],
            'tanggal_lahir' => $p['tanggal_lahir'],
            'agama' => $p['agama'],
            'jenis_kelamin' => $p['jenis_kelamin'],
            'alamat' => $p['alamat'],
            'keterangan' => $p['keterangan']
        ];
        
        // Check if this is pimpinan (only Kapolres & Wakapolres)
        if (isPimpinan($p['nama_jabatan'])) {
            $pimpinan_data[] = $personil_item;
        } else {
            $unsur_name = $p['nama_unsur'] ?? 'TANPA UNSUR';
            $bagian_name = $p['nama_bagian'] ?? 'TANPA BAGIAN';
            
            if (!isset($unsur_data[$unsur_name])) {
                $unsur_data[$unsur_name] = [
                    'nama_unsur' => $unsur_name,
                    'kode_unsur' => $p['kode_unsur'],
                    'bagian' => []
                ];
            }
            
            if (!isset($unsur_data[$unsur_name]['bagian'][$bagian_name])) {
                $unsur_data[$unsur_name]['bagian'][$bagian_name] = [
                    'nama_bagian' => $bagian_name,
                    'personil' => []
                ];
            }
            
            $unsur_data[$unsur_name]['bagian'][$bagian_name]['personil'][] = $personil_item;
        }
    }
    
    // Sort unsur by predefined order
    $unsur_order = [
        'UNSUR PIMPINAN', 
        'UNSUR PEMBANTU PIMPINAN', 
        'UNSUR PELAKSANA TUGAS POKOK', 
        'UNSUR PELAKSANA KEWILAYAHAN', 
        'UNSUR PENDUKUNG', 
        'UNSUR LAINNYA'
    ];
    $sorted_unsur = [];
    
    foreach ($unsur_order as $unsur_name) {
        if (isset($unsur_data[$unsur_name])) {
            $sorted_unsur[$unsur_name] = $unsur_data[$unsur_name];
            // Sort bagian within each unsur
            ksort($sorted_unsur[$unsur_name]['bagian']);
        }
    }
    
    return [
        'pimpinan' => $pimpinan_data,
        'unsur' => $sorted_unsur,
        'statistics' => $statistics
    ];
}

// Check if position is considered pimpinan (Based on PERKAP No. 23 Tahun 2010)
function isPimpinan($jabatan) {
    // Unsur Pimpinan POLRES hanya Kapolres & Wakapolres (exact match)
    $jabatan_upper = strtoupper(trim($jabatan));
    
    // Exact matches only
    if ($jabatan_upper === 'KAPOLRES' || 
        $jabatan_upper === 'KAPOLRES SAMOSIR' ||
        $jabatan_upper === 'WAKAPOLRES') {
        return true;
    }
    
    return false;
}

function getUnsurIcon($unsur_name) {
    $icons = [
        'UNSUR PIMPINAN' => '🏛️',
        'UNSUR PEMBANTU PIMPINAN' => '📋',
        'UNSUR PELAKSANA TUGAS POKOK' => '⚡',
        'UNSUR PELAKSANA KEWILAYAHAN' => '🏘️',
        'UNSUR PENDUKUNG' => '🔧',
        'UNSUR LAINNYA' => '📋'
    ];
    
    return $icons[$unsur_name] ?? '📋';
}

// Load and process data from DATABASE API ONLY
try {
    $api_data = loadPersonilFromAPI();
    $data = processAPIData($api_data);
    
    // Log successful API load
    error_log("personil_display.php: API SUCCESS - Loaded " . count($api_data['personil']) . " personil from database");
    
} catch (Exception $e) {
    // NO FALLBACK TO JSON - Force API usage
    error_log("personil_display.php: API FAILED - " . $e->getMessage());
    
    // Show critical error to user
    die("
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4><i class='fas fa-exclamation-triangle me-2'></i>Koneksi Database Gagal</h4>
                <p>Tidak dapat mengambil data personil dari database.</p>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <hr>
                <p class='mb-0'>
                    <small>Silakan hubungi administrator sistem. Aplikasi hanya menggunakan data dari database, bukan file JSON.</small>
                </p>
            </div>
        </div>
    ");
}

// Calculate statistics from processed data
$totalPimpinan = count($data['pimpinan']);
$totalUnsur = count($data['unsur']);
$totalPersonil = 0;
$totalBagian = 0;

foreach ($data['unsur'] as $unsur) {
    $totalBagian += count($unsur['bagian']);
    foreach ($unsur['bagian'] as $bagian) {
        $totalPersonil += count($bagian['personil']);
    }
}

// Use API statistics if available
if (isset($data['statistics'])) {
    $api_stats = $data['statistics'];
    $totalPersonil = $api_stats['total_personil'];
    $totalPolri = $api_stats['polri_count'];
    $totalAsn = $api_stats['asn_count'];
    $totalP3k = $api_stats['p3k_count'];
    $totalAktif = $api_stats['aktif_count'];
    $unsur_distribution = $api_stats['unsur_distribution'] ?? [];
} else {
    // Fallback statistics
    $totalPolri = 0;
    $totalAsn = 0;
    $totalP3k = 0;
    $totalAktif = 0;
    $unsur_distribution = [];
}
?>

<div class="container">
    <h1>DATA PERSONIL POLRES SAMOSIR</h1>
    <p class="subtitle">Daftar Personil Periode Februari 2026 (Real-time dari Database)</p>
    
    <div class="stats">
        <div class="stat-box">
            <h3><?php echo $totalPimpinan; ?></h3>
            <p>PIMPINAN</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalUnsur; ?></h3>
            <p>UNSUR</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalBagian; ?></h3>
            <p>SATUAN/BAGIAN/POLSEK</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalPersonil; ?></h3>
            <p>TOTAL PERSONIL</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalAktif; ?></h3>
            <p>PERSONIL AKTIF</p>
        </div>
    </div>
    
    <!-- Unsur Statistics -->
    <div class="stats-row">
        <?php if (!empty($unsur_distribution)): ?>
            <?php foreach ($unsur_distribution as $kode_unsur => $count): ?>
            <div class="stat-box-small">
                <h4><?php echo $count; ?></h4>
                <p><?php echo htmlspecialchars($kode_unsur); ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="stat-box-small">
            <h4><?php echo $totalPolri; ?></h4>
            <p>POLRI</p>
        </div>
        <div class="stat-box-small">
            <h4><?php echo $totalAsn; ?></h4>
            <p>ASN</p>
        </div>
        <div class="stat-box-small">
            <h4><?php echo $totalP3k; ?></h4>
            <p>P3K</p>
        </div>
        <div class="stat-box-small">
            <h4><?php echo $totalPersonil - $totalAktif; ?></h4>
            <p>NON-AKTIF</p>
        </div>
    </div>
    
    <!-- Refresh Button -->
    <div class="refresh-section">
        <button onclick="location.reload()" class="btn-refresh">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
        <small class="text-muted">Data diambil langsung dari database real-time</small>
    </div>
    
    <!-- Pimpinan -->
    <div class="pimpinan-section">
        <h2>PIMPINAN</h2>
        <div class="pimpinan-cards">
            <?php foreach ($data['pimpinan'] as $p): ?>
            <div class="pimpinan-card">
                <div class="nama"><?php echo htmlspecialchars($p['nama']); ?></div>
                <div class="pangkat"><?php echo htmlspecialchars($p['pangkat']); ?> (<?php echo htmlspecialchars($p['nrp']); ?>)</div>
                <div class="jabatan"><?php echo htmlspecialchars($p['jabatan']); ?></div>
                <div class="ket"><?php echo create_ket_button($p['ket']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Unsur-based Sections -->
    <?php $no = 1; foreach ($data['unsur'] as $unsur_name => $unsur_data): ?>
    <div class="unsur-section" id="unsur-<?php echo $no; ?>">
        <div class="unsur-header" onclick="toggleUnsur(<?php echo $no; ?>)">
            <h2><?php echo getUnsurIcon($unsur_name) . ' ' . htmlspecialchars($unsur_name); ?></h2>
            <div>
                <span class="unsur-count"><?php echo count($unsur_data['bagian']); ?> bagian, <?php echo array_sum(array_map(function($b) { return count($b['personil']); }, $unsur_data['bagian'])); ?> personil</span>
                <span class="toggle-icon" id="icon-<?php echo $no; ?>">▼</span>
            </div>
        </div>
        <div class="unsur-content" id="content-<?php echo $no; ?>">
            <?php foreach ($unsur_data['bagian'] as $bagian): ?>
            <div class="bagian-subsection">
                <div class="bagian-header" onclick="toggleBagian('<?php echo $unsur_name . '-' . $bagian['nama_bagian']; ?>')">
                    <h3><?php echo htmlspecialchars($bagian['nama_bagian']); ?></h3>
                    <div>
                        <span class="bagian-count"><?php echo count($bagian['personil']); ?> personil</span>
                        <span class="toggle-icon" id="bagian-icon-<?php echo $unsur_name . '-' . $bagian['nama_bagian']; ?>">▼</span>
                    </div>
                </div>
                <div class="personil-content" id="bagian-content-<?php echo $unsur_name . '-' . $bagian['nama_bagian']; ?>">
                    <table class="personil-table">
                        <thead>
                            <tr>
                                <th class="no-col">NO</th>
                                <th class="nama-col">NAMA</th>
                                <th class="nrp-col">NRP</th>
                                <th class="pangkat-col">PANGKAT</th>
                                <th class="jabatan-col">JABATAN</th>
                                <th class="telepon-col">TELEPON</th>
                                <th class="ket-col">KET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $personil_no = 1; 
                            foreach ($bagian['personil'] as $personil): 
                            ?>
                            <tr>
                                <td class="no-col"><?php echo $personil_no; ?></td>
                                <td class="nama-col"><?php echo htmlspecialchars($personil['nama']); ?></td>
                                <td class="nrp-col"><?php echo htmlspecialchars($personil['nrp']); ?></td>
                                <td class="pangkat-col"><?php echo htmlspecialchars($personil['pangkat']); ?></td>
                                <td class="jabatan-col"><?php echo htmlspecialchars($personil['jabatan']); ?></td>
                                <td class="telepon-col"><?php echo htmlspecialchars($personil['no_telepon'] ?? '-'); ?></td>
                                <td class="ket-col"><?php echo create_ket_button($personil['ket']); ?></td>
                            </tr>
                            <?php 
                            $personil_no++; 
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php 
    $no++; 
    endforeach; 
    ?>
    
    <!-- Data Source Info -->
    <div class="data-source-info">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Sumber Data:</strong> Database POLRES Samosir (Real-time)<br>
            <strong>Update Terakhir:</strong> <?php echo date('d F Y H:i:s'); ?><br>
            <strong>Total Records:</strong> <?php echo $totalPersonil; ?> personil<br>
            <strong>Struktur:</strong> Berdasarkan PERKAP No. 23 Tahun 2010
        </div>
    </div>
</div>

<script>
// Toggle unsur content
function toggleUnsur(unsurId) {
    const content = document.getElementById('content-' + unsurId);
    const icon = document.getElementById('icon-' + unsurId);
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.textContent = '▼';
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
    }
}

// Toggle bagian content
function toggleBagian(bagianId) {
    const content = document.getElementById('bagian-content-' + bagianId);
    const icon = document.getElementById('bagian-icon-' + bagianId);
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.textContent = '▼';
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
    }
}

// Initialize - collapse all except first unsur
document.addEventListener('DOMContentLoaded', function() {
    const allUnsurContents = document.querySelectorAll('.unsur-content');
    const allUnsurIcons = document.querySelectorAll('.unsur-header .toggle-icon');
    
    // Collapse all unsur
    allUnsurContents.forEach(content => {
        content.style.display = 'none';
    });
    
    allUnsurIcons.forEach(icon => {
        icon.textContent = '▶';
    });
    
    // Expand first unsur
    if (allUnsurContents.length > 0) {
        allUnsurContents[0].style.display = 'block';
        if (allUnsurIcons.length > 0) {
            allUnsurIcons[0].textContent = '▼';
        }
    }
    
    // Collapse all bagian
    const allBagianContents = document.querySelectorAll('.personil-content');
    const allBagianIcons = document.querySelectorAll('.bagian-header .toggle-icon');
    
    allBagianContents.forEach(content => {
        content.style.display = 'none';
    });
    
    allBagianIcons.forEach(icon => {
        icon.textContent = '▶';
    });
    
    // Auto-refresh every 5 minutes (300000 ms)
    setInterval(function() {
        location.reload();
    }, 300000);
});

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'r' && e.ctrlKey) {
        e.preventDefault();
        location.reload();
    }
});
</script>

<style>
/* Additional styles for unsur-based structure */
.unsur-section {
    background: white;
    border-radius: 15px;
    margin: 20px 0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.unsur-section:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.unsur-header {
    background: var(--gradient-primary);
    color: white;
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.unsur-header:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, #5e35b1 100%);
}

.unsur-header h2 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: bold;
}

.unsur-count {
    background: rgba(255,255,255,0.2);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.bagian-subsection {
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 0;
}

.bagian-subsection:last-child {
    border-bottom: none;
}

.bagian-header {
    background: linear-gradient(135deg, var(--bg-hover) 0%, var(--border-light) 100%);
    padding: 15px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    border-left: 4px solid #1a237e;
}

.bagian-header:hover {
    background: linear-gradient(135deg, var(--border-light) 0%, var(--border-color) 100%);
}

.bagian-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a237e;
}

.telepon-col {
    width: 120px;
    font-family: monospace;
    font-size: 0.85rem;
}

.unsur-content {
    padding: 0;
}

.personil-content {
    padding: 20px 25px;
    display: none;
}

.personil-content.show {
    display: block;
}

.toggle-icon {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.refresh-section {
    text-align: center;
    margin: 20px 0;
}

.btn-refresh {
    background: var(--gradient-primary);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    margin-bottom: 10px;
}

.btn-refresh:hover {
    background: linear-gradient(135deg, var(--primary-dark), #5e35b1);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.data-source-info {
    margin-top: 30px;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    padding: 15px;
    border-radius: 5px;
}

.alert-info i {
    margin-right: 10px;
}

@media (max-width: 768px) {
    .unsur-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .bagian-header {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
    
    .personil-table {
        font-size: 0.8rem;
    }
    
    .telepon-col {
        display: none;
    }
}
</style>

<?php include '../includes/components/footer.php'; ?>
