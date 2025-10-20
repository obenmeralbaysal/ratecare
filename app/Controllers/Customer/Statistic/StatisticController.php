<?php

namespace App\Controllers\Customer\Statistic;

use App\Controllers\BaseController;
use App\Models\Statistic;
use App\Models\Hotel;
use App\Models\Widget;
use Core\Auth;
use Core\Authorization;

/**
 * Customer Statistics Controller
 */
class StatisticController extends BaseController
{
    private $auth;
    private $authz;
    private $statisticModel;
    private $hotelModel;
    private $widgetModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        $this->statisticModel = new Statistic();
        $this->hotelModel = new Hotel();
        $this->widgetModel = new Widget();
    }
    
    /**
     * Show statistics dashboard
     */
    public function index()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $period = $this->input('period', '30');
        
        // Get user's hotels
        $hotels = $this->hotelModel->getByUser($userId);
        
        $selectedHotel = null;
        if ($hotelId) {
            if (!$this->authz->canManageHotels($hotelId)) {
                return $this->redirect('/customer/statistics')->with('error', 'Access denied');
            }
            $selectedHotel = $this->hotelModel->find($hotelId);
        }
        
        // Get dashboard statistics
        $dashboardStats = $this->getDashboardStatistics($userId, $hotelId, $period);
        
        return $this->view('customer.statistics.index', [
            'title' => 'Statistics Dashboard',
            'hotels' => $hotels,
            'selected_hotel' => $selectedHotel,
            'dashboard_stats' => $dashboardStats,
            'period' => $period
        ]);
    }
    
    /**
     * Get detailed statistics data
     */
    public function data()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $widgetId = $this->input('widget_id');
        $metricType = $this->input('metric_type', 'views');
        $period = $this->input('period', '30');
        $groupBy = $this->input('group_by', 'daily');
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        if ($widgetId && !$this->authz->canManageWidgets($widgetId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        if ($widgetId) {
            $filters['widget_id'] = $widgetId;
        }
        
        $data = [];
        
        if ($hotelId) {
            $data = $this->statisticModel->getAggregated($hotelId, $metricType, $groupBy, $filters);
        } else {
            // Get data for all user's hotels
            $hotels = $this->hotelModel->getByUser($userId);
            foreach ($hotels as $hotel) {
                $hotelData = $this->statisticModel->getAggregated($hotel['id'], $metricType, $groupBy, $filters);
                foreach ($hotelData as $item) {
                    $item['hotel_name'] = $hotel['name'];
                    $data[] = $item;
                }
            }
        }
        
        return $this->json([
            'data' => $data,
            'filters' => $filters,
            'metric_type' => $metricType,
            'group_by' => $groupBy
        ]);
    }
    
    /**
     * Get conversion funnel data
     */
    public function conversionFunnel()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $period = $this->input('period', '30');
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $funnelData = [];
        
        if ($hotelId) {
            $funnelData = $this->statisticModel->getConversionRates($hotelId, $dateFrom, $dateTo);
        } else {
            // Aggregate data for all user's hotels
            $hotels = $this->hotelModel->getByUser($userId);
            $aggregated = [
                'views' => 0,
                'clicks' => 0,
                'bookings' => 0
            ];
            
            foreach ($hotels as $hotel) {
                $hotelData = $this->statisticModel->getConversionRates($hotel['id'], $dateFrom, $dateTo);
                foreach ($hotelData as $day) {
                    $aggregated['views'] += $day['views'];
                    $aggregated['clicks'] += $day['clicks'];
                    $aggregated['bookings'] += $day['bookings'];
                }
            }
            
            $clickRate = $aggregated['views'] > 0 ? ($aggregated['clicks'] / $aggregated['views']) * 100 : 0;
            $conversionRate = $aggregated['clicks'] > 0 ? ($aggregated['bookings'] / $aggregated['clicks']) * 100 : 0;
            
            $funnelData = [[
                'views' => $aggregated['views'],
                'clicks' => $aggregated['clicks'],
                'bookings' => $aggregated['bookings'],
                'click_rate' => round($clickRate, 2),
                'conversion_rate' => round($conversionRate, 2)
            ]];
        }
        
        return $this->json([
            'funnel_data' => $funnelData,
            'period' => $period
        ]);
    }
    
    /**
     * Get top performing widgets
     */
    public function topWidgets()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $metricType = $this->input('metric_type', 'views');
        $limit = (int) $this->input('limit', 10);
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $topWidgets = [];
        
        if ($hotelId) {
            $topWidgets = $this->statisticModel->getTopWidgets($hotelId, $metricType, $limit);
        } else {
            // Get top widgets across all user's hotels
            $hotels = $this->hotelModel->getByUser($userId);
            $allWidgets = [];
            
            foreach ($hotels as $hotel) {
                $hotelWidgets = $this->statisticModel->getTopWidgets($hotel['id'], $metricType, $limit);
                foreach ($hotelWidgets as $widget) {
                    $widget['hotel_name'] = $hotel['name'];
                    $allWidgets[] = $widget;
                }
            }
            
            // Sort by total value and limit
            usort($allWidgets, function($a, $b) {
                return $b['total_value'] - $a['total_value'];
            });
            
            $topWidgets = array_slice($allWidgets, 0, $limit);
        }
        
        return $this->json([
            'top_widgets' => $topWidgets,
            'metric_type' => $metricType,
            'limit' => $limit
        ]);
    }
    
    /**
     * Export statistics data
     */
    public function export()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $format = $this->input('format', 'csv');
        $period = $this->input('period', '30');
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $data = [];
        
        if ($hotelId) {
            $data = $this->statisticModel->getByHotel($hotelId, $filters);
        } else {
            // Get data for all user's hotels
            $hotels = $this->hotelModel->getByUser($userId);
            foreach ($hotels as $hotel) {
                $hotelData = $this->statisticModel->getByHotel($hotel['id'], $filters);
                foreach ($hotelData as $item) {
                    $item['hotel_name'] = $hotel['name'];
                    $data[] = $item;
                }
            }
        }
        
        if ($format === 'csv') {
            return $this->exportCsv($data, 'statistics_' . date('Y-m-d') . '.csv');
        } else {
            return $this->json($data);
        }
    }
    
    /**
     * Get real-time statistics
     */
    public function realtime()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $today = date('Y-m-d');
        $currentHour = date('H');
        
        $filters = [
            'date_from' => $today,
            'date_to' => $today
        ];
        
        $realtimeData = [];
        
        if ($hotelId) {
            $realtimeData = $this->statisticModel->getByHotel($hotelId, $filters);
        } else {
            $hotels = $this->hotelModel->getByUser($userId);
            foreach ($hotels as $hotel) {
                $hotelData = $this->statisticModel->getByHotel($hotel['id'], $filters);
                foreach ($hotelData as $item) {
                    $item['hotel_name'] = $hotel['name'];
                    $realtimeData[] = $item;
                }
            }
        }
        
        // Group by hour for today
        $hourlyData = [];
        for ($hour = 0; $hour <= $currentHour; $hour++) {
            $hourlyData[$hour] = [
                'hour' => $hour,
                'views' => 0,
                'clicks' => 0,
                'bookings' => 0
            ];
        }
        
        foreach ($realtimeData as $stat) {
            $hour = $stat['hour'] ?? 0;
            if (isset($hourlyData[$hour])) {
                $hourlyData[$hour][$stat['metric_type']] += $stat['value'];
            }
        }
        
        return $this->json([
            'realtime_data' => array_values($hourlyData),
            'current_hour' => $currentHour,
            'last_updated' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics($userId, $hotelId = null, $period = '30')
    {
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        if ($hotelId) {
            return $this->statisticModel->getDashboardStats($hotelId, $dateFrom, $dateTo);
        } else {
            // Aggregate stats for all user's hotels
            $hotels = $this->hotelModel->getByUser($userId);
            $aggregatedStats = [];
            
            foreach ($hotels as $hotel) {
                $hotelStats = $this->statisticModel->getDashboardStats($hotel['id'], $dateFrom, $dateTo);
                
                foreach ($hotelStats as $stat) {
                    $metricType = $stat['metric_type'];
                    
                    if (!isset($aggregatedStats[$metricType])) {
                        $aggregatedStats[$metricType] = [
                            'metric_type' => $metricType,
                            'total' => 0,
                            'days' => 0,
                            'daily_avg' => 0
                        ];
                    }
                    
                    $aggregatedStats[$metricType]['total'] += $stat['total'];
                    $aggregatedStats[$metricType]['days'] = max($aggregatedStats[$metricType]['days'], $stat['days']);
                }
            }
            
            // Calculate daily averages
            foreach ($aggregatedStats as &$stat) {
                $stat['daily_avg'] = $stat['days'] > 0 ? $stat['total'] / $stat['days'] : 0;
            }
            
            return array_values($aggregatedStats);
        }
    }
    
    /**
     * Export data as CSV
     */
    private function exportCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Date', 'Hour', 'Hotel', 'Widget', 'Metric Type', 'Metric Name', 'Value', 'Currency'
        ]);
        
        // CSV data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['date'],
                $row['hour'] ?? '',
                $row['hotel_name'] ?? '',
                $row['widget_name'] ?? '',
                $row['metric_type'],
                $row['metric_name'],
                $row['value'],
                $row['currency'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}
