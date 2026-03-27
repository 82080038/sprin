<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$docxFile = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';
$jsonGuideFile = 'PERSONIL_ALL.json';

echo "<h2>Extract Personil dari SPRIN STRONG POINT ke JSON</h2>";

// Load reference JSON to see structure
$guide = json_decode(file_get_contents($jsonGuideFile), true);

// Parse DOCX
$phpWord = IOFactory::load($docxFile);

// Extract text with structure
$text = '';
$lines = [];
foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        if (method_exists($element, 'getText')) {
            $t = $element->getText();
            if (is_string($t)) {
                $text .= $t . "\n";
                $lines[] = $t;
            } elseif (is_array($t)) {
                foreach ($t as $txt) {
                    if (is_string($txt)) {
                        $text .= $txt . "\n";
                        $lines[] = $txt;
                    }
                }
            }
        }
    }
}

// Structure data like PERSONIL_ALL.json
$result = [
    'pimpinan' => [],
    'bagian' => []
];

$currentBagian = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    // Check for section headers (BAG, SAT, POLSEK, etc.)
    if (preg_match('/^(BAG\s|SAT\s|POLSEK\s|SIUM|SPKT|PAMAPTA|INTEL|PROVAM|HARIAN\s)/i', $line)) {
        $result['bagian'][] = [
            'nama_bagian' => $line,
            'personil' => []
        ];
        $currentBagian = &$result['bagian'][count($result['bagian']) - 1];
        continue;
    }
    
    // Check for pimpinan (KAPOLRES, WAKAPOLRES)
    if (preg_match('/(KAPOLRES|WAKAPOLRES).*?\s+(AKBP|KOMPOL).*?\s+(\d{8,})/i', $line, $m)) {
        // Try to parse pimpinan line
        if (preg_match('/^([A-Z].*?)\s+(AKBP|KOMPOL).*?\s+(\d{8,})/i', $line, $match)) {
            $nama = trim($match[1]);
            $pangkat = $match[2];
            $nrp = $match[3];
            $jabatan = stripos($line, 'WAKAPOLRES') !== false ? 'WAKAPOLRES' : 'KAPOLRES';
            
            $result['pimpinan'][] = [
                'nama' => $nama,
                'nrp' => $nrp,
                'pangkat' => $pangkat,
                'jabatan' => $jabatan,
                'ket' => ''
            ];
        }
        continue;
    }
    
    // Parse personil line: NAMA PANGKAT NRP JABATAN
    // Pattern: Nama dengan gelar, pangkat, NRP 8-12 digit
    if (preg_match('/^([A-Z][A-Z\s\-\.,]+?(?:S\.H\.|S\.E\.|S\.KOM\.|S\.T\.|M\.H\.|,\s*S\.H\.|,\s*S\.E\.)?)\s+(BRIPDA|BRIPTU|BRIGPOL|BRIPKA|AIPDA|AIPTU|IPDA|IPTU|AKP|KOMPOL|AKBP)\s+(\d{8,12})/i', $line, $m)) {
        $nama = trim($m[1]);
        $pangkat = $m[2];
        $nrp = $m[3];
        
        // Extract jabatan (remaining text after NRP)
        $afterNrp = substr($line, strpos($line, $nrp) + strlen($nrp));
        $jabatan = trim($afterNrp);
        $ket = '';
        
        // Check for KET (A, B, C, etc.) at the end
        if (preg_match('/\s+([ABC])\s*$/', $jabatan, $km)) {
            $ket = $km[1];
            $jabatan = trim(substr($jabatan, 0, -strlen($km[0])));
        }
        
        // Filter out invalid names
        if (strlen($nama) > 5 && !preg_match('/^(KANIT|KAPOLSEK|KABAG|WAKAPOLRES|KAPOLRES|BAMIN|PS\.|ANGGOTA|DIPERINTAHKAN)/i', $nama)) {
            $personil = [
                'nama' => $nama,
                'nrp' => $nrp,
                'pangkat' => $pangkat,
                'jabatan' => $jabatan,
                'ket' => $ket
            ];
            
            if ($currentBagian !== null) {
                $currentBagian['personil'][] = $personil;
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

echo "<p>File tersimpan: <b>$outputFile</b></p>";
echo "<p>Jumlah Pimpinan: " . count($result['pimpinan']) . "</p>";
echo "<p>Jumlah Bagian: " . count($result['bagian']) . "</p>";

$totalPersonil = 0;
foreach ($result['bagian'] as $bag) {
    $totalPersonil += count($bag['personil']);
}
echo "<p>Total Personil: $totalPersonil</p>";

echo "<h3>Daftar Bagian:</h3><ul>";
foreach ($result['bagian'] as $bag) {
    echo "<li><b>" . $bag['nama_bagian'] . "</b>: " . count($bag['personil']) . " personil</li>";
}
echo "</ul>";

echo "<h3>Preview JSON:</h3>";
echo "<pre style='max-height:400px;overflow:auto;font-size:11px;'>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
