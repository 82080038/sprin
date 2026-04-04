<?php
declare(strict_types=1);
/**
 * Database Structure Checker
 * Comprehensive database structure analysis and validation
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class DatabaseStructureChecker {
    private $pdo;
    private $results = [];
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Check complete database structure
     */
    public function checkStructure() {
        $this->results = [];
        
        // Get all tables
        $tables = $this->getAllTables();
        
        // Check each table structure
        foreach ($tables as $table) {
            $this->checkTableStructure($table);
        }
        
        // Check foreign key relationships
        $this->checkForeignKeys();
        
        // Check indexes
        $this->checkIndexes();
        
        // Check table relationships
        $this->checkTableRelationships();
        
        return $this->generateStructureReport();
    }
    
    /**
     * Get all tables in database
     */
    private function getAllTables() {
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    /**
     * Check individual table structure
     */
    private function checkTableStructure($table) {
        // Get table columns
        $stmt = $this->pdo->query("DESCRIBE `" . $table . "`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get table status
        $stmt = $this->pdo->query("SHOW TABLE STATUS LIKE '" . $table . "'");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get row count
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `" . $table . "`");
        $count = $stmt->fetch()['count'];
        
        // Check for required columns based on table type
        $issues = $this->validateTableColumns($table, $columns);
        
        $this->results['tables'][$table] = [
            'columns' => $columns,
            'row_count' => (int)$count,
            'engine' => $status['Engine'],
            'collation' => $status['Collation'],
            'auto_increment' => $status['Auto_increment'] ? (int)$status['Auto_increment'] : null,
            'size' => $status['Data_length'] + $status['Index_length'],
            'created_time' => $status['Create_time'],
            'updated_time' => $status['Update_time'],
            'issues' => $issues
        ];
    }
    
    /**
     * Validate table columns based on expected structure
     */
    private function validateTableColumns($table, $columns) {
        $issues = [];
        $column_names = array_column($columns, 'Field');
        
        switch ($table) {
            case 'personil':
                $required = ['id', 'nama', 'nrp', 'is_deleted', 'created_at', 'updated_at'];
                $optional = ['nip', 'JK', 'status_ket', 'alasan_status', 'tanggal_lahir', 'tempat_lahir', 
                             'tanggal_masuk', 'tanggal_pensiun', 'no_karpeg', 'id_pangkat', 'id_jabatan', 
                             'id_bagian', 'id_unsur', 'id_jenis_pegawai'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                
                // Check data types
                foreach ($columns as $col) {
                    if ($col['Field'] === 'id' && $col['Type'] !== 'int(11)') {
                        $issues[] = "ID column should be int(11)";
                    }
                    if ($col['Field'] === 'nrp' && !strpos($col['Type'], 'varchar')) {
                        $issues[] = "NRP column should be varchar";
                    }
                    if ($col['Field'] === 'is_deleted' && $col['Type'] !== 'tinyint(1)') {
                        $issues[] = "is_deleted column should be tinyint(1)";
                    }
                }
                break;
                
            case 'unsur':
                $required = ['id', 'nama_unsur', 'kode_unsur', 'urutan', 'is_active', 'created_at', 'updated_at'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                
                // Check uniqueness constraints
                if (!in_array('kode_unsur', $column_names)) {
                    $issues[] = "Missing kode_unsur column";
                }
                break;
                
            case 'bagian':
                $required = ['id', 'nama_bagian', 'id_unsur', 'urutan', 'is_active', 'created_at', 'updated_at'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                
                // Check foreign key
                if (!in_array('id_unsur', $column_names)) {
                    $issues[] = "Missing id_unsur foreign key column";
                }
                break;
                
            case 'jabatan':
                $required = ['id', 'nama_jabatan', 'id_unsur', 'is_active', 'created_at', 'updated_at'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                
                // Check foreign key
                if (!in_array('id_unsur', $column_names)) {
                    $issues[] = "Missing id_unsur foreign key column";
                }
                break;
                
            case 'pangkat':
                $required = ['id', 'nama_pangkat', 'singkatan', 'level_pangkat'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                break;
                
            case 'master_jenis_pegawai':
                $required = ['id', 'kategori'];
                
                foreach ($required as $req) {
                    if (!in_array($req, $column_names)) {
                        $issues[] = "Missing required column: $req";
                    }
                }
                break;
        }
        
        return $issues;
    }
    
    /**
     * Check foreign key constraints
     */
    private function checkForeignKeys() {
        $stmt = $this->pdo->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $foreign_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->results['foreign_keys'] = $foreign_keys;
        
        // Check for expected foreign keys
        $expected_fks = [
            'personil' => [
                'id_pangkat' => 'pangkat',
                'id_jabatan' => 'jabatan', 
                'id_bagian' => 'bagian',
                'id_unsur' => 'unsur',
                'id_jenis_pegawai' => 'master_jenis_pegawai'
            ],
            'bagian' => [
                'id_unsur' => 'unsur'
            ],
            'jabatan' => [
                'id_unsur' => 'unsur'
            ]
        ];
        
        $missing_fks = [];
        foreach ($expected_fks as $table => $fks) {
            foreach ($fks as $column => $ref_table) {
                $exists = false;
                foreach ($foreign_keys as $fk) {
                    if ($fk['TABLE_NAME'] === $table && $fk['COLUMN_NAME'] === $column) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $missing_fks[] = "$table.$column -> $ref_table";
                }
            }
        }
        
        $this->results['missing_foreign_keys'] = $missing_fks;
    }
    
    /**
     * Check indexes
     */
    private function checkIndexes() {
        $stmt = $this->pdo->query("SHOW INDEXES");
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by table
        $table_indexes = [];
        foreach ($indexes as $index) {
            $table_indexes[$index['Table']][] = $index;
        }
        
        $this->results['indexes'] = $table_indexes;
        
        // Check for expected indexes
        $expected_indexes = [
            'personil' => ['PRIMARY', 'nrp', 'is_deleted'],
            'unsur' => ['PRIMARY', 'kode_unsur', 'urutan'],
            'bagian' => ['PRIMARY', 'id_unsur', 'urutan'],
            'jabatan' => ['PRIMARY', 'id_unsur', 'nama_jabatan'],
            'pangkat' => ['PRIMARY', 'level_pangkat']
        ];
        
        $missing_indexes = [];
        foreach ($expected_indexes as $table => $expected) {
            if (!isset($table_indexes[$table])) {
                continue;
            }
            
            $existing_indexes = array_column($table_indexes[$table], 'Key_name');
            foreach ($expected as $exp_index) {
                if (!in_array($exp_index, $existing_indexes)) {
                    $missing_indexes[] = "$table.$exp_index";
                }
            }
        }
        
        $this->results['missing_indexes'] = $missing_indexes;
    }
    
    /**
     * Check table relationships and data consistency
     */
    private function checkTableRelationships() {
        $relationships = [];
        
        // Check personil -> unsur relationship
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as orphaned 
            FROM personil p 
            LEFT JOIN unsur u ON p.id_unsur = u.id 
            WHERE p.id_unsur > 0 AND u.id IS NULL AND p.is_deleted = 0
        ");
        $orphaned_personil_unsur = $stmt->fetch()['orphaned'];
        
        // Check personil -> bagian relationship
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as orphaned 
            FROM personil p 
            LEFT JOIN bagian b ON p.id_bagian = b.id 
            WHERE p.id_bagian > 0 AND b.id IS NULL AND p.is_deleted = 0
        ");
        $orphaned_personil_bagian = $stmt->fetch()['orphaned'];
        
        // Check personil -> jabatan relationship
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as orphaned 
            FROM personil p 
            LEFT JOIN jabatan j ON p.id_jabatan = j.id 
            WHERE p.id_jabatan > 0 AND j.id IS NULL AND p.is_deleted = 0
        ");
        $orphaned_personil_jabatan = $stmt->fetch()['orphaned'];
        
        // Check bagian -> unsur relationship
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as orphaned 
            FROM bagian b 
            LEFT JOIN unsur u ON b.id_unsur = u.id 
            WHERE b.id_unsur > 0 AND u.id IS NULL
        ");
        $orphaned_bagian_unsur = $stmt->fetch()['orphaned'];
        
        // Check jabatan -> unsur relationship
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as orphaned 
            FROM jabatan j 
            LEFT JOIN unsur u ON j.id_unsur = u.id 
            WHERE j.id_unsur > 0 AND u.id IS NULL
        ");
        $orphaned_jabatan_unsur = $stmt->fetch()['orphaned'];
        
        $relationships['orphaned_records'] = [
            'personil_unsur' => (int)$orphaned_personil_unsur,
            'personil_bagian' => (int)$orphaned_personil_bagian,
            'personil_jabatan' => (int)$orphaned_personil_jabatan,
            'bagian_unsur' => (int)$orphaned_bagian_unsur,
            'jabatan_unsur' => (int)$orphaned_jabatan_unsur
        ];
        
        $this->results['relationships'] = $relationships;
    }
    
    /**
     * Generate structure report
     */
    private function generateStructureReport() {
        $total_tables = count($this->results['tables'] ?? []);
        $total_rows = array_sum(array_column($this->results['tables'], 'row_count'));
        $total_size = array_sum(array_column($this->results['tables'], 'size'));
        
        $issues = [];
        foreach ($this->results['tables'] as $table => $info) {
            if (!empty($info['issues'])) {
                $issues = array_merge($issues, $info['issues']);
            }
        }
        
        return [
            'summary' => [
                'total_tables' => $total_tables,
                'total_rows' => $total_rows,
                'total_size_bytes' => $total_size,
                'total_size_mb' => round($total_size / 1024 / 1024, 2),
                'total_issues' => count($issues),
                'missing_foreign_keys' => count($this->results['missing_foreign_keys'] ?? []),
                'missing_indexes' => count($this->results['missing_indexes'] ?? [])
            ],
            'tables' => $this->results['tables'] ?? [],
            'foreign_keys' => $this->results['foreign_keys'] ?? [],
            'missing_foreign_keys' => $this->results['missing_foreign_keys'] ?? [],
            'indexes' => $this->results['indexes'] ?? [],
            'missing_indexes' => $this->results['missing_indexes'] ?? [],
            'relationships' => $this->results['relationships'] ?? [],
            'issues' => $issues,
            'recommendations' => $this->generateRecommendations(),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Generate recommendations based on structure analysis
     */
    private function generateRecommendations() {
        $recommendations = [];
        
        // Check for missing foreign keys
        if (!empty($this->results['missing_foreign_keys'])) {
            $recommendations[] = 'Add missing foreign key constraints: ' . implode(', ', $this->results['missing_foreign_keys']);
        }
        
        // Check for missing indexes
        if (!empty($this->results['missing_indexes'])) {
            $recommendations[] = 'Add missing indexes for better performance: ' . implode(', ', $this->results['missing_indexes']);
        }
        
        // Check for orphaned records
        if (!empty($this->results['relationships']['orphaned_records'])) {
            $orphaned = array_filter($this->results['relationships']['orphaned_records']);
            if (!empty($orphaned)) {
                $recommendations[] = 'Clean up orphaned records in relationships';
            }
        }
        
        // Check table issues
        foreach ($this->results['tables'] as $table => $info) {
            if (!empty($info['issues'])) {
                $recommendations[] = "Fix issues in table '$table': " . implode(', ', $info['issues']);
            }
        }
        
        return $recommendations;
    }
}

// API endpoint for database structure check
if (isset(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING)) && filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) === 'check_structure') {
    try {
        $checker = new DatabaseStructureChecker();
        $result = $checker->checkStructure();
        
        echo json_encode(APIResponse::success($result, 'Database structure check completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
