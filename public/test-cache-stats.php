<?php
/**
 * Browser Test for Cache Statistics Page
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

\Core\Environment::load(APP_ROOT . '/.env');
\Core\Config::load('app');

require_once APP_ROOT . '/app/Helpers/functions.php';

echo "<h1>Cache Statistics Page Test</h1>";
echo "<pre>";

try {
    echo "1. Testing CacheController...\n";
    $controller = new \App\Controllers\Admin\CacheController();
    echo "   ✓ Controller instantiated\n\n";
    
    echo "2. Testing url() function...\n";
    echo "   url('/admin/dashboard'): " . url('/admin/dashboard') . "\n";
    echo "   url('/api/v1/cache/statistics'): " . url('/api/v1/cache/statistics') . "\n\n";
    
    echo "3. Testing View rendering...\n";
    $view = \Core\View::getInstance();
    $viewPath = APP_ROOT . '/resources/views/admin/cache/statistics.php';
    echo "   View path: {$viewPath}\n";
    echo "   View exists: " . (file_exists($viewPath) ? 'YES' : 'NO') . "\n\n";
    
    if (file_exists($viewPath)) {
        echo "4. Attempting to render view...\n";
        ob_start();
        $output = $view->render('admin/cache/statistics', []);
        ob_end_clean();
        
        echo "   ✓ View rendered successfully!\n";
        echo "   Output size: " . strlen($output) . " bytes\n\n";
        
        echo "5. ✓ ALL TESTS PASSED!\n\n";
        echo "Now try visiting: " . url('/admin/cache/statistics') . "\n";
    } else {
        echo "   ❌ View file not found!\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li><a href='" . url('/admin/cache/statistics') . "'>Go to Cache Statistics Page</a></li>";
echo "<li><a href='" . url('/admin/dashboard') . "'>Go to Dashboard</a></li>";
echo "<li><a href='" . url('/admin/settings') . "'>Go to Settings</a></li>";
echo "</ol>";
