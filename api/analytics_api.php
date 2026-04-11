<?php
/**
 * Analytics API — Dashboard Statistics & Fairness Analysis
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/CSRFHelper.php';
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

// CSRF protection
CSRFHelper::applyProtection(['get_piket_trend','get_fairness_index','get_personil_workload']);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // ── GET: Piket trend per month (6 months) ───────────────────────────────
    if ($action === 'get_piket_trend') {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(shift_date, '%Y-%m') as bulan,
                COUNT(DISTINCT personil_id) as personil_unik,
                COUNT(*) as total_jadwal,
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir
            FROM schedules s
            WHERE shift_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(shift_date, '%Y-%m')
            ORDER BY bulan
        ");
        $stmt->execute();
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    // ── GET: Fairness index (distribusi jam piket per personil) ─────────────
    if ($action === 'get_fairness_index') {
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                COUNT(s.id) as total_jadwal,
                SUM(CASE WHEN s.shift_type='PAGI' THEN 8 
                         WHEN s.shift_type='SIANG' THEN 8 
                         WHEN s.shift_type='MALAM' THEN 10 
                         ELSE 8 END) as total_jam,
                b.nama_bagian
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            LEFT JOIN bagian b ON b.id = p.id_bagian
            WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            GROUP BY p.nrp, p.nama, b.nama_bagian
            ORDER BY total_jam DESC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate fairness metrics
        if ($data) {
            $jam = array_column($data, 'total_jam');
            $avg = array_sum($jam) / count($jam);
            $max = max($jam);
            $min = min($jam);
            $variance = 0;
            foreach ($jam as $j) $variance += pow($j - $avg, 2);
            $stdDev = sqrt($variance / count($jam));
            
            $fairness = [
                'avg_jam' => round($avg, 1),
                'max_jam' => $max,
                'min_jam' => $min,
                'std_dev' => round($stdDev, 1),
                'personil_count' => count($data),
                'fairness_score' => round(100 - ($stdDev / $avg * 100), 1)
            ];
        } else {
            $fairness = ['avg_jam' => 0, 'max_jam' => 0, 'min_jam' => 0, 'std_dev' => 0, 'personil_count' => 0, 'fairness_score' => 100];
        }
        
        echo json_encode(['success'=>true,'data'=>$data,'fairness'=>$fairness]); exit;
    }

    // ── GET: Personil workload summary ───────────────────────────────────────
    if ($action === 'get_personil_workload') {
        $stmt = $pdo->prepare("
            SELECT 
                p.nrp,
                p.nama,
                p.pangkat,
                COUNT(DISTINCT s.shift_date) as hari_piket,
                COUNT(s.id) as total_shift,
                SUM(CASE WHEN s.shift_type='MALAM' THEN 1 ELSE 0 END) as shift_malam
            FROM schedules s
            JOIN personil p ON p.nrp = s.personil_id
            WHERE s.shift_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            GROUP BY p.nrp, p.nama, p.pangkat
            ORDER BY total_shift DESC
            LIMIT 20
        ");
        $stmt->execute();
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
