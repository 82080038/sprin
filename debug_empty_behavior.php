<?php
require_once __DIR__ . '/core/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== EMPTY() CHECK DEBUG ===\n";
    
    // Get exact same data as bagian.php
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group bagian by unsur
    $bagianByUnsur = [];
    foreach ($bagianData as $bagian) {
        $unsurId = $bagian['id_unsur'];
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
        }
        $bagianByUnsur[$unsurId][] = $bagian;
    }
    
    // Test empty() behavior on actual data
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            $unsurId = $unsur['id'];
            
            echo "UNSUR LAINNYA (ID: {$unsurId}):\n";
            echo "  isset(\$bagianByUnsur[{$unsurId}]): " . var_export(isset($bagianByUnsur[$unsurId]), true) . "\n";
            echo "  !empty(\$bagianByUnsur[{$unsurId}]): " . var_export(!empty($bagianByUnsur[$unsurId]), true) . "\n";
            echo "  count(\$bagianByUnsur[{$unsurId}]): " . count($bagianByUnsur[$unsurId]) . "\n";
            echo "  var_dump(\$bagianByUnsur[{$unsurId}]): " . var_export($bagianByUnsur[$unsurId], true) . "\n";
            
            // Test each element in array
            if (isset($bagianByUnsur[$unsurId])) {
                echo "  Array contents:\n";
                foreach ($bagianByUnsur[$unsurId] as $index => $bagian) {
                    echo "    [{$index}] => var_export: " . var_export($bagian, true) . "\n";
                    echo "    [{$index}] => empty(): " . var_export(empty($bagian), true) . "\n";
                }
            }
            
            break;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
