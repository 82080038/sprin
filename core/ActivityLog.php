<?php
/**
 * Activity Log Middleware — Audit Trail
 * Logs all CREATE/UPDATE/DELETE operations to user_activity_log table
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SessionManager.php';

class ActivityLog {
    
    /**
     * Log an activity
     * @param string $action CREATE, UPDATE, DELETE, LOGIN, LOGOUT, VIEW, etc.
     * @param string $module Module name (e.g., 'tim_piket', 'operasi', 'lhpt')
     * @param string $description Human-readable description
     * @param int|null $recordId ID of affected record (if applicable)
     * @param array $additionalData Additional data to store as JSON
     */
    public static function log($action, $module, $description, $recordId = null, $additionalData = []) {
        try {
            SessionManager::start();
            
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? 'system';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO user_activity_log 
                (user_id, username, action, module, record_id, description, ip_address, user_agent, additional_data)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $username,
                strtoupper($action),
                $module,
                $recordId,
                $description,
                $ip,
                $userAgent,
                json_encode($additionalData)
            ]);
            
        } catch (Exception $e) {
            // Silent fail - don't break the application if logging fails
            error_log('ActivityLog error: ' . $e->getMessage());
        }
    }
    
    /**
     * Log CREATE operation
     */
    public static function logCreate($module, $recordId, $description = 'Created record') {
        self::log('CREATE', $module, $description, $recordId);
    }
    
    /**
     * Log UPDATE operation
     */
    public static function logUpdate($module, $recordId, $description = 'Updated record', $oldData = null, $newData = null) {
        $additionalData = [];
        if ($oldData !== null) $additionalData['old_data'] = $oldData;
        if ($newData !== null) $additionalData['new_data'] = $newData;
        self::log('UPDATE', $module, $description, $recordId, $additionalData);
    }
    
    /**
     * Log DELETE operation
     */
    public static function logDelete($module, $recordId, $description = 'Deleted record', $deletedData = null) {
        $additionalData = [];
        if ($deletedData !== null) $additionalData['deleted_data'] = $deletedData;
        self::log('DELETE', $module, $description, $recordId, $additionalData);
    }
    
    /**
     * Log LOGIN operation
     */
    public static function logLogin($userId, $username) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO user_activity_log 
                (user_id, username, action, module, description, ip_address, user_agent)
                VALUES (?, ?, 'LOGIN', 'auth', ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $username,
                'User logged in',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
        } catch (Exception $e) {
            error_log('ActivityLog error: ' . $e->getMessage());
        }
    }
    
    /**
     * Log LOGOUT operation
     */
    public static function logLogout() {
        try {
            SessionManager::start();
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? 'unknown';
            
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO user_activity_log 
                (user_id, username, action, module, description, ip_address, user_agent)
                VALUES (?, ?, 'LOGOUT', 'auth', ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $username,
                'User logged out',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
        } catch (Exception $e) {
            error_log('ActivityLog error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get recent activity for a user or all users
     * @param int|null $userId Filter by user ID (null for all)
     * @param int $limit Number of records to return
     * @return array
     */
    public static function getRecentActivity($userId = null, $limit = 50) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            if ($userId) {
                $stmt = $pdo->prepare("
                    SELECT * FROM user_activity_log 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?
                ");
                $stmt->execute([$userId, $limit]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT * FROM user_activity_log 
                    ORDER BY created_at DESC 
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('ActivityLog error: ' . $e->getMessage());
            return [];
        }
    }
}
