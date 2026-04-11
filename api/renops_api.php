<?php
/**
 * Renops API — Rencana Operasi
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
require_once __DIR__ . '/../core/ActivityLog.php';
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

// CSRF protection
CSRFHelper::applyProtection(['get_renops_by_id','get_all_renops','get_nomor_renops']);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // ── GET: all renops ─────────────────────────────────────────────
    if ($action === 'get_all_renops') {
        $stmt = $pdo->query("
            SELECT r.*, o.nama_operasi, o.tingkat, o.jenis
            FROM renops r
            LEFT JOIN operations o ON o.id = r.operation_id
            ORDER BY r.created_at DESC
        ");
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ── GET: renops by id ───────────────────────────────────────────
    if ($action === 'get_renops_by_id') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM renops WHERE id=?");
        $stmt->execute([$id]);
        $renops = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'data'=>$renops]); exit;
    }

    // ── GET: generate nomor renops ────────────────────────────────────
    if ($action === 'get_nomor_renops') {
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $bulan = $bulanRomawi[date('n') - 1];
        $tahun = date('Y');
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM renops WHERE YEAR(created_at)=?");
        $stmt->execute([$tahun]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $urut = str_pad($row['cnt'] + 1, 3, '0', STR_PAD_LEFT);
        
        $nomor = "RENOPS/$urut/$bulan/$tahun";
        echo json_encode(['success'=>true,'nomor'=>$nomor]); exit;
    }

    // ── POST: create renops ───────────────────────────────────────────
    if ($action === 'create_renops') {
        $nomor = trim($_POST['nomor_renops'] ?? '');
        $judul = trim($_POST['judul_renops'] ?? '');
        $sasaran = $_POST['sasaran'] ?? '';
        $wilayah = $_POST['wilayah'] ?? '';
        $kekuatan = $_POST['kekuatan'] ?? '';
        $anggaran = is_numeric($_POST['anggaran'] ?? '') ? (float)$_POST['anggaran'] : 0;
        $tglMulai = $_POST['tanggal_mulai'] ?? '';
        $tglSelesai = $_POST['tanggal_selesai'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        
        if (!$nomor || !$judul) throw new Exception('Nomor dan judul wajib diisi');
        
        $stmt = $pdo->prepare("
            INSERT INTO renops (nomor_renops,judul_renops,sasaran,wilayah,kekuatan,anggaran,tanggal_mulai,tanggal_selesai,status,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$nomor,$judul,$sasaran,$wilayah,$kekuatan,$anggaran,$tglMulai,$tglSelesai,$status,$_SESSION['username'] ?? 'system']);
        $newId = $pdo->lastInsertId();
        
        ActivityLog::logCreate('renops', $newId, "Created renops: $nomor - $judul");
        echo json_encode(['success'=>true,'id'=>$newId,'message'=>'Renops berhasil dibuat']); exit;
    }

    // ── POST: update renops ───────────────────────────────────────────
    if ($action === 'update_renops') {
        $id = (int)($_POST['id'] ?? 0);
        $nomor = trim($_POST['nomor_renops'] ?? '');
        $judul = trim($_POST['judul_renops'] ?? '');
        $sasaran = $_POST['sasaran'] ?? '';
        $wilayah = $_POST['wilayah'] ?? '';
        $kekuatan = $_POST['kekuatan'] ?? '';
        $anggaran = is_numeric($_POST['anggaran'] ?? '') ? (float)$_POST['anggaran'] : 0;
        $tglMulai = $_POST['tanggal_mulai'] ?? '';
        $tglSelesai = $_POST['tanggal_selesai'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        
        if (!$id || !$nomor || !$judul) throw new Exception('ID, nomor, dan judul wajib diisi');
        
        $stmt = $pdo->prepare("
            UPDATE renops SET nomor_renops=?,judul_renops=?,sasaran=?,wilayah=?,kekuatan=?,anggaran=?,tanggal_mulai=?,tanggal_selesai=?,status=?
            WHERE id=?
        ");
        $stmt->execute([$nomor,$judul,$sasaran,$wilayah,$kekuatan,$anggaran,$tglMulai,$tglSelesai,$status,$id]);
        
        ActivityLog::logUpdate('renops', $id, "Updated renops: $nomor - $judul");
        echo json_encode(['success'=>true,'message'=>'Renops berhasil diupdate']); exit;
    }

    // ── POST: delete renops ───────────────────────────────────────────
    if ($action === 'delete_renops') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID tidak valid');
        
        $stmt = $pdo->prepare("SELECT nomor_renops,judul_renops FROM renops WHERE id=?");
        $stmt->execute([$id]);
        $renops = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pdo->prepare("DELETE FROM renops WHERE id=?")->execute([$id]);
        
        ActivityLog::logDelete('renops', $id, "Deleted renops: {$renops['nomor_renops']} - {$renops['judul_renops']}");
        echo json_encode(['success'=>true,'message'=>'Renops berhasil dihapus']); exit;
    }

    // ── POST: convert renops to operation ───────────────────────────────
    if ($action === 'convert_to_operation') {
        $renopsId = (int)($_POST['renops_id'] ?? 0);
        if (!$renopsId) throw new Exception('renops_id tidak valid');
        
        // Get renops data
        $stmt = $pdo->prepare("SELECT * FROM renops WHERE id=?");
        $stmt->execute([$renopsId]);
        $renops = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$renops) throw new Exception('Renops tidak ditemukan');
        
        // Create operation from renops
        $stmt = $pdo->prepare("
            INSERT INTO operations (nama_operasi,tingkat,jenis,tanggal_mulai,tanggal_selesai,sasaran,wilayah,kekuatan,anggaran,status,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $renops['judul_renops'],
            'POLRES', // default tingkat
            'OPERASI', // default jenis
            $renops['tanggal_mulai'],
            $renops['tanggal_selesai'],
            $renops['sasaran'],
            $renops['wilayah'],
            $renops['kekuatan'],
            $renops['anggaran'],
            'rencana',
            $_SESSION['username'] ?? 'system'
        ]);
        $operationId = $pdo->lastInsertId();
        
        // Update renops with operation_id and status
        $pdo->prepare("UPDATE renops SET operation_id=?, status='executed' WHERE id=?")->execute([$operationId, $renopsId]);
        
        ActivityLog::logCreate('operations', $operationId, "Converted from renops: {$renops['nomor_renops']}");
        echo json_encode(['success'=>true,'operation_id'=>$operationId,'message'=>'Renops berhasil dikonversi menjadi operasi']); exit;
    }

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
