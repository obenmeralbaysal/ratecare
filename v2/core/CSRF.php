<?php

namespace Core;

/**
 * CSRF Protection
 */
class CSRF
{
    private static $instance = null;
    private $session;
    
    private function __construct()
    {
        $this->session = Session::getInstance();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateToken()
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('_csrf_token', $token);
        return $token;
    }
    
    /**
     * Get current CSRF token
     */
    public function getToken()
    {
        $token = $this->session->get('_csrf_token');
        
        if (!$token) {
            $token = $this->generateToken();
        }
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyToken($token)
    {
        $sessionToken = $this->session->get('_csrf_token');
        
        if (!$sessionToken || !$token) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Verify CSRF token from request
     */
    public function verifyRequest()
    {
        $request = Request::getInstance();
        
        // Skip verification for GET, HEAD, OPTIONS requests
        $method = $request->method();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }
        
        // Get token from request
        $token = $request->input('_csrf_token') ?: $request->header('X-CSRF-Token');
        
        return $this->verifyToken($token);
    }
    
    /**
     * Generate CSRF input field
     */
    public function field()
    {
        $token = $this->getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Generate CSRF meta tag
     */
    public function metaTag()
    {
        $token = $this->getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
