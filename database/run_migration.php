<?php
/**
 * Database Migration Runner
 * Runs SQL migration files
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load autoloader
require_once APP_ROOT . '/core/Autoloader.php';

// Register autoloader
$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

// Load environment variables
\Core\Environment::load(APP_ROOT . '/.env');

// Load helper functions
require_once APP_ROOT . '/app/Helpers/functions.php';

// Database configuration from .env
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', 3306);
$database = env('DB_DATABASE', 'hoteldigilab_new');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

echo "============================================\n";
echo "RateCare Database Migration Runner\n";
echo "============================================\n\n";

try {
    // Connect to database
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Database connected: {$database}\n\n";
    
    // Get migration file from command line or run all
    $migrationFile = $argv[1] ?? null;
    
    if ($migrationFile) {
        // Run specific migration
        $file = __DIR__ . '/migrations/' . $migrationFile;
        if (!file_exists($file)) {
            throw new Exception("Migration file not found: {$file}");
        }
        runMigration($pdo, $file);
    } else {
        // Run all migrations
        $migrationDir = __DIR__ . '/migrations';
        $files = glob($migrationDir . '/*.sql');
        
        if (empty($files)) {
            echo "No migration files found.\n";
            exit(0);
        }
        
        sort($files); // Run in order
        
        foreach ($files as $file) {
            runMigration($pdo, $file);
        }
    }
    
    echo "\n============================================\n";
    echo "✓ All migrations completed successfully!\n";
    echo "============================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

/**
 * Run a single migration file
 */
function runMigration(PDO $pdo, string $file): void
{
    $filename = basename($file);
    echo "Running migration: {$filename}\n";
    echo str_repeat('-', 50) . "\n";
    
    // Read SQL file
    $sql = file_get_contents($file);
    
    if (empty($sql)) {
        echo "⚠ Warning: Empty migration file\n\n";
        return;
    }
    
    // Split by semicolon (handle multiple statements)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0 && 
                   $stmt !== '';
        }
    );
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        // Skip comments
        if (strpos(trim($statement), '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Check if it's a "table already exists" error
            if ($e->getCode() === '42S01' || strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠ Table already exists - skipping\n";
            } else {
                echo "❌ Error: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    echo "✓ Executed {$executed} statement(s)";
    if ($errors > 0) {
        echo " ({$errors} error(s))";
    }
    echo "\n\n";
}
