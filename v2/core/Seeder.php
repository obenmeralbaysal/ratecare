<?php

namespace Core;

/**
 * Database Seeder
 */
class Seeder
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Run all seeders
     */
    public function run($path = null)
    {
        $path = $path ?: __DIR__ . '/../database/seeders/';
        
        if (!is_dir($path)) {
            echo "Seeder directory not found: {$path}\n";
            return;
        }
        
        $files = glob($path . '*.php');
        sort($files);
        
        echo "Running database seeders...\n";
        
        foreach ($files as $file) {
            $seederName = basename($file, '.php');
            echo "Seeding: {$seederName}\n";
            
            try {
                $seeder = require $file;
                
                if (is_object($seeder) && method_exists($seeder, 'run')) {
                    $seeder->run($this->db);
                    echo "Seeded: {$seederName}\n";
                } else {
                    echo "Invalid seeder: {$seederName}\n";
                }
            } catch (\Exception $e) {
                echo "Seeder failed: {$seederName} - " . $e->getMessage() . "\n";
            }
        }
        
        echo "Database seeding completed.\n";
    }
    
    /**
     * Run specific seeder
     */
    public function runSeeder($seederName, $path = null)
    {
        $path = $path ?: __DIR__ . '/../database/seeders/';
        $file = $path . $seederName . '.php';
        
        if (!file_exists($file)) {
            echo "Seeder not found: {$seederName}\n";
            return false;
        }
        
        echo "Running seeder: {$seederName}\n";
        
        try {
            $seeder = require $file;
            
            if (is_object($seeder) && method_exists($seeder, 'run')) {
                $seeder->run($this->db);
                echo "Seeded: {$seederName}\n";
                return true;
            } else {
                echo "Invalid seeder: {$seederName}\n";
                return false;
            }
        } catch (\Exception $e) {
            echo "Seeder failed: {$seederName} - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Truncate table
     */
    public function truncate($table)
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->db->query("TRUNCATE TABLE {$table}");
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    /**
     * Truncate all tables
     */
    public function truncateAll()
    {
        $tables = $this->db->select("SHOW TABLES");
        $tableColumn = 'Tables_in_' . $this->db->getDatabaseName();
        
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            $tableName = $table[$tableColumn];
            if ($tableName !== 'migrations') {
                $this->db->query("TRUNCATE TABLE {$tableName}");
                echo "Truncated: {$tableName}\n";
            }
        }
        
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "All tables truncated.\n";
    }
}
