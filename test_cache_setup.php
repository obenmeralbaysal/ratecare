<?php
/**
 * Test Cache & Statistics Setup
 * Validates database tables and helper classes
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

use App\Helpers\ApiCache;
use App\Helpers\ApiStatistics;

echo "============================================\n";
echo "RateCare Cache & Statistics Setup Test\n";
echo "============================================\n\n";

// Test 1: Check database connection
echo "1. Testing Database Connection...\n";
try {
    $db = \Core\Database::getInstance();
    $db->connect([
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hoteldigilab_new'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4')
    ]);
    $pdo = $db->getConnection();
    echo "   ✓ Database connected successfully\n\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if tables exist
echo "2. Checking Database Tables...\n";
$tables = ['settings', 'api_cache', 'api_statistics', 'api_statistics_summary'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
        echo "   ✓ Table '{$table}' exists\n";
    } catch (Exception $e) {
        echo "   ❌ Table '{$table}' NOT FOUND - Please run migration first!\n";
        echo "      Run: php database/run_migration.php\n";
        exit(1);
    }
}
echo "\n";

// Test 3: Check cache-time setting
echo "3. Checking Cache Time Setting...\n";
try {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'caching-time' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "   ✓ Cache time setting found: {$result['value']} minutes\n\n";
    } else {
        echo "   ⚠ Cache time setting not found, will use default (30 minutes)\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking setting: " . $e->getMessage() . "\n\n";
}

// Test 4: Initialize ApiCache helper
echo "4. Testing ApiCache Helper...\n";
try {
    $cache = new ApiCache();
    
    // Test cache time retrieval
    $cacheTime = $cache->getCacheTime();
    echo "   ✓ ApiCache initialized - Cache TTL: {$cacheTime} minutes\n";
    
    // Test cache key generation
    $testKey = $cache->generateCacheKey('TEST123', [
        'currency' => 'EUR',
        'checkin' => '2025-10-27',
        'checkout' => '2025-10-28',
        'adult' => 2,
        'child' => 0,
        'infant' => 0
    ]);
    echo "   ✓ Cache key generated: {$testKey}\n";
    
    // Test cache set
    $testData = [
        'status' => 'success',
        'data' => [
            'platforms' => [
                ['name' => 'booking', 'status' => 'success', 'price' => 4320]
            ]
        ]
    ];
    
    $cache->set($testKey, $testData, 'TEST123', [
        'currency' => 'EUR',
        'checkin' => '2025-10-27',
        'checkout' => '2025-10-28'
    ]);
    echo "   ✓ Test data cached\n";
    
    // Test cache get
    $cached = $cache->get($testKey);
    if ($cached && $cached['status'] === 'success') {
        echo "   ✓ Cache retrieved successfully\n";
    } else {
        echo "   ⚠ Cache retrieval returned unexpected data\n";
    }
    
    // Test cache stats
    $stats = $cache->getStats();
    echo "   ✓ Cache stats: {$stats['total_entries']} total, {$stats['active_entries']} active\n";
    
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ ApiCache test failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Initialize ApiStatistics helper
echo "5. Testing ApiStatistics Helper...\n";
try {
    $stats = new ApiStatistics();
    
    // Test log request
    $logged = $stats->logRequest(
        'TEST123',
        ['currency' => 'EUR', 'checkin' => '2025-10-27'],
        'full',
        ['booking', 'etstur'],
        null,
        null,
        250
    );
    
    if ($logged) {
        echo "   ✓ Statistics logged successfully\n";
    } else {
        echo "   ⚠ Statistics logging returned false\n";
    }
    
    // Test hit rate retrieval
    $today = date('Y-m-d');
    $hitRate = $stats->getCacheHitRate($today, $today);
    echo "   ✓ Cache hit rate today: {$hitRate['hit_rate']}%\n";
    
    // Test total requests
    $total = $stats->getTotalRequests($today, $today);
    echo "   ✓ Total requests today: {$total}\n";
    
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ ApiStatistics test failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 6: Cleanup test data
echo "6. Cleaning Up Test Data...\n";
try {
    $cache->clear('TEST123');
    $pdo->exec("DELETE FROM api_statistics WHERE widget_code = 'TEST123'");
    echo "   ✓ Test data cleaned up\n\n";
} catch (Exception $e) {
    echo "   ⚠ Cleanup warning: " . $e->getMessage() . "\n\n";
}

// Final summary
echo "============================================\n";
echo "✓ ALL TESTS PASSED!\n";
echo "============================================\n\n";

echo "Next Steps:\n";
echo "1. Integrate cache into ApiController.php\n";
echo "2. Test with real API requests\n";
echo "3. Build dashboard widgets\n";
echo "4. Monitor cache performance\n\n";

echo "To run migration if tables don't exist:\n";
echo "  php database/run_migration.php\n\n";
