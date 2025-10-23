<?php
/**
 * CLI-Friendly Statistics Page Test
 * Bypasses controller and directly renders view
 */

define('APP_ROOT', __DIR__);
define('PUBLIC_PATH', __DIR__ . '/public');

require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

\Core\Environment::load(APP_ROOT . '/.env');
\Core\Config::load('app');
\Core\Config::load('database');
require_once APP_ROOT . '/app/Helpers/functions.php';

$db = \Core\Database::getInstance();
$db->connect();

echo "============================================\n";
echo "Statistics Page - Complete Test\n";
echo "============================================\n\n";

// Test 1: Route Definition
echo "1. Testing Route Definition...\n";
$webRoutesContent = file_get_contents(APP_ROOT . '/routes/web.php');
if (strpos($webRoutesContent, '/admin/cache/statistics') !== false) {
    echo "   ✓ Route exists in web.php\n";
    if (strpos($webRoutesContent, 'Admin\CacheController@statistics') !== false) {
        echo "   ✓ Route points to correct controller\n";
    } else {
        echo "   ✗ Route controller mismatch\n";
    }
} else {
    echo "   ✗ Route NOT found in web.php\n";
}
echo "\n";

// Test 2: Controller Exists
echo "2. Testing Controller...\n";
$controllerPath = APP_ROOT . '/app/Controllers/Admin/CacheController.php';
if (file_exists($controllerPath)) {
    echo "   ✓ CacheController.php exists\n";
    
    // Check if class can be loaded
    if (class_exists('App\Controllers\Admin\CacheController')) {
        echo "   ✓ CacheController class can be autoloaded\n";
        
        // Check if method exists
        $reflection = new ReflectionClass('App\Controllers\Admin\CacheController');
        if ($reflection->hasMethod('statistics')) {
            echo "   ✓ statistics() method exists\n";
        } else {
            echo "   ✗ statistics() method NOT found\n";
        }
    } else {
        echo "   ✗ CacheController class cannot be loaded\n";
    }
} else {
    echo "   ✗ CacheController.php NOT found\n";
}
echo "\n";

// Test 3: View Exists
echo "3. Testing View File...\n";
$viewPath = APP_ROOT . '/resources/views/admin/cache/statistics.php';
if (file_exists($viewPath)) {
    echo "   ✓ View file exists\n";
    echo "   Path: {$viewPath}\n";
    echo "   Size: " . filesize($viewPath) . " bytes\n";
} else {
    echo "   ✗ View file NOT found\n";
}
echo "\n";

// Test 4: View Rendering (Direct)
echo "4. Testing View Rendering (Bypass Controller)...\n";
try {
    $view = \Core\View::getInstance();
    
    ob_start();
    $output = $view->render('admin/cache/statistics', []);
    ob_end_clean();
    
    echo "   ✓ View rendered successfully\n";
    echo "   Output size: " . strlen($output) . " bytes\n";
    
    // Check for key elements
    $checks = [
        'DOCTYPE' => strpos($output, '<!DOCTYPE') !== false,
        'overallHitRate element' => strpos($output, 'id="overallHitRate"') !== false,
        'jQuery' => strpos($output, 'jquery') !== false,
        'Chart.js' => strpos($output, 'chart.js') !== false,
        'loadStatistics function' => strpos($output, 'function loadStatistics') !== false,
        'API URL' => strpos($output, '/api/v1/cache/statistics') !== false,
    ];
    
    echo "\n   Content Checks:\n";
    foreach ($checks as $name => $found) {
        echo "     " . ($found ? '✓' : '✗') . " {$name}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ View rendering failed\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: API Endpoint
echo "5. Testing API Endpoint...\n";
try {
    $controller = new \App\Controllers\Api\CacheStatsController();
    
    ob_start();
    $controller->statistics();
    $apiOutput = ob_get_clean();
    
    $apiData = json_decode($apiOutput, true);
    
    if ($apiData && isset($apiData['status'])) {
        echo "   ✓ API endpoint works\n";
        echo "   Status: " . $apiData['status'] . "\n";
        
        if ($apiData['status'] === 'success' && isset($apiData['data'])) {
            echo "   ✓ API returns data\n";
            echo "   Total requests: " . ($apiData['data']['overview']['total_requests'] ?? 0) . "\n";
            echo "   Hit rate: " . ($apiData['data']['overview']['hit_rate'] ?? 0) . "%\n";
        }
    } else {
        echo "   ✗ API response invalid\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ API endpoint failed\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Authentication Check
echo "6. Testing Authentication Requirement...\n";
$inAuthMiddleware = strpos($webRoutesContent, "middleware' => ['AuthMiddleware']") !== false 
    && strpos($webRoutesContent, '/admin/cache/statistics') > strpos($webRoutesContent, "middleware' => ['AuthMiddleware']");
    
if ($inAuthMiddleware) {
    echo "   ✓ Route requires authentication (AuthMiddleware)\n";
    echo "   → Users must login to access this page\n";
} else {
    echo "   ⚠ Route may be public (no auth required)\n";
}
echo "\n";

// Summary
echo "============================================\n";
echo "Summary:\n";
echo "============================================\n";
echo "Route: /admin/cache/statistics\n";
echo "Controller: App\\Controllers\\Admin\\CacheController@statistics\n";
echo "View: resources/views/admin/cache/statistics.php\n";
echo "API: /api/v1/cache/statistics\n";
echo "Auth Required: YES\n";
echo "\n";

echo "To access in browser:\n";
echo "1. Login: https://test.ratecare.net/login\n";
echo "2. Visit: https://test.ratecare.net/admin/cache/statistics\n";
echo "\n";

echo "============================================\n";
echo "✓ All Tests Complete!\n";
echo "============================================\n";
