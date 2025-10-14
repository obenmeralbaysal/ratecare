<?php

namespace App\Models;

/**
 * Statistic Model
 */
class Statistic extends BaseModel
{
    protected $table = 'statistics';
    protected $fillable = [
        'hotel_id', 'widget_id', 'metric_type', 'metric_name', 'value', 
        'currency', 'date', 'hour', 'metadata'
    ];
    
    /**
     * Record a statistic
     */
    public function record($hotelId, $metricType, $metricName, $value, $options = [])
    {
        $data = [
            'hotel_id' => $hotelId,
            'metric_type' => $metricType,
            'metric_name' => $metricName,
            'value' => $value,
            'date' => $options['date'] ?? date('Y-m-d'),
            'hour' => $options['hour'] ?? null,
            'widget_id' => $options['widget_id'] ?? null,
            'currency' => $options['currency'] ?? null,
            'metadata' => isset($options['metadata']) ? json_encode($options['metadata']) : null
        ];
        
        // Try to update existing record first
        $existing = $this->findExisting($data);
        
        if ($existing) {
            return $this->update($existing['id'], [
                'value' => $existing['value'] + $value,
                'metadata' => $data['metadata']
            ]);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Find existing statistic record
     */
    private function findExisting($data)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE hotel_id = ? AND metric_type = ? AND metric_name = ? AND date = ?";
        $params = [$data['hotel_id'], $data['metric_type'], $data['metric_name'], $data['date']];
        
        if ($data['widget_id']) {
            $sql .= " AND widget_id = ?";
            $params[] = $data['widget_id'];
        } else {
            $sql .= " AND widget_id IS NULL";
        }
        
        if ($data['hour'] !== null) {
            $sql .= " AND hour = ?";
            $params[] = $data['hour'];
        } else {
            $sql .= " AND hour IS NULL";
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Get statistics by hotel
     */
    public function getByHotel($hotelId, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE hotel_id = ?";
        $params = [$hotelId];
        
        if (isset($filters['metric_type'])) {
            $sql .= " AND metric_type = ?";
            $params[] = $filters['metric_type'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['widget_id'])) {
            $sql .= " AND widget_id = ?";
            $params[] = $filters['widget_id'];
        }
        
        $sql .= " ORDER BY date DESC, hour DESC";
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get statistics by widget
     */
    public function getByWidget($widgetId, $filters = [])
    {
        $filters['widget_id'] = $widgetId;
        
        // Get hotel_id for the widget
        $widget = $this->db->selectOne("SELECT hotel_id FROM widgets WHERE id = ?", [$widgetId]);
        
        if (!$widget) {
            return [];
        }
        
        return $this->getByHotel($widget['hotel_id'], $filters);
    }
    
    /**
     * Get aggregated statistics
     */
    public function getAggregated($hotelId, $metricType, $period = 'daily', $filters = [])
    {
        $dateFormat = $this->getDateFormat($period);
        
        $sql = "SELECT 
                    {$dateFormat} as period,
                    metric_name,
                    SUM(value) as total_value,
                    AVG(value) as avg_value,
                    COUNT(*) as count
                FROM {$this->table} 
                WHERE hotel_id = ? AND metric_type = ?";
        
        $params = [$hotelId, $metricType];
        
        if (isset($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['widget_id'])) {
            $sql .= " AND widget_id = ?";
            $params[] = $filters['widget_id'];
        }
        
        $sql .= " GROUP BY period, metric_name ORDER BY period DESC";
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get date format for aggregation
     */
    private function getDateFormat($period)
    {
        switch ($period) {
            case 'hourly':
                return "CONCAT(date, ' ', LPAD(hour, 2, '0'), ':00')";
            case 'daily':
                return 'date';
            case 'weekly':
                return "DATE_FORMAT(date, '%Y-%u')";
            case 'monthly':
                return "DATE_FORMAT(date, '%Y-%m')";
            case 'yearly':
                return "YEAR(date)";
            default:
                return 'date';
        }
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats($hotelId, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "SELECT 
                    metric_type,
                    SUM(value) as total,
                    COUNT(DISTINCT date) as days,
                    AVG(value) as daily_avg
                FROM {$this->table} 
                WHERE hotel_id = ? AND date BETWEEN ? AND ?
                GROUP BY metric_type";
        
        return $this->raw($sql, [$hotelId, $dateFrom, $dateTo]);
    }
    
    /**
     * Get top performing widgets
     */
    public function getTopWidgets($hotelId, $metricType = 'views', $limit = 10)
    {
        $sql = "SELECT 
                    s.widget_id,
                    w.name as widget_name,
                    w.type as widget_type,
                    SUM(s.value) as total_value
                FROM {$this->table} s
                JOIN widgets w ON s.widget_id = w.id
                WHERE s.hotel_id = ? AND s.metric_type = ? AND s.widget_id IS NOT NULL
                GROUP BY s.widget_id
                ORDER BY total_value DESC
                LIMIT ?";
        
        return $this->raw($sql, [$hotelId, $metricType, $limit]);
    }
    
    /**
     * Get conversion rates
     */
    public function getConversionRates($hotelId, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "SELECT 
                    date,
                    SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END) as views,
                    SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) as clicks,
                    SUM(CASE WHEN metric_type = 'bookings' THEN value ELSE 0 END) as bookings,
                    CASE 
                        WHEN SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END) > 0 
                        THEN (SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) / SUM(CASE WHEN metric_type = 'views' THEN value ELSE 0 END)) * 100
                        ELSE 0 
                    END as click_rate,
                    CASE 
                        WHEN SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END) > 0 
                        THEN (SUM(CASE WHEN metric_type = 'bookings' THEN value ELSE 0 END) / SUM(CASE WHEN metric_type = 'clicks' THEN value ELSE 0 END)) * 100
                        ELSE 0 
                    END as conversion_rate
                FROM {$this->table}
                WHERE hotel_id = ? AND date BETWEEN ? AND ?
                GROUP BY date
                ORDER BY date DESC";
        
        return $this->raw($sql, [$hotelId, $dateFrom, $dateTo]);
    }
    
    /**
     * Get hotel relationship
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }
    
    /**
     * Get widget relationship
     */
    public function widget()
    {
        return $this->belongsTo('App\Models\Widget', 'widget_id');
    }
    
    /**
     * Clean old statistics (older than specified days)
     */
    public function cleanOldStats($days = 365)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE date < ?",
            [$cutoffDate]
        );
    }
}
