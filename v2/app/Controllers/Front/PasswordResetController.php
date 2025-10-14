<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use Core\Hash;
use Core\Database;

/**
 * Password Reset Controller
 */
class PasswordResetController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    /**
     * Show password reset form
     */
    public function showResetForm($token)
    {
        // Verify token exists and is not expired
        $reset = $this->db->selectOne(
            "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$token]
        );
        
        if (!$reset) {
            return $this->redirect('/forgot-password')->with('error', 'Invalid or expired reset token');
        }
        
        return $this->view('front.auth.reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $reset['email']
        ]);
    }
    
    /**
     * Handle password reset
     */
    public function resetPassword()
    {
        try {
            $this->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'password_confirmation' => 'required'
            ]);
            
            $token = $this->input('token');
            $email = $this->input('email');
            $password = $this->input('password');
            $passwordConfirmation = $this->input('password_confirmation');
            
            // Check passwords match
            if ($password !== $passwordConfirmation) {
                return $this->back()->with('error', 'Passwords do not match');
            }
            
            // Verify token
            $reset = $this->db->selectOne(
                "SELECT * FROM password_resets WHERE token = ? AND email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [$token, $email]
            );
            
            if (!$reset) {
                return $this->back()->with('error', 'Invalid or expired reset token');
            }
            
            // Check if user exists
            $user = $this->db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
            
            if (!$user) {
                return $this->back()->with('error', 'User not found');
            }
            
            $this->db->beginTransaction();
            
            try {
                // Update password
                $this->db->update(
                    "UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?",
                    [Hash::make($password), $email]
                );
                
                // Delete used reset token
                $this->db->delete("DELETE FROM password_resets WHERE email = ?", [$email]);
                
                $this->db->commit();
                
                return $this->redirect('/login')->with('success', 'Password reset successfully. Please log in with your new password.');
                
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
}
