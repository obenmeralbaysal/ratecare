<?php
/**
 * Test Statistics Page
 * Check if view renders correctly
 */

// Define application root
define('APP_ROOT', __DIR__);
define('PUBLIC_PATH', __DIR__ . '/public');

// Load autoloader
require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

// Load environment
\Core\Environment::load(APP_ROOT . '/.env');

// Load config
\Core\Config::load('app');

// Load helper functions
require_once APP_ROOT . '/app/Helpers/functions.php';

echo "Testing Statistics Page...\n";
echo "============================================\n\n";

try {
    // Initialize View
    $view = \Core\View::getInstance();
    
    // Test rendering
    echo "Attempting to render view...\n";
    $output = $view->render('admin/cache/statistics', []);
    
    echo "✓ View rendered successfully!\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
    // Check for PHP errors in output
    if (strpos($output, 'Parse error') !== false || 
        strpos($output, 'Fatal error') !== false ||
        strpos($output, 'Warning:') !== false) {
        echo "\n❌ PHP errors detected in output!\n";
        echo substr($output, 0, 500) . "...\n";
    } else {
        echo "✓ No PHP errors detected\n";
    }
    
    // Check if url() function works
    echo "\nTesting url() function:\n";
    echo "  url('/admin/dashboard'): " . url('/admin/dashboard') . "\n";
    echo "  url('/api/v1/cache/statistics'): " . url('/api/v1/cache/statistics') . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n============================================\n";
echo "Test complete!\n";
