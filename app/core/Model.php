<?php
/**
 * Base Model Class
 * All models extend from this class
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $result = $this->db->fetch($sql, ['id' => $id]);
        
        if ($result) {
            return $this->hideFields($result);
        }
        
        return null;
    }
    
    /**
     * Find all records
     */
    public function all($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $results = $this->db->fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }
    
    /**
     * Find records with conditions
     */
    public function where($conditions, $params = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $results = $this->db->fetchAll($sql, $params);
        
        return array_map([$this, 'hideFields'], $results);
    }
    
    /**
     * Find first record with conditions
     */
    public function first($conditions = '1=1', $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT 1";
        $result = $this->db->fetch($sql, $params);
        
        if ($result) {
            return $this->hideFields($result);
        }
        
        return null;
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $id = $this->db->insert($this->table, $data);
        
        return $this->find($id);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $this->db->update($this->table, $data, "{$this->primaryKey} = :id", ['id' => $id]);
        
        return $this->find($id);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        return $this->db->delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    /**
     * Count records
     */
    public function count($conditions = '1=1', $params = []) {
        return $this->db->count($this->table, $conditions, $params);
    }
    
    /**
     * Check if record exists
     */
    public function exists($conditions, $params = []) {
        return $this->db->exists($this->table, $conditions, $params);
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = null, $conditions = '1=1', $params = [], $orderBy = null) {
        $perPage = $perPage ?? ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $this->count($conditions, $params);
        
        // Get records for current page
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $params);
        $data = array_map([$this, 'hideFields'], $results);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Search records
     */
    public function search($query, $fields, $page = 1, $perPage = null) {
        $perPage = $perPage ?? ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Build search conditions
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE :query_{$field}";
            $params["query_{$field}"] = "%{$query}%";
        }
        
        $whereClause = implode(' OR ', $conditions);
        
        // Get total count
        $total = $this->count($whereClause, $params);
        
        // Get records for current page
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} LIMIT {$perPage} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $params);
        $data = array_map([$this, 'hideFields'], $results);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'query' => $query
        ];
    }
    
    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide specified fields from result
     */
    protected function hideFields($data) {
        if (empty($this->hidden)) {
            return $data;
        }
        
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
    
    /**
     * Execute raw SQL query
     */
    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
    
    /**
     * Execute raw SQL and fetch results
     */
    public function fetchRaw($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->db->rollback();
    }
}
