<?php
require 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;

$docxFile = 'SPRIN STRONG POINT DALAM RANGKA KRYD KETUPAT TOBA 2026 DI WILKUM KABUPATEN SAMOSIR TGL 27 MARET 2026.docx';
echo "<h2>Extract Personil dari SPRIN STRONG POINT ke JSON</h2>";

$phpWord = IOFactory::load($docxFile);
$allPersonil = [];

foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $rowNum = 0;
            foreach ($element->getRows() as $row) {
                $rowNum++;
                if ($rowNum <= 2) continue; // Skip headers
                
                $cells = $row->getCells();
                $cellTexts = [];
                foreach ($cells as $cell) {
                    $text = '';
                    foreach ($cell->getElements() as $e) {
                        if (method_exists($e, 'getText')) {
                            $t = $e->getText();
                            $text .= is_string($t) ? $t : implode('', $t);
                        }
                    }
                    $cellTexts[] = trim($text);
                }
                
                if (count($cellTexts) >= 4) {
                    $nama = $cellTexts[1];
                    $pangkat = $cellTexts[2];
                    $jabatan = $cellTexts[3];
                    $ket = $cellTexts[4] ?? '';
                    
                    if (!empty($nama) && strlen($nama) > 5) {
                        $isPimpinan = stripos($jabatan, 'KAPOLRES') !== false || stripos($jabatan, 'WAKA') !== false;
                        $allPersonil[] = [
                            'nama' => $nama,
                            'nrp' => '',
                            'pangkat' => $pangkat,
                            'jabatan' => $jabatan,
                            'ket' => $ket,
                            'kategori' => $isPimpinan ? 'pimpinan' : 'anggota'
                        ];
                    }
                }
            }
        }
    }
}

$result = ['pimpinan' => [], 'bagian' => [['nama_bagian' => 'STRONG POINT', 'personil' => []]]];
foreach ($allPersonil as $p) {
    $data = ['nama' => $p['nama'], 'nrp' => '', 'pangkat' => $p['pangkat'], 'jabatan' => $p['jabatan'], 'ket' => $p['ket']];
    if ($p['kategori'] === 'pimpinan') $result['pimpinan'][] = $data;
    else $result['bagian'][0]['personil'][] = $data;
}

file_put_contents('STRONG_POINT_ALL.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "<p><b>Saved: STRONG_POINT_ALL.json</b></p>";
echo "<p>Total: " . count($allPersonil) . " (Pimpinan: " . count($result['pimpinan']) . ", Anggota: " . count($result['bagian'][0]['personil']) . ")</p>";
echo "<pre>" . substr(json_encode($result, JSON_PRETTY_PRINT), 0, 2000) . "...</pre>";
