<?php

namespace App\Models;

/**
 * Language Model
 */
class Language extends BaseModel
{
    protected $table = 'languages';
    protected $fillable = [
        'name', 'code', 'native_name', 'is_active', 'is_default'
    ];
    
    /**
     * Get active languages
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC');
    }
    
    /**
     * Find language by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', strtolower($code));
    }
    
    /**
     * Get default language
     */
    public function getDefault()
    {
        return $this->whereFirst('is_default', 1);
    }
    
    /**
     * Set default language
     */
    public function setDefault($languageId)
    {
        // Remove default from all languages
        $this->db->update("UPDATE {$this->table} SET is_default = 0");
        
        // Set new default
        return $this->update($languageId, ['is_default' => 1]);
    }
    
    /**
     * Get language dropdown list
     */
    public function getDropdownList()
    {
        $languages = $this->getActive();
        $list = [];
        
        foreach ($languages as $language) {
            $displayName = $language['native_name'] ?: $language['name'];
            $list[$language['code']] = $displayName;
        }
        
        return $list;
    }
    
    /**
     * Get hotels using this language
     */
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel', 'language', 'code');
    }
    
    /**
     * Activate language
     */
    public function activate($languageId)
    {
        return $this->update($languageId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate language
     */
    public function deactivate($languageId)
    {
        return $this->update($languageId, ['is_active' => 0]);
    }
    
    /**
     * Get language statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    l.code,
                    l.name,
                    l.native_name,
                    COUNT(h.id) as hotel_count
                FROM {$this->table} l
                LEFT JOIN hotels h ON l.code = h.language
                WHERE l.is_active = 1
                GROUP BY l.id
                ORDER BY hotel_count DESC, l.name ASC";
        
        return $this->raw($sql);
    }
}
