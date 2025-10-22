<?php
/**
 * Cleanup Expired Cache Job
 * Runs daily to remove expired cache entries
 * 
 * Cron: 0 3 * * * php /var/www/ratecare/jobs/cleanup_expired_cache.php
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load autoloader
require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

// Load environment
\Core\Environment::load(APP_ROOT . '/.env');

// Load helper functions
require_once APP_ROOT . '/app/Helpers/functions.php';

use App\Helpers\ApiCache;

echo "============================================\n";
echo "Cleanup Expired Cache Job\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

try {
    // Initialize cache helper
    $cache = new ApiCache();
    
    // Check if cleanup is enabled
    $db = \Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute(['cache-cleanup-enabled']);
    $result = $stmt->fetch();
    $enabled = $result ? (bool) $result['value'] : true;
    
    if (!$enabled) {
        echo "⚠ Cache cleanup is DISABLED in settings\n";
        echo "Skipping...\n";
        exit(0);
    }
    
    // Get expired cache count
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM api_cache 
        WHERE expires_at < NOW()
    ");
    $expiredCount = $stmt->fetch()['count'];
    
    echo "Expired cache entries found: {$expiredCount}\n";
    
    if ($expiredCount > 0) {
        // Delete expired cache
        $stmt = $pdo->exec("DELETE FROM api_cache WHERE expires_at < NOW()");
        echo "✓ Deleted {$expiredCount} expired cache entries\n";
    } else {
        echo "✓ No expired cache entries to delete\n";
    }
    
    // Optimize table
    $pdo->exec("OPTIMIZE TABLE api_cache");
    echo "✓ Table optimized\n";
    
    // Log to file
    $logFile = APP_ROOT . '/storage/logs/cleanup.log';
    $logMessage = sprintf(
        "[%s] Cleanup completed: %d expired entries deleted\n",
        date('Y-m-d H:i:s'),
        $expiredCount
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    echo "\n============================================\n";
    echo "✓ Cleanup Job Completed Successfully!\n";
    echo "============================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    // Log error
    $logFile = APP_ROOT . '/storage/logs/cleanup.log';
    $logMessage = sprintf(
        "[%s] ERROR: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    exit(1);
}
