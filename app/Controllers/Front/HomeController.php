<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use Core\Auth;
use Core\Hash;
use Core\Database;

/**
 * Front Home Controller
 * Handles login, registration, and public pages
 */
class HomeController extends BaseController
{
    private $auth;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = Auth::getInstance();
    }
    
    /**
     * Show login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            return $this->redirectToDashboard();
        }
        
        echo $this->view('front.login.simple_login', [
            'title' => 'Login - Hotel DigiLab'
        ]);
    }
    
    /**
     * Handle login form submission
     */
    public function postLogin()
    {
        try {
            // Validate input
            $this->validate([
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);
            
            $credentials = [
                'email' => $this->input('email'),
                'password' => $this->input('password')
            ];
            
            if ($this->auth->attempt($credentials)) {
                // Login successful - redirect to appropriate dashboard
                return $this->redirectToDashboard();
            } else {
                // Login failed
                return $this->back()->with('error', 'Invalid email or password');
            }
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Handle logout
     */
    public function logout()
    {
        $this->auth->logout();
        return $this->redirect('/')->with('success', 'You have been logged out successfully');
    }
    
    /**
     * Show forgot password page
     */
    public function forgotPassword()
    {
        return $this->view('front.login.forgot-password', [
            'title' => 'Forgot Password - Hotel DigiLab'
        ]);
    }
    
    /**
     * Handle forgot password form
     */
    public function postForgotPassword()
    {
        try {
            $this->validate([
                'email' => 'required|email'
            ]);
            
            $email = $this->input('email');
            $db = Database::getInstance();
            
            $user = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user) {
                // Generate reset token
                $token = Hash::resetToken();
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token
                $db->query(
                    "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?) 
                     ON DUPLICATE KEY UPDATE token = ?, created_at = ?",
                    [$email, $token, $expires, $token, $expires]
                );
                
                // TODO: Send email with reset link
                // For now, just show success message
                return $this->back()->with('success', 'Password reset link has been sent to your email');
            } else {
                return $this->back()->with('error', 'Email address not found');
            }
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Show invitation page
     */
    public function invite($code)
    {
        $db = Database::getInstance();
        $invitation = $db->selectOne("SELECT * FROM invites WHERE code = ? AND accepted = 0", [$code]);
        
        if (!$invitation) {
            return $this->redirect('/')->with('error', 'Invalid or expired invitation code');
        }
        
        return $this->view('front.login.new-user', [
            'title' => 'Create Account - Hotel DigiLab',
            'invitation' => $invitation
        ]);
    }
    
    /**
     * Handle account creation from invitation
     */
    public function createAccount()
    {
        try {
            $this->validate([
                'code' => 'required',
                'namesurname' => 'required|min:3',
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);
            
            $code = $this->input('code');
            $db = Database::getInstance();
            
            // Verify invitation
            $invitation = $db->selectOne("SELECT * FROM invites WHERE code = ? AND accepted = 0", [$code]);
            
            if (!$invitation) {
                return $this->back()->with('error', 'Invalid or expired invitation code');
            }
            
            // Check if email already exists
            $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ?", [$this->input('email')]);
            
            if ($existingUser) {
                return $this->back()->with('error', 'Email address already exists');
            }
            
            $db->beginTransaction();
            
            try {
                // Create user
                $userId = $db->insert(
                    "INSERT INTO users (namesurname, email, password, reseller_id, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())",
                    [
                        $this->input('namesurname'),
                        $this->input('email'),
                        Hash::make($this->input('password')),
                        $invitation['reseller_id']
                    ]
                );
                
                // Mark invitation as accepted
                $db->update(
                    "UPDATE invites SET accepted = 1, accepted_at = NOW() WHERE id = ?",
                    [$invitation['id']]
                );
                
                $db->commit();
                
                return $this->redirect('/')->with('success', 'Account created successfully. Please log in.');
                
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard()
    {
        if ($this->auth->isAdmin()) {
            return $this->redirect('/admin');
        } elseif ($this->auth->isReseller()) {
            return $this->redirect('/reseller');
        } else {
            return $this->redirect('/customer/dashboard');
        }
    }
}
