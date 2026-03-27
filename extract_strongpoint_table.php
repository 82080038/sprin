<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$docxFile = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';

echo "<h2>Extract Personil dari SPRIN STRONG POINT (Table Structure)</h2>";

$phpWord = IOFactory::load($docxFile);

$result = [
    'pimpinan' => [],
    'bagian' => []
];

$currentBagian = null;
$inTable = false;

foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        // Check for table
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $inTable = true;
            
            foreach ($element->getRows() as $row) {
                $cells = $row->getCells();
                $cellTexts = [];
                
                foreach ($cells as $cell) {
                    $cellText = '';
                    foreach ($cell->getElements() as $cellElement) {
                        if (method_exists($cellElement, 'getText')) {
                            $t = $cellElement->getText();
                            if (is_string($t)) $cellText .= $t;
                            elseif (is_array($t)) $cellText .= implode('', $t);
                        }
                    }
                    $cellTexts[] = trim($cellText);
                }
                
                // Debug: show first few rows
                if (count($cellTexts) >= 3) {
                    echo "<pre style='font-size:10px;margin:2px 0;'>" . implode(' | ', $cellTexts) . "</pre>";
                }
                
                // Check if this is a section header row (BAG, SAT, POLSEK)
                if (count($cellTexts) > 0) {
                    $firstCell = $cellTexts[0] ?? '';
                    if (preg_match('/^(BAG\s|SAT\s|POLSEK\s|SIUM|SPKT|HARIAN\s)/i', $firstCell)) {
                        $result['bagian'][] = [
                            'nama_bagian' => $firstCell,
                            'personil' => []
                        ];
                        $currentBagian = &$result['bagian'][count($result['bagian']) - 1];
                        continue;
                    }
                }
                
                // Check if this is a personil row (has NRP pattern)
                $rowText = implode(' ', $cellTexts);
                if (preg_match('/(\d{8,12})/', $rowText, $m)) {
                    $nrp = $m[1];
                    
                    // Try to extract other data from cells
                    $nama = '';
                    $pangkat = '';
                    $jabatan = '';
                    $ket = '';
                    
                    foreach ($cellTexts as $i => $text) {
                        $text = trim($text);
                        if (empty($text)) continue;
                        
                        // Check for pangkat
                        if (preg_match('/(AKBP|KOMPOL|AKP|IPTU|IPDA|AIPTU|AIPDA|BRIPKA|BRIGPOL|BRIPTU|BRIPDA)/i', $text)) {
                            $pangkat = $text;
                        }
                        // Check for NRP
                        elseif (preg_match('/^\d{8,12}$/', $text)) {
                            $nrp = $text;
                        }
                        // Check for KET (single letter A/B/C)
                        elseif (preg_match('/^[ABC]$/', $text)) {
                            $ket = $text;
                        }
                        // Check for jabatan keywords
                        elseif (preg_match('/(KANIT|KAPOLSEK|KASAT|KABAG|BAMIN|PS\.|ANGGOTA)/i', $text)) {
                            $jabatan = $text;
                        }
                        // Otherwise likely nama (longest text with uppercase)
                        elseif (strlen($text) > 5 && preg_match('/[A-Z]{3,}/', $text)) {
                            if (strlen($text) > strlen($nama)) {
                                $nama = $text;
                            }
                        }
                    }
                    
                    if (!empty($nama) && $currentBagian !== null) {
                        $currentBagian['personil'][] = [
                            'nama' => $nama,
                            'nrp' => $nrp,
                            'pangkat' => $pangkat,
                            'jabatan' => $jabatan,
                            'ket' => $ket
                        ];
                    }
                }
            }
        }
    }
}

// Remove empty bagian
$result['bagian'] = array_filter($result['bagian'], function($b) {
    return count($b['personil']) > 0;
});

// Save to JSON
$outputFile = 'STRONG_POINT_ALL.json';
file_put_contents($outputFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "<hr><p>File tersimpan: <b>$outputFile</b></p>";
echo "<p>Jumlah Bagian: " . count($result['bagian']) . "</p>";

$totalPersonil = 0;
foreach ($result['bagian'] as $bag) {
    $totalPersonil += count($bag['personil']);
}
echo "<p>Total Personil: $totalPersonil</p>";

echo "<h3>Preview JSON:</h3>";
echo "<pre style='max-height:400px;overflow:auto;font-size:10px;border:1px solid #ccc;padding:10px;'>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
