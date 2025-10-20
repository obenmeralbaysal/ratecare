<?php

namespace Core;

/**
 * PSR-4 Compatible Autoloader
 */
class Autoloader
{
    private $prefixes = [];
    
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }
    
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        // Normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';
        
        // Normalize the base directory with a trailing separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        
        // Initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }
        
        // Retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            array_push($this->prefixes[$prefix], $baseDir);
        }
    }
    
    public function loadClass($class)
    {
        // The current namespace prefix
        $prefix = $class;
        
        // Work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {
            
            // Retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);
            
            // The rest is the relative class name
            $relativeClass = substr($class, $pos + 1);
            
            // Try to load a mapped file for the prefix and relative class
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            
            // Remove the trailing namespace separator for the next iteration
            $prefix = rtrim($prefix, '\\');
        }
        
        // Never found a mapped file
        return false;
    }
    
    protected function loadMappedFile($prefix, $relativeClass)
    {
        // Are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
        
        // Look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $baseDir) {
            
            // Replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $baseDir
                  . str_replace('\\', '/', $relativeClass)
                  . '.php';
            
            // If the mapped file exists, require it
            if ($this->requireFile($file)) {
                // Yes, we're done
                return $file;
            }
        }
        
        // Never found it
        return false;
    }
    
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
