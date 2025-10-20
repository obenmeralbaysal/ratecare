<?php

namespace App\Models;

/**
 * Widget Model
 */
class Widget extends BaseModel
{
    protected $table = 'widgets';
    protected $fillable = [
        'hotel_id', 'code', 'type', 'name', 'settings', 'is_active'
    ];
    
    /**
     * Find widget by code
     */
    public function findByCode($code, $type = 'main')
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? AND type = ? LIMIT 1";
        return $this->raw($sql, [$code, $type]);
    }
    
    /**
     * Get widgets by hotel ID
     */
    public function getByHotelId($hotelId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE hotel_id = ?";
        return $this->raw($sql, [$hotelId]);
    }
    
    /**
     * Get active widgets
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        return $this->raw($sql);
    }
    
    /**
     * Generate unique widget code
     */
    public function generateCode($length = 12)
    {
        do {
            $code = $this->randomString($length);
            $exists = $this->findByCode($code);
        } while (!empty($exists));
        
        return $code;
    }
    
    /**
     * Generate random string
     */
    private function randomString($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Create new widget
     */
    public function createWidget($data)
    {
        if (!isset($data['code'])) {
            $data['code'] = $this->generateCode();
        }
        
        if (!isset($data['type'])) {
            $data['type'] = 'main';
        }
        
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Update widget
     */
    public function updateWidget($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    /**
     * Get widget with hotel info
     */
    public function getWithHotel($widgetId)
    {
        $sql = "SELECT w.*, h.name as hotel_name, h.web_url as hotel_web_url 
                FROM {$this->table} w 
                LEFT JOIN hotels h ON w.hotel_id = h.id 
                WHERE w.id = ? LIMIT 1";
        return $this->raw($sql, [$widgetId]);
    }
    
    /**
     * Get widgets with hotel info
     */
    public function getAllWithHotel()
    {
        $sql = "SELECT w.*, h.name as hotel_name, h.web_url as hotel_web_url,
                       u.namesurname as user_name
                FROM {$this->table} w 
                LEFT JOIN hotels h ON w.hotel_id = h.id 
                LEFT JOIN users u ON h.user_id = u.id
                ORDER BY w.created_at DESC";
        return $this->raw($sql);
    }
}
