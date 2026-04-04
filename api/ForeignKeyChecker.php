<?php
declare(strict_types=1);
/**
 * Foreign Key Constraint Checker
 * Analyze and validate foreign key constraints in the database
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

class ForeignKeyChecker {
    private $pdo;
    private $issues = [];
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Check all foreign key constraints
     */
    public function checkConstraints() {
        $this->issues = [];
        
        // Get current foreign keys
        $current_fks = $this->getCurrentForeignKeys();
        
        // Check expected foreign keys
        $expected_fks = $this->getExpectedForeignKeys();
        
        // Compare and find missing
        $missing_fks = $this->findMissingForeignKeys($current_fks, $expected_fks);
        
        // Check constraint integrity
        $this->checkConstraintIntegrity();
        
        // Check cascade rules
        $this->checkCascadeRules();
        
        return $this->generateReport($current_fks, $expected_fks, $missing_fks);
    }
    
    /**
     * Get current foreign keys from database
     */
    private function getCurrentForeignKeys() {
        $fks = [];
        
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME,
                    CONSTRAINT_NAME,
                    UPDATE_RULE,
                    DELETE_RULE
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY TABLE_NAME, ORDINAL_POSITION
            ");
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as $row) {
                $fks[] = [
                    'table' => $row['TABLE_NAME'],
                    'column' => $row['COLUMN_NAME'],
                    'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                    'referenced_column' => $row['REFERENCED_COLUMN_NAME'],
                    'constraint_name' => $row['CONSTRAINT_NAME'],
                    'update_rule' => $row['UPDATE_RULE'],
                    'delete_rule' => $row['DELETE_RULE']
                ];
            }
        } catch (Exception $e) {
            $this->issues[] = [
                'type' => 'query_error',
                'description' => 'Error retrieving foreign keys: ' . $e->getMessage(),
                'severity' => 'high'
            ];
        }
        
        return $fks;
    }
    
    /**
     * Get expected foreign keys based on application design
     */
    private function getExpectedForeignKeys() {
        return [
            'personil' => [
                'id_pangkat' => [
                    'referenced_table' => 'pangkat',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'SET NULL'
                ],
                'id_jabatan' => [
                    'referenced_table' => 'jabatan',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'SET NULL'
                ],
                'id_bagian' => [
                    'referenced_table' => 'bagian',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'SET NULL'
                ],
                'id_unsur' => [
                    'referenced_table' => 'unsur',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'SET NULL'
                ],
                'id_jenis_pegawai' => [
                    'referenced_table' => 'master_jenis_pegawai',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'SET NULL'
                ]
            ],
            'bagian' => [
                'id_unsur' => [
                    'referenced_table' => 'unsur',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'CASCADE'
                ]
            ],
            'jabatan' => [
                'id_unsur' => [
                    'referenced_table' => 'unsur',
                    'referenced_column' => 'id',
                    'update_rule' => 'CASCADE',
                    'delete_rule' => 'CASCADE'
                ]
            ],
            'personil_backup' => [
                'id_pangkat' => [
                    'referenced_table' => 'pangkat',
                    'referenced_column' => 'id',
                    'update_rule' => 'NO ACTION',
                    'delete_rule' => 'NO ACTION'
                ],
                'id_jabatan' => [
                    'referenced_table' => 'jabatan',
                    'referenced_column' => 'id',
                    'update_rule' => 'NO ACTION',
                    'delete_rule' => 'NO ACTION'
                ],
                'id_bagian' => [
                    'referenced_table' => 'bagian',
                    'referenced_column' => 'id',
                    'update_rule' => 'NO ACTION',
                    'delete_rule' => 'NO ACTION'
                ],
                'id_unsur' => [
                    'referenced_table' => 'unsur',
                    'referenced_column' => 'id',
                    'update_rule' => 'NO ACTION',
                    'delete_rule' => 'NO ACTION'
                ]
            ]
        ];
    }
    
    /**
     * Find missing foreign keys
     */
    private function findMissingForeignKeys($current_fks, $expected_fks) {
        $missing = [];
        
        // Create lookup for current FKs
        $current_lookup = [];
        foreach ($current_fks as $fk) {
            $key = $fk['table'] . '.' . $fk['column'];
            $current_lookup[$key] = $fk;
        }
        
        // Check each expected FK
        foreach ($expected_fks as $table => $fks) {
            foreach ($fks as $column => $expected) {
                $key = $table . '.' . $column;
                
                if (!isset($current_lookup[$key])) {
                    $missing[] = [
                        'table' => $table,
                        'column' => $column,
                        'expected' => $expected,
                        'status' => 'missing'
                    ];
                } else {
                    // Check if rules match expectations
                    $current = $current_lookup[$key];
                    $rule_mismatch = false;
                    
                    if ($current['update_rule'] !== $expected['update_rule']) {
                        $rule_mismatch = true;
                    }
                    
                    if ($current['delete_rule'] !== $expected['delete_rule']) {
                        $rule_mismatch = true;
                    }
                    
                    if ($rule_mismatch) {
                        $missing[] = [
                            'table' => $table,
                            'column' => $column,
                            'expected' => $expected,
                            'current' => [
                                'update_rule' => $current['update_rule'],
                                'delete_rule' => $current['delete_rule']
                            ],
                            'status' => 'rule_mismatch'
                        ];
                    }
                }
            }
        }
        
        return $missing;
    }
    
    /**
     * Check constraint integrity
     */
    private function checkConstraintIntegrity() {
        // Check for orphaned records
        $orphaned_checks = [
            'personil_unsur' => "
                SELECT COUNT(*) as count 
                FROM personil p 
                WHERE p.is_deleted = 0 
                AND p.id_unsur > 0 
                AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = p.id_unsur)
            ",
            'personil_bagian' => "
                SELECT COUNT(*) as count 
                FROM personil p 
                WHERE p.is_deleted = 0 
                AND p.id_bagian > 0 
                AND NOT EXISTS (SELECT 1 FROM bagian b WHERE b.id = p.id_bagian)
            ",
            'personil_jabatan' => "
                SELECT COUNT(*) as count 
                FROM personil p 
                WHERE p.is_deleted = 0 
                AND p.id_jabatan > 0 
                AND NOT EXISTS (SELECT 1 FROM jabatan j WHERE j.id = p.id_jabatan)
            ",
            'bagian_unsur' => "
                SELECT COUNT(*) as count 
                FROM bagian b 
                WHERE b.id_unsur > 0 
                AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = b.id_unsur)
            ",
            'jabatan_unsur' => "
                SELECT COUNT(*) as count 
                FROM jabatan j 
                WHERE j.id_unsur > 0 
                AND NOT EXISTS (SELECT 1 FROM unsur u WHERE u.id = j.id_unsur)
            "
        ];
        
        foreach ($orphaned_checks as $check_name => $sql) {
            try {
                $stmt = $this->pdo->query($sql);
                $count = $stmt->fetch()['count'];
                
                if ($count > 0) {
                    $this->issues[] = [
                        'type' => 'orphaned_records',
                        'check' => $check_name,
                        'count' => (int)$count,
                        'description' => "Found {$count} orphaned records in {$check_name}",
                        'severity' => 'high'
                    ];
                }
            } catch (Exception $e) {
                $this->issues[] = [
                    'type' => 'query_error',
                    'check' => $check_name,
                    'description' => 'Error checking orphaned records: ' . $e->getMessage(),
                    'severity' => 'medium'
                ];
            }
        }
    }
    
    /**
     * Check cascade rules
     */
    private function checkCascadeRules() {
        // This would test cascade behavior by attempting operations
        // For now, just analyze the current rules
        
        $cascade_issues = [];
        
        // Check if any critical relationships have CASCADE delete
        $critical_tables = ['personil', 'bagian', 'jabatan'];
        
        foreach ($critical_tables as $table) {
            try {
                $stmt = $this->pdo->query("
                    SELECT UPDATE_RULE, DELETE_RULE
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '$table'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                $rules = $stmt->fetchAll();
                
                foreach ($rules as $rule) {
                    if ($rule['DELETE_RULE'] === 'CASCADE') {
                        $cascade_issues[] = [
                            'table' => $table,
                            'rule' => 'DELETE CASCADE',
                            'warning' => 'Cascade delete may cause data loss',
                            'severity' => 'medium'
                        ];
                    }
                }
            } catch (Exception $e) {
                $this->issues[] = [
                    'type' => 'query_error',
                    'description' => 'Error checking cascade rules for ' . $table . ': ' . $e->getMessage(),
                    'severity' => 'medium'
                ];
            }
        }
        
        $this->issues = array_merge($this->issues, $cascade_issues);
    }
    
    /**
     * Generate foreign key report
     */
    private function generateReport($current_fks, $expected_fks, $missing_fks) {
        $total_expected = 0;
        $total_current = count($current_fks);
        $total_missing = count($missing_fks);
        
        foreach ($expected_fks as $table => $fks) {
            $total_expected += count($fks);
        }
        
        $severity_counts = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($this->issues as $issue) {
            $severity = $issue['severity'] ?? 'medium';
            $severity_counts[$severity]++;
        }
        
        return [
            'summary' => [
                'total_expected_constraints' => $total_expected,
                'total_current_constraints' => $total_current,
                'total_missing_constraints' => $total_missing,
                'coverage_percentage' => $total_expected > 0 ? round(($total_current / $total_expected) * 100, 2) : 0,
                'total_issues' => count($this->issues),
                'severity_breakdown' => $severity_counts,
                'status' => $total_missing === 0 && $severity_counts['high'] === 0 ? 'healthy' : 'needs_attention'
            ],
            'current_constraints' => $current_fks,
            'expected_constraints' => $expected_fks,
            'missing_constraints' => $missing_fks,
            'issues' => $this->issues,
            'recommendations' => $this->generateRecommendations($missing_fks),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Generate recommendations for foreign key improvements
     */
    private function generateRecommendations($missing_fks) {
        $recommendations = [];
        
        if (!empty($missing_fks)) {
            $recommendations[] = 'Add missing foreign key constraints to ensure referential integrity';
            
            // Group by table for specific recommendations
            $table_groups = [];
            foreach ($missing_fks as $missing) {
                $table_groups[$missing['table']][] = $missing;
            }
            
            foreach ($table_groups as $table => $missing_list) {
                $columns = array_column($missing_list, 'column');
                $recommendations[] = "Add foreign keys for table '{$table}' columns: " . implode(', ', $columns);
            }
        }
        
        $orphaned_issues = array_filter($this->issues, function($i) { return $i['type'] === 'orphaned_records'; });
        if (!empty($orphaned_issues)) {
            $recommendations[] = 'Clean up orphaned records before adding foreign key constraints';
        }
        
        $cascade_issues = array_filter($this->issues, function($i) { return isset($i['rule']) && $i['rule'] === 'DELETE CASCADE'; });
        if (!empty($cascade_issues)) {
            $recommendations[] = 'Review CASCADE delete rules to prevent unintended data loss';
        }
        
        $high_issues = array_filter($this->issues, function($i) { return $i['severity'] === 'high'; });
        if (!empty($high_issues)) {
            $recommendations[] = 'Address high severity issues immediately as they may affect data integrity';
        }
        
        return $recommendations;
    }
}

// Run check if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $checker = new ForeignKeyChecker();
        $result = $checker->checkConstraints();
        
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
