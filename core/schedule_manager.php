<?php
declare(strict_types=1);
require_once __DIR__ . '/calendar_config.php';
require_once __DIR__ . '/config.php'; // Add this for API_BASE_URL

class ScheduleManager {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function createSchedule($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO schedules 
                (personil_id, personil_name, bagian, shift_type, shift_date, start_time, end_time, location, description, google_event_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['personil_id'],
                $data['personil_name'],
                $data['bagian'],
                $data['shift_type'],
                $data['shift_date'],
                $data['start_time'],
                $data['end_time'],
                $data['location'] ?? '',
                $data['description'] ?? '',
                $data['google_event_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'schedule_id' => $this->pdo->lastInsertId()
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function updateSchedule($scheduleId, $data) {
        try {
            $fields = [];
            $values = [];
            
            foreach (['personil_id', 'personil_name', 'bagian', 'shift_type', 'shift_date', 'start_time', 'end_time', 'location', 'description', 'google_event_id', 'status'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return ['success' => false, 'error' => 'No fields to update'];
            }
            
            $values[] = $scheduleId;
            
            $stmt = $this->pdo->prepare("
                UPDATE schedules 
                SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute($values);
            
            return ['success' => true];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function deleteSchedule($scheduleId) {
        try {
            // Get Google event ID first
            $stmt = $this->pdo->prepare("SELECT google_event_id FROM schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete from database
            $stmt = $this->pdo->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            
            return [
                'success' => true,
                'google_event_id' => $schedule['google_event_id'] ?? null
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getSchedules($filters = []) {
        try {
            $sql = "SELECT * FROM schedules WHERE 1=1";
            $params = [];
            
            if (!empty($filters['personil_id'])) {
                $sql .= " AND personil_id = ?";
                $params[] = $filters['personil_id'];
            }
            
            if (!empty($filters['bagian'])) {
                $sql .= " AND bagian = ?";
                $params[] = $filters['bagian'];
            }
            
            if (!empty($filters['shift_type'])) {
                $sql .= " AND shift_type = ?";
                $params[] = $filters['shift_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND shift_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND shift_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY shift_date, start_time";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'schedules' => $schedules
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function createOperation($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO operations 
                (operation_name, operation_date, start_time, end_time, location, description, required_personnel, google_event_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['operation_name'],
                $data['operation_date'],
                $data['start_time'],
                $data['end_time'],
                $data['location'],
                $data['description'] ?? '',
                $data['required_personnel'] ?? 0,
                $data['google_event_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'operation_id' => $this->pdo->lastInsertId()
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function assignPersonnelToOperation($operationId, $assignments) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO assignments (operation_id, personil_id, personil_name, role)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($assignments as $assignment) {
                $stmt->execute([
                    $operationId,
                    $assignment['personil_id'],
                    $assignment['personil_name'],
                    $assignment['role'] ?? ''
                ]);
            }
            
            return ['success' => true];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getOperations($filters = []) {
        try {
            $sql = "SELECT o.*, COUNT(a.id) as assigned_count 
                    FROM operations o 
                    LEFT JOIN assignments a ON o.id = a.operation_id 
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND o.operation_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND o.operation_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND o.status = ?";
                $params[] = $filters['status'];
            }
            
            $sql .= " GROUP BY o.id ORDER BY o.operation_date, o.start_time";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get assignments for each operation
            foreach ($operations as &$operation) {
                $stmt = $this->pdo->prepare("SELECT * FROM assignments WHERE operation_id = ?");
                $stmt->execute([$operation['id']]);
                $operation['assignments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'success' => true,
                'operations' => $operations
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getPersonilFromAPI() {
        try {
            // Use API to get ALL personil data from database
            $api_url = API_BASE_URL . '/personil_simple.php?limit=1000';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                throw new Exception("API request failed with HTTP code: $http_code");
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !$data['success']) {
                throw new Exception("API request failed: " . ($data['error']['message'] ?? 'Unknown error'));
            }
            
            $personilList = [];
            $personilData = $data['data']['personil'];
            
            // Process personil data
            foreach ($personilData as $personil) {
                $personilList[] = [
                    'id' => $personil['nrp'],
                    'name' => $personil['nama'],
                    'pangkat' => $personil['pangkat_singkatan'] ?? $personil['nama_pangkat'],
                    'jabatan' => $personil['nama_jabatan'],
                    'bagian' => $personil['nama_bagian'] ?? 'TANPA BAGIAN',
                    'status' => $personil['status_ket'] ?? 'aktif',
                    'status_kepegawaian' => $personil['status_kepegawaian']
                ];
            }
            
            return [
                'success' => true,
                'personil' => $personilList,
                'total' => count($personilList),
                'statistics' => $data['data']['statistics']
            ];
            
        } catch (Exception $e) {
            // Fallback to database if API fails
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET,
                    DB_USER,
                    DB_PASS
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->query("
                    SELECT p.id, p.nama, p.nrp, b.nama_bagian, pg.nama_pangkat 
                    FROM personil p
                    LEFT JOIN bagian b ON p.bagian_id = b.id
                    LEFT JOIN pangkat pg ON p.pangkat_id = pg.id
                    WHERE p.is_deleted = 0 AND p.is_active = 1
                    ORDER BY b.nama_bagian, p.nama
                ");
                
                $personilList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return [
                    'success' => true,
                    'personil' => $personilList,
                    'total' => count($personilList)
                ];
            } catch (Exception $dbEx) {
                return [
                    'success' => false,
                    'error' => 'Failed to load personil data: ' . $e->getMessage()
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to load personil data: ' . $e->getMessage()
            ];
        }
    }
    
    public function getPersonilFromJSON() {
        throw new Exception("JSON method deprecated. Use database queries instead.");
    }
    
    public function getBagianList() {
        try {
            // Use API to get bagian data from database
            $api_url = API_BASE_URL . '/simple.php?limit=1000';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                throw new Exception("API request failed with HTTP code: $http_code");
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !$data['success']) {
                throw new Exception("API request failed: " . ($data['error']['message'] ?? 'Unknown error'));
            }
            
            $bagianList = [];
            $bagianData = $data['data'];
            
            foreach ($bagianData as $bagian) {
                $bagianList[] = [
                    'id' => $bagian['id'] ?? null,
                    'name' => $bagian['nama_bagian'] ?? 'Unknown Bagian',
                    'type' => $bagian['type'] ?? 'BAG/SAT/SIE',
                    'personil_count' => $bagian['personil_count'] ?? 0
                ];
            }
            
            return [
                'success' => true,
                'bagian' => $bagianList,
                'total' => count($bagianList)
            ];
            
        } catch (Exception $e) {
            // Fallback to database if API fails
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET,
                    DB_USER,
                    DB_PASS
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->query("
                    SELECT DISTINCT b.id, b.nama_bagian, b.urutan
                    FROM bagian b
                    ORDER BY b.urutan, b.nama_bagian
                ");
                
                $bagianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return [
                    'success' => true,
                    'bagian' => $bagianList,
                    'total' => count($bagianList)
                ];
            } catch (Exception $dbEx) {
                return [
                    'success' => false,
                    'error' => 'Failed to load bagian data: ' . $e->getMessage()
                ];
            }
        }
    }
    
    public function generateCalendarEvents($startDate, $endDate) {
        $schedules = $this->getSchedules([
            'date_from' => $startDate,
            'date_to' => $endDate
        ]);
        
        if (!$schedules['success']) {
            return $schedules;
        }
        
        $events = [];
        foreach ($schedules['schedules'] as $schedule) {
            $startDateTime = ($schedule['shift_date'] ?? '') . ' ' . ($schedule['start_time'] ?? '');
            $endDateTime = ($schedule['shift_date'] ?? '') . ' ' . ($schedule['end_time'] ?? '');
            
            // Handle overnight shifts
            if (($schedule['end_time'] ?? '') < ($schedule['start_time'] ?? '')) {
                $endDateTime = date('Y-m-d H:i:s', strtotime(($schedule['shift_date'] ?? '') . ' ' . ($schedule['end_time'] ?? '') . ' +1 day'));
            }
            
            $events[] = [
                'title' => ($schedule['personil_name'] ?? 'Unknown') . ' - ' . ($schedule['shift_type'] ?? 'Unknown'),
                'start' => $startDateTime,
                'end' => $endDateTime,
                'color' => EVENT_COLORS[$schedule['shift_type'] ?? ''] ?? '#4285F4',
                'extendedProps' => [
                    'personil_id' => $schedule['personil_id'] ?? null,
                    'bagian' => $schedule['bagian'] ?? 'Unknown',
                    'location' => $schedule['location'] ?? '',
                    'description' => $schedule['description'] ?? '',
                    'schedule_id' => $schedule['id'] ?? null,
                    'google_event_id' => $schedule['google_event_id'] ?? null
                ]
            ];
        }
        
        return [
            'success' => true,
            'events' => $events
        ];
    }
}
?>
