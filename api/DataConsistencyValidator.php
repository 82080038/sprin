<?php
declare(strict_types=1);
/**
 * Data Consistency Validator
 * Validates and maintains data consistency across all modules
 */

require_once __DIR__ . '/E2EClient.php';
require_once __DIR__ . '/APIResponse.php';

class DataConsistencyValidator {
    private $pdo;
    private $issues = [];
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Validate all data consistency
     */
    public function validateAll() {
        $this->issues = [];
        
        $this->validatePersonilReferences();
        $this->validateUnsurBagianRelationships();
        $this->validateJabatanConsistency();
        $this->validateDuplicateRecords();
        $this->validateDataIntegrity();
        
        return [
            'success' => empty($this->issues),
            'issues' => $this->issues,
            'summary' => $this->generateSummary(),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Validate personil foreign key references
     */
    private function validatePersonilReferences() {
        // Check unsur references
        $sql = "SELECT p.id, p.nama, p.id_unsur, u.nama_unsur 
                FROM personil p 
                LEFT JOIN unsur u ON p.id_unsur = u.id 
                WHERE p.id_unsur > 0 AND u.id IS NULL AND p.is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orphans as $orphan) {
            $this->issues[] = [
                'type' => 'orphaned_unsur_reference',
                'severity' => 'high',
                'table' => 'personil',
                'record_id' => $orphan['id'],
                'description' => "Personil '{$orphan['nama']}' references non-existent unsur ID: {$orphan['id_unsur']}",
                'suggestion' => 'Update personil unsur_id to valid value or remove reference'
            ];
        }
        
        // Check bagian references
        $sql = "SELECT p.id, p.nama, p.id_bagian, b.nama_bagian 
                FROM personil p 
                LEFT JOIN bagian b ON p.id_bagian = b.id 
                WHERE p.id_bagian > 0 AND b.id IS NULL AND p.is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orphans as $orphan) {
            $this->issues[] = [
                'type' => 'orphaned_bagian_reference',
                'severity' => 'high',
                'table' => 'personil',
                'record_id' => $orphan['id'],
                'description' => "Personil '{$orphan['nama']}' references non-existent bagian ID: {$orphan['id_bagian']}",
                'suggestion' => 'Update personil bagian_id to valid value or remove reference'
            ];
        }
        
        // Check jabatan references
        $sql = "SELECT p.id, p.nama, p.id_jabatan, j.nama_jabatan 
                FROM personil p 
                LEFT JOIN jabatan j ON p.id_jabatan = j.id 
                WHERE p.id_jabatan > 0 AND j.id IS NULL AND p.is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orphans as $orphan) {
            $this->issues[] = [
                'type' => 'orphaned_jabatan_reference',
                'severity' => 'high',
                'table' => 'personil',
                'record_id' => $orphan['id'],
                'description' => "Personil '{$orphan['nama']}' references non-existent jabatan ID: {$orphan['id_jabatan']}",
                'suggestion' => 'Update personil jabatan_id to valid value or remove reference'
            ];
        }
    }
    
    /**
     * Validate unsur-bagian relationships
     */
    private function validateUnsurBagianRelationships() {
        // Check bagian with invalid unsur references
        $sql = "SELECT b.id, b.nama_bagian, b.id_unsur, u.nama_unsur 
                FROM bagian b 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                WHERE b.id_unsur > 0 AND u.id IS NULL";
        
        $stmt = $this->pdo->query($sql);
        $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orphans as $orphan) {
            $this->issues[] = [
                'type' => 'orphaned_unsur_in_bagian',
                'severity' => 'high',
                'table' => 'bagian',
                'record_id' => $orphan['id'],
                'description' => "Bagian '{$orphan['nama_bagian']}' references non-existent unsur ID: {$orphan['id_unsur']}",
                'suggestion' => 'Update bagian unsur_id to valid value'
            ];
        }
        
        // Check for duplicate urutan within same unsur
        $sql = "SELECT b1.id, b1.nama_bagian, b1.id_unsur, b1.urutan, u.nama_unsur
                FROM bagian b1
                JOIN unsur u ON b1.id_unsur = u.id
                JOIN bagian b2 ON b1.id_unsur = b2.id_unsur AND b1.urutan = b2.urutan AND b1.id != b2.id";
        
        $stmt = $this->pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($duplicates as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_urutan_in_unsur',
                'severity' => 'medium',
                'table' => 'bagian',
                'record_id' => $dup['id'],
                'description' => "Bagian '{$dup['nama_bagian']}' has duplicate urutan {$dup['urutan']} in unsur '{$dup['nama_unsur']}'",
                'suggestion' => 'Update urutan to unique value within unsur'
            ];
        }
    }
    
    /**
     * Validate jabatan consistency
     */
    private function validateJabatanConsistency() {
        // Check jabatan with invalid unsur references
        $sql = "SELECT j.id, j.nama_jabatan, j.id_unsur, u.nama_unsur 
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                WHERE j.id_unsur > 0 AND u.id IS NULL";
        
        $stmt = $this->pdo->query($sql);
        $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orphans as $orphan) {
            $this->issues[] = [
                'type' => 'orphaned_unsur_in_jabatan',
                'severity' => 'high',
                'table' => 'jabatan',
                'record_id' => $orphan['id'],
                'description' => "Jabatan '{$orphan['nama_jabatan']}' references non-existent unsur ID: {$orphan['id_unsur']}",
                'suggestion' => 'Update jabatan unsur_id to valid value'
            ];
        }
        
        // Check for duplicate jabatan names within same unsur
        $sql = "SELECT j1.id, j1.nama_jabatan, j1.id_unsur, u.nama_unsur
                FROM jabatan j1
                JOIN unsur u ON j1.id_unsur = u.id
                JOIN jabatan j2 ON j1.id_unsur = j2.id_unsur AND j1.nama_jabatan = j2.nama_jabatan AND j1.id != j2.id";
        
        $stmt = $this->pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($duplicates as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_jabatan_in_unsur',
                'severity' => 'medium',
                'table' => 'jabatan',
                'record_id' => $dup['id'],
                'description' => "Duplicate jabatan name '{$dup['nama_jabatan']}' in unsur '{$dup['nama_unsur']}'",
                'suggestion' => 'Rename jabatan to unique value within unsur'
            ];
        }
    }
    
    /**
     * Validate duplicate records
     */
    private function validateDuplicateRecords() {
        // Check duplicate NRP
        $sql = "SELECT p1.id, p1.nama, p1.nrp
                FROM personil p1
                JOIN personil p2 ON p1.nrp = p2.nrp AND p1.id != p2.id
                WHERE p1.is_deleted = 0 AND p2.is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($duplicates as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_nrp',
                'severity' => 'high',
                'table' => 'personil',
                'record_id' => $dup['id'],
                'description' => "Personil '{$dup['nama']}' has duplicate NRP: {$dup['nrp']}",
                'suggestion' => 'Update NRP to unique value'
            ];
        }
        
        // Check duplicate NIP
        $sql = "SELECT p1.id, p1.nama, p1.nip
                FROM personil p1
                JOIN personil p2 ON p1.nip = p2.nip AND p1.id != p2.id
                WHERE p1.nip IS NOT NULL AND p1.nip != '' AND p1.is_deleted = 0 AND p2.is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($duplicates as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_nip',
                'severity' => 'medium',
                'table' => 'personil',
                'record_id' => $dup['id'],
                'description' => "Personil '{$dup['nama']}' has duplicate NIP: {$dup['nip']}",
                'suggestion' => 'Update NIP to unique value or leave empty'
            ];
        }
    }
    
    /**
     * Validate data integrity
     */
    private function validateDataIntegrity() {
        // Check personil with missing required fields
        $sql = "SELECT id, nama FROM personil 
                WHERE (nama IS NULL OR nama = '' OR nrp IS NULL OR nrp = '') 
                AND is_deleted = 0";
        
        $stmt = $this->pdo->query($sql);
        $invalid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($invalid as $record) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'severity' => 'high',
                'table' => 'personil',
                'record_id' => $record['id'],
                'description' => "Personil ID {$record['id']} missing required fields (nama or nrp)",
                'suggestion' => 'Update personil with required information'
            ];
        }
        
        // Check unsur with missing required fields
        $sql = "SELECT id, nama_unsur FROM unsur 
                WHERE (nama_unsur IS NULL OR nama_unsur = '' OR kode_unsur IS NULL OR kode_unsur = '')";
        
        $stmt = $this->pdo->query($sql);
        $invalid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($invalid as $record) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'severity' => 'high',
                'table' => 'unsur',
                'record_id' => $record['id'],
                'description' => "Unsur ID {$record['id']} missing required fields",
                'suggestion' => 'Update unsur with required information'
            ];
        }
        
        // Check bagian with missing required fields
        $sql = "SELECT id, nama_bagian FROM bagian 
                WHERE (nama_bagian IS NULL OR nama_bagian = '' OR id_unsur IS NULL)";
        
        $stmt = $this->pdo->query($sql);
        $invalid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($invalid as $record) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'severity' => 'high',
                'table' => 'bagian',
                'record_id' => $record['id'],
                'description' => "Bagian ID {$record['id']} missing required fields",
                'suggestion' => 'Update bagian with required information'
            ];
        }
    }
    
    /**
     * Generate summary of validation results
     */
    private function generateSummary() {
        $summary = [
            'total_issues' => count($this->issues),
            'by_severity' => [
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ],
            'by_type' => [],
            'by_table' => []
        ];
        
        foreach ($this->issues as $issue) {
            $severity = $issue['severity'] ?? 'medium';
            $type = $issue['type'] ?? 'unknown';
            $table = $issue['table'] ?? 'unknown';
            
            $summary['by_severity'][$severity]++;
            $summary['by_type'][$type] = ($summary['by_type'][$type] ?? 0) + 1;
            $summary['by_table'][$table] = ($summary['by_table'][$table] ?? 0) + 1;
        }
        
        return $summary;
    }
    
    /**
     * Auto-fix common issues
     */
    public function autoFixIssues() {
        $fixed = [];
        $failed = [];
        
        foreach ($this->issues as $issue) {
            try {
                switch ($issue['type']) {
                    case 'orphaned_unsur_reference':
                    case 'orphaned_bagian_reference':
                    case 'orphaned_jabatan_reference':
                        // Set invalid references to 0
                        $field = $this->getFieldNameFromType($issue['type']);
                        $sql = "UPDATE personil SET {$field} = 0 WHERE id = ?";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$issue['record_id']]);
                        $fixed[] = $issue['record_id'];
                        break;
                        
                    case 'missing_required_fields':
                        // Cannot auto-fix missing required data
                        $failed[] = $issue['record_id'];
                        break;
                        
                    default:
                        $failed[] = $issue['record_id'];
                        break;
                }
            } catch (Exception $e) {
                $failed[] = $issue['record_id'];
            }
        }
        
        return [
            'success' => true,
            'fixed' => $fixed,
            'failed' => $failed,
            'timestamp' => date('c')
        ];
    }
    
    private function getFieldNameFromType($type) {
        $mapping = [
            'orphaned_unsur_reference' => 'id_unsur',
            'orphaned_bagian_reference' => 'id_bagian',
            'orphaned_jabatan_reference' => 'id_jabatan'
        ];
        
        return $mapping[$type] ?? 'id';
    }
}

// API endpoint for data consistency validation
if (isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING)) && filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) === 'validate_consistency') {
    try {
        $validator = new DataConsistencyValidator();
        $result = $validator->validateAll();
        
        if (isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'auto_fix', FILTER_SANITIZE_STRING)) && filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'auto_fix', FILTER_SANITIZE_STRING) === 'true') {
            $fixResult = $validator->autoFixIssues();
            $result['auto_fix'] = $fixResult;
        }
        
        echo json_encode(APIResponse::success($result, 'Data consistency validation completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
