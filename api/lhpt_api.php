<?php
/**
 * LHPT API — Laporan Hasil Pelaksanaan Tugas
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
            $operationId = isset($_GET['operation_id']) ? (int)$_GET['operation_id'] : null;
            $sql = "
                SELECT l.*, o.operation_name, o.nomor_sprint
                FROM lhpt l
                LEFT JOIN operations o ON l.operation_id = o.id
            ";
            $params = [];
            if ($operationId) {
                $sql .= " WHERE l.operation_id = ?";
                $params[] = $operationId;
            }
            $sql .= " ORDER BY l.tanggal_laporan DESC, l.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_one':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID LHPT tidak valid');
            $stmt = $pdo->prepare("
                SELECT l.*, o.operation_name, o.nomor_sprint, o.tingkat_operasi, o.jenis_operasi,
                       o.operation_date, o.operation_date_end, o.location, o.kuat_personil, o.dukgra
                FROM lhpt l
                LEFT JOIN operations o ON l.operation_id = o.id
                WHERE l.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception('LHPT tidak ditemukan');
            echo json_encode(['success' => true, 'data' => $row]);
            break;

        case 'create':
            $operation_id    = (int)($_POST['operation_id'] ?? 0);
            $tanggal_laporan = trim($_POST['tanggal_laporan'] ?? '');
            $isi_laporan     = trim($_POST['isi_laporan'] ?? '');
            $kendala         = trim($_POST['kendala'] ?? '');
            $hasil           = trim($_POST['hasil'] ?? '');
            $rekomendasi     = trim($_POST['rekomendasi'] ?? '');
            $pelapor         = trim($_POST['pelapor'] ?? '');
            $jabatan_pelapor = trim($_POST['jabatan_pelapor'] ?? '');
            $status_lhpt     = in_array($_POST['status_lhpt'] ?? '', ['draft','submitted','approved'])
                                ? $_POST['status_lhpt'] : 'draft';

            if (!$operation_id) throw new Exception('Operasi wajib dipilih');
            if (!$tanggal_laporan) throw new Exception('Tanggal laporan wajib diisi');
            if (!$isi_laporan) throw new Exception('Isi laporan wajib diisi');

            // Auto-generate nomor LHPT
            $bulanRomawi = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            $year  = date('Y');
            $month = (int)date('m');
            $stmtSeq = $pdo->prepare("SELECT COUNT(*) FROM lhpt WHERE YEAR(created_at) = ?");
            $stmtSeq->execute([$year]);
            $seqNum = (int)$stmtSeq->fetchColumn() + 1;
            $nomor_lhpt = "LHPT / {$seqNum} / {$bulanRomawi[$month]} / {$year} / OPS";

            $stmt = $pdo->prepare("
                INSERT INTO lhpt (nomor_lhpt, operation_id, tanggal_laporan, isi_laporan, 
                                  kendala, hasil, rekomendasi, pelapor, jabatan_pelapor, status_lhpt, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nomor_lhpt, $operation_id, $tanggal_laporan, $isi_laporan,
                $kendala, $hasil, $rekomendasi, $pelapor, $jabatan_pelapor, $status_lhpt,
                $_SESSION['username'] ?? 'system'
            ]);
            echo json_encode([
                'success' => true,
                'message' => 'LHPT berhasil dibuat',
                'id'      => $pdo->lastInsertId(),
                'nomor_lhpt' => $nomor_lhpt
            ]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID LHPT tidak valid');

            $fields = [
                'operation_id'    => (int)($_POST['operation_id'] ?? 0),
                'tanggal_laporan' => trim($_POST['tanggal_laporan'] ?? ''),
                'isi_laporan'     => trim($_POST['isi_laporan'] ?? ''),
                'kendala'         => trim($_POST['kendala'] ?? ''),
                'hasil'           => trim($_POST['hasil'] ?? ''),
                'rekomendasi'     => trim($_POST['rekomendasi'] ?? ''),
                'pelapor'         => trim($_POST['pelapor'] ?? ''),
                'jabatan_pelapor' => trim($_POST['jabatan_pelapor'] ?? ''),
                'status_lhpt'     => in_array($_POST['status_lhpt'] ?? '', ['draft','submitted','approved'])
                                      ? $_POST['status_lhpt'] : 'draft',
            ];
            if (!$fields['operation_id']) throw new Exception('Operasi wajib dipilih');
            if (!$fields['tanggal_laporan']) throw new Exception('Tanggal laporan wajib diisi');
            if (!$fields['isi_laporan']) throw new Exception('Isi laporan wajib diisi');

            $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
            $stmt = $pdo->prepare("UPDATE lhpt SET $setClauses WHERE id = ?");
            $stmt->execute([...array_values($fields), $id]);
            echo json_encode(['success' => true, 'message' => 'LHPT berhasil diupdate']);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID LHPT tidak valid');
            $stmt = $pdo->prepare("DELETE FROM lhpt WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) throw new Exception('LHPT tidak ditemukan');
            echo json_encode(['success' => true, 'message' => 'LHPT berhasil dihapus']);
            break;

        case 'get_operations':
            $stmt = $pdo->query("
                SELECT o.id, o.nomor_sprint, o.operation_name, o.status,
                       (SELECT COUNT(*) FROM lhpt l WHERE l.operation_id = o.id) as lhpt_count
                FROM operations o
                ORDER BY o.created_at DESC
            ");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
