<?php

namespace App\Middleware;

use Core\CSRF;
use Core\Response;

/**
 * CSRF Protection Middleware
 */
class CSRFMiddleware extends BaseMiddleware
{
    public function handle()
    {
        $csrf = CSRF::getInstance();
        
        if (!$csrf->verifyRequest()) {
            $response = Response::getInstance();
            $response->status(419)->json(['error' => 'CSRF token mismatch']);
            return false;
        }
        
        return true;
    }
}
