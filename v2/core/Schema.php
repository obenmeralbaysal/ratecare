<?php

namespace Core;

/**
 * Database Schema Builder
 */
class Schema
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create table
     */
    public static function create($table, $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
        
        $db = Database::getInstance();
        $db->query($sql);
    }
    
    /**
     * Modify table
     */
    public static function table($table, $callback)
    {
        $blueprint = new Blueprint($table, 'alter');
        $callback($blueprint);
        
        $statements = $blueprint->getStatements();
        
        $db = Database::getInstance();
        foreach ($statements as $sql) {
            $db->query($sql);
        }
    }
    
    /**
     * Drop table
     */
    public static function drop($table)
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS {$table}");
    }
    
    /**
     * Check if table exists
     */
    public static function hasTable($table)
    {
        $db = Database::getInstance();
        $result = $db->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            [$table]
        );
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if column exists
     */
    public static function hasColumn($table, $column)
    {
        $db = Database::getInstance();
        $result = $db->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        );
        
        return $result['count'] > 0;
    }
}

/**
 * Blueprint for table creation/modification
 */
class Blueprint
{
    private $table;
    private $columns = [];
    private $indexes = [];
    private $foreign_keys = [];
    private $action = 'create';
    private $statements = [];
    
    public function __construct($table, $action = 'create')
    {
        $this->table = $table;
        $this->action = $action;
    }
    
    /**
     * Add auto-incrementing ID column
     */
    public function id($name = 'id')
    {
        return $this->bigIncrements($name);
    }
    
    /**
     * Add big incrementing column
     */
    public function bigIncrements($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'BIGINT',
            'auto_increment' => true,
            'primary' => true,
            'unsigned' => true,
            'nullable' => false
        ];
        
        return $this;
    }
    
    /**
     * Add incrementing column
     */
    public function increments($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'INT',
            'auto_increment' => true,
            'primary' => true,
            'unsigned' => true,
            'nullable' => false
        ];
        
        return $this;
    }
    
    /**
     * Add string column
     */
    public function string($name, $length = 255)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => "VARCHAR({$length})",
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add text column
     */
    public function text($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'TEXT',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add integer column
     */
    public function integer($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'INT',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add big integer column
     */
    public function bigInteger($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'BIGINT',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add decimal column
     */
    public function decimal($name, $precision = 8, $scale = 2)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => "DECIMAL({$precision},{$scale})",
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add float column
     */
    public function float($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'FLOAT',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add boolean column
     */
    public function boolean($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'TINYINT(1)',
            'nullable' => true,
            'default' => 0
        ];
        
        return $this;
    }
    
    /**
     * Add date column
     */
    public function date($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'DATE',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add datetime column
     */
    public function dateTime($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'DATETIME',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add timestamp column
     */
    public function timestamp($name)
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'TIMESTAMP',
            'nullable' => true
        ];
        
        return $this;
    }
    
    /**
     * Add timestamps (created_at, updated_at)
     */
    public function timestamps()
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
        
        return $this;
    }
    
    /**
     * Make column nullable
     */
    public function nullable()
    {
        if (!empty($this->columns)) {
            $lastIndex = count($this->columns) - 1;
            $this->columns[$lastIndex]['nullable'] = true;
        }
        
        return $this;
    }
    
    /**
     * Set default value
     */
    public function default($value)
    {
        if (!empty($this->columns)) {
            $lastIndex = count($this->columns) - 1;
            $this->columns[$lastIndex]['default'] = $value;
        }
        
        return $this;
    }
    
    /**
     * Make column unsigned
     */
    public function unsigned()
    {
        if (!empty($this->columns)) {
            $lastIndex = count($this->columns) - 1;
            $this->columns[$lastIndex]['unsigned'] = true;
        }
        
        return $this;
    }
    
    /**
     * Add index
     */
    public function index($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: 'idx_' . $this->table . '_' . implode('_', $columns);
        
        $this->indexes[] = [
            'type' => 'INDEX',
            'name' => $name,
            'columns' => $columns
        ];
        
        return $this;
    }
    
    /**
     * Add unique index
     */
    public function unique($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: 'unique_' . $this->table . '_' . implode('_', $columns);
        
        $this->indexes[] = [
            'type' => 'UNIQUE',
            'name' => $name,
            'columns' => $columns
        ];
        
        return $this;
    }
    
    /**
     * Add foreign key
     */
    public function foreign($column)
    {
        return new ForeignKeyDefinition($this, $column);
    }
    
    /**
     * Add foreign key constraint
     */
    public function addForeignKey($column, $references, $on, $onDelete = null, $onUpdate = null)
    {
        $this->foreign_keys[] = [
            'column' => $column,
            'references' => $references,
            'on' => $on,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];
    }
    
    /**
     * Generate SQL for table creation
     */
    public function toSql()
    {
        if ($this->action === 'create') {
            return $this->createTableSql();
        }
        
        return '';
    }
    
    /**
     * Get ALTER statements
     */
    public function getStatements()
    {
        return $this->statements;
    }
    
    /**
     * Generate CREATE TABLE SQL
     */
    private function createTableSql()
    {
        $sql = "CREATE TABLE {$this->table} (\n";
        
        $columnDefinitions = [];
        
        // Add columns
        foreach ($this->columns as $column) {
            $definition = "  {$column['name']} {$column['type']}";
            
            if (isset($column['unsigned']) && $column['unsigned']) {
                $definition .= ' UNSIGNED';
            }
            
            if (isset($column['nullable']) && !$column['nullable']) {
                $definition .= ' NOT NULL';
            }
            
            if (isset($column['auto_increment']) && $column['auto_increment']) {
                $definition .= ' AUTO_INCREMENT';
            }
            
            if (isset($column['default'])) {
                $default = is_string($column['default']) ? "'{$column['default']}'" : $column['default'];
                $definition .= " DEFAULT {$default}";
            }
            
            $columnDefinitions[] = $definition;
        }
        
        // Add primary key
        $primaryKeys = [];
        foreach ($this->columns as $column) {
            if (isset($column['primary']) && $column['primary']) {
                $primaryKeys[] = $column['name'];
            }
        }
        
        if (!empty($primaryKeys)) {
            $columnDefinitions[] = "  PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
        }
        
        // Add indexes
        foreach ($this->indexes as $index) {
            $columns = implode(', ', $index['columns']);
            $columnDefinitions[] = "  {$index['type']} {$index['name']} ({$columns})";
        }
        
        $sql .= implode(",\n", $columnDefinitions);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $sql;
    }
}

/**
 * Foreign Key Definition Helper
 */
class ForeignKeyDefinition
{
    private $blueprint;
    private $column;
    
    public function __construct($blueprint, $column)
    {
        $this->blueprint = $blueprint;
        $this->column = $column;
    }
    
    public function references($column)
    {
        $this->references = $column;
        return $this;
    }
    
    public function on($table)
    {
        $this->on = $table;
        return $this;
    }
    
    public function onDelete($action)
    {
        $this->onDelete = $action;
        return $this;
    }
    
    public function onUpdate($action)
    {
        $this->onUpdate = $action;
        return $this;
    }
    
    public function cascadeOnDelete()
    {
        return $this->onDelete('CASCADE');
    }
    
    public function nullOnDelete()
    {
        return $this->onDelete('SET NULL');
    }
}
