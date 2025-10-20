<?php

namespace Core;

/**
 * HTTP Request Handler
 */
class Request
{
    private static $instance = null;
    private $input = [];
    private $files = [];
    private $headers = [];
    
    private function __construct()
    {
        $this->parseInput();
        $this->parseFiles();
        $this->parseHeaders();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Parse input data
     */
    private function parseInput()
    {
        $this->input = array_merge($_GET, $_POST);
        
        // Parse JSON input for API requests
        if ($this->isJson()) {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json) {
                $this->input = array_merge($this->input, $json);
            }
        }
    }
    
    /**
     * Parse uploaded files
     */
    private function parseFiles()
    {
        $this->files = $_FILES;
    }
    
    /**
     * Parse request headers
     */
    private function parseHeaders()
    {
        $this->headers = getallheaders() ?: [];
    }
    
    /**
     * Get input value
     */
    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->input;
        }
        
        return $this->input[$key] ?? $default;
    }
    
    /**
     * Get all input
     */
    public function all()
    {
        return $this->input;
    }
    
    /**
     * Check if input key exists
     */
    public function has($key)
    {
        return isset($this->input[$key]);
    }
    
    /**
     * Get only specified keys
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return array_intersect_key($this->input, array_flip($keys));
    }
    
    /**
     * Get all except specified keys
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return array_diff_key($this->input, array_flip($keys));
    }
    
    /**
     * Get uploaded file
     */
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Get request method
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Get request URI
     */
    public function uri()
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }
    
    /**
     * Get full URL
     */
    public function url()
    {
        $protocol = $this->isSecure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return "{$protocol}://{$host}{$uri}";
    }
    
    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }
    
    /**
     * Check if request is AJAX
     */
    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if request expects JSON
     */
    public function expectsJson()
    {
        return $this->isAjax() || $this->wantsJson();
    }
    
    /**
     * Check if request wants JSON
     */
    public function wantsJson()
    {
        $acceptable = $this->header('Accept', '');
        return strpos($acceptable, 'application/json') !== false;
    }
    
    /**
     * Check if request is JSON
     */
    public function isJson()
    {
        $contentType = $this->header('Content-Type', '');
        return strpos($contentType, 'application/json') !== false;
    }
    
    /**
     * Get request header
     */
    public function header($key, $default = null)
    {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)));
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Get all headers
     */
    public function headers()
    {
        return $this->headers;
    }
    
    /**
     * Get user IP address
     */
    public function ip()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
               $_SERVER['HTTP_X_REAL_IP'] ?? 
               $_SERVER['REMOTE_ADDR'] ?? 
               '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    public function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Simple validation
     */
    public function validate($rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $ruleSet = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $this->input($field);
            
            foreach ($ruleSet as $rule) {
                $ruleName = $rule;
                $ruleValue = null;
                
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleValue) = explode(':', $rule, 2);
                }
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "The {$field} field is required.";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "The {$field} must be a valid email address.";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = "The {$field} must be at least {$ruleValue} characters.";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = "The {$field} may not be greater than {$ruleValue} characters.";
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "The {$field} must be a number.";
                        }
                        break;
                }
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return true;
    }
}

/**
 * Validation Exception
 */
class ValidationException extends \Exception
{
    private $errors;
    
    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}
