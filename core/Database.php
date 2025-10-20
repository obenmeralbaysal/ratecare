<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 */
class Database
{
    private static $instance = null;
    private $connection = null;
    private $config = [];
    
    private function __construct()
    {
        // Singleton pattern
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function connect($config = null)
    {
        if ($config === null) {
            $config = Config::get('database.connections.mysql');
        }
        
        $this->config = $config;
        
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
            
            return $this->connection;
            
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function select($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->getConnection()->lastInsertId();
    }
    
    public function update($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }
    
    public function commit()
    {
        return $this->getConnection()->commit();
    }
    
    public function rollback()
    {
        return $this->getConnection()->rollback();
    }
    
    /**
     * Get PDO instance
     */
    public function getPdo()
    {
        return $this->pdo;
    }
    
    /**
     * Get database name
     */
    public function getDatabaseName()
    {
        return $this->config['database'];
    }
}
