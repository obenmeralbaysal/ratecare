<?php

namespace Core;

/**
 * Authentication System
 */
class Auth
{
    private static $instance = null;
    private $session;
    private $db;
    private $user = null;
    
    private function __construct()
    {
        $this->session = Session::getInstance();
        $this->db = Database::getInstance();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Attempt to authenticate user
     */
    public function attempt($credentials)
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        
        if (!$email || !$password) {
            return false;
        }
        
        // Find user by email
        $user = $this->db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Check if user is active
        if (isset($user['is_active']) && !$user['is_active']) {
            return false;
        }
        
        // Login user
        $this->login($user);
        
        return true;
    }
    
    /**
     * Login user
     */
    public function login($user)
    {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_name', $user['namesurname'] ?? $user['name']);
        $this->session->set('is_admin', $user['is_admin'] ?? false);
        $this->session->set('reseller_id', $user['reseller_id'] ?? null);
        
        // Regenerate session ID for security
        $this->session->regenerate();
        
        // Update last login
        $this->db->update(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        $this->user = $user;
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->clear();
        $this->session->regenerate();
        $this->user = null;
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public function check()
    {
        return $this->session->has('user_id');
    }
    
    /**
     * Check if user is guest (not authenticated)
     */
    public function guest()
    {
        return !$this->check();
    }
    
    /**
     * Get authenticated user
     */
    public function user()
    {
        if ($this->user === null && $this->check()) {
            $userId = $this->session->get('user_id');
            $this->user = $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);
        }
        
        return $this->user;
    }
    
    /**
     * Get user ID
     */
    public function id()
    {
        return $this->session->get('user_id');
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($role)
    {
        $user = $this->user();
        
        if (!$user) {
            return false;
        }
        
        switch ($role) {
            case 'admin':
                return (bool) ($user['is_admin'] ?? false);
                
            case 'reseller':
                return !empty($user['reseller_id']) && $user['reseller_id'] == 0;
                
            case 'customer':
                return !empty($user['reseller_id']) && $user['reseller_id'] > 0;
                
            default:
                return false;
        }
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if user is reseller
     */
    public function isReseller()
    {
        return $this->hasRole('reseller');
    }
    
    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->hasRole('customer');
    }
    
    /**
     * Get user's reseller ID
     */
    public function getResellerId()
    {
        $user = $this->user();
        return $user['reseller_id'] ?? null;
    }
    
    /**
     * Check if user can access resource
     */
    public function can($permission, $resource = null)
    {
        $user = $this->user();
        
        if (!$user) {
            return false;
        }
        
        // Admin can do everything
        if ($this->isAdmin()) {
            return true;
        }
        
        // Define permissions based on roles
        $permissions = [
            'reseller' => [
                'view_users',
                'create_users', 
                'edit_users',
                'delete_users',
                'export_users'
            ],
            'customer' => [
                'view_dashboard',
                'manage_widgets',
                'view_rates',
                'view_statistics',
                'manage_hotels'
            ]
        ];
        
        if ($this->isReseller()) {
            return in_array($permission, $permissions['reseller']);
        }
        
        if ($this->isCustomer()) {
            return in_array($permission, $permissions['customer']);
        }
        
        return false;
    }
    
    /**
     * Switch to another user (admin/reseller feature)
     */
    public function switchToUser($userId)
    {
        $currentUser = $this->user();
        
        // Only admins and resellers can switch users
        if (!$this->isAdmin() && !$this->isReseller()) {
            return false;
        }
        
        $targetUser = $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$targetUser) {
            return false;
        }
        
        // Resellers can only switch to their customers
        if ($this->isReseller()) {
            $resellerId = $this->getResellerId();
            if ($targetUser['reseller_id'] != $resellerId) {
                return false;
            }
        }
        
        // Store original user info for switching back
        $this->session->set('original_user_id', $currentUser['id']);
        $this->session->set('switched_user', true);
        
        // Login as target user
        $this->login($targetUser);
        
        return true;
    }
    
    /**
     * Switch back to original user
     */
    public function switchBack()
    {
        if (!$this->session->has('switched_user')) {
            return false;
        }
        
        $originalUserId = $this->session->get('original_user_id');
        $originalUser = $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$originalUserId]);
        
        if (!$originalUser) {
            return false;
        }
        
        // Remove switch session data
        $this->session->remove('original_user_id');
        $this->session->remove('switched_user');
        
        // Login as original user
        $this->login($originalUser);
        
        return true;
    }
    
    /**
     * Check if currently switched to another user
     */
    public function isSwitched()
    {
        return $this->session->has('switched_user');
    }
}
