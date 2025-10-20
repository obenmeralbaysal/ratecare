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
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $response = Response::getInstance();
            $response->redirect('/login');
            return false;
        }
        
        return true;
    }
}
