<?php

namespace Tests;

/**
 * Simple Testing Framework
 */
class TestRunner
{
    private $tests = [];
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    
    public function __construct()
    {
        // Initialize test environment
        $this->setupTestEnvironment();
    }
    
    /**
     * Setup test environment
     */
    private function setupTestEnvironment()
    {
        // Set test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        
        // Use test database
        $_ENV['DB_DATABASE'] = 'hotel_digilab_test';
        
        // Disable external services
        $_ENV['MAIL_DRIVER'] = 'log';
        $_ENV['CACHE_DRIVER'] = 'array';
    }
    
    /**
     * Add test case
     */
    public function addTest($name, $callback)
    {
        $this->tests[$name] = $callback;
    }
    
    /**
     * Run all tests
     */
    public function run()
    {
        echo "🧪 Running Tests...\n";
        echo str_repeat("=", 50) . "\n";
        
        foreach ($this->tests as $name => $callback) {
            $this->runTest($name, $callback);
        }
        
        $this->printSummary();
    }
    
    /**
     * Run single test
     */
    private function runTest($name, $callback)
    {
        echo "Testing: {$name}... ";
        
        try {
            $start = microtime(true);
            $result = $callback();
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            if ($result === true) {
                echo "✅ PASSED ({$duration}ms)\n";
                $this->passed++;
                $this->results[$name] = ['status' => 'passed', 'duration' => $duration];
            } else {
                echo "❌ FAILED ({$duration}ms)\n";
                $this->failed++;
                $this->results[$name] = ['status' => 'failed', 'duration' => $duration, 'message' => $result];
            }
        } catch (\Exception $e) {
            echo "💥 ERROR: " . $e->getMessage() . "\n";
            $this->failed++;
            $this->results[$name] = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Print test summary
     */
    private function printSummary()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 Test Summary:\n";
        echo "✅ Passed: {$this->passed}\n";
        echo "❌ Failed: {$this->failed}\n";
        echo "📈 Total: " . ($this->passed + $this->failed) . "\n";
        
        if ($this->failed > 0) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->results as $name => $result) {
                if ($result['status'] !== 'passed') {
                    echo "  - {$name}: " . ($result['message'] ?? 'Unknown error') . "\n";
                }
            }
        }
        
        echo "\n" . ($this->failed === 0 ? "🎉 All tests passed!" : "⚠️  Some tests failed!") . "\n";
    }
    
    /**
     * Assert functions
     */
    public static function assertTrue($condition, $message = 'Assertion failed')
    {
        if (!$condition) {
            throw new \Exception($message);
        }
        return true;
    }
    
    public static function assertFalse($condition, $message = 'Assertion failed')
    {
        if ($condition) {
            throw new \Exception($message);
        }
        return true;
    }
    
    public static function assertEquals($expected, $actual, $message = 'Values are not equal')
    {
        if ($expected !== $actual) {
            throw new \Exception("{$message}. Expected: {$expected}, Actual: {$actual}");
        }
        return true;
    }
    
    public static function assertNotEquals($expected, $actual, $message = 'Values should not be equal')
    {
        if ($expected === $actual) {
            throw new \Exception($message);
        }
        return true;
    }
    
    public static function assertNull($value, $message = 'Value is not null')
    {
        if ($value !== null) {
            throw new \Exception($message);
        }
        return true;
    }
    
    public static function assertNotNull($value, $message = 'Value is null')
    {
        if ($value === null) {
            throw new \Exception($message);
        }
        return true;
    }
    
    public static function assertArrayHasKey($key, $array, $message = 'Array does not have key')
    {
        if (!array_key_exists($key, $array)) {
            throw new \Exception("{$message}: {$key}");
        }
        return true;
    }
    
    public static function assertInstanceOf($expected, $actual, $message = 'Instance type mismatch')
    {
        if (!($actual instanceof $expected)) {
            throw new \Exception($message);
        }
        return true;
    }
}
