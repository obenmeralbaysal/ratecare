<?php

/**
 * Hotel DigiLab Backup System
 * Usage: php scripts/backup.php [--type=full|database|files] [--compress] [--cleanup]
 */

require_once __DIR__ . '/../vendor/autoload.php';

class BackupManager
{
    private $config;
    private $backupDir;
    private $timestamp;
    private $logFile;
    
    public function __construct()
    {
        $this->loadEnvironment();
        $this->config = $this->getConfig();
        $this->backupDir = $this->config['backup_dir'];
        $this->timestamp = date('Y-m-d_H-i-s');
        $this->logFile = __DIR__ . '/../storage/logs/backup.log';
        
        $this->ensureBackupDirectory();
    }
    
    /**
     * Load environment variables
     */
    private function loadEnvironment()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value, '"\'');
                }
            }
        }
    }
    
    /**
     * Get backup configuration
     */
    private function getConfig()
    {
        return [
            'backup_dir' => '/var/backups/hoteldigilab',
            'retention_days' => 30,
            'max_backups' => 50,
            'compress' => true,
            'exclude_patterns' => [
                'storage/cache/*',
                'storage/logs/*',
                'node_modules',
                '.git',
                '*.tmp',
                '*.log'
            ]
        ];
    }
    
    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory()
    {
        if (!is_dir($this->backupDir)) {
            if (!mkdir($this->backupDir, 0755, true)) {
                $this->error("Failed to create backup directory: {$this->backupDir}");
            }
        }
        
        if (!is_writable($this->backupDir)) {
            $this->error("Backup directory is not writable: {$this->backupDir}");
        }
    }
    
    /**
     * Run full backup
     */
    public function runFullBackup($compress = true)
    {
        $this->log("Starting full backup...");
        
        $backupName = "hoteldigilab_full_{$this->timestamp}";
        $backupPath = $this->backupDir . '/' . $backupName;
        
        // Create backup directory
        mkdir($backupPath, 0755, true);
        
        // Backup database
        $dbBackup = $this->backupDatabase($backupPath . '/database.sql');
        
        // Backup files
        $filesBackup = $this->backupFiles($backupPath . '/files');
        
        // Create manifest
        $this->createManifest($backupPath, [
            'type' => 'full',
            'database' => $dbBackup,
            'files' => $filesBackup
        ]);
        
        // Compress if requested
        if ($compress) {
            $this->compressBackup($backupPath);
        }
        
        $this->log("Full backup completed: $backupName");
        return $backupName;
    }
    
    /**
     * Backup database
     */
    public function backupDatabase($outputFile = null)
    {
        $this->log("Starting database backup...");
        
        if (!$outputFile) {
            $outputFile = $this->backupDir . "/database_backup_{$this->timestamp}.sql";
        }
        
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        if (empty($database)) {
            $this->error("Database configuration not found");
        }
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outputFile)
        );
        
        // Execute backup
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Database backup failed with code: $returnCode");
        }
        
        if (!file_exists($outputFile) || filesize($outputFile) === 0) {
            $this->error("Database backup file is empty or not created");
        }
        
        $size = $this->formatBytes(filesize($outputFile));
        $this->log("Database backup completed: $outputFile ($size)");
        
        return [
            'file' => $outputFile,
            'size' => filesize($outputFile),
            'tables' => $this->getTableCount()
        ];
    }
    
    /**
     * Backup files
     */
    public function backupFiles($outputDir = null)
    {
        $this->log("Starting files backup...");
        
        if (!$outputDir) {
            $outputDir = $this->backupDir . "/files_backup_{$this->timestamp}";
        }
        
        $sourceDir = dirname(__DIR__);
        
        // Create output directory
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // Build rsync command with exclusions
        $excludes = '';
        foreach ($this->config['exclude_patterns'] as $pattern) {
            $excludes .= " --exclude='" . $pattern . "'";
        }
        
        $command = sprintf(
            'rsync -av %s %s/ %s/',
            $excludes,
            escapeshellarg($sourceDir),
            escapeshellarg($outputDir)
        );
        
        // Execute backup
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Files backup failed with code: $returnCode");
        }
        
        $size = $this->getDirectorySize($outputDir);
        $fileCount = $this->getFileCount($outputDir);
        
        $this->log("Files backup completed: $outputDir (" . $this->formatBytes($size) . ", $fileCount files)");
        
        return [
            'directory' => $outputDir,
            'size' => $size,
            'file_count' => $fileCount
        ];
    }
    
    /**
     * Create backup manifest
     */
    private function createManifest($backupPath, $info)
    {
        $manifest = [
            'backup_name' => basename($backupPath),
            'timestamp' => $this->timestamp,
            'date' => date('Y-m-d H:i:s'),
            'type' => $info['type'],
            'version' => $this->getApplicationVersion(),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'database' => $info['database'] ?? null,
            'files' => $info['files'] ?? null,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'hostname' => gethostname()
            ]
        ];
        
        file_put_contents(
            $backupPath . '/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT)
        );
    }
    
    /**
     * Compress backup
     */
    private function compressBackup($backupPath)
    {
        $this->log("Compressing backup...");
        
        $archiveName = $backupPath . '.tar.gz';
        $command = sprintf(
            'tar -czf %s -C %s %s',
            escapeshellarg($archiveName),
            escapeshellarg(dirname($backupPath)),
            escapeshellarg(basename($backupPath))
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($archiveName)) {
            // Remove uncompressed directory
            $this->removeDirectory($backupPath);
            
            $size = $this->formatBytes(filesize($archiveName));
            $this->log("Backup compressed: $archiveName ($size)");
        } else {
            $this->error("Failed to compress backup");
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups()
    {
        $backups = [];
        $files = glob($this->backupDir . '/*');
        
        foreach ($files as $file) {
            if (is_dir($file) || pathinfo($file, PATHINFO_EXTENSION) === 'gz') {
                $manifestFile = is_dir($file) 
                    ? $file . '/manifest.json'
                    : null;
                
                $info = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => is_dir($file) ? $this->getDirectorySize($file) : filesize($file),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => 'unknown'
                ];
                
                // Read manifest if available
                if ($manifestFile && file_exists($manifestFile)) {
                    $manifest = json_decode(file_get_contents($manifestFile), true);
                    if ($manifest) {
                        $info = array_merge($info, $manifest);
                    }
                }
                
                $backups[] = $info;
            }
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Restore from backup
     */
    public function restore($backupName, $options = [])
    {
        $this->log("Starting restore from backup: $backupName");
        
        $backupPath = $this->backupDir . '/' . $backupName;
        
        if (!file_exists($backupPath)) {
            $this->error("Backup not found: $backupName");
        }
        
        // Extract if compressed
        if (pathinfo($backupPath, PATHINFO_EXTENSION) === 'gz') {
            $this->log("Extracting compressed backup...");
            $extractPath = $this->backupDir . '/restore_temp_' . time();
            mkdir($extractPath, 0755, true);
            
            $command = sprintf(
                'tar -xzf %s -C %s',
                escapeshellarg($backupPath),
                escapeshellarg($extractPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                $this->error("Failed to extract backup");
            }
            
            $backupPath = $extractPath . '/' . pathinfo($backupName, PATHINFO_FILENAME);
        }
        
        // Read manifest
        $manifestFile = $backupPath . '/manifest.json';
        if (!file_exists($manifestFile)) {
            $this->error("Backup manifest not found");
        }
        
        $manifest = json_decode(file_get_contents($manifestFile), true);
        
        // Restore database
        if (!empty($options['skip_database']) !== true && isset($manifest['database'])) {
            $this->restoreDatabase($backupPath . '/database.sql');
        }
        
        // Restore files
        if (!empty($options['skip_files']) !== true && isset($manifest['files'])) {
            $this->restoreFiles($backupPath . '/files');
        }
        
        // Cleanup temp extraction
        if (isset($extractPath)) {
            $this->removeDirectory($extractPath);
        }
        
        $this->log("Restore completed successfully");
    }
    
    /**
     * Restore database
     */
    private function restoreDatabase($sqlFile)
    {
        $this->log("Restoring database...");
        
        if (!file_exists($sqlFile)) {
            $this->error("Database backup file not found: $sqlFile");
        }
        
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Database restore failed with code: $returnCode");
        }
        
        $this->log("Database restored successfully");
    }
    
    /**
     * Restore files
     */
    private function restoreFiles($filesDir)
    {
        $this->log("Restoring files...");
        
        if (!is_dir($filesDir)) {
            $this->error("Files backup directory not found: $filesDir");
        }
        
        $targetDir = dirname(__DIR__);
        
        $command = sprintf(
            'rsync -av --delete %s/ %s/',
            escapeshellarg($filesDir),
            escapeshellarg($targetDir)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Files restore failed with code: $returnCode");
        }
        
        $this->log("Files restored successfully");
    }
    
    /**
     * Cleanup old backups
     */
    public function cleanup()
    {
        $this->log("Starting backup cleanup...");
        
        $backups = $this->listBackups();
        $deleted = 0;
        
        foreach ($backups as $backup) {
            $age = time() - strtotime($backup['date']);
            $ageDays = $age / (24 * 60 * 60);
            
            if ($ageDays > $this->config['retention_days'] || 
                count($backups) - $deleted > $this->config['max_backups']) {
                
                $this->log("Deleting old backup: " . $backup['name']);
                
                if (is_dir($backup['path'])) {
                    $this->removeDirectory($backup['path']);
                } else {
                    unlink($backup['path']);
                }
                
                $deleted++;
            }
        }
        
        $this->log("Cleanup completed. Deleted $deleted old backups");
    }
    
    /**
     * Utility functions
     */
    private function getTableCount()
    {
        try {
            $pdo = new PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . $_ENV['DB_DATABASE'],
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD']
            );
            
            $result = $pdo->query("SHOW TABLES");
            return $result->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getDirectorySize($dir)
    {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    private function getFileCount($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        return iterator_count($files);
    }
    
    private function getApplicationVersion()
    {
        $versionFile = dirname(__DIR__) . '/VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return 'unknown';
    }
    
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
    
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function log($message)
    {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        echo $logMessage;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    private function error($message)
    {
        $this->log("ERROR: $message");
        exit(1);
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['type:', 'compress', 'cleanup', 'list', 'restore:', 'help']);
    
    if (isset($options['help'])) {
        echo "Hotel DigiLab Backup System\n";
        echo "Usage: php backup.php [options]\n\n";
        echo "Options:\n";
        echo "  --type=TYPE      Backup type: full, database, files (default: full)\n";
        echo "  --compress       Compress backup archive\n";
        echo "  --cleanup        Remove old backups\n";
        echo "  --list           List available backups\n";
        echo "  --restore=NAME   Restore from backup\n";
        echo "  --help           Show this help\n";
        exit(0);
    }
    
    $backup = new BackupManager();
    
    if (isset($options['list'])) {
        $backups = $backup->listBackups();
        echo "Available backups:\n";
        foreach ($backups as $b) {
            echo sprintf("  %s (%s) - %s\n", 
                $b['name'], 
                $backup->formatBytes($b['size']), 
                $b['date']
            );
        }
        exit(0);
    }
    
    if (isset($options['restore'])) {
        $backup->restore($options['restore']);
        exit(0);
    }
    
    if (isset($options['cleanup'])) {
        $backup->cleanup();
        exit(0);
    }
    
    $type = $options['type'] ?? 'full';
    $compress = isset($options['compress']);
    
    switch ($type) {
        case 'database':
            $backup->backupDatabase();
            break;
        case 'files':
            $backup->backupFiles();
            break;
        case 'full':
        default:
            $backup->runFullBackup($compress);
            break;
    }
}
