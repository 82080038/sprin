<?php
declare(strict_types=1);
/**
 * Manual NRP Format Fixer
 * Fix specific NRP records that need manual intervention
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class ManualNRPFixer {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Fix specific problematic NRP records
     */
    public function fixSpecificNRP() {
        $fixes = [
            [
                'id' => 282,
                'nama' => 'REYMESTA AMBARITA, S.Kom.',
                'current_nrp' => '198111252014122004',
                'proposed_nrp' => '84112520' // Extract from NIP: 1981-11-25
            ],
            [
                'id' => 305,
                'nama' => 'NENENG GUSNIARTI',
                'current_nrp' => '197008291993032002',
                'proposed_nrp' => '70082920' // Extract from NIP: 1970-08-29
            ],
            [
                'id' => 511,
                'nama' => 'FERNANDO SILALAHI, A.Md.',
                'current_nrp' => '198112262024211002',
                'proposed_nrp' => '81122620' // Extract from NIP: 1981-12-26
            ]
        ];
        
        $results = [];
        $fixed_count = 0;
        
        foreach ($fixes as $fix) {
            $result = $this->applyManualFix($fix);
            $results[] = $result;
            
            if ($result['status'] === 'fixed') {
                $fixed_count++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Manually fixed {$fixed_count} NRP records",
            'results' => $results,
            'fixed_count' => $fixed_count,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Apply manual fix for specific record
     */
    private function applyManualFix($fix) {
        $id = $fix['id'];
        $current_nrp = $fix['current_nrp'];
        $proposed_nrp = $fix['proposed_nrp'];
        
        // Check if proposed NRP already exists
        if ($this->nrpExists($proposed_nrp, $id)) {
            return [
                'id' => $id,
                'nama' => $fix['nama'],
                'current_nrp' => $current_nrp,
                'proposed_nrp' => $proposed_nrp,
                'status' => 'conflict',
                'message' => 'Proposed NRP conflicts with existing record'
            ];
        }
        
        try {
            $update_sql = "UPDATE personil SET nrp = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($update_sql);
            $result = $stmt->execute([$proposed_nrp, $id]);
            
            if ($result) {
                return [
                    'id' => $id,
                    'nama' => $fix['nama'],
                    'old_nrp' => $current_nrp,
                    'new_nrp' => $proposed_nrp,
                    'status' => 'fixed',
                    'message' => 'NRP manually corrected successfully'
                ];
            } else {
                return [
                    'id' => $id,
                    'nama' => $fix['nama'],
                    'current_nrp' => $current_nrp,
                    'status' => 'error',
                    'message' => 'Failed to update NRP'
                ];
            }
        } catch (Exception $e) {
            return [
                'id' => $id,
                'nama' => $fix['nama'],
                'current_nrp' => $current_nrp,
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
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
     * Verify final NRP format status
     */
    public function verifyFinalNRPStatus() {
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
        
        return [
            'success' => $total_invalid === 0,
            'total_invalid' => (int)$total_invalid,
            'all_valid' => $total_invalid === 0,
            'timestamp' => date('c')
        ];
    }
}

// Execute if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $fixer = new ManualNRPFixer();
        
        // Apply manual fixes
        $fix_result = $fixer->fixSpecificNRP();
        
        // Verify final status
        $verify_result = $fixer->verifyFinalNRPStatus();
        
        echo json_encode(APIResponse::success([
            'fix_result' => $fix_result,
            'verification' => $verify_result
        ], 'Manual NRP fix completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
