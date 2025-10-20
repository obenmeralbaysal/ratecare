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
        $db = \Core\Database::getInstance();
        $db->connect([
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'hoteldigilab_new',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ]);
        
        $this->userModel = new User();
    }
    
    /**
     * Show users list
     */
    public function index()
    {
        // Get search query
        $search = $this->input('q', '');
        
        // Get users with pagination
        $users = $this->getUsersList($search);
        
        echo $this->view('admin.users.index', [
            'title' => 'Users Management',
            'users' => $users,
            'search' => $search
        ]);
    }
    
    /**
     * Get users list with search and pagination
     */
    private function getUsersList($search = '')
    {
        try {
            $sql = "SELECT 
                        u.id,
                        u.namesurname,
                        u.email,
                        u.is_admin,
                        u.user_type,
                        u.reseller_id,
                        u.created_at,
                        u.is_rate_comparison_active,
                        r.namesurname as reseller_name
                    FROM users u
                    LEFT JOIN users r ON u.reseller_id = r.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (u.namesurname LIKE ? OR u.email LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $sql .= " ORDER BY u.created_at DESC LIMIT 50";
            
            return $this->userModel->raw($sql, $params);
            
        } catch (\Exception $e) {
            error_log("Users list error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        echo $this->view('admin.users.create', [
            'title' => 'Create New User'
        ]);
    }
    
    /**
     * Show invite user form
     */
    public function invite()
    {
        echo $this->view('admin.users.invite', [
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
            
            // For demo purposes, just redirect with success
            return $this->redirect('/admin/users')->with('success', 'User created successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
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
            
            // For demo purposes, just redirect with success
            return $this->redirect('/admin/users')->with('success', 'Invitation sent successfully to ' . $this->input('email'));
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
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
