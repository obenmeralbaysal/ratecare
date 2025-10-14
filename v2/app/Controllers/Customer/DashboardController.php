<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\Hotel;
use App\Models\Widget;
use App\Models\Rate;
use App\Models\Statistic;
use Core\Auth;

/**
 * Customer Dashboard Controller
 */
class DashboardController extends BaseController
{
    private $auth;
    private $hotelModel;
    private $widgetModel;
    private $rateModel;
    private $statisticModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->hotelModel = new Hotel();
        $this->widgetModel = new Widget();
        $this->rateModel = new Rate();
        $this->statisticModel = new Statistic();
        
        // Ensure user is customer
        if (!$this->auth->isCustomer() && !$this->auth->isAdmin()) {
            return $this->redirect('/')->with('error', 'Access denied');
        }
    }
    
    /**
     * Show customer dashboard
     */
    public function dashboard()
    {
        $userId = $this->auth->id();
        
        // Get user's hotels
        $hotels = $this->hotelModel->getByUser($userId);
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($userId);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($userId);
        
        return $this->view('customer.dashboard.index', [
            'title' => 'Dashboard',
            'hotels' => $hotels,
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'user' => $this->auth->user()
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($userId)
    {
        $hotels = $this->hotelModel->getByUser($userId);
        $hotelIds = array_column($hotels, 'id');
        
        if (empty($hotelIds)) {
            return [
                'total_hotels' => 0,
                'total_widgets' => 0,
                'total_views' => 0,
                'total_clicks' => 0,
                'total_bookings' => 0,
                'conversion_rate' => 0
            ];
        }
        
        $hotelIdsStr = implode(',', $hotelIds);
        
        // Get widget count
        $widgetCount = $this->widgetModel->db->selectOne(
            "SELECT COUNT(*) as count FROM widgets WHERE hotel_id IN ({$hotelIdsStr})"
        )['count'];
        
        // Get statistics for last 30 days
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');
        
        $viewStats = $this->statisticModel->db->selectOne(
            "SELECT SUM(value) as total FROM statistics 
             WHERE hotel_id IN ({$hotelIdsStr}) 
             AND metric_type = 'views' 
             AND date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        
        $clickStats = $this->statisticModel->db->selectOne(
            "SELECT SUM(value) as total FROM statistics 
             WHERE hotel_id IN ({$hotelIdsStr}) 
             AND metric_type = 'clicks' 
             AND date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        
        $bookingStats = $this->statisticModel->db->selectOne(
            "SELECT SUM(value) as total FROM statistics 
             WHERE hotel_id IN ({$hotelIdsStr}) 
             AND metric_type = 'bookings' 
             AND date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        
        $totalViews = $viewStats['total'] ?? 0;
        $totalClicks = $clickStats['total'] ?? 0;
        $totalBookings = $bookingStats['total'] ?? 0;
        
        $conversionRate = $totalClicks > 0 ? ($totalBookings / $totalClicks) * 100 : 0;
        
        return [
            'total_hotels' => count($hotels),
            'total_widgets' => $widgetCount,
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'total_bookings' => $totalBookings,
            'conversion_rate' => round($conversionRate, 2)
        ];
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($userId)
    {
        $hotels = $this->hotelModel->getByUser($userId);
        $hotelIds = array_column($hotels, 'id');
        
        if (empty($hotelIds)) {
            return [];
        }
        
        $hotelIdsStr = implode(',', $hotelIds);
        
        // Get recent statistics
        $activities = $this->statisticModel->db->select(
            "SELECT s.*, h.name as hotel_name, w.name as widget_name
             FROM statistics s
             JOIN hotels h ON s.hotel_id = h.id
             LEFT JOIN widgets w ON s.widget_id = w.id
             WHERE s.hotel_id IN ({$hotelIdsStr})
             ORDER BY s.created_at DESC
             LIMIT 20"
        );
        
        return $activities;
    }
    
    /**
     * Get dashboard data via AJAX
     */
    public function getData()
    {
        $userId = $this->auth->id();
        $period = $this->input('period', '30'); // days
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $hotels = $this->hotelModel->getByUser($userId);
        $hotelIds = array_column($hotels, 'id');
        
        if (empty($hotelIds)) {
            return $this->json([
                'stats' => [],
                'chart_data' => [],
                'top_widgets' => []
            ]);
        }
        
        $hotelIdsStr = implode(',', $hotelIds);
        
        // Get daily statistics
        $dailyStats = $this->statisticModel->db->select(
            "SELECT 
                date,
                metric_type,
                SUM(value) as total
             FROM statistics 
             WHERE hotel_id IN ({$hotelIdsStr}) 
             AND date BETWEEN ? AND ?
             GROUP BY date, metric_type
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
        
        // Get top performing widgets
        $topWidgets = $this->statisticModel->db->select(
            "SELECT 
                w.name as widget_name,
                w.type as widget_type,
                h.name as hotel_name,
                SUM(s.value) as total_views
             FROM statistics s
             JOIN widgets w ON s.widget_id = w.id
             JOIN hotels h ON s.hotel_id = h.id
             WHERE s.hotel_id IN ({$hotelIdsStr}) 
             AND s.metric_type = 'views'
             AND s.date BETWEEN ? AND ?
             GROUP BY s.widget_id
             ORDER BY total_views DESC
             LIMIT 10",
            [$dateFrom, $dateTo]
        );
        
        // Format chart data
        $chartData = $this->formatChartData($dailyStats);
        
        return $this->json([
            'stats' => $this->getDashboardStats($userId),
            'chart_data' => $chartData,
            'top_widgets' => $topWidgets
        ]);
    }
    
    /**
     * Format data for charts
     */
    private function formatChartData($dailyStats)
    {
        $chartData = [
            'labels' => [],
            'datasets' => [
                'views' => [],
                'clicks' => [],
                'bookings' => []
            ]
        ];
        
        $groupedData = [];
        
        foreach ($dailyStats as $stat) {
            $date = $stat['date'];
            $type = $stat['metric_type'];
            $total = $stat['total'];
            
            if (!isset($groupedData[$date])) {
                $groupedData[$date] = [
                    'views' => 0,
                    'clicks' => 0,
                    'bookings' => 0
                ];
            }
            
            $groupedData[$date][$type] = $total;
        }
        
        foreach ($groupedData as $date => $data) {
            $chartData['labels'][] = $date;
            $chartData['datasets']['views'][] = $data['views'];
            $chartData['datasets']['clicks'][] = $data['clicks'];
            $chartData['datasets']['bookings'][] = $data['bookings'];
        }
        
        return $chartData;
    }
    
    /**
     * Export dashboard data
     */
    public function export()
    {
        $userId = $this->auth->id();
        $format = $this->input('format', 'csv');
        $period = $this->input('period', '30');
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $hotels = $this->hotelModel->getByUser($userId);
        $hotelIds = array_column($hotels, 'id');
        
        if (empty($hotelIds)) {
            return $this->json(['error' => 'No data to export'], 400);
        }
        
        $hotelIdsStr = implode(',', $hotelIds);
        
        $data = $this->statisticModel->db->select(
            "SELECT 
                s.date,
                s.metric_type,
                s.metric_name,
                s.value,
                h.name as hotel_name,
                w.name as widget_name
             FROM statistics s
             JOIN hotels h ON s.hotel_id = h.id
             LEFT JOIN widgets w ON s.widget_id = w.id
             WHERE s.hotel_id IN ({$hotelIdsStr})
             AND s.date BETWEEN ? AND ?
             ORDER BY s.date DESC, h.name, s.metric_type",
            [$dateFrom, $dateTo]
        );
        
        if ($format === 'csv') {
            return $this->exportCsv($data, 'dashboard_stats_' . date('Y-m-d') . '.csv');
        } else {
            return $this->json($data);
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
        fputcsv($output, ['Date', 'Hotel', 'Widget', 'Metric Type', 'Metric Name', 'Value']);
        
        // CSV data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['date'],
                $row['hotel_name'],
                $row['widget_name'] ?: 'N/A',
                $row['metric_type'],
                $row['metric_name'],
                $row['value']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
