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
        
        return $this->jsonResponse([
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
        
        return $this->jsonResponse([
            'status' => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }
}
