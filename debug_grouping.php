<?php
// Debug lebih detail untuk grouping issue
require_once __DIR__ . '/core/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DEBUG GROUPING ISSUE ===\n\n";
    
    // Get bagian data
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "All bagian records:\n";
    foreach ($bagianData as $index => $bagian) {
        echo "  [$index] ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Unsur ID: '{$bagian['id_unsur']}', Unsur: '{$bagian['nama_unsur']}', Urutan: {$bagian['urutan']}\n";
    }
    
    echo "\n=== GROUPING PROCESS DEBUG ===\n";
    
    // Group bagian by unsur - step by step
    $bagianByUnsur = [];
    foreach ($bagianData as $index => $bagian) {
        echo "Processing record [$index]:\n";
        echo "  bagian['id_unsur'] = '{$bagian['id_unsur']}' (type: " . gettype($bagian['id_unsur']) . ")\n";
        echo "  bagian['nama_bagian'] = '{$bagian['nama_bagian']}'\n";
        
        $unsurId = $bagian['id_unsur'];
        echo "  \$unsurId = '$unsurId' (type: " . gettype($unsurId) . ")\n";
        
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
            echo "  Created bagianByUnsur[$unsurId]\n";
        } else {
            echo "  bagianByUnsur[$unsurId] already exists\n";
        }
        
        $bagianByUnsur[$unsurId][] = $bagian;
        echo "  Added bagian to bagianByUnsur[$unsurId]\n";
        echo "  bagianByUnsur[$unsurId] now has " . count($bagianByUnsur[$unsurId]) . " items\n\n";
    }
    
    echo "=== FINAL bagianByUnsur ARRAY ===\n";
    foreach ($bagianByUnsur as $unsurId => $bagians) {
        echo "bagianByUnsur[$unsurId]:\n";
        foreach ($bagians as $bagian) {
            echo "  - ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Unsur ID: {$bagian['id_unsur']}\n";
        }
    }
    
    echo "\n=== SPECIFIC CHECK FOR KEY '6' ===\n";
    var_dump(isset($bagianByUnsur[6]));
    var_dump(isset($bagianByUnsur['6']));
    var_dump(array_key_exists(6, $bagianByUnsur));
    var_dump(array_key_exists('6', $bagianByUnsur));
    
    if (isset($bagianByUnsur[6])) {
        echo "bagianByUnsur[6] exists with " . count($bagianByUnsur[6]) . " items\n";
    } else {
        echo "bagianByUnsur[6] does NOT exist\n";
    }
    
    if (isset($bagianByUnsur['6'])) {
        echo "bagianByUnsur['6'] exists with " . count($bagianByUnsur['6']) . " items\n";
    } else {
        echo "bagianByUnsur['6'] does NOT exist\n";
    }
    
    echo "\nAll keys in bagianByUnsur:\n";
    foreach (array_keys($bagianByUnsur) as $key) {
        echo "  '$key' (type: " . gettype($key) . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
