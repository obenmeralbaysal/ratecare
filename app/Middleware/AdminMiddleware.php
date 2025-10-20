<?php

namespace App\Middleware;

use Core\Response;
use Core\Database;

/**
 * Admin Authorization Middleware
 */
class AdminMiddleware extends BaseMiddleware
{
    public function handle()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $response = Response::getInstance();
            $response->redirect('/login');
            return false;
        }
        
        // Check if user is admin
        $db = Database::getInstance();
        $user = $db->selectOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if (!$user || !$user['is_admin']) {
            $response = Response::getInstance();
            $response->status(403)->html('Access Denied');
            return false;
        }
        
        return true;
    }
}
