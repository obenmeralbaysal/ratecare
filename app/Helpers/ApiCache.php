<?php

namespace App\Helpers;

use Core\Database;
use PDO;

/**
 * API Cache Helper
 * Manages API response caching with partial update support
 */
class ApiCache
{
    private $db;
    private $pdo;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Get cache time from settings (in minutes)
     */
    public function getCacheTime(): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = 'caching-time' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (int)$result['value'] : 30; // Default 30 minutes
        } catch (\Exception $e) {
            error_log("ApiCache: Error getting cache time - " . $e->getMessage());
            return 30; // Fallback
        }
    }
    
    /**
     * Generate unique cache key from parameters
     */
    public function generateCacheKey(string $widgetCode, array $params): string
    {
        // Normalize parameters
        $currency = $params['currency'] ?? 'TRY';
        $checkin = $params['checkin'] ?? date('Y-m-d');
        $checkout = $params['checkout'] ?? date('Y-m-d', strtotime('+1 day'));
        $adult = $params['adult'] ?? 2;
        $child = $params['child'] ?? 0;
        $infant = $params['infant'] ?? 0;
        
        // Format: api_cache:{widgetCode}:{currency}:{checkin}:{checkout}:{adult}:{child}:{infant}
        return sprintf(
            'api_cache:%s:%s:%s:%s:%d:%d:%d',
            $widgetCode,
            strtoupper($currency),
            $checkin,
            $checkout,
            $adult,
            $child,
            $infant
        );
    }
    
    /**
     * Get cached data
     * @return array|null Returns cached data or null if not found/expired
     */
    public function get(string $cacheKey): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT response_data, expires_at, created_at
                FROM api_cache
                WHERE cache_key = ? AND expires_at > NOW()
                LIMIT 1
            ");
            $stmt->execute([$cacheKey]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Decode JSON response
            $data = json_decode($result['response_data'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("ApiCache: JSON decode error for key {$cacheKey}");
                return null;
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error getting cache - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set cache data
     */
    public function set(string $cacheKey, array $data, string $widgetCode, array $params, ?int $ttlMinutes = null): bool
    {
        try {
            $ttl = $ttlMinutes ?? $this->getCacheTime();
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$ttl} minutes"));
            
            $stmt = $this->pdo->prepare("
                INSERT INTO api_cache (cache_key, widget_code, parameters, response_data, expires_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    response_data = VALUES(response_data),
                    expires_at = VALUES(expires_at),
                    created_at = CURRENT_TIMESTAMP
            ");
            
            return $stmt->execute([
                $cacheKey,
                $widgetCode,
                json_encode($params),
                json_encode($data),
                $expiresAt
            ]);
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error setting cache - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update single platform in cache (Partial Cache Update)
     */
    public function updatePlatformInCache(string $cacheKey, string $platform, array $platformData): bool
    {
        try {
            // Get existing cache
            $cached = $this->get($cacheKey);
            if (!$cached) {
                error_log("ApiCache: Cannot update platform - cache not found for key {$cacheKey}");
                return false;
            }
            
            // Find and update platform in platforms array
            $updated = false;
            if (isset($cached['data']['platforms'])) {
                foreach ($cached['data']['platforms'] as $key => $existingPlatform) {
                    if ($existingPlatform['name'] === $platform) {
                        // Update existing platform
                        $cached['data']['platforms'][$key] = array_merge(
                            $existingPlatform,
                            $platformData
                        );
                        $updated = true;
                        break;
                    }
                }
                
                // If platform doesn't exist in cache, add it
                if (!$updated) {
                    $cached['data']['platforms'][] = $platformData;
                    $updated = true;
                }
            }
            
            if ($updated) {
                // Update cache with merged data
                $stmt = $this->pdo->prepare("
                    UPDATE api_cache
                    SET response_data = ?
                    WHERE cache_key = ?
                ");
                
                return $stmt->execute([
                    json_encode($cached),
                    $cacheKey
                ]);
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error updating platform in cache - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Merge new platform data with existing cache
     */
    public function mergePlatformData(array $existingCache, array $newPlatforms): array
    {
        if (!isset($existingCache['data']['platforms'])) {
            $existingCache['data']['platforms'] = [];
        }
        
        foreach ($newPlatforms as $newPlatform) {
            $found = false;
            
            // Update existing platform
            foreach ($existingCache['data']['platforms'] as $key => $existingPlatform) {
                if ($existingPlatform['name'] === $newPlatform['name']) {
                    // Only update if new data is successful
                    if ($newPlatform['status'] === 'success' && $newPlatform['price'] !== 'NA') {
                        $existingCache['data']['platforms'][$key] = $newPlatform;
                    }
                    $found = true;
                    break;
                }
            }
            
            // Add new platform if not found
            if (!$found) {
                $existingCache['data']['platforms'][] = $newPlatform;
            }
        }
        
        return $existingCache;
    }
    
    /**
     * Extend cache expiry time (for partial updates)
     */
    public function extendCacheExpiry(string $cacheKey, int $additionalMinutes = 10): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE api_cache
                SET expires_at = DATE_ADD(expires_at, INTERVAL ? MINUTE)
                WHERE cache_key = ? AND expires_at > NOW()
            ");
            
            return $stmt->execute([$additionalMinutes, $cacheKey]);
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error extending cache expiry - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if cache is expired
     */
    public function isExpired(string $cacheKey): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM api_cache
                WHERE cache_key = ? AND expires_at > NOW()
            ");
            $stmt->execute([$cacheKey]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] == 0;
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error checking expiry - " . $e->getMessage());
            return true; // Assume expired on error
        }
    }
    
    /**
     * Clear cache for specific widget or all
     */
    public function clear(?string $widgetCode = null): bool
    {
        try {
            if ($widgetCode) {
                $stmt = $this->pdo->prepare("DELETE FROM api_cache WHERE widget_code = ?");
                return $stmt->execute([$widgetCode]);
            } else {
                return $this->pdo->exec("TRUNCATE TABLE api_cache") !== false;
            }
        } catch (\Exception $e) {
            error_log("ApiCache: Error clearing cache - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanExpired(): int
    {
        try {
            $stmt = $this->pdo->exec("DELETE FROM api_cache WHERE expires_at < NOW()");
            return $stmt;
        } catch (\Exception $e) {
            error_log("ApiCache: Error cleaning expired cache - " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_entries,
                    COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_entries,
                    COUNT(CASE WHEN expires_at < NOW() THEN 1 END) as expired_entries
                FROM api_cache
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (\Exception $e) {
            error_log("ApiCache: Error getting stats - " . $e->getMessage());
            return [];
        }
    }
}
