<?php
/**
 * Test Route Directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

echo "<h1>Route Test: /admin/cache/statistics</h1>";
echo "<pre>";

try {
    echo "1. Testing Controller Instantiation...\n";
    $controller = new \App\Controllers\Admin\CacheController();
    echo "   ✓ Controller created\n\n";
    
    echo "2. Testing statistics() method...\n";
    $result = $controller->statistics();
    echo "   ✓ Method executed\n";
    echo "   Result type: " . gettype($result) . "\n";
    echo "   Result length: " . strlen($result) . " bytes\n\n";
    
    echo "3. ✅ ROUTE IS WORKING!\n\n";
    
    echo "4. Now let's check authentication...\n";
    echo "   Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not active') . "\n";
    
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    echo "   Logged in: " . (isset($_SESSION['user_id']) ? 'YES (User ID: ' . $_SESSION['user_id'] . ')' : 'NO') . "\n\n";
    
    if (!isset($_SESSION['user_id'])) {
        echo "❌ NOT LOGGED IN!\n";
        echo "This is why the page redirects to login.\n\n";
        echo "Login at: https://test.ratecare.net/login\n";
    } else {
        echo "✅ LOGGED IN!\n";
        echo "You should be able to access the page.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<h2>Quick Links:</h2>";
echo "<ul>";
echo "<li><a href='https://test.ratecare.net/login'>Login Page</a></li>";
echo "<li><a href='https://test.ratecare.net/admin/cache/statistics'>Cache Statistics (requires login)</a></li>";
echo "<li><a href='https://test.ratecare.net/admin/dashboard'>Dashboard (requires login)</a></li>";
echo "<li><a href='https://test.ratecare.net/debug_view.php'>Debug View (no login)</a></li>";
echo "</ul>";
