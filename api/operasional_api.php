<?php
/**
 * Operasional Management API
 * Manajemen operasi kepolisian BAGOPS
 */

require_once 'config.php';
header('Content-Type: application/json');

// Start session for authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_operasi':
        createOperasi();
        break;
    case 'update_operasi':
        updateOperasi();
        break;
    case 'get_operasi_list':
        getOperasiList();
        break;
    case 'get_operasi_detail':
        getOperasiDetail();
        break;
    case 'add_personil_operasi':
        addPersonilOperasi();
        break;
    case 'upload_dokumentasi':
        uploadDokumentasi();
        break;
    case 'generate_sprint':
        generateSprint();
        break;
    case 'delete_operasi':
        deleteOperasi();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createOperasi() {
    global $pdo;
    
    $kode_operasi = generateKodeOperasi();
    $nama_operasi = $_POST['nama_operasi'];
    $jenis_operasi = $_POST['jenis_operasi'];
    $tingkat_operasi = $_POST['tingkat_operasi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? null;
    $lokasi_operasi = $_POST['lokasi_operasi'];
    $komandan_ops = $_POST['komandan_ops'];
    $wakil_komandan = $_POST['wakil_komandan'] ?? null;
    $deskripsi = $_POST['deskripsi'] ?? '';
    $target_sasaran = $_POST['target_sasaran'] ?? '';
    $cara_bertindak = $_POST['cara_bertindak'] ?? '';
    $kekuatan_dilibatkan = $_POST['kekuatan_dilibatkan'] ?? '';
    $dukungan_anggaran = $_POST['dukungan_anggaran'] ?? 0;
    $created_by = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO operasi_kepolisian 
            (kode_operasi, nama_operasi, jenis_operasi, tingkat_operasi, 
             tanggal_mulai, tanggal_selesai, lokasi_operasi, komandan_ops, 
             wakil_komandan, deskripsi, target_sasaran, cara_bertindak, 
             kekuatan_dilibatkan, dukungan_anggaran, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $kode_operasi, $nama_operasi, $jenis_operasi, $tingkat_operasi,
            $tanggal_mulai, $tanggal_selesai, $lokasi_operasi, $komandan_ops,
            $wakil_komandan, $deskripsi, $target_sasaran, $cara_bertindak,
            $kekuatan_dilibatkan, $dukungan_anggaran, $created_by
        ]);
        
        $operasi_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Operasi berhasil dibuat',
            'data' => ['operasi_id' => $operasi_id, 'kode_operasi' => $kode_operasi]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function generateKodeOperasi() {
    $prefix = 'OPS-' . date('Y');
    $counter = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . '-' . $counter;
}

function getOperasiList() {
    global $pdo;
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $jenis = $_GET['jenis'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nama_operasi LIKE ? OR kode_operasi LIKE ? OR lokasi_operasi LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status)) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
    }
    
    if (!empty($jenis)) {
        $where_conditions[] = "jenis_operasi = ?";
        $params[] = $jenis;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM operasi_kepolisian $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get data
    $sql = "
        SELECT o.*, 
               p1.nama as nama_komandan, pk1.nama_pangkat as pangkat_komandan,
               p2.nama as nama_wakil, pk2.nama_pangkat as pangkat_wakil,
               (SELECT COUNT(*) FROM personil_operasi po WHERE po.operasi_id = o.id) as total_personil
        FROM operasi_kepolisian o
        LEFT JOIN personil p1 ON o.komandan_ops = p1.nrp
        LEFT JOIN pangkat pk1 ON p1.id_pangkat = pk1.id
        LEFT JOIN personil p2 ON o.wakil_komandan = p2.nrp
        LEFT JOIN pangkat pk2 ON p2.id_pangkat = pk2.id
        $where_clause
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $operasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $operasi,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function getOperasiDetail() {
    global $pdo;
    
    $operasi_id = $_GET['operasi_id'];
    
    // Get operasi detail
    $stmt = $pdo->prepare("
        SELECT o.*, 
               p1.nama as nama_komandan, pk1.nama_pangkat as pangkat_komandan,
               p2.nama as nama_wakil, pk2.nama_pangkat as pangkat_wakil,
               p3.nama as nama_creator, pk3.nama_pangkat as pangkat_creator
        FROM operasi_kepolisian o
        LEFT JOIN personil p1 ON o.komandan_ops = p1.nrp
        LEFT JOIN pangkat pk1 ON p1.id_pangkat = pk1.id
        LEFT JOIN personil p2 ON o.wakil_komandan = p2.nrp
        LEFT JOIN pangkat pk2 ON p2.id_pangkat = pk2.id
        LEFT JOIN personil p3 ON o.created_by = p3.nrp
        LEFT JOIN pangkat pk3 ON p3.id_pangkat = pk3.id
        WHERE o.id = ?
    ");
    $stmt->execute([$operasi_id]);
    $operasi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$operasi) {
        echo json_encode(['success' => false, 'message' => 'Operasi tidak ditemukan']);
        return;
    }
    
    // Get personil operasi
    $stmt = $pdo->prepare("
        SELECT po.*, p.nama, pk.nama_pangkat, b.nama_bagian
        FROM personil_operasi po
        JOIN personil p ON po.personil_id = p.nrp
        LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
        LEFT JOIN bagian b ON p.id_bagian = b.id
        WHERE po.operasi_id = ?
        ORDER BY po.peran, p.nama
    ");
    $stmt->execute([$operasi_id]);
    $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get dokumentasi
    $stmt = $pdo->prepare("
        SELECT do.*, p.nama as nama_uploader
        FROM dokumentasi_operasi do
        LEFT JOIN personil p ON do.upload_by = p.nrp
        WHERE do.operasi_id = ?
        ORDER BY do.created_at DESC
    ");
    $stmt->execute([$operasi_id]);
    $dokumentasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'operasi' => $operasi,
            'personil' => $personil,
            'dokumentasi' => $dokumentasi
        ]
    ]);
}

function addPersonilOperasi() {
    global $pdo;
    
    $operasi_id = $_POST['operasi_id'];
    $personil_id = $_POST['personil_id'];
    $peran = $_POST['peran'];
    $unit_kerja = $_POST['unit_kerja'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO personil_operasi 
            (operasi_id, personil_id, peran, unit_kerja, catatan)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$operasi_id, $personil_id, $peran, $unit_kerja, $catatan]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Personil berhasil ditambahkan ke operasi'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function generateSprint() {
    global $pdo;
    
    $operasi_id = $_POST['operasi_id'];
    $tipe_sprint = $_POST['tipe_sprint']; // 'tugas' atau 'pengamanan'
    
    // Get operasi detail
    $stmt = $pdo->prepare("SELECT * FROM operasi_kepolisian WHERE id = ?");
    $stmt->execute([$operasi_id]);
    $operasi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$operasi) {
        echo json_encode(['success' => false, 'message' => 'Operasi tidak ditemukan']);
        return;
    }
    
    // Generate Sprint content
    $sprint_content = generateSprintContent($operasi, $tipe_sprint);
    
    // Save sprint to dokumentasi
    $nama_dokumen = "SPRINT-" . strtoupper($tipe_sprint) . "-" . $operasi['kode_operasi'] . ".pdf";
    $path_file = "uploads/sprint/" . $nama_dokumen;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO dokumentasi_operasi 
            (operasi_id, jenis_dokumen, nama_dokumen, path_file, upload_by, status_dokumen)
            VALUES (?, ?, ?, ?, ?, 'disetujui')
        ");
        $stmt->execute([$operasi_id, 'laporan_pelaksanaan', $nama_dokumen, $path_file, $_SESSION['user_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sprint berhasil dibuat',
            'data' => [
                'sprint_content' => $sprint_content,
                'dokumen_id' => $pdo->lastInsertId()
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function generateSprintContent($operasi, $tipe_sprint) {
    $prefix = $tipe_sprint == 'tugas' ? 'SPRINT' : 'SPRINPAM';
    $content = "
    <div style='font-family: Arial; padding: 20px;'>
        <h2 style='text-align: center;'>SURAT PERINTAH " . strtoupper($tipe_sprint) . "</h2>
        <p><strong>Nomor:</strong> $prefix/" . $operasi['kode_operasi'] . "/" . date('Y') . "</p>
        <p><strong>Tanggal:</strong> " . date('d F Y') . "</p>
        
        <h3>1. DATA OPERASI</h3>
        <p><strong>Nama Operasi:</strong> " . $operasi['nama_operasi'] . "</p>
        <p><strong>Kode Operasi:</strong> " . $operasi['kode_operasi'] . "</p>
        <p><strong>Jenis Operasi:</strong> " . ucfirst($operasi['jenis_operasi']) . "</p>
        <p><strong>Tanggal Mulai:</strong> " . date('d F Y H:i', strtotime($operasi['tanggal_mulai'])) . "</p>
        <p><strong>Lokasi:</strong> " . $operasi['lokasi_operasi'] . "</p>
        
        <h3>2. SASARAN DAN CARA BERTINDAK</h3>
        <p><strong>Sasaran:</strong><br>" . nl2br($operasi['target_sasaran']) . "</p>
        <p><strong>Cara Bertindak:</strong><br>" . nl2br($operasi['cara_bertindak']) . "</p>
        
        <h3>3. PERSONIL YANG DILIBATKAN</h3>
        <p><strong>Komandan Operasi:</strong> " . ($operasi['nama_komandan'] ?? 'N/A') . "</p>
        <p><strong>Kekuatan yang Dilibatkan:</strong><br>" . nl2br($operasi['kekuatan_dilibatkan']) . "</p>
        
        <h3>4. PELAKSANAAN</h3>
        <p>Surat perintah ini berlaku sejak tanggal ditandatangani dan harus dilaksanakan dengan penuh tanggung jawab.</p>
        
        <br><br>
        <p style='text-align: right;'>
            <strong>Komandan Operasi</strong><br><br><br><br>
            " . ($operasi['nama_komandan'] ?? 'N/A') . "<br>
            " . ($operasi['pangkat_komandan'] ?? 'N/A') . "
        </p>
    </div>
    ";
    
    return $content;
}

function deleteOperasi() {
    global $pdo;
    
    $operasi_id = $_POST['operasi_id'];
    
    try {
        // First check if operation exists
        $stmt = $pdo->prepare("SELECT kode_operasi FROM operasi_kepolisian WHERE id = ?");
        $stmt->execute([$operasi_id]);
        $operasi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$operasi) {
            echo json_encode(['success' => false, 'message' => 'Operasi tidak ditemukan']);
            return;
        }
        
        // Delete related records first
        $stmt = $pdo->prepare("DELETE FROM personil_operasi WHERE operasi_id = ?");
        $stmt->execute([$operasi_id]);
        
        $stmt = $pdo->prepare("DELETE FROM dokumentasi_operasi WHERE operasi_id = ?");
        $stmt->execute([$operasi_id]);
        
        // Delete operation
        $stmt = $pdo->prepare("DELETE FROM operasi_kepolisian WHERE id = ?");
        $stmt->execute([$operasi_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Operasi ' . $operasi['kode_operasi'] . ' berhasil dihapus'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function uploadDokumentasi() {
    global $pdo;
    
    $operasi_id = $_POST['operasi_id'];
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $nama_dokumen = $_POST['nama_dokumen'];
    $catatan = $_POST['catatan'] ?? '';
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file'];
        $upload_dir = 'uploads/dokumentasi/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $file['name'];
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO dokumentasi_operasi 
                    (operasi_id, jenis_dokumen, nama_dokumen, path_file, tipe_file, ukuran_file, upload_by, catatan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $operasi_id, $jenis_dokumen, $nama_dokumen, $file_path,
                    $file['type'], $file['size'], $_SESSION['user_id'], $catatan
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Dokumentasi berhasil diupload'
                ]);
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
    }
}
?>
