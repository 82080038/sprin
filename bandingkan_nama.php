<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

$pdfFile = 'SPRIN INDUK KRYD KETUPAT TOBA - 2026 DI WILKUM POLRES SAMOSIR.pdf';
$docxFile = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';

echo "<h2>Perbandingan Personil POLSEK PANGURURAN</h2>";
echo "<p style='color:#666;'>Membandingkan SPRIN INDUK vs SPRIN STRONG POINT (hanya untuk POLSEK PANGURURAN)</p>";

// Parse PDF
$parser = new Parser();
$pdf = $parser->parseFile($pdfFile);
$pdfText = $pdf->getText();

// Parse DOCX
$phpWord = IOFactory::load($docxFile);
$docxText = '';
foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if (is_string($text)) $docxText .= $text . "\n";
            elseif (is_array($text)) {
                foreach ($text as $t) if (is_string($t)) $docxText .= $t . "\n";
            }
        }
    }
}

// Extract names from specific section - POLSEK PANGURURAN only
function extractPersonilNamesFromSection($text, $sectionName) {
    $names = [];
    $lines = explode("\n", $text);
    $inTargetSection = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Check if we hit the target section
        if (stripos($line, $sectionName) !== false) {
            $inTargetSection = true;
            continue;
        }
        
        // Check if we hit another POLSEK section (to stop collecting)
        if ($inTargetSection && preg_match('/^(POLSEK\s|HARIAN\s)/i', $line)) {
            break;
        }
        
        // Only extract names if we're in the target section
        if (!$inTargetSection) continue;
        
        // Pattern: nama diikuti pangkat
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

// Extract only POLSEK PANGURURAN personnel
$pdfNames = extractPersonilNamesFromSection($pdfText, 'POLSEK PANGURURAN');
$docxNames = extractPersonilNamesFromSection($docxText, 'POLSEK PANGURURAN');

echo "<p>Total nama di PDF (SPRIN INDUK): <b>" . count($pdfNames) . "</b></p>";
echo "<p>Total nama di DOCX (SPRIN STRONG POINT): <b>" . count($docxNames) . "</b></p>";

// Find names in PDF but not in DOCX
$onlyInPdf = array_diff($pdfNames, $docxNames);
$onlyInDocx = array_diff($docxNames, $pdfNames);

echo "<h3 style='color:red'>Personil di SPRIN INDUK (PDF) tapi TIDAK di SPRIN STRONG POINT (DOCX):</h3>";
if (empty($onlyInPdf)) {
    echo "<p style='color:green'><b>Tidak ada - semua personil di SPRIN INDUK juga ada di SPRIN STRONG POINT</b></p>";
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

echo "<h3 style='color:blue'>Personil di SPRIN STRONG POINT (DOCX) tapi TIDAK di SPRIN INDUK (PDF):</h3>";
if (empty($onlyInDocx)) {
    echo "<p style='color:green'><b>Tidak ada - semua personil di SPRIN STRONG POINT juga ada di SPRIN INDUK</b></p>";
} else {
    echo "<p><b>" . count($onlyInDocx) . " personil:</b></p>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr style='background:#ccffff'><th>NO</th><th>NAMA</th></tr>";
    $no = 1;
    foreach ($onlyInDocx as $name) {
        echo "<tr><td>$no</td><td>$name</td></tr>";
        $no++;
    }
    echo "</table>";
}

echo "<h3>Semua Nama di SPRIN INDUK (PDF):</h3>";
echo "<ol style='font-size:12px'>";
foreach ($pdfNames as $name) {
    $inDocx = in_array($name, $docxNames) ? " <span style='color:green'>✓</span>" : " <span style='color:red'>✗ (tidak di DOCX)</span>";
    echo "<li>$name$inDocx</li>";
}
echo "</ol>";
