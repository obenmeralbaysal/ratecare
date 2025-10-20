<?php

namespace Core;

/**
 * Rate Limiting System
 */
class RateLimiter
{
    private $cache;
    private $defaultLimit = 60;
    private $defaultWindow = 60; // seconds
    
    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }
    
    /**
     * Check if request is within rate limit
     */
    public function attempt($key, $limit = null, $window = null)
    {
        $limit = $limit ?? $this->defaultLimit;
        $window = $window ?? $this->defaultWindow;
        
        $cacheKey = 'rate_limit:' . $key;
        $current = $this->cache->get($cacheKey, 0);
        
        if ($current >= $limit) {
            return false;
        }
        
        $this->cache->set($cacheKey, $current + 1, $window);
        return true;
    }
    
    /**
     * Get remaining attempts
     */
    public function remaining($key, $limit = null)
    {
        $limit = $limit ?? $this->defaultLimit;
        $cacheKey = 'rate_limit:' . $key;
        $current = $this->cache->get($cacheKey, 0);
        
        return max(0, $limit - $current);
    }
    
    /**
     * Get time until reset
     */
    public function resetTime($key)
    {
        $cacheKey = 'rate_limit:' . $key;
        return $this->cache->ttl($cacheKey);
    }
    
    /**
     * Clear rate limit for key
     */
    public function clear($key)
    {
        $cacheKey = 'rate_limit:' . $key;
        $this->cache->delete($cacheKey);
    }
    
    /**
     * Rate limit by IP address
     */
    public function limitByIp($limit = null, $window = null)
    {
        $ip = $this->getClientIp();
        return $this->attempt("ip:{$ip}", $limit, $window);
    }
    
    /**
     * Rate limit by user ID
     */
    public function limitByUser($userId, $limit = null, $window = null)
    {
        return $this->attempt("user:{$userId}", $limit, $window);
    }
    
    /**
     * Rate limit API endpoints
     */
    public function limitApi($endpoint, $identifier, $limit = null, $window = null)
    {
        $key = "api:{$endpoint}:{$identifier}";
        return $this->attempt($key, $limit, $window);
    }
    
    /**
     * Rate limit login attempts
     */
    public function limitLogin($identifier, $limit = 5, $window = 900) // 5 attempts per 15 minutes
    {
        return $this->attempt("login:{$identifier}", $limit, $window);
    }
    
    /**
     * Rate limit password reset attempts
     */
    public function limitPasswordReset($email, $limit = 3, $window = 3600) // 3 attempts per hour
    {
        return $this->attempt("password_reset:{$email}", $limit, $window);
    }
    
    /**
     * Rate limit widget requests
     */
    public function limitWidget($widgetId, $ip, $limit = 100, $window = 3600) // 100 requests per hour
    {
        return $this->attempt("widget:{$widgetId}:{$ip}", $limit, $window);
    }
    
    /**
     * Sliding window rate limiter
     */
    public function slidingWindow($key, $limit, $window)
    {
        $now = time();
        $cacheKey = "sliding:{$key}";
        
        // Get existing timestamps
        $timestamps = $this->cache->get($cacheKey, []);
        
        // Remove old timestamps
        $timestamps = array_filter($timestamps, function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        // Check if limit exceeded
        if (count($timestamps) >= $limit) {
            return false;
        }
        
        // Add current timestamp
        $timestamps[] = $now;
        
        // Save updated timestamps
        $this->cache->set($cacheKey, $timestamps, $window);
        
        return true;
    }
    
    /**
     * Token bucket rate limiter
     */
    public function tokenBucket($key, $capacity, $refillRate, $tokensRequested = 1)
    {
        $now = microtime(true);
        $cacheKey = "bucket:{$key}";
        
        $bucket = $this->cache->get($cacheKey, [
            'tokens' => $capacity,
            'last_refill' => $now
        ]);
        
        // Calculate tokens to add
        $timePassed = $now - $bucket['last_refill'];
        $tokensToAdd = $timePassed * $refillRate;
        
        // Refill bucket
        $bucket['tokens'] = min($capacity, $bucket['tokens'] + $tokensToAdd);
        $bucket['last_refill'] = $now;
        
        // Check if enough tokens
        if ($bucket['tokens'] < $tokensRequested) {
            $this->cache->set($cacheKey, $bucket, 3600);
            return false;
        }
        
        // Consume tokens
        $bucket['tokens'] -= $tokensRequested;
        $this->cache->set($cacheKey, $bucket, 3600);
        
        return true;
    }
    
    /**
     * Adaptive rate limiting based on system load
     */
    public function adaptiveLimit($key, $baseLimit, $window = 60)
    {
        $systemLoad = $this->getSystemLoad();
        
        // Adjust limit based on system load
        if ($systemLoad > 0.8) {
            $adjustedLimit = (int)($baseLimit * 0.5); // Reduce by 50%
        } elseif ($systemLoad > 0.6) {
            $adjustedLimit = (int)($baseLimit * 0.7); // Reduce by 30%
        } else {
            $adjustedLimit = $baseLimit;
        }
        
        return $this->attempt($key, $adjustedLimit, $window);
    }
    
    /**
     * Whitelist IP addresses
     */
    public function isWhitelisted($ip = null)
    {
        $ip = $ip ?? $this->getClientIp();
        
        $whitelist = [
            '127.0.0.1',
            '::1',
            // Add more whitelisted IPs
        ];
        
        return in_array($ip, $whitelist);
    }
    
    /**
     * Blacklist IP addresses
     */
    public function isBlacklisted($ip = null)
    {
        $ip = $ip ?? $this->getClientIp();
        
        $cacheKey = "blacklist:{$ip}";
        return $this->cache->get($cacheKey, false);
    }
    
    /**
     * Add IP to blacklist
     */
    public function blacklistIp($ip, $duration = 3600)
    {
        $cacheKey = "blacklist:{$ip}";
        $this->cache->set($cacheKey, true, $duration);
        
        // Log blacklist action
        $this->logRateLimit('blacklist', $ip, [
            'action' => 'blacklisted',
            'duration' => $duration
        ]);
    }
    
    /**
     * Progressive penalties for repeat offenders
     */
    public function progressivePenalty($key, $baseWindow = 60)
    {
        $penaltyKey = "penalty:{$key}";
        $violations = $this->cache->get($penaltyKey, 0);
        
        // Increase penalty with each violation
        $penaltyMultiplier = min(pow(2, $violations), 32); // Cap at 32x
        $penaltyWindow = $baseWindow * $penaltyMultiplier;
        
        // Increment violations
        $this->cache->set($penaltyKey, $violations + 1, $penaltyWindow);
        
        return $penaltyWindow;
    }
    
    /**
     * Get rate limit headers for HTTP response
     */
    public function getHeaders($key, $limit = null)
    {
        $limit = $limit ?? $this->defaultLimit;
        $remaining = $this->remaining($key, $limit);
        $resetTime = time() + $this->resetTime($key);
        
        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $resetTime
        ];
    }
    
    /**
     * Middleware for automatic rate limiting
     */
    public function middleware($request, $options = [])
    {
        $ip = $this->getClientIp();
        
        // Check if IP is whitelisted
        if ($this->isWhitelisted($ip)) {
            return true;
        }
        
        // Check if IP is blacklisted
        if ($this->isBlacklisted($ip)) {
            http_response_code(429);
            die('Too Many Requests - IP Blacklisted');
        }
        
        $limit = $options['limit'] ?? $this->defaultLimit;
        $window = $options['window'] ?? $this->defaultWindow;
        
        if (!$this->limitByIp($limit, $window)) {
            // Add headers
            $headers = $this->getHeaders("ip:{$ip}", $limit);
            foreach ($headers as $header => $value) {
                header("{$header}: {$value}");
            }
            
            // Log rate limit exceeded
            $this->logRateLimit('exceeded', $ip, [
                'limit' => $limit,
                'window' => $window,
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            
            // Progressive penalty for repeat offenders
            $penaltyWindow = $this->progressivePenalty("ip:{$ip}");
            
            if ($penaltyWindow > 3600) { // If penalty > 1 hour, blacklist temporarily
                $this->blacklistIp($ip, min($penaltyWindow, 86400)); // Max 24 hours
            }
            
            http_response_code(429);
            die('Too Many Requests');
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get system load (simplified)
     */
    private function getSystemLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] / 100; // Normalize to 0-1
        }
        
        return 0.5; // Default moderate load
    }
    
    /**
     * Log rate limiting events
     */
    private function logRateLimit($event, $identifier, $data = [])
    {
        $logData = array_merge([
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'identifier' => $identifier,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ], $data);
        
        $logFile = __DIR__ . '/../storage/logs/rate_limits.log';
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
}
