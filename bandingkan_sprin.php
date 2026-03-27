<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

// Parse PDF
$pdfFile = 'SPRIN INDUK KRYD KETUPAT TOBA - 2026 DI WILKUM POLRES SAMOSIR.pdf';
$docxFile = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';

echo "<h2>Perbandingan Daftar Personil SPRIN</h2>";

// Extract from PDF
$parser = new Parser();
$pdf = $parser->parseFile($pdfFile);
$pdfText = $pdf->getText();

echo "<h3>PDF - Full Text (all lines with numbers):</h3>";
echo "<pre style='font-size:9px;max-height:400px;overflow:auto;border:1px solid #ccc;padding:10px;'>";
$lines = explode("\n", $pdfText);
$lineNum = 1;
foreach ($lines as $line) {
    $line = trim($line);
    if (!empty($line) && (preg_match('/\d{5,}/', $line) || stripos($line, 'NRP') !== false || stripos($line, 'kapolsek') !== false || stripos($line, 'kanit') !== false || stripos($line, 'bamin') !== false)) {
        echo sprintf("%3d", $lineNum) . ": " . htmlspecialchars(substr($line, 0, 150)) . "\n";
    }
    $lineNum++;
}
echo "</pre>";

// Extract from DOCX
$phpWord = IOFactory::load($docxFile);
$docxText = '';
foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if (is_string($text)) {
                $docxText .= $text . "\n";
            } elseif (is_array($text)) {
                foreach ($text as $t) {
                    if (is_string($t)) $docxText .= $t . "\n";
                }
            }
        }
    }
}

echo "<h3>DOCX - Full Text (all lines with numbers):</h3>";
echo "<pre style='font-size:9px;max-height:400px;overflow:auto;border:1px solid #ccc;padding:10px;'>";
$lines = explode("\n", $docxText);
$lineNum = 1;
foreach ($lines as $line) {
    $line = trim($line);
    if (!empty($line) && (preg_match('/\d{5,}/', $line) || stripos($line, 'NRP') !== false || stripos($line, 'kapolsek') !== false || stripos($line, 'kanit') !== false || stripos($line, 'bamin') !== false)) {
        echo sprintf("%3d", $lineNum) . ": " . htmlspecialchars(substr($line, 0, 150)) . "\n";
    }
    $lineNum++;
}
echo "</pre>";
