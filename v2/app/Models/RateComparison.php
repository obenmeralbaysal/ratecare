<?php

namespace App\Models;

/**
 * Rate Comparison Model
 */
class RateComparison extends BaseModel
{
    protected $table = 'rate_comparisons';
    protected $fillable = [
        'hotel_id', 'check_in', 'check_out', 'adults', 'children', 'room_type',
        'comparison_data', 'best_price', 'best_source', 'currency', 'cached_at'
    ];
    
    /**
     * Get cached comparison
     */
    public function getCached($hotelId, $checkIn, $checkOut, $adults = 2, $children = 0)
    {
        $cacheExpiry = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE hotel_id = ? AND check_in = ? AND check_out = ? 
                AND adults = ? AND children = ? AND cached_at > ?
                ORDER BY cached_at DESC LIMIT 1";
        
        return $this->db->selectOne($sql, [$hotelId, $checkIn, $checkOut, $adults, $children, $cacheExpiry]);
    }
    
    /**
     * Store comparison result
     */
    public function storeComparison($hotelId, $checkIn, $checkOut, $comparisonData, $options = [])
    {
        $adults = $options['adults'] ?? 2;
        $children = $options['children'] ?? 0;
        $roomType = $options['room_type'] ?? null;
        $currency = $options['currency'] ?? 'USD';
        
        // Find best price
        $bestPrice = null;
        $bestSource = null;
        
        foreach ($comparisonData as $source => $data) {
            if (isset($data['price']) && ($bestPrice === null || $data['price'] < $bestPrice)) {
                $bestPrice = $data['price'];
                $bestSource = $source;
            }
        }
        
        return $this->create([
            'hotel_id' => $hotelId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'room_type' => $roomType,
            'comparison_data' => json_encode($comparisonData),
            'best_price' => $bestPrice,
            'best_source' => $bestSource,
            'currency' => $currency,
            'cached_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get comparison with decoded data
     */
    public function getWithData($comparisonId)
    {
        $comparison = $this->find($comparisonId);
        
        if ($comparison) {
            $comparison['comparison_data'] = json_decode($comparison['comparison_data'], true);
        }
        
        return $comparison;
    }
    
    /**
     * Clean old comparisons
     */
    public function cleanOld($hours = 24)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE cached_at < ?",
            [$cutoff]
        );
    }
    
    /**
     * Get hotel relationship
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }
}
