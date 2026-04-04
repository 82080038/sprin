<?php
declare(strict_types=1);
/**
 * Data Integrity Validator
 * Comprehensive data validation and consistency checks
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

class DataIntegrityValidator {
    private $pdo;
    private $issues = [];
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Validate all data integrity
     */
    public function validateAll() {
        $this->issues = [];
        
        $this->validatePersonilIntegrity();
        $this->validateUnsurIntegrity();
        $this->validateBagianIntegrity();
        $this->validateJabatanIntegrity();
        $this->validateRelationships();
        $this->validateDataConsistency();
        $this->validateBusinessRules();
        
        return $this->generateReport();
    }
    
    /**
     * Validate personil data integrity
     */
    private function validatePersonilIntegrity() {
        // Check for missing required fields
        $stmt = $this->pdo->query("
            SELECT id, nama, nrp 
            FROM personil 
            WHERE is_deleted = 0 
            AND (nama IS NULL OR nama = '' OR nrp IS NULL OR nrp = '')
        ");
        $missing_required = $stmt->fetchAll();
        
        foreach ($missing_required as $person) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} missing required fields (nama or nrp)",
                'severity' => 'high'
            ];
        }
        
        // Check for duplicate NRP
        $stmt = $this->pdo->query("
            SELECT nrp, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM personil 
            WHERE is_deleted = 0 AND nrp IS NOT NULL AND nrp != ''
            GROUP BY nrp 
            HAVING count > 1
        ");
        $duplicate_nrps = $stmt->fetchAll();
        
        foreach ($duplicate_nrps as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_nrp',
                'table' => 'personil',
                'description' => "Duplicate NRP '{$dup['nrp']}' found in records: {$dup['ids']}",
                'severity' => 'high',
                'count' => $dup['count']
            ];
        }
        
        // Check for invalid dates
        $stmt = $this->pdo->query("
            SELECT id, nama, tanggal_lahir, tanggal_masuk, tanggal_pensiun
            FROM personil 
            WHERE is_deleted = 0 
            AND (
                (tanggal_lahir IS NOT NULL AND tanggal_lahir != '' AND (tanggal_lahir > CURDATE() OR tanggal_lahir < '1900-01-01'))
                OR (tanggal_masuk IS NOT NULL AND tanggal_masuk != '' AND (tanggal_masuk > CURDATE() OR tanggal_masuk < '1950-01-01'))
                OR (tanggal_pensiun IS NOT NULL AND tanggal_pensiun != '' AND (tanggal_pensiun > CURDATE() OR tanggal_pensiun < '1950-01-01'))
            )
        ");
        $invalid_dates = $stmt->fetchAll();
        
        foreach ($invalid_dates as $person) {
            $this->issues[] = [
                'type' => 'invalid_date',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} has invalid dates",
                'severity' => 'medium',
                'details' => $person
            ];
        }
        
        // Check logical date consistency
        $stmt = $this->pdo->query("
            SELECT id, nama, tanggal_lahir, tanggal_masuk, tanggal_pensiun
            FROM personil 
            WHERE is_deleted = 0 
            AND (
                (tanggal_lahir IS NOT NULL AND tanggal_masuk IS NOT NULL AND tanggal_lahir > tanggal_masuk)
                OR (tanggal_masuk IS NOT NULL AND tanggal_pensiun IS NOT NULL AND tanggal_masuk > tanggal_pensiun)
                OR (tanggal_lahir IS NOT NULL AND tanggal_pensiun IS NOT NULL AND tanggal_lahir > tanggal_pensiun)
            )
        ");
        $inconsistent_dates = $stmt->fetchAll();
        
        foreach ($inconsistent_dates as $person) {
            $this->issues[] = [
                'type' => 'inconsistent_dates',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} has inconsistent date logic",
                'severity' => 'medium',
                'details' => $person
            ];
        }
    }
    
    /**
     * Validate unsur data integrity
     */
    private function validateUnsurIntegrity() {
        // Check for missing required fields
        $stmt = $this->pdo->query("
            SELECT id, nama_unsur, kode_unsur
            FROM unsur 
            WHERE nama_unsur IS NULL OR nama_unsur = '' OR kode_unsur IS NULL OR kode_unsur = ''
        ");
        $missing_required = $stmt->fetchAll();
        
        foreach ($missing_required as $unsur) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'table' => 'unsur',
                'record_id' => $unsur['id'],
                'description' => "Unsur ID {$unsur['id']} missing required fields",
                'severity' => 'high'
            ];
        }
        
        // Check for duplicate kode_unsur
        $stmt = $this->pdo->query("
            SELECT kode_unsur, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM unsur 
            GROUP BY kode_unsur 
            HAVING count > 1
        ");
        $duplicate_kodes = $stmt->fetchAll();
        
        foreach ($duplicate_kodes as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_kode_unsur',
                'table' => 'unsur',
                'description' => "Duplicate kode_unsur '{$dup['kode_unsur']}' found in records: {$dup['ids']}",
                'severity' => 'high',
                'count' => $dup['count']
            ];
        }
        
        // Check for duplicate urutan
        $stmt = $this->pdo->query("
            SELECT urutan, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM unsur 
            GROUP BY urutan 
            HAVING count > 1
        ");
        $duplicate_urutan = $stmt->fetchAll();
        
        foreach ($duplicate_urutan as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_urutan',
                'table' => 'unsur',
                'description' => "Duplicate urutan '{$dup['urutan']}' found in records: {$dup['ids']}",
                'severity' => 'medium',
                'count' => $dup['count']
            ];
        }
    }
    
    /**
     * Validate bagian data integrity
     */
    private function validateBagianIntegrity() {
        // Check for missing required fields
        $stmt = $this->pdo->query("
            SELECT id, nama_bagian, id_unsur
            FROM bagian 
            WHERE nama_bagian IS NULL OR nama_bagian = '' OR id_unsur IS NULL OR id_unsur = 0
        ");
        $missing_required = $stmt->fetchAll();
        
        foreach ($missing_required as $bagian) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'table' => 'bagian',
                'record_id' => $bagian['id'],
                'description' => "Bagian ID {$bagian['id']} missing required fields",
                'severity' => 'high'
            ];
        }
        
        // Check for duplicate nama_bagian within same unsur
        $stmt = $this->pdo->query("
            SELECT id_unsur, nama_bagian, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM bagian 
            GROUP BY id_unsur, nama_bagian 
            HAVING count > 1
        ");
        $duplicate_names = $stmt->fetchAll();
        
        foreach ($duplicate_names as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_bagian_name',
                'table' => 'bagian',
                'description' => "Duplicate bagian name '{$dup['nama_bagian']}' in unsur {$dup['id_unsur']} found in records: {$dup['ids']}",
                'severity' => 'medium',
                'count' => $dup['count']
            ];
        }
        
        // Check for duplicate urutan within same unsur
        $stmt = $this->pdo->query("
            SELECT id_unsur, urutan, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM bagian 
            GROUP BY id_unsur, urutan 
            HAVING count > 1
        ");
        $duplicate_urutan = $stmt->fetchAll();
        
        foreach ($duplicate_urutan as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_bagian_urutan',
                'table' => 'bagian',
                'description' => "Duplicate urutan '{$dup['urutan']}' in unsur {$dup['id_unsur']} found in records: {$dup['ids']}",
                'severity' => 'medium',
                'count' => $dup['count']
            ];
        }
    }
    
    /**
     * Validate jabatan data integrity
     */
    private function validateJabatanIntegrity() {
        // Check for missing required fields
        $stmt = $this->pdo->query("
            SELECT id, nama_jabatan, id_unsur
            FROM jabatan 
            WHERE nama_jabatan IS NULL OR nama_jabatan = '' OR id_unsur IS NULL OR id_unsur = 0
        ");
        $missing_required = $stmt->fetchAll();
        
        foreach ($missing_required as $jabatan) {
            $this->issues[] = [
                'type' => 'missing_required_fields',
                'table' => 'jabatan',
                'record_id' => $jabatan['id'],
                'description' => "Jabatan ID {$jabatan['id']} missing required fields",
                'severity' => 'high'
            ];
        }
        
        // Check for duplicate nama_jabatan within same unsur
        $stmt = $this->pdo->query("
            SELECT id_unsur, nama_jabatan, COUNT(*) as count, GROUP_CONCAT(id) as ids
            FROM jabatan 
            GROUP BY id_unsur, nama_jabatan 
            HAVING count > 1
        ");
        $duplicate_names = $stmt->fetchAll();
        
        foreach ($duplicate_names as $dup) {
            $this->issues[] = [
                'type' => 'duplicate_jabatan_name',
                'table' => 'jabatan',
                'description' => "Duplicate jabatan name '{$dup['nama_jabatan']}' in unsur {$dup['id_unsur']} found in records: {$dup['ids']}",
                'severity' => 'medium',
                'count' => $dup['count']
            ];
        }
    }
    
    /**
     * Validate relationships between tables
     */
    private function validateRelationships() {
        // Check personil with invalid unsur references
        $stmt = $this->pdo->query("
            SELECT p.id, p.nama, p.id_unsur
            FROM personil p 
            WHERE p.is_deleted = 0 
            AND p.id_unsur > 0 
            AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = p.id_unsur)
        ");
        $invalid_unsur_refs = $stmt->fetchAll();
        
        foreach ($invalid_unsur_refs as $person) {
            $this->issues[] = [
                'type' => 'invalid_unsur_reference',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} references non-existent unsur ID {$person['id_unsur']}",
                'severity' => 'high'
            ];
        }
        
        // Check personil with invalid bagian references
        $stmt = $this->pdo->query("
            SELECT p.id, p.nama, p.id_bagian
            FROM personil p 
            WHERE p.is_deleted = 0 
            AND p.id_bagian > 0 
            AND NOT EXISTS (SELECT 1 FROM bagian b WHERE b.id = p.id_bagian)
        ");
        $invalid_bagian_refs = $stmt->fetchAll();
        
        foreach ($invalid_bagian_refs as $person) {
            $this->issues[] = [
                'type' => 'invalid_bagian_reference',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} references non-existent bagian ID {$person['id_bagian']}",
                'severity' => 'high'
            ];
        }
        
        // Check personil with invalid jabatan references
        $stmt = $this->pdo->query("
            SELECT p.id, p.nama, p.id_jabatan
            FROM personil p 
            WHERE p.is_deleted = 0 
            AND p.id_jabatan > 0 
            AND NOT EXISTS (SELECT 1 FROM jabatan j WHERE j.id = p.id_jabatan)
        ");
        $invalid_jabatan_refs = $stmt->fetchAll();
        
        foreach ($invalid_jabatan_refs as $person) {
            $this->issues[] = [
                'type' => 'invalid_jabatan_reference',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} references non-existent jabatan ID {$person['id_jabatan']}",
                'severity' => 'high'
            ];
        }
        
        // Check bagian with invalid unsur references
        $stmt = $this->pdo->query("
            SELECT b.id, b.nama_bagian, b.id_unsur
            FROM bagian b 
            WHERE b.id_unsur > 0 
            AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = b.id_unsur)
        ");
        $invalid_bagian_unsur = $stmt->fetchAll();
        
        foreach ($invalid_bagian_unsur as $bagian) {
            $this->issues[] = [
                'type' => 'invalid_unsur_reference',
                'table' => 'bagian',
                'record_id' => $bagian['id'],
                'description' => "Bagian ID {$bagian['id']} references non-existent unsur ID {$bagian['id_unsur']}",
                'severity' => 'high'
            ];
        }
        
        // Check jabatan with invalid unsur references
        $stmt = $this->pdo->query("
            SELECT j.id, j.nama_jabatan, j.id_unsur
            FROM jabatan j 
            WHERE j.id_unsur > 0 
            AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = j.id_unsur)
        ");
        $invalid_jabatan_unsur = $stmt->fetchAll();
        
        foreach ($invalid_jabatan_unsur as $jabatan) {
            $this->issues[] = [
                'type' => 'invalid_unsur_reference',
                'table' => 'jabatan',
                'record_id' => $jabatan['id'],
                'description' => "Jabatan ID {$jabatan['id']} references non-existent unsur ID {$jabatan['id_unsur']}",
                'severity' => 'high'
            ];
        }
    }
    
    /**
     * Validate data consistency
     */
    private function validateDataConsistency() {
        // Check for personil with status 'nonaktif' but no alasan_status
        $stmt = $this->pdo->query("
            SELECT id, nama, status_ket
            FROM personil 
            WHERE is_deleted = 0 
            AND status_ket = 'nonaktif' 
            AND (alasan_status IS NULL OR alasan_status = '')
        ");
        $missing_alasan = $stmt->fetchAll();
        
        foreach ($missing_alasan as $person) {
            $this->issues[] = [
                'type' => 'missing_alasan_status',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} has status 'nonaktif' but missing alasan_status",
                'severity' => 'medium'
            ];
        }
        
        // Check for personil with status 'aktif' but has alasan_status
        $stmt = $this->pdo->query("
            SELECT id, nama, status_ket
            FROM personil 
            WHERE is_deleted = 0 
            AND status_ket = 'aktif' 
            AND alasan_status IS NOT NULL AND alasan_status != ''
        ");
        $inconsistent_status = $stmt->fetchAll();
        
        foreach ($inconsistent_status as $person) {
            $this->issues[] = [
                'type' => 'inconsistent_status',
                'table' => 'personil',
                'record_id' => $person['id'],
                'description' => "Personil ID {$person['id']} has status 'aktif' but has alasan_status",
                'severity' => 'low'
            ];
        }
    }
    
    /**
     * Validate business rules
     */
    private function validateBusinessRules() {
        // Check if there are any personil without assigned unsur
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM personil 
            WHERE is_deleted = 0 
            AND (id_unsur IS NULL OR id_unsur = 0)
        ");
        $no_unsur = $stmt->fetch()['count'];
        
        if ($no_unsur > 0) {
            $this->issues[] = [
                'type' => 'business_rule_violation',
                'table' => 'personil',
                'description' => "Found {$no_unsur} personil without assigned unsur",
                'severity' => 'medium',
                'count' => $no_unsur
            ];
        }
        
        // Check if there are any bagian without assigned unsur
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM bagian 
            WHERE id_unsur IS NULL OR id_unsur = 0
        ");
        $no_unsur = $stmt->fetch()['count'];
        
        if ($no_unsur > 0) {
            $this->issues[] = [
                'type' => 'business_rule_violation',
                'table' => 'bagian',
                'description' => "Found {$no_unsur} bagian without assigned unsur",
                'severity' => 'high',
                'count' => $no_unsur
            ];
        }
        
        // Check if there are any jabatan without assigned unsur
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM jabatan 
            WHERE id_unsur IS NULL OR id_unsur = 0
        ");
        $no_unsur = $stmt->fetch()['count'];
        
        if ($no_unsur > 0) {
            $this->issues[] = [
                'type' => 'business_rule_violation',
                'table' => 'jabatan',
                'description' => "Found {$no_unsur} jabatan without assigned unsur",
                'severity' => 'high',
                'count' => $no_unsur
            ];
        }
    }
    
    /**
     * Generate validation report
     */
    private function generateReport() {
        $total_issues = count($this->issues);
        $severity_counts = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        $type_counts = [];
        $table_counts = [];
        
        foreach ($this->issues as $issue) {
            $severity = $issue['severity'] ?? 'medium';
            $type = $issue['type'] ?? 'unknown';
            $table = $issue['table'] ?? 'unknown';
            
            $severity_counts[$severity]++;
            $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
            $table_counts[$table] = ($table_counts[$table] ?? 0) + 1;
        }
        
        return [
            'summary' => [
                'total_issues' => $total_issues,
                'severity_breakdown' => $severity_counts,
                'type_breakdown' => $type_counts,
                'table_breakdown' => $table_counts,
                'status' => $total_issues === 0 ? 'healthy' : ($severity_counts['high'] > 0 ? 'critical' : 'warning')
            ],
            'issues' => $this->issues,
            'recommendations' => $this->generateRecommendations(),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Generate recommendations based on issues found
     */
    private function generateRecommendations() {
        $recommendations = [];
        
        $high_issues = array_filter($this->issues, function($i) { return $i['severity'] === 'high'; });
        $medium_issues = array_filter($this->issues, function($i) { return $i['severity'] === 'medium'; });
        
        if (!empty($high_issues)) {
            $recommendations[] = 'Address high severity issues first as they may affect application functionality';
        }
        
        $missing_fields = array_filter($this->issues, function($i) { return $i['type'] === 'missing_required_fields'; });
        if (!empty($missing_fields)) {
            $recommendations[] = 'Update records with missing required fields to ensure data completeness';
        }
        
        $invalid_refs = array_filter($this->issues, function($i) { return strpos($i['type'], 'invalid_') === 0; });
        if (!empty($invalid_refs)) {
            $recommendations[] = 'Fix invalid foreign key references to maintain data integrity';
        }
        
        $duplicates = array_filter($this->issues, function($i) { return strpos($i['type'], 'duplicate_') === 0; });
        if (!empty($duplicates)) {
            $recommendations[] = 'Resolve duplicate records to ensure data uniqueness';
        }
        
        if (!empty($medium_issues)) {
            $recommendations[] = 'Review medium severity issues for data quality improvements';
        }
        
        return $recommendations;
    }
}

// Run validation if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $validator = new DataIntegrityValidator();
        $result = $validator->validateAll();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    }
}
?>
