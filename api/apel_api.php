<?php
/**
 * Apel Nominal API — Absensi Apel Pagi/Sore seluruh personil
 */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

// CSRF protection for POST (skip read-only actions)
CSRFHelper::applyProtection(['get_apel_by_id','get_all_apel','get_rekap_apel']);

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

        case 'get_personil_list':
            $unsurId = isset($_GET['unsur_id']) ? (int)$_GET['unsur_id'] : null;
            $bagianId = isset($_GET['bagian_id']) ? (int)$_GET['bagian_id'] : null;
            $sql = "
                SELECT p.id, p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian, u.nama_unsur,
                       j.nama_jabatan
                FROM personil p
                LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON p.id_unsur = u.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                WHERE p.is_active = 1 AND p.is_deleted = 0
            ";
            $params = [];
            if ($unsurId) { $sql .= " AND p.id_unsur = ?"; $params[] = $unsurId; }
            if ($bagianId) { $sql .= " AND p.id_bagian = ?"; $params[] = $bagianId; }
            $sql .= " ORDER BY u.urutan ASC, b.nama_bagian ASC, pk.id DESC, p.nama ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_apel':
            $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
            $jenisApel = in_array($_GET['jenis_apel'] ?? '', ['pagi','sore']) ? $_GET['jenis_apel'] : 'pagi';
            $unsurId = isset($_GET['unsur_id']) ? (int)$_GET['unsur_id'] : null;
            $bagianId = isset($_GET['bagian_id']) ? (int)$_GET['bagian_id'] : null;

            $sql = "
                SELECT p.id as personil_id, p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian,
                       u.nama_unsur, j.nama_jabatan,
                       a.id as apel_id, a.status, a.jam_hadir, a.keterangan
                FROM personil p
                LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON p.id_unsur = u.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN apel_nominal a ON a.personil_id = p.id AND a.tanggal = ? AND a.jenis_apel = ?
                WHERE p.is_active = 1 AND p.is_deleted = 0
            ";
            $params = [$tanggal, $jenisApel];
            if ($unsurId) { $sql .= " AND p.id_unsur = ?"; $params[] = $unsurId; }
            if ($bagianId) { $sql .= " AND p.id_bagian = ?"; $params[] = $bagianId; }
            $sql .= " ORDER BY u.urutan ASC, b.nama_bagian ASC, pk.id DESC, p.nama ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'save_apel':
            $tanggal   = $_POST['tanggal'] ?? date('Y-m-d');
            $jenisApel = in_array($_POST['jenis_apel'] ?? '', ['pagi','sore']) ? $_POST['jenis_apel'] : 'pagi';
            $items     = json_decode($_POST['items'] ?? '[]', true);
            $pencatat  = $_POST['pencatat'] ?? ($_SESSION['username'] ?? 'system');

            if (empty($items)) throw new Exception('Data absensi kosong');

            $stmt = $pdo->prepare("
                INSERT INTO apel_nominal (tanggal, jenis_apel, personil_id, status, jam_hadir, keterangan, pencatat)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status=VALUES(status), jam_hadir=VALUES(jam_hadir), keterangan=VALUES(keterangan), pencatat=VALUES(pencatat)
            ");

            $saved = 0;
            foreach ($items as $item) {
                $validStatus = ['hadir','tidak_hadir','sakit','ijin','cuti','dinas_luar','tugas_belajar'];
                $st = in_array($item['status'] ?? '', $validStatus) ? $item['status'] : 'hadir';
                $stmt->execute([
                    $tanggal, $jenisApel, (int)$item['personil_id'],
                    $st, $item['jam_hadir'] ?? null, $item['keterangan'] ?? null, $pencatat
                ]);
                $saved++;
            }
            echo json_encode(['success' => true, 'message' => "Berhasil menyimpan $saved data apel", 'saved' => $saved]);
            break;

        case 'get_rekap':
            $bulan = $_GET['bulan'] ?? date('Y-m');
            $jenisApel = in_array($_GET['jenis_apel'] ?? '', ['pagi','sore','']) ? ($_GET['jenis_apel'] ?: null) : null;

            $sql = "
                SELECT p.id, p.nama, pk.nama_pangkat, b.nama_bagian, u.nama_unsur,
                    SUM(CASE WHEN a.status='hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN a.status='tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir,
                    SUM(CASE WHEN a.status='sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN a.status='ijin' THEN 1 ELSE 0 END) as ijin,
                    SUM(CASE WHEN a.status='cuti' THEN 1 ELSE 0 END) as cuti,
                    SUM(CASE WHEN a.status='dinas_luar' THEN 1 ELSE 0 END) as dinas_luar,
                    SUM(CASE WHEN a.status='tugas_belajar' THEN 1 ELSE 0 END) as tugas_belajar,
                    COUNT(a.id) as total_apel
                FROM personil p
                LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON p.id_unsur = u.id
                LEFT JOIN apel_nominal a ON a.personil_id = p.id AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
            ";
            $params = [$bulan];
            if ($jenisApel) { $sql .= " AND a.jenis_apel = ?"; $params[] = $jenisApel; }
            $sql .= " WHERE p.is_active = 1 AND p.is_deleted = 0
                       GROUP BY p.id ORDER BY u.urutan ASC, b.nama_bagian ASC, p.nama ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_unsur_bagian':
            $unsur = $pdo->query("SELECT id, nama_unsur FROM unsur ORDER BY urutan ASC")->fetchAll();
            $bagian = $pdo->query("SELECT id, nama_bagian, id_unsur FROM bagian WHERE is_active=1 ORDER BY urutan ASC, nama_bagian ASC")->fetchAll();
            echo json_encode(['success' => true, 'unsur' => $unsur, 'bagian' => $bagian]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
