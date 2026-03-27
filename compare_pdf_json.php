<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pdfFile = 'SPRIN INDUK KRYD KETUPAT TOBA - 2026 DI WILKUM POLRES SAMOSIR.pdf';
$jsonFile = 'PERSONIL_ALL.json';

echo "<h2>Perbandingan SPRIN INDUK (Halaman 2+) vs PERSONIL_ALL.json</h2>";

// Load JSON
if (!file_exists($jsonFile)) {
    die("File $jsonFile tidak ditemukan");
}
$json = json_decode(file_get_contents($jsonFile), true);

// Build name list from JSON
$jsonNames = [];
foreach ($json['pimpinan'] as $p) {
    $jsonNames[] = strtoupper(preg_replace('/\s+/', ' ', trim($p['nama'])));
}
foreach ($json['bagian'] as $bagian) {
    foreach ($bagian['personil'] as $p) {
        $jsonNames[] = strtoupper(preg_replace('/\s+/', ' ', trim($p['nama'])));
    }
}
$jsonNames = array_unique($jsonNames);

// Parse PDF - extract from page 2 onwards
$parser = new Parser();
$pdf = $parser->parseFile($pdfFile);

// Get text from page 2 onwards
$pages = $pdf->getPages();
$pdfText = '';
$pageCount = count($pages);

echo "<p>Total halaman PDF: <b>$pageCount</b></p>";
echo "<p>Mengambil teks dari halaman 2 sampai akhir...</p>";

for ($i = 1; $i < $pageCount; $i++) { // Start from page 2 (index 1)
    $pdfText .= $pages[$i]->getText() . "\n";
}

// Extract names - same pattern as before
function extractPersonilNamesFromText($text) {
    $names = [];
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^([A-Z][A-Z\s\-\.,]+?(?:S\.H\.|S\.E\.|S\.KOM\.|S\.T\.|M\.H\.|,\s*S\.H\.|,\s*S\.E\.)?)\s+(BRIPDA|BRIPTU|BRIGPOL|BRIPKA|AIPDA|AIPTU|IPDA|IPTU|AKP|KOMPOL|AKBP)/i', $line, $m)) {
            $name = trim($m[1]);
            if (strlen($name) > 5 && !preg_match('/^(KANIT|KAPOLSEK|KABAG|WAKAPOLRES|KAPOLRES|BAMIN|PS\.|ANGGOTA)/i', $name)) {
                $names[] = strtoupper(preg_replace('/\s+/', ' ', $name));
            }
        }
        elseif (preg_match('/^([A-Z][A-Z\s\-,]+),?\s+(BRIPDA|BRIPTU|BRIGPOL|BRIPKA|AIPDA|AIPTU|IPDA|IPTU|AKP)/i', $line, $m)) {
            $name = trim($m[1]);
            if (strlen($name) > 5 && !preg_match('/^(KANIT|KAPOLSEK|KABAG|WAKAPOLRES|KAPOLRES|BAMIN|PS\.|ANGGOTA)/i', $name)) {
                $names[] = strtoupper(preg_replace('/\s+/', ' ', $name));
            }
        }
    }
    return array_unique($names);
}

$pdfNames = extractPersonilNamesFromText($pdfText);

echo "<p>Total nama di PDF (halaman 2+): <b>" . count($pdfNames) . "</b></p>";
echo "<p>Total nama di PERSONIL_ALL.json: <b>" . count($jsonNames) . "</b></p>";

// Find names in PDF but not in JSON
$onlyInPdf = array_diff($pdfNames, $jsonNames);

echo "<h3 style='color:red'>Personil di PDF (halaman 2+) tapi TIDAK di PERSONIL_ALL.json:</h3>";
if (empty($onlyInPdf)) {
    echo "<p style='color:green'><b>Tidak ada - semua personil di PDF juga ada di JSON</b></p>";
} else {
    echo "<p><b>" . count($onlyInPdf) . " personil:</b></p>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr style='background:#ffcccc'><th>NO</th><th>NAMA</th></tr>";
    $no = 1;
    foreach ($onlyInPdf as $name) {
        echo "<tr><td>$no</td><td><b>$name</b></td></tr>";
        $no++;
    }
    echo "</table>";
}

// Also show matches
$matches = array_intersect($pdfNames, $jsonNames);
echo "<h3 style='color:green'>Personil yang cocok di kedua sumber:</h3>";
echo "<p><b>" . count($matches) . " personil cocok</b></p>";

echo "<h3>Semua nama di PDF (halaman 2+):</h3>";
echo "<ol style='font-size:12px;max-height:400px;overflow:auto;border:1px solid #ccc;padding:10px;'>";
foreach ($pdfNames as $name) {
    $inJson = in_array($name, $jsonNames) ? " <span style='color:green'>✓ (ada di JSON)</span>" : " <span style='color:red'>✗ (tidak di JSON)</span>";
    echo "<li>$name$inJson</li>";
}
echo "</ol>";
