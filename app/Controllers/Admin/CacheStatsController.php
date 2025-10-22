<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\ApiCache;
use App\Helpers\ApiStatistics;

/**
 * Cache Statistics Controller
 * Displays cache performance metrics
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
     * Display cache statistics page
     */
    public function index()
    {
        // Get today's date
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        
        // Get statistics
        $hitRate = $this->statistics->getCacheHitRate($today, $today);
        $weekHitRate = $this->statistics->getCacheHitRate($weekAgo, $today);
        $channelUsage = $this->statistics->getChannelUsage($today, $today);
        $cacheStats = $this->cache->getStats();
        
        // Prepare data for view
        $data = [
            'pageTitle' => 'Cache Statistics',
            'today' => [
                'total_requests' => $hitRate['total'],
                'full_hits' => $hitRate['full_hits'],
                'partial_hits' => $hitRate['partial_hits'],
                'misses' => $hitRate['misses'],
                'hit_rate' => $hitRate['hit_rate'],
                'full_hit_rate' => $hitRate['full_hit_rate'],
                'partial_hit_rate' => $hitRate['partial_hit_rate']
            ],
            'week' => [
                'total_requests' => $weekHitRate['total'],
                'hit_rate' => $weekHitRate['hit_rate']
            ],
            'channels' => $channelUsage,
            'cache' => [
                'total_entries' => $cacheStats['total_entries'] ?? 0,
                'active_entries' => $cacheStats['active_entries'] ?? 0,
                'expired_entries' => $cacheStats['expired_entries'] ?? 0
            ]
        ];
        
        return $this->view('admin/cache/index', $data);
    }
    
    /**
     * API endpoint for dashboard widgets (AJAX)
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
