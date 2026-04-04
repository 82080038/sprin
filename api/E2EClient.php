<?php
declare(strict_types=1);
/**
 * Endpoint-to-Endpoint (E2E) Communication Layer
 * Standardized internal API communication between endpoints
 */

class E2EClient {
    private $baseURL;
    private $timeout;
    
    public function __construct() {
        $this->baseURL = BASE_URL;
        $this->timeout = 30;
    }
    
    /**
     * Make internal API call
     */
    public function request($endpoint, $options = []) {
        $method = $options['method'] ?? 'GET';
        $data = $options['data'] ?? null;
        $headers = $options['headers'] ?? [];
        $timeout = $options['timeout'] ?? $this->timeout;
        
        $url = $this->baseURL . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
                'X-Internal-API: 1',
                'X-Request-ID: ' . uniqid('e2e_', true)
            ], $headers),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($data && $method !== 'GET') {
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("E2E Request Error: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("E2E HTTP Error: $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * GET request
     */
    public function get($endpoint, $params = []) {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        return $this->request($endpoint, ['method' => 'GET']);
    }
    
    /**
     * POST request
     */
    public function post($endpoint, $data = []) {
        return $this->request($endpoint, ['method' => 'POST', 'data' => $data]);
    }
    
    /**
     * PUT request
     */
    public function put($endpoint, $data = []) {
        return $this->request($endpoint, ['method' => 'PUT', 'data' => $data]);
    }
    
    /**
     * DELETE request
     */
    public function delete($endpoint, $data = []) {
        return $this->request($endpoint, ['method' => 'DELETE', 'data' => $data]);
    }
}

/**
 * E2E Service Registry
 * Manages internal service communication
 */
class E2EServiceRegistry {
    private static $services = [];
    private static $client = null;
    
    public static function getClient() {
        if (self::$client === null) {
            self::$client = new E2EClient();
        }
        return self::$client;
    }
    
    /**
     * Register a service
     */
    public static function register($name, $endpoint, $options = []) {
        self::$services[$name] = [
            'endpoint' => $endpoint,
            'options' => $options
        ];
    }
    
    /**
     * Call a registered service
     */
    public static function call($service, $action, $data = []) {
        if (!isset(self::$services[$service])) {
            throw new Exception("Service '$service' not registered");
        }
        
        $serviceConfig = self::$services[$service];
        $endpoint = $serviceConfig['endpoint'];
        $options = $serviceConfig['options'];
        
        // Add action to data
        if (is_array($data)) {
            $data['action'] = $action;
        } else {
            $data = ['action' => $action, 'data' => $data];
        }
        
        $method = $options['method'] ?? 'POST';
        
        return self::getClient()->request($endpoint, [
            'method' => $method,
            'data' => $data
        ]);
    }
    
    /**
     * Get personil data from personil service
     */
    public static function getPersonil($filters = []) {
        return self::getClient()->get('/api/personil_list.php', $filters);
    }
    
    /**
     * Get unsur data from unsur service
     */
    public static function getUnsur($filters = []) {
        return self::getClient()->get('/api/unsur_crud.php', array_merge(['action' => 'list'], $filters));
    }
    
    /**
     * Get bagian data from bagian service
     */
    public static function getBagian($filters = []) {
        return self::getClient()->get('/api/bagian_crud.php', array_merge(['action' => 'list'], $filters));
    }
    
    /**
     * Get jabatan data from jabatan service
     */
    public static function getJabatan($filters = []) {
        return self::getClient()->get('/api/jabatan_crud.php', array_merge(['action' => 'list'], $filters));
    }
    
    /**
     * Get statistics from multiple services
     */
    public static function getStatistics() {
        $client = self::getClient();
        
        $promises = [
            'personil' => $client->get('/api/personil_list.php', ['per_page' => 1]),
            'unsur' => $client->get('/api/unsur_stats.php'),
            'bagian' => $client->get('/api/bagian_crud.php', ['action' => 'list']),
            'jabatan' => $client->get('/api/jabatan_crud.php', ['action' => 'list'])
        ];
        
        return $promises;
    }
    
    /**
     * Validate data consistency across services
     */
    public static function validateDataConsistency() {
        $client = self::getClient();
        
        try {
            // Get data from all services
            $personil = $client->get('/api/personil_list.php', ['per_page' => 1000]);
            $unsur = $client->get('/api/unsur_crud.php', ['action' => 'list']);
            $bagian = $client->get('/api/bagian_crud.php', ['action' => 'list']);
            $jabatan = $client->get('/api/jabatan_crud.php', ['action' => 'list']);
            
            $issues = [];
            
            // Check for orphaned records
            if ($personil['success'] && $unsur['success'] && $bagian['success'] && $jabatan['success']) {
                $unsurIds = array_column($unsur['data'], 'id');
                $bagianIds = array_column($bagian['data'], 'id');
                $jabatanIds = array_column($jabatan['data'], 'id');
                
                foreach ($personil['data'] as $person) {
                    // Check unsur reference
                    if ($person['unsur_id'] && !in_array($person['unsur_id'], $unsurIds)) {
                        $issues[] = [
                            'type' => 'orphaned_unsur',
                            'personil_id' => $person['id'],
                            'unsur_id' => $person['unsur_id'],
                            'message' => "Personil {$person['nama']} references non-existent unsur {$person['unsur_id']}"
                        ];
                    }
                    
                    // Check bagian reference
                    if ($person['bagian_id'] && !in_array($person['bagian_id'], $bagianIds)) {
                        $issues[] = [
                            'type' => 'orphaned_bagian',
                            'personil_id' => $person['id'],
                            'bagian_id' => $person['bagian_id'],
                            'message' => "Personil {$person['nama']} references non-existent bagian {$person['bagian_id']}"
                        ];
                    }
                    
                    // Check jabatan reference
                    if ($person['jabatan_id'] && !in_array($person['jabatan_id'], $jabatanIds)) {
                        $issues[] = [
                            'type' => 'orphaned_jabatan',
                            'personil_id' => $person['id'],
                            'jabatan_id' => $person['jabatan_id'],
                            'message' => "Personil {$person['nama']} references non-existent jabatan {$person['jabatan_id']}"
                        ];
                    }
                }
            }
            
            return [
                'success' => true,
                'issues' => $issues,
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * Sync data between services
     */
    public static function syncData($source, $target, $data) {
        $client = self::getClient();
        
        try {
            // Get current data from target
            $currentData = $client->get($target, ['action' => 'list']);
            
            if (!$currentData['success']) {
                throw new Exception("Failed to get current data from target: {$target}");
            }
            
            $synced = [];
            $errors = [];
            
            foreach ($data as $item) {
                try {
                    // Check if item exists in target
                    $exists = false;
                    foreach ($currentData['data'] as $existing) {
                        if ($existing['id'] == $item['id']) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if ($exists) {
                        // Update existing item
                        $result = $client->put($target, array_merge($item, ['action' => 'update']));
                    } else {
                        // Create new item
                        $result = $client->post($target, array_merge($item, ['action' => 'create']));
                    }
                    
                    if ($result['success']) {
                        $synced[] = $item['id'];
                    } else {
                        $errors[] = [
                            'id' => $item['id'],
                            'error' => $result['message']
                        ];
                    }
                    
                } catch (Exception $e) {
                    $errors[] = [
                        'id' => $item['id'],
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors,
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
}

// Auto-register common services
E2EServiceRegistry::register('personil', '/api/personil_list.php', ['method' => 'GET']);
E2EServiceRegistry::register('unsur', '/api/unsur_crud.php', ['method' => 'POST']);
E2EServiceRegistry::register('bagian', '/api/bagian_crud.php', ['method' => 'POST']);
E2EServiceRegistry::register('jabatan', '/api/jabatan_crud.php', ['method' => 'POST']);
E2EServiceRegistry::register('user', '/api/user_management.php', ['method' => 'POST']);
E2EServiceRegistry::register('backup', '/api/backup_api.php', ['method' => 'POST']);

// Helper functions for common E2E operations
function validateE2EData() {
    return E2EServiceRegistry::validateDataConsistency();
}

function getE2EStatistics() {
    return E2EServiceRegistry::getStatistics();
}

function syncE2EData($source, $target, $data) {
    return E2EServiceRegistry::syncData($source, $target, $data);
}
?>
