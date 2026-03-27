<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$file = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';

if (!file_exists($file)) {
    die("File tidak ditemukan: $file");
}

echo "<h2>Isi File DOCX</h2>";
echo "<pre style='background:#f5f5f5;padding:20px;border-radius:5px;white-space:pre-wrap;'>";

$phpWord = IOFactory::load($file);
$sections = $phpWord->getSections();

foreach ($sections as $section) {
    $elements = $section->getElements();
    foreach ($elements as $element) {
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if (is_string($text)) {
                echo htmlspecialchars($text) . "\n";
            } elseif (is_array($text)) {
                foreach ($text as $t) {
                    if (is_string($t)) {
                        echo htmlspecialchars($t);
                    }
                }
                echo "\n";
            }
        }
    }
}

echo "</pre>";
echo "<p><b>File berhasil dibaca!</b></p>";
