<?php

namespace App\Controllers\Api;

use Core\ApiRouter;
use Core\WidgetRenderer;
use Core\Database;
use Core\Validator;
use App\Models\Widget;
use App\Models\Hotel;
use App\Models\Statistic;

/**
 * API Widget Controller
 */
class WidgetController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all widgets
     */
    public function index($params)
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 15;
        $userId = $_SESSION['api_user']['id'] ?? null;
        
        $sql = "SELECT w.*, h.name as hotel_name, h.city, h.country 
                FROM widgets w 
                LEFT JOIN hotels h ON w.hotel_id = h.id";
        
        $sqlParams = [];
        
        // Filter by user if not admin
        if (!$_SESSION['api_user']['is_admin']) {
            $sql .= " WHERE w.user_id = ?";
            $sqlParams[] = $userId;
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_table";
        $totalResult = $this->db->selectOne($countSql, $sqlParams);
        $total = $totalResult['total'];
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $paginatedSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
        $widgets = $this->db->select($paginatedSql, $sqlParams);
        
        return [
            'data' => $widgets,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $total,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }
    
    /**
     * Get single widget
     */
    public function show($params)
    {
        $widgetId = $params['id'];
        
        $widget = $this->db->selectOne(
            "SELECT w.*, h.name as hotel_name, h.city, h.country, h.code as hotel_code
             FROM widgets w 
             LEFT JOIN hotels h ON w.hotel_id = h.id 
             WHERE w.id = ?",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        // Check permissions
        if (!$_SESSION['api_user']['is_admin'] && $widget['user_id'] != $_SESSION['api_user']['id']) {
            return ['error' => 'Access denied'];
        }
        
        return ['data' => $widget];
    }
    
    /**
     * Create new widget
     */
    public function store($params)
    {
        $data = ApiRouter::getRequestData();
        
        // Validate input
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'type' => 'required|in:search,rates,booking,comparison',
            'hotel_id' => 'required|exists:hotels,id',
            'settings' => 'json'
        ]);
        
        if ($validator->fails()) {
            return ['error' => 'Validation failed', 'errors' => $validator->errors()];
        }
        
        // Check hotel ownership
        $hotel = $this->db->selectOne(
            "SELECT * FROM hotels WHERE id = ? AND user_id = ?",
            [$data['hotel_id'], $_SESSION['api_user']['id']]
        );
        
        if (!$hotel && !$_SESSION['api_user']['is_admin']) {
            return ['error' => 'Hotel not found or access denied'];
        }
        
        // Create widget
        $widgetData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'hotel_id' => $data['hotel_id'],
            'user_id' => $_SESSION['api_user']['id'],
            'settings' => $data['settings'] ?? '{}',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $widgetId = $this->db->insert('widgets', $widgetData);
        
        if ($widgetId) {
            $widget = $this->db->selectOne("SELECT * FROM widgets WHERE id = ?", [$widgetId]);
            return ['data' => $widget, 'message' => 'Widget created successfully'];
        }
        
        return ['error' => 'Failed to create widget'];
    }
    
    /**
     * Update widget
     */
    public function update($params)
    {
        $widgetId = $params['id'];
        $data = ApiRouter::getRequestData();
        
        // Check widget exists and permissions
        $widget = $this->db->selectOne(
            "SELECT * FROM widgets WHERE id = ?",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        if (!$_SESSION['api_user']['is_admin'] && $widget['user_id'] != $_SESSION['api_user']['id']) {
            return ['error' => 'Access denied'];
        }
        
        // Validate input
        $validator = Validator::make($data, [
            'name' => 'max:255',
            'type' => 'in:search,rates,booking,comparison',
            'hotel_id' => 'exists:hotels,id',
            'settings' => 'json'
        ]);
        
        if ($validator->fails()) {
            return ['error' => 'Validation failed', 'errors' => $validator->errors()];
        }
        
        // Update widget
        $updateData = [];
        
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['type'])) $updateData['type'] = $data['type'];
        if (isset($data['hotel_id'])) $updateData['hotel_id'] = $data['hotel_id'];
        if (isset($data['settings'])) $updateData['settings'] = $data['settings'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $updated = $this->db->update('widgets', $updateData, ['id' => $widgetId]);
        
        if ($updated) {
            $widget = $this->db->selectOne("SELECT * FROM widgets WHERE id = ?", [$widgetId]);
            return ['data' => $widget, 'message' => 'Widget updated successfully'];
        }
        
        return ['error' => 'Failed to update widget'];
    }
    
    /**
     * Delete widget
     */
    public function destroy($params)
    {
        $widgetId = $params['id'];
        
        // Check widget exists and permissions
        $widget = $this->db->selectOne(
            "SELECT * FROM widgets WHERE id = ?",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        if (!$_SESSION['api_user']['is_admin'] && $widget['user_id'] != $_SESSION['api_user']['id']) {
            return ['error' => 'Access denied'];
        }
        
        // Delete widget
        $deleted = $this->db->delete('widgets', ['id' => $widgetId]);
        
        if ($deleted) {
            return ['message' => 'Widget deleted successfully'];
        }
        
        return ['error' => 'Failed to delete widget'];
    }
    
    /**
     * Render widget HTML
     */
    public function render($params)
    {
        $widgetId = $params['id'];
        
        $widget = $this->db->selectOne(
            "SELECT w.*, h.* FROM widgets w 
             LEFT JOIN hotels h ON w.hotel_id = h.id 
             WHERE w.id = ? AND w.is_active = 1",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        $renderer = new WidgetRenderer($widget);
        
        return [
            'html' => $renderer->render(),
            'css' => $renderer->getCSS(),
            'widget' => $widget
        ];
    }
    
    /**
     * Get widget embed code
     */
    public function embed($params)
    {
        $widgetId = $params['id'];
        
        $widget = $this->db->selectOne(
            "SELECT * FROM widgets WHERE id = ? AND is_active = 1",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        $embedCode = WidgetRenderer::getEmbedCode($widgetId);
        
        return [
            'embed_code' => $embedCode,
            'iframe_url' => url("/widgets/{$widgetId}/embed"),
            'widget' => $widget
        ];
    }
    
    /**
     * Track widget events
     */
    public function track($params)
    {
        $widgetId = $params['id'];
        $data = ApiRouter::getRequestData();
        
        $widget = $this->db->selectOne(
            "SELECT * FROM widgets WHERE id = ? AND is_active = 1",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        $event = $data['event'] ?? 'view';
        $timestamp = $data['timestamp'] ?? time();
        
        // Record statistic
        $statistic = new Statistic();
        $statistic->record([
            'hotel_id' => $widget['hotel_id'],
            'widget_id' => $widgetId,
            'metric_type' => 'widget',
            'metric_name' => $event,
            'value' => 1,
            'date' => date('Y-m-d', $timestamp / 1000),
            'metadata' => json_encode([
                'url' => $data['url'] ?? '',
                'referrer' => $data['referrer'] ?? '',
                'element' => $data['element'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => get_client_ip()
            ])
        ]);
        
        return ['message' => 'Event tracked successfully'];
    }
    
    /**
     * Get widget statistics
     */
    public function statistics($params)
    {
        $widgetId = $params['id'];
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Check widget exists and permissions
        $widget = $this->db->selectOne(
            "SELECT * FROM widgets WHERE id = ?",
            [$widgetId]
        );
        
        if (!$widget) {
            return ['error' => 'Widget not found'];
        }
        
        if (!$_SESSION['api_user']['is_admin'] && $widget['user_id'] != $_SESSION['api_user']['id']) {
            return ['error' => 'Access denied'];
        }
        
        // Get statistics
        $stats = $this->db->select(
            "SELECT 
                date,
                metric_name,
                SUM(value) as total_value
             FROM statistics 
             WHERE widget_id = ? 
             AND date BETWEEN ? AND ?
             GROUP BY date, metric_name
             ORDER BY date ASC",
            [$widgetId, $dateFrom, $dateTo]
        );
        
        // Get totals
        $totals = $this->db->select(
            "SELECT 
                metric_name,
                SUM(value) as total_value
             FROM statistics 
             WHERE widget_id = ? 
             AND date BETWEEN ? AND ?
             GROUP BY metric_name",
            [$widgetId, $dateFrom, $dateTo]
        );
        
        return [
            'widget' => $widget,
            'statistics' => $stats,
            'totals' => $totals,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
    }
}
