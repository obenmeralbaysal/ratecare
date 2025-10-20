<?php

namespace Core;

/**
 * Security Headers Manager
 */
class SecurityHeaders
{
    /**
     * Apply security headers
     */
    public static function apply()
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = self::getContentSecurityPolicy();
        header("Content-Security-Policy: {$csp}");
        
        // Strict Transport Security (HTTPS only)
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Feature Policy / Permissions Policy
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        
        // Remove server information
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    
    /**
     * Get Content Security Policy
     */
    private static function getContentSecurityPolicy()
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $domain = parse_url($baseUrl, PHP_URL_HOST);
        
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: http:",
            "connect-src 'self' https://api.booking.com https://api.expedia.com",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        return implode('; ', $policies);
    }
    
    /**
     * Check if connection is HTTPS
     */
    private static function isHttps()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Generate nonce for inline scripts
     */
    public static function generateNonce()
    {
        return base64_encode(random_bytes(16));
    }
    
    /**
     * Set CORS headers for API
     */
    public static function setCorsHeaders($allowedOrigins = ['*'])
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    /**
     * Security audit check
     */
    public static function auditHeaders()
    {
        $requiredHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Content-Security-Policy',
            'Referrer-Policy'
        ];
        
        $missingHeaders = [];
        
        foreach ($requiredHeaders as $header) {
            if (!self::headerExists($header)) {
                $missingHeaders[] = $header;
            }
        }
        
        return [
            'secure' => empty($missingHeaders),
            'missing_headers' => $missingHeaders,
            'https' => self::isHttps()
        ];
    }
    
    /**
     * Check if header exists
     */
    private static function headerExists($headerName)
    {
        $headers = headers_list();
        foreach ($headers as $header) {
            if (stripos($header, $headerName . ':') === 0) {
                return true;
            }
        }
        return false;
    }
}
