<?php
/**
 * Personil Import API — Bulk Import from Excel/CSV
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
CSRFHelper::applyProtection(['preview_import']);

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $action = $_REQUEST['action'] ?? '';

    // ── POST: preview import from CSV/Excel ───────────────────────────────
    if ($action === 'preview_import') {
        if (!isset($_FILES['file'])) throw new Exception('File tidak ditemukan');
        
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            throw new Exception('Format file harus CSV atau Excel (xlsx/xls)');
        }
        
        $data = [];
        
        if ($ext === 'csv') {
            // Parse CSV
            $handle = fopen($file['tmp_name'], 'r');
            $headers = fgetcsv($handle); // Skip header
            $rowNum = 0;
            while (($row = fgetcsv($handle)) !== false && $rowNum < 100) {
                $rowNum++;
                if (count($row) < 3) continue; // Skip incomplete rows
                $data[] = [
                    'nrp' => $row[0] ?? '',
                    'nama' => $row[1] ?? '',
                    'pangkat' => $row[2] ?? '',
                    'jabatan' => $row[3] ?? '',
                    'bagian' => $row[4] ?? '',
                    'unsur' => $row[5] ?? '',
                    'no_hp' => $row[6] ?? ''
                ];
            }
            fclose($handle);
        } else {
            // For Excel, we'd need a library like PhpSpreadsheet
            // For now, return error suggesting CSV format
            throw new Exception('Untuk Excel, gunakan library PhpSpreadsheet. Silakan konversi ke CSV terlebih dahulu.');
        }
        
        echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]); exit;
    }

    // ── POST: execute import ───────────────────────────────────────────────
    if ($action === 'execute_import') {
        $json = $_POST['data'] ?? '[]';
        $personil = json_decode($json, true);
        if (!is_array($personil) || empty($personil)) throw new Exception('Data tidak valid');
        
        $pdo->beginTransaction();
        $imported = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($personil as $p) {
            $nrp = trim($p['nrp'] ?? '');
            $nama = trim($p['nama'] ?? '');
            
            if (!$nrp || !$nama) {
                $errors[] = "Barai kosong: nrp=$nrp, nama=$nama";
                continue;
            }
            
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM personil WHERE nrp=?");
            $stmt->execute([$nrp]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($exists) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE personil SET nama=?, pangkat=?, jabatan=?, no_hp=?
                    WHERE nrp=?
                ");
                $stmt->execute([$nama, $p['pangkat'], $p['jabatan'], $p['no_hp'], $nrp]);
                $updated++;
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO personil (nrp, nama, pangkat, jabatan, no_hp, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$nrp, $nama, $p['pangkat'], $p['jabatan'], $p['no_hp']]);
                $imported++;
            }
        }
        
        $pdo->commit();
        
        ActivityLog::logCreate('personil', null, "Bulk import: $imported new, $updated updated");
        
        echo json_encode([
            'success'=>true,
            'imported'=>$imported,
            'updated'=>$updated,
            'errors'=>$errors
        ]); exit;
    }

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
