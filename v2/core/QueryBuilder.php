<?php

namespace Core;

/**
 * Advanced Query Builder
 */
class QueryBuilder
{
    private $db;
    private $table;
    private $select = ['*'];
    private $joins = [];
    private $wheres = [];
    private $orderBy = [];
    private $groupBy = [];
    private $having = [];
    private $limit = null;
    private $offset = null;
    private $bindings = [];
    
    public function __construct($table = null)
    {
        $this->db = Database::getInstance();
        $this->table = $table;
    }
    
    /**
     * Set table
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Select columns
     */
    public function select($columns = ['*'])
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    /**
     * Add WHERE clause
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, '=', $val, $boolean);
            }
            return $this;
        }
        
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * Add OR WHERE clause
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }
    
    /**
     * WHERE IN clause
     */
    public function whereIn($column, $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        foreach ($values as $value) {
            $this->bindings[] = $value;
        }
        
        return $this;
    }
    
    /**
     * WHERE NOT IN clause
     */
    public function whereNotIn($column, $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'not_in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        foreach ($values as $value) {
            $this->bindings[] = $value;
        }
        
        return $this;
    }
    
    /**
     * WHERE NULL clause
     */
    public function whereNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * WHERE NOT NULL clause
     */
    public function whereNotNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * WHERE BETWEEN clause
     */
    public function whereBetween($column, $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->bindings[] = $values[0];
        $this->bindings[] = $values[1];
        
        return $this;
    }
    
    /**
     * WHERE LIKE clause
     */
    public function whereLike($column, $value, $boolean = 'AND')
    {
        return $this->where($column, 'LIKE', $value, $boolean);
    }
    
    /**
     * JOIN clause
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if ($operator === null) {
            $operator = '=';
        }
        
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }
        
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    /**
     * LEFT JOIN clause
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }
    
    /**
     * RIGHT JOIN clause
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }
    
    /**
     * ORDER BY clause
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }
    
    /**
     * GROUP BY clause
     */
    public function groupBy($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->groupBy = array_merge($this->groupBy, $columns);
        
        return $this;
    }
    
    /**
     * HAVING clause
     */
    public function having($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->having[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * LIMIT clause
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * OFFSET clause
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Take (alias for limit)
     */
    public function take($limit)
    {
        return $this->limit($limit);
    }
    
    /**
     * Skip (alias for offset)
     */
    public function skip($offset)
    {
        return $this->offset($offset);
    }
    
    /**
     * Build SELECT query
     */
    public function toSql()
    {
        $sql = 'SELECT ' . implode(', ', $this->select);
        $sql .= ' FROM ' . $this->table;
        
        // Add JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Add WHERE clauses
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        // Add GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        // Add HAVING
        if (!empty($this->having)) {
            $sql .= ' HAVING ' . $this->buildHaving();
        }
        
        // Add ORDER BY
        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }
        
        // Add LIMIT and OFFSET
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        
        return $sql;
    }
    
    /**
     * Build WHERE clauses
     */
    private function buildWheres()
    {
        $wheres = [];
        
        foreach ($this->wheres as $index => $where) {
            $boolean = $index === 0 ? '' : " {$where['boolean']} ";
            
            switch ($where['type']) {
                case 'basic':
                    $wheres[] = $boolean . "{$where['column']} {$where['operator']} ?";
                    break;
                    
                case 'in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . "{$where['column']} IN ({$placeholders})";
                    break;
                    
                case 'not_in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . "{$where['column']} NOT IN ({$placeholders})";
                    break;
                    
                case 'null':
                    $wheres[] = $boolean . "{$where['column']} IS NULL";
                    break;
                    
                case 'not_null':
                    $wheres[] = $boolean . "{$where['column']} IS NOT NULL";
                    break;
                    
                case 'between':
                    $wheres[] = $boolean . "{$where['column']} BETWEEN ? AND ?";
                    break;
            }
        }
        
        return implode('', $wheres);
    }
    
    /**
     * Build HAVING clauses
     */
    private function buildHaving()
    {
        $havings = [];
        
        foreach ($this->having as $index => $having) {
            $boolean = $index === 0 ? '' : ' AND ';
            $havings[] = $boolean . "{$having['column']} {$having['operator']} ?";
        }
        
        return implode('', $havings);
    }
    
    /**
     * Execute query and get results
     */
    public function get()
    {
        $sql = $this->toSql();
        return $this->db->select($sql, $this->bindings);
    }
    
    /**
     * Get first result
     */
    public function first()
    {
        $this->limit(1);
        $sql = $this->toSql();
        return $this->db->selectOne($sql, $this->bindings);
    }
    
    /**
     * Find by ID
     */
    public function find($id)
    {
        return $this->where('id', $id)->first();
    }
    
    /**
     * Count results
     */
    public function count()
    {
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as count'];
        
        $sql = $this->toSql();
        $result = $this->db->selectOne($sql, $this->bindings);
        
        $this->select = $originalSelect;
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Insert data
     */
    public function insert($data)
    {
        if (empty($data)) {
            return false;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->db->insert($sql, $data);
    }
    
    /**
     * Update data
     */
    public function update($data)
    {
        if (empty($data) || empty($this->wheres)) {
            return false;
        }
        
        $setParts = [];
        $bindings = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        $whereClause = $this->buildWheres();
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$whereClause}";
        
        $allBindings = array_merge($bindings, $this->bindings);
        
        return $this->db->update($sql, $allBindings);
    }
    
    /**
     * Delete data
     */
    public function delete()
    {
        if (empty($this->wheres)) {
            throw new \Exception("Cannot delete without WHERE clause");
        }
        
        $whereClause = $this->buildWheres();
        $sql = "DELETE FROM {$this->table} WHERE {$whereClause}";
        
        return $this->db->delete($sql, $this->bindings);
    }
    
    /**
     * Paginate results
     */
    public function paginate($page = 1, $perPage = 15)
    {
        $total = $this->count();
        
        $this->offset(($page - 1) * $perPage);
        $this->limit($perPage);
        
        $data = $this->get();
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total)
        ];
    }
}
