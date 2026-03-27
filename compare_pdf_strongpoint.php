<?php
require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

$pdfFile = 'SPRIN INDUK KRYD KETUPAT TOBA - 2026 DI WILKUM POLRES SAMOSIR.pdf';
$jsonFile = 'STRONG_POINT_ALL.json';

echo "<h2>Personil di SPRIN INDUK tapi TIDAK di STRONG_POINT_ALL.json</h2>";

// Load JSON names
$json = json_decode(file_get_contents($jsonFile), true);
$jsonNames = [];
foreach ($json['pimpinan'] as $p) {
    $jsonNames[] = strtoupper(preg_replace('/\s+/', ' ', trim($p['nama'])));
}
foreach ($json['bagian'] as $bag) {
    foreach ($bag['personil'] as $p) {
        $jsonNames[] = strtoupper(preg_replace('/\s+/', ' ', trim($p['nama'])));
    }
}
$jsonNames = array_unique($jsonNames);

// Parse PDF page 2+
$parser = new Parser();
$pdf = $parser->parseFile($pdfFile);
$pages = $pdf->getPages();
$pdfText = '';
for ($i = 1; $i < count($pages); $i++) {
    $pdfText .= $pages[$i]->getText() . "\n";
}

// Extract names from PDF
function extractNames($text) {
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

$pdfNames = extractNames($pdfText);

// Find names in PDF but not in JSON
$onlyInPdf = array_diff($pdfNames, $jsonNames);

echo "<p>Total di PDF (hal 2+): <b>" . count($pdfNames) . "</b></p>";
echo "<p>Total di STRONG_POINT_ALL.json: <b>" . count($jsonNames) . "</b></p>";

echo "<h3 style='color:red'>Personil di SPRIN INDUK tapi TIDAK di STRONG POINT:</h3>";
if (empty($onlyInPdf)) {
    echo "<p style='color:green'><b>Tidak ada - semua personil di SPRIN INDUK juga ada di STRONG POINT</b></p>";
} else {
    echo "<p><b>" . count($onlyInPdf) . " personil:</b></p>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr style='background:#ffcccc'><th>NO</th><th>NAMA</th></tr>";
    $no = 1;
    foreach ($onlyInPdf as $name) {
        echo "<tr><td>$no</td><td>$name</td></tr>";
        $no++;
    }
    echo "</table>";
}
