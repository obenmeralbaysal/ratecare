<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Cache Management Controller (Admin)
 * Handles cache statistics page
 */
class CacheController extends BaseController
{
    /**
     * Display cache statistics page
     */
    public function statistics()
    {
        echo $this->view('admin/cache/statistics-new');
    }
    
    /**
     * Clear all cache
     */
    public function clear()
    {
        try {
            $cacheDir = __DIR__ . '/../../../storage/cache/';
            
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '*.json');
                $count = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $count++;
                    }
                }
                
                return $this->json([
                    'success' => true,
                    'message' => "Cache cleared successfully! ($count files deleted)"
                ]);
            }
            
            return $this->json([
                'success' => true,
                'message' => 'Cache directory not found or empty'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
