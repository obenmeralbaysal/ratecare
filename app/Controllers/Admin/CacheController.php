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
}
