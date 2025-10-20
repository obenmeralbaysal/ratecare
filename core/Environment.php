<?php

namespace Core;

/**
 * Environment Variable Handler
 */
class Environment
{
    /**
     * Load environment variables from .env file
     */
    public static function load($path = null)
    {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            return false;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Set environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Set environment variable
     */
    public static function set($key, $value)
    {
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key)
    {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }
}
