<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Cek apakah JSON sudah ada, jika belum generate dulu
if (!file_exists('PERSONIL_ALL.json')) {
    die("File PERSONIL_ALL.json belum ada. Jalankan baca_xlsx.php dulu.");
}

$json = file_get_contents('PERSONIL_ALL.json');
$data = json_decode($json, true);

// Hitung statistik
$totalPimpinan = count($data['pimpinan']);
$totalBagian = count($data['bagian']);
$totalPersonil = 0;
foreach ($data['bagian'] as $bagian) {
    $totalPersonil += count($bagian['personil']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Personil POLRES Samosir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { 
            text-align: center; 
            color: #1a237e; 
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle { 
            text-align: center; 
            color: #666; 
            margin-bottom: 20px;
            font-size: 14px;
        }
        .stats { 
            display: flex; 
            justify-content: center; 
            gap: 30px; 
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .stat-box { 
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
            min-width: 120px;
        }
        .stat-box h3 { font-size: 24px; margin-bottom: 5px; }
        .stat-box p { font-size: 12px; opacity: 0.9; }
        
        /* Pimpinan Section */
        .pimpinan-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .pimpinan-section h2 {
            color: #1a237e;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 10px;
        }
        .pimpinan-cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .pimpinan-card {
            background: linear-gradient(135deg, #ffd700, #ffed4a);
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #b8860b;
        }
        .pimpinan-card .nama {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }
        .pimpinan-card .pangkat {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .pimpinan-card .jabatan {
            color: #1a237e;
            font-weight: bold;
            font-size: 13px;
        }
        
        /* Bagian Section */
        .bagian-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .bagian-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
            padding: 15px;
            background: #1a237e;
            color: white;
            border-radius: 8px;
        }
        .bagian-header:hover { background: #283593; }
        .bagian-header h2 {
            font-size: 16px;
            margin: 0;
        }
        .bagian-count {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .personil-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .personil-table th {
            background: #3949ab;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
        }
        .personil-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .personil-table tr:hover { background: #f5f5f5; }
        .personil-table tr:nth-child(even) { background: #fafafa; }
        .no-col { width: 40px; text-align: center; }
        .nrp-col { width: 90px; }
        .pangkat-col { width: 80px; }
        .ket-col { width: 60px; text-align: center; }
        .ket-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .ket-a { background: #e3f2fd; color: #1565c0; }
        .ket-b { background: #f3e5f5; color: #7b1fa2; }
        .ket-c { background: #e8f5e9; color: #2e7d32; }
        .ket-other { background: #fff3e0; color: #e65100; }
        
        .toggle-icon { font-size: 12px; transition: transform 0.3s; }
        .collapsed .toggle-icon { transform: rotate(-90deg); }
        .personil-content { overflow: hidden; transition: max-height 0.3s; }
        
        @media print {
            .bagian-header { cursor: default; }
            .personil-content { max-height: none !important; display: block !important; }
        }
        
        @media (max-width: 768px) {
            .personil-table { font-size: 12px; }
            .personil-table th, .personil-table td { padding: 8px 5px; }
            .pangkat-col, .ket-col { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DATA PERSONIL POLRES SAMOSIR</h1>
        <p class="subtitle">Daftar Personil Periode Februari 2026</p>
        
        <div class="stats">
            <div class="stat-box">
                <h3><?php echo $totalPimpinan; ?></h3>
                <p>PIMPINAN</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $totalBagian; ?></h3>
                <p>SATUAN/BAGIAN/POLSEK</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $totalPersonil; ?></h3>
                <p>TOTAL PERSONIL</p>
            </div>
        </div>
        
        <!-- Pimpinan -->
        <div class="pimpinan-section">
            <h2>PIMPINAN</h2>
            <div class="pimpinan-cards">
                <?php foreach ($data['pimpinan'] as $p): ?>
                <div class="pimpinan-card">
                    <div class="nama"><?php echo htmlspecialchars($p['nama']); ?></div>
                    <div class="pangkat"><?php echo htmlspecialchars($p['pangkat']); ?> (<?php echo htmlspecialchars($p['nrp']); ?>)</div>
                    <div class="jabatan"><?php echo htmlspecialchars($p['jabatan']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Bagian/Polsek -->
        <?php $no = 1; foreach ($data['bagian'] as $bagian): ?>
        <div class="bagian-section" id="bagian-<?php echo $no; ?>">
            <div class="bagian-header" onclick="toggleBagian(<?php echo $no; ?>)">
                <h2><?php echo htmlspecialchars($bagian['nama_bagian']); ?></h2>
                <div>
                    <span class="bagian-count"><?php echo count($bagian['personil']); ?> personil</span>
                    <span class="toggle-icon" id="icon-<?php echo $no; ?>">▼</span>
                </div>
            </div>
            <div class="personil-content" id="content-<?php echo $no; ?>">
                <table class="personil-table">
                    <thead>
                        <tr>
                            <th class="no-col">NO</th>
                            <th>NAMA</th>
                            <th class="nrp-col">NRP</th>
                            <th class="pangkat-col">PANGKAT</th>
                            <th>JABATAN</th>
                            <th class="ket-col">KET</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pno = 1; foreach ($bagian['personil'] as $personil): 
                            $ketClass = '';
                            if (!empty($personil['ket'])) {
                                $ket = strtoupper($personil['ket']);
                                if ($ket === 'A') $ketClass = 'ket-a';
                                elseif ($ket === 'B') $ketClass = 'ket-b';
                                elseif ($ket === 'C') $ketClass = 'ket-c';
                                else $ketClass = 'ket-other';
                            }
                        ?>
                        <tr>
                            <td class="no-col"><?php echo $pno++; ?></td>
                            <td><?php echo htmlspecialchars($personil['nama']); ?></td>
                            <td class="nrp-col"><?php echo htmlspecialchars($personil['nrp']); ?></td>
                            <td class="pangkat-col"><?php echo htmlspecialchars($personil['pangkat']); ?></td>
                            <td><?php echo htmlspecialchars($personil['jabatan']); ?></td>
                            <td class="ket-col">
                                <?php if (!empty($personil['ket'])): ?>
                                <span class="ket-badge <?php echo $ketClass; ?>"><?php echo htmlspecialchars($personil['ket']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php $no++; endforeach; ?>
    </div>
    
    <script>
        function toggleBagian(id) {
            const content = document.getElementById('content-' + id);
            const section = document.getElementById('bagian-' + id);
            const icon = document.getElementById('icon-' + id);
            
            if (content.style.maxHeight && content.style.maxHeight !== 'none') {
                content.style.maxHeight = null;
                section.classList.add('collapsed');
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                section.classList.remove('collapsed');
            }
        }
        
        // Collapse all by default
        document.querySelectorAll('.personil-content').forEach((el, i) => {
            if (i > 0) { // Keep first one open
                el.style.maxHeight = '0px';
                el.parentElement.classList.add('collapsed');
            } else {
                el.style.maxHeight = el.scrollHeight + 'px';
            }
        });
    </script>
</body>
</html>
