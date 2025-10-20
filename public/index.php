<?php

/**
 * Hotel DigiLab V2 - Framework-less MVC Application
 * Entry Point
 */

// Define application constants
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

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

// Register error handler
\Core\ErrorHandler::register(APP_ROOT . '/storage/logs/');

try {
    // Bootstrap application
    $app = \Core\Application::getInstance();
    $app->bootstrap();
    
    // Run application
    $app->run();
    
} catch (Exception $e) {
    \Core\ErrorHandler::handleException($e);
}
