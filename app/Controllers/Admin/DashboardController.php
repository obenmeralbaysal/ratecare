<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Widget;
use App\Models\Rate;
use App\Models\Statistic;
use App\Models\Invite;
use Core\Auth;
use Core\Authorization;

/**
 * Admin Dashboard Controller
 */
class DashboardController extends BaseController
{
    private $auth;
    private $authz;
    private $userModel;
    private $hotelModel;
    private $widgetModel;
    private $rateModel;
    private $statisticModel;
    private $inviteModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        
        // Check admin permission
        if (!$this->authz->canAccessAdmin()) {
            return $this->redirect('/')->with('error', 'Access denied');
        }
        
        $this->userModel = new User();
        $this->hotelModel = new Hotel();
        $this->widgetModel = new Widget();
        $this->rateModel = new Rate();
        $this->statisticModel = new Statistic();
        $this->inviteModel = new Invite();
    }
    
    /**
     * Show admin dashboard
     */
    public function index()
    {
        // Get overview statistics
        $overviewStats = $this->getOverviewStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get system health
        $systemHealth = $this->getSystemHealth();
        
        // Get top performing hotels
        $topHotels = $this->getTopHotels();
        
        return $this->view('admin.dashboard.index', [
            'title' => 'Admin Dashboard',
            'overview_stats' => $overviewStats,
            'recent_activities' => $recentActivities,
            'system_health' => $systemHealth,
            'top_hotels' => $topHotels
        ]);
    }
    
    /**
     * Get dashboard data via AJAX
     */
    public function getData()
    {
        $period = $this->input('period', '30');
        $metric = $this->input('metric', 'all');
        
        $data = [];
        
        switch ($metric) {
            case 'users':
                $data = $this->getUserGrowthData($period);
                break;
                
            case 'hotels':
                $data = $this->getHotelGrowthData($period);
                break;
                
            case 'widgets':
                $data = $this->getWidgetGrowthData($period);
                break;
                
            case 'statistics':
                $data = $this->getStatisticsData($period);
                break;
                
            default:
                $data = [
                    'users' => $this->getUserGrowthData($period),
                    'hotels' => $this->getHotelGrowthData($period),
                    'widgets' => $this->getWidgetGrowthData($period),
                    'statistics' => $this->getStatisticsData($period)
                ];
        }
        
        return $this->json([
            'data' => $data,
            'period' => $period,
            'metric' => $metric
        ]);
    }
    
    /**
     * Get system analytics
     */
    public function analytics()
    {
        $period = $this->input('period', '30');
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        // User analytics
        $userAnalytics = $this->userModel->db->select(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as new_admins,
                SUM(CASE WHEN reseller_id = 0 AND is_admin = 0 THEN 1 ELSE 0 END) as new_resellers,
                SUM(CASE WHEN reseller_id > 0 THEN 1 ELSE 0 END) as new_customers
             FROM users 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
        
        // Hotel analytics
        $hotelAnalytics = $this->hotelModel->db->select(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_hotels,
                COUNT(DISTINCT country) as countries,
                COUNT(DISTINCT city) as cities
             FROM hotels 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
        
        // Widget analytics
        $widgetAnalytics = $this->widgetModel->db->select(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_widgets,
                SUM(CASE WHEN type = 'booking' THEN 1 ELSE 0 END) as booking_widgets,
                SUM(CASE WHEN type = 'rates' THEN 1 ELSE 0 END) as rate_widgets,
                SUM(CASE WHEN type = 'availability' THEN 1 ELSE 0 END) as availability_widgets
             FROM widgets 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
        
        // Performance analytics
        $performanceAnalytics = $this->statisticModel->db->select(
            "SELECT 
                date,
                SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END) as total_views,
                SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) as total_clicks,
                SUM(CASE WHEN metric_type = 'bookings' THEN value ELSE 0 END) as total_bookings
             FROM statistics 
             WHERE date BETWEEN ? AND ?
             GROUP BY date
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
        
        return $this->json([
            'user_analytics' => $userAnalytics,
            'hotel_analytics' => $hotelAnalytics,
            'widget_analytics' => $widgetAnalytics,
            'performance_analytics' => $performanceAnalytics,
            'period' => $period
        ]);
    }
    
    /**
     * Get system reports
     */
    public function reports()
    {
        $reportType = $this->input('type', 'summary');
        $period = $this->input('period', '30');
        
        switch ($reportType) {
            case 'users':
                return $this->getUserReport($period);
                
            case 'hotels':
                return $this->getHotelReport($period);
                
            case 'widgets':
                return $this->getWidgetReport($period);
                
            case 'performance':
                return $this->getPerformanceReport($period);
                
            default:
                return $this->getSummaryReport($period);
        }
    }
    
    /**
     * Export dashboard data
     */
    public function export()
    {
        $format = $this->input('format', 'csv');
        $type = $this->input('type', 'summary');
        $period = $this->input('period', '30');
        
        $data = $this->getExportData($type, $period);
        
        if ($format === 'csv') {
            return $this->exportCsv($data, "admin_report_{$type}_" . date('Y-m-d') . '.csv');
        } else {
            return $this->json($data);
        }
    }
    
    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        // Total counts
        $totalUsers = $this->userModel->count();
        $totalHotels = $this->hotelModel->count();
        $totalWidgets = $this->widgetModel->count();
        $totalRates = $this->rateModel->count();
        
        // Active counts
        $activeHotels = $this->hotelModel->count('is_active = 1');
        $activeWidgets = $this->widgetModel->count('is_active = 1');
        
        // User breakdown
        $adminCount = $this->userModel->count('is_admin = 1');
        $resellerCount = $this->userModel->count('reseller_id = 0 AND is_admin = 0');
        $customerCount = $this->userModel->count('reseller_id > 0');
        
        // Recent statistics (last 30 days)
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');
        
        $recentStats = $this->statisticModel->db->selectOne(
            "SELECT 
                SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END) as total_views,
                SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) as total_clicks,
                SUM(CASE WHEN metric_type = 'bookings' THEN value ELSE 0 END) as total_bookings
             FROM statistics 
             WHERE date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        
        return [
            'total_users' => $totalUsers,
            'total_hotels' => $totalHotels,
            'total_widgets' => $totalWidgets,
            'total_rates' => $totalRates,
            'active_hotels' => $activeHotels,
            'active_widgets' => $activeWidgets,
            'admin_count' => $adminCount,
            'reseller_count' => $resellerCount,
            'customer_count' => $customerCount,
            'total_views' => $recentStats['total_views'] ?? 0,
            'total_clicks' => $recentStats['total_clicks'] ?? 0,
            'total_bookings' => $recentStats['total_bookings'] ?? 0
        ];
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        // Recent user registrations
        $recentUsers = $this->userModel->db->select(
            "SELECT 'user_registered' as type, namesurname as title, email as subtitle, created_at 
             FROM users 
             ORDER BY created_at DESC 
             LIMIT 5"
        );
        
        // Recent hotel creations
        $recentHotels = $this->hotelModel->db->select(
            "SELECT 'hotel_created' as type, h.name as title, CONCAT(h.city, ', ', h.country) as subtitle, h.created_at,
                    u.namesurname as user_name
             FROM hotels h
             JOIN users u ON h.user_id = u.id
             ORDER BY h.created_at DESC 
             LIMIT 5"
        );
        
        // Recent widget creations
        $recentWidgets = $this->widgetModel->db->select(
            "SELECT 'widget_created' as type, w.name as title, CONCAT(w.type, ' - ', h.name) as subtitle, w.created_at,
                    u.namesurname as user_name
             FROM widgets w
             JOIN hotels h ON w.hotel_id = h.id
             JOIN users u ON h.user_id = u.id
             ORDER BY w.created_at DESC 
             LIMIT 5"
        );
        
        // Merge and sort activities
        $activities = array_merge($recentUsers, $recentHotels, $recentWidgets);
        
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    /**
     * Get system health metrics
     */
    private function getSystemHealth()
    {
        // Database health
        $dbHealth = $this->checkDatabaseHealth();
        
        // Application health
        $appHealth = $this->checkApplicationHealth();
        
        // Performance metrics
        $performance = $this->getPerformanceMetrics();
        
        return [
            'database' => $dbHealth,
            'application' => $appHealth,
            'performance' => $performance,
            'overall_status' => $this->calculateOverallHealth($dbHealth, $appHealth, $performance)
        ];
    }
    
    /**
     * Get top performing hotels
     */
    private function getTopHotels()
    {
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');
        
        return $this->statisticModel->db->select(
            "SELECT 
                h.name as hotel_name,
                h.city,
                h.country,
                u.namesurname as owner_name,
                SUM(CASE WHEN s.metric_type = 'views' THEN s.value ELSE 0 END) as total_views,
                SUM(CASE WHEN s.metric_type = 'clicks' THEN s.value ELSE 0 END) as total_clicks,
                SUM(CASE WHEN s.metric_type = 'bookings' THEN s.value ELSE 0 END) as total_bookings
             FROM hotels h
             JOIN users u ON h.user_id = u.id
             LEFT JOIN statistics s ON h.id = s.hotel_id AND s.date BETWEEN ? AND ?
             GROUP BY h.id
             ORDER BY total_views DESC
             LIMIT 10",
            [$dateFrom, $dateTo]
        );
    }
    
    /**
     * Helper methods for data retrieval
     */
    private function getUserGrowthData($period)
    {
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        return $this->userModel->db->select(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM users 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
    }
    
    private function getHotelGrowthData($period)
    {
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        return $this->hotelModel->db->select(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM hotels 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
    }
    
    private function getWidgetGrowthData($period)
    {
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        return $this->widgetModel->db->select(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM widgets 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
    }
    
    private function getStatisticsData($period)
    {
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        return $this->statisticModel->db->select(
            "SELECT 
                date,
                SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END) as views,
                SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) as clicks,
                SUM(CASE WHEN metric_type = 'bookings' THEN value ELSE 0 END) as bookings
             FROM statistics 
             WHERE date BETWEEN ? AND ?
             GROUP BY date
             ORDER BY date ASC",
            [$dateFrom, $dateTo]
        );
    }
    
    private function checkDatabaseHealth()
    {
        try {
            $this->userModel->db->selectOne("SELECT 1");
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }
    
    private function checkApplicationHealth()
    {
        $checks = [
            'storage_writable' => is_writable(__DIR__ . '/../../../storage'),
            'views_writable' => is_writable(__DIR__ . '/../../../storage/views'),
            'logs_writable' => is_writable(__DIR__ . '/../../../storage/logs')
        ];
        
        $healthy = array_reduce($checks, function($carry, $check) {
            return $carry && $check;
        }, true);
        
        return [
            'status' => $healthy ? 'healthy' : 'warning',
            'checks' => $checks
        ];
    }
    
    private function getPerformanceMetrics()
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ];
    }
    
    private function calculateOverallHealth($db, $app, $perf)
    {
        if ($db['status'] === 'error') return 'critical';
        if ($app['status'] === 'warning') return 'warning';
        return 'healthy';
    }
    
    private function getSummaryReport($period)
    {
        return [
            'overview' => $this->getOverviewStats(),
            'growth' => [
                'users' => $this->getUserGrowthData($period),
                'hotels' => $this->getHotelGrowthData($period),
                'widgets' => $this->getWidgetGrowthData($period)
            ],
            'performance' => $this->getStatisticsData($period)
        ];
    }
    
    private function getExportData($type, $period)
    {
        switch ($type) {
            case 'users':
                return $this->userModel->all();
            case 'hotels':
                return $this->hotelModel->all();
            case 'widgets':
                return $this->widgetModel->all();
            default:
                return $this->getSummaryReport($period);
        }
    }
    
    private function exportCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data) && is_array($data[0])) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}
