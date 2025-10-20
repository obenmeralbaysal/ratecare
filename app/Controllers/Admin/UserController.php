<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Auth;
use Core\Hash;
use Core\Database;
use Core\Authorization;

/**
 * Admin User Controller
 * Handles user management for admins
 */
class UserController extends BaseController
{
    private $auth;
    private $authz;
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        $this->db = Database::getInstance();
        
        // Check admin permission
        if (!$this->authz->canAccessAdmin()) {
            return $this->redirect('/')->with('error', 'Access denied');
        }
    }
    
    /**
     * List all users
     */
    public function index()
    {
        $page = (int) $this->input('page', 1);
        $search = $this->input('search', '');
        
        $whereClause = "1=1";
        $params = [];
        
        if ($search) {
            $whereClause .= " AND (namesurname LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        // Apply authorization filter
        $sql = "SELECT * FROM users WHERE " . $whereClause;
        $sql = $this->authz->filterQuery($sql, 'users');
        
        $users = $this->db->select($sql . " ORDER BY created_at DESC LIMIT 20 OFFSET " . (($page - 1) * 20), $params);
        
        $totalSql = "SELECT COUNT(*) as count FROM users WHERE " . $whereClause;
        $totalSql = $this->authz->filterQuery($totalSql, 'users');
        $total = $this->db->selectOne($totalSql, $params)['count'];
        
        return $this->view('admin.users.index', [
            'title' => 'User Management',
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total' => $total,
                'per_page' => 20,
                'last_page' => ceil($total / 20)
            ],
            'search' => $search
        ]);
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        return $this->view('admin.users.create', [
            'title' => 'Create User'
        ]);
    }
    
    /**
     * Store new user
     */
    public function store()
    {
        try {
            $this->validate([
                'namesurname' => 'required|min:3',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'is_admin' => 'numeric'
            ]);
            
            // Check if email already exists
            $existingUser = $this->db->selectOne("SELECT id FROM users WHERE email = ?", [$this->input('email')]);
            
            if ($existingUser) {
                return $this->back()->with('error', 'Email address already exists');
            }
            
            $userId = $this->db->insert(
                "INSERT INTO users (namesurname, email, password, is_admin, reseller_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $this->input('namesurname'),
                    $this->input('email'),
                    Hash::make($this->input('password')),
                    (int) $this->input('is_admin', 0),
                    $this->input('reseller_id', 0)
                ]
            );
            
            return $this->redirect('/admin/users')->with('success', 'User created successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Show edit user form
     */
    public function edit($id)
    {
        if (!$this->authz->canManageUsers($id)) {
            return $this->redirect('/admin/users')->with('error', 'Access denied');
        }
        
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (!$user) {
            return $this->redirect('/admin/users')->with('error', 'User not found');
        }
        
        return $this->view('admin.users.edit', [
            'title' => 'Edit User',
            'user' => $user
        ]);
    }
    
    /**
     * Update user
     */
    public function update($id)
    {
        if (!$this->authz->canManageUsers($id)) {
            return $this->redirect('/admin/users')->with('error', 'Access denied');
        }
        
        try {
            $this->validate([
                'namesurname' => 'required|min:3',
                'email' => 'required|email',
                'is_admin' => 'numeric'
            ]);
            
            // Check if email already exists (excluding current user)
            $existingUser = $this->db->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$this->input('email'), $id]);
            
            if ($existingUser) {
                return $this->back()->with('error', 'Email address already exists');
            }
            
            $updateData = [
                $this->input('namesurname'),
                $this->input('email'),
                (int) $this->input('is_admin', 0),
                $this->input('reseller_id', 0),
                $id
            ];
            
            $sql = "UPDATE users SET namesurname = ?, email = ?, is_admin = ?, reseller_id = ?, updated_at = NOW() WHERE id = ?";
            
            // Update password if provided
            if ($this->input('password')) {
                $this->validate(['password' => 'min:6']);
                $sql = "UPDATE users SET namesurname = ?, email = ?, password = ?, is_admin = ?, reseller_id = ?, updated_at = NOW() WHERE id = ?";
                array_splice($updateData, 2, 0, [Hash::make($this->input('password'))]);
            }
            
            $this->db->update($sql, $updateData);
            
            return $this->redirect('/admin/users')->with('success', 'User updated successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Delete user
     */
    public function destroy($id)
    {
        if (!$this->authz->canManageUsers($id)) {
            return $this->redirect('/admin/users')->with('error', 'Access denied');
        }
        
        // Prevent deleting self
        if ($id == $this->auth->id()) {
            return $this->redirect('/admin/users')->with('error', 'Cannot delete your own account');
        }
        
        try {
            $this->db->delete("DELETE FROM users WHERE id = ?", [$id]);
            return $this->redirect('/admin/users')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->redirect('/admin/users')->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
    
    /**
     * Show invite user form
     */
    public function invite()
    {
        return $this->view('admin.users.invite', [
            'title' => 'Invite User'
        ]);
    }
    
    /**
     * Send user invitation
     */
    public function postInvite()
    {
        try {
            $this->validate([
                'email' => 'required|email',
                'reseller_id' => 'numeric'
            ]);
            
            $email = $this->input('email');
            $resellerId = (int) $this->input('reseller_id', 0);
            
            // Check if user already exists
            $existingUser = $this->db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
            
            if ($existingUser) {
                return $this->back()->with('error', 'User with this email already exists');
            }
            
            // Check if invitation already exists
            $existingInvite = $this->db->selectOne("SELECT id FROM invites WHERE email = ? AND accepted = 0", [$email]);
            
            if ($existingInvite) {
                return $this->back()->with('error', 'Invitation already sent to this email');
            }
            
            // Create invitation
            $code = Hash::inviteCode();
            
            $this->db->insert(
                "INSERT INTO invites (email, code, reseller_id, invited_by, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$email, $code, $resellerId, $this->auth->id()]
            );
            
            // TODO: Send invitation email
            // For now, just show the invitation link
            $inviteUrl = Config::get('app.url') . '/invite/' . $code;
            
            return $this->back()->with('success', "Invitation sent! Link: {$inviteUrl}");
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Switch to user account
     */
    public function switchUser($id)
    {
        if (!$this->authz->canManageUsers($id)) {
            return $this->redirect('/admin/users')->with('error', 'Access denied');
        }
        
        if ($this->auth->switchToUser($id)) {
            return $this->redirect('/customer/dashboard')->with('success', 'Switched to user account');
        } else {
            return $this->redirect('/admin/users')->with('error', 'Failed to switch user');
        }
    }
}
