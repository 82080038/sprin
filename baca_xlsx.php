<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = 'DATA PERS FEBRUARI 2026 NEW(1).xlsx';

if (!file_exists($file)) {
    die("File tidak ditemukan: $file");
}

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

// Struktur kolom dari hasil analisis
$namaColumn = 3;
$nrpColumn = 5;
$pangkatColumn = 4;
$jabatanColumn = 6;
$ketColumn = 7;

$highestRow = $sheet->getHighestRow();

// Struktur data
$result = [
    'pimpinan' => [],
    'bagian' => []
];

$currentBagian = null;

for ($row = 10; $row <= $highestRow; $row++) {
    $colA = trim($sheet->getCell('A' . $row)->getValue() ?? '');
    $nama = trim($sheet->getCell(Coordinate::stringFromColumnIndex($namaColumn) . $row)->getValue() ?? '');
    $nrp = trim($sheet->getCell(Coordinate::stringFromColumnIndex($nrpColumn) . $row)->getValue() ?? '');
    $pangkat = trim($sheet->getCell(Coordinate::stringFromColumnIndex($pangkatColumn) . $row)->getValue() ?? '');
    $jabatan = trim($sheet->getCell(Coordinate::stringFromColumnIndex($jabatanColumn) . $row)->getValue() ?? '');
    $ket = trim($sheet->getCell(Coordinate::stringFromColumnIndex($ketColumn) . $row)->getValue() ?? '');
    
    // Skip baris kosong
    if (empty($nama) && empty($nrp) && empty($colA)) continue;
    
    $personil = [
        'nama' => $nama,
        'nrp' => $nrp,
        'pangkat' => $pangkat,
        'jabatan' => $jabatan,
        'ket' => $ket
    ];
    
    // Baris 10 dan 11 adalah pimpinan (punya NRP valid)
    if (($row == 10 || $row == 11) && !empty($nrp) && is_numeric(str_replace(' ', '', $nrp))) {
        $result['pimpinan'][] = $personil;
        continue;
    }
    
    // Cek apakah ini baris header bagian/polsek (kolom A berisi nama bagian/polsek, NRP kosong)
    if (empty($nrp) && !empty($colA) && preg_match('/^(BAG|SIUM|KASI|SAT|SIE|SPKT|PAMAPTA|INTEL|PROVAM|POLSEK|HARIAN)/i', $colA)) {
        $result['bagian'][] = [
            'nama_bagian' => $colA,
            'personil' => []
        ];
        $currentBagian = &$result['bagian'][count($result['bagian']) - 1];
    }
    // Jika ada NRP valid, ini adalah personil
    elseif (!empty($nrp) && is_numeric(str_replace([' ', '.', '-'], '', $nrp))) {
        if ($currentBagian !== null) {
            $currentBagian['personil'][] = $personil;
        }
    }
}

// Simpan ke JSON
$jsonFile = 'PERSONIL_ALL.json';
file_put_contents($jsonFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "<h2>Export JSON Berhasil!</h2>";
echo "<p>File tersimpan: <b>$jsonFile</b></p>";
echo "<p>Jumlah Pimpinan: " . count($result['pimpinan']) . "</p>";
echo "<p>Jumlah Bagian: " . count($result['bagian']) . "</p>";

// Summary bagian
echo "<h3>Daftar Bagian:</h3><ul>";
foreach ($result['bagian'] as $bag) {
    echo "<li><b>" . $bag['nama_bagian'] . "</b>: " . count($bag['personil']) . " personil</li>";
}
echo "</ul>";

echo "<h3>Preview JSON:</h3><pre>" . substr(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 2000) . "...</pre>";
