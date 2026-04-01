<?php
// Debug script untuk BKO dan UNSUR LAINNYA
require_once __DIR__ . '/core/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DEBUG BKO DAN UNSUR LAINNYA ===\n\n";
    
    // 1. Check UNSUR data
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "UNSUR Data:\n";
    foreach ($unsurData as $unsur) {
        echo "  ID: {$unsur['id']}, Nama: {$unsur['nama_unsur']}, Urutan: {$unsur['urutan']}\n";
    }
    
    // 2. Check BAGIAN data
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nBAGIAN Data:\n";
    foreach ($bagianData as $bagian) {
        echo "  ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Unsur ID: {$bagian['id_unsur']}, Unsur: {$bagian['nama_unsur']}, Urutan: {$bagian['urutan']}\n";
    }
    
    // 3. Group bagian by unsur
    $bagianByUnsur = [];
    foreach ($bagianData as $bagian) {
        $unsurId = $bagian['id_unsur'];
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
        }
        $bagianByUnsur[$unsurId][] = $bagian;
    }
    
    echo "\nBagian by Unsur:\n";
    foreach ($bagianByUnsur as $unsurId => $bagians) {
        echo "  Unsur ID $unsurId: " . count($bagians) . " bagian\n";
        foreach ($bagians as $bagian) {
            echo "    - {$bagian['nama_bagian']}\n";
        }
    }
    
    // 4. Specific check for UNSUR LAINNYA (ID 6)
    echo "\n=== SPESIFIC CHECK UNSUR LAINNYA (ID 6) ===\n";
    $unsurLainnyaExists = false;
    foreach ($unsurData as $unsur) {
        if ($unsur['id'] == 6) {
            $unsurLainnyaExists = true;
            echo "UNSUR LAINNYA ditemukan: {$unsur['nama_unsur']}\n";
            break;
        }
    }
    
    if (!$unsurLainnyaExists) {
        echo "UNSUR LAINNYA TIDAK ditemukan di unsurData!\n";
    }
    
    // 5. Check BKO in bagianByUnsur[6]
    echo "\nCheck BKO di bagianByUnsur[6]:\n";
    if (isset($bagianByUnsur[6])) {
        echo "  bagianByUnsur[6] exists dengan " . count($bagianByUnsur[6]) . " bagian:\n";
        foreach ($bagianByUnsur[6] as $bagian) {
            echo "    - ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}\n";
        }
    } else {
        echo "  bagianByUnsur[6] TIDAK EXISTS!\n";
    }
    
    // 6. Check if BKO exists anywhere
    echo "\nCheck BKO di semua bagian:\n";
    $bkoFound = false;
    foreach ($bagianData as $bagian) {
        if ($bagian['nama_bagian'] === 'BKO') {
            $bkoFound = true;
            echo "  BKO ditemukan: ID {$bagian['id']}, Unsur ID {$bagian['id_unsur']}, Unsur {$bagian['nama_unsur']}\n";
            break;
        }
    }
    
    if (!$bkoFound) {
        echo "  BKO TIDAK ditemukan di bagianData!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
