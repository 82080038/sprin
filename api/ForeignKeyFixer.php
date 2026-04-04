<?php
declare(strict_types=1);
/**
 * Foreign Key Constraints Fixer
 * Add all missing foreign key constraints to improve data integrity
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/APIResponse.php';

class ForeignKeyFixer {
    private $pdo;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Add all missing foreign key constraints
     */
    public function addMissingConstraints() {
        $results = [];
        $errors = [];
        
        // Disable foreign key checks temporarily
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        try {
            // Add personil foreign keys
            $results[] = $this->addPersonilConstraints();
            
            // Add bagian foreign keys
            $results[] = $this->addBagianConstraints();
            
            // Add jabatan foreign keys
            $results[] = $this->addJabatanConstraints();
            
            // Add personil_backup foreign keys
            $results[] = $this->addPersonilBackupConstraints();
            
        } catch (Exception $e) {
            $errors[] = "Error adding constraints: " . $e->getMessage();
        } finally {
            // Re-enable foreign key checks
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
        
        return [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Add personil table foreign key constraints
     */
    private function addPersonilConstraints() {
        $constraints = [
            [
                'name' => 'fk_personil_pangkat',
                'sql' => "ALTER TABLE personil ADD CONSTRAINT fk_personil_pangkat 
                        FOREIGN KEY (id_pangkat) REFERENCES pangkat(id) 
                        ON DELETE SET NULL ON UPDATE CASCADE"
            ],
            [
                'name' => 'fk_personil_jabatan',
                'sql' => "ALTER TABLE personil ADD CONSTRAINT fk_personil_jabatan 
                        FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) 
                        ON DELETE SET NULL ON UPDATE CASCADE"
            ],
            [
                'name' => 'fk_personil_bagian',
                'sql' => "ALTER TABLE personil ADD CONSTRAINT fk_personil_bagian 
                        FOREIGN KEY (id_bagian) REFERENCES bagian(id) 
                        ON DELETE SET NULL ON UPDATE CASCADE"
            ],
            [
                'name' => 'fk_personil_unsur',
                'sql' => "ALTER TABLE personil ADD CONSTRAINT fk_personil_unsur 
                        FOREIGN KEY (id_unsur) REFERENCES unsur(id) 
                        ON DELETE SET NULL ON UPDATE CASCADE"
            ],
            [
                'name' => 'fk_personil_jenis_pegawai',
                'sql' => "ALTER TABLE personil ADD CONSTRAINT fk_personil_jenis_pegawai 
                        FOREIGN KEY (id_jenis_pegawai) REFERENCES master_jenis_pegawai(id) 
                        ON DELETE SET NULL ON UPDATE CASCADE"
            ]
        ];
        
        $results = [];
        foreach ($constraints as $constraint) {
            try {
                // Check if constraint already exists
                $check_sql = "SELECT COUNT(*) as count 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'personil' 
                            AND CONSTRAINT_NAME = '{$constraint['name']}'";
                
                $stmt = $this->pdo->query($check_sql);
                $exists = $stmt->fetch()['count'] > 0;
                
                if (!$exists) {
                    $this->pdo->exec($constraint['sql']);
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'added',
                        'message' => 'Successfully added'
                    ];
                } else {
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'exists',
                        'message' => 'Constraint already exists'
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'constraint' => $constraint['name'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return [
            'table' => 'personil',
            'constraints' => $results
        ];
    }
    
    /**
     * Add bagian table foreign key constraints
     */
    private function addBagianConstraints() {
        $constraints = [
            [
                'name' => 'fk_bagian_unsur',
                'sql' => "ALTER TABLE bagian ADD CONSTRAINT fk_bagian_unsur 
                        FOREIGN KEY (id_unsur) REFERENCES unsur(id) 
                        ON DELETE CASCADE ON UPDATE CASCADE"
            ]
        ];
        
        $results = [];
        foreach ($constraints as $constraint) {
            try {
                $check_sql = "SELECT COUNT(*) as count 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'bagian' 
                            AND CONSTRAINT_NAME = '{$constraint['name']}'";
                
                $stmt = $this->pdo->query($check_sql);
                $exists = $stmt->fetch()['count'] > 0;
                
                if (!$exists) {
                    $this->pdo->exec($constraint['sql']);
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'added',
                        'message' => 'Successfully added'
                    ];
                } else {
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'exists',
                        'message' => 'Constraint already exists'
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'constraint' => $constraint['name'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return [
            'table' => 'bagian',
            'constraints' => $results
        ];
    }
    
    /**
     * Add jabatan table foreign key constraints
     */
    private function addJabatanConstraints() {
        $constraints = [
            [
                'name' => 'fk_jabatan_unsur',
                'sql' => "ALTER TABLE jabatan ADD CONSTRAINT fk_jabatan_unsur 
                        FOREIGN KEY (id_unsur) REFERENCES unsur(id) 
                        ON DELETE CASCADE ON UPDATE CASCADE"
            ]
        ];
        
        $results = [];
        foreach ($constraints as $constraint) {
            try {
                $check_sql = "SELECT COUNT(*) as count 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'jabatan' 
                            AND CONSTRAINT_NAME = '{$constraint['name']}'";
                
                $stmt = $this->pdo->query($check_sql);
                $exists = $stmt->fetch()['count'] > 0;
                
                if (!$exists) {
                    $this->pdo->exec($constraint['sql']);
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'added',
                        'message' => 'Successfully added'
                    ];
                } else {
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'exists',
                        'message' => 'Constraint already exists'
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'constraint' => $constraint['name'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return [
            'table' => 'jabatan',
            'constraints' => $results
        ];
    }
    
    /**
     * Add personil_backup table foreign key constraints
     */
    private function addPersonilBackupConstraints() {
        $constraints = [
            [
                'name' => 'fk_personil_backup_pangkat',
                'sql' => "ALTER TABLE personil_backup ADD CONSTRAINT fk_personil_backup_pangkat 
                        FOREIGN KEY (id_pangkat) REFERENCES pangkat(id) 
                        ON DELETE NO ACTION ON UPDATE NO ACTION"
            ],
            [
                'name' => 'fk_personil_backup_jabatan',
                'sql' => "ALTER TABLE personil_backup ADD CONSTRAINT fk_personil_backup_jabatan 
                        FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) 
                        ON DELETE NO ACTION ON UPDATE NO ACTION"
            ],
            [
                'name' => 'fk_personil_backup_bagian',
                'sql' => "ALTER TABLE personil_backup ADD CONSTRAINT fk_personil_backup_bagian 
                        FOREIGN KEY (id_bagian) REFERENCES bagian(id) 
                        ON DELETE NO ACTION ON UPDATE NO ACTION"
            ],
            [
                'name' => 'fk_personil_backup_unsur',
                'sql' => "ALTER TABLE personil_backup ADD CONSTRAINT fk_personil_backup_unsur 
                        FOREIGN KEY (id_unsur) REFERENCES unsur(id) 
                        ON DELETE NO ACTION ON UPDATE NO ACTION"
            ]
        ];
        
        $results = [];
        foreach ($constraints as $constraint) {
            try {
                $check_sql = "SELECT COUNT(*) as count 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'personil_backup' 
                            AND CONSTRAINT_NAME = '{$constraint['name']}'";
                
                $stmt = $this->pdo->query($check_sql);
                $exists = $stmt->fetch()['count'] > 0;
                
                if (!$exists) {
                    $this->pdo->exec($constraint['sql']);
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'added',
                        'message' => 'Successfully added'
                    ];
                } else {
                    $results[] = [
                        'constraint' => $constraint['name'],
                        'status' => 'exists',
                        'message' => 'Constraint already exists'
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'constraint' => $constraint['name'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return [
            'table' => 'personil_backup',
            'constraints' => $results
        ];
    }
}

// Execute if accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    try {
        $fixer = new ForeignKeyFixer();
        $result = $fixer->addMissingConstraints();
        
        echo json_encode(APIResponse::success($result, 'Foreign key constraints fix completed'));
        
    } catch (Exception $e) {
        echo json_encode(APIResponse::error($e->getMessage(), 500));
    }
}
?>
