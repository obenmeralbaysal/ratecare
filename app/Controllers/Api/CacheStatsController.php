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
        
        // Find most used channel
        $topChannel = !empty($channelUsage) ? array_key_first($channelUsage) : 'N/A';
        
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
}
