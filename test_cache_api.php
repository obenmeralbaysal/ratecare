<?php
/**
 * Test Cache API Endpoint
 */

// Define application root
define('APP_ROOT', __DIR__);

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

echo "============================================\n";
echo "Testing Cache API Endpoint\n";
echo "============================================\n\n";

// Initialize database
try {
    $db = \Core\Database::getInstance();
    $db->connect([
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hoteldigilab_new'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4'
    ]);
    echo "✓ Database connected\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test the helpers directly (CLI-friendly)
echo "Testing Cache & Statistics Helpers\n";
echo str_repeat('-', 50) . "\n";

try {
    $cache = new \App\Helpers\ApiCache();
    $stats = new \App\Helpers\ApiStatistics();
    
    $today = date('Y-m-d');
    
    echo "1. Getting Cache Stats...\n";
    $cacheStats = $cache->getStats();
    echo "   ✓ Total Entries: " . ($cacheStats['total_entries'] ?? 0) . "\n";
    echo "   ✓ Active Entries: " . ($cacheStats['active_entries'] ?? 0) . "\n";
    echo "   ✓ Expired Entries: " . ($cacheStats['expired_entries'] ?? 0) . "\n\n";
    
    echo "2. Getting Cache Hit Rate...\n";
    $hitRate = $stats->getCacheHitRate($today, $today);
    echo "   ✓ Total Requests: " . $hitRate['total'] . "\n";
    echo "   ✓ Full Hits: " . $hitRate['full_hits'] . "\n";
    echo "   ✓ Partial Hits: " . $hitRate['partial_hits'] . "\n";
    echo "   ✓ Misses: " . $hitRate['misses'] . "\n";
    echo "   ✓ Hit Rate: " . round($hitRate['hit_rate'], 1) . "%\n\n";
    
    echo "3. Getting Channel Usage...\n";
    $channelUsage = $stats->getChannelUsage($today, $today);
    if (!empty($channelUsage)) {
        foreach ($channelUsage as $channel => $count) {
            echo "   ✓ {$channel}: {$count} requests\n";
        }
        $topChannel = array_key_first($channelUsage);
        echo "   → Top Channel: " . ucfirst($topChannel) . "\n";
    } else {
        echo "   ℹ No channel usage data yet\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "✓ API Data Ready!\n";
    echo str_repeat('=', 50) . "\n\n";
    
    // Simulate API response
    $apiResponse = [
        'status' => 'success',
        'data' => [
            'cache_hit_rate' => round($hitRate['hit_rate'], 1),
            'full_hit_rate' => round($hitRate['full_hit_rate'], 1),
            'partial_hit_rate' => round($hitRate['partial_hit_rate'], 1),
            'total_requests' => $hitRate['total'],
            'full_hits' => $hitRate['full_hits'],
            'partial_hits' => $hitRate['partial_hits'],
            'misses' => $hitRate['misses'],
            'top_channel' => !empty($channelUsage) ? ucfirst(array_key_first($channelUsage)) : 'N/A',
            'cache_entries' => $cacheStats['active_entries'] ?? 0
        ]
    ];
    
    echo "Expected API Response:\n";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n============================================\n";
echo "API Endpoint URLs:\n";
echo "============================================\n";
echo "Direct: /api/v1/cache/summary\n";
echo "Full URL: " . url('/api/v1/cache/summary') . "\n";
echo "\n";

echo "Test with curl:\n";
echo "curl " . url('/api/v1/cache/summary') . "\n";
echo "\n";
