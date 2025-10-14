<?php

namespace App\Models;

use Core\Hash;

/**
 * Widget Model
 */
class Widget extends BaseModel
{
    protected $table = 'widgets';
    protected $fillable = [
        'hotel_id', 'name', 'code', 'type', 'settings', 'style_settings', 'is_active'
    ];
    
    /**
     * Create widget with auto-generated code
     */
    public function createWidget($data)
    {
        if (!isset($data['code'])) {
            $data['code'] = Hash::widgetCode();
        }
        
        // Encode settings as JSON if array
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings']);
        }
        
        if (isset($data['style_settings']) && is_array($data['style_settings'])) {
            $data['style_settings'] = json_encode($data['style_settings']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Get widgets by hotel
     */
    public function getByHotel($hotelId)
    {
        return $this->where('hotel_id', $hotelId);
    }
    
    /**
     * Get widgets by type
     */
    public function getByType($type)
    {
        return $this->where('type', $type);
    }
    
    /**
     * Get active widgets
     */
    public function getActive()
    {
        return $this->where('is_active', 1);
    }
    
    /**
     * Find widget by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', $code);
    }
    
    /**
     * Activate widget
     */
    public function activate($widgetId)
    {
        return $this->update($widgetId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate widget
     */
    public function deactivate($widgetId)
    {
        return $this->update($widgetId, ['is_active' => 0]);
    }
    
    /**
     * Update widget settings
     */
    public function updateSettings($widgetId, $settings)
    {
        $settingsJson = is_array($settings) ? json_encode($settings) : $settings;
        return $this->update($widgetId, ['settings' => $settingsJson]);
    }
    
    /**
     * Update widget style settings
     */
    public function updateStyleSettings($widgetId, $styleSettings)
    {
        $styleJson = is_array($styleSettings) ? json_encode($styleSettings) : $styleSettings;
        return $this->update($widgetId, ['style_settings' => $styleJson]);
    }
    
    /**
     * Get widget's hotel (relationship)
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }
    
    /**
     * Get widget with decoded settings
     */
    public function getWithSettings($widgetId)
    {
        $widget = $this->find($widgetId);
        
        if ($widget) {
            $widget['settings'] = $widget['settings'] ? json_decode($widget['settings'], true) : [];
            $widget['style_settings'] = $widget['style_settings'] ? json_decode($widget['style_settings'], true) : [];
        }
        
        return $widget;
    }
    
    /**
     * Get widgets with hotel information
     */
    public function getWithHotel($filters = [])
    {
        $sql = "SELECT w.*, h.name as hotel_name, h.code as hotel_code, u.namesurname as user_name 
                FROM {$this->table} w 
                JOIN hotels h ON w.hotel_id = h.id 
                JOIN users u ON h.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['hotel_id'])) {
            $sql .= " AND w.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (isset($filters['user_id'])) {
            $sql .= " AND h.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['type'])) {
            $sql .= " AND w.type = ?";
            $params[] = $filters['type'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND w.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get widget statistics
     */
    public function getStats($filters = [])
    {
        $whereClause = "1=1";
        $params = [];
        
        if (isset($filters['hotel_id'])) {
            $whereClause .= " AND hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (isset($filters['user_id'])) {
            $whereClause .= " AND hotel_id IN (SELECT id FROM hotels WHERE user_id = ?)";
            $params[] = $filters['user_id'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN type = 'booking' THEN 1 ELSE 0 END) as booking_widgets,
                    SUM(CASE WHEN type = 'rates' THEN 1 ELSE 0 END) as rate_widgets,
                    SUM(CASE WHEN type = 'availability' THEN 1 ELSE 0 END) as availability_widgets
                FROM {$this->table} 
                WHERE {$whereClause}";
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Search widgets
     */
    public function search($query, $filters = [])
    {
        $sql = "SELECT w.*, h.name as hotel_name 
                FROM {$this->table} w 
                JOIN hotels h ON w.hotel_id = h.id 
                WHERE 1=1";
        $params = [];
        
        if ($query) {
            $sql .= " AND (w.name LIKE ? OR w.code LIKE ? OR h.name LIKE ?)";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
        }
        
        if (isset($filters['hotel_id'])) {
            $sql .= " AND w.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (isset($filters['type'])) {
            $sql .= " AND w.type = ?";
            $params[] = $filters['type'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND w.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY w.name";
        
        return $this->raw($sql, $params);
    }
}
