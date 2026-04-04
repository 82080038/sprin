<?php
declare(strict_types=1);
// Simple API to display UNSUR data in terminal
require_once __DIR__ . '/../core/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DATA UNSUR ===\n\n";
    
    // Get all unsur data
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Unsur: " . count($unsurData) . "\n\n";
    
    foreach ($unsurData as $unsur) {
        echo "ID: {$unsur['id']}\n";
        echo "Kode: {$unsur['kode_unsur']}\n";
        echo "Nama: {$unsur['nama_unsur']}\n";
        echo "Urutan: {$unsur['urutan']}\n";
        echo "Deskripsi: " . ($unsur['deskripsi'] ?? 'N/A') . "\n";
        
        // Get bagian count for this unsur
        $stmt2 = $pdo->prepare("SELECT COUNT(*) as total FROM bagian WHERE id_unsur = ?");
        $stmt2->execute([$unsur['id']]);
        $bagianCount = $stmt2->fetch()['total'];
        
        echo "Total Bagian: $bagianCount\n";
        
        // Get bagian list
        if ($bagianCount > 0) {
            $stmt3 = $pdo->prepare("SELECT nama_bagian, kode_bagian, urutan FROM bagian WHERE id_unsur = ? ORDER BY urutan, nama_bagian");
            $stmt3->execute([$unsur['id']]);
            $bagians = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Daftar Bagian:\n";
            foreach ($bagians as $bagian) {
                echo "  - {$bagian['nama_bagian']} ({$bagian['kode_bagian']}) - Urutan: {$bagian['urutan']}\n";
            }
        }
        
        echo str_repeat("-", 50) . "\n";
    }
    
    echo "\n=== STATISTIK UNSUR ===\n";
    
    // Get statistics
    $stmt = $pdo->query("
        SELECT 
            u.nama_unsur,
            COUNT(b.id) as total_bagian,
            COUNT(p.id) as total_personil
        FROM unsur u
        LEFT JOIN bagian b ON u.id = b.id_unsur
        LEFT JOIN personil p ON b.id = p.id_bagian
        GROUP BY u.id, u.nama_unsur
        ORDER BY u.urutan
    ");
    
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nFormat: | Nama Unsur | Total Bagian | Total Personil |\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($stats as $stat) {
        printf("| %-20s | %-12s | %-12s |\n", 
            $stat['nama_unsur'], 
            $stat['total_bagian'], 
            $stat['total_personil']
        );
    }
    echo str_repeat("-", 60) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
