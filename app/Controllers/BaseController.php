<?php

namespace App\Controllers;

use Core\View;
use Core\Request;
use Core\Response;

/**
 * Base Controller Class
 * All controllers should extend this class
 */
abstract class BaseController
{
    protected $request;
    protected $response;
    protected $view;
    
    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        $this->view = View::getInstance();
    }
    
    /**
     * Render a view
     */
    protected function view($template, $data = [])
    {
        return $this->view->render($template, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $status = 200)
    {
        return $this->response->json($data, $status);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url, $status = 302)
    {
        return $this->response->redirect($url, $status);
    }
    
    /**
     * Redirect back
     */
    protected function back()
    {
        return $this->response->back();
    }
    
    /**
     * Get request input
     */
    protected function input($key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }
    
    /**
     * Validate request input
     */
    protected function validate($rules)
    {
        return $this->request->validate($rules);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get authenticated user ID
     */
    protected function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }
}
