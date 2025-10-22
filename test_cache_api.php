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

// Test the controller directly
echo "Testing CacheStatsController::summary()\n";
echo str_repeat('-', 50) . "\n";

try {
    $controller = new \App\Controllers\Api\CacheStatsController();
    
    // Simulate the summary request
    ob_start();
    $controller->summary();
    $output = ob_get_clean();
    
    echo "Response:\n";
    echo $output . "\n\n";
    
    // Parse JSON
    $response = json_decode($output, true);
    
    if ($response && $response['status'] === 'success') {
        echo "✓ API Response: SUCCESS\n";
        echo "✓ Data:\n";
        echo "  - Cache Hit Rate: " . $response['data']['cache_hit_rate'] . "%\n";
        echo "  - Total Requests: " . $response['data']['total_requests'] . "\n";
        echo "  - Full Hits: " . $response['data']['full_hits'] . "\n";
        echo "  - Partial Hits: " . $response['data']['partial_hits'] . "\n";
        echo "  - Misses: " . $response['data']['misses'] . "\n";
        echo "  - Top Channel: " . $response['data']['top_channel'] . "\n";
        echo "  - Cache Entries: " . $response['data']['cache_entries'] . "\n";
    } else {
        echo "❌ API Response: FAILED\n";
        print_r($response);
    }
    
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
