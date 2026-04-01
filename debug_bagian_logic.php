<?php
// Debug script khusus untuk bagian.php
require_once __DIR__ . '/core/config.php';

// Include bagian.php logic
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DEBUG BAGIAN.PHP LOGIC ===\n\n";
    
    // 1. Check if urutan column exists
    $columnCheck = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
    $hasUrutanColumn = $columnCheck->rowCount() > 0;
    echo "Has urutan column: " . ($hasUrutanColumn ? 'YES' : 'NO') . "\n\n";
    
    // 2. Get bagian data using exact same query as bagian.php
    if ($hasUrutanColumn) {
        $stmt = $pdo->query("
            SELECT b.*, u.nama_unsur 
            FROM bagian b 
            LEFT JOIN unsur u ON b.id_unsur = u.id 
            ORDER BY u.urutan, b.urutan, b.nama_bagian
        ");
    } else {
        $stmt = $pdo->query("
            SELECT b.*, u.nama_unsur 
            FROM bagian b 
            LEFT JOIN unsur u ON b.id_unsur = u.id 
            ORDER BY u.urutan, b.nama_bagian
        ");
    }
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total bagian records: " . count($bagianData) . "\n\n";
    
    // 3. Show all records for UNSUR LAINNYA
    echo "Bagian records for UNSUR LAINNYA:\n";
    $lainnyaBagians = [];
    foreach ($bagianData as $bagian) {
        if ($bagian['nama_unsur'] === 'UNSUR LAINNYA' || $bagian['id_unsur'] == 6) {
            echo "  ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Unsur ID: {$bagian['id_unsur']}, Unsur: {$bagian['nama_unsur']}, Urutan: {$bagian['urutan']}\n";
            $lainnyaBagians[] = $bagian;
        }
    }
    
    // 4. Ensure urutan field exists (same as bagian.php)
    foreach ($bagianData as &$bagian) {
        if (!isset($bagian['urutan'])) {
            $bagian['urutan'] = 0;
            echo "Fixed missing urutan for ID {$bagian['id']}\n";
        }
    }
    
    // 5. Add type field (same as bagian.php)
    foreach ($bagianData as &$bagian) {
        if (strpos($bagian['nama_bagian'], 'PIMPINAN') !== false) {
            $bagian['type'] = 'PIMPINAN';
        } elseif (strpos($bagian['nama_bagian'], 'BAG_') !== false) {
            $bagian['type'] = 'BAG';
        } elseif (strpos($bagian['nama_bagian'], 'SAT_') !== false) {
            $bagian['type'] = 'SAT';
        } elseif (strpos($bagian['nama_bagian'], 'POLSEK') !== false) {
            $bagian['type'] = 'POLSEK';
        } elseif (strpos($bagian['nama_bagian'], 'SPKT') !== false) {
            $bagian['type'] = 'SPKT';
        } elseif (strpos($bagian['nama_bagian'], 'SIUM') !== false) {
            $bagian['type'] = 'SIUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIKEU') !== false) {
            $bagian['type'] = 'SIKEU';
        } elseif (strpos($bagian['nama_bagian'], 'SIDOKKES') !== false) {
            $bagian['type'] = 'SIDOKKES';
        } elseif (strpos($bagian['nama_bagian'], 'SIWAS') !== false) {
            $bagian['type'] = 'SIWAS';
        } elseif (strpos($bagian['nama_bagian'], 'SITIK') !== false) {
            $bagian['type'] = 'SITIK';
        } elseif (strpos($bagian['nama_bagian'], 'SIKUM') !== false) {
            $bagian['type'] = 'SIKUM';
        } elseif (strpos($bagian['nama_bagian'], 'SIPROPAM') !== false) {
            $bagian['type'] = 'SIPROPAM';
        } elseif (strpos($bagian['nama_bagian'], 'SIHUMAS') !== false) {
            $bagian['type'] = 'SIHUMAS';
        } elseif (strpos($bagian['nama_bagian'], 'BKO') !== false) {
            $bagian['type'] = 'BKO';
        } else {
            $bagian['type'] = 'LAINNYA';
        }
    }
    
    // 6. Group bagian by unsur (same as bagian.php)
    $bagianByUnsur = [];
    foreach ($bagianData as $bagian) {
        $unsurId = $bagian['id_unsur'];
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
        }
        $bagianByUnsur[$unsurId][] = $bagian;
    }
    
    echo "\nBagian grouped by unsur:\n";
    foreach ($bagianByUnsur as $unsurId => $bagians) {
        echo "  Unsur ID $unsurId: " . count($bagians) . " bagian\n";
        if ($unsurId == 6) {
            foreach ($bagians as $bagian) {
                echo "    - ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Type: {$bagian['type']}, Urutan: {$bagian['urutan']}\n";
            }
        }
    }
    
    // 7. Check specific for UNSUR LAINNYA (ID 6)
    echo "\n=== FINAL CHECK FOR UNSUR LAINNYA (ID 6) ===\n";
    if (isset($bagianByUnsur[6])) {
        echo "✅ bagianByUnsur[6] exists with " . count($bagianByUnsur[6]) . " bagian:\n";
        foreach ($bagianByUnsur[6] as $bagian) {
            echo "  - ID: {$bagian['id']}, Nama: {$bagian['nama_bagian']}, Type: {$bagian['type']}, Urutan: {$bagian['urutan']}\n";
        }
    } else {
        echo "❌ bagianByUnsur[6] does NOT exist!\n";
    }
    
    // 8. Check if BKO is in the final result
    $bkoFound = false;
    foreach ($bagianByUnsur as $unsurId => $bagians) {
        foreach ($bagians as $bagian) {
            if ($bagian['nama_bagian'] === 'BKO') {
                $bkoFound = true;
                echo "✅ BKO found in bagianByUnsur[$unsurId] with ID {$bagian['id']}\n";
                break 2;
            }
        }
    }
    
    if (!$bkoFound) {
        echo "❌ BKO NOT found in bagianByUnsur array!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
