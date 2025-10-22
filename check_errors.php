<?php
/**
 * Check PHP Error Logs
 */

echo "============================================\n";
echo "Checking PHP Error Logs\n";
echo "============================================\n\n";

$logPaths = [
    APP_ROOT . '/storage/logs/app.log',
    APP_ROOT . '/storage/logs/error.log',
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log',
    ini_get('error_log')
];

foreach ($logPaths as $path) {
    if (file_exists($path)) {
        echo "Found log: {$path}\n";
        echo "Last 20 lines:\n";
        echo str_repeat('-', 60) . "\n";
        
        $lines = file($path);
        $lastLines = array_slice($lines, -20);
        echo implode('', $lastLines);
        echo str_repeat('-', 60) . "\n\n";
    }
}

echo "PHP Settings:\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
