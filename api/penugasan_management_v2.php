<?php
declare(strict_types=1);
/**
 * Penugasan Management API v2.0
 * Assignment Management - Definitif, Sementara (PS, Plt, Pjs, Plh, Pj)
 */

require_once '../core/config.php';
require_once 'auth_helper.php';

class PenugasanManagementV2 {
    private $db;
    public $auth;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->auth = APIAuth::getInstance();
    }
    
    /**
     * Check eligibility for penugasan
     */
    public function checkPenugasanEligibility($id_personil, $id_jenis_penugasan, $id_jabatan = null) {
        // Get personil data
        $query = "SELECT p.*, pg.nama_pangkat, pg.level_pangkat,
                        j.nama_jabatan, j.level_eselon,
                        msj.level_eselon as jabatan_eselon
                 FROM personil p
                 JOIN pangkat pg ON p.id_pangkat = pg.id
                 LEFT JOIN jabatan j ON p.id_jabatan = j.id
                 LEFT JOIN master_status_jabatan msj ON j.id_status_jabatan = msj.id
                 WHERE p.id = ? AND p.is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_personil);
        $stmt->execute();
        $personil = $stmt->fetch_assoc();
        
        if (!$personil) {
            throw new Exception("Personil tidak ditemukan");
        }
        
        // Get jenis penugasan data
        $query = "SELECT * FROM master_jenis_penugasan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_jenis_penugasan);
        $stmt->execute();
        $jenisPenugasan = $stmt->fetch_assoc();
        
        if (!$jenisPenugasan) {
            throw new Exception("Jenis penugasan tidak ditemukan");
        }
        
        $eligibility = [
            'eligible' => true,
            'reasons' => [],
            'personil' => $personil,
            'jenis_penugasan' => $jenisPenugasan,
            'jabatan_target' => null
        ];
        
        // Check level requirement
        if ($jenisPenugasan['level_minimal']) {
            $currentEselon = $personil['jabatan_eselon'];
            $requiredLevel = $jenisPenugasan['level_minimal'];
            
            if ($requiredLevel === 'eselon_2' && $currentEselon !== 'eselon_2') {
                $eligibility['eligible'] = false;
                $eligibility['reasons'][] = "Penugasan ini hanya untuk Eselon II";
            } elseif ($requiredLevel === 'eselon_3' && !in_array($currentEselon, ['eselon_2', 'eselon_3'])) {
                $eligibility['eligible'] = false;
                $eligibility['reasons'][] = "Penugasan ini minimal Eselon III";
            }
        }
        
        // Check PS percentage limit
        if ($jenisPenugasan['kode'] === 'PS') {
            $query = "SELECT COUNT(*) as total_ps FROM jabatan j
                     JOIN master_jenis_penugasan mj ON j.id_jenis_penugasan = mj.id
                     WHERE mj.kode = 'PS' AND j.is_active = 1";
            $result = $this->db->query($query);
            $totalPs = $result->fetch_assoc()['total_ps'];
            
            $query = "SELECT COUNT(*) as total_jabatan FROM jabatan WHERE is_active = 1";
            $result = $this->db->query($query);
            $totalJabatan = $result->fetch_assoc()['total_jabatan'];
            
            $psPercentage = ($totalPs / $totalJabatan) * 100;
            
            if ($psPercentage >= 15.0) {
                $eligibility['eligible'] = false;
                $eligibility['reasons'][] = "Persentase PS sudah mencapai " . round($psPercentage, 2) . "% (maksimal 15%)";
            }
        }
        
        // Check duration limit
        if (!empty($jenisPenugasan['durasi_maximal_bulan'])) {
            $eligibility['max_duration'] = $jenisPenugasan['durasi_maximal_bulan'] . " bulan";
        }
        
        // Get target jabatan if specified
        if ($id_jabatan) {
            $query = "SELECT j.*, msj.level_eselon FROM jabatan j
                     JOIN master_status_jabatan msj ON j.id_status_jabatan = msj.id
                     WHERE j.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id_jabatan);
            $stmt->execute();
            $jabatanTarget = $stmt->fetch_assoc();
            
            if ($jabatanTarget) {
                $eligibility['jabatan_target'] = $jabatanTarget;
                
                // Check if jabatan is available
                $query = "SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_active = 1 AND id != ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('ii', $id_jabatan, $id_personil);
                $stmt->execute();
                $isOccupied = $stmt->fetch_assoc()['COUNT(*)'] > 0;
                
                if ($isOccupied) {
                    $eligibility['eligible'] = false;
                    $eligibility['reasons'][] = "Jabatan target sudah diisi";
                }
            }
        }
        
        return $eligibility;
    }
    
    /**
     * Process penugasan assignment
     */
    public function processPenugasan($data) {
        $id_personil = $data['id_personil'];
        $id_jabatan = $data['id_jabatan'];
        $id_jenis_penugasan = $data['id_jenis_penugasan'];
        $id_alasan_penugasan = $data['id_alasan_penugasan'];
        $tanggal_mulai = $data['tanggal_mulai'];
        $tanggal_selesai = $data['tanggal_selesai'] ?? null;
        $no_sk = $data['no_sk_penugasan'] ?? '';
        $tanggal_sk = $data['tanggal_sk_penugasan'] ?? '';
        $keterangan = $data['keterangan'] ?? '';
        
        // Check eligibility
        $eligibility = $this->checkPenugasanEligibility($id_personil, $id_jenis_penugasan, $id_jabatan);
        if (!$eligibility['eligible']) {
            throw new Exception("Personil tidak eligible untuk penugasan: " . implode(', ', $eligibility['reasons']));
        }
        
        // Validate duration
        if ($tanggal_selesai && $tanggal_mulai) {
            $duration = date_diff(date_create($tanggal_mulai), date_create($tanggal_selesai), 'm')->m;
            $maxDuration = $eligibility['jenis_penugasan']['durasi_maximal_bulan'];
            
            if ($maxDuration && $duration > $maxDuration) {
                throw new Exception("Durasi maksimal penugasan adalah " . $maxDuration . " bulan");
            }
        }
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Create riwayat penugasan
            $query = "INSERT INTO riwayat_penugasan (
                        id_personil, id_jabatan, id_jenis_penugasan, id_alasan_penugasan,
                        tanggal_mulai, tanggal_selesai, no_sk_penugasan, tanggal_sk_penugasan,
                        keterangan, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiisssssss', 
                $id_personil, $id_jabatan, $id_jenis_penugasan, $id_alasan_penugasan,
                $tanggal_mulai, $tanggal_selesai, $no_sk, $tanggal_sk, $keterangan,
                $_SESSION['user_id'] ?? 'system'
            );
            $stmt->execute();
            
            // Update personil assignment
            $query = "UPDATE personil SET 
                        id_jabatan = ?, id_jenis_penugasan = ?, id_alasan_penugasan = ?,
                        tanggal_mulai_penugasan = ?, tanggal_selesai_penugasan = ?, keterangan_penugasan = ?,
                        updated_by = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iissssssi', 
                $id_jabatan, $id_jenis_penugasan, $id_alasan_penugasan,
                $tanggal_mulai, $tanggal_selesai, $keterangan,
                $_SESSION['user_id'] ?? 'system', $id_personil
            );
            $stmt->execute();
            
            // Update jabatan jenis penugasan
            $query = "UPDATE jabatan SET id_jenis_penugasan = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $id_jenis_penugasan, $id_jabatan);
            $stmt->execute();
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Extend penugasan
     */
    public function extendPenugasan($id_riwayat, $tanggal_selesai_baru, $alasan = '') {
        // Get current riwayat
        $query = "SELECT * FROM riwayat_penugasan WHERE id = ? AND is_aktif = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_riwayat);
        $stmt->execute();
        $riwayat = $stmt->fetch_assoc();
        
        if (!$riwayat) {
            throw new Exception("Riwayat penugasan tidak ditemukan");
        }
        
        // Check if extension is allowed
        $query = "SELECT durasi_maximal_bulan FROM master_jenis_penugasan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $riwayat['id_jenis_penugasan']);
        $stmt->execute();
        $jenisPenugasan = $stmt->fetch_assoc();
        
        if ($jenisPenugasan['durasi_maximal_bulan']) {
            $totalDuration = date_diff(date_create($riwayat['tanggal_mulai']), date_create($tanggal_selesai_baru), 'm')->m;
            
            if ($totalDuration > $jenisPenugasan['durasi_maximal_bulan']) {
                throw new Exception("Total durasi tidak boleh melebihi " . $jenisPenugasan['durasi_maximal_bulan'] . " bulan");
            }
        }
        
        // Update riwayat
        $query = "UPDATE riwayat_penugasan SET 
                    tanggal_selesai = ?, keterangan = CONCAT(keterangan, '\nExtended: ', ?),
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssi', $tanggal_selesai_baru, $alasan, $id_riwayat);
        $stmt->execute();
        
        // Update personil
        $query = "UPDATE personil SET 
                    tanggal_selesai_penugasan = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si', $tanggal_selesai_baru, $riwayat['id_personil']);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * End penugasan
     */
    public function endPenugasan($id_riwayat, $alasan = '') {
        // Get current riwayat
        $query = "SELECT * FROM riwayat_penugasan WHERE id = ? AND is_aktif = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id_riwayat);
        $stmt->execute();
        $riwayat = $stmt->fetch_assoc();
        
        if (!$riwayat) {
            throw new Exception("Riwayat penugasan tidak ditemukan");
        }
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Update riwayat
            $query = "UPDATE riwayat_penugasan SET 
                        is_aktif = 0, is_expired = 1, keterangan = CONCAT(keterangan, '\nEnded: ', ?),
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $alasan, $id_riwayat);
            $stmt->execute();
            
            // Reset personil assignment
            $query = "UPDATE personil SET 
                        id_jenis_penugasan = NULL, id_alasan_penugasan = NULL,
                        tanggal_mulai_penugasan = NULL, tanggal_selesai_penugasan = NULL, keterangan_penugasan = NULL,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $riwayat['id_personil']);
            $stmt->execute();
            
            // Reset jabatan jenis penugasan
            $query = "UPDATE jabatan SET id_jenis_penugasan = NULL WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $riwayat['id_jabatan']);
            $stmt->execute();
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get active penugasan
     */
    public function getActivePenugasan($filters = []) {
        $whereClause = "WHERE rp.is_aktif = 1";
        $params = [];
        
        // Filter by jenis penugasan
        if (!empty($filters['id_jenis_penugasan'])) {
            $whereClause .= " AND rp.id_jenis_penugasan = ?";
            $params[] = $filters['id_jenis_penugasan'];
        }
        
        // Filter by personil
        if (!empty($filters['id_personil'])) {
            $whereClause .= " AND rp.id_personil = ?";
            $params[] = $filters['id_personil'];
        }
        
        // Filter by expiry status
        if (isset($filters['expired'])) {
            if ($filters['expired']) {
                $whereClause .= " AND (rp.tanggal_selesai < CURDATE() OR rp.tanggal_selesai IS NULL)";
            } else {
                $whereClause .= " AND rp.tanggal_selesai >= CURDATE()";
            }
        }
        
        $query = "SELECT rp.*, 
                        p.nama as personil_nama, p.nrp,
                        j.nama_jabatan,
                        mjn.nama as jenis_penugasan, mjn.kode as kode_penugasan,
                        maj.nama as alasan_penugasan,
                        DATEDIFF(rp.tanggal_selesai, CURDATE()) as hari_sisa,
                        CASE 
                            WHEN rp.tanggal_selesai < CURDATE() THEN 'expired'
                            WHEN rp.tanggal_selesai <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'expiring_soon'
                            ELSE 'active'
                        END as status
                 FROM riwayat_penugasan rp
                 JOIN personil p ON rp.id_personil = p.id
                 JOIN jabatan j ON rp.id_jabatan = j.id
                 JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                 LEFT JOIN master_alasan_penugasan maj ON rp.id_alasan_penugasan = maj.id
                 $whereClause
                 ORDER BY rp.tanggal_selesai ASC, p.nama";
        
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
     * Get penugasan statistics
     */
    public function getPenugasanStatistics() {
        $stats = [];
        
        // Total active penugasan by type
        $query = "SELECT mjn.nama, mjn.kode, COUNT(*) as count
                 FROM riwayat_penugasan rp
                 JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                 WHERE rp.is_aktif = 1
                 GROUP BY mjn.id, mjn.nama, mjn.kode
                 ORDER BY count DESC";
        $result = $this->db->query($query);
        $stats['by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_type'][] = $row;
        }
        
        // PS compliance check
        $query = "SELECT 
                    COUNT(*) as total_ps,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM jabatan WHERE is_active = 1), 2) as percentage
                 FROM jabatan j
                 JOIN master_jenis_penugasan mj ON j.id_jenis_penugasan = mj.id
                 WHERE mj.kode = 'PS' AND j.is_active = 1";
        $result = $this->db->query($query);
        $stats['ps_compliance'] = $result->fetch_assoc();
        
        // Expiring soon (7 days)
        $query = "SELECT COUNT(*) as expiring_soon
                 FROM riwayat_penugasan 
                 WHERE is_aktif = 1 
                 AND tanggal_selesai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $result = $this->db->query($query);
        $stats['expiring_soon'] = $result->fetch_assoc();
        
        // Expired penugasan
        $query = "SELECT COUNT(*) as expired
                 FROM riwayat_penugasan 
                 WHERE is_aktif = 1 
                 AND tanggal_selesai < CURDATE()";
        $result = $this->db->query($query);
        $stats['expired'] = $result->fetch_assoc();
        
        return $stats;
    }
    
    /**
     * Monitor penugasan expiration
     */
    public function monitorExpiration() {
        $monitoring = [];
        
        // Get expiring soon (7 days)
        $query = "SELECT rp.*, p.nama as personil_nama, p.nrp,
                        j.nama_jabatan,
                        mjn.nama as jenis_penugasan,
                        DATEDIFF(rp.tanggal_selesai, CURDATE()) as hari_sisa
                 FROM riwayat_penugasan rp
                 JOIN personil p ON rp.id_personil = p.id
                 JOIN jabatan j ON rp.id_jabatan = j.id
                 JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                 WHERE rp.is_aktif = 1 
                 AND rp.tanggal_selesai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY rp.tanggal_selesai ASC";
        $result = $this->db->query($query);
        $monitoring['expiring_soon'] = [];
        while ($row = $result->fetch_assoc()) {
            $monitoring['expiring_soon'][] = $row;
        }
        
        // Get expired
        $query = "SELECT rp.*, p.nama as personil_nama, p.nrp,
                        j.nama_jabatan,
                        mjn.nama as jenis_penugasan,
                        DATEDIFF(CURDATE(), rp.tanggal_selesai) as hari_terlambat
                 FROM riwayat_penugasan rp
                 JOIN personil p ON rp.id_personil = p.id
                 JOIN jabatan j ON rp.id_jabatan = j.id
                 JOIN master_jenis_penugasan mjn ON rp.id_jenis_penugasan = mjn.id
                 WHERE rp.is_aktif = 1 
                 AND rp.tanggal_selesai < CURDATE()
                 ORDER BY rp.tanggal_selesai DESC";
        $result = $this->db->query($query);
        $monitoring['expired'] = [];
        while ($row = $result->fetch_assoc()) {
            $monitoring['expired'][] = $row;
        }
        
        return $monitoring;
    }
}

// API Handler
try {
    $penugasan = new PenugasanManagementV2();
    $action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
    
    switch ($action) {
        case 'check_penugasan_eligibility':
            $penugasan->auth->requireAuth();
            try {
                $id_personil = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0;
                $id_jenis_penugasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? 0;
                $id_jabatan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? null;
                $eligibility = $penugasan->checkPenugasanEligibility($id_personil, $id_jenis_penugasan, $id_jabatan);
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
            
        case 'process_penugasan':
            $penugasan->auth->requireAuth();
            try {
                $data = [
                    'id_personil' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? 0,
                    'id_jabatan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jabatan', FILTER_SANITIZE_STRING) ?? 0,
                    'id_jenis_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? 0,
                    'id_alasan_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_alasan_penugasan', FILTER_SANITIZE_STRING) ?? 0,
                    'tanggal_mulai' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_mulai', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_selesai' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_selesai', FILTER_SANITIZE_STRING) ?? null,
                    'no_sk_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'no_sk_penugasan', FILTER_SANITIZE_STRING) ?? '',
                    'tanggal_sk_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_sk_penugasan', FILTER_SANITIZE_STRING) ?? '',
                    'keterangan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'keterangan', FILTER_SANITIZE_STRING) ?? ''
                ];
                
                $penugasan->processPenugasan($data);
                echo json_encode([
                    'success' => true,
                    'message' => 'Penugasan berhasil diproses'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'extend_penugasan':
            $penugasan->auth->requireAuth();
            try {
                $id_riwayat = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_riwayat', FILTER_SANITIZE_STRING) ?? 0;
                $tanggal_selesai_baru = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tanggal_selesai_baru', FILTER_SANITIZE_STRING) ?? '';
                $alasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '';
                
                $penugasan->extendPenugasan($id_riwayat, $tanggal_selesai_baru, $alasan);
                echo json_encode([
                    'success' => true,
                    'message' => 'Penugasan berhasil diperpanjang'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'end_penugasan':
            $penugasan->auth->requireAuth();
            try {
                $id_riwayat = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_riwayat', FILTER_SANITIZE_STRING) ?? 0;
                $alasan = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'alasan', FILTER_SANITIZE_STRING) ?? '';
                
                $penugasan->endPenugasan($id_riwayat, $alasan);
                echo json_encode([
                    'success' => true,
                    'message' => 'Penugasan berhasil diakhiri'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'get_active_penugasan':
            $penugasan->auth->requireAuth();
            $filters = [
                'id_jenis_penugasan' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_jenis_penugasan', FILTER_SANITIZE_STRING) ?? null,
                'id_personil' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_personil', FILTER_SANITIZE_STRING) ?? null,
                'expired' => filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'expired', FILTER_SANITIZE_STRING) ?? null
            ];
            $data = $penugasan->getActivePenugasan($filters);
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            break;
            
        case 'get_penugasan_statistics':
            $penugasan->auth->requireAuth();
            $stats = $penugasan->getPenugasanStatistics();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'monitor_expiration':
            $penugasan->auth->requireAuth();
            $monitoring = $penugasan->monitorExpiration();
            echo json_encode([
                'success' => true,
                'data' => $monitoring
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
