<?php

namespace App\Models;

/**
 * Rate Channel Model
 */
class RateChannel extends BaseModel
{
    protected $table = 'rate_channels';
    protected $fillable = [
        'name', 'code', 'api_url', 'api_credentials', 'is_active', 'priority'
    ];
    
    /**
     * Get active channels
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('priority', 'DESC');
    }
    
    /**
     * Find channel by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', $code);
    }
    
    /**
     * Get API credentials (decoded)
     */
    public function getCredentials($channelId)
    {
        $channel = $this->find($channelId);
        
        if (!$channel || !$channel['api_credentials']) {
            return [];
        }
        
        return json_decode($channel['api_credentials'], true) ?: [];
    }
    
    /**
     * Update API credentials
     */
    public function updateCredentials($channelId, $credentials)
    {
        return $this->update($channelId, [
            'api_credentials' => json_encode($credentials)
        ]);
    }
    
    /**
     * Get rates from this channel
     */
    public function rates()
    {
        return $this->hasMany('App\Models\Rate', 'source', 'code');
    }
}
