<?php

namespace Core;

/**
 * Database Migration System
 */
class Migration
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        )";
        
        $this->db->query($sql);
    }
    
    /**
     * Run migrations
     */
    public function run($path = null)
    {
        $path = $path ?: __DIR__ . '/../database/migrations/';
        
        if (!is_dir($path)) {
            throw new \Exception("Migration directory not found: {$path}");
        }
        
        $files = glob($path . '*.php');
        sort($files);
        
        $executed = $this->getExecutedMigrations();
        $batch = $this->getNextBatchNumber();
        
        $newMigrations = [];
        
        foreach ($files as $file) {
            $migration = basename($file, '.php');
            
            if (!in_array($migration, $executed)) {
                echo "Running migration: {$migration}\n";
                
                try {
                    $this->runMigration($file);
                    $this->recordMigration($migration, $batch);
                    $newMigrations[] = $migration;
                    
                    echo "Migrated: {$migration}\n";
                } catch (\Exception $e) {
                    echo "Migration failed: {$migration} - " . $e->getMessage() . "\n";
                    break;
                }
            }
        }
        
        if (empty($newMigrations)) {
            echo "Nothing to migrate.\n";
        } else {
            echo "Migrated " . count($newMigrations) . " migrations.\n";
        }
        
        return $newMigrations;
    }
    
    /**
     * Rollback migrations
     */
    public function rollback($steps = 1)
    {
        $batches = $this->db->select(
            "SELECT DISTINCT batch FROM migrations ORDER BY batch DESC LIMIT ?",
            [$steps]
        );
        
        if (empty($batches)) {
            echo "Nothing to rollback.\n";
            return [];
        }
        
        $batchNumbers = array_column($batches, 'batch');
        $placeholders = str_repeat('?,', count($batchNumbers) - 1) . '?';
        
        $migrations = $this->db->select(
            "SELECT migration FROM migrations WHERE batch IN ({$placeholders}) ORDER BY id DESC",
            $batchNumbers
        );
        
        $rolledBack = [];
        
        foreach ($migrations as $migration) {
            $migrationName = $migration['migration'];
            echo "Rolling back: {$migrationName}\n";
            
            try {
                $this->rollbackMigration($migrationName);
                $this->removeMigrationRecord($migrationName);
                $rolledBack[] = $migrationName;
                
                echo "Rolled back: {$migrationName}\n";
            } catch (\Exception $e) {
                echo "Rollback failed: {$migrationName} - " . $e->getMessage() . "\n";
                break;
            }
        }
        
        return $rolledBack;
    }
    
    /**
     * Reset all migrations
     */
    public function reset()
    {
        $migrations = $this->db->select(
            "SELECT migration FROM migrations ORDER BY id DESC"
        );
        
        foreach ($migrations as $migration) {
            $migrationName = $migration['migration'];
            echo "Rolling back: {$migrationName}\n";
            
            try {
                $this->rollbackMigration($migrationName);
                echo "Rolled back: {$migrationName}\n";
            } catch (\Exception $e) {
                echo "Rollback failed: {$migrationName} - " . $e->getMessage() . "\n";
            }
        }
        
        // Clear migrations table
        $this->db->query("DELETE FROM migrations");
        
        echo "All migrations reset.\n";
    }
    
    /**
     * Get migration status
     */
    public function status($path = null)
    {
        $path = $path ?: __DIR__ . '/../database/migrations/';
        
        $files = glob($path . '*.php');
        sort($files);
        
        $executed = $this->getExecutedMigrations();
        
        echo "Migration Status:\n";
        echo str_repeat('-', 50) . "\n";
        
        foreach ($files as $file) {
            $migration = basename($file, '.php');
            $status = in_array($migration, $executed) ? 'Ran' : 'Pending';
            echo sprintf("%-40s %s\n", $migration, $status);
        }
    }
    
    /**
     * Run single migration file
     */
    private function runMigration($file)
    {
        $migration = require $file;
        
        if (!is_object($migration) || !method_exists($migration, 'up')) {
            throw new \Exception("Invalid migration file: {$file}");
        }
        
        $migration->up($this->db);
    }
    
    /**
     * Rollback single migration
     */
    private function rollbackMigration($migrationName)
    {
        $path = __DIR__ . '/../database/migrations/';
        $file = $path . $migrationName . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("Migration file not found: {$file}");
        }
        
        $migration = require $file;
        
        if (!is_object($migration) || !method_exists($migration, 'down')) {
            throw new \Exception("Migration does not support rollback: {$migrationName}");
        }
        
        $migration->down($this->db);
    }
    
    /**
     * Get executed migrations
     */
    private function getExecutedMigrations()
    {
        $result = $this->db->select("SELECT migration FROM migrations ORDER BY id");
        return array_column($result, 'migration');
    }
    
    /**
     * Get next batch number
     */
    private function getNextBatchNumber()
    {
        $result = $this->db->selectOne("SELECT MAX(batch) as max_batch FROM migrations");
        return ($result['max_batch'] ?? 0) + 1;
    }
    
    /**
     * Record migration execution
     */
    private function recordMigration($migration, $batch)
    {
        $this->db->insert(
            "INSERT INTO migrations (migration, batch) VALUES (?, ?)",
            [$migration, $batch]
        );
    }
    
    /**
     * Remove migration record
     */
    private function removeMigrationRecord($migration)
    {
        $this->db->delete(
            "DELETE FROM migrations WHERE migration = ?",
            [$migration]
        );
    }
}
