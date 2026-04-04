<?php
declare(strict_types=1);
/**
 * Enhanced Personil Management API v2.0
 * Personil-First Flow - 100% Compliance dengan PERKAP
 */

require_once '../core/config.php';
require_once 'auth_helper.php';

class PersonilManagementV2 {
    private $db;
    public $auth;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->auth = APIAuth::getInstance();
    }
    
    /**
     * Get personil list with comprehensive data
     */
    public function getPersonilList($filters = []) {
        $whereClause = "WHERE p.is_active = 1";
        $params = [];
        
        // Filter by unsur
        if (!empty($filters['id_unsur'])) {
            $whereClause .= " AND p.id_unsur = ?";
            $params[] = $filters['id_unsur'];
        }
        
        // Filter by bagian
        if (!empty($filters['id_bagian'])) {
            $whereClause .= " AND p.id_bagian = ?";
            $params[] = $filters['id_bagian'];
        }
        
        // Filter by pangkat
        if (!empty($filters['id_pangkat'])) {
            $whereClause .= " AND p.id_pangkat = ?";
            $params[] = $filters['id_pangkat'];
        }
        
        // Filter by jenis penugasan
        if (!empty($filters['id_jenis_penugasan'])) {
            $whereClause .= " AND p.id_jenis_penugasan = ?";
            $params[] = $filters['id_jenis_penugasan'];
        }
        
        // Search by nama or NRP
        if (!empty($filters['search'])) {
            $whereClause .= " AND (p.nama LIKE ? OR p.nrp LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query = "SELECT p.*, 
                        pg.nama_pangkat, pg.singkatan,
                        mjp.nama_jenis_pegawai,
                        j.nama_jabatan,
                        u.nama_unsur,
                        b.nama_bagian,
                        msf.nama_satuan_fungsi,
                        mup.nama_unit_pendukung,
                        msk.nama as status_kepegawaian,
                        mjn.nama as jenis_penugasan,
                        mjn.kode as kode_penugasan,
                        maj.nama as alasan_penugasan,
                        msj.nama as status_jabatan,
                        TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur,
                        TIMESTAMPDIFF(YEAR, p.tanggal_masuk, CURDATE()) as masa_kerja_tahun,
                        TIMESTAMPDIFF(MONTH, p.tanggal_masuk, CURDATE()) % 12 as masa_kerja_bulan
                 FROM personil p
                 LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
                 LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
                 LEFT JOIN jabatan j ON p.id_jabatan = j.id
                 LEFT JOIN unsur u ON p.id_unsur = u.id
                 LEFT JOIN bagian b ON p.id_bagian = b.id
                 LEFT JOIN master_satuan_fungsi msf ON p.id_satuan_fungsi = msf.id
                 LEFT JOIN master_unit_pendukung mup ON p.id_unit_pendukung = mup.id
                 LEFT JOIN master_status_kepegawaian msk ON p.id_status_kepegawaian = msk.id
                 LEFT JOIN master_jenis_penugasan mjn ON p.id_jenis_penugasan = mjn.id
                 LEFT JOIN master_alasan_penugasan maj ON p.id_alasan_penugasan = maj.id
                 LEFT JOIN master_status_jabatan msj ON p.id_status_jabatan = msj.id
                 $whereClause
                 ORDER BY u.urutan, b.urutan, pg.level_pangkat DESC, p.nama";
        
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
        }
        $stmt->execute();
        
        $data = [];
        while ($row = $stmt->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Add new personil with validation
     */
    public function addPersonil($data) {
        // Validate required fields
        $required = ['nrp', 'nama', 'tempat_lahir', 'tanggal_lahir', 'JK', 'id_pangkat', 'id_jenis_pegawai', 'tanggal_masuk'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required");
            }
        }
        
        // Validate NRP format
        if (!preg_match('/^[0-9]{8}$/', $data['nrp'])) {
            throw new Exception("NRP harus 8 digit angka");
        }
        
        // Check NRP uniqueness
        $query = "SELECT COUNT(*) FROM personil WHERE nrp = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $data['nrp']);
        $stmt->execute();
        if ($stmt->fetch_assoc()['COUNT(*)'] > 0) {
            throw new Exception("NRP sudah terdaftar");
        }
        
        // Validate age
        $age = date_diff(date_create($data['tanggal_lahir']), date_create(), 'y')->y;
        if ($age < 18) {
            throw new Exception("Umur minimal 18 tahun");
        }
        
        // Insert personil
        $query = "INSERT INTO personil (
                    nrp, nama, gelar_depan, gelar_belakang, tempat_lahir, tanggal_lahir, JK,
                    id_pangkat, id_jenis_pegawai, id_jabatan, id_unsur, id_bagian, id_satuan_fungsi, id_unit_pendukung,
                    id_status_kepegawaian, status_ket, alasan_status,
                    id_jenis_penugasan, id_alasan_penugasan, id_status_jabatan,
                    alamat, telepon, email,
                    pendidikan_terakhir, jurusan, tahun_lulus,
                    status_nikah, jumlah_anak,
                    tanggal_masuk, tanggal_pensiun, no_karpeg,
                    created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('sssssssiiiiiiiiiiiisssssssssssssssss', 
            $data['nrp'], $data['nama'], $data['gelar_depan'] ?? '', $data['gelar_belakang'] ?? '',
            $data['tempat_lahir'], $data['tanggal_lahir'], $data['JK'],
            $data['id_pangkat'], $data['id_jenis_pegawai'], 
            $data['id_jabatan'] ?? null, $data['id_unsur'] ?? null, $data['id_bagian'] ?? null,
            $data['id_satuan_fungsi'] ?? null, $data['id_unit_pendukung'] ?? null,
            $data['id_status_kepegawaian'] ?? 1, $data['status_ket'] ?? 'aktif', $data['alasan_status'] ?? '',
            $data['id_jenis_penugasan'] ?? null, $data['id_alasan_penugasan'] ?? null, $data['id_status_jabatan'] ?? null,
            $data['alamat'] ?? '', $data['telepon'] ?? '', $data['email'] ?? '',
            $data['pendidikan_terakhir'] ?? '', $data['jurusan'] ?? '', $data['tahun_lulus'] ?? 0,
            $data['status_nikah'] ?? '', $data['jumlah_anak'] ?? 0,
            $data['tanggal_masuk'], $data['tanggal_pensiun'] ?? null, $data['no_karpeg'] ?? '',
            $_SESSION['user_id'] ?? 'system'
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menambahkan personil: " . $stmt->error);
        }
        
        return $this->db->insert_id;
    }
    
    /**
     * Update personil data
     */
    public function updatePersonil($id, $data) {
        // Check if personil exists
        $query = "SELECT id FROM personil WHERE id = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        if ($stmt->fetch_assoc() === null) {
            throw new Exception("Personil tidak ditemukan");
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        $types = '';
        
        $allowedFields = [
            'nama' => 's', 'gelar_depan' => 's', 'gelar_belakang' => 's', 'tempat_lahir' => 's', 
            'tanggal_lahir' => 's', 'JK' => 's', 'id_pangkat' => 'i', 'id_jenis_pegawai' => 'i',
            'id_jabatan' => 'i', 'id_unsur' => 'i', 'id_bagian' => 'i', 'id_satuan_fungsi' => 'i', 'id_unit_pendukung' => 'i',
            'id_status_kepegawaian' => 'i', 'status_ket' => 's', 'alasan_status' => 's',
            'id_jenis_penugasan' => 'i', 'id_alasan_penugasan' => 'i', 'id_status_jabatan' => 'i',
            'alamat' => 's', 'telepon' => 's', 'email' => 's',
            'pendidikan_terakhir' => 's', 'jurusan' => 's', 'tahun_lulus' => 'i',
            'status_nikah' => 's', 'jumlah_anak' => 'i', 'tanggal_pensiun' => 's', 'no_karpeg' => 's'
        ];
        
        foreach ($allowedFields as $field => $type) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }
        
        if (empty($updateFields)) {
            throw new Exception("Tidak ada field yang akan diupdate");
        }
        
        $params[] = $id;
        $types .= 'i';
        
        $query = "UPDATE personil SET " . implode(', ', $updateFields) . ", updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal update personil: " . $stmt->error);
        }
        
        return true;
    }
    
    /**
     * Soft delete personil
     */
    public function deletePersonil($id, $alasan = '') {
        $query = "UPDATE personil SET is_active = 0, is_deleted = 1, alasan_status = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssi', $alasan, $_SESSION['user_id'] ?? 'system', $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menghapus personil: " . $stmt->error);
        }
        
        return true;
    }
    
    /**
     * Get personil detail with complete information
     */
    public function getPersonilDetail($id) {
        $query = "SELECT p.*, 
                        pg.nama_pangkat, pg.singkatan,
                        mjp.nama_jenis_pegawai,
                        j.nama_jabatan, j.kode_jabatan,
                        u.nama_unsur, u.kategori as unsur_kategori,
                        b.nama_bagian,
                        msf.nama_satuan_fungsi,
                        mup.nama_unit_pendukung,
                        msk.nama as status_kepegawaian,
                        mjn.nama as jenis_penugasan,
                        mjn.kode as kode_penugasan,
                        mjn.deskripsi as deskripsi_penugasan,
                        maj.nama as alasan_penugasan,
                        msj.nama as status_jabatan,
                        TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur,
                        TIMESTAMPDIFF(YEAR, p.tanggal_masuk, CURDATE()) as masa_kerja_tahun,
                        TIMESTAMPDIFF(MONTH, p.tanggal_masuk, CURDATE()) % 12 as masa_kerja_bulan,
                        DATEDIFF(p.tanggal_pensiun, CURDATE()) as hari_menuju_pensiun
                 FROM personil p
                 LEFT JOIN pangkat pg ON p.id_pangkat = pg.id
                 LEFT JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
                 LEFT JOIN jabatan j ON p.id_jabatan = j.id
                 LEFT JOIN unsur u ON p.id_unsur = u.id
                 LEFT JOIN bagian b ON p.id_bagian = b.id
                 LEFT JOIN master_satuan_fungsi msf ON p.id_satuan_fungsi = msf.id
                 LEFT JOIN master_unit_pendukung mup ON p.id_unit_pendukung = mup.id
                 LEFT JOIN master_status_kepegawaian msk ON p.id_status_kepegawaian = msk.id
                 LEFT JOIN master_jenis_penugasan mjn ON p.id_jenis_penugasan = mjn.id
                 LEFT JOIN master_alasan_penugasan maj ON p.id_alasan_penugasan = maj.id
                 LEFT JOIN master_status_jabatan msj ON p.id_status_jabatan = msj.id
                 WHERE p.id = ? AND p.is_active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $personil = $stmt->fetch_assoc();
        if (!$personil) {
            throw new Exception("Personil tidak ditemukan");
        }
        
        // Get riwayat jabatan
        $riwayatQuery = "SELECT rj.*, 
                              jl.nama_jabatan as jabatan_lama,
                              jb.nama_jabatan as jabatan_baru,
                              ul.nama_unsur as unsur_lama,
                              ub.nama_unsur as unsur_baru,
                              bl.nama_bagian as bagian_lama,
                              bb.nama_bagian as bagian_baru
                       FROM riwayat_jabatan rj
                       LEFT JOIN jabatan jl ON rj.id_jabatan_lama = jl.id
                       LEFT JOIN jabatan jb ON rj.id_jabatan_baru = jb.id
                       LEFT JOIN unsur ul ON rj.id_unsur_lama = ul.id
                       LEFT JOIN unsur ub ON rj.id_unsur_baru = ub.id
                       LEFT JOIN bagian bl ON rj.id_bagian_lama = bl.id
                       LEFT JOIN bagian bb ON rj.id_bagian_baru = bb.id
                       WHERE rj.id_personil = ? AND rj.is_aktif = 1
                       ORDER BY rj.tanggal_mutasi DESC";
        
        $stmt = $this->db->prepare($riwayatQuery);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $riwayatJabatan = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayatJabatan[] = $row;
        }
        
        // Get riwayat pangkat
        $pangkatQuery = "SELECT rp.*, 
                             pl.nama_pangkat as pangkat_lama,
                             pb.nama_pangkat as pangkat_baru
                      FROM riwayat_pangkat rp
                      LEFT JOIN pangkat pl ON rp.id_pangkat_lama = pl.id
                      LEFT JOIN pangkat pb ON rp.id_pangkat_baru = pb.id
                      WHERE rp.id_personil = ? AND rp.is_aktif = 1
                      ORDER BY rp.tanggal_kenaikan_pangkat DESC";
        
        $stmt = $this->db->prepare($pangkatQuery);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $riwayatPangkat = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayatPangkat[] = $row;
        }
        
        // Get riwayat penugasan
        $penugasanQuery = "SELECT rp.*, 
                                j.nama_jabatan,
                                mjn.nama as jenis_penugasan,
                                maj.nama as alasan_penugasan
                         FROM riwayat_penugasan rp
                         LEFT JOIN jabatan j ON rp.id_jabatan = j.id
                         LEFT JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                         LEFT JOIN master_alasan_penugasan maj ON rp.id_alasan_penugasan = maj.id
                         WHERE rp.id_personil = ? AND rp.is_aktif = 1
                         ORDER BY rp.tanggal_mulai DESC";
        
        $stmt = $this->db->prepare($penugasanQuery);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $riwayatPenugasan = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayatPenugasan[] = $row;
        }
        
        $personil['riwayat_jabatan'] = $riwayatJabatan;
        $personil['riwayat_pangkat'] = $riwayatPangkat;
        $personil['riwayat_penugasan'] = $riwayatPenugasan;
        
        return $personil;
    }
    
    /**
     * Get personil statistics
     */
    public function getPersonilStatistics() {
        $stats = [];
        
        // Total personil by status
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as aktif,
                    COUNT(CASE WHEN is_active = 0 THEN 1 END) as non_aktif
                 FROM personil";
        $result = $this->db->query($query);
        $stats['total'] = $result->fetch_assoc();
        
        // By pangkat
        $query = "SELECT pg.nama_pangkat, COUNT(*) as count
                 FROM personil p
                 JOIN pangkat pg ON p.id_pangkat = pg.id
                 WHERE p.is_active = 1
                 GROUP BY pg.id, pg.nama_pangkat
                 ORDER BY pg.level_pangkat DESC";
        $result = $this->db->query($query);
        $stats['by_pangkat'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_pangkat'][] = $row;
        }
        
        // By jenis pegawai
        $query = "SELECT mjp.nama_jenis_pegawai, COUNT(*) as count
                 FROM personil p
                 JOIN master_jenis_pegawai mjp ON p.id_jenis_pegawai = mjp.id
                 WHERE p.is_active = 1
                 GROUP BY mjp.id, mjp.nama_jenis_pegawai
                 ORDER BY mjp.urutan";
        $result = $this->db->query($query);
        $stats['by_jenis_pegawai'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_jenis_pegawai'][] = $row;
        }
        
        // By unsur
        $query = "SELECT u.nama_unsur, COUNT(*) as count
                 FROM personil p
                 JOIN unsur u ON p.id_unsur = u.id
                 WHERE p.is_active = 1
                 GROUP BY u.id, u.nama_unsur
                 ORDER BY u.urutan";
        $result = $this->db->query($query);
        $stats['by_unsur'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_unsur'][] = $row;
        }
        
        // By penugasan
        $query = "SELECT mjn.nama as jenis_penugasan, COUNT(*) as count
                 FROM personil p
                 JOIN master_jenis_penugasan mjn ON p.id_jenis_penugasan = mjn.id
                 WHERE p.is_active = 1
                 GROUP BY mjn.id, mjn.nama
                 ORDER BY mjn.nama";
        $result = $this->db->query($query);
        $stats['by_penugasan'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_penugasan'][] = $row;
        }
        
        // Age distribution
        $query = "SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
                        ELSE '56+'
                    END as age_range,
                    COUNT(*) as count
                 FROM personil
                 WHERE is_active = 1
                 GROUP BY age_range
                 ORDER BY age_range";
        $result = $this->db->query($query);
        $stats['by_age'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_age'][] = $row;
        }
        
        return $stats;
    }
    
    /**
     * Validate personil data
     */
    public function validatePersonilData($data, $id = null) {
        $errors = [];
        
        // Validate NRP
        if (!empty($data['nrp'])) {
            if (!preg_match('/^[0-9]{8}$/', $data['nrp'])) {
                $errors[] = "NRP harus 8 digit angka";
            }
            
            // Check uniqueness
            $query = "SELECT COUNT(*) FROM personil WHERE nrp = ? AND is_active = 1";
            $params = [$data['nrp']];
            if ($id) {
                $query .= " AND id != ?";
                $params[] = $id;
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            
            if ($stmt->fetch_assoc()['COUNT(*)'] > 0) {
                $errors[] = "NRP sudah terdaftar";
            }
        }
        
        // Validate age
        if (!empty($data['tanggal_lahir'])) {
            $age = date_diff(date_create($data['tanggal_lahir']), date_create(), 'y')->y;
            if ($age < 18) {
                $errors[] = "Umur minimal 18 tahun";
            }
        }
        
        // Validate email format
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email tidak valid";
            }
        }
        
        // Validate phone format
        if (!empty($data['telepon'])) {
            if (!preg_match('/^[0-9+\-\s()]+$/', $data['telepon'])) {
                $errors[] = "Format telepon tidak valid";
            }
        }
        
        // Validate tanggal masuk vs tanggal lahir
        if (!empty($data['tanggal_lahir']) && !empty($data['tanggal_masuk'])) {
            if ($data['tanggal_masuk'] < $data['tanggal_lahir']) {
                $errors[] = "Tanggal masuk tidak boleh sebelum tanggal lahir";
            }
            
            $ageAtJoin = date_diff(date_create($data['tanggal_lahir']), date_create($data['tanggal_masuk']), 'y')->y;
            if ($ageAtJoin < 18) {
                $errors[] = "Usur minimal saat masuk 18 tahun";
            }
        }
        
        return $errors;
    }
}

// API Handler
try {
    $personil = new PersonilManagementV2();
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    switch ($action) {
        case 'get_personil_list':
            $personil->auth->requireAuth();
            $filters = [
                'id_unsur' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? null,
                'id_bagian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? null,
                'id_pangkat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? null,
                'id_jenis_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? null,
                'search' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'search', FILTER_SANITIZE_STRING) ?? null
            ];
            $data = $personil->getPersonilList($filters);
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'add_personil':
            $personil->auth->requireAuth();
            try {
                $data = [
                    'nrp' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nrp', FILTER_SANITIZE_STRING) ?? '',
                    'nama' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? '',
                    'gelar_depan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_depan', FILTER_SANITIZE_STRING) ?? '',
                    'gelar_belakang' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_belakang', FILTER_SANITIZE_STRING) ?? '',
                    'tempat_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tempat_lahir', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_lahir', FILTER_SANITIZE_STRING) ?? '',
                    'JK' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'JK', FILTER_SANITIZE_STRING) ?? '',
                    'id_pangkat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? 0,
                    'id_jenis_pegawai' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? 0,
                    'id_jabatan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? null,
                    'id_unsur' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? null,
                    'id_bagian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? null,
                    'id_satuan_fungsi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_satuan_fungsi', FILTER_SANITIZE_STRING) ?? null,
                    'id_unit_pendukung' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unit_pendukung', FILTER_SANITIZE_STRING) ?? null,
                    'id_status_kepegawaian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_status_kepegawaian', FILTER_SANITIZE_STRING) ?? 1,
                    'status_ket' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_ket', FILTER_SANITIZE_STRING) ?? 'aktif',
                    'alasan_status' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_status', FILTER_SANITIZE_STRING) ?? '',
                    'id_jenis_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? null,
                    'id_alasan_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_alasan_penugasan', FILTER_SANITIZE_STRING) ?? null,
                    'id_status_jabatan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_status_jabatan', FILTER_SANITIZE_STRING) ?? null,
                    'alamat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alamat', FILTER_SANITIZE_STRING) ?? '',
                    'telepon' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'telepon', FILTER_SANITIZE_STRING) ?? '',
                    'email' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'email', FILTER_SANITIZE_STRING) ?? '',
                    'pendidikan_terakhir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pendidikan_terakhir', FILTER_SANITIZE_STRING) ?? '',
                    'jurusan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jurusan', FILTER_SANITIZE_STRING) ?? '',
                    'tahun_lulus' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tahun_lulus', FILTER_SANITIZE_STRING) ?? 0,
                    'status_nikah' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_nikah', FILTER_SANITIZE_STRING) ?? '',
                    'jumlah_anak' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jumlah_anak', FILTER_SANITIZE_STRING) ?? 0,
                    'tanggal_masuk' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_masuk', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_pensiun' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_pensiun', FILTER_SANITIZE_STRING) ?? '',
                    'no_karpeg' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_karpeg', FILTER_SANITIZE_STRING) ?? ''
                ];
                
                $id = $personil->addPersonil($data);
                echo json_encode([
                    'success' => true,
                    'message' => 'Personil berhasil ditambahkan',
                    'id' => $id
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'update_personil':
            $personil->auth->requireAuth();
            try {
                $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
                $data = [
                    'nama' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? null,
                    'gelar_depan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_depan', FILTER_SANITIZE_STRING) ?? null,
                    'gelar_belakang' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_belakang', FILTER_SANITIZE_STRING) ?? null,
                    'tempat_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tempat_lahir', FILTER_SANITIZE_STRING) ?? null,
                    'tanggal_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_lahir', FILTER_SANITIZE_STRING) ?? null,
                    'JK' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'JK', FILTER_SANITIZE_STRING) ?? null,
                    'id_pangkat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? null,
                    'id_jenis_pegawai' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? null,
                    'id_jabatan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? null,
                    'id_unsur' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? null,
                    'id_bagian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? null,
                    'id_satuan_fungsi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_satuan_fungsi', FILTER_SANITIZE_STRING) ?? null,
                    'id_unit_pendukung' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unit_pendukung', FILTER_SANITIZE_STRING) ?? null,
                    'id_status_kepegawaian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_status_kepegawaian', FILTER_SANITIZE_STRING) ?? null,
                    'status_ket' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_ket', FILTER_SANITIZE_STRING) ?? null,
                    'alasan_status' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_status', FILTER_SANITIZE_STRING) ?? null,
                    'id_jenis_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? null,
                    'id_alasan_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_alasan_penugasan', FILTER_SANITIZE_STRING) ?? null,
                    'id_status_jabatan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_status_jabatan', FILTER_SANITIZE_STRING) ?? null,
                    'alamat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alamat', FILTER_SANITIZE_STRING) ?? null,
                    'telepon' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'telepon', FILTER_SANITIZE_STRING) ?? null,
                    'email' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'email', FILTER_SANITIZE_STRING) ?? null,
                    'pendidikan_terakhir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'pendidikan_terakhir', FILTER_SANITIZE_STRING) ?? null,
                    'jurusan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jurusan', FILTER_SANITIZE_STRING) ?? null,
                    'tahun_lulus' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tahun_lulus', FILTER_SANITIZE_STRING) ?? null,
                    'status_nikah' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_nikah', FILTER_SANITIZE_STRING) ?? null,
                    'jumlah_anak' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jumlah_anak', FILTER_SANITIZE_STRING) ?? null,
                    'tanggal_pensiun' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_pensiun', FILTER_SANITIZE_STRING) ?? null,
                    'no_karpeg' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_karpeg', FILTER_SANITIZE_STRING) ?? null
                ];
                
                $personil->updatePersonil($id, $data);
                echo json_encode([
                    'success' => true,
                    'message' => 'Data personil berhasil diupdate'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'delete_personil':
            $personil->auth->requireAuth();
            try {
                $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
                $alasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '';
                $personil->deletePersonil($id, $alasan);
                echo json_encode([
                    'success' => true,
                    'message' => 'Personil berhasil dihapus'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'get_personil_detail':
            $personil->auth->requireAuth();
            try {
                $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? 0;
                $detail = $personil->getPersonilDetail($id);
                echo json_encode([
                    'success' => true,
                    'data' => $detail
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'get_personil_statistics':
            $personil->auth->requireAuth();
            $stats = $personil->getPersonilStatistics();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'validate_personil':
            $personil->auth->requireAuth();
            try {
                $data = [
                    'nrp' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nrp', FILTER_SANITIZE_STRING) ?? '',
                    'nama' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nama', FILTER_SANITIZE_STRING) ?? '',
                    'gelar_depan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_depan', FILTER_SANITIZE_STRING) ?? '',
                    'gelar_belakang' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'gelar_belakang', FILTER_SANITIZE_STRING) ?? '',
                    'tempat_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tempat_lahir', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_lahir' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_lahir', FILTER_SANITIZE_STRING) ?? '',
                    'JK' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'JK', FILTER_SANITIZE_STRING) ?? '',
                    'id_pangkat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? null,
                    'id_jenis_pegawai' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_pegawai', FILTER_SANITIZE_STRING) ?? null,
                    'tanggal_masuk' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_masuk', FILTER_SANITIZE_STRING) ?? '',
                    'email' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'email', FILTER_SANITIZE_STRING) ?? '',
                    'telepon' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'telepon', FILTER_SANITIZE_STRING) ?? ''
                ];
                
                $id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id', FILTER_SANITIZE_STRING) ?? null;
                $errors = $personil->validatePersonilData($data, $id);
                
                echo json_encode([
                    'success' => empty($errors),
                    'errors' => $errors
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        default:
            header('HTTP/1.0 400 Bad Request');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
