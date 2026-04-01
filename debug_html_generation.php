<?php
// Minimal reproduction of bagian.php HTML generation
require_once __DIR__ . '/core/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Content-Type: text/html\n\n";
echo "<!DOCTYPE html>\n<html>\n<head><title>Debug BKO</title></head>\n<body>\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>DEBUG BKO HTML GENERATION</h1>\n";
    
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
    
    echo "<h2>PHP Logic Results:</h2>\n";
    echo "<pre>\n";
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            $unsurId = $unsur['id'];
            $bagianCount = isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0;
            echo "UNSUR LAINNYA (ID: {$unsurId}): {$bagianCount} bagian\n";
            
            if (isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId])) {
                foreach ($bagianByUnsur[$unsurId] as $bagian) {
                    echo "  - {$bagian['nama_bagian']} (ID: {$bagian['id']})\n";
                }
            }
            break;
        }
    }
    echo "</pre>\n";
    
    echo "<h2>HTML Generation Test:</h2>\n";
    
    // Simulate exact HTML generation from bagian.php
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            echo "<div class='unsur-card' data-unsur-id='{$unsur['id']}'>\n";
            echo "  <h6>{$unsur['nama_unsur']}</h6>\n";
            echo "  <span class='badge'>" . count($bagianByUnsur[$unsur['id']] ?? []) . "</span>\n";
            echo "  <div class='bagian-list'>\n";
            
            if (isset($bagianByUnsur[$unsur['id']]) && !empty($bagianByUnsur[$unsur['id']])) {
                foreach ($bagianByUnsur[$unsur['id']] as $bagian) {
                    echo "    <div class='bagian-item' data-id='{$bagian['id']}'>\n";
                    echo "      <span class='bagian-name'>{$bagian['nama_bagian']}</span>\n";
                    echo "    </div>\n";
                }
            } else {
                echo "    <div class='empty-message'>No bagians</div>\n";
            }
            
            echo "  </div>\n";
            echo "</div>\n";
            break;
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>\n";
    echo "<pre>" . $e->getMessage() . "</pre>\n";
}

echo "</body>\n</html>\n";
?>
