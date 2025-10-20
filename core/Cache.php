<?php

namespace Core;

/**
 * Simple File-based Cache System
 */
class Cache
{
    private static $instance = null;
    private $cachePath;
    private $defaultTtl = 3600; // 1 hour
    
    private function __construct()
    {
        $this->cachePath = __DIR__ . '/../storage/cache/';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set cache path
     */
    public function setCachePath($path)
    {
        $this->cachePath = rtrim($path, '/') . '/';
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        
        return $this;
    }
    
    /**
     * Set default TTL
     */
    public function setDefaultTtl($ttl)
    {
        $this->defaultTtl = $ttl;
        return $this;
    }
    
    /**
     * Get cache item
     */
    public function get($key, $default = null)
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }
        
        $data = file_get_contents($filePath);
        $item = unserialize($data);
        
        // Check if expired
        if ($item['expires'] > 0 && time() > $item['expires']) {
            $this->delete($key);
            return $default;
        }
        
        return $item['data'];
    }
    
    /**
     * Set cache item
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?: $this->defaultTtl;
        $expires = $ttl > 0 ? time() + $ttl : 0;
        
        $item = [
            'data' => $value,
            'expires' => $expires,
            'created' => time()
        ];
        
        $filePath = $this->getFilePath($key);
        $dir = dirname($filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filePath, serialize($item)) !== false;
    }
    
    /**
     * Check if cache item exists and is valid
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Delete cache item
     */
    public function delete($key)
    {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear()
    {
        return $this->deleteDirectory($this->cachePath);
    }
    
    /**
     * Get or set cache item
     */
    public function remember($key, $callback, $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Get cache item and delete it
     */
    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->delete($key);
        return $value;
    }
    
    /**
     * Increment cache value
     */
    public function increment($key, $value = 1)
    {
        $current = $this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }
    
    /**
     * Decrement cache value
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, -$value);
    }
    
    /**
     * Get multiple cache items
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        
        return $result;
    }
    
    /**
     * Set multiple cache items
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        
        return true;
    }
    
    /**
     * Delete multiple cache items
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        
        return true;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats()
    {
        $files = $this->getCacheFiles();
        $totalSize = 0;
        $expiredCount = 0;
        $validCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            $item = unserialize($data);
            
            if ($item['expires'] > 0 && time() > $item['expires']) {
                $expiredCount++;
            } else {
                $validCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_items' => $validCount,
            'expired_items' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize)
        ];
    }
    
    /**
     * Clean expired cache items
     */
    public function cleanExpired()
    {
        $files = $this->getCacheFiles();
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $item = unserialize($data);
            
            if ($item['expires'] > 0 && time() > $item['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get file path for cache key
     */
    private function getFilePath($key)
    {
        $hash = md5($key);
        $dir = substr($hash, 0, 2);
        return $this->cachePath . $dir . '/' . $hash . '.cache';
    }
    
    /**
     * Get all cache files
     */
    private function getCacheFiles()
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cachePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Static methods for convenience
     */
    public static function get_static($key, $default = null)
    {
        return self::getInstance()->get($key, $default);
    }
    
    public static function set_static($key, $value, $ttl = null)
    {
        return self::getInstance()->set($key, $value, $ttl);
    }
    
    public static function remember_static($key, $callback, $ttl = null)
    {
        return self::getInstance()->remember($key, $callback, $ttl);
    }
    
    public static function delete_static($key)
    {
        return self::getInstance()->delete($key);
    }
    
    public static function clear_static()
    {
        return self::getInstance()->clear();
    }
}
