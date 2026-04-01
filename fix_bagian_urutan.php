<?php
/**
 * Manual Migration Script - Add urutan column to bagian table
 * Run this script to fix the ordering issue
 */

// Database connection
$host = 'localhost';
$dbname = 'bagops';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Starting migration for bagian table...\n";
    
    // 1. Check if urutan column exists
    echo "📋 Checking if urutan column exists...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✅ Urutan column already exists\n";
    } else {
        echo "➕ Adding urutan column...\n";
        $pdo->exec("ALTER TABLE bagian ADD COLUMN urutan INT DEFAULT 0 AFTER id_unsur");
        echo "✅ Urutan column added\n";
    }
    
    // 2. Update existing records with proper urutan values
    echo "🔄 Updating urutan values for existing records...\n";
    
    // Get all bagian grouped by unsur
    $stmt = $pdo->query("
        SELECT id, id_unsur, nama_bagian 
        FROM bagian 
        ORDER BY id_unsur, nama_bagian
    ");
    $bagians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by unsur and assign urutan
    $currentUnsur = null;
    $currentUrutan = 1;
    $updateCount = 0;
    
    foreach ($bagians as $bagian) {
        if ($currentUnsur !== $bagian['id_unsur']) {
            $currentUnsur = $bagian['id_unsur'];
            $currentUrutan = 1;
        }
        
        $updateStmt = $pdo->prepare("UPDATE bagian SET urutan = ? WHERE id = ?");
        $updateStmt->execute([$currentUrutan, $bagian['id']]);
        
        echo "  Updated: {$bagian['nama_bagian']} (Unsur: {$bagian['id_unsur']}, Urutan: {$currentUrutan})\n";
        $currentUrutan++;
        $updateCount++;
    }
    
    // 3. Create index for better performance
    echo "📊 Creating index for better performance...\n";
    try {
        $pdo->exec("CREATE INDEX idx_bagian_unsur_urutan ON bagian (id_unsur, urutan)");
        echo "✅ Index created\n";
    } catch (Exception $e) {
        echo "⚠️ Index might already exist: " . $e->getMessage() . "\n";
    }
    
    // 4. Verify the results
    echo "\n🔍 Verification:\n";
    $stmt = $pdo->query("
        SELECT u.nama_unsur, b.nama_bagian, b.urutan 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current bagian order:\n";
    foreach ($results as $result) {
        echo "  {$result['nama_unsur']} - {$result['nama_bagian']} (urutan: {$result['urutan']})\n";
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "📈 Updated {$updateCount} records\n";
    echo "✅ Bagian table now has proper urutan column\n";
    echo "🔄 Changes will now persist after reordering\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
