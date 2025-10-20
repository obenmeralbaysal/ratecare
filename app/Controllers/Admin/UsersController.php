<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Admin Users Controller
 */
class UsersController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Show users list
     */
    public function index()
    {
        echo $this->view('admin.users.index', [
            'title' => 'Users Management'
        ]);
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
