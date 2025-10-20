<?php

namespace App\Models;

use Core\Database;

/**
 * Base Model Class
 * All models should extend this class
 */
abstract class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $timestamps = true;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Find all records
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->select($sql);
    }
    
    /**
     * Find records with where condition
     */
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->select($sql, [$value]);
    }
    
    /**
     * Find first record with where condition
     */
    public function whereFirst($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ? LIMIT 1";
        return $this->db->selectOne($sql, [$value]);
    }
    
    /**
     * Create new record
     */
    public function create($data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->db->insert($sql, $data);
    }
    
    /**
     * Update record by ID
     */
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $data[$this->primaryKey] = $id;
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        return $this->db->update($sql, $data);
    }
    
    /**
     * Delete record by ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    /**
     * Count records
     */
    public function count($where = null, $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = 15, $where = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->select($sql, $params);
        $total = $this->count($where, $params);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Filter data based on fillable fields
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Execute raw SQL query
     */
    public function raw($sql, $params = [])
    {
        return $this->db->select($sql, $params);
    }
    
    /**
     * Has One relationship
     */
    protected function hasOne($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        
        $relatedModel = new $related();
        $sql = "SELECT * FROM {$relatedModel->table} WHERE {$foreignKey} = ?";
        
        return $this->db->selectOne($sql, [$this->{$localKey}]);
    }
    
    /**
     * Has Many relationship
     */
    protected function hasMany($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        
        $relatedModel = new $related();
        $sql = "SELECT * FROM {$relatedModel->table} WHERE {$foreignKey} = ?";
        
        return $this->db->select($sql, [$this->{$localKey}]);
    }
    
    /**
     * Belongs To relationship
     */
    protected function belongsTo($related, $foreignKey = null, $ownerKey = 'id')
    {
        $relatedModel = new $related();
        $foreignKey = $foreignKey ?: $relatedModel->getForeignKey();
        
        $sql = "SELECT * FROM {$relatedModel->table} WHERE {$ownerKey} = ?";
        
        return $this->db->selectOne($sql, [$this->{$foreignKey}]);
    }
    
    /**
     * Belongs To Many relationship (Many-to-Many)
     */
    protected function belongsToMany($related, $pivotTable = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = 'id', $relatedKey = 'id')
    {
        $relatedModel = new $related();
        
        $pivotTable = $pivotTable ?: $this->getPivotTableName($relatedModel);
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $relatedModel->getForeignKey();
        
        $sql = "SELECT r.* FROM {$relatedModel->table} r 
                INNER JOIN {$pivotTable} p ON r.{$relatedKey} = p.{$relatedPivotKey} 
                WHERE p.{$foreignPivotKey} = ?";
        
        return $this->db->select($sql, [$this->{$parentKey}]);
    }
    
    /**
     * Get foreign key for this model
     */
    protected function getForeignKey()
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower($className) . '_id';
    }
    
    /**
     * Get pivot table name for many-to-many relationship
     */
    protected function getPivotTableName($relatedModel)
    {
        $models = [
            strtolower((new \ReflectionClass($this))->getShortName()),
            strtolower((new \ReflectionClass($relatedModel))->getShortName())
        ];
        
        sort($models);
        return implode('_', $models);
    }
    
    /**
     * Query Builder instance
     */
    public function query()
    {
        return new \Core\QueryBuilder($this->table);
    }
    
    /**
     * Create new query builder instance
     */
    public static function query_static()
    {
        $instance = new static();
        return new \Core\QueryBuilder($instance->table);
    }
    
    /**
     * Get all with query builder
     */
    public static function all_static()
    {
        return static::query_static()->get();
    }
    
    /**
     * Find with query builder
     */
    public static function find_static($id)
    {
        return static::query_static()->find($id);
    }
    
    /**
     * Where with query builder
     */
    public static function where_static($column, $operator = null, $value = null)
    {
        return static::query_static()->where($column, $operator, $value);
    }
}
