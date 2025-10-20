<?php

namespace Core;

/**
 * Router System
 */
class Router
{
    private static $instance = null;
    private $routes = [];
    private $middlewares = [];
    private $currentGroup = [];
    
    private function __construct()
    {
        // Singleton pattern
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Add GET route
     */
    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }
    
    /**
     * Add POST route
     */
    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }
    
    /**
     * Add PUT route
     */
    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }
    
    /**
     * Add route for any method
     */
    public function any($uri, $action)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        foreach ($methods as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }
    
    /**
     * Add route group
     */
    public function group($attributes, $callback)
    {
        $previousGroup = $this->currentGroup;
        
        $this->currentGroup = array_merge($previousGroup, $attributes);
        
        call_user_func($callback, $this);
        
        $this->currentGroup = $previousGroup;
    }
    
    /**
     * Add route
     */
    private function addRoute($method, $uri, $action)
    {
        // Apply group prefix
        if (isset($this->currentGroup['prefix'])) {
            $uri = trim($this->currentGroup['prefix'], '/') . '/' . trim($uri, '/');
        }
        
        // Apply group namespace
        if (isset($this->currentGroup['namespace']) && is_string($action)) {
            $action = $this->currentGroup['namespace'] . '\\' . $action;
        }
        
        // Apply group middleware
        $middleware = [];
        if (isset($this->currentGroup['middleware'])) {
            $middleware = array_merge($middleware, (array) $this->currentGroup['middleware']);
        }
        
        $route = [
            'method' => $method,
            'uri' => '/' . trim($uri, '/'),
            'action' => $action,
            'middleware' => $middleware,
            'parameters' => []
        ];
        
        $this->routes[] = $route;
        
        return $this;
    }
    
    /**
     * Dispatch route
     */
    public function dispatch($method, $uri)
    {
        $uri = '/' . trim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertToRegex($route['uri']);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Extract parameters
                array_shift($matches); // Remove full match
                $route['parameters'] = $matches;
                
                return $this->callAction($route);
            }
        }
        
        // Route not found
        http_response_code(404);
        echo "404 - Route not found";
        return false;
    }
    
    /**
     * Convert route URI to regex pattern
     */
    private function convertToRegex($uri)
    {
        // Replace {param} with regex capture groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $uri);
        
        // Replace {param?} with optional regex capture groups
        $pattern = preg_replace('/\{([^}]+)\?\}/', '([^/]*)', $pattern);
        
        // Escape forward slashes and add delimiters
        $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
        
        return $pattern;
    }
    
    /**
     * Call route action
     */
    private function callAction($route)
    {
        // Run middleware
        foreach ($route['middleware'] as $middleware) {
            $middlewareClass = "App\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                if (method_exists($middlewareInstance, 'handle')) {
                    $result = $middlewareInstance->handle();
                    if ($result === false) {
                        return false; // Middleware blocked the request
                    }
                }
            }
        }
        
        $action = $route['action'];
        
        // Handle closure
        if (is_callable($action)) {
            return call_user_func_array($action, $route['parameters']);
        }
        
        // Handle Controller@method
        if (is_string($action) && strpos($action, '@') !== false) {
            list($controller, $method) = explode('@', $action);
            
            // Add namespace if not present
            if (strpos($controller, 'App\\Controllers\\') !== 0) {
                $controller = "App\\Controllers\\{$controller}";
            }
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func_array([$controllerInstance, $method], $route['parameters']);
                } else {
                    throw new \Exception("Method {$method} not found in {$controller}");
                }
            } else {
                throw new \Exception("Controller {$controller} not found");
            }
        }
        
        throw new \Exception("Invalid route action");
    }
    
    /**
     * Get all routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
