<?php
/**
 * Test API Statistics Endpoint
 */

define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/core/Autoloader.php';

$autoloader = new \Core\Autoloader();
$autoloader->addNamespace('Core', APP_ROOT . '/core');
$autoloader->addNamespace('App', APP_ROOT . '/app');
$autoloader->register();

\Core\Environment::load(APP_ROOT . '/.env');
require_once APP_ROOT . '/app/Helpers/functions.php';

echo "============================================\n";
echo "Testing API Statistics Endpoint\n";
echo "============================================\n\n";

try {
    // Initialize database
    $db = \Core\Database::getInstance();
    $db->connect();
    echo "✓ Database connected\n\n";
    
    // Test controller directly
    $controller = new \App\Controllers\Api\CacheStatsController();
    
    echo "Calling statistics() method...\n";
    ob_start();
    $result = $controller->statistics();
    $output = ob_get_clean();
    
    echo "Raw output:\n";
    echo $output . "\n\n";
    
    // Parse JSON
    $json = json_decode($output, true);
    
    if ($json && isset($json['status'])) {
        echo "✓ Valid JSON response!\n";
        echo "Status: " . $json['status'] . "\n\n";
        
        if ($json['status'] === 'success' && isset($json['data'])) {
            echo "Data structure:\n";
            print_r($json['data']);
        }
    } else {
        echo "❌ Invalid JSON or no response\n";
        echo "Output: " . substr($output, 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
