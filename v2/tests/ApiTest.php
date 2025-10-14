<?php

namespace Tests;

use Core\ApiRouter;
use Core\WidgetRenderer;

class ApiTest
{
    public static function run()
    {
        $runner = new TestRunner();
        
        // API Router Tests
        $runner->addTest('API Router - Route Registration', function() {
            // Clear existing routes
            $reflection = new \ReflectionClass(ApiRouter::class);
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routesProperty->setValue([]);
            
            // Add test route
            ApiRouter::get('test', function() { return ['test' => true]; });
            
            $routes = ApiRouter::getRoutes();
            TestRunner::assertTrue(count($routes) > 0, 'Should have registered routes');
            TestRunner::assertEquals('GET', $routes[0]['method'], 'Method should be GET');
            TestRunner::assertTrue(strpos($routes[0]['path'], '/api/') === 0, 'Path should start with /api/');
            
            return true;
        });
        
        $runner->addTest('API Router - Request Data Parsing', function() {
            // Mock JSON input
            $testData = ['name' => 'Test', 'value' => 123];
            
            // This would normally read from php://input
            // For testing, we'll just verify the method exists
            TestRunner::assertTrue(method_exists(ApiRouter::class, 'getRequestData'), 'getRequestData method should exist');
            
            return true;
        });
        
        $runner->addTest('Widget Renderer - Basic Functionality', function() {
            $mockWidget = [
                'id' => 1,
                'name' => 'Test Widget',
                'type' => 'search',
                'settings' => '{"theme":"default"}'
            ];
            
            $mockHotel = [
                'id' => 1,
                'name' => 'Test Hotel',
                'city' => 'Test City',
                'country' => 'Test Country'
            ];
            
            $renderer = new WidgetRenderer($mockWidget, $mockHotel);
            TestRunner::assertInstanceOf(WidgetRenderer::class, $renderer, 'Should create WidgetRenderer instance');
            
            // Test render method exists
            TestRunner::assertTrue(method_exists($renderer, 'render'), 'render method should exist');
            TestRunner::assertTrue(method_exists($renderer, 'getCSS'), 'getCSS method should exist');
            
            return true;
        });
        
        $runner->addTest('Widget Renderer - JSON Output', function() {
            $mockWidget = [
                'id' => 1,
                'name' => 'Test Widget',
                'type' => 'rates',
                'settings' => '{}'
            ];
            
            $renderer = new WidgetRenderer($mockWidget);
            $json = $renderer->renderJson();
            
            TestRunner::assertTrue(is_array($json), 'renderJson should return array');
            TestRunner::assertArrayHasKey('widget', $json, 'Should have widget key');
            TestRunner::assertArrayHasKey('html', $json, 'Should have html key');
            TestRunner::assertArrayHasKey('settings', $json, 'Should have settings key');
            
            return true;
        });
        
        $runner->addTest('Widget Renderer - CSS Generation', function() {
            $mockWidget = [
                'id' => 1,
                'name' => 'Test Widget',
                'type' => 'search',
                'settings' => '{}'
            ];
            
            $renderer = new WidgetRenderer($mockWidget);
            $css = $renderer->getCSS();
            
            TestRunner::assertTrue(is_string($css), 'getCSS should return string');
            TestRunner::assertTrue(strlen($css) > 0, 'CSS should not be empty');
            TestRunner::assertTrue(strpos($css, '.hotel-widget') !== false, 'CSS should contain widget classes');
            
            return true;
        });
        
        $runner->addTest('Widget Renderer - Embed Code', function() {
            $widgetId = 123;
            $embedCode = WidgetRenderer::getEmbedCode($widgetId);
            
            TestRunner::assertTrue(is_string($embedCode), 'Embed code should be string');
            TestRunner::assertTrue(strpos($embedCode, '<iframe') !== false, 'Should contain iframe tag');
            TestRunner::assertTrue(strpos($embedCode, (string)$widgetId) !== false, 'Should contain widget ID');
            
            return true;
        });
        
        // Helper function tests
        $runner->addTest('Helper Functions - Array Utilities', function() {
            // Test array_get with dot notation
            $array = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
            
            TestRunner::assertEquals('John', array_get($array, 'user.name'), 'array_get should work with dot notation');
            TestRunner::assertEquals('default', array_get($array, 'user.age', 'default'), 'array_get should return default for missing key');
            
            return true;
        });
        
        $runner->addTest('Helper Functions - String Utilities', function() {
            // Test str_slug
            TestRunner::assertEquals('hello-world', str_slug('Hello World'), 'str_slug should create slug');
            TestRunner::assertEquals('test-123', str_slug('Test 123!@#'), 'str_slug should handle special chars');
            
            // Test str_limit
            TestRunner::assertEquals('Hello...', str_limit('Hello World', 5), 'str_limit should truncate');
            
            return true;
        });
        
        $runner->addTest('Helper Functions - Validation', function() {
            // Test email validation
            TestRunner::assertTrue(is_email('test@example.com'), 'Valid email should pass');
            TestRunner::assertFalse(is_email('invalid-email'), 'Invalid email should fail');
            
            // Test URL validation
            TestRunner::assertTrue(is_url('https://example.com'), 'Valid URL should pass');
            TestRunner::assertFalse(is_url('not-a-url'), 'Invalid URL should fail');
            
            return true;
        });
        
        $runner->run();
    }
}
