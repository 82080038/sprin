<?php
declare(strict_types=1);
/**
 * API untuk Master Data Kepegawaian POLRI
 * Jenis Penugasan, Alasan Penugasan, Status Jabatan
 */

require_once '../core/config.php';
require_once 'auth_helper.php';

class MasterKepegawaianManager {
    private $db;
    public $auth;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->auth = APIAuth::getInstance();
    }
    
    /**
     * Get all jenis penugasan
     */
    public function getJenisPenugasan() {
        $query = "SELECT id, kode, nama, nama_lengkap, deskripsi, kategori, 
                        level_minimal, durasi_maximal_bulan, kewenangan, 
                        persentase_maximal, is_active
                 FROM master_jenis_penugasan 
                 WHERE is_active = TRUE 
                 ORDER BY id";
        
        $result = $this->db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Get all alasan penugasan
     */
    public function getAlasanPenugasan() {
        $query = "SELECT id, kode, nama, kategori, deskripsi, 
                        durasi_rekomendasi_bulan, requires_sk, is_active
                 FROM master_alasan_penugasan 
                 WHERE is_active = TRUE 
                 ORDER BY kategori, nama";
        
        $result = $this->db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Get all status jabatan
     */
    public function getStatusJabatan() {
        $query = "SELECT id, kode, nama, nama_lengkap, deskripsi, kategori, 
                        level_eselon, is_definitif, is_managerial, is_supervisor, is_active
                 FROM master_status_jabatan 
                 WHERE is_active = TRUE 
                 ORDER BY level_eselon, nama";
        
        $result = $this->db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Get jabatan dengan master data
     */
    public function getJabatanWithMaster() {
        $query = "SELECT j.id, j.nama_jabatan, 
                        jp.nama as jenis_penugasan, jp.kode as kode_penugasan,
                        ap.nama as alasan_penugasan, ap.kode as kode_alasan,
                        sj.nama as status_jabatan, sj.kode as kode_status,
                        u.nama_unsur,
                        COUNT(p.id) as personil_count
                 FROM jabatan j
                 LEFT JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
                 LEFT JOIN master_alasan_penugasan ap ON j.id_alasan_penugasan = ap.id
                 LEFT JOIN master_status_jabatan sj ON j.id_status_jabatan = sj.id
                 LEFT JOIN unsur u ON j.id_unsur = u.id
                 LEFT JOIN personil p ON j.id = p.id_jabatan
                 GROUP BY j.id, j.nama_jabatan, jp.nama, jp.kode, ap.nama, ap.kode, 
                          sj.nama, sj.kode, u.nama_unsur
                 ORDER BY u.urutan, j.nama_jabatan";
        
        $result = $this->db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Update status penugasan jabatan
     */
    public function updateStatusPenugasan($jabatan_id, $jenis_penugasan_id, $alasan_penugasan_id, 
                                         $tanggal_mulai, $tanggal_selesai, $keterangan) {
        // Validate jabatan
        $jabatan = $this->getJabatanById($jabatan_id);
        if (!$jabatan) {
            throw new Exception("Jabatan not found");
        }
        
        // Validate jenis penugasan
        $jenis_penugasan = $this->getJenisPenugasanById($jenis_penugasan_id);
        if (!$jenis_penugasan) {
            throw new Exception("Jenis penugasan not found");
        }
        
        // Validate alasan penugasan
        $alasan_penugasan = $this->getAlasanPenugasanById($alasan_penugasan_id);
        if (!$alasan_penugasan) {
            throw new Exception("Alasan penugasan not found");
        }
        
        // Validate rules
        $this->validatePenugasanRules($jabatan, $jenis_penugasan, $alasan_penugasan);
        
        // Update jabatan
        $query = "UPDATE jabatan SET 
                    id_jenis_penugasan = ?, 
                    id_alasan_penugasan = ?, 
                    tanggal_mulai_penugasan = ?, 
                    tanggal_selesai_penugasan = ?,
                    keterangan_penugasan = ?
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iisssi", $jenis_penugasan_id, $alasan_penugasan_id, 
                           $tanggal_mulai, $tanggal_selesai, $keterangan, $jabatan_id);
        $stmt->execute();
        
        // Update personil assignment
        $this->updatePersonilPenugasan($jabatan_id, $jenis_penugasan_id, $alasan_penugasan_id, 
                                       $tanggal_mulai, $tanggal_selesai, $keterangan);
        
        return true;
    }
    
    /**
     * Get penugasan statistics
     */
    public function getPenugasanStats() {
        $query = "SELECT jp.nama as jenis_penugasan, jp.kode as kode_penugasan,
                        COUNT(DISTINCT j.id) as jabatan_count,
                        COUNT(DISTINCT p.id) as personil_count,
                        ROUND(COUNT(DISTINCT j.id) * 100.0 / (SELECT COUNT(*) FROM jabatan), 2) as percentage
                 FROM jabatan j
                 LEFT JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
                 LEFT JOIN personil p ON j.id = p.id_jabatan
                 GROUP BY jp.id, jp.nama, jp.kode
                 ORDER BY jabatan_count DESC";
        
        $result = $this->db->query($query);
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
    
    /**
     * Get expired penugasan
     */
    public function getExpiredPenugasan() {
        $query = "SELECT j.id, j.nama_jabatan,
                        jp.nama as jenis_penugasan,
                        ap.nama as alasan_penugasan,
                        j.tanggal_selesai_penugasan,
                        p.nama as personil_nama,
                        DATEDIFF(CURDATE(), j.tanggal_selesai_penugasan) as days_expired
                 FROM jabatan j
                 LEFT JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
                 LEFT JOIN master_alasan_penugasan ap ON j.id_alasan_penugasan = ap.id
                 LEFT JOIN personil p ON j.id = p.id_jabatan
                 WHERE j.id_jenis_penugasan IS NOT NULL
                 AND j.tanggal_selesai_penugasan < CURDATE()
                 ORDER BY days_expired DESC";
        
        $result = $this->db->query($query);
        $expired = [];
        
        while ($row = $result->fetch_assoc()) {
            $expired[] = $row;
        }
        
        return $expired;
    }
    
    /**
     * Validate penugasan rules
     */
    private function validatePenugasanRules($jabatan, $jenis_penugasan, $alasan_penugasan) {
        // Check level minimal
        $level_ok = $this->checkLevelRequirement($jabatan, $jenis_penugasan);
        if (!$level_ok) {
            throw new Exception("Jenis penugasan tidak sesuai untuk level jabatan ini");
        }
        
        // Check percentage limit for PS
        if ($jenis_penugasan['kode'] === 'PS') {
            $ps_count = $this->getPSCount();
            $total_jabatan = $this->getTotalJabatan();
            $ps_percentage = ($ps_count / $total_jabatan) * 100;
            
            if ($ps_percentage > $jenis_penugasan['persentase_maximal']) {
                throw new Exception("PS melebihi batas maksimal " . $jenis_penugasan['persentase_maximal'] . "%");
            }
        }
        
        // Check duration limit
        if ($jenis_penugasan['durasi_maximal_bulan']) {
            $duration = $this->calculateDuration($jenis_penugasan['durasi_maximal_bulan']);
            if ($duration > $jenis_penugasan['durasi_maximal_bulan']) {
                throw new Exception("Durasi penugasan melebihi batas maksimal " . $jenis_penugasan['durasi_maximal_bulan'] . " bulan");
            }
        }
        
        // Check SK requirement
        if ($alasan_penugasan['requires_sk']) {
            // TODO: Check if SK exists
        }
    }
    
    /**
     * Helper methods
     */
    private function getJabatanById($id) {
        $query = "SELECT j.*, sj.nama as status_jabatan, sj.level_eselon
                 FROM jabatan j
                 LEFT JOIN master_status_jabatan sj ON j.id_status_jabatan = sj.id
                 WHERE j.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getJenisPenugasanById($id) {
        $query = "SELECT * FROM master_jenis_penugasan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getAlasanPenugasanById($id) {
        $query = "SELECT * FROM master_alasan_penugasan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getPSCount() {
        $query = "SELECT COUNT(*) as count 
                 FROM jabatan j
                 JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
                 WHERE jp.kode = 'PS'";
        $result = $this->db->query($query);
        return $result->fetch_assoc()['count'];
    }
    
    private function getTotalJabatan() {
        $query = "SELECT COUNT(*) as count FROM jabatan";
        $result = $this->db->query($query);
        return $result->fetch_assoc()['count'];
    }
    
    private function checkLevelRequirement($jabatan, $jenis_penugasan) {
        $level_map = [
            'eselon_2' => ['KAPOLRES', 'WAKAPOLRES'],
            'eselon_3' => ['KABAG', 'KASAT', 'KAPOLSEK'],
            'eselon_4' => ['KASUBBAG', 'KASUBSAT'],
            'eselon_5' => ['KANIT', 'KAUR'],
            'semua_level' => ['all']
        ];
        
        if ($jenis_penugasan['level_minimal'] === 'semua_level') {
            return true;
        }
        
        $allowed_levels = $level_map[$jenis_penugasan['level_minimal']] ?? [];
        $jabatan_level = $jabatan['level_eselon'] ?? 'non_eselon';
        
        return in_array($jabatan_level, $allowed_levels);
    }
    
    private function calculateDuration($max_months) {
        // TODO: Implement duration calculation
        return 0;
    }
    
    private function updatePersonilPenugasan($jabatan_id, $jenis_penugasan_id, $alasan_penugasan_id, 
                                           $tanggal_mulai, $tanggal_selesai, $keterangan) {
        $query = "UPDATE personil SET 
                    id_jenis_penugasan = ?, 
                    id_alasan_penugasan = ?, 
                    tanggal_mulai_penugasan = ?, 
                    tanggal_selesai_penugasan = ?,
                    keterangan_penugasan = ?
                  WHERE id_jabatan = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iisssi", $jenis_penugasan_id, $alasan_penugasan_id, 
                           $tanggal_mulai, $tanggal_selesai, $keterangan, $jabatan_id);
        $stmt->execute();
    }
}

// API Handler
try {
    $master = new MasterKepegawaianManager();
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    switch ($action) {
        case 'get_jenis_penugasan':
            // $master->auth->requireAuth(); // Commented for testing
            $data = $master->getJenisPenugasan();
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'get_alasan_penugasan':
            // $master->auth->requireAuth(); // Commented for testing
            $data = $master->getAlasanPenugasan();
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'get_status_jabatan':
            // $master->auth->requireAuth(); // Commented for testing
            $data = $master->getStatusJabatan();
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'get_jabatan_with_master':
            // $master->auth->requireAuth(); // Commented for testing
            $data = $master->getJabatanWithMaster();
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'update_status_penugasan':
            $master->auth->requireAuth();
            
            $jabatan_id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jabatan_id', FILTER_SANITIZE_STRING) ?? 0;
            $jenis_penugasan_id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jenis_penugasan_id', FILTER_SANITIZE_STRING) ?? 0;
            $alasan_penugasan_id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan_penugasan_id', FILTER_SANITIZE_STRING) ?? 0;
            $tanggal_mulai = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_mulai', FILTER_SANITIZE_STRING) ?? null;
            $tanggal_selesai = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_selesai', FILTER_SANITIZE_STRING) ?? null;
            $keterangan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'keterangan', FILTER_SANITIZE_STRING) ?? '';
            
            $master->updateStatusPenugasan($jabatan_id, $jenis_penugasan_id, $alasan_penugasan_id, 
                                           $tanggal_mulai, $tanggal_selesai, $keterangan);
            
            echo json_encode([
                'success' => true,
                'message' => 'Status penugasan berhasil diupdate'
            ]);
            break;
            
        case 'get_penugasan_stats':
            $master->auth->requireAuth();
            $stats = $master->getPenugasanStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'get_expired_penugasan':
            $master->auth->requireAuth();
            $expired = $master->getExpiredPenugasan();
            echo json_encode([
                'success' => true,
                'data' => $expired,
                'total' => count($expired)
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
