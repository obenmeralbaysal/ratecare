<?php
/**
 * Run PHASE 5-6 Migration
 * Adds settings and circuit breaker table
 */

define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->register();

\Core\Environment::load(APP_ROOT . '/.env');

echo "============================================\n";
echo "PHASE 5-6: Background Jobs + Circuit Breaker\n";
echo "Migration Runner\n";
echo "============================================\n\n";

try {
    $db = \Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "Running migration: 002_add_cache_settings.sql\n\n";
    
    $sqlFile = APP_ROOT . '/database/migrations/002_add_cache_settings.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon to execute multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            echo "✓ Executed statement " . ($index + 1) . "\n";
        } catch (PDOException $e) {
            // Ignore duplicate key errors (settings already exist)
            if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    echo "\n============================================\n";
    echo "✓ Migration completed successfully!\n";
    echo "============================================\n\n";
    
    // Verify tables
    echo "Verifying installation...\n\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'circuit_breaker_state'");
    if ($stmt->rowCount() > 0) {
        echo "✓ circuit_breaker_state table created\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM circuit_breaker_state");
        $count = $stmt->fetch()['count'];
        echo "  → {$count} platforms initialized\n";
    }
    
    echo "\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM settings WHERE `key` LIKE 'cache-%' OR `key` LIKE 'circuit-%'");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "✓ {$count} cache/circuit settings added\n";
    
    echo "\n";
    echo "Settings added:\n";
    $stmt = $pdo->prepare("SELECT `key`, `value`, `description` FROM settings WHERE `key` LIKE 'cache-%' OR `key` LIKE 'circuit-%' ORDER BY `key`");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        echo "  • {$row['key']}: {$row['value']}\n";
        echo "    {$row['description']}\n";
    }
    
    echo "\n============================================\n";
    echo "Next Steps:\n";
    echo "============================================\n\n";
    echo "1. Setup cron jobs:\n";
    echo "   chmod +x setup_cron_jobs.sh\n";
    echo "   ./setup_cron_jobs.sh\n\n";
    
    echo "2. Test background jobs:\n";
    echo "   php jobs/cleanup_expired_cache.php\n";
    echo "   php jobs/aggregate_statistics.php\n";
    echo "   php jobs/warm_cache.php\n\n";
    
    echo "3. Test circuit breaker:\n";
    echo "   curl https://test.ratecare.net/api/v1/circuit-breaker/status\n\n";
    
    echo "4. View settings in admin panel:\n";
    echo "   https://test.ratecare.net/admin/settings\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
