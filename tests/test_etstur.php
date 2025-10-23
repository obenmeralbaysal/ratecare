<?php
/**
 * Test Etstur API Integration
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

use App\Controllers\Api\ApiController;

// Test parameters
$widgetCode = 'YOUR_WIDGET_CODE'; // Widget kodunu buraya yazın
$currency = 'EUR';
$checkin = '2025-10-27';
$checkout = '2025-10-28';

echo "=== ETSTUR API TEST ===\n";
echo "Widget Code: {$widgetCode}\n";
echo "Currency: {$currency}\n";
echo "Check-in: {$checkin}\n";
echo "Check-out: {$checkout}\n\n";

// Simulate API request
$_GET['currency'] = $currency;
$_GET['checkin'] = $checkin;
$_GET['checkout'] = $checkout;
$_GET['adult'] = 2;
$_GET['child'] = 0;
$_GET['infant'] = 0;

try {
    $controller = new ApiController();
    
    // Call the API method
    echo "Calling API...\n\n";
    $controller->getRequest($widgetCode);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Check logs at: app/storage/logs/app.log ===\n";
