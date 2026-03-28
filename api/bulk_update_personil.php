<?php
/**
 * Bulk Update API Endpoint
 * For safe database update from CSV
 */

// Define constants if not already defined
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'bagops');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'root');
}

header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get method
    $method = $_SERVER["REQUEST_METHOD"];
    
    if ($method === "POST") {
        // Get JSON data
        $json_data = file_get_contents("php://input");
        $personil_data = json_decode($json_data, true);
        
        if (!$personil_data || !isset($personil_data["personil"])) {
            throw new Exception("Invalid data format");
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Clear existing data
            $pdo->exec("DELETE FROM personil");
            
            // Get reference data
            $pangkat_stmt = $pdo->query("SELECT id, nama_pangkat FROM pangkat");
            $pangkat_ref = [];
            while ($row = $pangkat_stmt->fetch(PDO::FETCH_ASSOC)) {
                $pangkat_ref[strtoupper($row["nama_pangkat"])] = $row["id"];
            }
            
            $jabatan_stmt = $pdo->query("SELECT id, nama_jabatan FROM jabatan");
            $jabatan_ref = [];
            while ($row = $jabatan_stmt->fetch(PDO::FETCH_ASSOC)) {
                $jabatan_ref[strtoupper($row["nama_jabatan"])] = $row["id"];
            }
            
            $bagian_stmt = $pdo->query("SELECT id, nama_bagian FROM bagian");
            $bagian_ref = [];
            while ($row = $bagian_stmt->fetch(PDO::FETCH_ASSOC)) {
                $bagian_ref[strtoupper($row["nama_bagian"])] = $row["id"];
            }
            
            // Insert new data
            $insert_count = 0;
            $error_count = 0;
            
            foreach ($personil_data["personil"] as $personil) {
                $db_data = [
                    "nama" => $personil["nama"] ?? "",
                    "nrp" => $personil["nrp"] ?? "",
                    "status_ket" => $personil["status_ket"] ?? "aktif",
                    "status_kepegawaian" => $personil["status_kepegawaian"] ?? "POLRI"
                ];
                
                // Map references
                if (!empty($personil["pangkat"])) {
                    $pangkat_key = strtoupper(trim($personil["pangkat"]));
                    $db_data["pangkat_id"] = $pangkat_ref[$pangkat_key] ?? null;
                }
                
                if (!empty($personil["jabatan"])) {
                    $jabatan_key = strtoupper(trim($personil["jabatan"]));
                    $db_data["jabatan_id"] = $jabatan_ref[$jabatan_key] ?? null;
                }
                
                if (!empty($personil["bagian"])) {
                    $bagian_key = strtoupper(trim($personil["bagian"]));
                    $db_data["bagian_id"] = $bagian_ref[$bagian_key] ?? null;
                }
                
                // Insert
                $sql = "INSERT INTO personil (nama, nrp, pangkat_id, jabatan_id, bagian_id, status_ket, status_kepegawaian) 
                        VALUES (:nama, :nrp, :pangkat_id, :jabatan_id, :bagian_id, :status_ket, :status_kepegawaian)";
                
                $stmt = $pdo->prepare($sql);
                
                // Handle null values for foreign keys
                if (empty($db_data["pangkat_id"])) $db_data["pangkat_id"] = null;
                if (empty($db_data["jabatan_id"])) $db_data["jabatan_id"] = null;
                if (empty($db_data["bagian_id"])) $db_data["bagian_id"] = null;
                
                $stmt->execute($db_data);
                $insert_count++;
            }
            
            // Commit transaction
            $pdo->commit();
            
            echo json_encode([
                "success" => true,
                "message" => "Bulk update completed",
                "inserted" => $insert_count,
                "errors" => $error_count
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        // GET method - show current status
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM personil");
        $count = $stmt->fetch()["total"];
        
        echo json_encode([
            "success" => true,
            "message" => "Bulk update API ready",
            "current_count" => $count,
            "method" => "POST to update data"
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>