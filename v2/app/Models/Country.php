<?php

namespace App\Models;

/**
 * Country Model
 */
class Country extends BaseModel
{
    protected $table = 'countries';
    protected $fillable = [
        'name', 'code', 'code3', 'phone_code', 'currency', 'is_active'
    ];
    
    /**
     * Get active countries
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC');
    }
    
    /**
     * Find country by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', strtoupper($code));
    }
    
    /**
     * Find country by 3-letter code
     */
    public function findByCode3($code3)
    {
        return $this->whereFirst('code3', strtoupper($code3));
    }
    
    /**
     * Get countries by currency
     */
    public function getByCurrency($currency)
    {
        return $this->where('currency', strtoupper($currency));
    }
    
    /**
     * Search countries
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR code LIKE ? OR code3 LIKE ?
                ORDER BY name ASC";
        
        $searchTerm = "%{$query}%";
        return $this->raw($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get country list for dropdown
     */
    public function getDropdownList()
    {
        $countries = $this->getActive();
        $list = [];
        
        foreach ($countries as $country) {
            $list[$country['code']] = $country['name'];
        }
        
        return $list;
    }
    
    /**
     * Get hotels in this country
     */
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel', 'country', 'code');
    }
    
    /**
     * Activate country
     */
    public function activate($countryId)
    {
        return $this->update($countryId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate country
     */
    public function deactivate($countryId)
    {
        return $this->update($countryId, ['is_active' => 0]);
    }
}
