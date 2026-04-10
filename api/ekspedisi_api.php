<?php
/**
 * Ekspedisi Surat API — Penomoran agenda surat masuk & keluar
 */
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'get_all':
            $jenis = isset($_GET['jenis']) && in_array($_GET['jenis'], ['masuk','keluar']) ? $_GET['jenis'] : null;
            $sql = "SELECT * FROM surat_ekspedisi";
            $params = [];
            if ($jenis) {
                $sql .= " WHERE jenis = ?";
                $params[] = $jenis;
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_one':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID surat tidak valid');
            $stmt = $pdo->prepare("SELECT * FROM surat_ekspedisi WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception('Surat tidak ditemukan');
            echo json_encode(['success' => true, 'data' => $row]);
            break;

        case 'create':
            $jenis         = in_array($_POST['jenis'] ?? '', ['masuk','keluar']) ? $_POST['jenis'] : 'masuk';
            $nomor_surat   = trim($_POST['nomor_surat'] ?? '');
            $tanggal_surat = !empty($_POST['tanggal_surat']) ? $_POST['tanggal_surat'] : null;
            $tanggal_terima= !empty($_POST['tanggal_terima']) ? $_POST['tanggal_terima'] : null;
            $perihal       = trim($_POST['perihal'] ?? '');
            $pengirim      = trim($_POST['pengirim'] ?? '');
            $tujuan        = trim($_POST['tujuan'] ?? '');
            $kategori      = in_array($_POST['kategori'] ?? '', ['biasa','penting','rahasia','segera']) ? $_POST['kategori'] : 'biasa';
            $status        = in_array($_POST['status'] ?? '', ['diterima','diproses','selesai','diarsipkan']) ? $_POST['status'] : 'diterima';
            $disposisi     = trim($_POST['disposisi'] ?? '');
            $keterangan    = trim($_POST['keterangan'] ?? '');

            if (!$perihal) throw new Exception('Perihal wajib diisi');

            // Auto-generate nomor agenda
            $year = date('Y');
            $prefix = $jenis === 'masuk' ? 'SM' : 'SK';
            $stmtSeq = $pdo->prepare("SELECT COUNT(*) FROM surat_ekspedisi WHERE jenis = ? AND YEAR(created_at) = ?");
            $stmtSeq->execute([$jenis, $year]);
            $seqNum = (int)$stmtSeq->fetchColumn() + 1;
            $nomor_agenda = sprintf("%s/%04d/%s", $prefix, $seqNum, $year);

            $stmt = $pdo->prepare("
                INSERT INTO surat_ekspedisi 
                    (nomor_agenda, jenis, nomor_surat, tanggal_surat, tanggal_terima,
                     perihal, pengirim, tujuan, kategori, status, disposisi, keterangan, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nomor_agenda, $jenis, $nomor_surat, $tanggal_surat, $tanggal_terima,
                $perihal, $pengirim, $tujuan, $kategori, $status, $disposisi, $keterangan,
                $_SESSION['username'] ?? 'system'
            ]);
            echo json_encode([
                'success' => true,
                'message' => 'Surat berhasil dicatat',
                'id' => $pdo->lastInsertId(),
                'nomor_agenda' => $nomor_agenda
            ]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID surat tidak valid');

            $fields = [
                'jenis'          => in_array($_POST['jenis'] ?? '', ['masuk','keluar']) ? $_POST['jenis'] : 'masuk',
                'nomor_surat'    => trim($_POST['nomor_surat'] ?? ''),
                'tanggal_surat'  => !empty($_POST['tanggal_surat']) ? $_POST['tanggal_surat'] : null,
                'tanggal_terima' => !empty($_POST['tanggal_terima']) ? $_POST['tanggal_terima'] : null,
                'perihal'        => trim($_POST['perihal'] ?? ''),
                'pengirim'       => trim($_POST['pengirim'] ?? ''),
                'tujuan'         => trim($_POST['tujuan'] ?? ''),
                'kategori'       => in_array($_POST['kategori'] ?? '', ['biasa','penting','rahasia','segera']) ? $_POST['kategori'] : 'biasa',
                'status'         => in_array($_POST['status'] ?? '', ['diterima','diproses','selesai','diarsipkan']) ? $_POST['status'] : 'diterima',
                'disposisi'      => trim($_POST['disposisi'] ?? ''),
                'keterangan'     => trim($_POST['keterangan'] ?? ''),
            ];
            if (!$fields['perihal']) throw new Exception('Perihal wajib diisi');

            $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
            $stmt = $pdo->prepare("UPDATE surat_ekspedisi SET $setClauses WHERE id = ?");
            $stmt->execute([...array_values($fields), $id]);
            echo json_encode(['success' => true, 'message' => 'Surat berhasil diupdate']);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID surat tidak valid');
            $stmt = $pdo->prepare("DELETE FROM surat_ekspedisi WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) throw new Exception('Surat tidak ditemukan');
            echo json_encode(['success' => true, 'message' => 'Surat berhasil dihapus']);
            break;

        case 'stats':
            $year = (int)($_GET['year'] ?? date('Y'));
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN jenis='masuk' THEN 1 ELSE 0 END) as masuk,
                    SUM(CASE WHEN jenis='keluar' THEN 1 ELSE 0 END) as keluar,
                    SUM(CASE WHEN status='diterima' THEN 1 ELSE 0 END) as diterima,
                    SUM(CASE WHEN status='diproses' THEN 1 ELSE 0 END) as diproses,
                    SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status='diarsipkan' THEN 1 ELSE 0 END) as diarsipkan
                FROM surat_ekspedisi WHERE YEAR(created_at) = ?
            ");
            $stmt->execute([$year]);
            echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
