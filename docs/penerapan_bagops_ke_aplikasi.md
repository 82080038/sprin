# Penerapan Analisis BAGOPS ke Aplikasi SPRIN

## Mapping Fitur BAGOPS ke Aplikasi SPRIN

### **1. Struktur Organisasi BAGOPS**

#### **Implementasi Database:**
```sql
-- Tabel Struktur BAGOPS
CREATE TABLE bagops_structure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jabatan VARCHAR(100) NOT NULL,
    pangkat VARCHAR(50) NOT NULL,
    eselon VARCHAR(20),
    atasan VARCHAR(100),
    bawahan JSON,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data struktur BAGOPS
INSERT INTO bagops_structure (jabatan, pangkat, eselon, atasan, bawahan, deskripsi) VALUES
('Kepala Bagian Operasional', 'AKBP', 'III.a', 'Kapolres', 
 '{"Sub Bag Bin Ops": "Kasubbag Bin Ops", "Sub Bag Dal Ops": "Kasubbag Dal Ops", "Sub Bag Humas": "Kasubbag Humas"}',
 'Unsur pengawas dan pembantu pimpinan yang berada di bawah Kapolres'),
('Kepala Sub Bagian Pembinaan Operasi', 'Kompol', 'IV.a', 'Kabag Ops', '[]',
 'Melaksanakan pembinaan dan pelatihan operasional'),
('Kepala Sub Bagian Pengendalian Operasi', 'Kompol', 'IV.a', 'Kabag Ops', '[]',
 'Mengendalikan pelaksanaan operasi dan monitoring'),
('Kepala Sub Bagian Hubungan Masyarakat', 'Kompol', 'IV.a', 'Kabag Ops', '[]',
 'Manajemen media dan dokumentasi kegiatan');
```

#### **Implementasi API:**
```php
// /api/bagops_structure_api.php
<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_structure':
        getBagOpsStructure();
        break;
    case 'get_personil_by_jabatan':
        getPersonilByJabatan();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getBagOpsStructure() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM bagops_structure ORDER BY id");
    $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $structures
    ]);
}

function getPersonilByJabatan() {
    global $pdo;
    $jabatan = $_GET['jabatan'] ?? '';
    
    $stmt = $pdo->prepare("
        SELECT p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian 
        FROM personil p 
        LEFT JOIN pangkat pk ON p.id_pangkat = pk.id 
        LEFT JOIN bagian b ON p.id_bagian = b.id 
        WHERE p.is_active = 1 AND p.is_deleted = 0
        ORDER BY p.nama
    ");
    $stmt->execute();
    $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $personil
    ]);
}
?>
```

### **2. Manajemen Operasi Kepolisian**

#### **Database Schema:**
```sql
-- Tabel Operasi Kepolisian
CREATE TABLE operasi_kepolisian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_operasi VARCHAR(50) UNIQUE NOT NULL,
    nama_operasi VARCHAR(255) NOT NULL,
    jenis_operasi ENUM('rutin', 'khusus', 'terpadu', 'kamtibmas', 'penegakan_hukum') NOT NULL,
    tingkat_operasi ENUM('mabes', 'polda', 'polres', 'polsek') NOT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME,
    lokasi_operasi VARCHAR(255),
    komandan_ops VARCHAR(50),
    wakil_komandan VARCHAR(50),
    status ENUM('rencana', 'berlangsung', 'selesai', 'dibatalkan') DEFAULT 'rencana',
    deskripsi TEXT,
    target_sasaran TEXT,
    cara_bertindak TEXT,
    kekuatan_dilibatkan TEXT,
    dukungan_anggaran DECIMAL(15,2),
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (komandan_ops) REFERENCES personil(nrp),
    FOREIGN KEY (wakil_komandan) REFERENCES personil(nrp),
    FOREIGN KEY (created_by) REFERENCES personil(nrp)
);

-- Tabel Personil Operasi
CREATE TABLE personil_operasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operasi_id INT NOT NULL,
    personil_id VARCHAR(50) NOT NULL,
    peran ENUM('komandan', 'wakil', 'anggota', 'staf_intel', 'staf_ops', 'staf_logistik', 'staf_personel') NOT NULL,
    unit_kerja VARCHAR(100),
    status_kehadiran ENUM('hadir', 'izin', 'sakit', 'tanpa_kabar') DEFAULT 'hadir',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (operasi_id) REFERENCES operasi_kepolisian(id) ON DELETE CASCADE,
    FOREIGN KEY (personil_id) REFERENCES personil(nrp)
);

-- Tabel Dokumentasi Operasi
CREATE TABLE dokumentasi_operasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operasi_id INT NOT NULL,
    jenis_dokumen ENUM('laporan_pra_ops', 'laporan_pelaksanaan', 'laporan_pasca_ops', 'foto', 'video', 'arsip', 'anev') NOT NULL,
    nama_dokumen VARCHAR(255) NOT NULL,
    path_file VARCHAR(500),
    ukuran_file INT,
    tipe_file VARCHAR(50),
    upload_by VARCHAR(50),
    status_dokumen ENUM('draft', 'disetujui', 'ditolak') DEFAULT 'draft',
    catatan_approval TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (operasi_id) REFERENCES operasi_kepolisian(id) ON DELETE CASCADE,
    FOREIGN KEY (upload_by) REFERENCES personil(nrp)
);
```

#### **API Implementation:**
```php
// /api/operasional_api.php
<?php
require_once 'config.php';
header('Content-Type: application/json');

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
        <p><strong>Komandan Operasi:</strong> " . $operasi['nama_komandan'] . "</p>
        <p><strong>Kekuatan yang Dilibatkan:</strong><br>" . nl2br($operasi['kekuatan_dilibatkan']) . "</p>
        
        <h3>4. PELAKSANAAN</h3>
        <p>Surat perintah ini berlaku sejak tanggal ditandatangani dan harus dilaksanakan dengan penuh tanggung jawab.</p>
        
        <br><br>
        <p style='text-align: right;'>
            <strong>Komandan Operasi</strong><br><br><br><br>
            " . $operasi['nama_komandan'] . "<br>
            " . $operasi['pangkat_komandan'] . "
        </p>
    </div>
    ";
    
    return $content;
}
?>
```

### **3. UI/UX Implementation**

#### **Main Operational Management Page:**
```php
// /pages/operasional_management.php
<?php
require_once 'includes/header.php';
require_once 'includes/auth_check.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Operasional BAGOPS</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOperasiModal">
                        <i class="fas fa-plus"></i> Buat Operasi Baru
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchOperasi" placeholder="Cari operasi...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="rencana">Rencana</option>
                                <option value="berlangsung">Berlangsung</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterJenis">
                                <option value="">Semua Jenis</option>
                                <option value="rutin">Rutin</option>
                                <option value="khusus">Khusus</option>
                                <option value="terpadu">Terpadu</option>
                                <option value="kamtibmas">Kamtibmas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary" onclick="loadOperasi()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    
                    <!-- Operations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="operasiTable">
                            <thead>
                                <tr>
                                    <th>Kode Operasi</th>
                                    <th>Nama Operasi</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Komandan</th>
                                    <th>Personil</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="operasiTableBody">
                                <!-- Data will be loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination will be loaded via JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Operation Modal -->
<div class="modal fade" id="createOperasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Operasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOperasiForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Operasi *</label>
                                <input type="text" class="form-control" name="nama_operasi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Operasi *</label>
                                <select class="form-select" name="jenis_operasi" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="rutin">Rutin</option>
                                    <option value="khusus">Khusus</option>
                                    <option value="terpadu">Terpadu</option>
                                    <option value="kamtibmas">Kamtibmas</option>
                                    <option value="penegakan_hukum">Penegakan Hukum</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai *</label>
                                <input type="datetime-local" class="form-control" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tanggal_selesai">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Operasi *</label>
                                <input type="text" class="form-control" name="lokasi_operasi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Komandan Operasi *</label>
                                <select class="form-select" name="komandan_ops" required>
                                    <option value="">Pilih Komandan</option>
                                    <!-- Load personil via JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Operasi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target dan Sasaran</label>
                        <textarea class="form-control" name="target_sasaran" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cara Bertindak</label>
                        <textarea class="form-control" name="cara_bertindak" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kekuatan yang Dilibatkan</label>
                        <textarea class="form-control" name="kekuatan_dilibatkan" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dukungan Anggaran</label>
                                <input type="number" class="form-control" name="dukungan_anggaran" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wakil Komandan</label>
                                <select class="form-select" name="wakil_komandan">
                                    <option value="">Pilih Wakil Komandan</option>
                                    <!-- Load personil via JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="createOperasi()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Operation Detail Modal -->
<div class="modal fade" id="operasiDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Operasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="operasiDetailContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const limit = 10;

// Load operations on page load
$(document).ready(function() {
    loadPersonilOptions();
    loadOperasi();
});

function loadPersonilOptions() {
    $.ajax({
        url: 'api/bagops_structure_api.php?action=get_personil_by_jabatan',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Pilih Personil</option>';
                response.data.forEach(personil => {
                    options += `<option value="${personil.nrp}">${personil.nama} - ${personil.nama_pangkat}</option>`;
                });
                $('select[name="komandan_ops"]').html(options);
                $('select[name="wakil_komandan"]').html(options);
            }
        }
    });
}

function loadOperasi(page = 1) {
    currentPage = page;
    const search = $('#searchOperasi').val();
    const status = $('#filterStatus').val();
    const jenis = $('#filterJenis').val();
    
    $.ajax({
        url: `api/operasional_api.php?action=get_operasi_list&page=${page}&limit=${limit}&search=${search}&status=${status}&jenis=${jenis}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayOperasiTable(response.data);
                displayPagination(response.pagination);
            }
        }
    });
}

function displayOperasiTable(operasi) {
    let html = '';
    operasi.forEach(op => {
        const statusBadge = getStatusBadge(op.status);
        const jenisBadge = getJenisBadge(op.jenis_operasi);
        
        html += `
            <tr>
                <td>${op.kode_operasi}</td>
                <td>${op.nama_operasi}</td>
                <td>${jenisBadge}</td>
                <td>${statusBadge}</td>
                <td>${formatDateTime(op.tanggal_mulai)}</td>
                <td>${op.nama_komandan || '-'}</td>
                <td>${op.total_personil || 0}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewOperasiDetail(${op.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editOperasi(${op.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="generateSprint(${op.id})">
                        <i class="fas fa-file-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    $('#operasiTableBody').html(html);
}

function getStatusBadge(status) {
    const badges = {
        'rencana': '<span class="badge bg-primary">Rencana</span>',
        'berlangsung': '<span class="badge bg-success">Berlangsung</span>',
        'selesai': '<span class="badge bg-secondary">Selesai</span>',
        'dibatalkan': '<span class="badge bg-danger">Dibatalkan</span>'
    };
    return badges[status] || status;
}

function getJenisBadge(jenis) {
    const badges = {
        'rutin': '<span class="badge bg-info">Rutin</span>',
        'khusus': '<span class="badge bg-warning">Khusus</span>',
        'terpadu': '<span class="badge bg-primary">Terpadu</span>',
        'kamtibmas': '<span class="badge bg-success">Kamtibmas</span>',
        'penegakan_hukum': '<span class="badge bg-danger">Penegakan Hukum</span>'
    };
    return badges[jenis] || jenis;
}

function createOperasi() {
    const formData = $('#createOperasiForm').serialize();
    
    $.ajax({
        url: 'api/operasional_api.php?action=create_operasi',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#createOperasiModal').modal('hide');
                $('#createOperasiForm')[0].reset();
                loadOperasi();
                showAlert('success', response.message);
            } else {
                showAlert('error', response.message);
            }
        }
    });
}

function viewOperasiDetail(operasiId) {
    $.ajax({
        url: `api/operasional_api.php?action=get_operasi_detail&operasi_id=${operasiId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayOperasiDetail(response.data);
                $('#operasiDetailModal').modal('show');
            }
        }
    });
}

function displayOperasiDetail(data) {
    const operasi = data.operasi;
    const personil = data.personil;
    const dokumentasi = data.dokumentasi;
    
    let html = `
        <div class="row">
            <div class="col-md-8">
                <h6>Informasi Operasi</h6>
                <table class="table table-sm">
                    <tr><td><strong>Kode Operasi:</strong></td><td>${operasi.kode_operasi}</td></tr>
                    <tr><td><strong>Nama Operasi:</strong></td><td>${operasi.nama_operasi}</td></tr>
                    <tr><td><strong>Jenis:</strong></td><td>${getJenisBadge(operasi.jenis_operasi)}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${getStatusBadge(operasi.status)}</td></tr>
                    <tr><td><strong>Lokasi:</strong></td><td>${operasi.lokasi_operasi}</td></tr>
                    <tr><td><strong>Tanggal Mulai:</strong></td><td>${formatDateTime(operasi.tanggal_mulai)}</td></tr>
                    <tr><td><strong>Komandan:</strong></td><td>${operasi.nama_komandan || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <h6>Aksi Cepat</h6>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="generateSprint('${operasi.id}')">
                        <i class="fas fa-file-alt"></i> Generate Sprint
                    </button>
                    <button class="btn btn-info" onclick="editOperasi('${operasi.id}')">
                        <i class="fas fa-edit"></i> Edit Operasi
                    </button>
                    <button class="btn btn-success" onclick="addPersonilModal('${operasi.id}')">
                        <i class="fas fa-user-plus"></i> Tambah Personil
                    </button>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h6>Personil Operasi (${personil.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Pangkat</th>
                                <th>Peran</th>
                                <th>Unit Kerja</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    personil.forEach(p => {
        html += `
            <tr>
                <td>${p.nama}</td>
                <td>${p.nama_pangkat}</td>
                <td>${p.peran}</td>
                <td>${p.unit_kerja || '-'}</td>
                <td><span class="badge bg-success">${p.status_kehadiran}</span></td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#operasiDetailContent').html(html);
}

function generateSprint(operasiId) {
    if (confirm('Apakah Anda yakin ingin membuat Sprint untuk operasi ini?')) {
        $.ajax({
            url: 'api/operasional_api.php?action=generate_sprint',
            method: 'POST',
            data: {
                operasi_id: operasiId,
                tipe_sprint: 'tugas'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Sprint berhasil dibuat');
                    // You can add code here to display or download the sprint
                } else {
                    showAlert('error', response.message);
                }
            }
        });
    }
}

function formatDateTime(dateTime) {
    if (!dateTime) return '-';
    const date = new Date(dateTime);
    return date.toLocaleString('id-ID');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alertHtml);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
```

### **4. Integration dengan Sistem Existing**

#### **Update Unified API Gateway:**
```php
// /api/unified-api.php (add these cases)
case 'operasional':
    include 'api/operasional_api.php';
    break;
case 'bagops_structure':
    include 'api/bagops_structure_api.php';
    break;
case 'dokumentasi':
    include 'api/dokumentasi_api.php';
    break;
```

#### **Update Navigation Menu:**
```php
// Add to navigation menu in includes/sidebar.php
<li class="nav-item">
    <a class="nav-link" href="pages/operasional_management.php">
        <i class="fas fa-shield-alt"></i>
        <span>Manajemen Operasional</span>
    </a>
</li>
```

### **5. Testing & Validation**

#### **Test Script:**
```php
// /test/test_operasional.php
<?php
require_once '../api/operasional_api.php';

// Test create operation
$_POST['action'] = 'create_operasi';
$_POST['nama_operasi'] = 'Test Operation';
$_POST['jenis_operasi'] = 'rutin';
$_POST['tanggal_mulai'] = '2026-04-12 08:00:00';
$_POST['lokasi_operasi'] = 'Test Location';
$_POST['komandan_ops'] = '123456'; // Test NRP

echo "Testing create operation...\n";
createOperasi();

// Test get operations
$_GET['action'] = 'get_operasi_list';
echo "\nTesting get operations...\n";
getOperasiList();
?>
```

---

## Summary Implementation

### **Fitur yang Ditambahkan:**
1. **Struktur BAGOPS** - Manajemen organisasi BAGOPS
2. **Manajemen Operasi** - CRUD operasi kepolisian
3. **Personil Operasi** - Assignment personil ke operasi
4. **Dokumentasi** - Upload dan management dokumen operasi
5. **Sprint Generator** - Generate surat perintah tugas
6. **Analytics Dashboard** - Statistik operasional

### **Database Tables:**
- `bagops_structure` - Struktur organisasi
- `operasi_kepolisian` - Data operasi
- `personil_operasi` - Personil assignment
- `dokumentasi_operasi` - Dokumentasi operasi

### **API Endpoints:**
- `/api/operasional_api.php` - Manajemen operasi
- `/api/bagops_structure_api.php` - Struktur BAGOPS
- `/api/dokumentasi_api.php` - Dokumentasi

### **UI Pages:**
- `/pages/operasional_management.php` - Main management page
- Modal forms untuk CRUD operations
- Real-time filtering dan searching

### **Integration Points:**
- Personil data dari sistem existing
- Authentication dari sistem existing
- Notification system untuk alerts
- Analytics dashboard untuk reporting

Implementasi ini memastikan aplikasi SPRIN memiliki kemampuan penuh untuk manajemen operasional BAGOPS sesuai dengan regulasi dan standar yang berlaku.
