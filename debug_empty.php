<?php
// Test empty() behavior
echo "=== TESTING EMPTY() BEHAVIOR ===\n\n";

// Simulate bagianByUnsur[6] with BKO data
$bagianByUnsur[6] = [
    [
        'id' => 29,
        'nama_bagian' => 'BKO',
        'id_unsur' => 6,
        'urutan' => 1,
        'type' => 'BKO'
    ]
];

echo "bagianByUnsur[6]:\n";
var_dump($bagianByUnsur[6]);

echo "\nEmpty checks:\n";
echo "isset(\$bagianByUnsur[6]): " . (isset($bagianByUnsur[6]) ? 'true' : 'false') . "\n";
echo "!empty(\$bagianByUnsur[6]): " . (!empty($bagianByUnsur[6]) ? 'true' : 'false') . "\n";
echo "count(\$bagianByUnsur[6]): " . count($bagianByUnsur[6]) . "\n";

// Test with actual data from database
require_once __DIR__ . '/core/config.php';
$pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("
    SELECT b.*, u.nama_unsur 
    FROM bagian b 
    LEFT JOIN unsur u ON b.id_unsur = u.id 
    WHERE b.id_unsur = 6
");
$actualData = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nActual database data:\n";
var_dump($actualData);

echo "\nEmpty checks on actual data:\n";
echo "isset(\$actualData): " . (isset($actualData) ? 'true' : 'false') . "\n";
echo "!empty(\$actualData): " . (!empty($actualData) ? 'true' : 'false') . "\n";
echo "count(\$actualData): " . count($actualData) . "\n";

// Test each field
if (!empty($actualData)) {
    foreach ($actualData as $index => $bagian) {
        echo "\nBagian [$index]:\n";
        foreach ($bagian as $field => $value) {
            echo "  $field: " . var_export($value, true) . " (empty: " . (empty($value) ? 'true' : 'false') . ")\n";
        }
    }
}
?>
