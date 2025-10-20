<?php

namespace Core;

/**
 * Configuration Management System
 */
class Config
{
    private static $config = [];
    
    /**
     * Load configuration from file
     */
    public static function load($file)
    {
        $configPath = __DIR__ . '/../config/' . $file . '.php';
        
        if (file_exists($configPath)) {
            $config = require $configPath;
            self::$config[$file] = $config;
            return $config;
        }
        
        throw new \Exception("Configuration file not found: {$file}");
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = self::$config;
        
        foreach ($keys as $segment) {
            if (isset($config[$segment])) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
    
    /**
     * Set configuration value
     */
    public static function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }
        
        $config = $value;
    }
    
    /**
     * Check if configuration key exists
     */
    public static function has($key)
    {
        $keys = explode('.', $key);
        $config = self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return false;
            }
            $config = $config[$segment];
        }
        
        return true;
    }
    
    /**
     * Get all configuration
     */
    public static function all()
    {
        return self::$config;
    }
}
