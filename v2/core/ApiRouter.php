<?php

namespace Core;

/**
 * API Router System
 */
class ApiRouter
{
    private static $routes = [];
    private static $middlewares = [];
    private static $prefix = '';
    private static $version = 'v1';
    
    /**
     * Set API version
     */
    public static function version($version)
    {
        self::$version = $version;
        return new static();
    }
    
    /**
     * Set route prefix
     */
    public static function prefix($prefix)
    {
        self::$prefix = $prefix;
        return new static();
    }
    
    /**
     * Add middleware to all routes
     */
    public static function middleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        self::$middlewares = array_merge(self::$middlewares, $middleware);
        return new static();
    }
    
    /**
     * Group routes
     */
    public static function group($attributes, $callback)
    {
        $previousPrefix = self::$prefix;
        $previousMiddlewares = self::$middlewares;
        
        // Apply group attributes
        if (isset($attributes['prefix'])) {
            self::$prefix = trim($previousPrefix . '/' . $attributes['prefix'], '/');
        }
        
        if (isset($attributes['middleware'])) {
            $middleware = is_string($attributes['middleware']) ? [$attributes['middleware']] : $attributes['middleware'];
            self::$middlewares = array_merge(self::$middlewares, $middleware);
        }
        
        // Execute callback
        $callback();
        
        // Restore previous state
        self::$prefix = $previousPrefix;
        self::$middlewares = $previousMiddlewares;
    }
    
    /**
     * Add GET route
     */
    public static function get($path, $handler)
    {
        return self::addRoute('GET', $path, $handler);
    }
    
    /**
     * Add POST route
     */
    public static function post($path, $handler)
    {
        return self::addRoute('POST', $path, $handler);
    }
    
    /**
     * Add PUT route
     */
    public static function put($path, $handler)
    {
        return self::addRoute('PUT', $path, $handler);
    }
    
    /**
     * Add PATCH route
     */
    public static function patch($path, $handler)
    {
        return self::addRoute('PATCH', $path, $handler);
    }
    
    /**
     * Add DELETE route
     */
    public static function delete($path, $handler)
    {
        return self::addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add OPTIONS route
     */
    public static function options($path, $handler)
    {
        return self::addRoute('OPTIONS', $path, $handler);
    }
    
    /**
     * Add resource routes
     */
    public static function resource($name, $controller)
    {
        $routes = [
            ['GET', $name, $controller . '@index'],
            ['POST', $name, $controller . '@store'],
            ['GET', $name . '/{id}', $controller . '@show'],
            ['PUT', $name . '/{id}', $controller . '@update'],
            ['PATCH', $name . '/{id}', $controller . '@update'],
            ['DELETE', $name . '/{id}', $controller . '@destroy']
        ];
        
        foreach ($routes as $route) {
            self::addRoute($route[0], $route[1], $route[2]);
        }
    }
    
    /**
     * Add route
     */
    private static function addRoute($method, $path, $handler)
    {
        $fullPath = '/api/' . self::$version;
        
        if (self::$prefix) {
            $fullPath .= '/' . self::$prefix;
        }
        
        $fullPath .= '/' . ltrim($path, '/');
        $fullPath = rtrim($fullPath, '/');
        
        self::$routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => self::$middlewares
        ];
        
        return new static();
    }
    
    /**
     * Handle API request
     */
    public static function handle()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Handle preflight OPTIONS requests
        if ($method === 'OPTIONS') {
            self::handleCors();
            return;
        }
        
        // Set JSON response headers
        header('Content-Type: application/json');
        self::handleCors();
        
        try {
            $route = self::findRoute($method, $path);
            
            if (!$route) {
                self::jsonResponse(['error' => 'Route not found'], 404);
                return;
            }
            
            // Run middlewares
            foreach ($route['middlewares'] as $middleware) {
                $result = self::runMiddleware($middleware);
                if ($result !== true) {
                    return;
                }
            }
            
            // Parse parameters
            $params = self::parseParameters($route['path'], $path);
            
            // Execute handler
            $response = self::executeHandler($route['handler'], $params);
            
            if (is_array($response) || is_object($response)) {
                self::jsonResponse($response);
            } else {
                echo $response;
            }
            
        } catch (\Exception $e) {
            self::jsonResponse([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Find matching route
     */
    private static function findRoute($method, $path)
    {
        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::matchPath($route['path'], $path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Match path with parameters
     */
    private static function matchPath($routePath, $requestPath)
    {
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        return preg_match($routePattern, $requestPath);
    }
    
    /**
     * Parse route parameters
     */
    private static function parseParameters($routePath, $requestPath)
    {
        $params = [];
        
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        if (preg_match($routePattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
        }
        
        return $params;
    }
    
    /**
     * Execute route handler
     */
    private static function executeHandler($handler, $params)
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($class, $method) = explode('@', $handler);
            
            $controllerClass = "App\\Controllers\\Api\\{$class}";
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller {$controllerClass} not found");
            }
            
            $controller = new $controllerClass();
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in {$controllerClass}");
            }
            
            return $controller->$method($params);
        }
        
        if (is_callable($handler)) {
            return $handler($params);
        }
        
        throw new \Exception("Invalid route handler");
    }
    
    /**
     * Run middleware
     */
    private static function runMiddleware($middleware)
    {
        switch ($middleware) {
            case 'auth':
                return self::authMiddleware();
            case 'admin':
                return self::adminMiddleware();
            case 'throttle':
                return self::throttleMiddleware();
            default:
                return true;
        }
    }
    
    /**
     * Authentication middleware
     */
    private static function authMiddleware()
    {
        $token = self::getBearerToken();
        
        if (!$token) {
            self::jsonResponse(['error' => 'Authentication required'], 401);
            return false;
        }
        
        // Validate token (implement your token validation logic)
        $user = self::validateToken($token);
        
        if (!$user) {
            self::jsonResponse(['error' => 'Invalid token'], 401);
            return false;
        }
        
        // Set authenticated user
        $_SESSION['api_user'] = $user;
        
        return true;
    }
    
    /**
     * Admin middleware
     */
    private static function adminMiddleware()
    {
        if (!isset($_SESSION['api_user'])) {
            self::jsonResponse(['error' => 'Authentication required'], 401);
            return false;
        }
        
        $user = $_SESSION['api_user'];
        
        if (!$user['is_admin']) {
            self::jsonResponse(['error' => 'Admin access required'], 403);
            return false;
        }
        
        return true;
    }
    
    /**
     * Rate limiting middleware
     */
    private static function throttleMiddleware()
    {
        $ip = get_client_ip();
        $key = "api_throttle_{$ip}";
        
        $cache = Cache::getInstance();
        $requests = $cache->get($key, 0);
        
        if ($requests >= 60) { // 60 requests per minute
            self::jsonResponse(['error' => 'Rate limit exceeded'], 429);
            return false;
        }
        
        $cache->set($key, $requests + 1, 60);
        
        return true;
    }
    
    /**
     * Get Bearer token from header
     */
    private static function getBearerToken()
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Validate API token
     */
    private static function validateToken($token)
    {
        // Simple token validation - implement proper JWT or database validation
        $db = Database::getInstance();
        
        $user = $db->selectOne(
            "SELECT * FROM users WHERE api_token = ? AND is_active = 1",
            [$token]
        );
        
        return $user;
    }
    
    /**
     * Handle CORS
     */
    private static function handleCors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Send JSON response
     */
    private static function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Get request data
     */
    public static function getRequestData()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $_POST;
        }
        
        return $data ?: [];
    }
    
    /**
     * Get all routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }
}
