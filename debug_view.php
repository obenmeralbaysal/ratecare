<?php
/**
 * Debug View Rendering
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
require_once APP_ROOT . '/app/Helpers/functions.php';

// Initialize database
$db = \Core\Database::getInstance();
$db->connect();

echo "<h1>View Debug</h1>";
echo "<pre>";

try {
    $view = \Core\View::getInstance();
    
    echo "Rendering view...\n\n";
    
    // Capture output
    ob_start();
    $output = $view->render('admin/cache/statistics', []);
    ob_end_clean();
    
    echo "Output length: " . strlen($output) . " bytes\n\n";
    
    // Check for specific elements
    $checks = [
        'DOCTYPE' => strpos($output, '<!DOCTYPE') !== false,
        'overallHitRate' => strpos($output, 'id="overallHitRate"') !== false,
        'jQuery' => strpos($output, 'jquery') !== false,
        'Chart.js' => strpos($output, 'chart.js') !== false,
        'loadStatistics' => strpos($output, 'function loadStatistics') !== false,
    ];
    
    echo "Content checks:\n";
    foreach ($checks as $name => $found) {
        echo "  " . ($found ? '✓' : '✗') . " {$name}\n";
    }
    
    echo "\nFirst 500 chars:\n";
    echo substr($output, 0, 500) . "\n\n";
    
    echo "Last 500 chars:\n";
    echo substr($output, -500) . "\n\n";
    
    // Now actually output the view
    echo "</pre>";
    echo "<hr>";
    echo "<h2>Actual Rendered View:</h2>";
    echo $output;
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
