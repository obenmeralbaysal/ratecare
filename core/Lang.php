<?php

namespace Core;

/**
 * Localization System
 */
class Lang
{
    private static $instance = null;
    private $locale = 'en';
    private $fallbackLocale = 'en';
    private $translations = [];
    private $langPath;
    
    private function __construct()
    {
        $this->langPath = __DIR__ . '/../resources/lang/';
        
        // Create lang directory if it doesn't exist
        if (!is_dir($this->langPath)) {
            mkdir($this->langPath, 0755, true);
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
     * Set current locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    
    /**
     * Get current locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * Set fallback locale
     */
    public function setFallbackLocale($locale)
    {
        $this->fallbackLocale = $locale;
        return $this;
    }
    
    /**
     * Load translations for locale
     */
    public function loadTranslations($locale = null)
    {
        $locale = $locale ?: $this->locale;
        
        if (isset($this->translations[$locale])) {
            return;
        }
        
        $this->translations[$locale] = [];
        $localePath = $this->langPath . $locale . '/';
        
        if (!is_dir($localePath)) {
            return;
        }
        
        $files = glob($localePath . '*.php');
        
        foreach ($files as $file) {
            $key = basename($file, '.php');
            $translations = include $file;
            
            if (is_array($translations)) {
                $this->translations[$locale][$key] = $translations;
            }
        }
    }
    
    /**
     * Get translation
     */
    public function get($key, $replace = [], $locale = null)
    {
        $locale = $locale ?: $this->locale;
        
        // Load translations if not loaded
        $this->loadTranslations($locale);
        
        // Get translation
        $translation = $this->getTranslation($key, $locale);
        
        // Try fallback locale if not found
        if (is_null($translation) && $locale !== $this->fallbackLocale) {
            $this->loadTranslations($this->fallbackLocale);
            $translation = $this->getTranslation($key, $this->fallbackLocale);
        }
        
        // Return key if translation not found
        if (is_null($translation)) {
            return $key;
        }
        
        // Replace placeholders
        return $this->replacePlaceholders($translation, $replace);
    }
    
    /**
     * Get translation from loaded translations
     */
    private function getTranslation($key, $locale)
    {
        $keys = explode('.', $key);
        $translation = $this->translations[$locale] ?? [];
        
        foreach ($keys as $segment) {
            if (!is_array($translation) || !array_key_exists($segment, $translation)) {
                return null;
            }
            $translation = $translation[$segment];
        }
        
        return $translation;
    }
    
    /**
     * Replace placeholders in translation
     */
    private function replacePlaceholders($translation, $replace)
    {
        if (empty($replace)) {
            return $translation;
        }
        
        foreach ($replace as $key => $value) {
            $translation = str_replace(':' . $key, $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Check if translation exists
     */
    public function has($key, $locale = null)
    {
        $locale = $locale ?: $this->locale;
        $this->loadTranslations($locale);
        
        return !is_null($this->getTranslation($key, $locale));
    }
    
    /**
     * Get choice translation (pluralization)
     */
    public function choice($key, $number, $replace = [], $locale = null)
    {
        $translation = $this->get($key, $replace, $locale);
        
        if ($translation === $key) {
            return $translation;
        }
        
        // Simple pluralization logic
        $parts = explode('|', $translation);
        
        if (count($parts) === 1) {
            return $translation;
        }
        
        // Handle different plural forms
        if (count($parts) === 2) {
            return $number === 1 ? $parts[0] : $parts[1];
        }
        
        // More complex pluralization can be added here
        return $parts[0];
    }
    
    /**
     * Add translation at runtime
     */
    public function addTranslation($key, $value, $locale = null)
    {
        $locale = $locale ?: $this->locale;
        
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
        }
        
        $keys = explode('.', $key);
        $translation = &$this->translations[$locale];
        
        foreach ($keys as $segment) {
            if (!isset($translation[$segment])) {
                $translation[$segment] = [];
            }
            $translation = &$translation[$segment];
        }
        
        $translation = $value;
    }
    
    /**
     * Get all translations for locale
     */
    public function all($locale = null)
    {
        $locale = $locale ?: $this->locale;
        $this->loadTranslations($locale);
        
        return $this->translations[$locale] ?? [];
    }
    
    /**
     * Get available locales
     */
    public function getAvailableLocales()
    {
        $locales = [];
        $directories = glob($this->langPath . '*', GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $locales[] = basename($directory);
        }
        
        return $locales;
    }
    
    /**
     * Static methods for convenience
     */
    public static function trans($key, $replace = [], $locale = null)
    {
        return self::getInstance()->get($key, $replace, $locale);
    }
    
    public static function transChoice($key, $number, $replace = [], $locale = null)
    {
        return self::getInstance()->choice($key, $number, $replace, $locale);
    }
    
    public static function setCurrentLocale($locale)
    {
        return self::getInstance()->setLocale($locale);
    }
    
    public static function getCurrentLocale()
    {
        return self::getInstance()->getLocale();
    }
}
