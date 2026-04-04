<?php
declare(strict_types=1);
/**
 * Bagian Urutan Fixer
 * Fix duplicate urutan values in bagian table
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class BagianUrutanFixer {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Fix all duplicate urutan in bagian table
     */
    public function fixDuplicateUrutan() {
        $results = [];
        
        try {
            // Get all duplicate urutan groups
            $duplicates = $this->getDuplicateUrutan();
            
            if (empty($duplicates)) {
                return [
                    'success' => true,
                    'message' => 'No duplicate urutan found',
                    'fixed_count' => 0,
                    'timestamp' => date('c')
                ];
            }
            
            $fixed_count = 0;
            foreach ($duplicates as $duplicate) {
                $fix_result = $this->fixDuplicateGroup($duplicate);
                $results[] = $fix_result;
                $fixed_count += $fix_result['fixed_count'];
            }
            
            return [
                'success' => true,
                'message' => "Fixed {$fixed_count} duplicate urutan values",
                'results' => $results,
                'fixed_count' => $fixed_count,
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fixing duplicate urutan: ' . $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * Get all duplicate urutan groups
     */
    private function getDuplicateUrutan() {
        $sql = "
            SELECT 
                id_unsur, 
                urutan, 
                COUNT(*) as duplicate_count,
                GROUP_CONCAT(id ORDER BY id) as bagian_ids,
                GROUP_CONCAT(nama_bagian ORDER BY nama_bagian) as bagian_names
            FROM bagian 
            GROUP BY id_unsur, urutan 
            HAVING COUNT(*) > 1
            ORDER BY id_unsur, urutan
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fix duplicate urutan for a specific group
     */
    private function fixDuplicateGroup($duplicate) {
        $id_unsur = $duplicate['id_unsur'];
        $original_urutan = $duplicate['urutan'];
        $bagian_ids = explode(',', $duplicate['bagian_ids']);
        
        $fixed_count = 0;
        $updates = [];
        
        // Get max urutan for this unsur
        $stmt = $this->pdo->prepare("SELECT MAX(urutan) as max_urutan FROM bagian WHERE id_unsur = ?");
        $stmt->execute([$id_unsur]);
        $max_urutan = $stmt->fetch()['max_urutan'];
        
        // Start transaction
        $this->pdo->beginTransaction();
        
        try {
            // Keep first record with original urutan
            $first_id = array_shift($bagian_ids);
            
            // Update remaining records with new urutan values
            $new_urutan = $max_urutan + 1;
            foreach ($bagian_ids as $bagian_id) {
                $update_sql = "UPDATE bagian SET urutan = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->pdo->prepare($update_sql);
                $result = $stmt->execute([$new_urutan, $bagian_id]);
                
                if ($result) {
                    $updates[] = [
                        'bagian_id' => $bagian_id,
                        'old_urutan' => $original_urutan,
                        'new_urutan' => $new_urutan
                    ];
                    $fixed_count++;
                    $new_urutan++;
                }
            }
            
            $this->pdo->commit();
            
            return [
                'id_unsur' => $id_unsur,
                'original_urutan' => $original_urutan,
                'duplicate_count' => $duplicate['duplicate_count'],
                'fixed_count' => $fixed_count,
                'updates' => $updates,
                'status' => 'success'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            
            return [
                'id_unsur' => $id_unsur,
                'original_urutan' => $original_urutan,
                'duplicate_count' => $duplicate['duplicate_count'],
                'fixed_count' => 0,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
    
    /**
     * Verify urutan uniqueness after fix
     */
    public function verifyUrutanUniqueness() {
        $sql = "
            SELECT 
                id_unsur, 
                urutan, 
                COUNT(*) as count
            FROM bagian 
            GROUP BY id_unsur, urutan 
            HAVING COUNT(*) > 1
        ";
        
        $stmt = $this->pdo->query($sql);
        $remaining_duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => empty($remaining_duplicates),
            'remaining_duplicates' => $remaining_duplicates,
            'is_unique' => empty($remaining_duplicates),
            'timestamp' => date('c')
        ];
    }
}

// Execute if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $fixer = new BagianUrutanFixer();
        
        // Fix duplicates
        $fix_result = $fixer->fixDuplicateUrutan();
        
        // Verify after fix
        $verify_result = $fixer->verifyUrutanUniqueness();
        
        echo json_encode(APIResponse::success([
            'fix_result' => $fix_result,
            'verification' => $verify_result
        ], 'Bagian urutan fix completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
