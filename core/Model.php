<?php
declare(strict_types=1);
/**
 * Base Model Class - Part of MVC Architecture
 * All models should extend this class
 */

abstract class Model {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['password', 'is_deleted'];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND is_deleted = FALSE LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Get all records
     */
    public function all($limit = 1000, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = FALSE LIMIT :limit OFFSET :offset";
        return $this->db->fetchAll($sql, ['limit' => $limit, 'offset' => $offset]);
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Add timestamps
        $filteredData['created_at'] = date('Y-m-d H:i:s');
        $filteredData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $filteredData);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        $filteredData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update(
            $this->table, 
            $filteredData, 
            "{$this->primaryKey} = :id AND is_deleted = FALSE", 
            ['id' => $id]
        );
    }
    
    /**
     * Delete record (soft delete)
     */
    public function delete($id) {
        return $this->db->softDelete(
            $this->table, 
            "{$this->primaryKey} = :id", 
            ['id' => $id]
        );
    }
    
    /**
     * Hard delete record
     */
    public function forceDelete($id) {
        return $this->db->delete(
            $this->table, 
            "{$this->primaryKey} = :id", 
            ['id' => $id]
        );
    }
    
    /**
     * Find by specific column
     */
    public function findBy($column, $value, $limit = 100) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value AND is_deleted = FALSE LIMIT :limit";
        return $this->db->fetchAll($sql, ['value' => $value, 'limit' => $limit]);
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        $where = "is_deleted = FALSE";
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where .= " AND {$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Paginate results
     */
    public function paginate($page = 1, $perPage = 20, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        $where = "is_deleted = FALSE";
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where .= " AND {$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        $total = $this->count($conditions);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Execute custom query
     */
    protected function query($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback() {
        return $this->db->rollback();
    }
}

?>