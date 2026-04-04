<?php
declare(strict_types=1);
/**
 * Audit Trail System for SPRIN
 * Logs all data changes for compliance and tracking
 */

class AuditTrail {
    
    private $db;
    private $logTable = 'audit_logs';
    
    // Action types
    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_VIEW = 'VIEW';
    const ACTION_EXPORT = 'EXPORT';
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
        $this->createAuditTable();
    }
    
    /**
     * Create audit logs table if not exists
     */
    private function createAuditTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->logTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                username VARCHAR(100),
                action VARCHAR(50) NOT NULL,
                table_name VARCHAR(100),
                record_id INT,
                old_values JSON,
                new_values JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                session_id VARCHAR(255),
                url VARCHAR(500),
                method VARCHAR(10),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_table_name (table_name),
                INDEX idx_created_at (created_at),
                INDEX idx_record (table_name, record_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        try {
            $this->db->getConnection()->exec($sql);
        } catch (PDOException $e) {
            error_log("Failed to create audit table: " . $e->getMessage());
        }
    }
    
    /**
     * Log an action
     */
    public function log($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $userInfo = $this->getCurrentUserInfo();
        $requestInfo = $this->getRequestInfo();
        
        $data = [
            'user_id' => $userInfo['id'] ?? null,
            'username' => $userInfo['username'] ?? 'anonymous',
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $requestInfo['ip'],
            'user_agent' => $requestInfo['user_agent'],
            'session_id' => session_id() ?: null,
            'url' => $requestInfo['url'],
            'method' => $requestInfo['method']
        ];
        
        try {
            $sql = "
                INSERT INTO {$this->logTable} 
                (user_id, username, action, table_name, record_id, old_values, new_values, 
                 ip_address, user_agent, session_id, url, method, created_at)
                VALUES 
                (:user_id, :username, :action, :table_name, :record_id, :old_values, :new_values,
                 :ip_address, :user_agent, :session_id, :url, :method, NOW())
            ";
            
            $this->db->query($sql, $data);
            return true;
            
        } catch (PDOException $e) {
            error_log("Failed to log audit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log data creation
     */
    public function logCreate($tableName, $recordId, $newValues) {
        return $this->log(self::ACTION_CREATE, $tableName, $recordId, null, $newValues);
    }
    
    /**
     * Log data update
     */
    public function logUpdate($tableName, $recordId, $oldValues, $newValues) {
        // Only log changed values
        $changes = $this->getChanges($oldValues, $newValues);
        if (empty($changes)) {
            return false;
        }
        
        return $this->log(self::ACTION_UPDATE, $tableName, $recordId, $changes['old'], $changes['new']);
    }
    
    /**
     * Log data deletion
     */
    public function logDelete($tableName, $recordId, $oldValues) {
        return $this->log(self::ACTION_DELETE, $tableName, $recordId, $oldValues, null);
    }
    
    /**
     * Log view action
     */
    public function logView($tableName, $recordId = null) {
        return $this->log(self::ACTION_VIEW, $tableName, $recordId);
    }
    
    /**
     * Log export action
     */
    public function logExport($tableName, $format, $recordCount) {
        return $this->log(self::ACTION_EXPORT, $tableName, null, null, [
            'format' => $format,
            'record_count' => $recordCount
        ]);
    }
    
    /**
     * Log login
     */
    public function logLogin($username, $success = true) {
        $action = $success ? self::ACTION_LOGIN : 'LOGIN_FAILED';
        return $this->log($action, 'users', null, null, ['username' => $username, 'success' => $success]);
    }
    
    /**
     * Log logout
     */
    public function logLogout($username) {
        return $this->log(self::ACTION_LOGOUT, 'users', null, null, ['username' => $username]);
    }
    
    /**
     * Get changes between old and new values
     */
    private function getChanges($old, $new) {
        $changes = ['old' => [], 'new' => []];
        
        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $changes['old'][$key] = $old[$key] ?? null;
                $changes['new'][$key] = $value;
            }
        }
        
        return $changes;
    }
    
    /**
     * Get current user info from session
     */
    private function getCurrentUserInfo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? 'anonymous'
        ];
    }
    
    /**
     * Get request information
     */
    private function getRequestInfo() {
        return [
            'ip' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ];
    }
    
    /**
     * Get audit logs with filters
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $whereConditions[] = "action = :action";
            $params['action'] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $whereConditions[] = "table_name = :table_name";
            $params['table_name'] = $filters['table_name'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->logTable} {$whereClause}";
        $totalResult = $this->db->fetchOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Get logs
        $sql = "
            SELECT * FROM {$this->logTable}
            {$whereClause}
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $logs = $this->db->fetchAll($sql, $params);
        
        // Parse JSON values
        foreach ($logs as &$log) {
            $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
            $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
        }
        
        return [
            'data' => $logs,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * Get activity statistics
     */
    public function getActivityStats($days = 30) {
        $sql = "
            SELECT 
                action,
                COUNT(*) as count
            FROM {$this->logTable}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY action
            ORDER BY count DESC
        ";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }
    
    /**
     * Get user activity
     */
    public function getUserActivity($userId, $limit = 50) {
        return $this->getLogs(['user_id' => $userId], $limit);
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 90) {
        $sql = "DELETE FROM {$this->logTable} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        try {
            $stmt = $this->db->query($sql, ['days' => $days]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Failed to clean audit logs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get audit trail for a specific record
     */
    public function getRecordHistory($tableName, $recordId) {
        $sql = "
            SELECT * FROM {$this->logTable}
            WHERE table_name = :table_name AND record_id = :record_id
            ORDER BY created_at DESC
        ";
        
        $logs = $this->db->fetchAll($sql, [
            'table_name' => $tableName,
            'record_id' => $recordId
        ]);
        
        foreach ($logs as &$log) {
            $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
            $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
        }
        
        return $logs;
    }
}

?>