<?php

namespace App\Controllers;

/**
 * Test Controller for basic functionality testing
 */
class TestController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Test Page',
            'message' => 'Framework-less MVC is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'features' => [
                'PSR-4 Autoloading',
                'MVC Architecture', 
                'Database Abstraction',
                'Template Engine',
                'Routing System',
                'Middleware Support',
                'Session Management',
                'CSRF Protection'
            ]
        ];
        
        return $this->json($data);
    }
    
    public function database()
    {
        try {
            $db = \Core\Database::getInstance();
            $result = $db->selectOne("SELECT 1 as test");
            
            return $this->json([
                'status' => 'success',
                'message' => 'Database connection working',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
