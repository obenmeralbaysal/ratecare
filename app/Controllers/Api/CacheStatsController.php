<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\ApiCache;
use App\Helpers\ApiStatistics;

/**
 * Cache Statistics API Controller
 * Provides cache performance metrics for dashboard widgets
 */
class CacheStatsController extends BaseController
{
    private $cache;
    private $statistics;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize helpers
        $this->cache = new ApiCache();
        $this->statistics = new ApiStatistics();
    }
    
    /**
     * API endpoint for dashboard widgets (AJAX)
     * GET /api/v1/cache/summary
     */
    public function summary()
    {
        $today = date('Y-m-d');
        
        // Get cache hit rate
        $hitRate = $this->statistics->getCacheHitRate($today, $today);
        
        // Get cache stats
        $cacheStats = $this->cache->getStats();
        
        // Get channel usage
        $channelUsage = $this->statistics->getChannelUsage($today, $today);
        
        // Find most stable channel (least errors)
        $topChannel = $this->getMostStableChannel($today, $today);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'cache_hit_rate' => round($hitRate['hit_rate'], 1),
                'full_hit_rate' => round($hitRate['full_hit_rate'], 1),
                'partial_hit_rate' => round($hitRate['partial_hit_rate'], 1),
                'total_requests' => $hitRate['total'],
                'full_hits' => $hitRate['full_hits'],
                'partial_hits' => $hitRate['partial_hits'],
                'misses' => $hitRate['misses'],
                'top_channel' => ucfirst($topChannel),
                'cache_entries' => $cacheStats['active_entries'] ?? 0
            ]
        ]);
    }
    
    /**
     * Get detailed statistics for analytics page
     * GET /api/v1/cache/statistics
     */
    public function statistics()
    {
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        
        // Get week hit rate
        $weekHitRate = $this->statistics->getCacheHitRate($weekAgo, $today);
        
        // Get cache stats
        $cacheStats = $this->cache->getStats();
        
        // Get channel usage
        $channelUsage = $this->statistics->getChannelUsage($weekAgo, $today);
        
        // Get daily trend (last 7 days)
        $trend = $this->getDailyTrend($weekAgo, $today);
        
        // Prepare channel data for chart
        $channelLabels = [];
        $channelValues = [];
        foreach ($channelUsage as $channel => $count) {
            $channelLabels[] = ucfirst($channel);
            $channelValues[] = $count;
        }
        
        // Get channel error rates
        $channelErrors = $this->getChannelErrorRates($weekAgo, $today);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'overview' => [
                    'hit_rate' => round($weekHitRate['hit_rate'], 1),
                    'total_requests' => $weekHitRate['total'],
                    'full_hits' => $weekHitRate['full_hits'],
                    'partial_hits' => $weekHitRate['partial_hits'],
                    'misses' => $weekHitRate['misses'],
                    'avg_response_time' => $this->getAverageResponseTime($weekAgo, $today),
                    'cache_entries' => $cacheStats['active_entries'] ?? 0
                ],
                'hit_breakdown' => [
                    'full_hits' => $weekHitRate['full_hits'],
                    'partial_hits' => $weekHitRate['partial_hits'],
                    'misses' => $weekHitRate['misses']
                ],
                'trend' => $trend,
                'channels' => [
                    'labels' => $channelLabels,
                    'values' => $channelValues
                ]
            ]
        ]);
    }
    
    /**
     * Get daily trend data
     */
    private function getDailyTrend($startDate, $endDate)
    {
        $dates = [];
        $fullHits = [];
        $partialHits = [];
        $misses = [];
        
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        while ($currentDate <= $endTimestamp) {
            $date = date('Y-m-d', $currentDate);
            $dayStats = $this->statistics->getCacheHitRate($date, $date);
            
            $dates[] = date('M j', $currentDate);
            $fullHits[] = $dayStats['full_hits'];
            $partialHits[] = $dayStats['partial_hits'];
            $misses[] = $dayStats['misses'];
            
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        return [
            'dates' => $dates,
            'full_hits' => $fullHits,
            'partial_hits' => $partialHits,
            'misses' => $misses
        ];
    }
    
    /**
     * Get average response time
     */
    private function getAverageResponseTime($startDate, $endDate)
    {
        try {
            $stmt = $this->statistics->pdo->prepare("
                SELECT AVG(response_time_ms) as avg_time
                FROM api_statistics
                WHERE request_date BETWEEN ? AND ?
                AND cache_hit_type IN ('full', 'partial')
            ");
            $stmt->execute([$startDate, $endDate]);
            $result = $stmt->fetch();
            
            return $result ? round($result['avg_time'] ?? 0) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get circuit breaker status
     * GET /api/v1/circuit-breaker/status
     */
    public function circuitBreakerStatus()
    {
        $circuitBreaker = new \App\Helpers\CircuitBreaker();
        $stats = $circuitBreaker->getStatistics();
        
        return $this->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
    
    /**
     * Reset circuit breaker for platform
     * POST /api/v1/circuit-breaker/reset
     */
    public function circuitBreakerReset()
    {
        $platform = $this->input('platform', null);
        $circuitBreaker = new \App\Helpers\CircuitBreaker();
        
        if ($platform) {
            $circuitBreaker->reset($platform);
            $message = "Circuit breaker reset for platform: {$platform}";
        } else {
            $circuitBreaker->resetAll();
            $message = "All circuit breakers reset";
        }
        
        return $this->json([
            'status' => 'success',
            'message' => $message
        ]);
    }
    
    /**
     * Clear cache (admin action)
     * POST /api/v1/cache/clear
     */
    public function clear()
    {
        $widgetCode = $this->input('widget_code', null);
        
        if ($widgetCode) {
            $result = $this->cache->clear($widgetCode);
            $message = "Cache cleared for widget: {$widgetCode}";
        } else {
            $result = $this->cache->clear();
            $message = "All cache cleared";
        }
        
        return $this->json([
            'status' => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }
    
    /**
     * Get most stable channel (least errors/failures)
     */
    private function getMostStableChannel($dateFrom, $dateTo)
    {
        try {
            $sql = "SELECT channel, 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN miss_count > 0 THEN 1 ELSE 0 END) as error_requests,
                    (SUM(CASE WHEN miss_count > 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as error_rate
                    FROM api_statistics 
                    WHERE DATE(created_at) BETWEEN ? AND ?
                    AND channel IS NOT NULL
                    AND channel != ''
                    GROUP BY channel
                    HAVING total_requests >= 10
                    ORDER BY error_rate ASC, total_requests DESC
                    LIMIT 1";
            
            $result = $this->statistics->pdo->query($sql, [$dateFrom, $dateTo]);
            
            if (!empty($result) && isset($result[0]['channel'])) {
                return ucfirst($result[0]['channel']);
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    
    /**
     * Get error rates for all channels
     */
    private function getChannelErrorRates($dateFrom, $dateTo)
    {
        try {
            $sql = "SELECT channel, 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN miss_count > 0 THEN 1 ELSE 0 END) as error_requests,
                    ROUND((SUM(CASE WHEN miss_count > 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as error_rate,
                    ROUND((1 - (SUM(CASE WHEN miss_count > 0 THEN 1 ELSE 0 END) / COUNT(*))) * 100, 2) as success_rate
                    FROM api_statistics 
                    WHERE DATE(created_at) BETWEEN ? AND ?
                    AND channel IS NOT NULL
                    AND channel != ''
                    GROUP BY channel
                    HAVING total_requests >= 5
                    ORDER BY error_rate DESC";
            
            $result = $this->statistics->pdo->query($sql, [$dateFrom, $dateTo]);
            
            $channelErrors = [];
            foreach ($result as $row) {
                $channelErrors[] = [
                    'channel' => ucfirst($row['channel']),
                    'total_requests' => (int)$row['total_requests'],
                    'error_requests' => (int)$row['error_requests'],
                    'error_rate' => (float)$row['error_rate'],
                    'success_rate' => (float)$row['success_rate'],
                    'status' => $row['error_rate'] > 20 ? 'critical' : ($row['error_rate'] > 10 ? 'warning' : 'healthy')
                ];
            }
            
            return $channelErrors;
        } catch (\Exception $e) {
            return [];
        }
    }
}
