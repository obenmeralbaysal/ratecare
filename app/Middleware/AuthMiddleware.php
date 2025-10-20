<?php

namespace App\Middleware;

use Core\Response;

/**
 * Authentication Middleware
 */
class AuthMiddleware extends BaseMiddleware
{
    public function handle()
    {
        // Session is already started by Application class
        if (!isset($_SESSION['user_id'])) {
            $response = Response::getInstance();
            $response->redirect('/login');
            return false;
        }
        
        return true;
    }
}
