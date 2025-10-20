<?php

namespace Core;

/**
 * XSS Protection Class
 */
class XssProtection
{
    /**
     * Dangerous HTML tags
     */
    private static $dangerousTags = [
        'script', 'iframe', 'object', 'embed', 'form', 'input', 'textarea',
        'button', 'select', 'option', 'link', 'meta', 'style', 'base',
        'applet', 'body', 'html', 'head', 'title'
    ];
    
    /**
     * Dangerous attributes
     */
    private static $dangerousAttributes = [
        'onload', 'onerror', 'onclick', 'onmouseover', 'onmouseout',
        'onkeydown', 'onkeyup', 'onkeypress', 'onfocus', 'onblur',
        'onchange', 'onsubmit', 'onreset', 'onselect', 'onresize',
        'onscroll', 'ondblclick', 'oncontextmenu', 'onwheel',
        'javascript', 'vbscript', 'data', 'livescript', 'mocha'
    ];
    
    /**
     * Clean HTML content
     */
    public static function clean($input, $allowedTags = [])
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Remove dangerous tags
        foreach (self::$dangerousTags as $tag) {
            $input = preg_replace("/<\/?{$tag}[^>]*>/i", '', $input);
        }
        
        // Remove dangerous attributes
        foreach (self::$dangerousAttributes as $attr) {
            $input = preg_replace("/{$attr}\s*=\s*[\"'][^\"']*[\"']/i", '', $input);
            $input = preg_replace("/{$attr}\s*=\s*[^\s>]+/i", '', $input);
        }
        
        // Remove javascript: and data: protocols
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/vbscript:/i', '', $input);
        $input = preg_replace('/data:/i', '', $input);
        
        // Remove CSS expressions
        $input = preg_replace('/expression\s*\(/i', '', $input);
        
        // If allowed tags specified, strip all others
        if (!empty($allowedTags)) {
            $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
            $input = strip_tags($input, $allowedTagsString);
        }
        
        return $input;
    }
    
    /**
     * Escape HTML entities
     */
    public static function escape($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Clean for display in HTML attributes
     */
    public static function attribute($input)
    {
        if (!is_string($input)) {
            return '';
        }
        
        // Remove quotes and dangerous characters
        $input = str_replace(['"', "'", '<', '>', '&'], '', $input);
        
        // Remove javascript: and data: protocols
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/data:/i', '', $input);
        
        return trim($input);
    }
    
    /**
     * Clean URL for href attributes
     */
    public static function url($url)
    {
        if (!is_string($url)) {
            return '#';
        }
        
        // Remove dangerous protocols
        $dangerousProtocols = ['javascript:', 'vbscript:', 'data:', 'file:', 'ftp:'];
        
        foreach ($dangerousProtocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return '#';
            }
        }
        
        // Allow only http, https, mailto, tel
        if (!preg_match('/^(https?:|mailto:|tel:|#|\/)/i', $url)) {
            return '#';
        }
        
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * Clean CSS content
     */
    public static function css($css)
    {
        if (!is_string($css)) {
            return '';
        }
        
        // Remove dangerous CSS functions
        $dangerousFunctions = [
            'expression', 'javascript', 'vbscript', 'import', 'url',
            'behavior', 'binding', '-moz-binding'
        ];
        
        foreach ($dangerousFunctions as $func) {
            $css = preg_replace("/{$func}\s*\(/i", '', $css);
        }
        
        // Remove comments that might contain code
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        return $css;
    }
    
    /**
     * Detect XSS attempts
     */
    public static function detectXss($input)
    {
        if (!is_string($input)) {
            return false;
        }
        
        $input = strtolower($input);
        
        // Check for script tags
        if (preg_match('/<script/i', $input)) {
            return true;
        }
        
        // Check for javascript: protocol
        if (preg_match('/javascript:/i', $input)) {
            return true;
        }
        
        // Check for event handlers
        if (preg_match('/\s*on\w+\s*=/i', $input)) {
            return true;
        }
        
        // Check for data: protocol with base64
        if (preg_match('/data:.*base64/i', $input)) {
            return true;
        }
        
        // Check for CSS expressions
        if (preg_match('/expression\s*\(/i', $input)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Log XSS attempts
     */
    public static function logXssAttempt($input, $userInfo = [])
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'input' => $input,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_info' => $userInfo,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../storage/logs/xss_attempts.log';
        $logEntry = json_encode($logData) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        error_log("XSS Attempt: " . substr($input, 0, 100) . " from IP: " . ($logData['ip']));
    }
    
    /**
     * Middleware to check all inputs for XSS
     */
    public static function checkAllInputs()
    {
        $allInputs = array_merge($_GET, $_POST);
        
        foreach ($allInputs as $key => $value) {
            if (is_string($value) && self::detectXss($value)) {
                self::logXssAttempt($value, [
                    'input_type' => 'form_data',
                    'field_name' => $key
                ]);
                
                // Block the request
                http_response_code(403);
                die('Forbidden: Potential XSS attack detected');
            }
        }
    }
    
    /**
     * Clean array recursively
     */
    public static function cleanArray($array, $allowedTags = [])
    {
        if (!is_array($array)) {
            return self::clean($array, $allowedTags);
        }
        
        $cleaned = [];
        
        foreach ($array as $key => $value) {
            $cleanKey = self::escape($key);
            
            if (is_array($value)) {
                $cleaned[$cleanKey] = self::cleanArray($value, $allowedTags);
            } else {
                $cleaned[$cleanKey] = self::clean($value, $allowedTags);
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Generate Content Security Policy nonce
     */
    public static function generateNonce()
    {
        return base64_encode(random_bytes(16));
    }
    
    /**
     * Safe JSON encode for HTML context
     */
    public static function jsonEncode($data)
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        
        // Additional escaping for HTML context
        $json = str_replace(['<', '>'], ['\u003C', '\u003E'], $json);
        
        return $json;
    }
    
    /**
     * Whitelist-based HTML cleaner
     */
    public static function whitelist($input, $config = [])
    {
        $defaultConfig = [
            'allowed_tags' => ['p', 'br', 'strong', 'em', 'u', 'a'],
            'allowed_attributes' => ['href', 'title', 'alt'],
            'allowed_protocols' => ['http', 'https', 'mailto']
        ];
        
        $config = array_merge($defaultConfig, $config);
        
        if (!is_string($input)) {
            return $input;
        }
        
        // Parse HTML
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $input, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        
        // Remove disallowed tags
        $allTags = $xpath->query('//body//*');
        foreach ($allTags as $tag) {
            if (!in_array(strtolower($tag->nodeName), $config['allowed_tags'])) {
                $tag->parentNode->removeChild($tag);
            }
        }
        
        // Remove disallowed attributes
        $allElements = $xpath->query('//body//*[@*]');
        foreach ($allElements as $element) {
            $attributes = [];
            foreach ($element->attributes as $attr) {
                $attributes[] = $attr;
            }
            
            foreach ($attributes as $attr) {
                if (!in_array(strtolower($attr->name), $config['allowed_attributes'])) {
                    $element->removeAttribute($attr->name);
                }
            }
        }
        
        // Clean URLs
        $links = $xpath->query('//body//a[@href]');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $cleanHref = self::url($href);
            $link->setAttribute('href', $cleanHref);
        }
        
        return $dom->saveHTML();
    }
}
