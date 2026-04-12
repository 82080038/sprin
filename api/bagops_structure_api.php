<?php
/**
 * BAGOPS Structure Management API
 * Manajemen struktur organisasi Bagian Operasional POLRI
 */

require_once 'config.php';
header('Content-Type: application/json');

// Start session for authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_structure':
        getBagOpsStructure();
        break;
    case 'get_personil_by_jabatan':
        getPersonilByJabatan();
        break;
    case 'create_structure':
        createBagOpsStructure();
        break;
    case 'update_structure':
        updateBagOpsStructure();
        break;
    case 'delete_structure':
        deleteBagOpsStructure();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getBagOpsStructure() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM bagops_structure ORDER BY id");
        $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($structures as &$structure) {
            if ($structure['bawahan']) {
                $structure['bawahan'] = json_decode($structure['bawahan'], true);
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $structures
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getPersonilByJabatan() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.nrp, p.nama, pk.nama_pangkat, b.nama_bagian, j.nama_jabatan
            FROM personil p 
            LEFT JOIN pangkat pk ON p.id_pangkat = pk.id 
            LEFT JOIN bagian b ON p.id_bagian = b.id
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            WHERE p.is_active = 1 AND p.is_deleted = 0
            ORDER BY p.nama
        ");
        $stmt->execute();
        $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $personil
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createBagOpsStructure() {
    global $pdo;
    
    $jabatan = $_POST['jabatan'] ?? '';
    $pangkat = $_POST['pangkat'] ?? '';
    $eselon = $_POST['eselon'] ?? '';
    $atasan = $_POST['atasan'] ?? '';
    $bawahan = $_POST['bawahan'] ?? [];
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($jabatan) || empty($pangkat)) {
        echo json_encode(['success' => false, 'message' => 'Jabatan dan pangkat wajib diisi']);
        return;
    }
    
    try {
        // Encode bawahan to JSON
        $bawahan_json = is_array($bawahan) ? json_encode($bawahan) : $bawahan;
        
        $stmt = $pdo->prepare("
            INSERT INTO bagops_structure 
            (jabatan, pangkat, eselon, atasan, bawahan, deskripsi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$jabatan, $pangkat, $eselon, $atasan, $bawahan_json, $deskripsi]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Struktur BAGOPS berhasil dibuat',
            'data' => ['id' => $pdo->lastInsertId()]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateBagOpsStructure() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $pangkat = $_POST['pangkat'] ?? '';
    $eselon = $_POST['eselon'] ?? '';
    $atasan = $_POST['atasan'] ?? '';
    $bawahan = $_POST['bawahan'] ?? [];
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($id) || empty($jabatan) || empty($pangkat)) {
        echo json_encode(['success' => false, 'message' => 'ID, jabatan, dan pangkat wajib diisi']);
        return;
    }
    
    try {
        // Encode bawahan to JSON
        $bawahan_json = is_array($bawahan) ? json_encode($bawahan) : $bawahan;
        
        $stmt = $pdo->prepare("
            UPDATE bagops_structure 
            SET jabatan = ?, pangkat = ?, eselon = ?, atasan = ?, bawahan = ?, deskripsi = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$jabatan, $pangkat, $eselon, $atasan, $bawahan_json, $deskripsi, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Struktur BAGOPS berhasil diperbarui'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteBagOpsStructure() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID wajib diisi']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bagops_structure WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Struktur BAGOPS berhasil dihapus'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Struktur tidak ditemukan']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
