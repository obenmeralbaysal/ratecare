<?php

namespace Core;

/**
 * Main Application Class
 * Handles application bootstrapping and request lifecycle
 */
class Application
{
    private static $instance = null;
    private $config = [];
    private $router;
    private $request;
    private $response;
    
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
    
    public function bootstrap()
    {
        // Load configuration
        $this->loadConfiguration();
        
        // Set up error handling
        $this->setupErrorHandling();
        
        // Initialize core components
        $this->initializeComponents();
        
        return $this;
    }
    
    private function loadConfiguration()
    {
        // Will be implemented in next steps
    }
    
    private function setupErrorHandling()
    {
        // Will be implemented in next steps
    }
    
    private function initializeComponents()
    {
        $this->router = Router::getInstance();
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        
        // Start session
        Session::getInstance()->start();
    }
    
    public function run()
    {
        // Load routes
        $this->loadRoutes();
        
        // Dispatch request
        $method = $this->request->method();
        $uri = $this->request->uri();
        
        $this->router->dispatch($method, $uri);
    }
    
    private function loadRoutes()
    {
        // Load web routes
        $webRoutesFile = __DIR__ . '/../routes/web.php';
        if (file_exists($webRoutesFile)) {
            require $webRoutesFile;
        }
        
        // Load API routes
        $apiRoutesFile = __DIR__ . '/../routes/api.php';
        if (file_exists($apiRoutesFile)) {
            require $apiRoutesFile;
            
            // Transfer API routes from ApiRouter to main Router
            $apiRoutes = \Core\ApiRouter::getRoutes();
            foreach ($apiRoutes as $route) {
                $method = strtolower($route['method']);
                $uri = $route['path'];
                $handler = $route['handler'];
                
                // Add API route to main router using public methods
                if (method_exists($this->router, $method)) {
                    $this->router->$method($uri, $handler);
                }
            }
        }
    }
}
