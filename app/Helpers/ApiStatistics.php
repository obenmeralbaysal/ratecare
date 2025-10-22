<?php

namespace App\Helpers;

use Core\Database;
use PDO;

/**
 * API Statistics Helper
 * Tracks API requests, cache hits, and platform usage
 */
class ApiStatistics
{
    private $db;
    public $pdo;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Log API request with cache and platform information
     * 
     * @param string $widgetCode Widget identifier
     * @param array $params Request parameters
     * @param string $cacheHitType 'full', 'partial', or 'miss'
     * @param array|null $cachedPlatforms Platforms read from cache
     * @param array|null $requestedPlatforms Platforms requested from APIs
     * @param array|null $updatedPlatforms Platforms updated in cache
     * @param int|null $responseTimeMs Response time in milliseconds
     */
    public function logRequest(
        string $widgetCode,
        array $params,
        string $cacheHitType,
        ?array $cachedPlatforms = null,
        ?array $requestedPlatforms = null,
        ?array $updatedPlatforms = null,
        ?int $responseTimeMs = null
    ): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO api_statistics (
                    widget_code,
                    request_date,
                    request_time,
                    parameters,
                    cache_hit_type,
                    cached_platforms,
                    requested_platforms,
                    updated_platforms,
                    response_time_ms
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $now = time();
            
            return $stmt->execute([
                $widgetCode,
                date('Y-m-d', $now),
                date('H:i:s', $now),
                json_encode($params),
                $cacheHitType,
                $cachedPlatforms ? json_encode($cachedPlatforms) : null,
                $requestedPlatforms ? json_encode($requestedPlatforms) : null,
                $updatedPlatforms ? json_encode($updatedPlatforms) : null,
                $responseTimeMs
            ]);
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error logging request - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache hit rate for date range
     * 
     * @return array ['full_hits' => int, 'partial_hits' => int, 'misses' => int, 'total' => int, 'hit_rate' => float]
     */
    public function getCacheHitRate(string $startDate, string $endDate): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN cache_hit_type = 'full' THEN 1 ELSE 0 END) as full_hits,
                    SUM(CASE WHEN cache_hit_type = 'partial' THEN 1 ELSE 0 END) as partial_hits,
                    SUM(CASE WHEN cache_hit_type = 'miss' THEN 1 ELSE 0 END) as misses
                FROM api_statistics
                WHERE request_date BETWEEN ? AND ?
            ");
            
            $stmt->execute([$startDate, $endDate]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = (int)$result['total'];
            $fullHits = (int)$result['full_hits'];
            $partialHits = (int)$result['partial_hits'];
            
            return [
                'full_hits' => $fullHits,
                'partial_hits' => $partialHits,
                'misses' => (int)$result['misses'],
                'total' => $total,
                'hit_rate' => $total > 0 ? round((($fullHits + $partialHits) / $total) * 100, 2) : 0,
                'full_hit_rate' => $total > 0 ? round(($fullHits / $total) * 100, 2) : 0,
                'partial_hit_rate' => $total > 0 ? round(($partialHits / $total) * 100, 2) : 0
            ];
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting cache hit rate - " . $e->getMessage());
            return [
                'full_hits' => 0,
                'partial_hits' => 0,
                'misses' => 0,
                'total' => 0,
                'hit_rate' => 0,
                'full_hit_rate' => 0,
                'partial_hit_rate' => 0
            ];
        }
    }
    
    /**
     * Get platform usage statistics
     * 
     * @return array Platform names with request counts
     */
    public function getChannelUsage(string $startDate, string $endDate): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT requested_platforms
                FROM api_statistics
                WHERE request_date BETWEEN ? 
                  AND ? 
                  AND requested_platforms IS NOT NULL
            ");
            
            $stmt->execute([$startDate, $endDate]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $usage = [];
            
            foreach ($results as $row) {
                $platforms = json_decode($row['requested_platforms'], true);
                if (is_array($platforms)) {
                    foreach ($platforms as $platform) {
                        if (!isset($usage[$platform])) {
                            $usage[$platform] = 0;
                        }
                        $usage[$platform]++;
                    }
                }
            }
            
            // Sort by usage descending
            arsort($usage);
            
            return $usage;
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting channel usage - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total requests for date range
     */
    public function getTotalRequests(string $startDate, string $endDate): int
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM api_statistics
                WHERE request_date BETWEEN ? AND ?
            ");
            
            $stmt->execute([$startDate, $endDate]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting total requests - " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get daily summary statistics
     */
    public function getDailySummary(string $date): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM api_statistics_summary WHERE date = ? LIMIT 1
            ");
            
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Decode JSON fields
                if ($result['channels_usage']) {
                    $result['channels_usage'] = json_decode($result['channels_usage'], true);
                }
                return $result;
            }
            
            return [];
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting daily summary - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update daily summary (aggregation)
     */
    public function updateDailySummary(string $date): bool
    {
        try {
            // Get statistics for the day
            $hitRate = $this->getCacheHitRate($date, $date);
            $channelUsage = $this->getChannelUsage($date, $date);
            
            // Get average response time
            $stmt = $this->pdo->prepare("
                SELECT AVG(response_time_ms) as avg_time
                FROM api_statistics
                WHERE request_date = ? AND response_time_ms IS NOT NULL
            ");
            $stmt->execute([$date]);
            $avgResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $avgResponseTime = $avgResult ? (int)$avgResult['avg_time'] : null;
            
            // Calculate platform counts
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(JSON_LENGTH(requested_platforms)) as total_requested,
                    SUM(JSON_LENGTH(cached_platforms)) as total_cached
                FROM api_statistics
                WHERE request_date = ?
            ");
            $stmt->execute([$date]);
            $platformCounts = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Insert or update summary
            $stmt = $this->pdo->prepare("
                INSERT INTO api_statistics_summary (
                    date,
                    total_requests,
                    cache_full_hits,
                    cache_partial_hits,
                    cache_misses,
                    total_platforms_requested,
                    total_platforms_from_cache,
                    channels_usage,
                    avg_response_time_ms
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    total_requests = VALUES(total_requests),
                    cache_full_hits = VALUES(cache_full_hits),
                    cache_partial_hits = VALUES(cache_partial_hits),
                    cache_misses = VALUES(cache_misses),
                    total_platforms_requested = VALUES(total_platforms_requested),
                    total_platforms_from_cache = VALUES(total_platforms_from_cache),
                    channels_usage = VALUES(channels_usage),
                    avg_response_time_ms = VALUES(avg_response_time_ms)
            ");
            
            return $stmt->execute([
                $date,
                $hitRate['total'],
                $hitRate['full_hits'],
                $hitRate['partial_hits'],
                $hitRate['misses'],
                (int)($platformCounts['total_requested'] ?? 0),
                (int)($platformCounts['total_cached'] ?? 0),
                json_encode($channelUsage),
                $avgResponseTime
            ]);
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error updating daily summary - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get statistics for widget
     */
    public function getWidgetStats(string $widgetCode, string $startDate, string $endDate): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN cache_hit_type = 'full' THEN 1 ELSE 0 END) as full_hits,
                    SUM(CASE WHEN cache_hit_type = 'partial' THEN 1 ELSE 0 END) as partial_hits,
                    SUM(CASE WHEN cache_hit_type = 'miss' THEN 1 ELSE 0 END) as misses,
                    AVG(response_time_ms) as avg_response_time
                FROM api_statistics
                WHERE widget_code = ? AND request_date BETWEEN ? AND ?
            ");
            
            $stmt->execute([$widgetCode, $startDate, $endDate]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting widget stats - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get hourly request distribution
     */
    public function getHourlyDistribution(string $date): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    HOUR(request_time) as hour,
                    COUNT(*) as count,
                    SUM(CASE WHEN cache_hit_type IN ('full', 'partial') THEN 1 ELSE 0 END) as hits
                FROM api_statistics
                WHERE request_date = ?
                GROUP BY HOUR(request_time)
                ORDER BY hour
            ");
            
            $stmt->execute([$date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("ApiStatistics: Error getting hourly distribution - " . $e->getMessage());
            return [];
        }
    }
}
