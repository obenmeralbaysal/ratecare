<?php

/**
 * Test Runner Script
 * Usage: php tests/run_tests.php [test_name]
 */

// Set up autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
}

// Include test classes
require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/AuthTest.php';
require_once __DIR__ . '/DatabaseTest.php';
require_once __DIR__ . '/ApiTest.php';

use Tests\AuthTest;
use Tests\DatabaseTest;
use Tests\ApiTest;

// Get command line arguments
$testName = $argv[1] ?? 'all';

echo "🚀 Hotel DigiLab Test Suite\n";
echo "==========================\n\n";

// Run specific test or all tests
switch (strtolower($testName)) {
    case 'auth':
        echo "Running Authentication Tests...\n\n";
        AuthTest::run();
        break;
        
    case 'database':
    case 'db':
        echo "Running Database Tests...\n\n";
        DatabaseTest::run();
        break;
        
    case 'api':
        echo "Running API Tests...\n\n";
        ApiTest::run();
        break;
        
    case 'all':
    default:
        echo "Running All Tests...\n\n";
        
        echo "1️⃣ Authentication Tests\n";
        echo str_repeat("-", 30) . "\n";
        AuthTest::run();
        
        echo "\n2️⃣ Database Tests\n";
        echo str_repeat("-", 30) . "\n";
        DatabaseTest::run();
        
        echo "\n3️⃣ API Tests\n";
        echo str_repeat("-", 30) . "\n";
        ApiTest::run();
        
        echo "\n🏁 All test suites completed!\n";
        break;
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 Usage: php tests/run_tests.php [auth|database|api|all]\n";
echo "📝 Add more tests in the /tests directory\n";
echo "🔧 Configure test database in .env file\n";
