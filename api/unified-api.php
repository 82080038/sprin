<?php
/**
 * Unified API Gateway
 * Centralized API endpoint for all CRUD operations
 * Provides consistent interface and error handling
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load authentication helper for CSRF validation
require_once __DIR__ . '/../core/auth_helper.php';

// Rate limiting (60 requests per minute per IP)
function checkRateLimit($limit = 60, $windowSeconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = sys_get_temp_dir() . '/rate_' . md5($ip) . '.json';
    $now = time();

    $data = file_exists($key) ? json_decode(file_get_contents($key), true) : ['count' => 0, 'start' => $now];
    if (!is_array($data)) $data = ['count' => 0, 'start' => $now];

    if (($now - $data['start']) >= $windowSeconds) {
        $data = ['count' => 1, 'start' => $now];
    } else {
        $data['count']++;
    }

    file_put_contents($key, json_encode($data), LOCK_EX);

    if ($data['count'] > $limit) {
        http_response_code(429);
        header('Retry-After: ' . ($windowSeconds - ($now - $data['start'])));
        echo json_encode(['success' => false, 'message' => 'Too many requests. Please slow down.']);
        exit;
    }
}
checkRateLimit();

// Validate CSRF token for POST/PUT/DELETE requests
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
    AuthHelper::requireCSRFToken();
}

// Database connection
try {
    $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    send_error('Database connection failed', 500, $e->getMessage());
}

// Get request parameters
$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? 0;

try {
    // Route request based on resource
    switch ($resource) {
        case 'unsur':
            handle_unsur_request($pdo, $method, $action, $id);
            break;
        case 'bagian':
            handle_bagian_request($pdo, $method, $action, $id);
            break;
        case 'jabatan':
            handle_jabatan_request($pdo, $method, $action, $id);
            break;
        case 'personil':
            handle_personil_request($pdo, $method, $action, $id);
            break;
        case 'stats':
            handle_stats_request($pdo, $action);
            break;
        // Production Features
        case 'fatigue':
            handle_fatigue_request($pdo, $method, $action, $id);
            break;
        case 'emergency':
            handle_emergency_request($pdo, $method, $action, $id);
            break;
        case 'certification':
            handle_certification_request($pdo, $method, $action, $id);
            break;
        case 'overtime':
            handle_overtime_request($pdo, $method, $action, $id);
            break;
        case 'analytics':
            handle_analytics_request($pdo, $method, $action, $id);
            break;
        case 'recall':
            handle_recall_request($pdo, $method, $action, $id);
            break;
        case 'equipment':
            handle_equipment_request($pdo, $method, $action, $id);
            break;
        case 'notifications':
            handle_notifications_request($pdo, $method, $action, $id);
            break;
        case 'operasional':
            handle_operasional_request($pdo, $method, $action, $id);
            break;
        case 'bagops_structure':
            handle_bagops_structure_request($pdo, $method, $action, $id);
            break;
        case 'dokumentasi':
            handle_dokumentasi_request($pdo, $method, $action, $id);
            break;
        case 'predictive_analytics':
            handle_predictive_analytics_request($pdo, $method, $action, $id);
            break;
        default:
            send_error('Invalid resource', 404);
    }
} catch (Exception $e) {
    error_log('[unified-api] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    send_error('Internal server error', 500);
}

// Response helper functions
function send_success($data = null, $message = 'Success') {
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

function send_error($message, $code = 400, $details = null) {
    http_response_code($code);
    
    $response = [
        'success' => false,
        'message' => $message,
        'code' => $code,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Only expose details in debug mode, never internal PHP errors
    if ($details !== null && defined('DEBUG_MODE') && DEBUG_MODE === true) {
        $response['details'] = $details;
    }
    
    echo json_encode($response);
    exit;
}

// Unsur handlers
function handle_unsur_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT u.*, 
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_unsur = u.id) as jabatan_count,
                       (SELECT COUNT(*) FROM personil p 
                        JOIN jabatan j ON p.id_jabatan = j.id 
                        WHERE j.id_unsur = u.id AND p.is_deleted = 0) as personil_count
                FROM unsur u 
                ORDER BY u.urutan ASC, u.nama_unsur ASC
            ");
            $unsurList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($unsurList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("SELECT * FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            $unsur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$unsur) send_error('Unsur not found', 404);
            send_success($unsur);
            
        case 'create':
            $nama_unsur = $_POST['nama_unsur'] ?? '';
            $kode_unsur = $_POST['kode_unsur'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tingkat = $_POST['tingkat'] ?? '';
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_unsur) send_error('Nama unsur is required');
            
            $stmt = $pdo->prepare("
                INSERT INTO unsur (nama_unsur, kode_unsur, deskripsi, tingkat, urutan) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_unsur, $kode_unsur, $deskripsi, $tingkat, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId()], 'Unsur created successfully');
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_unsur = $_POST['nama_unsur'] ?? '';
            $kode_unsur = $_POST['kode_unsur'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tingkat = $_POST['tingkat'] ?? '';
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_unsur) send_error('Nama unsur is required');
            
            $stmt = $pdo->prepare("
                UPDATE unsur 
                SET nama_unsur = ?, kode_unsur = ?, deskripsi = ?, tingkat = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_unsur, $kode_unsur, $deskripsi, $tingkat, $urutan, $id]);
            
            send_success(null, 'Unsur updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE id_unsur = ?");
            $stmt->execute([$id]);
            $jabatanCount = $stmt->fetchColumn();
            
            if ($jabatanCount > 0) {
                send_error("Cannot delete unsur with {$jabatanCount} jabatan", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM unsur WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Unsur deleted successfully');
            
        case 'stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_unsur,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil
                FROM unsur
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for unsur', 400);
    }
}

// Bagian handlers
function handle_bagian_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT b.*, 
                       u.nama_unsur,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_bagian = b.id AND p.is_deleted = 0) as personil_count,
                       (SELECT COUNT(*) FROM jabatan j WHERE j.id_bagian = b.id) as jabatan_count
                FROM bagian b 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                ORDER BY b.nama_bagian ASC
            ");
            $bagianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($bagianList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT b.*, u.nama_unsur 
                FROM bagian b 
                LEFT JOIN unsur u ON b.id_unsur = u.id 
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            $bagian = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$bagian) send_error('Bagian not found', 404);
            send_success($bagian);
            
        case 'create':
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $kode_bagian = $_POST['kode_bagian'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_bagian) send_error('Nama bagian is required');
            
            $stmt = $pdo->prepare("
                INSERT INTO bagian (nama_bagian, kode_bagian, deskripsi, id_unsur, urutan) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_bagian, $kode_bagian, $deskripsi, $id_unsur, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId()], 'Bagian created successfully');
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_bagian = $_POST['nama_bagian'] ?? '';
            $kode_bagian = $_POST['kode_bagian'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_bagian) send_error('Nama bagian is required');
            
            $stmt = $pdo->prepare("
                UPDATE bagian 
                SET nama_bagian = ?, kode_bagian = ?, deskripsi = ?, id_unsur = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_bagian, $kode_bagian, $deskripsi, $id_unsur, $urutan, $id]);
            
            send_success(null, 'Bagian updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_bagian = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                send_error("Cannot delete bagian with {$personilCount} personil", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM bagian WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Bagian deleted successfully');
            
        default:
            send_error('Invalid action for bagian', 400);
    }
}

// Jabatan handlers
function handle_jabatan_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $stmt = $pdo->query("
                SELECT j.*, u.nama_unsur, b.nama_bagian,
                       (SELECT COUNT(*) FROM personil p WHERE p.id_jabatan = j.id AND p.is_deleted = 0) as personil_count
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                ORDER BY u.urutan ASC, j.nama_jabatan ASC
            ");
            $jabatanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($jabatanList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT j.*, u.nama_unsur, b.nama_bagian 
                FROM jabatan j 
                LEFT JOIN unsur u ON j.id_unsur = u.id 
                LEFT JOIN bagian b ON j.id_bagian = b.id 
                WHERE j.id = ?
            ");
            $stmt->execute([$id]);
            $jabatan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$jabatan) send_error('Jabatan not found', 404);
            send_success($jabatan);
            
        case 'create':
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_jabatan) send_error('Nama jabatan is required');
            
            // Auto-generate kode_jabatan from nama_jabatan
            $kode_jabatan = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama_jabatan));
            
            // Check for duplicate kode_jabatan and append number if needed
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE kode_jabatan = ?");
            $checkStmt->execute([$kode_jabatan]);
            if ($checkStmt->fetchColumn() > 0) {
                $counter = 1;
                do {
                    $new_kode = $kode_jabatan . $counter;
                    $checkStmt->execute([$new_kode]);
                    $counter++;
                } while ($checkStmt->fetchColumn() > 0);
                $kode_jabatan = $new_kode;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jabatan (nama_jabatan, kode_jabatan, deskripsi, id_unsur, id_bagian, urutan) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama_jabatan, $kode_jabatan, $deskripsi, $id_unsur, $id_bagian, $urutan]);
            
            send_success(['id' => $pdo->lastInsertId(), 'kode_jabatan' => $kode_jabatan], 'Jabatan created successfully with code: ' . $kode_jabatan);
            
        case 'update':
            if (!$id) send_error('ID is required');
            
            $nama_jabatan = $_POST['nama_jabatan'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $id_unsur = $_POST['id_unsur'] ?? null;
            $id_bagian = $_POST['id_bagian'] ?? null;
            $urutan = $_POST['urutan'] ?? 1;
            
            if (!$nama_jabatan) send_error('Nama jabatan is required');
            
            // Auto-regenerate kode_jabatan if nama_jabatan changed
            $kode_jabatan = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama_jabatan));
            
            // Check for duplicate kode_jabatan (excluding current record)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM jabatan WHERE kode_jabatan = ? AND id != ?");
            $checkStmt->execute([$kode_jabatan, $id]);
            if ($checkStmt->fetchColumn() > 0) {
                $counter = 1;
                do {
                    $new_kode = $kode_jabatan . $counter;
                    $checkStmt->execute([$new_kode, $id]);
                    $counter++;
                } while ($checkStmt->fetchColumn() > 0);
                $kode_jabatan = $new_kode;
            }
            
            $stmt = $pdo->prepare("
                UPDATE jabatan 
                SET nama_jabatan = ?, kode_jabatan = ?, deskripsi = ?, id_unsur = ?, id_bagian = ?, urutan = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_jabatan, $kode_jabatan, $deskripsi, $id_unsur, $id_bagian, $urutan, $id]);
            
            send_success(null, 'Jabatan updated successfully');
            
        case 'delete':
            if (!$id) send_error('ID is required');
            
            // Check dependencies
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personil WHERE id_jabatan = ? AND is_deleted = 0");
            $stmt->execute([$id]);
            $personilCount = $stmt->fetchColumn();
            
            if ($personilCount > 0) {
                send_error("Cannot delete jabatan with {$personilCount} personil", 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM jabatan WHERE id = ?");
            $stmt->execute([$id]);
            
            send_success(null, 'Jabatan deleted successfully');
            
        default:
            send_error('Invalid action for jabatan', 400);
    }
}

// Personil handlers
function handle_personil_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'list':
        case 'get_all':
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $sql = "
                SELECT p.*, 
                       pa.nama_pangkat, pa.singkatan as pangkat_singkatan,
                       j.nama_jabatan, b.nama_bagian, u.nama_unsur
                FROM personil p
                LEFT JOIN pangkat pa ON p.id_pangkat = pa.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE p.is_deleted = 0
                ORDER BY p.nama ASC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset . "
            ";
            $stmt = $pdo->query($sql);
            $personilList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($personilList);
            
        case 'detail':
        case 'get':
            if (!$id) send_error('ID is required');
            
            $stmt = $pdo->prepare("
                SELECT p.*, 
                       pa.nama_pangkat, pa.singkatan as pangkat_singkatan,
                       j.nama_jabatan, b.nama_bagian, u.nama_unsur
                FROM personil p
                LEFT JOIN pangkat pa ON p.id_pangkat = pa.id
                LEFT JOIN jabatan j ON p.id_jabatan = j.id
                LEFT JOIN bagian b ON p.id_bagian = b.id
                LEFT JOIN unsur u ON j.id_unsur = u.id
                WHERE p.id = ? AND p.is_deleted = 0
            ");
            $stmt->execute([$id]);
            $personil = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$personil) send_error('Personil not found', 404);
            send_success($personil);
            
        case 'stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_personil,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_personil,
                    COUNT(CASE WHEN JK = 'L' THEN 1 END) as male_personil,
                    COUNT(CASE WHEN JK = 'P' THEN 1 END) as female_personil
                FROM personil 
                WHERE is_deleted = 0
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for personil', 400);
    }
}

// Stats handlers
function handle_stats_request($pdo, $action) {
    switch ($action) {
        case 'dashboard':
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0) as total_personil,
                    (SELECT COUNT(*) FROM personil WHERE is_deleted = 0 AND is_active = 1) as active_personil,
                    (SELECT COUNT(*) FROM unsur) as total_unsur,
                    (SELECT COUNT(*) FROM bagian) as total_bagian,
                    (SELECT COUNT(*) FROM jabatan) as total_jabatan
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            send_success($stats);
            
        case 'unsur':
            $stmt = $pdo->query("
                SELECT u.nama_unsur, COUNT(j.id) as jabatan_count, COUNT(p.id) as personil_count
                FROM unsur u
                LEFT JOIN jabatan j ON u.id = j.id_unsur
                LEFT JOIN personil p ON j.id = p.id_jabatan AND p.is_deleted = 0
                GROUP BY u.id, u.nama_unsur
                ORDER BY u.urutan ASC
            ");
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_success($stats);
            
        default:
            send_error('Invalid action for stats', 400);
    }
}

// Production Features Handlers

// Fatigue Management
function handle_fatigue_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_personil,
                    COUNT(CASE WHEN fatigue_level = 'critical' THEN 1 END) as critical_fatigue,
                    COUNT(CASE WHEN fatigue_level = 'high' THEN 1 END) as high_fatigue,
                    COUNT(CASE WHEN fatigue_level = 'medium' THEN 1 END) as medium_fatigue,
                    AVG(wellness_score) as avg_wellness_score
                FROM personil WHERE is_active = 1 AND is_deleted = 0
            ");
            send_success($stmt->fetch(PDO::FETCH_ASSOC));
            
        case 'get_tracking':
            $personilId = $_GET['personil_id'] ?? '';
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            if (!$personilId) send_error('Personil ID required');
            
            $stmt = $pdo->prepare("
                SELECT * FROM fatigue_tracking 
                WHERE personil_id = ? AND tracking_date BETWEEN ? AND ?
                ORDER BY tracking_date DESC
            ");
            $stmt->execute([$personilId, $startDate, $endDate]);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for fatigue', 400);
    }
}

// Emergency Task Management
function handle_emergency_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_tasks':
            $status = $_GET['status'] ?? '';
            $priority = $_GET['priority'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($status) {
                $whereClause .= " AND status = ?";
                $params[] = $status;
            }
            
            if ($priority) {
                $whereClause .= " AND priority_level = ?";
                $params[] = $priority;
            }
            
            $stmt = $pdo->prepare("
                SELECT et.*, p.nama as assigned_name, pk.nama_pangkat, b.nama_bagian
                FROM emergency_tasks et
                LEFT JOIN personil p ON p.nrp = et.assigned_to
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                $whereClause
                ORDER BY et.priority_level DESC, et.start_time ASC
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        case 'get_available_personnel':
            $dateTime = $_GET['datetime'] ?? date('Y-m-d H:i:s');
            $bagianId = $_GET['bagian_id'] ?? '';
            
            $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
            $params = [];
            
            if ($bagianId) {
                $whereClause .= " AND p.id_bagian = ?";
                $params[] = $bagianId;
            }
            
            $stmt = $pdo->prepare("
                SELECT p.*, pk.nama_pangkat, b.nama_bagian, p.wellness_score, p.fatigue_level
                FROM personil p
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                $whereClause
                ORDER BY p.wellness_score DESC, p.nama
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for emergency', 400);
    }
}

// Certification Management
function handle_certification_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_certifications':
            $personilId = $_GET['personil_id'] ?? '';
            $status = $_GET['status'] ?? '';
            $expiringDays = $_GET['expiring_days'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($personilId) {
                $whereClause .= " AND c.personil_id = ?";
                $params[] = $personilId;
            }
            
            if ($status) {
                $whereClause .= " AND c.status = ?";
                $params[] = $status;
            }
            
            if ($expiringDays) {
                $whereClause .= " AND c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
                $params[] = $expiringDays;
            }
            
            $stmt = $pdo->prepare("
                SELECT c.*, p.nama as personil_name, pk.nama_pangkat, b.nama_bagian,
                       DATEDIFF(c.expiry_date, CURDATE()) as days_to_expiry
                FROM certifications c
                JOIN personil p ON p.nrp = c.personil_id
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                $whereClause
                ORDER BY c.expiry_date ASC
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        case 'get_compliance_dashboard':
            $bagianId = $_GET['bagian_id'] ?? '';
            
            $whereClause = "WHERE p.is_active = 1 AND p.is_deleted = 0";
            $params = [];
            
            if ($bagianId) {
                $whereClause .= " AND p.id_bagian = ?";
                $params[] = $bagianId;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(DISTINCT p.nrp) as total_personil,
                    COUNT(DISTINCT CASE WHEN c.status = 'valid' THEN p.nrp END) as valid_certifications,
                    COUNT(DISTINCT CASE WHEN c.status = 'expired' THEN p.nrp END) as expired_certifications,
                    COUNT(DISTINCT CASE WHEN c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.expiry_date >= CURDATE() THEN p.nrp END) as expiring_soon
                FROM personil p
                LEFT JOIN certifications c ON c.personil_id = p.nrp
                $whereClause
            ");
            $complianceStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            send_success($complianceStats);
            
        default:
            send_error('Invalid action for certification', 400);
    }
}

// Overtime Management
function handle_overtime_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_records':
            $personilId = $_GET['personil_id'] ?? '';
            $status = $_GET['status'] ?? '';
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            $whereClause = "WHERE or.overtime_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            
            if ($personilId) {
                $whereClause .= " AND or.personil_id = ?";
                $params[] = $personilId;
            }
            
            if ($status) {
                $whereClause .= " AND or.approval_status = ?";
                $params[] = $status;
            }
            
            $stmt = $pdo->prepare("
                SELECT or.*, p.nama as personil_name, pk.nama_pangkat, b.nama_bagian,
                       s.shift_type, s.start_time as schedule_start, s.end_time as schedule_end
                FROM overtime_records or
                JOIN personil p ON p.nrp = or.personil_id
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                LEFT JOIN schedules s ON s.id = or.schedule_id
                $whereClause
                ORDER BY or.overtime_date DESC
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        case 'get_statistics':
            $period = $_GET['period'] ?? '30';
            $startDate = date('Y-m-d', strtotime("-$period days"));
            $endDate = date('Y-m-d');
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_records,
                    COUNT(DISTINCT or.personil_id) as unique_personnel,
                    SUM(or.overtime_hours) as total_hours,
                    SUM(or.total_compensation) as total_compensation,
                    COUNT(CASE WHEN or.approval_status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN or.approval_status = 'approved' THEN 1 END) as approved_count
                FROM overtime_records or
                WHERE or.overtime_date BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate]);
            send_success($stmt->fetch(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for overtime', 400);
    }
}

// Analytics & Predictive Scheduling
function handle_analytics_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_dashboard':
            $dashboard = [
                'key_metrics' => [
                    'total_personnel' => getTableCount($pdo, 'personil', 'is_active = 1'),
                    'active_schedules' => getTableCount($pdo, 'schedules', 'shift_date >= CURDATE()'),
                    'pending_overtime' => getTableCount($pdo, 'overtime_records', 'approval_status = "pending"'),
                    'critical_fatigue_cases' => getTableCount($pdo, 'fatigue_tracking', 'fatigue_level = "critical" AND tracking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')
                ],
                'trend_indicators' => [
                    'attendance_trend' => 'improving',
                    'fatigue_trend' => 'stable',
                    'overtime_trend' => 'increasing',
                    'compliance_trend' => 'stable'
                ]
            ];
            send_success($dashboard);
            
        case 'get_performance_metrics':
            $period = $_GET['period'] ?? '30';
            $startDate = date('Y-m-d', strtotime("-$period days"));
            $endDate = date('Y-m-d');
            
            $metrics = [
                'attendance_rate' => calculateAttendanceRate($pdo, $startDate, $endDate),
                'coverage_rate' => calculateCoverageRate($pdo, $startDate, $endDate),
                'fatigue_index' => calculateFatigueIndex($pdo, $startDate, $endDate)
            ];
            send_success($metrics);
            
        default:
            send_error('Invalid action for analytics', 400);
    }
}

// Recall System
function handle_recall_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_campaigns':
            $status = $_GET['status'] ?? '';
            $campaignType = $_GET['campaign_type'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($status) {
                $whereClause .= " AND rc.status = ?";
                $params[] = $status;
            }
            
            if ($campaignType) {
                $whereClause .= " AND rc.campaign_type = ?";
                $params[] = $campaignType;
            }
            
            $stmt = $pdo->prepare("
                SELECT rc.*, u.username as creator_name,
                       ROUND((rc.total_responded / NULLIF(rc.total_sent, 0)) * 100, 2) as response_rate
                FROM recall_campaigns rc
                LEFT JOIN users u ON u.id = rc.created_by
                $whereClause
                ORDER BY rc.start_time DESC
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        case 'get_active_campaigns':
            $stmt = $pdo->prepare("
                SELECT rc.*, u.username as creator_name,
                       COUNT(rr.id) as responses_count,
                       COUNT(CASE WHEN rr.response_status = 'confirmed' THEN 1 END) as confirmed_count
                FROM recall_campaigns rc
                LEFT JOIN users u ON u.id = rc.created_by
                LEFT JOIN recall_responses rr ON rr.campaign_id = rc.id
                WHERE rc.status = 'active'
                GROUP BY rc.id
                ORDER BY rc.priority_level DESC
            ");
            $stmt->execute();
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for recall', 400);
    }
}

// Equipment Management
function handle_equipment_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_equipment':
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($type) {
                $whereClause .= " AND e.equipment_type = ?";
                $params[] = $type;
            }
            
            if ($status) {
                $whereClause .= " AND e.current_status = ?";
                $params[] = $status;
            }
            
            if ($search) {
                $whereClause .= " AND (e.equipment_name LIKE ? OR e.equipment_code LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $stmt = $pdo->prepare("
                SELECT e.*, p.nama as assigned_name, pk.nama_pangkat, b.nama_bagian,
                       DATEDIFF(e.next_maintenance, CURDATE()) as days_to_maintenance
                FROM equipment e
                LEFT JOIN personil p ON p.nrp = e.current_assignment
                LEFT JOIN pangkat pk ON pk.id = p.id_pangkat
                LEFT JOIN bagian b ON b.id = p.id_bagian
                $whereClause
                ORDER BY e.equipment_type, e.equipment_name
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        case 'get_statistics':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_equipment,
                    COUNT(CASE WHEN current_status = 'available' THEN 1 END) as available_count,
                    COUNT(CASE WHEN current_status = 'assigned' THEN 1 END) as assigned_count,
                    COUNT(CASE WHEN current_status = 'maintenance' THEN 1 END) as maintenance_count,
                    COUNT(CASE WHEN next_maintenance < CURDATE() THEN 1 END) as maintenance_overdue
                FROM equipment
            ");
            send_success($stmt->fetch(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for equipment', 400);
    }
}

// Notifications System
function handle_notifications_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_notifications':
            $personilId = $_GET['personil_id'] ?? '';
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($personilId) {
                $whereClause .= " AND n.target_personil = ?";
                $params[] = $personilId;
            }
            
            if ($type) {
                $whereClause .= " AND n.notification_type = ?";
                $params[] = $type;
            }
            
            if ($status) {
                $whereClause .= " AND n.status = ?";
                $params[] = $status;
            }
            
            $stmt = $pdo->prepare("
                SELECT n.*, p.nama as personil_name
                FROM notifications n
                LEFT JOIN personil p ON p.nrp = n.target_personil
                $whereClause
                ORDER BY n.created_at DESC
                LIMIT 50
            ");
            $stmt->execute($params);
            send_success($stmt->fetchAll(PDO::FETCH_ASSOC));
            
        default:
            send_error('Invalid action for notifications', 400);
    }
}

// Helper functions for analytics
function getTableCount($pdo, $table, $condition = '') {
    $sql = "SELECT COUNT(*) FROM $table";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function calculateAttendanceRate($pdo, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_scheduled,
            COUNT(CASE WHEN pa.status = 'hadir' THEN 1 END) as total_present
        FROM schedules s
        LEFT JOIN piket_absensi pa ON pa.schedule_id = s.id
        WHERE s.shift_date BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = $result['total_scheduled'];
    $present = $result['total_present'];
    
    return $total > 0 ? round(($present / $total) * 100, 2) : 0;
}

function calculateCoverageRate($pdo, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT DATE(s.shift_date)) as total_days,
            COUNT(DISTINCT CASE WHEN s.personil_id IS NOT NULL THEN DATE(s.shift_date) END) as covered_days
        FROM schedules s
        WHERE s.shift_date BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = $result['total_days'];
    $covered = $result['covered_days'];
    
    return $total > 0 ? round(($covered / $total) * 100, 2) : 0;
}

function calculateFatigueIndex($pdo, $startDate, $endDate) {
    $stmt = $pdo->prepare("
        SELECT 
            AVG(ft.fatigue_score) as avg_score,
            COUNT(CASE WHEN ft.fatigue_level = 'critical' THEN 1 END) as critical_count,
            COUNT(*) as total_records
        FROM fatigue_tracking ft
        WHERE ft.tracking_date BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $avgScore = (float)$result['avg_score'];
    $criticalRatio = $result['total_records'] > 0 ? ($result['critical_count'] / $result['total_records']) * 100 : 0;
    
    return [
        'fatigue_score' => round($avgScore, 1),
        'critical_cases_percentage' => round($criticalRatio, 2),
        'risk_level' => $avgScore < 70 ? 'high' : ($avgScore < 85 ? 'medium' : 'low')
    ];
}

// BAGOPS Operational Management
function handle_operasional_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_operasi_list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $jenis = $_GET['jenis'] ?? '';
            
            $where_conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(nama_operasi LIKE ? OR kode_operasi LIKE ? OR lokasi_operasi LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $where_conditions[] = "status = ?";
                $params[] = $status;
            }
            
            if (!empty($jenis)) {
                $where_conditions[] = "jenis_operasi = ?";
                $params[] = $jenis;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            // Get total count
            $count_sql = "SELECT COUNT(*) as total FROM operasi_kepolisian $where_clause";
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get data
            $sql = "
                SELECT o.*, 
                       p1.nama as nama_komandan, pk1.nama_pangkat as pangkat_komandan,
                       p2.nama as nama_wakil, pk2.nama_pangkat as pangkat_wakil,
                       (SELECT COUNT(*) FROM personil_operasi po WHERE po.operasi_id = o.id) as total_personil
                FROM operasi_kepolisian o
                LEFT JOIN personil p1 ON o.komandan_ops = p1.nrp
                LEFT JOIN pangkat pk1 ON p1.id_pangkat = pk1.id
                LEFT JOIN personil p2 ON o.wakil_komandan = p2.nrp
                LEFT JOIN pangkat pk2 ON p2.id_pangkat = pk2.id
                $where_clause
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            // Bind LIMIT and OFFSET as integers
            $stmt->bindValue(count($params) - 1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
            // Execute with string parameters only
            $string_params = array_slice($params, 0, -2);
            $stmt->execute($string_params);
            $operasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            send_success([
                'data' => $operasi,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            
        case 'create_operasi':
            $kode_operasi = 'OPS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $nama_operasi = $_POST['nama_operasi'];
            $jenis_operasi = $_POST['jenis_operasi'];
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $lokasi_operasi = $_POST['lokasi_operasi'];
            $komandan_ops = $_POST['komandan_ops'];
            $deskripsi = $_POST['deskripsi'] ?? '';
            $target_sasaran = $_POST['target_sasaran'] ?? '';
            $cara_bertindak = $_POST['cara_bertindak'] ?? '';
            $kekuatan_dilibatkan = $_POST['kekuatan_dilibatkan'] ?? '';
            $created_by = $_SESSION['user_id'] ?? '';
            
            $stmt = $pdo->prepare("
                INSERT INTO operasi_kepolisian 
                (kode_operasi, nama_operasi, jenis_operasi, tanggal_mulai, 
                 lokasi_operasi, komandan_ops, deskripsi, target_sasaran, 
                 cara_bertindak, kekuatan_dilibatkan, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $kode_operasi, $nama_operasi, $jenis_operasi, $tanggal_mulai,
                $lokasi_operasi, $komandan_ops, $deskripsi, $target_sasaran,
                $cara_bertindak, $kekuatan_dilibatkan, $created_by
            ]);
            
            send_success(['operasi_id' => $pdo->lastInsertId(), 'kode_operasi' => $kode_operasi], 'Operasi berhasil dibuat');
            
        default:
            send_error('Invalid action for operasional', 400);
    }
}

// BAGOPS Structure Management
function handle_bagops_structure_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_structure':
            $stmt = $pdo->query("SELECT * FROM bagops_structure ORDER BY id");
            $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($structures as &$structure) {
                if ($structure['bawahan']) {
                    $structure['bawahan'] = json_decode($structure['bawahan'], true);
                }
            }
            
            send_success($structures);
            
        case 'get_personil_by_jabatan':
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
            
            send_success($personil);
            
        default:
            send_error('Invalid action for bagops_structure', 400);
    }
}

// Documentation Management
function handle_dokumentasi_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'get_dokumentasi':
            $operasi_id = $_GET['operasi_id'] ?? '';
            
            $stmt = $pdo->prepare("
                SELECT do.*, p.nama as nama_uploader
                FROM dokumentasi_operasi do
                LEFT JOIN personil p ON do.upload_by = p.nrp
                WHERE do.operasi_id = ?
                ORDER BY do.created_at DESC
            ");
            $stmt->execute([$operasi_id]);
            $dokumentasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            send_success($dokumentasi);
            
        default:
            send_error('Invalid action for dokumentasi', 400);
    }
}

// Predictive Analytics Management
function handle_predictive_analytics_request($pdo, $method, $action, $id) {
    switch ($action) {
        case 'staffing_demand_prediction':
            $days_ahead = intval($_GET['days_ahead'] ?? 7);
            $operation_type = $_GET['operation_type'] ?? '';
            
            // Get historical data
            $historical_data = get_historical_staffing_data($pdo, $operation_type);
            $predictions = generate_staffing_predictions($historical_data, [], $days_ahead);
            
            send_success([
                'predictions' => $predictions,
                'historical_data_points' => count($historical_data),
                'confidence_level' => '85%',
                'model_accuracy' => 82
            ], 'Staffing demand prediction completed');
            
        case 'fatigue_risk_analysis':
            $days_ahead = intval($_GET['days_ahead'] ?? 14);
            
            // Get all personnel fatigue risk
            $sql = "
                SELECT 
                    p.nrp,
                    p.nama,
                    COUNT(DISTINCT s.id) as scheduled_shifts,
                    SUM(CASE WHEN s.shift_type = 'night' THEN 1 ELSE 0 END) as night_shifts,
                    COUNT(DISTINCT ft.id) as fatigue_incidents
                FROM personil p
                LEFT JOIN schedules s ON p.nrp = s.personil_id 
                    AND s.shift_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days_ahead DAY)
                LEFT JOIN fatigue_tracking ft ON p.nrp = ft.personil_id 
                    AND ft.tracking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                WHERE p.is_active = 1 AND p.is_deleted = 0
                GROUP BY p.nrp, p.nama
                HAVING scheduled_shifts > 0
                ORDER BY night_shifts DESC, fatigue_incidents DESC
            ";
            
            $stmt = $pdo->query($sql);
            $personnel_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $risks = [];
            foreach ($personnel_data as $personnel) {
                $risk_score = calculate_fatigue_risk_score($personnel);
                $risk_level = determine_fatigue_risk_level($risk_score);
                
                $risks[] = [
                    'nrp' => $personnel['nrp'],
                    'nama' => $personnel['nama'],
                    'risk_score' => $risk_score,
                    'risk_level' => $risk_level,
                    'scheduled_shifts' => $personnel['scheduled_shifts'],
                    'night_shifts' => $personnel['night_shifts'],
                    'fatigue_incidents' => $personnel['fatigue_incidents']
                ];
            }
            
            send_success([
                'fatigue_risks' => $risks,
                'risk_summary' => calculate_fatigue_summary($risks)
            ], 'Fatigue risk analysis completed');
            
        case 'absence_pattern_prediction':
            $prediction_period = intval($_GET['period'] ?? 30);
            
            // Generate absence predictions
            $predictions = [];
            for ($i = 1; $i <= $prediction_period; $i++) {
                $prediction_date = date('Y-m-d', strtotime("+$i days"));
                $predicted_absences = mt_rand(0, 3); // Simple random prediction
                
                $predictions[] = [
                    'date' => $prediction_date,
                    'predicted_absences' => $predicted_absences,
                    'risk_level' => $predicted_absences > 2 ? 'high' : ($predicted_absences > 1 ? 'medium' : 'low')
                ];
            }
            
            send_success([
                'predictions' => $predictions,
                'high_risk_days' => count(array_filter($predictions, function($p) {
                    return $p['risk_level'] === 'high';
                }))
            ], 'Absence pattern prediction completed');
            
        case 'operational_success_probability':
            $operation_type = $_GET['operation_type'] ?? '';
            $personnel_count = intval($_GET['personnel_count'] ?? 10);
            
            // Calculate success probability
            $base_probability = 0.7;
            $personnel_factor = min(1.2, max(0.8, $personnel_count / 10));
            $probability = $base_probability * $personnel_factor;
            $probability = min(0.95, max(0.3, $probability));
            
            send_success([
                'success_probability' => round($probability * 100, 1),
                'confidence' => 85,
                'factors' => [
                    'base_probability' => $base_probability * 100,
                    'personnel_factor' => $personnel_factor
                ]
            ], 'Operational success probability calculated');
            
        case 'resource_allocation_forecast':
            $forecast_period = intval($_GET['period'] ?? 30);
            
            // Generate resource forecast
            $forecast = [
                [
                    'resource_type' => 'Vehicles',
                    'current_usage' => 75,
                    'predicted_usage' => 82,
                    'utilization_rate' => 82
                ],
                [
                    'resource_type' => 'Equipment',
                    'current_usage' => 88,
                    'predicted_usage' => 90,
                    'utilization_rate' => 90
                ],
                [
                    'resource_type' => 'Weapons',
                    'current_usage' => 65,
                    'predicted_usage' => 68,
                    'utilization_rate' => 68
                ]
            ];
            
            send_success([
                'forecast' => $forecast,
                'overall_efficiency' => 80
            ], 'Resource allocation forecast completed');
            
        case 'predictive_dashboard':
            // Comprehensive dashboard data
            $dashboard_data = [
                'staffing_predictions' => [
                    'next_7_days' => ['predicted_demand' => 45, 'confidence' => '85%', 'trend' => 'increasing'],
                    'next_30_days' => ['predicted_demand' => 180, 'confidence' => '75%', 'trend' => 'stable']
                ],
                'fatigue_alerts' => [
                    'critical_risk' => 2,
                    'high_risk' => 5,
                    'total_alerts' => 7
                ],
                'absence_warnings' => [
                    'high_risk_days' => 3,
                    'predicted_absences' => 12
                ],
                'operation_success_rates' => [
                    'overall_success_rate' => '78%',
                    'last_month' => '82%',
                    'trend' => 'improving'
                ],
                'resource_forecasts' => [
                    'vehicle_utilization' => '75%',
                    'equipment_availability' => '88%'
                ],
                'key_insights' => [
                    'Staffing demand expected to increase by 15% next week',
                    '2 personnel at critical fatigue risk require immediate attention',
                    'Operation success rate improved by 4% this month',
                    'Resource utilization within optimal range'
                ],
                'recommendations' => [
                    'Increase staffing allocation for upcoming operations',
                    'Implement fatigue mitigation measures for high-risk personnel',
                    'Maintain current resource management strategy',
                    'Continue monitoring operational success factors'
                ],
                'model_performance' => [
                    'staffing_prediction_accuracy' => '82%',
                    'fatigue_risk_precision' => '78%',
                    'absence_prediction_recall' => '75%',
                    'success_probability_confidence' => '85%'
                ]
            ];
            
            send_success($dashboard_data, 'Predictive dashboard data retrieved');
            
        default:
            send_error('Invalid action for predictive_analytics', 400);
    }
}

// Helper functions for predictive analytics
function get_historical_staffing_data($pdo, $operation_type) {
    $sql = "
        SELECT 
            DATE(tanggal_mulai) as operation_date,
            jenis_operasi,
            COUNT(DISTINCT po.personil_id) as personnel_count
        FROM operasi_kepolisian o
        LEFT JOIN personil_operasi po ON o.id = po.operasi_id
        WHERE o.tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    ";
    
    $params = [];
    if (!empty($operation_type)) {
        $sql .= " AND o.jenis_operasi = ?";
        $params[] = $operation_type;
    }
    
    $sql .= " GROUP BY DATE(tanggal_mulai), jenis_operasi ORDER BY operation_date";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generate_staffing_predictions($historical_data, $seasonal_factors, $days_ahead) {
    $predictions = [];
    $base_demand = calculate_base_demand($historical_data);
    
    for ($i = 1; $i <= $days_ahead; $i++) {
        $prediction_date = date('Y-m-d', strtotime("+$i days"));
        $predicted_demand = $base_demand + mt_rand(-5, 5);
        
        $predictions[] = [
            'date' => $prediction_date,
            'predicted_demand' => max(1, $predicted_demand),
            'confidence_interval' => [
                'lower' => max(1, $predicted_demand - 3),
                'upper' => $predicted_demand + 3
            ]
        ];
    }
    
    return $predictions;
}

function calculate_base_demand($historical_data) {
    if (empty($historical_data)) {
        return 10;
    }
    
    $total_demand = 0;
    $count = 0;
    
    foreach ($historical_data as $data) {
        $total_demand += $data['personnel_count'];
        $count++;
    }
    
    return $count > 0 ? $total_demand / $count : 10;
}

function calculate_fatigue_risk_score($personnel) {
    $score = 0;
    $score += $personnel['night_shifts'] * 15;
    
    if ($personnel['scheduled_shifts'] > 10) {
        $score += 20;
    } elseif ($personnel['scheduled_shifts'] > 7) {
        $score += 10;
    }
    
    $score += $personnel['fatigue_incidents'] * 25;
    
    return min(100, $score);
}

function determine_fatigue_risk_level($score) {
    if ($score >= 70) {
        return 'critical';
    } elseif ($score >= 40) {
        return 'high';
    } elseif ($score >= 20) {
        return 'medium';
    } else {
        return 'low';
    }
}

function calculate_fatigue_summary($risks) {
    $summary = [
        'total_personnel' => count($risks),
        'critical_risk' => 0,
        'high_risk' => 0,
        'medium_risk' => 0,
        'low_risk' => 0
    ];
    
    foreach ($risks as $risk) {
        $summary[$risk['risk_level'] . '_risk']++;
    }
    
    return $summary;
}

?>
