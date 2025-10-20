<?php

namespace App\Controllers;

/**
 * Test Controller for basic functionality testing
 */
class TestController extends BaseController
{
    public function index()
    {
        echo "Test page works!";
    }
    
    public function api()
    {
        echo $this->view('test.api', [
            'title' => 'API Test'
        ]);
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
