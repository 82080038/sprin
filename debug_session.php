<?php
// Test session dan auth bypass effect
require_once __DIR__ . '/core/config.php';

// Start session
session_start();

// Set test session like real app
$_SESSION['logged_in'] = true;
$_SESSION['username'] = 'Test User';
$_SESSION['user_id'] = 1;

echo "=== SESSION & AUTH TEST ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Logged in: " . ($_SESSION['logged_in'] ? 'true' : 'false') . "\n";
echo "Username: " . $_SESSION['username'] . "\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simulate exact bagian.php logic with session
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
    
    echo "With Session:\n";
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            $unsurId = $unsur['id'];
            $bagianCount = isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0;
            echo "  UNSUR LAINNYA (ID: {$unsurId}): {$bagianCount} bagian\n";
            
            if (isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId])) {
                foreach ($bagianByUnsur[$unsurId] as $bagian) {
                    echo "    - {$bagian['nama_bagian']} (ID: {$bagian['id']})\n";
                }
            }
            break;
        }
    }
    
    // Clear session and test again
    session_unset();
    session_destroy();
    
    echo "\nWithout Session:\n";
    // Restart connection to simulate fresh state
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM unsur ORDER BY urutan");
    $unsurData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT b.*, u.nama_unsur 
        FROM bagian b 
        LEFT JOIN unsur u ON b.id_unsur = u.id 
        ORDER BY u.urutan, b.urutan, b.nama_bagian
    ");
    $bagianData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $bagianByUnsur = [];
    foreach ($bagianData as $bagian) {
        $unsurId = $bagian['id_unsur'];
        if (!isset($bagianByUnsur[$unsurId])) {
            $bagianByUnsur[$unsurId] = [];
        }
        $bagianByUnsur[$unsurId][] = $bagian;
    }
    
    foreach ($unsurData as $unsur) {
        if ($unsur['nama_unsur'] === 'UNSUR LAINNYA') {
            $unsurId = $unsur['id'];
            $bagianCount = isset($bagianByUnsur[$unsurId]) ? count($bagianByUnsur[$unsurId]) : 0;
            echo "  UNSUR LAINNYA (ID: {$unsurId}): {$bagianCount} bagian\n";
            
            if (isset($bagianByUnsur[$unsurId]) && !empty($bagianByUnsur[$unsurId])) {
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
