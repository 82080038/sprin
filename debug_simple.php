<?php
require_once __DIR__ . '/core/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DEBUG BKO ISSUE ===\n";
    
    // Get unsur data
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get bagian data
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
    
    echo "UNSUR LAINNYA Analysis:\n";
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            $unsurId = $unsur['id'];
            $bagianCount = isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0;
            echo "  Unsur ID: {$unsurId}\n";
            echo "  Bagian Count: {$bagianCount}\n";
            echo "  isset check: " . (isset($bagianByUnsur[$unsurId]) ? 'true' : 'false') . "\n";
            echo "  empty check: " . (!empty($bagianByUnsur[$unsurId]) ? 'true' : 'false') . "\n";
            
            if (isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId])) {
                echo "  Bagians:\n";
                foreach ($bagianByUnsur[$unsurId] as $bagian) {
                    echo "    - {$bagian['nama_bagian']} (ID: {$bagian['id']})\n";
                }
            }
            break;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
