<?php
declare(strict_types=1);
/**
 * Database Check Script
 * Check database schema and table status
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

echo "=== DATABASE CHECK REPORT ===\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check database connection
    echo "✅ Database connection successful\n\n";
    
    // Get all tables
    echo "TABLES IN DATABASE:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expectedTables = [
        'assignments', 'bagian', 'jadwal', 'jabatan', 'master_jenis_pegawai',
        'notifications', 'operations', 'pangkat', 'personil', 'personil_kontak',
        'personil_medsos', 'personil_pendidikan', 'unsur'
    ];
    
    $newTables = ['users', 'user_sessions', 'user_activity_log', 'password_reset_tokens', 'backups', 'backup_schedule'];
    
    foreach ($tables as $table) {
        $status = in_array($table, $expectedTables) ? '✅' : 
                  (in_array($table, $newTables) ? '🆕' : '⚠️');
        echo "  $status $table\n";
    }
    
    echo "\n";
    
    // Check if new tables exist
    echo "MIGRATION STATUS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $usersExists = in_array('users', $tables);
    $backupsExists = in_array('backups', $tables);
    
    if ($usersExists) {
        echo "✅ users table - MIGRATED\n";
        
        // Check user count
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        echo "   └─ Users count: $userCount\n";
    } else {
        echo "❌ users table - NOT MIGRATED\n";
        echo "   └─ Run: database/migrations/create_users_table.sql\n";
    }
    
    if ($backupsExists) {
        echo "✅ backups table - MIGRATED\n";
        
        // Check backup count
        $stmt = $pdo->query("SELECT COUNT(*) FROM backups");
        $backupCount = $stmt->fetchColumn();
        echo "   └─ Backups count: $backupCount\n";
    } else {
        echo "❌ backups table - NOT MIGRATED\n";
        echo "   └─ Run: database/migrations/create_backup_tables.sql\n";
    }
    
    echo "\n";
    
    // Check personil count
    echo "DATA STATISTICS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_deleted = FALSE AND is_active = TRUE");
    $personilCount = $stmt->fetchColumn();
    echo "  Personil (active): $personilCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM unsur");
    $unsurCount = $stmt->fetchColumn();
    echo "  Unsur: $unsurCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM bagian");
    $bagianCount = $stmt->fetchColumn();
    echo "  Bagian: $bagianCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM jabatan");
    $jabatanCount = $stmt->fetchColumn();
    echo "  Jabatan: $jabatanCount\n";
    
    echo "\n";
    
    // Check table structures
    echo "TABLE STRUCTURES:\n";
    echo str_repeat("-", 50) . "\n";
    
    $checkTables = ['users', 'backups', 'personil', 'unsur'];
    foreach ($checkTables as $table) {
        if (in_array($table, $tables)) {
            echo "\n📋 $table columns:\n";
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                $pk = $col['Key'] === 'PRI' ? ' (PK)' : '';
                $null = $col['Null'] === 'NO' ? ' NOT NULL' : '';
                echo "   • {$col['Field']} ({$col['Type']})$null$pk\n";
            }
        } else {
            echo "\n❌ $table - Table not found\n";
        }
    }
    
    echo "\n✅ Database check completed successfully\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
