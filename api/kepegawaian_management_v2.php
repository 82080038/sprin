<?php
declare(strict_types=1);
/**
 * Kepegawaian Management API v2.0
 * Career Management - Kenaikan Pangkat, Mutasi, Promosi
 */

require_once '../core/config.php';
require_once 'auth_helper.php';

class KepegawaianManagementV2 {
    private $db;
    public $auth;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->auth = APIAuth::getInstance();
    }
    
    /**
     * Check eligibility for kenaikan pangkat
     */
    public function checkKenaikanPangkatEligibility($id_personil) {
        // Get personil data
        $query = "SELECT p.*, pg.nama_pangkat, pg.level_pangkat, pg.singkatan
                 FROM personil p
                 JOIN pangkat pg ON p.id_pangkat = pg.id
                 WHERE p.id = ? AND p.is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $personil = $stmt->fetch_assoc();
        
        if (!$personil) {
            throw new Exception("Personil tidak ditemukan");
        }
        
        $eligibility = [
            'eligible' => true,
            'reasons' => [],
            'personil' => $personil,
            'next_pangkat' => null,
            'requirements' => []
        ];
        
        // Check masa kerja
        $masaKerja = $personil['masa_kerja_tahun'];
        $query = "SELECT * FROM jenjang_karir WHERE id_pangkat_saat_ini = ? AND masa_kerja_minimal_tahun <= ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $personil['id_pangkat'], $masaKerja);
        $stmt->execute();
        $jenjang = $stmt->fetch_assoc();
        
        if (!$jenjang) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Tidak ada jenjang karir untuk pangkat saat ini";
            return $eligibility;
        }
        
        $eligibility['next_pangkat'] = $jenjang;
        
        // Check masa kerja minimal
        if ($masaKerja < $jenjang['masa_kerja_minimal_tahun']) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Masa kerja minimal " . $jenjang['masa_kerja_minimal_tahun'] . " tahun (saat ini: " . $masaKerja . " tahun)";
        }
        
        // Check if there are pending disciplinary issues
        $query = "SELECT COUNT(*) FROM personil_disciplinary WHERE id_personil = ? AND status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $pendingDisciplinary = $stmt->fetch_assoc()['COUNT(*)'];
        
        if ($pendingDisciplinary > 0) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Ada " . $pendingDisciplinary . " kasus disiplin pending";
        }
        
        // Check if already promoted within last 6 months
        $query = "SELECT COUNT(*) FROM riwayat_pangkat 
                 WHERE id_personil = ? AND is_aktif = 1 
                 AND tanggal_kenaikan_pangkat >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $recentPromotion = $stmt->fetch_assoc()['COUNT(*)'];
        
        if ($recentPromotion > 0) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Sudah ada kenaikan pangkat dalam 6 bulan terakhir";
        }
        
        // Get next pangkat details
        $query = "SELECT * FROM pangkat WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $jenjang['id_pangkat_berikutnya']);
        $stmt->execute();
        $nextPangkat = $stmt->fetch_assoc();
        $eligibility['next_pangkat']['detail'] = $nextPangkat;
        
        return $eligibility;
    }
    
    /**
     * Process kenaikan pangkat
     */
    public function processKenaikanPangkat($data) {
        $id_personil = $data['id_personil'];
        $id_pangkat_baru = $data['id_pangkat_baru'];
        $tanggal_kenaikan = $data['tanggal_kenaikan_pangkat'];
        $no_sk = $data['no_sk_kenaikan'];
        $tanggal_sk = $data['tanggal_sk_kenaikan'];
        $alasan = $data['alasan_kenaikan'];
        $jenis_kenaikan = $data['jenis_kenaikan'] ?? 'reguler';
        $keterangan = $data['keterangan'] ?? '';
        
        // Check eligibility
        $eligibility = $this->checkKenaikanPangkatEligibility($id_personil);
        if (!$eligibility['eligible']) {
            throw new Exception("Personil tidak eligible untuk kenaikan pangkat: " . implode(', ', $eligibility['reasons']));
        }
        
        // Get current pangkat
        $query = "SELECT id_pangkat FROM personil WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $current = $stmt->fetch_assoc();
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Create riwayat pangkat
            $query = "INSERT INTO riwayat_pangkat (
                        id_personil, id_pangkat_lama, id_pangkat_baru, 
                        tanggal_kenaikan_pangkat, no_sk_kenaikan, tanggal_sk_kenaikan,
                        alasan_kenaikan, jenis_kenaikan, keterangan, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iissssssss', 
                $id_personil, $current['id_pangkat'], $id_pangkat_baru,
                $tanggal_kenaikan, $no_sk, $tanggal_sk, $alasan, $jenis_kenaikan, $keterangan,
                $_SESSION['user_id'] ?? 'system'
            );
            $stmt->execute();
            
            // Update personil pangkat
            $query = "UPDATE personil SET id_pangkat = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('isi', $id_pangkat_baru, $_SESSION['user_id'] ?? 'system', $id_personil);
            $stmt->execute();
            
            // Update masa kerja if needed
            $query = "UPDATE personil SET 
                        masa_kerja_tahun = TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()),
                        masa_kerja_bulan = TIMESTAMPDIFF(MONTH, tanggal_masuk, CURDATE()) % 12
                        WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id_personil);
            $stmt->execute();
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Check eligibility for mutasi jabatan
     */
    public function checkMutasiJabatanEligibility($id_personil, $id_jabatan_baru) {
        // Get personil data
        $query = "SELECT p.*, pg.nama_pangkat, pg.level_pangkat,
                        j.nama_jabatan as jabatan_lama, j.level_eselon as eselon_lama
                 FROM personil p
                 JOIN pangkat pg ON p.id_pangkat = pg.id
                 LEFT JOIN jabatan j ON p.id_jabatan = j.id
                 WHERE p.id = ? AND p.is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $personil = $stmt->fetch_assoc();
        
        if (!$personil) {
            throw new Exception("Personil tidak ditemukan");
        }
        
        // Get target jabatan data
        $query = "SELECT j.*, msj.level_eselon, msj.nama as status_jabatan,
                        pg.nama_pangkat as pangkat_minimal_nama
                 FROM jabatan j
                 JOIN master_status_jabatan msj ON j.id_status_jabatan = msj.id
                 LEFT JOIN pangkat pg ON j.id_pangkat_minimal = pg.id
                 WHERE j.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_jabatan_baru);
        $stmt->execute();
        $jabatanBaru = $stmt->fetch_assoc();
        
        if (!$jabatanBaru) {
            throw new Exception("Jabatan target tidak ditemukan");
        }
        
        $eligibility = [
            'eligible' => true,
            'reasons' => [],
            'personil' => $personil,
            'jabatan_lama' => $personil['jabatan_lama'],
            'jabatan_baru' => $jabatanBaru
        ];
        
        // Check if jabatan is available
        $query = "SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_active = 1 AND id != ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $id_jabatan_baru, $id_personil);
        $stmt->execute();
        $isOccupied = $stmt->fetch_assoc()['COUNT(*)'] > 0;
        
        if ($isOccupied) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Jabatan target sudah diisi oleh personil lain";
        }
        
        // Check pangkat compatibility
        if ($jabatanBaru['id_pangkat_minimal'] && $personil['level_pangkat'] < $jabatanBaru['id_pangkat_minimal']) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Pangkat minimal jabatan adalah " . $jabatanBaru['pangkat_minimal_nama'] . " (saat ini: " . $personil['nama_pangkat'] . ")";
        }
        
        // Check eselon compatibility for certain types of mutation
        if ($jabatanBaru['level_eselon']) {
            $currentEselon = $personil['eselon_lama'];
            $targetEselon = $jabatanBaru['level_eselon'];
            
            // Check if this is a promotion (higher eselon)
            if ($currentEselon && $targetEselon) {
                $eselonOrder = ['eselon_5' => 5, 'eselon_4' => 4, 'eselon_3' => 3, 'eselon_2' => 2];
                if (isset($eselonOrder[$currentEselon]) && isset($eselonOrder[$targetEselon])) {
                    if ($eselonOrder[$targetEselon] < $eselonOrder[$currentEselon]) {
                        $eligibility['eligible'] = false;
                        $eligibility['reasons'][] = "Mutasi ke eselon yang lebih tinggi harus melalui proses promosi";
                    }
                }
            }
        }
        
        // Check if already mutated within last 6 months
        $query = "SELECT COUNT(*) FROM riwayat_jabatan 
                 WHERE id_personil = ? AND is_aktif = 1 
                 AND tanggal_mutasi >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $recentMutation = $stmt->fetch_assoc()['COUNT(*)'];
        
        if ($recentMutation > 0) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Sudah ada mutasi dalam 6 bulan terakhir";
        }
        
        return $eligibility;
    }
    
    /**
     * Process mutasi jabatan
     */
    public function processMutasiJabatan($data) {
        $id_personil = $data['id_personil'];
        $id_jabatan_baru = $data['id_jabatan_baru'];
        $tanggal_mutasi = $data['tanggal_mutasi'];
        $no_sk = $data['no_sk_mutasi'];
        $tanggal_sk = $data['tanggal_sk_mutasi'];
        $alasan = $data['alasan_mutasi'];
        $jenis_mutasi = $data['jenis_mutasi'];
        $keterangan = $data['keterangan'] ?? '';
        
        // Check eligibility
        $eligibility = $this->checkMutasiJabatanEligibility($id_personil, $id_jabatan_baru);
        if (!$eligibility['eligible']) {
            throw new Exception("Personil tidak eligible untuk mutasi: " . implode(', ', $eligibility['reasons']));
        }
        
        // Get current assignment
        $query = "SELECT id_jabatan, id_unsur, id_bagian, id_satuan_fungsi, id_unit_pendukung 
                 FROM personil WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $current = $stmt->fetch_assoc();
        
        // Get new jabatan details
        $query = "SELECT id_unsur, id_bagian, id_satuan_fungsi, id_unit_pendukung 
                 FROM jabatan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_jabatan_baru);
        $stmt->execute();
        $new = $stmt->fetch_assoc();
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Create riwayat jabatan
            $query = "INSERT INTO riwayat_jabatan (
                        id_personil, id_jabatan_lama, id_jabatan_baru,
                        id_unsur_lama, id_unsur_baru, id_bagian_lama, id_bagian_baru,
                        id_satuan_fungsi_lama, id_satuan_fungsi_baru,
                        tanggal_mutasi, no_sk_mutasi, tanggal_sk_mutasi,
                        alasan_mutasi, jenis_mutasi, keterangan, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiiiiiiiisssssss', 
                $id_personil, $current['id_jabatan'], $id_jabatan_baru,
                $current['id_unsur'], $new['id_unsur'], $current['id_bagian'], $new['id_bagian'],
                $current['id_satuan_fungsi'], $new['id_satuan_fungsi'],
                $tanggal_mutasi, $no_sk, $tanggal_sk, $alasan, $jenis_mutasi, $keterangan,
                $_SESSION['user_id'] ?? 'system'
            );
            $stmt->execute();
            
            // Update personil jabatan
            $query = "UPDATE personil SET 
                        id_jabatan = ?, id_unsur = ?, id_bagian = ?, id_satuan_fungsi = ?, id_unit_pendukung = ?,
                        updated_by = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiiiisi', 
                $id_jabatan_baru, $new['id_unsur'], $new['id_bagian'], $new['id_satuan_fungsi'], $new['id_unit_pendukung'],
                $_SESSION['user_id'] ?? 'system', $id_personil
            );
            $stmt->execute();
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get available jabatan for mutation
     */
    public function getAvailableJabatan($filters = []) {
        $whereClause = "WHERE j.is_active = 1";
        $params = [];
        
        // Filter by unsur
        if (!empty($filters['id_unsur'])) {
            $whereClause .= " AND j.id_unsur = ?";
            $params[] = $filters['id_unsur'];
        }
        
        // Filter by bagian
        if (!empty($filters['id_bagian'])) {
            $whereClause .= " AND j.id_bagian = ?";
            $params[] = $filters['id_bagian'];
        }
        
        // Filter by satuan fungsi
        if (!empty($filters['id_satuan_fungsi'])) {
            $whereClause .= " AND j.id_satuan_fungsi = ?";
            $params[] = $filters['id_satuan_fungsi'];
        }
        
        $query = "SELECT j.*, msj.level_eselon, msj.nama as status_jabatan,
                        pg.nama_pangkat as pangkat_minimal_nama,
                        u.nama_unsur, b.nama_bagian,
                        msf.nama_satuan_fungsi, mup.nama_unit_pendukung,
                        CASE WHEN p.id IS NOT NULL THEN 'occupied' ELSE 'available' END as status,
                        p.nama as personil_name, p.nrp as personil_nrp
                 FROM jabatan j
                 LEFT JOIN master_status_jabatan msj ON j.id_status_jabatan = msj.id
                 LEFT JOIN pangkat pg ON j.id_pangkat_minimal = pg.id
                 LEFT JOIN unsur u ON j.id_unsur = u.id
                 LEFT JOIN bagian b ON j.id_bagian = b.id
                 LEFT JOIN master_satuan_fungsi msf ON j.id_satuan_fungsi = msf.id
                 LEFT JOIN master_unit_pendukung mup ON j.id_unit_pendukung = mup.id
                 LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_active = 1
                 $whereClause
                 ORDER BY u.urutan, b.urutan, msj.level_eselon, j.nama_jabatan";
        
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
     * Get riwayat karir personil
     */
    public function getRiwayatKarir($id_personil) {
        $riwayat = [];
        
        // Riwayat jabatan
        $query = "SELECT rj.*, 
                        jl.nama_jabatan as jabatan_lama,
                        jb.nama_jabatan as jabatan_baru,
                        ul.nama_unsur as unsur_lama,
                        ub.nama_unsur as unsur_baru,
                        bl.nama_bagian as bagian_lama,
                        bb.nama_bagian as bagian_baru,
                        sfl.nama_satuan_fungsi as satfung_lama,
                        sfb.nama_satuan_fungsi as satfung_baru
                 FROM riwayat_jabatan rj
                 LEFT JOIN jabatan jl ON rj.id_jabatan_lama = jl.id
                 LEFT JOIN jabatan jb ON rj.id_jabatan_baru = jb.id
                 LEFT JOIN unsur ul ON rj.id_unsur_lama = ul.id
                 LEFT JOIN unsur ub ON rj.id_unsur_baru = ub.id
                 LEFT JOIN bagian bl ON rj.id_bagian_lama = bl.id
                 LEFT JOIN bagian bb ON rj.id_bagian_baru = bb.id
                 LEFT JOIN master_satuan_fungsi sfl ON rj.id_satuan_fungsi_lama = sfl.id
                 LEFT JOIN master_satuan_fungsi sfb ON rj.id_satuan_fungsi_baru = sfb.id
                 WHERE rj.id_personil = ? AND rj.is_aktif = 1
                 ORDER BY rj.tanggal_mutasi DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        
        $riwayat['jabatan'] = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayat['jabatan'][] = $row;
        }
        
        // Riwayat pangkat
        $query = "SELECT rp.*, 
                        pl.nama_pangkat as pangkat_lama,
                        pb.nama_pangkat as pangkat_baru
                 FROM riwayat_pangkat rp
                 LEFT JOIN pangkat pl ON rp.id_pangkat_lama = pl.id
                 LEFT JOIN pangkat pb ON rp.id_pangkat_baru = pb.id
                 WHERE rp.id_personil = ? AND rp.is_aktif = 1
                 ORDER BY rp.tanggal_kenaikan_pangkat DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        
        $riwayat['pangkat'] = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayat['pangkat'][] = $row;
        }
        
        // Riwayat penugasan
        $query = "SELECT rp.*, 
                        j.nama_jabatan,
                        mjn.nama as jenis_penugasan,
                        maj.nama as alasan_penugasan
                 FROM riwayat_penugasan rp
                 LEFT JOIN jabatan j ON rp.id_jabatan = j.id
                 LEFT JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                 LEFT JOIN master_alasan_penugasan maj ON rp.id_alasan_penugasan = maj.id
                 WHERE rp.id_personil = ? AND rp.is_aktif = 1
                 ORDER BY rp.tanggal_mulai DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        
        $riwayat['penugasan'] = [];
        while ($row = $stmt->fetch_assoc()) {
            $riwayat['penugasan'][] = $row;
        }
        
        return $riwayat;
    }
    
    /**
     * Get kepegawaian statistics
     */
    public function getKepegawaianStatistics() {
        $stats = [];
        
        // Kenaikan pangkat statistics
        $query = "SELECT 
                    COUNT(*) as total_promotions,
                    COUNT(CASE WHEN jenis_kenaikan = 'reguler' THEN 1 END) as regular,
                    COUNT(CASE WHEN jenis_kenaikan = 'luar_biasa' THEN 1 END) as luar_biasa,
                    COUNT(CASE WHEN jenis_kenaikan = 'penghargaan' THEN 1 END) as penghargaan
                 FROM riwayat_pangkat 
                 WHERE is_aktif = 1 AND YEAR(tanggal_kenaikan_pangkat) = YEAR(CURDATE())";
        $result = $this->db->query($query);
        $stats['kenaikan_pangkat'] = $result->fetch_assoc();
        
        // Mutasi statistics
        $query = "SELECT 
                    jenis_mutasi,
                    COUNT(*) as count
                 FROM riwayat_jabatan 
                 WHERE is_aktif = 1 AND YEAR(tanggal_mutasi) = YEAR(CURDATE())
                 GROUP BY jenis_mutasi";
        $result = $this->db->query($query);
        $stats['mutasi'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['mutasi'][] = $row;
        }
        
        // Personil eligible for promotion
        $query = "SELECT COUNT(*) as eligible_count
                 FROM personil p
                 JOIN jenjang_karir jk ON p.id_pangkat = jk.id_pangkat_saat_ini
                 WHERE p.is_active = 1 
                 AND p.masa_kerja_tahun >= jk.masa_kerja_minimal_tahun
                 AND NOT EXISTS (
                     SELECT 1 FROM riwayat_pangkat rp 
                     WHERE rp.id_personil = p.id 
                     AND rp.is_aktif = 1 
                     AND rp.tanggal_kenaikan_pangkat >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 )";
        $result = $this->db->query($query);
        $stats['eligible_promotion'] = $result->fetch_assoc();
        
        // Jabatan kosong
        $query = "SELECT COUNT(*) as empty_positions
                 FROM jabatan j
                 LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_active = 1
                 WHERE j.is_active = 1 AND p.id IS NULL";
        $result = $this->db->query($query);
        $stats['jabatan_kosong'] = $result->fetch_assoc();
        
        return $stats;
    }
}

// API Handler
try {
    $kepegawaian = new KepegawaianManagementV2();
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    switch ($action) {
        case 'check_kenaikan_pangkat_eligibility':
            $kepegawaian->auth->requireAuth();
            try {
                $id_personil = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0;
                $eligibility = $kepegawaian->checkKenaikanPangkatEligibility($id_personil);
                echo json_encode([
                    'success' => true,
                    'data' => $eligibility
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'process_kenaikan_pangkat':
            $kepegawaian->auth->requireAuth();
            try {
                $data = [
                    'id_personil' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0,
                    'id_pangkat_baru' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat_baru', FILTER_SANITIZE_STRING) ?? 0,
                    'tanggal_kenaikan_pangkat' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_kenaikan_pangkat', FILTER_SANITIZE_STRING) ?? '',
                    'no_sk_kenaikan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_sk_kenaikan', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_sk_kenaikan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_sk_kenaikan', FILTER_SANITIZE_STRING) ?? '',
                    'alasan_kenaikan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_kenaikan', FILTER_SANITIZE_STRING) ?? '',
                    'jenis_kenaikan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jenis_kenaikan', FILTER_SANITIZE_STRING) ?? 'reguler',
                    'keterangan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'keterangan', FILTER_SANITIZE_STRING) ?? ''
                ];
                
                $kepegawaian->processKenaikanPangkat($data);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kenaikan pangkat berhasil diproses'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'check_mutasi_eligibility':
            $kepegawaian->auth->requireAuth();
            try {
                $id_personil = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0;
                $id_jabatan_baru = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan_baru', FILTER_SANITIZE_STRING) ?? 0;
                $eligibility = $kepegawaian->checkMutasiJabatanEligibility($id_personil, $id_jabatan_baru);
                echo json_encode([
                    'success' => true,
                    'data' => $eligibility
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'process_mutasi_jabatan':
            $kepegawaian->auth->requireAuth();
            try {
                $data = [
                    'id_personil' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0,
                    'id_jabatan_baru' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan_baru', FILTER_SANITIZE_STRING) ?? 0,
                    'tanggal_mutasi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_mutasi', FILTER_SANITIZE_STRING) ?? '',
                    'no_sk_mutasi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_sk_mutasi', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_sk_mutasi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_sk_mutasi', FILTER_SANITIZE_STRING) ?? '',
                    'alasan_mutasi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_mutasi', FILTER_SANITIZE_STRING) ?? '',
                    'jenis_mutasi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jenis_mutasi', FILTER_SANITIZE_STRING) ?? '',
                    'keterangan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'keterangan', FILTER_SANITIZE_STRING) ?? ''
                ];
                
                $kepegawaian->processMutasiJabatan($data);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mutasi jabatan berhasil diproses'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'get_available_jabatan':
            $kepegawaian->auth->requireAuth();
            $filters = [
                'id_unsur' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_unsur', FILTER_SANITIZE_STRING) ?? null,
                'id_bagian' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_bagian', FILTER_SANITIZE_STRING) ?? null,
                'id_satuan_fungsi' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_satuan_fungsi', FILTER_SANITIZE_STRING) ?? null
            ];
            $data = $kepegawaian->getAvailableJabatan($filters);
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'get_riwayat_karir':
            $kepegawaian->auth->requireAuth();
            try {
                $id_personil = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0;
                $riwayat = $kepegawaian->getRiwayatKarir($id_personil);
                echo json_encode([
                    'success' => true,
                    'data' => $riwayat
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'get_kepegawaian_statistics':
            $kepegawaian->auth->requireAuth();
            $stats = $kepegawaian->getKepegawaianStatistics();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
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
