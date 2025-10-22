<?php
/**
 * Aggregate Statistics Job
 * Runs daily to aggregate detailed statistics into summaries
 * Deletes old detailed records based on retention setting
 * 
 * Cron: 0 2 * * * php /var/www/ratecare/jobs/aggregate_statistics.php
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

use App\Helpers\ApiStatistics;

echo "============================================\n";
echo "Aggregate Statistics Job\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

try {
    $db = \Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get retention days setting
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute(['cache-statistics-retention-days']);
    $result = $stmt->fetch();
    $retentionDays = $result ? (int) $result['value'] : 30;
    
    echo "Statistics retention: {$retentionDays} days\n\n";
    
    // Aggregate yesterday's statistics
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    echo "Aggregating statistics for: {$yesterday}\n";
    
    // Check if already aggregated
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM api_statistics_summary 
        WHERE date = ?
    ");
    $stmt->execute([$yesterday]);
    $exists = $stmt->fetch()['count'] > 0;
    
    if ($exists) {
        echo "⚠ Statistics for {$yesterday} already aggregated, updating...\n";
        $action = 'UPDATE';
    } else {
        $action = 'INSERT';
    }
    
    // Aggregate data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN cache_hit_type = 'full' THEN 1 ELSE 0 END) as cache_full_hits,
            SUM(CASE WHEN cache_hit_type = 'partial' THEN 1 ELSE 0 END) as cache_partial_hits,
            SUM(CASE WHEN cache_hit_type = 'miss' THEN 1 ELSE 0 END) as cache_misses,
            AVG(response_time_ms) as avg_response_time_ms
        FROM api_statistics
        WHERE request_date = ?
    ");
    $stmt->execute([$yesterday]);
    $stats = $stmt->fetch();
    
    // Get channel usage
    $stmt = $pdo->prepare("
        SELECT JSON_UNQUOTE(JSON_EXTRACT(requested_platforms, '$[*]')) as platforms
        FROM api_statistics
        WHERE request_date = ?
        AND requested_platforms IS NOT NULL
    ");
    $stmt->execute([$yesterday]);
    
    $channelUsage = [];
    while ($row = $stmt->fetch()) {
        $platforms = json_decode('[' . $row['platforms'] . ']', true);
        if ($platforms) {
            foreach ($platforms as $platform) {
                if (!isset($channelUsage[$platform])) {
                    $channelUsage[$platform] = 0;
                }
                $channelUsage[$platform]++;
            }
        }
    }
    
    // Insert or update summary
    if ($action === 'INSERT') {
        $stmt = $pdo->prepare("
            INSERT INTO api_statistics_summary 
            (date, total_requests, cache_full_hits, cache_partial_hits, cache_misses, 
             channels_usage, avg_response_time_ms)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE api_statistics_summary 
            SET total_requests = ?,
                cache_full_hits = ?,
                cache_partial_hits = ?,
                cache_misses = ?,
                channels_usage = ?,
                avg_response_time_ms = ?
            WHERE date = ?
        ");
    }
    
    $channelsJson = json_encode($channelUsage);
    
    if ($action === 'INSERT') {
        $stmt->execute([
            $yesterday,
            $stats['total_requests'],
            $stats['cache_full_hits'],
            $stats['cache_partial_hits'],
            $stats['cache_misses'],
            $channelsJson,
            round($stats['avg_response_time_ms'])
        ]);
    } else {
        $stmt->execute([
            $stats['total_requests'],
            $stats['cache_full_hits'],
            $stats['cache_partial_hits'],
            $stats['cache_misses'],
            $channelsJson,
            round($stats['avg_response_time_ms']),
            $yesterday
        ]);
    }
    
    echo "✓ Statistics aggregated:\n";
    echo "  - Total Requests: {$stats['total_requests']}\n";
    echo "  - Full Hits: {$stats['cache_full_hits']}\n";
    echo "  - Partial Hits: {$stats['cache_partial_hits']}\n";
    echo "  - Misses: {$stats['cache_misses']}\n";
    echo "  - Avg Response Time: " . round($stats['avg_response_time_ms']) . "ms\n\n";
    
    // Delete old detailed records
    $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));
    
    echo "Deleting detailed statistics older than: {$cutoffDate}\n";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM api_statistics 
        WHERE request_date < ?
    ");
    $stmt->execute([$cutoffDate]);
    $oldRecords = $stmt->fetch()['count'];
    
    if ($oldRecords > 0) {
        $stmt = $pdo->prepare("DELETE FROM api_statistics WHERE request_date < ?");
        $stmt->execute([$cutoffDate]);
        echo "✓ Deleted {$oldRecords} old detailed records\n";
    } else {
        echo "✓ No old records to delete\n";
    }
    
    // Optimize table
    $pdo->exec("OPTIMIZE TABLE api_statistics");
    $pdo->exec("OPTIMIZE TABLE api_statistics_summary");
    echo "✓ Tables optimized\n";
    
    // Log to file
    $logFile = APP_ROOT . '/storage/logs/aggregate.log';
    $logMessage = sprintf(
        "[%s] Aggregation completed: %d requests aggregated, %d old records deleted\n",
        date('Y-m-d H:i:s'),
        $stats['total_requests'],
        $oldRecords
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    echo "\n============================================\n";
    echo "✓ Aggregation Job Completed Successfully!\n";
    echo "============================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    // Log error
    $logFile = APP_ROOT . '/storage/logs/aggregate.log';
    $logMessage = sprintf(
        "[%s] ERROR: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    exit(1);
}
