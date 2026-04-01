<?php
/**
 * Database Connection Manager - Singleton Pattern
 * Part of MVC Architecture
 */

class Database {
    
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            // Try socket first (XAMPP), then fallback to TCP
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
            try {
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            } catch (PDOException $e) {
                // Fallback to TCP connection
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            }
            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Set timezone
            $this->pdo->exec("SET time_zone = '+07:00'");
            $this->pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute query with parameters
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch all results
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Insert data and return ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete data
     */
    public function delete($table, $where, $params) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Soft delete (update is_deleted flag)
     */
    public function softDelete($table, $where, $params) {
        $sql = "UPDATE $table SET is_deleted = TRUE, updated_at = NOW() WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
