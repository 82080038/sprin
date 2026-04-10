<?php
require_once __DIR__ . '/core/SessionManager.php';
SessionManager::start();
require_once __DIR__ . '/../core/config.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Set headers
header("Content-Type: application/json; charset=UTF-8");

try {
    // Database connection using config constants
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get ID from parameter
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if (!$id) {
        throw new Exception("ID parameter is required");
    }
    
    // Query personil data
    $sql = "
        SELECT 
            p.id, p.nama, p.nrp, p.JK, p.status_ket, p.tanggal_lahir,
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
        WHERE p.id = ? AND p.is_deleted = FALSE AND p.is_active = TRUE
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $personil = $stmt->fetch();
    
    if (!$personil) {
        throw new Exception("Personil not found");
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $personil['id'],
            'nama' => $personil['nama'],
            'nrp' => $personil['nrp'],
            'JK' => $personil['JK'],
            'status_ket' => $personil['status_ket'],
            'tanggal_lahir' => $personil['tanggal_lahir'],
            'pangkat' => $personil['singkatan'] ?: $personil['nama_pangkat'],
            'jabatan' => $personil['nama_jabatan'],
            'bagian' => $personil['nama_bagian'],
            'unsur' => $personil['nama_unsur'],
            'status_kepegawaian' => $personil['status_kepegawaian']
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
