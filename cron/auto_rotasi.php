<?php
/**
 * Cron: Auto Rotasi Piket
 * Jalankan setiap hari jam 07:00:
 * 0 7 * * * /opt/lampp/bin/php /opt/lampp/htdocs/sprin/cron/auto_rotasi.php
 * 
 * Atau jalankan manual: http://localhost/sprin/cron/auto_rotasi.php
 */
require_once __DIR__ . '/../core/config.php';

$isCli = php_sapi_name() === 'cli';
$results = [];

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil semua bagian yang punya siklus dan auto_rotasi aktif
    $bagianList = $pdo->query("
        SELECT DISTINCT s.id_bagian, b.nama_bagian
        FROM siklus_piket_fase s
        JOIN bagian b ON b.id = s.id_bagian
        WHERE s.id_bagian IS NOT NULL
        ORDER BY s.id_bagian
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($bagianList as $bag) {
        $bagianId = $bag['id_bagian'];

        // Ambil urutan fase
        $faseList = $pdo->prepare("SELECT id, urutan FROM siklus_piket_fase WHERE id_bagian=? ORDER BY urutan");
        $faseList->execute([$bagianId]);
        $fases = $faseList->fetchAll(PDO::FETCH_ASSOC);
        if (empty($fases)) continue;

        $faseMap = array_column($fases, 'urutan', 'id');
        $maxUrutan = max(array_values($faseMap));

        // Ambil semua tim aktif di bagian ini
        $timList = $pdo->prepare("SELECT id, fase_siklus_id FROM tim_piket WHERE id_bagian=? AND is_active=1");
        $timList->execute([$bagianId]);
        $tims = $timList->fetchAll(PDO::FETCH_ASSOC);
        if (empty($tims)) continue;

        $pdo->beginTransaction();
        $rotated = 0;
        $timIds = [];
        $dariFase = null;
        $keFase = null;

        foreach ($tims as $tim) {
            $curFaseId = $tim['fase_siklus_id'];
            $curUrutan = $curFaseId && isset($faseMap[$curFaseId]) ? $faseMap[$curFaseId] : 0;
            $nextUrutan = $curUrutan >= $maxUrutan ? 1 : $curUrutan + 1;
            $nextFaseId = array_search($nextUrutan, $faseMap);
            if ($nextFaseId !== false) {
                $pdo->prepare("UPDATE tim_piket SET fase_siklus_id=? WHERE id=?")->execute([$nextFaseId, $tim['id']]);
                $rotated++;
                $timIds[] = $tim['id'];
                if ($dariFase === null) { $dariFase = $curFaseId; $keFase = $nextFaseId; }
            }
        }

        // Log rotasi
        $pdo->prepare("INSERT INTO rotasi_log (id_bagian, dari_fase_id, ke_fase_id, tim_ids, jumlah_tim, tipe, oleh) VALUES (?,?,?,?,?,?,?)")
            ->execute([$bagianId, $dariFase, $keFase, json_encode($timIds), $rotated, 'otomatis', null]);

        // Notifikasi in-app
        $pdo->prepare("INSERT INTO notifikasi_piket (tipe, judul, pesan) VALUES (?,?,?)")
            ->execute(['rotasi', 'Rotasi Otomatis: '.$bag['nama_bagian'], $rotated.' tim berhasil dirotasi ke fase berikutnya']);

        $pdo->commit();
        $results[] = [
            'bagian' => $bag['nama_bagian'],
            'rotated' => $rotated,
            'status' => 'OK'
        ];
    }

    $success = true;
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $success = false;
    $error = $e->getMessage();
}

if ($isCli) {
    echo "=== Auto Rotasi Piket ===\n";
    echo "Waktu: " . date('Y-m-d H:i:s') . "\n";
    if ($success) {
        foreach ($results as $r) {
            echo "  [{$r['status']}] {$r['bagian']}: {$r['rotated']} tim dirotasi\n";
        }
        echo "Selesai.\n";
    } else {
        echo "ERROR: $error\n";
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'waktu'   => date('Y-m-d H:i:s'),
        'results' => $results,
        'error'   => $error ?? null
    ]);
}
