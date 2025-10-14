<?php

namespace App\Models;

/**
 * Setting Model
 */
class Setting extends BaseModel
{
    protected $table = 'settings';
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'is_public'
    ];
    
    /**
     * Get setting value by key
     */
    public function getValue($key, $default = null)
    {
        $setting = $this->whereFirst('key', $key);
        
        if (!$setting) {
            return $default;
        }
        
        return $this->castValue($setting['value'], $setting['type']);
    }
    
    /**
     * Set setting value
     */
    public function setValue($key, $value, $type = 'string', $group = 'general')
    {
        $existing = $this->whereFirst('key', $key);
        
        $data = [
            'key' => $key,
            'value' => $this->prepareValue($value, $type),
            'type' => $type,
            'group' => $group
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Get settings by group
     */
    public function getByGroup($group)
    {
        $settings = $this->where('group', $group);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get all settings as key-value pairs
     */
    public function getAllSettings()
    {
        $settings = $this->all();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get public settings (for frontend)
     */
    public function getPublicSettings()
    {
        $settings = $this->where('is_public', 1);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
                
            case 'integer':
                return (int) $value;
                
            case 'float':
                return (float) $value;
                
            case 'json':
                return json_decode($value, true);
                
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
                
            default:
                return $value;
        }
    }
    
    /**
     * Prepare value for storage
     */
    private function prepareValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
                
            case 'json':
            case 'array':
                return is_string($value) ? $value : json_encode($value);
                
            default:
                return (string) $value;
        }
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key)
    {
        return $this->db->delete("DELETE FROM {$this->table} WHERE key = ?", [$key]);
    }
    
    /**
     * Check if setting exists
     */
    public function exists($key)
    {
        return $this->whereFirst('key', $key) !== null;
    }
    
    /**
     * Get settings for admin panel
     */
    public function getForAdmin()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY `group`, `key`";
        return $this->raw($sql);
    }
    
    /**
     * Bulk update settings
     */
    public function bulkUpdate($settings)
    {
        foreach ($settings as $key => $value) {
            $existing = $this->whereFirst('key', $key);
            
            if ($existing) {
                $this->update($existing['id'], [
                    'value' => $this->prepareValue($value, $existing['type'])
                ]);
            }
        }
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        $defaults = [
            // General settings
            'app_name' => ['Hotel DigiLab', 'string', 'general', 'Application name', true],
            'app_url' => ['http://localhost', 'string', 'general', 'Application URL', false],
            'app_timezone' => ['UTC', 'string', 'general', 'Application timezone', false],
            'app_locale' => ['en', 'string', 'general', 'Default language', true],
            'app_currency' => ['USD', 'string', 'general', 'Default currency', true],
            
            // Email settings
            'mail_driver' => ['smtp', 'string', 'email', 'Mail driver', false],
            'mail_host' => ['localhost', 'string', 'email', 'SMTP host', false],
            'mail_port' => ['587', 'integer', 'email', 'SMTP port', false],
            'mail_username' => ['', 'string', 'email', 'SMTP username', false],
            'mail_password' => ['', 'string', 'email', 'SMTP password', false],
            'mail_from_address' => ['noreply@hoteldigilab.com', 'string', 'email', 'From email address', false],
            'mail_from_name' => ['Hotel DigiLab', 'string', 'email', 'From name', false],
            
            // API settings
            'api_rate_limit' => ['1000', 'integer', 'api', 'API rate limit per hour', false],
            'api_cache_ttl' => ['3600', 'integer', 'api', 'API cache TTL in seconds', false],
            
            // Widget settings
            'widget_cache_ttl' => ['1800', 'integer', 'widget', 'Widget cache TTL in seconds', false],
            'widget_max_per_hotel' => ['50', 'integer', 'widget', 'Maximum widgets per hotel', false],
            
            // Rate settings
            'rate_update_interval' => ['3600', 'integer', 'rates', 'Rate update interval in seconds', false],
            'rate_sources' => ['["booking.com", "expedia.com", "hotels.com"]', 'json', 'rates', 'Available rate sources', false],
        ];
        
        foreach ($defaults as $key => $config) {
            if (!$this->exists($key)) {
                $this->create([
                    'key' => $key,
                    'value' => $config[0],
                    'type' => $config[1],
                    'group' => $config[2],
                    'description' => $config[3],
                    'is_public' => $config[4]
                ]);
            }
        }
    }
}
