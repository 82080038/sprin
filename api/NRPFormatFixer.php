<?php
declare(strict_types=1);
/**
 * NRP Format Fixer
 * Correct invalid NRP formats in personil table
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class NRPFormatFixer {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Fix all invalid NRP formats
     */
    public function fixInvalidNRP() {
        $results = [];
        
        try {
            // Get all invalid NRP records
            $invalid_nrps = $this->getInvalidNRP();
            
            if (empty($invalid_nrps)) {
                return [
                    'success' => true,
                    'message' => 'No invalid NRP formats found',
                    'fixed_count' => 0,
                    'timestamp' => date('c')
                ];
            }
            
            $fixed_count = 0;
            foreach ($invalid_nrps as $invalid_nrp) {
                $fix_result = $this->fixSingleNRP($invalid_nrp);
                $results[] = $fix_result;
                if ($fix_result['status'] === 'fixed') {
                    $fixed_count++;
                }
            }
            
            return [
                'success' => true,
                'message' => "Fixed {$fixed_count} invalid NRP formats",
                'results' => $results,
                'fixed_count' => $fixed_count,
                'total_invalid' => count($invalid_nrps),
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fixing NRP formats: ' . $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * Get all invalid NRP records
     */
    private function getInvalidNRP() {
        $sql = "
            SELECT id, nama, nrp 
            FROM personil 
            WHERE is_deleted = 0 
            AND nrp IS NOT NULL 
            AND nrp != '' 
            AND nrp NOT REGEXP '^[0-9]{8,9}$'
            ORDER BY id
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fix single NRP record
     */
    private function fixSingleNRP($personil) {
        $id = $personil['id'];
        $nama = $personil['nama'];
        $current_nrp = $personil['nrp'];
        
        // Try to fix NRP format
        $fixed_nrp = $this->normalizeNRP($current_nrp);
        
        if ($fixed_nrp === $current_nrp) {
            return [
                'id' => $id,
                'nama' => $nama,
                'current_nrp' => $current_nrp,
                'status' => 'unchanged',
                'message' => 'NRP format already correct or cannot be fixed automatically'
            ];
        }
        
        // Check if fixed NRP already exists
        if ($this->nrpExists($fixed_nrp, $id)) {
            return [
                'id' => $id,
                'nama' => $nama,
                'current_nrp' => $current_nrp,
                'proposed_nrp' => $fixed_nrp,
                'status' => 'conflict',
                'message' => 'Fixed NRP conflicts with existing record'
            ];
        }
        
        // Update the record
        try {
            $update_sql = "UPDATE personil SET nrp = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($update_sql);
            $result = $stmt->execute([$fixed_nrp, $id]);
            
            if ($result) {
                return [
                    'id' => $id,
                    'nama' => $nama,
                    'old_nrp' => $current_nrp,
                    'new_nrp' => $fixed_nrp,
                    'status' => 'fixed',
                    'message' => 'NRP format corrected successfully'
                ];
            } else {
                return [
                    'id' => $id,
                    'nama' => $nama,
                    'current_nrp' => $current_nrp,
                    'status' => 'error',
                    'message' => 'Failed to update NRP'
                ];
            }
        } catch (Exception $e) {
            return [
                'id' => $id,
                'nama' => $nama,
                'current_nrp' => $current_nrp,
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Normalize NRP format
     */
    private function normalizeNRP($nrp) {
        // Remove all non-digit characters
        $digits_only = preg_replace('/[^0-9]/', '', $nrp);
        
        // If empty after cleaning, return original
        if (empty($digits_only)) {
            return $nrp;
        }
        
        // If too short or too long, return original
        if (strlen($digits_only) < 6 || strlen($digits_only) > 10) {
            return $nrp;
        }
        
        // Pad with leading zeros if needed to make 8 digits
        if (strlen($digits_only) < 8) {
            $digits_only = str_pad($digits_only, 8, '0', STR_PAD_LEFT);
        }
        
        // If more than 8 digits, truncate to 8 (for cases like 9 digits)
        if (strlen($digits_only) > 8) {
            $digits_only = substr($digits_only, 0, 8);
        }
        
        return $digits_only;
    }
    
    /**
     * Check if NRP already exists (excluding current record)
     */
    private function nrpExists($nrp, $exclude_id) {
        $sql = "SELECT COUNT(*) as count FROM personil WHERE nrp = ? AND id != ? AND is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nrp, $exclude_id]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Verify NRP format consistency after fix
     */
    public function verifyNRPFormats() {
        $sql = "
            SELECT COUNT(*) as total_invalid
            FROM personil 
            WHERE is_deleted = 0 
            AND nrp IS NOT NULL 
            AND nrp != '' 
            AND nrp NOT REGEXP '^[0-9]{8,9}$'
        ";
        
        $stmt = $this->pdo->query($sql);
        $total_invalid = $stmt->fetch()['total_invalid'];
        
        // Get format statistics
        $stats_sql = "
            SELECT 
                CASE 
                    WHEN nrp REGEXP '^[0-9]{8,9}$' THEN 'valid'
                    WHEN nrp IS NULL OR nrp = '' THEN 'empty'
                    ELSE 'invalid'
                END as format_status,
                COUNT(*) as count
            FROM personil 
            WHERE is_deleted = 0
            GROUP BY format_status
            ORDER BY format_status
        ";
        
        $stmt = $this->pdo->query($stats_sql);
        $format_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => $total_invalid === 0,
            'total_invalid' => (int)$total_invalid,
            'format_statistics' => $format_stats,
            'all_valid' => $total_invalid === 0,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Get detailed NRP analysis
     */
    public function getNRPAnalysis() {
        $analysis = [];
        
        // Get all NRP patterns
        $pattern_sql = "
            SELECT 
                nrp,
                CASE 
                    WHEN nrp REGEXP '^[0-9]{8,9}$' THEN 'valid'
                    WHEN nrp IS NULL OR nrp = '' THEN 'empty'
                    ELSE 'invalid'
                END as format_status,
                LENGTH(nrp) as length,
                nrp REGEXP '^[0-9]+$' as digits_only
            FROM personil 
            WHERE is_deleted = 0
            ORDER BY id
            LIMIT 20
        ";
        
        $stmt = $this->pdo->query($pattern_sql);
        $analysis['samples'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get summary statistics
        $summary_sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN nrp REGEXP '^[0-9]{8,9}$' THEN 1 END) as valid,
                COUNT(CASE WHEN nrp IS NULL OR nrp = '' THEN 1 END) as empty,
                COUNT(CASE WHEN nrp NOT REGEXP '^[0-9]{8,9}$' AND nrp IS NOT NULL AND nrp != '' THEN 1 END) as invalid
            FROM personil 
            WHERE is_deleted = 0
        ";
        
        $stmt = $this->pdo->query($summary_sql);
        $analysis['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $analysis;
    }
}

// Execute if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $fixer = new NRPFormatFixer();
        
        // Get analysis before fix
        $analysis = $fixer->getNRPAnalysis();
        
        // Fix invalid NRP
        $fix_result = $fixer->fixInvalidNRP();
        
        // Verify after fix
        $verify_result = $fixer->verifyNRPFormats();
        
        echo json_encode(APIResponse::success([
            'analysis' => $analysis,
            'fix_result' => $fix_result,
            'verification' => $verify_result
        ], 'NRP format fix completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
