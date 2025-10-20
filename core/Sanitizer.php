<?php

namespace Core;

/**
 * Input Sanitization Class
 */
class Sanitizer
{
    /**
     * Sanitize string input
     */
    public static function string($input, $options = [])
    {
        if (!is_string($input)) {
            return '';
        }
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Trim whitespace
        if (!isset($options['preserve_whitespace']) || !$options['preserve_whitespace']) {
            $input = trim($input);
        }
        
        // Remove control characters except newlines and tabs
        if (!isset($options['allow_control_chars']) || !$options['allow_control_chars']) {
            $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        }
        
        // HTML encode if requested
        if (isset($options['html_encode']) && $options['html_encode']) {
            $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Sanitize email
     */
    public static function email($email)
    {
        $email = self::string($email);
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize URL
     */
    public static function url($url)
    {
        $url = self::string($url);
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize integer
     */
    public static function int($input, $min = null, $max = null)
    {
        $int = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        $int = (int) $int;
        
        if ($min !== null && $int < $min) {
            $int = $min;
        }
        
        if ($max !== null && $int > $max) {
            $int = $max;
        }
        
        return $int;
    }
    
    /**
     * Sanitize float
     */
    public static function float($input, $min = null, $max = null)
    {
        $float = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $float = (float) $float;
        
        if ($min !== null && $float < $min) {
            $float = $min;
        }
        
        if ($max !== null && $float > $max) {
            $float = $max;
        }
        
        return $float;
    }
    
    /**
     * Sanitize filename
     */
    public static function filename($filename)
    {
        $filename = self::string($filename);
        
        // Remove path separators
        $filename = str_replace(['/', '\\', '..'], '', $filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        $filename = substr($filename, 0, 255);
        
        return $filename;
    }
    
    /**
     * Sanitize HTML content
     */
    public static function html($html, $allowedTags = [])
    {
        if (empty($allowedTags)) {
            // Default allowed tags for rich content
            $allowedTags = [
                'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'a'
            ];
        }
        
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        
        // Strip unwanted tags
        $html = strip_tags($html, $allowedTagsString);
        
        // Remove dangerous attributes
        $html = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|vbscript:|data:)[^>]*>/i', '$1>', $html);
        
        return $html;
    }
    
    /**
     * Sanitize SQL input (for dynamic queries)
     */
    public static function sql($input)
    {
        $input = self::string($input);
        
        // Remove SQL injection patterns
        $patterns = [
            '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)(\s|$)/i',
            '/(\s|^)(or|and)(\s|$)(\d+(\s|$)=(\s|$)\d+|\'\w*\'(\s|$)=(\s|$)\'\w*\')/i',
            '/[\'";\\\\]/',
            '/\/\*.*?\*\//',
            '/--.*$/',
            '/#.*$/'
        ];
        
        foreach ($patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }
        
        return $input;
    }
    
    /**
     * Sanitize array recursively
     */
    public static function array($array, $type = 'string', $options = [])
    {
        if (!is_array($array)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            $cleanKey = self::string($key);
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = self::array($value, $type, $options);
            } else {
                switch ($type) {
                    case 'int':
                        $sanitized[$cleanKey] = self::int($value);
                        break;
                    case 'float':
                        $sanitized[$cleanKey] = self::float($value);
                        break;
                    case 'email':
                        $sanitized[$cleanKey] = self::email($value);
                        break;
                    case 'url':
                        $sanitized[$cleanKey] = self::url($value);
                        break;
                    case 'html':
                        $sanitized[$cleanKey] = self::html($value, $options['allowed_tags'] ?? []);
                        break;
                    default:
                        $sanitized[$cleanKey] = self::string($value, $options);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize $_POST data
     */
    public static function post($type = 'string', $options = [])
    {
        return self::array($_POST, $type, $options);
    }
    
    /**
     * Sanitize $_GET data
     */
    public static function get($type = 'string', $options = [])
    {
        return self::array($_GET, $type, $options);
    }
    
    /**
     * Sanitize $_REQUEST data
     */
    public static function request($type = 'string', $options = [])
    {
        return self::array($_REQUEST, $type, $options);
    }
    
    /**
     * Remove XSS attempts
     */
    public static function xss($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove script tags
        $input = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $input);
        
        // Remove javascript: and vbscript: protocols
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/vbscript:/i', '', $input);
        
        // Remove on* event handlers
        $input = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        
        // Remove data: URLs (can contain base64 encoded scripts)
        $input = preg_replace('/data:/i', '', $input);
        
        // Remove style attributes that might contain expressions
        $input = preg_replace('/style\s*=\s*["\'][^"\']*expression[^"\']*["\']/i', '', $input);
        
        return $input;
    }
    
    /**
     * Validate and sanitize JSON
     */
    public static function json($input)
    {
        if (!is_string($input)) {
            return null;
        }
        
        $input = self::string($input);
        $decoded = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Sanitize phone number
     */
    public static function phone($phone)
    {
        $phone = self::string($phone);
        
        // Keep only digits, spaces, hyphens, parentheses, and plus sign
        $phone = preg_replace('/[^0-9\s\-\(\)\+]/', '', $phone);
        
        return trim($phone);
    }
    
    /**
     * Sanitize credit card number (for display)
     */
    public static function creditCard($cardNumber, $maskAll = false)
    {
        $cardNumber = preg_replace('/[^0-9]/', '', $cardNumber);
        
        if ($maskAll) {
            return str_repeat('*', strlen($cardNumber));
        }
        
        // Show only last 4 digits
        if (strlen($cardNumber) > 4) {
            return str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
        }
        
        return $cardNumber;
    }
}
