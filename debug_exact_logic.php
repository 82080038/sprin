<?php
// Debug script yang persis sama dengan bagian.php
require_once __DIR__ . '/core/config.php';

session_start();

// Bypass auth for testing
$_SESSION['logged_in'] = true;
$_SESSION['username'] = 'Debug User';
$_SESSION['user_id'] = 1;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "=== EXACT BAGIAN.PHP LOGIC DEBUG ===\n\n";

// Get unsur data (exact same as bagian.php)
$stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
$unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Unsur Data:\n";
foreach ($unsurData as $unsur) {
    echo "  ID: {$unsur['id']}, Nama: {$unsur['nama_unsur']}, Urutan: {$unsur['urutan']}\n";
}

// Check if urutan column exists in bagian table
$columnCheck = $pdo->query("SHOW COLUMNS FROM bagian LIKE 'urutan'");
$hasUrutanColumn = $columnCheck->rowCount() > 0;

echo "\nHas urutan column: " . ($hasUrutanColumn ? 'YES' : 'NO') . "\n";

// Get bagian data with unsur info using proper ordering (exact same as bagian.php)
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

echo "\nTotal bagian records: " . count($bagianData) . "\n";

// Ensure urutan field exists for all records (exact same as bagian.php)
foreach ($bagianData as &$bagian) {
    if (!isset($bagian['urutan'])) {
        $bagian['urutan'] = 0;
    }
}

// Add type field based on bagian name (exact same as bagian.php)
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

// Group bagian by unsur (exact same as bagian.php)
$bagianByUnsur = [];
foreach ($bagianData as $bagian) {
    $unsurId = $bagian['id_unsur'];
    if (!isset($bagianByUnsur[$unsurId])) {
        $bagianByUnsur[$unsurId] = [];
    }
    $bagianByUnsur[$unsurId][] = $bagian;
}

echo "\n=== FINAL RESULT ===\n";
foreach ($unsurData as $unsur) {
    $unsurId = $unsur['id'];
    $bagianCount = isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0;
    echo "Unsur: {$unsur['nama_unsur']} (ID: {$unsurId}) - {$bagianCount} bagian\n";
    
    if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
        echo "  Bagians:\n";
        if (isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId])) {
            foreach ($bagianByUnsur[$unsurId] as $bagian) {
                echo "    - {$bagian['nama_bagian']} (ID: {$bagian['id']}, Type: {$bagian['type']})\n";
            }
        } else {
            echo "    (No bagians)\n";
        }
    }
}

echo "\n=== HTML CONDITION CHECK ===\n";
foreach ($unsurData as $unsur) {
    if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
        $unsurId = $unsur['id'];
        $hasBagians = isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId]);
        echo "UNSUR LAINNYA (ID: {$unsurId}):\n";
        echo "  isset(\$bagianByUnsur[{$unsurId}]): " . (isset($bagianByUnsur[$unsurId]) ? 'true' : 'false') . "\n";
        echo "  !empty(\$bagianByUnsur[{$unsurId}]): " . (!empty($bagianByUnsur[$unsurId]) ? 'true' : 'false') . "\n";
        echo "  Final condition (isset && !empty): " . ($hasBagians ? 'true' : 'false') . "\n";
        echo "  Bagian count: " . (isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0) . "\n";
        break;
    }
}

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
