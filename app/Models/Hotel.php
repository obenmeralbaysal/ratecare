<?php

namespace App\Models;

use Core\Hash;

/**
 * Hotel Model
 */
class Hotel extends BaseModel
{
    protected $table = 'hotels';
    protected $fillable = [
        'user_id', 'name', 'code', 'address', 'city', 'country', 
        'phone', 'email', 'website', 'star_rating', 'description',
        'currency', 'language', 'is_active'
    ];
    
    /**
     * Create hotel with auto-generated code
     */
    public function createHotel($data)
    {
        if (!isset($data['code'])) {
            $data['code'] = Hash::hotelCode();
        }
        
        return $this->create($data);
    }
    
    /**
     * Get hotels by user
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId);
    }
    
    /**
     * Get active hotels
     */
    public function getActive()
    {
        return $this->where('is_active', 1);
    }
    
    /**
     * Get hotels by country
     */
    public function getByCountry($country)
    {
        return $this->where('country', $country);
    }
    
    /**
     * Get hotels by city
     */
    public function getByCity($city)
    {
        return $this->where('city', $city);
    }
    
    /**
     * Find hotel by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', $code);
    }
    
    /**
     * Activate hotel
     */
    public function activate($hotelId)
    {
        return $this->update($hotelId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate hotel
     */
    public function deactivate($hotelId)
    {
        return $this->update($hotelId, ['is_active' => 0]);
    }
    
    /**
     * Get hotel's user (relationship)
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    /**
     * Get hotel's widgets (relationship)
     */
    public function widgets()
    {
        return $this->hasMany('App\Models\Widget', 'hotel_id');
    }
    
    /**
     * Get hotel statistics
     */
    public function getStats($hotelId)
    {
        $hotel = $this->find($hotelId);
        
        if (!$hotel) {
            return null;
        }
        
        $widgetCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM widgets WHERE hotel_id = ?",
            [$hotelId]
        )['count'];
        
        $activeWidgetCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM widgets WHERE hotel_id = ? AND is_active = 1",
            [$hotelId]
        )['count'];
        
        return [
            'hotel' => $hotel,
            'widget_count' => $widgetCount,
            'active_widget_count' => $activeWidgetCount,
            'inactive_widget_count' => $widgetCount - $activeWidgetCount
        ];
    }
    
    /**
     * Search hotels
     */
    public function search($query, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($query) {
            $sql .= " AND (name LIKE ? OR city LIKE ? OR country LIKE ?)";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
        }
        
        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['country'])) {
            $sql .= " AND country = ?";
            $params[] = $filters['country'];
        }
        
        if (isset($filters['city'])) {
            $sql .= " AND city = ?";
            $params[] = $filters['city'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY name";
        
        return $this->raw($sql, $params);
    }
}
