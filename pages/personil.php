<?php
session_start();

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /sprint/login.php');
    exit;
}

$page_title = 'Data Personil - POLRES Samosir';
include '../includes/components/header.php';

// Add Font Awesome for icons
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';

// Add jQuery and Bootstrap for modals/toasts
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';

// Add Toastr for toast notifications
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>';

// Add SweetAlert for better alerts
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.0.0/sweetalert2.min.js"></script>';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.0.0/sweetalert2.min.css">';

// Simple database connection
try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simple query to get all personil with their unsur and bagian
    $sql = "
        SELECT 
            p.id, p.nama, p.nrp, p.JK, p.status_ket,
            pg.nama_pangkat, pg.singkatan,
            j.nama_jabatan,
            b.nama_bagian,
            u.nama_unsur,
            mjp.kategori as status_kepegawaian
        FROM personil p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
        LEFT JOIN jabatan j ON p.id_jabatan = j.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        LEFT JOIN unsur u ON p.id_unsur = u.id
        LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ORDER BY 
                u.urutan, 
                b.nama_bagian,
                CASE WHEN pg.level_pangkat IS NULL THEN 999999 ELSE pg.level_pangkat END ASC,
                CASE 
                    WHEN p.nrp REGEXP '^[0-9]{8}' THEN 
                        CASE 
                            WHEN SUBSTRING(p.nrp, 1, 1) = '0' THEN CONCAT('20', SUBSTRING(p.nrp, 1, 4))
                            ELSE CONCAT('19', SUBSTRING(p.nrp, 1, 4))
                        END
                    WHEN p.nrp REGEXP '^[0-9]{9}' THEN CONCAT('19', SUBSTRING(p.nrp, 1, 6))
                    ELSE '99999999'
                END ASC,
                p.nama
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $personil = $stmt->fetchAll();
    
} catch(Exception $e) {
    die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
}

// Group by unsur
$unsur_data = [];
foreach ($personil as $p) {
    $unsur_name = $p['nama_unsur'] ?: 'TANPA UNSUR';
    $bagian_name = $p['nama_bagian'] ?: 'TANPA BAGIAN';
    
    if (!isset($unsur_data[$unsur_name])) {
        $unsur_data[$unsur_name] = [];
    }
    
    if (!isset($unsur_data[$unsur_name][$bagian_name])) {
        $unsur_data[$unsur_name][$bagian_name] = [];
    }
    
    $unsur_data[$unsur_name][$bagian_name][] = $p;
}

?>

<div class="container">
    <h1>DATA PERSONIL POLRES SAMOSIR</h1>
    <p class="subtitle">Daftar Personil Periode Februari 2026</p>
    
    <div class="stats">
        <div class="stat-box">
            <h3><?php echo count($personil); ?></h3>
            <p>TOTAL PERSONIL</p>
        </div>
    </div>
    
    <?php foreach ($unsur_data as $unsur_name => $bagian_data): ?>
    <div class="unsur-section">
        <h2><?php echo htmlspecialchars($unsur_name); ?></h2>
        
        <?php foreach ($bagian_data as $bagian_name => $bagian_personil): ?>
        <div class="bagian-section">
            <h3><?php echo htmlspecialchars($bagian_name); ?> (<?php echo count($bagian_personil); ?>)</h3>
            
            <table class="personil-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA</th>
                        <th>NRP</th>
                        <th>PANGKAT</th>
                        <th>JABATAN</th>
                        <th>STATUS</th>
                        <th>KEPEGAWAIAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($bagian_personil as $p): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($p['nama']); ?></td>
                        <td><?php echo htmlspecialchars($p['nrp']); ?></td>
                        <td><?php echo htmlspecialchars($p['singkatan'] ?: $p['nama_pangkat']); ?></td>
                        <td><?php echo htmlspecialchars($p['nama_jabatan']); ?></td>
                        <td><?php echo htmlspecialchars($p['status_ket'] ?: 'aktif'); ?></td>
                        <td><?php echo htmlspecialchars($p['status_kepegawaian'] ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    
    <div class="data-source-info">
        <div class="alert alert-info">
            <strong>Sumber Data:</strong> Database POLRES Samosir<br>
            <strong>Total Records:</strong> <?php echo count($personil); ?> personil<br>
            <strong>Update Terakhir:</strong> <?php echo date('d F Y H:i:s'); ?>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;
}

h1 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 2em;
    text-align: center;
}

.subtitle {
    color: #7f8c8d;
    margin-bottom: 30px;
    font-style: italic;
    text-align: center;
    font-size: 1.1em;
}

.stats {
    display: flex;
    gap: 20px;
    margin: 30px 0;
    justify-content: center;
    flex-wrap: wrap;
}

.stat-box {
    background: #007bff;
    color: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    min-width: 180px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.stat-box h3 {
    margin: 0;
    font-size: 2.5em;
    font-weight: bold;
}

.stat-box p {
    margin: 8px 0 0 0;
    font-size: 0.9em;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.unsur-section {
    margin: 30px 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.unsur-section h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 1.5em;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.bagian-section {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #007bff;
}

.bagian-section h3 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 1.2em;
}

.personil-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.personil-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: bold;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.personil-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e9ecef;
}

.personil-table tr:hover {
    background: #f8f9fa;
}

.personil-table tr:last-child td {
    border-bottom: none;
}

.data-source-info {
    margin-top: 40px;
    text-align: center;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin: 20px 0;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert strong {
    color: #0c5460;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }
    
    h1 {
        font-size: 1.8em;
    }
    
    .subtitle {
        font-size: 1em;
    }
    
    .stats {
        flex-direction: column;
        align-items: center;
    }
    
    .stat-box {
        width: 100%;
        max-width: 300px;
    }
    
    .unsur-section {
        margin: 20px 0;
    }
    
    .bagian-section {
        padding: 10px;
    }
    
    .personil-table {
        font-size: 0.8em;
    }
    
    .personil-table th,
    .personil-table td {
        padding: 8px 6px;
    }
}
</style>

<?php include '../includes/components/footer.php'; ?>
