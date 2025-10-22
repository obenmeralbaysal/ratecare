<?php
/**
 * Cache Warming Job
 * Runs in the morning to pre-load popular widgets into cache
 * 
 * Cron: 0 6 * * * php /var/www/ratecare/jobs/warm_cache.php
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

echo "============================================\n";
echo "Cache Warming Job\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

try {
    $db = \Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check if warming is enabled
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute(['cache-warming-enabled']);
    $result = $stmt->fetch();
    $enabled = $result ? (bool) $result['value'] : false;
    
    if (!$enabled) {
        echo "⚠ Cache warming is DISABLED in settings\n";
        echo "Skipping...\n";
        exit(0);
    }
    
    // Get widget count setting
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute(['cache-warming-widget-count']);
    $result = $stmt->fetch();
    $widgetCount = $result ? (int) $result['value'] : 10;
    
    echo "Warming cache for top {$widgetCount} widgets\n\n";
    
    // Get most popular widgets (last 7 days)
    $stmt = $pdo->prepare("
        SELECT 
            widget_code,
            COUNT(*) as request_count,
            parameters
        FROM api_statistics
        WHERE request_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY widget_code, parameters
        ORDER BY request_count DESC
        LIMIT ?
    ");
    $stmt->execute([$widgetCount]);
    $popularWidgets = $stmt->fetchAll();
    
    if (empty($popularWidgets)) {
        echo "⚠ No popular widgets found\n";
        echo "Nothing to warm\n";
        exit(0);
    }
    
    echo "Found " . count($popularWidgets) . " popular widget configurations\n\n";
    
    $warmedCount = 0;
    $skippedCount = 0;
    $failedCount = 0;
    
    foreach ($popularWidgets as $index => $widget) {
        $num = $index + 1;
        $widgetCode = $widget['widget_code'];
        $params = json_decode($widget['parameters'], true);
        $requests = $widget['request_count'];
        
        echo "{$num}. Widget: {$widgetCode} ({$requests} requests/week)\n";
        
        // Check if already cached
        $cacheKey = generateCacheKey($widgetCode, $params);
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM api_cache 
            WHERE cache_key = ? AND expires_at > NOW()
        ");
        $stmt->execute([$cacheKey]);
        $exists = $stmt->fetch()['count'] > 0;
        
        if ($exists) {
            echo "   ⏭ Already cached, skipping\n";
            $skippedCount++;
            continue;
        }
        
        // Make API request to warm cache
        $url = env('APP_URL') . '/api/' . $widgetCode;
        $queryString = http_build_query($params);
        $fullUrl = $url . '?' . $queryString;
        
        echo "   🔥 Warming: {$fullUrl}\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "   ✓ Warmed successfully\n";
            $warmedCount++;
        } else {
            echo "   ✗ Failed (HTTP {$httpCode})\n";
            $failedCount++;
        }
        
        // Small delay to avoid overwhelming the server
        usleep(500000); // 0.5 second
        echo "\n";
    }
    
    // Summary
    echo "============================================\n";
    echo "Summary:\n";
    echo "  - Warmed: {$warmedCount}\n";
    echo "  - Skipped (already cached): {$skippedCount}\n";
    echo "  - Failed: {$failedCount}\n";
    echo "============================================\n";
    
    // Log to file
    $logFile = APP_ROOT . '/storage/logs/warming.log';
    $logMessage = sprintf(
        "[%s] Cache warming completed: %d warmed, %d skipped, %d failed\n",
        date('Y-m-d H:i:s'),
        $warmedCount,
        $skippedCount,
        $failedCount
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    echo "\n✓ Cache Warming Job Completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    // Log error
    $logFile = APP_ROOT . '/storage/logs/warming.log';
    $logMessage = sprintf(
        "[%s] ERROR: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    exit(1);
}

/**
 * Generate cache key (same as ApiCache helper)
 */
function generateCacheKey($widgetCode, $params)
{
    $keyParts = [
        'api_cache',
        $widgetCode,
        $params['currency'] ?? 'EUR',
        $params['checkin'] ?? '',
        $params['checkout'] ?? '',
        $params['adult'] ?? 2,
        $params['child'] ?? 0,
        $params['infant'] ?? 0
    ];
    
    return implode(':', $keyParts);
}
