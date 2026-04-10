<?php
/**
 * Training Management API — Pelatihan Praoperasi
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
            $status = isset($_GET['status']) && in_array($_GET['status'], ['rencana','berlangsung','selesai','batal'])
                      ? $_GET['status'] : null;
            $jenis  = isset($_GET['jenis']) && in_array($_GET['jenis'], ['menembak','bela_diri','sar','ketahanan','teknis','lainnya'])
                      ? $_GET['jenis'] : null;
            $sql = "SELECT p.*, b.nama_bagian FROM pelatihan p LEFT JOIN bagian b ON p.bagian_id = b.id WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; }
            if ($jenis)  { $sql .= " AND p.jenis = ?";  $params[] = $jenis; }
            $sql .= " ORDER BY p.tanggal_mulai DESC, p.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_one':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID tidak valid');
            $stmt = $pdo->prepare("SELECT p.*, b.nama_bagian FROM pelatihan p LEFT JOIN bagian b ON p.bagian_id = b.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception('Data tidak ditemukan');
            echo json_encode(['success' => true, 'data' => $row]);
            break;

        case 'create':
            $nama      = trim($_POST['nama_pelatihan'] ?? '');
            $jenis     = in_array($_POST['jenis'] ?? '', ['menembak','bela_diri','sar','ketahanan','teknis','lainnya']) ? $_POST['jenis'] : 'lainnya';
            $tglMulai  = $_POST['tanggal_mulai'] ?? '';
            $tglSelesai= !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
            $jamLatihan= (float)($_POST['jam_latihan'] ?? 0);
            $lokasi    = trim($_POST['lokasi'] ?? '');
            $instruktur= trim($_POST['instruktur'] ?? '');
            $target    = (int)($_POST['peserta_target'] ?? 0);
            $hadir     = (int)($_POST['peserta_hadir'] ?? 0);
            $bagianId  = !empty($_POST['bagian_id']) ? (int)$_POST['bagian_id'] : null;
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $status    = in_array($_POST['status'] ?? '', ['rencana','berlangsung','selesai','batal']) ? $_POST['status'] : 'rencana';

            if (!$nama) throw new Exception('Nama pelatihan wajib diisi');
            if (!$tglMulai) throw new Exception('Tanggal mulai wajib diisi');

            $stmt = $pdo->prepare("
                INSERT INTO pelatihan (nama_pelatihan, jenis, tanggal_mulai, tanggal_selesai,
                    jam_latihan, lokasi, instruktur, peserta_target, peserta_hadir,
                    bagian_id, deskripsi, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama, $jenis, $tglMulai, $tglSelesai, $jamLatihan, $lokasi,
                $instruktur, $target, $hadir, $bagianId, $deskripsi, $status,
                $_SESSION['username'] ?? 'system']);
            echo json_encode(['success' => true, 'message' => 'Pelatihan berhasil ditambahkan', 'id' => $pdo->lastInsertId()]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID tidak valid');
            $fields = [
                'nama_pelatihan' => trim($_POST['nama_pelatihan'] ?? ''),
                'jenis'          => in_array($_POST['jenis'] ?? '', ['menembak','bela_diri','sar','ketahanan','teknis','lainnya']) ? $_POST['jenis'] : 'lainnya',
                'tanggal_mulai'  => $_POST['tanggal_mulai'] ?? '',
                'tanggal_selesai'=> !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
                'jam_latihan'    => (float)($_POST['jam_latihan'] ?? 0),
                'lokasi'         => trim($_POST['lokasi'] ?? ''),
                'instruktur'     => trim($_POST['instruktur'] ?? ''),
                'peserta_target' => (int)($_POST['peserta_target'] ?? 0),
                'peserta_hadir'  => (int)($_POST['peserta_hadir'] ?? 0),
                'bagian_id'      => !empty($_POST['bagian_id']) ? (int)$_POST['bagian_id'] : null,
                'deskripsi'      => trim($_POST['deskripsi'] ?? ''),
                'status'         => in_array($_POST['status'] ?? '', ['rencana','berlangsung','selesai','batal']) ? $_POST['status'] : 'rencana',
            ];
            if (!$fields['nama_pelatihan']) throw new Exception('Nama pelatihan wajib diisi');
            $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
            $stmt = $pdo->prepare("UPDATE pelatihan SET $setClauses WHERE id = ?");
            $stmt->execute([...array_values($fields), $id]);
            echo json_encode(['success' => true, 'message' => 'Pelatihan berhasil diupdate']);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID tidak valid');
            $stmt = $pdo->prepare("DELETE FROM pelatihan WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) throw new Exception('Data tidak ditemukan');
            echo json_encode(['success' => true, 'message' => 'Pelatihan berhasil dihapus']);
            break;

        case 'get_stats':
            $year = (int)($_GET['year'] ?? date('Y'));
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total,
                    SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status='rencana' THEN 1 ELSE 0 END) as rencana,
                    SUM(CASE WHEN status='berlangsung' THEN 1 ELSE 0 END) as berlangsung,
                    SUM(jam_latihan) as total_jam,
                    SUM(peserta_hadir) as total_peserta
                FROM pelatihan WHERE YEAR(tanggal_mulai) = ?
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
