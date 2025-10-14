<?php

namespace App\Models;

/**
 * Rate Model
 */
class Rate extends BaseModel
{
    protected $table = 'rates';
    protected $fillable = [
        'hotel_id', 'room_type', 'check_in', 'check_out', 'adults', 'children',
        'price', 'currency', 'source', 'room_details', 'breakfast_included',
        'free_cancellation', 'booking_url'
    ];
    
    /**
     * Get rates by hotel
     */
    public function getByHotel($hotelId, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE hotel_id = ?";
        $params = [$hotelId];
        
        if (isset($filters['check_in'])) {
            $sql .= " AND check_in >= ?";
            $params[] = $filters['check_in'];
        }
        
        if (isset($filters['check_out'])) {
            $sql .= " AND check_out <= ?";
            $params[] = $filters['check_out'];
        }
        
        if (isset($filters['currency'])) {
            $sql .= " AND currency = ?";
            $params[] = $filters['currency'];
        }
        
        if (isset($filters['source'])) {
            $sql .= " AND source = ?";
            $params[] = $filters['source'];
        }
        
        $sql .= " ORDER BY price ASC";
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get rates for date range
     */
    public function getForDateRange($checkIn, $checkOut, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE check_in >= ? AND check_out <= ?";
        $params = [$checkIn, $checkOut];
        
        if (isset($filters['hotel_id'])) {
            $sql .= " AND hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (isset($filters['currency'])) {
            $sql .= " AND currency = ?";
            $params[] = $filters['currency'];
        }
        
        $sql .= " ORDER BY price ASC";
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get lowest rates by hotel
     */
    public function getLowestRates($hotelId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE hotel_id = ? ORDER BY price ASC LIMIT ?";
        return $this->raw($sql, [$hotelId, $limit]);
    }
    
    /**
     * Get rates by source
     */
    public function getBySource($source)
    {
        return $this->where('source', $source);
    }
    
    /**
     * Get rates with breakfast
     */
    public function getWithBreakfast($hotelId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE breakfast_included = 1";
        $params = [];
        
        if ($hotelId) {
            $sql .= " AND hotel_id = ?";
            $params[] = $hotelId;
        }
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get rates with free cancellation
     */
    public function getWithFreeCancellation($hotelId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE free_cancellation = 1";
        $params = [];
        
        if ($hotelId) {
            $sql .= " AND hotel_id = ?";
            $params[] = $hotelId;
        }
        
        return $this->raw($sql, $params);
    }
    
    /**
     * Get rate statistics
     */
    public function getStats($hotelId = null)
    {
        $whereClause = $hotelId ? "WHERE hotel_id = ?" : "";
        $params = $hotelId ? [$hotelId] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total_rates,
                    MIN(price) as min_price,
                    MAX(price) as max_price,
                    AVG(price) as avg_price,
                    COUNT(DISTINCT source) as sources_count,
                    SUM(CASE WHEN breakfast_included = 1 THEN 1 ELSE 0 END) as with_breakfast,
                    SUM(CASE WHEN free_cancellation = 1 THEN 1 ELSE 0 END) as free_cancellation
                FROM {$this->table} {$whereClause}";
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Get rate comparison
     */
    public function compareRates($hotelId, $checkIn, $checkOut, $adults = 2, $children = 0)
    {
        $sql = "SELECT source, MIN(price) as min_price, currency, COUNT(*) as rate_count
                FROM {$this->table} 
                WHERE hotel_id = ? AND check_in = ? AND check_out = ? AND adults = ? AND children = ?
                GROUP BY source, currency
                ORDER BY min_price ASC";
        
        return $this->raw($sql, [$hotelId, $checkIn, $checkOut, $adults, $children]);
    }
    
    /**
     * Get hotel relationship
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }
    
    /**
     * Search rates
     */
    public function search($query, $filters = [])
    {
        $sql = "SELECT r.*, h.name as hotel_name 
                FROM {$this->table} r 
                JOIN hotels h ON r.hotel_id = h.id 
                WHERE 1=1";
        $params = [];
        
        if ($query) {
            $sql .= " AND (h.name LIKE ? OR r.room_type LIKE ? OR r.source LIKE ?)";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
        }
        
        if (isset($filters['hotel_id'])) {
            $sql .= " AND r.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (isset($filters['check_in'])) {
            $sql .= " AND r.check_in >= ?";
            $params[] = $filters['check_in'];
        }
        
        if (isset($filters['check_out'])) {
            $sql .= " AND r.check_out <= ?";
            $params[] = $filters['check_out'];
        }
        
        if (isset($filters['min_price'])) {
            $sql .= " AND r.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $sql .= " AND r.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        $sql .= " ORDER BY r.price ASC";
        
        return $this->raw($sql, $params);
    }
}
