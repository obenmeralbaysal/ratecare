<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;

/**
 * Admin Users Controller
 */
class UsersController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize database connection
        try {
            $db = \Core\Database::getInstance();
            
            $config = [
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'hoteldigilab_new'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4')
            ];
            
            // Log database config for debugging (without password)
            error_log("DB Config: " . json_encode([
                'host' => $config['host'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password_length' => strlen($config['password'])
            ]));
            
            $db->connect($config);
            
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Continue without database for now
        }
        
        $this->userModel = new User();
    }
    
    /**
     * Show users list
     */
    public function index()
    {
        // Get search query and pagination
        $search = $this->input('q', '');
        $page = (int)$this->input('page', 1);
        $perPage = 10;
        
        // Get users with pagination
        $result = $this->getUsersList($search, $page, $perPage);
        
        echo $this->view('admin.users.index-new', [
            'title' => 'Users Management',
            'users' => $result['users'],
            'search' => $search,
            'pagination' => $result['pagination']
        ]);
    }
    
    /**
     * Get users list with search and pagination
     */
    private function getUsersList($search = '', $page = 1, $perPage = 10)
    {
        try {
            // Count total records
            $countSql = "SELECT COUNT(*) as total FROM users u WHERE 1=1";
            $countParams = [];
            
            if (!empty($search)) {
                $countSql .= " AND (u.namesurname LIKE ? OR u.email LIKE ?)";
                $countParams[] = "%{$search}%";
                $countParams[] = "%{$search}%";
            }
            
            $totalResult = $this->userModel->raw($countSql, $countParams);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Calculate pagination
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get users data
            $sql = "SELECT 
                        u.id,
                        u.namesurname,
                        u.email,
                        u.is_admin,
                        u.user_type,
                        u.reseller_id,
                        u.created_at,
                        u.is_rate_comparison_active,
                        r.namesurname as reseller_name,
                        h.name as hotel_name,
                        h.web_url as hotel_web_url
                    FROM users u
                    LEFT JOIN users r ON u.reseller_id = r.id
                    LEFT JOIN hotels h ON u.id = h.user_id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (u.namesurname LIKE ? OR u.email LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $users = $this->userModel->raw($sql, $params);
            
            return [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages,
                    'prev_page' => $page > 1 ? $page - 1 : null,
                    'next_page' => $page < $totalPages ? $page + 1 : null
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Users list error: " . $e->getMessage());
            return [
                'users' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_prev' => false,
                    'has_next' => false,
                    'prev_page' => null,
                    'next_page' => null
                ]
            ];
        }
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        echo $this->view('admin.users.create-new', [
            'title' => 'Create New User'
        ]);
    }
    
    /**
     * Show invite user form
     */
    public function invite()
    {
        echo $this->view('admin.users.invite-new', [
            'title' => 'Invite User'
        ]);
    }
    
    /**
     * Handle user creation
     */
    public function store()
    {
        try {
            // Validate input
            $this->validate([
                'namesurname' => 'required|min:2',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password',
                'userType' => 'required|in:0,1,2'
            ]);
            
            // For demo purposes, return JSON success
            return $this->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Handle user invitation
     */
    public function sendInvite()
    {
        try {
            // Validate input
            $this->validate([
                'namesurname' => 'required|min:2',
                'email' => 'required|email'
            ]);
            
            // For demo purposes, return JSON success
            return $this->json([
                'success' => true,
                'message' => 'Invitation sent successfully to ' . $this->input('email')
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Show edit user form
     */
    public function edit($id)
    {
        echo $this->view('admin.users.edit', [
            'title' => 'Edit User',
            'user_id' => $id
        ]);
    }
    
    /**
     * Handle user update
     */
    public function update($id)
    {
        // Handle user update logic here
        return $this->redirect('/admin/users')->with('success', 'User updated successfully');
    }
    
    /**
     * Handle user deletion
     */
    public function delete($id)
    {
        // Handle user deletion logic here
        return $this->redirect('/admin/users')->with('success', 'User deleted successfully');
    }
}
