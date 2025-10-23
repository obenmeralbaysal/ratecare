<?php
/**
 * Test Routes Loading
 * Check if API routes are properly loaded
 */

// Define application root
define('APP_ROOT', __DIR__);
define('PUBLIC_PATH', __DIR__ . '/public');

// Load autoloader
require_once APP_ROOT . '/core/Autoloader.php';

// Register autoloader
$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

// Load environment variables
\Core\Environment::load(APP_ROOT . '/.env');

// Load configuration
\Core\Config::load('app');
\Core\Config::load('database');

// Load helper functions
require_once APP_ROOT . '/app/Helpers/functions.php';

echo "============================================\n";
echo "Testing Route Loading\n";
echo "============================================\n\n";

// Load API routes
echo "Loading API routes...\n";
require __DIR__ . '/routes/api.php';

// Get API routes
$apiRoutes = \Core\ApiRouter::getRoutes();

echo "Total API routes loaded: " . count($apiRoutes) . "\n\n";

// Display routes
echo "API Routes:\n";
echo str_repeat('-', 80) . "\n";
foreach ($apiRoutes as $index => $route) {
    echo ($index + 1) . ". ";
    echo str_pad($route['method'], 6) . " ";
    echo $route['path'];
    echo " → " . (is_string($route['handler']) ? $route['handler'] : 'Closure');
    echo "\n";
}

echo "\n";
echo "Looking for cache/summary route:\n";
echo str_repeat('-', 80) . "\n";

$found = false;
foreach ($apiRoutes as $route) {
    if (strpos($route['path'], 'cache/summary') !== false) {
        echo "✓ FOUND!\n";
        echo "  Method: " . $route['method'] . "\n";
        echo "  Path: " . $route['path'] . "\n";
        echo "  Handler: " . $route['handler'] . "\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "✗ NOT FOUND - This is the problem!\n";
}

echo "\n";
echo "Expected URL: https://test.ratecare.net/api/v1/cache/summary\n";
echo "============================================\n";
