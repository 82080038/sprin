<?php
declare(strict_types=1);
/**
 * Simple Database Checker
 * Basic database structure and data validation
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

class SimpleDatabaseChecker {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Basic database check
     */
    public function checkDatabase() {
        $results = [];
        
        // Check connection
        $results['connection'] = $this->checkConnection();
        
        // Check tables
        $results['tables'] = $this->checkTables();
        
        // Check data counts
        $results['data_counts'] = $this->checkDataCounts();
        
        // Check basic data integrity
        $results['data_integrity'] = $this->checkDataIntegrity();
        
        return $results;
    }
    
    /**
     * Check database connection
     */
    private function checkConnection() {
        try {
            $version = $this->pdo->query("SELECT VERSION() as version")->fetch();
            return [
                'status' => 'connected',
                'version' => $version['version'],
                'database' => DB_NAME
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check basic table structure
     */
    private function checkTables() {
        $tables = [];
        
        // Get table list
        $stmt = $this->pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tableName = $row[0];
            
            // Get basic table info
            $stmt2 = $this->pdo->query("SELECT COUNT(*) as count FROM `" . $tableName . "`");
            $count = $stmt2->fetch()['count'];
            
            $tables[$tableName] = [
                'row_count' => (int)$count,
                'status' => 'exists'
            ];
        }
        
        return $tables;
    }
    
    /**
     * Check data counts
     */
    private function checkDataCounts() {
        $counts = [];
        
        // Core tables
        $tables_to_check = ['personil', 'unsur', 'bagian', 'jabatan', 'pangkat', 'master_jenis_pegawai'];
        
        foreach ($tables_to_check as $table) {
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `" . $table . "`");
                $counts[$table] = (int)$stmt->fetch()['count'];
            } catch (Exception $e) {
                $counts[$table] = 'error: ' . $e->getMessage();
            }
        }
        
        return $counts;
    }
    
    /**
     * Check basic data integrity
     */
    private function checkDataIntegrity() {
        $issues = [];
        
        try {
            // Check personil with invalid references
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM personil p 
                WHERE p.is_deleted = 0 
                AND (
                    (p.id_unsur > 0 AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = p.id_unsur))
                    OR (p.id_bagian > 0 AND NOT EXISTS (SELECT 1 FROM bagian b WHERE b.id = p.id_bagian))
                    OR (p.id_jabatan > 0 AND NOT EXISTS (SELECT 1 FROM jabatan j WHERE j.id = p.id_jabatan))
                )
            ");
            $invalid_refs = $stmt->fetch()['count'];
            if ($invalid_refs > 0) {
                $issues[] = "Found {$invalid_refs} personil with invalid references";
            }
            
            // Check duplicate NRP
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM (
                    SELECT nrp, COUNT(*) as cnt 
                    FROM personil 
                    WHERE is_deleted = 0 AND nrp IS NOT NULL AND nrp != ''
                    GROUP BY nrp 
                    HAVING cnt > 1
                ) duplicates
            ");
            $duplicate_nrp = $stmt->fetch()['count'];
            if ($duplicate_nrp > 0) {
                $issues[] = "Found {$duplicate_nrp} duplicate NRP values";
            }
            
            // Check bagian with invalid unsur
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM bagian b 
                WHERE b.id_unsur > 0 
                AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = b.id_unsur)
            ");
            $invalid_bagian = $stmt->fetch()['count'];
            if ($invalid_bagian > 0) {
                $issues[] = "Found {$invalid_bagian} bagian with invalid unsur reference";
            }
            
            // Check jabatan with invalid unsur
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM jabatan j 
                WHERE j.id_unsur > 0 
                AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = j.id_unsur)
            ");
            $invalid_jabatan = $stmt->fetch()['count'];
            if ($invalid_jabatan > 0) {
                $issues[] = "Found {$invalid_jabatan} jabatan with invalid unsur reference";
            }
            
        } catch (Exception $e) {
            $issues[] = "Error checking data integrity: " . $e->getMessage();
        }
        
        return [
            'issues' => $issues,
            'status' => empty($issues) ? 'ok' : 'has_issues'
        ];
    }
}

// Run check if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $checker = new SimpleDatabaseChecker();
        $result = $checker->checkDatabase();
        
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
