<?php
declare(strict_types=1);
/**
 * API untuk Manajemen Status Penugasan
 * PS, Plt, Pjs, Plh, Pj
 */

require_once '../core/config.php';
require_once 'auth_helper.php';

class PenugasanManager {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->auth = APIAuth::getInstance();
    }
    
    /**
     * Get all jabatan with penugasan status
     */
    public function getJabatanPenugasan() {
        $query = "SELECT 
                    j.id, 
                    j.nama_jabatan, 
                    j.status_penugasan, 
                    j.alasan_penugasan,
                    j.tanggal_mulai_penugasan,
                    j.tanggal_selesai_penugasan,
                    u.nama_unsur,
                    COUNT(p.id) as personil_count
                FROM jabatan j
                LEFT JOIN unsur u ON j.id_unsur = u.id
                LEFT JOIN personil p ON j.id = p.id_jabatan
                GROUP BY j.id, j.nama_jabatan, j.status_penugasan, j.alasan_penugasan, 
                         j.tanggal_mulai_penugasan, j.tanggal_selesai_penugasan, u.nama_unsur
                ORDER BY u.urutan, j.nama_jabatan";
        
        $result = $this->db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'nama_jabatan' => $row['nama_jabatan'],
                'unsur' => $row['nama_unsur'],
                'status_penugasan' => $row['status_penugasan'] ?: 'definitif',
                'alasan_penugasan' => $row['alasan_penugasan'],
                'tanggal_mulai_penugasan' => $row['tanggal_mulai_penugasan'],
                'tanggal_selesai_penugasan' => $row['tanggal_selesai_penugasan'],
                'personil_count' => $row['personil_count'],
                'status' => $this->getPenugasanStatus($row)
            ];
        }
        
        return $data;
    }
    
    /**
     * Update status penugasan jabatan
     */
    public function updateStatusPenugasan($jabatan_id, $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai) {
        // Validate status
        $valid_statuses = ['definitif', 'ps', 'plt', 'pjs', 'plh', 'pj'];
        if (!in_array($status_penugasan, $valid_statuses)) {
            throw new Exception("Invalid status penugasan");
        }
        
        // Validate jabatan
        $jabatan = $this->getJabatanById($jabatan_id);
        if (!$jabatan) {
            throw new Exception("Jabatan not found");
        }
        
        // Validate status rules
        $this->validateStatusRules($jabatan, $status_penugasan);
        
        // Update jabatan
        $query = "UPDATE jabatan SET 
                    status_penugasan = ?, 
                    alasan_penugasan = ?, 
                    tanggal_mulai_penugasan = ?, 
                    tanggal_selesai_penugasan = ?
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssi", $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai, $jabatan_id);
        $stmt->execute();
        
        // Update personil assignment if needed
        $this->updatePersonilPenugasan($jabatan_id, $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai);
        
        return true;
    }
    
    /**
     * Get penugasan statistics
     */
    public function getPenugasanStats() {
        $query = "SELECT 
                    status_penugasan,
                    COUNT(*) as total,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM jabatan), 2) as percentage
                FROM jabatan
                GROUP BY status_penugasan
                ORDER BY total DESC";
        
        $result = $this->db->query($query);
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[] = [
                'status' => $row['status_penugasan'] ?: 'definitif',
                'total' => $row['total'],
                'percentage' => $row['percentage']
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get expired penugasan
     */
    public function getExpiredPenugasan() {
        $query = "SELECT 
                    j.id,
                    j.nama_jabatan,
                    j.status_penugasan,
                    j.tanggal_selesai_penugasan,
                    p.nama as personil_nama,
                    DATEDIFF(CURDATE(), j.tanggal_selesai_penugasan) as days_expired
                FROM jabatan j
                LEFT JOIN personil p ON j.id = p.id_jabatan
                WHERE j.status_penugasan != 'definitif'
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
     * Validate status rules
     */
    private function validateStatusRules($jabatan, $status_penugasan) {
        // PS hanya untuk level Eselon III ke atas
        if ($status_penugasan === 'ps') {
            if (strpos($jabatan['nama_jabatan'], 'KANIT') !== false || 
                strpos($jabatan['nama_jabatan'], 'BINTARA') !== false) {
                throw new Exception("PS tidak boleh untuk jabatan level KANIT ke bawah");
            }
        }
        
        // Pjs hanya untuk level tinggi
        if ($status_penugasan === 'pjs') {
            if (strpos($jabatan['nama_jabatan'], 'KABAG') === false && 
                strpos($jabatan['nama_jabatan'], 'KASAT') === false && 
                strpos($jabatan['nama_jabatan'], 'KAPOLRES') === false) {
                throw new Exception("Pjs hanya untuk jabatan level KABAG ke atas");
            }
        }
        
        // Check PS percentage
        if ($status_penugasan === 'ps') {
            $ps_count = $this->getPSCount();
            $total_jabatan = $this->getTotalJabatan();
            $ps_percentage = ($ps_count / $total_jabatan) * 100;
            
            if ($ps_percentage > 15) {
                throw new Exception("PS tidak boleh lebih dari 15% dari total jabatan");
            }
        }
    }
    
    /**
     * Get penugasan status
     */
    private function getPenugasanStatus($jabatan) {
        if ($jabatan['status_penugasan'] === 'definitif') {
            return 'active';
        }
        
        if (!$jabatan['tanggal_selesai_penugasan']) {
            return 'active';
        }
        
        $today = new DateTime();
        $end_date = new DateTime($jabatan['tanggal_selesai_penugasan']);
        
        if ($end_date < $today) {
            return 'expired';
        }
        
        $diff = $today->diff($end_date);
        if ($diff->days <= 30) {
            return 'expiring_soon';
        }
        
        return 'active';
    }
    
    /**
     * Helper methods
     */
    private function getJabatanById($id) {
        $query = "SELECT * FROM jabatan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getPSCount() {
        $query = "SELECT COUNT(*) as count FROM jabatan WHERE status_penugasan = 'ps'";
        $result = $this->db->query($query);
        return $result->fetch_assoc()['count'];
    }
    
    private function getTotalJabatan() {
        $query = "SELECT COUNT(*) as count FROM jabatan";
        $result = $this->db->query($query);
        return $result->fetch_assoc()['count'];
    }
    
    private function updatePersonilPenugasan($jabatan_id, $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai) {
        $query = "UPDATE personil SET 
                    status_penugasan = ?, 
                    alasan_penugasan = ?, 
                    tanggal_mulai_penugasan = ?, 
                    tanggal_selesai_penugasan = ?
                  WHERE id_jabatan = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssi", $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai, $jabatan_id);
        $stmt->execute();
    }
}

// API Handler
try {
    $penugasan = new PenugasanManager();
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    switch ($action) {
        case 'get_penugasan_list':
            $penugasan->auth->requireAuth();
            $data = $penugasan->getJabatanPenugasan();
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'update_status_penugasan':
            $penugasan->auth->requireAuth();
            
            $jabatan_id = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'jabatan_id', FILTER_SANITIZE_STRING) ?? 0;
            $status_penugasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'status_penugasan', FILTER_SANITIZE_STRING) ?? '';
            $alasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '';
            $tanggal_mulai = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_mulai', FILTER_SANITIZE_STRING) ?? null;
            $tanggal_selesai = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_selesai', FILTER_SANITIZE_STRING) ?? null;
            
            $penugasan->updateStatusPenugasan($jabatan_id, $status_penugasan, $alasan, $tanggal_mulai, $tanggal_selesai);
            
            echo json_encode([
                'success' => true,
                'message' => 'Status penugasan berhasil diupdate'
            ]);
            break;
            
        case 'get_penugasan_stats':
            $penugasan->auth->requireAuth();
            $stats = $penugasan->getPenugasanStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'get_expired_penugasan':
            $penugasan->auth->requireAuth();
            $expired = $penugasan->getExpiredPenugasan();
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
