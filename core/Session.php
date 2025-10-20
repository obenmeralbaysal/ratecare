<?php

namespace Core;

/**
 * Session Management
 */
class Session
{
    private static $instance = null;
    private $started = false;
    
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
     * Start session
     */
    public function start()
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            // Configure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', $this->isSecure() ? 1 : 0);
            
            session_start();
            $this->started = true;
            
            // Regenerate session ID periodically for security
            if (!$this->has('_session_started')) {
                $this->regenerate();
                $this->set('_session_started', time());
            }
        }
        
        return $this;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $this->start();
        $_SESSION[$key] = $value;
        return $this;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key)
    {
        $this->start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public function remove($key)
    {
        $this->start();
        unset($_SESSION[$key]);
        return $this;
    }
    
    /**
     * Get all session data
     */
    public function all()
    {
        $this->start();
        return $_SESSION;
    }
    
    /**
     * Clear all session data
     */
    public function clear()
    {
        $this->start();
        $_SESSION = [];
        return $this;
    }
    
    /**
     * Destroy session
     */
    public function destroy()
    {
        $this->start();
        
        // Clear session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        $this->started = false;
        
        return $this;
    }
    
    /**
     * Regenerate session ID
     */
    public function regenerate($deleteOld = true)
    {
        $this->start();
        session_regenerate_id($deleteOld);
        return $this;
    }
    
    /**
     * Get session ID
     */
    public function getId()
    {
        $this->start();
        return session_id();
    }
    
    /**
     * Set session ID
     */
    public function setId($id)
    {
        if ($this->started) {
            throw new \Exception("Cannot set session ID after session has started");
        }
        
        session_id($id);
        return $this;
    }
    
    /**
     * Flash data (available for next request only)
     */
    public function flash($key, $value = null)
    {
        if ($value === null) {
            // Get flash data
            $flashData = $this->get('_flash', []);
            return $flashData[$key] ?? null;
        } else {
            // Set flash data
            $flashData = $this->get('_flash', []);
            $flashData[$key] = $value;
            $this->set('_flash', $flashData);
        }
        
        return $this;
    }
    
    /**
     * Get and remove flash data
     */
    public function getFlash($key, $default = null)
    {
        $value = $this->flash($key) ?? $default;
        $this->removeFlash($key);
        return $value;
    }
    
    /**
     * Remove flash data
     */
    public function removeFlash($key)
    {
        $flashData = $this->get('_flash', []);
        unset($flashData[$key]);
        $this->set('_flash', $flashData);
        return $this;
    }
    
    /**
     * Clear old flash data
     */
    public function clearOldFlashData()
    {
        $this->remove('_flash');
        return $this;
    }
    
    /**
     * Check if connection is secure
     */
    private function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }
}
