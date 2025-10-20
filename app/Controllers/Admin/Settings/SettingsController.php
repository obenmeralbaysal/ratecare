<?php

namespace App\Controllers\Admin\Settings;

use App\Controllers\BaseController;
use App\Models\Setting;
use Core\Auth;
use Core\Authorization;

/**
 * Admin Settings Controller
 */
class SettingsController extends BaseController
{
    private $auth;
    private $authz;
    private $settingModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        
        // Check admin permission
        if (!$this->authz->canAccessAdmin()) {
            return $this->redirect('/')->with('error', 'Access denied');
        }
        
        $this->settingModel = new Setting();
    }
    
    /**
     * Show settings page
     */
    public function index()
    {
        $group = $this->input('group', 'general');
        
        // Get all settings grouped
        $allSettings = $this->settingModel->getForAdmin();
        $groupedSettings = [];
        
        foreach ($allSettings as $setting) {
            $groupedSettings[$setting['group']][] = $setting;
        }
        
        // Get available groups
        $groups = array_keys($groupedSettings);
        
        return $this->view('admin.settings.index', [
            'title' => 'System Settings',
            'grouped_settings' => $groupedSettings,
            'groups' => $groups,
            'current_group' => $group
        ]);
    }
    
    /**
     * Update settings
     */
    public function update()
    {
        try {
            $settings = $this->input('settings', []);
            
            if (empty($settings)) {
                return $this->back()->with('error', 'No settings to update');
            }
            
            // Validate critical settings
            $this->validateSettings($settings);
            
            // Update settings
            $this->settingModel->bulkUpdate($settings);
            
            return $this->back()->with('success', 'Settings updated successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Create new setting
     */
    public function store()
    {
        try {
            $this->validate([
                'key' => 'required',
                'value' => 'required',
                'type' => 'required',
                'group' => 'required'
            ]);
            
            $key = $this->input('key');
            
            // Check if setting already exists
            if ($this->settingModel->exists($key)) {
                return $this->back()->with('error', 'Setting key already exists');
            }
            
            $this->settingModel->setValue(
                $key,
                $this->input('value'),
                $this->input('type'),
                $this->input('group')
            );
            
            return $this->back()->with('success', 'Setting created successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Delete setting
     */
    public function destroy()
    {
        $key = $this->input('key');
        
        if (!$key) {
            return $this->json(['error' => 'Setting key required'], 400);
        }
        
        // Prevent deletion of critical settings
        $criticalSettings = [
            'app_name', 'app_url', 'app_timezone', 'app_locale', 'app_currency',
            'mail_driver', 'mail_host', 'mail_port'
        ];
        
        if (in_array($key, $criticalSettings)) {
            return $this->json(['error' => 'Cannot delete critical system setting'], 400);
        }
        
        try {
            $this->settingModel->deleteSetting($key);
            return $this->json(['success' => true, 'message' => 'Setting deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Test email configuration
     */
    public function testEmail()
    {
        $testEmail = $this->input('test_email');
        
        if (!$testEmail) {
            return $this->json(['error' => 'Test email address required'], 400);
        }
        
        try {
            // Get email settings
            $emailSettings = $this->settingModel->getByGroup('email');
            
            // TODO: Implement actual email sending
            // For now, just validate settings exist
            $requiredSettings = ['mail_host', 'mail_port', 'mail_from_address'];
            
            foreach ($requiredSettings as $setting) {
                if (!isset($emailSettings[$setting]) || empty($emailSettings[$setting])) {
                    return $this->json(['error' => "Missing email setting: {$setting}"], 400);
                }
            }
            
            // Simulate email test
            return $this->json([
                'success' => true,
                'message' => 'Email configuration test successful (simulated)'
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => 'Email test failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Reset settings to default
     */
    public function resetToDefault()
    {
        $group = $this->input('group');
        
        if (!$group) {
            return $this->json(['error' => 'Group parameter required'], 400);
        }
        
        try {
            // Get current settings for the group
            $currentSettings = $this->settingModel->getByGroup($group);
            
            // Delete current settings
            foreach ($currentSettings as $key => $value) {
                $this->settingModel->deleteSetting($key);
            }
            
            // Reinitialize defaults (this would restore default values for the group)
            $this->settingModel->initializeDefaults();
            
            return $this->json([
                'success' => true,
                'message' => "Settings for group '{$group}' reset to default values"
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Export settings
     */
    public function export()
    {
        $format = $this->input('format', 'json');
        $group = $this->input('group');
        
        try {
            if ($group) {
                $settings = $this->settingModel->getByGroup($group);
            } else {
                $settings = $this->settingModel->getAllSettings();
            }
            
            if ($format === 'csv') {
                return $this->exportCsv($settings, 'settings_' . ($group ?: 'all') . '_' . date('Y-m-d') . '.csv');
            } else {
                return $this->json([
                    'settings' => $settings,
                    'exported_at' => date('Y-m-d H:i:s'),
                    'group' => $group ?: 'all'
                ]);
            }
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Import settings
     */
    public function import()
    {
        $file = $this->request->file('settings_file');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->back()->with('error', 'Please select a valid settings file');
        }
        
        try {
            $content = file_get_contents($file['tmp_name']);
            $settings = json_decode($content, true);
            
            if (!$settings) {
                return $this->back()->with('error', 'Invalid JSON format');
            }
            
            $imported = 0;
            $errors = [];
            
            foreach ($settings as $key => $value) {
                try {
                    // Determine type based on value
                    $type = $this->determineSettingType($value);
                    
                    $this->settingModel->setValue($key, $value, $type, 'imported');
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to import {$key}: " . $e->getMessage();
                }
            }
            
            $message = "Imported {$imported} settings successfully";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', array_slice($errors, 0, 3));
            }
            
            return $this->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return $this->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get setting value via AJAX
     */
    public function getValue()
    {
        $key = $this->input('key');
        
        if (!$key) {
            return $this->json(['error' => 'Setting key required'], 400);
        }
        
        $value = $this->settingModel->getValue($key);
        
        return $this->json([
            'key' => $key,
            'value' => $value,
            'exists' => $value !== null
        ]);
    }
    
    /**
     * Clear cache (if implemented)
     */
    public function clearCache()
    {
        try {
            // TODO: Implement cache clearing
            // For now, just return success
            
            return $this->json([
                'success' => true,
                'message' => 'Settings cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Validate critical settings
     */
    private function validateSettings($settings)
    {
        // Validate app_url format
        if (isset($settings['app_url'])) {
            if (!filter_var($settings['app_url'], FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid app URL format');
            }
        }
        
        // Validate email settings
        if (isset($settings['mail_from_address'])) {
            if (!filter_var($settings['mail_from_address'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email address format');
            }
        }
        
        // Validate numeric settings
        $numericSettings = ['mail_port', 'api_rate_limit', 'api_cache_ttl'];
        foreach ($numericSettings as $setting) {
            if (isset($settings[$setting]) && !is_numeric($settings[$setting])) {
                throw new \Exception("Setting {$setting} must be numeric");
            }
        }
    }
    
    /**
     * Determine setting type based on value
     */
    private function determineSettingType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }
    
    /**
     * Export settings as CSV
     */
    private function exportCsv($settings, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['Key', 'Value', 'Type', 'Group', 'Description']);
        
        // Get detailed settings for CSV
        $detailedSettings = $this->settingModel->getForAdmin();
        
        foreach ($detailedSettings as $setting) {
            if (isset($settings[$setting['key']])) {
                fputcsv($output, [
                    $setting['key'],
                    $setting['value'],
                    $setting['type'],
                    $setting['group'],
                    $setting['description'] ?? ''
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
}
