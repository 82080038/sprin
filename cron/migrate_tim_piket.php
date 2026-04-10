<?php
/**
 * Migration: Tim Piket & Recurrence
 * Jalankan sekali: http://localhost/sprin/cron/migrate_tim_piket.php
 */
require_once __DIR__ . '/../core/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    $steps = [];

    // ── 1. Tabel tim_piket ──────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tim_piket` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `nama_tim`      VARCHAR(100) NOT NULL,
        `id_bagian`     INT(11) DEFAULT NULL,
        `id_unsur`      INT(11) DEFAULT NULL,
        `jenis`         ENUM('piket','satuan_tugas','kegiatan') NOT NULL DEFAULT 'piket',
        `shift_default` VARCHAR(20) DEFAULT NULL COMMENT 'PAGI/SIANG/MALAM/FULL_DAY/ROTASI',
        `pola_rotasi`   VARCHAR(100) DEFAULT NULL COMMENT 'Urutan shift rotasi, misal: PAGI,SIANG,MALAM',
        `keterangan`    TEXT DEFAULT NULL,
        `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
        `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_bagian` (`id_bagian`),
        KEY `idx_unsur`  (`id_unsur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Manajemen tim/regu piket per bagian'");
    $steps[] = ['✔', 'Tabel tim_piket dibuat / sudah ada'];

    // ── 2. Tabel tim_piket_anggota ──────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tim_piket_anggota` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `tim_id`       INT(11) NOT NULL,
        `personil_id`  VARCHAR(20) NOT NULL,
        `peran`        ENUM('ketua','wakil','anggota') NOT NULL DEFAULT 'anggota',
        `urutan`       INT(11) NOT NULL DEFAULT 0,
        `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_tim_personil` (`tim_id`, `personil_id`),
        KEY `idx_tim`      (`tim_id`),
        KEY `idx_personil` (`personil_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Anggota tim piket'");
    $steps[] = ['✔', 'Tabel tim_piket_anggota dibuat / sudah ada'];

    // ── Helper: cek kolom sudah ada ─────────────────────────────────────────
    $colExists = function(string $table, string $col) use ($pdo): bool {
        $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetchAll();
        return count($r) > 0;
    };

    // ── 3. Tambah kolom recurrence ke schedules ─────────────────────────────
    $schedCols = [
        'tim_id'               => "INT(11) DEFAULT NULL COMMENT 'FK tim_piket'",
        'recurrence_type'      => "ENUM('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none'",
        'recurrence_interval'  => "INT(11) NOT NULL DEFAULT 1 COMMENT 'Setiap N hari/minggu/bulan'",
        'recurrence_days'      => "VARCHAR(20) DEFAULT NULL COMMENT 'weekly: 1,3,5 = Sen,Rab,Jum'",
        'recurrence_end'       => "DATE DEFAULT NULL",
        'recurrence_parent_id' => "INT(11) DEFAULT NULL COMMENT 'NULL = induk series'",
    ];
    foreach ($schedCols as $col => $def) {
        if (!$colExists('schedules', $col)) {
            $pdo->exec("ALTER TABLE `schedules` ADD COLUMN `$col` $def");
            $steps[] = ['✔', "schedules.$col ditambahkan"];
        } else {
            $steps[] = ['–', "schedules.$col sudah ada"];
        }
    }

    // ── 4. Tambah kolom recurrence ke operations ────────────────────────────
    $opCols = [
        'recurrence_type'      => "ENUM('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none'",
        'recurrence_interval'  => "INT(11) NOT NULL DEFAULT 1",
        'recurrence_days'      => "VARCHAR(20) DEFAULT NULL",
        'recurrence_end'       => "DATE DEFAULT NULL",
        'recurrence_parent_id' => "INT(11) DEFAULT NULL",
    ];
    foreach ($opCols as $col => $def) {
        if (!$colExists('operations', $col)) {
            $pdo->exec("ALTER TABLE `operations` ADD COLUMN `$col` $def");
            $steps[] = ['✔', "operations.$col ditambahkan"];
        } else {
            $steps[] = ['–', "operations.$col sudah ada"];
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    $success = true;

} catch (Exception $e) {
    $success = false;
    $error   = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Migration: Tim Piket</title>
<style>
body { font-family: monospace; max-width: 700px; margin: 40px auto; background:#1a1a2e; color:#eee; padding:20px; }
h2   { color: #4fc3f7; }
.ok  { color: #81c784; }
.skip{ color: #aaa; }
.err { color: #ef5350; background:#2a0000; padding:10px; border-radius:4px; }
.done{ background:#003300; border:1px solid #4caf50; padding:15px; border-radius:6px; margin-top:20px; }
a    { color:#4fc3f7; }
</style>
</head>
<body>
<h2>🔧 Migration: Tim Piket & Recurrence</h2>
<?php if (!empty($steps)): ?>
<ul>
<?php foreach ($steps as [$icon, $msg]): ?>
    <li class="<?php echo $icon==='✔'?'ok':'skip'; ?>"><?php echo $icon; ?> <?php echo htmlspecialchars($msg); ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
<div class="done">
    ✅ <strong>Migration berhasil!</strong><br><br>
    Tabel baru: <code>tim_piket</code>, <code>tim_piket_anggota</code><br>
    Kolom baru di <code>schedules</code>: tim_id, recurrence_type, recurrence_interval, recurrence_days, recurrence_end, recurrence_parent_id<br>
    Kolom baru di <code>operations</code>: recurrence_type, recurrence_interval, recurrence_days, recurrence_end, recurrence_parent_id<br><br>
    <a href="../pages/tim_piket.php">→ Buka halaman Tim Piket</a>
</div>
<?php else: ?>
<div class="err">❌ Error: <?php echo htmlspecialchars($error ?? 'Unknown'); ?></div>
<?php endif; ?>
</body>
</html>
