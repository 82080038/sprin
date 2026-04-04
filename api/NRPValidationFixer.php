<?php
declare(strict_types=1);
/**
 * NRP Format Validator Fixer
 * Update NRP validation to accept both NRP (8-9 digit) and NIP (18 digit) formats
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class NRPValidationFixer {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Update NRP validation logic in all relevant files
     */
    public function fixNRPValidation() {
        $results = [];
        
        try {
            // Update DataQualityAnalyzer
            $results[] = $this->updateDataQualityAnalyzer();
            
            // Verify the fix
            $results[] = $this->verifyNRPValidation();
            
            return [
                'success' => true,
                'message' => 'NRP validation updated to accept NRP and NIP formats',
                'results' => $results,
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating NRP validation: ' . $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * Update DataQualityAnalyzer NRP validation
     */
    private function updateDataQualityAnalyzer() {
        $file_path = __DIR__ . '/DataQualityAnalyzer.php';
        
        // Read current file
        $content = file_get_contents($file_path);
        
        // Update NRP validation regex to accept both NRP (8-9 digits) and NIP (18 digits)
        $old_pattern = "nrp NOT REGEXP '^\[0-9\]\{8,9\}\$'";
        $new_pattern = "nrp NOT REGEXP '^\[0-9\]\{8,9\}\$' AND nrp NOT REGEXP '^\[0-9\]\{18\}\$'";
        
        $content = str_replace($old_pattern, $new_pattern, $content);
        
        // Also update the validation logic description
        $old_desc = "Found 3 personil with invalid NRP format";
        $new_desc = "Found personil with invalid NRP/NIP format";
        
        $content = str_replace($old_desc, $new_desc, $content);
        
        // Write updated file
        file_put_contents($file_path, $content);
        
        return [
            'file' => 'DataQualityAnalyzer.php',
            'action' => 'updated',
            'message' => 'Updated NRP validation to accept 8-9 digit NRP and 18 digit NIP formats'
        ];
    }
    
    /**
     * Verify NRP validation after fix
     */
    public function verifyNRPValidation() {
        // Test the new validation logic
        $sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN nrp REGEXP '^[0-9]{8,9}$' OR nrp REGEXP '^[0-9]{18}$' THEN 1 END) as valid,
                COUNT(CASE WHEN nrp IS NULL OR nrp = '' THEN 1 END) as empty,
                COUNT(CASE WHEN nrp NOT REGEXP '^[0-9]{8,9}$' AND nrp NOT REGEXP '^[0-9]{18}$' AND nrp IS NOT NULL AND nrp != '' THEN 1 END) as invalid
            FROM personil 
            WHERE is_deleted = 0
        ";
        
        $stmt = $this->pdo->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get sample records
        $sample_sql = "
            SELECT id, nama, nrp,
                CASE 
                    WHEN nrp REGEXP '^[0-9]{8,9}$' THEN 'NRP'
                    WHEN nrp REGEXP '^[0-9]{18}$' THEN 'NIP'
                    WHEN nrp IS NULL OR nrp = '' THEN 'empty'
                    ELSE 'invalid'
                END as format_type
            FROM personil 
            WHERE is_deleted = 0
            ORDER BY id
            LIMIT 10
        ";
        
        $stmt = $this->pdo->query($sample_sql);
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'validation_stats' => $stats,
            'sample_records' => $samples,
            'all_valid' => $stats['invalid'] == 0,
            'message' => "NRP validation now accepts both formats: {$stats['valid']} valid, {$stats['invalid']} invalid"
        ];
    }
    
    /**
     * Re-run data quality analysis with updated validation
     */
    public function rerunQualityAnalysis() {
        // Include and run the updated DataQualityAnalyzer
        require_once __DIR__ . '/DataQualityAnalyzer.php';
        
        $analyzer = new DataQualityAnalyzer();
        $result = $analyzer->analyzeQuality();
        
        return [
            'quality_score' => $result['summary']['overall_quality_score'],
            'format_issues' => $result['metrics']['personil']['format_issues'] ?? [],
            'message' => 'Quality analysis completed with updated NRP validation'
        ];
    }
}

// Execute if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $fixer = new NRPValidationFixer();
        
        // Fix NRP validation
        $fix_result = $fixer->fixNRPValidation();
        
        // Verify the fix
        $verify_result = $fixer->verifyNRPValidation();
        
        // Re-run quality analysis
        $quality_result = $fixer->rerunQualityAnalysis();
        
        echo json_encode(APIResponse::success([
            'fix_result' => $fix_result,
            'verification' => $verify_result,
            'quality_analysis' => $quality_result
        ], 'NRP validation fix completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
