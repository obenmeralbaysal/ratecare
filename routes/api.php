<?php

use Core\ApiRouter;

// API v1 routes
ApiRouter::version('v1')->group(['middleware' => ['throttle']], function() {
    
    // Public routes (no authentication required)
    ApiRouter::get('status', function() {
        return [
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => time(),
            'server_time' => date('Y-m-d H:i:s')
        ];
    });
    
    // Widget public routes
    ApiRouter::get('widgets/{id}/render', 'WidgetController@render');
    ApiRouter::get('widgets/{id}/embed', 'WidgetController@embed');
    ApiRouter::post('widgets/{id}/track', 'WidgetController@track');
    
    // Hotel public routes
    ApiRouter::get('hotels/search', 'HotelController@search');
    ApiRouter::get('hotels/{id}/rates', 'HotelController@rates');
    
    // Rate public routes
    ApiRouter::get('rates/search', 'RateController@search');
    ApiRouter::get('rates/compare', 'RateController@compare');
    
    // Cache statistics (public for dashboard)
    ApiRouter::get('cache/summary', 'Admin\CacheStatsController@summary');
    
    // Authentication routes
    ApiRouter::post('auth/login', 'AuthController@login');
    ApiRouter::post('auth/register', 'AuthController@register');
    ApiRouter::post('auth/forgot-password', 'AuthController@forgotPassword');
    ApiRouter::post('auth/reset-password', 'AuthController@resetPassword');
    
    // Protected routes (authentication required)
    ApiRouter::group(['middleware' => ['auth']], function() {
        
        // Auth user info
        ApiRouter::get('auth/user', 'AuthController@user');
        ApiRouter::post('auth/logout', 'AuthController@logout');
        ApiRouter::put('auth/profile', 'AuthController@updateProfile');
        ApiRouter::put('auth/password', 'AuthController@changePassword');
        
        // Widget management
        ApiRouter::resource('widgets', 'WidgetController');
        ApiRouter::get('widgets/{id}/statistics', 'WidgetController@statistics');
        
        // Hotel management
        ApiRouter::resource('hotels', 'HotelController');
        ApiRouter::get('hotels/{id}/statistics', 'HotelController@statistics');
        
        // Rate management
        ApiRouter::resource('rates', 'RateController');
        ApiRouter::post('rates/import', 'RateController@import');
        ApiRouter::get('rates/export', 'RateController@export');
        
        // Statistics
        ApiRouter::get('statistics/dashboard', 'StatisticController@dashboard');
        ApiRouter::get('statistics/widgets', 'StatisticController@widgets');
        ApiRouter::get('statistics/hotels', 'StatisticController@hotels');
        ApiRouter::get('statistics/performance', 'StatisticController@performance');
        
        // User management (customer level)
        ApiRouter::get('profile', 'UserController@profile');
        ApiRouter::put('profile', 'UserController@updateProfile');
        
        // Admin routes
        ApiRouter::group(['middleware' => ['admin']], function() {
            
            // User management (admin)
            ApiRouter::resource('users', 'UserController');
            ApiRouter::post('users/{id}/activate', 'UserController@activate');
            ApiRouter::post('users/{id}/deactivate', 'UserController@deactivate');
            ApiRouter::post('users/invite', 'UserController@invite');
            
            // System settings
            ApiRouter::resource('settings', 'SettingController');
            ApiRouter::get('settings/groups/{group}', 'SettingController@group');
            
            // System statistics
            ApiRouter::get('admin/statistics', 'AdminController@statistics');
            ApiRouter::get('admin/health', 'AdminController@health');
            ApiRouter::get('admin/logs', 'AdminController@logs');
            
            // Cache management
            ApiRouter::post('admin/cache/clear', 'AdminController@clearCache');
            ApiRouter::get('admin/cache/stats', 'AdminController@cacheStats');
            
            // System maintenance
            ApiRouter::post('admin/maintenance/enable', 'AdminController@enableMaintenance');
            ApiRouter::post('admin/maintenance/disable', 'AdminController@disableMaintenance');
        });
    });
});

// API v2 routes (future expansion)
ApiRouter::version('v2')->group(['middleware' => ['throttle']], function() {
    
    ApiRouter::get('status', function() {
        return [
            'status' => 'ok',
            'version' => '2.0.0',
            'message' => 'API v2 - Coming soon'
        ];
    });
    
});
