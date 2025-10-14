<?php

namespace App\Models;

use Core\Hash;

/**
 * User Model
 */
class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'namesurname', 'email', 'password', 'is_admin', 'reseller_id', 'is_active'
    ];
    
    /**
     * Create new user with hashed password
     */
    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($id, $newPassword)
    {
        return $this->update($id, [
            'password' => Hash::make($newPassword)
        ]);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->whereFirst('email', $email);
    }
    
    /**
     * Get users by reseller
     */
    public function getByReseller($resellerId)
    {
        return $this->where('reseller_id', $resellerId);
    }
    
    /**
     * Get admin users
     */
    public function getAdmins()
    {
        return $this->where('is_admin', 1);
    }
    
    /**
     * Get resellers (users with reseller_id = 0)
     */
    public function getResellers()
    {
        return $this->where('reseller_id', 0);
    }
    
    /**
     * Get customers (users with reseller_id > 0)
     */
    public function getCustomers()
    {
        $sql = "SELECT * FROM {$this->table} WHERE reseller_id > 0";
        return $this->raw($sql);
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['is_admin'];
    }
    
    /**
     * Check if user is reseller
     */
    public function isReseller($userId)
    {
        $user = $this->find($userId);
        return $user && $user['reseller_id'] == 0 && !$user['is_admin'];
    }
    
    /**
     * Check if user is customer
     */
    public function isCustomer($userId)
    {
        $user = $this->find($userId);
        return $user && $user['reseller_id'] > 0;
    }
    
    /**
     * Get user's hotels
     */
    public function getHotels($userId)
    {
        $sql = "SELECT * FROM hotels WHERE user_id = ?";
        return $this->raw($sql, [$userId]);
    }
    
    /**
     * Get user's widgets
     */
    public function getWidgets($userId)
    {
        $sql = "SELECT w.* FROM widgets w 
                JOIN hotels h ON w.hotel_id = h.id 
                WHERE h.user_id = ?";
        return $this->raw($sql, [$userId]);
    }
    
    /**
     * Activate user
     */
    public function activate($userId)
    {
        return $this->update($userId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate user
     */
    public function deactivate($userId)
    {
        return $this->update($userId, ['is_active' => 0]);
    }
    
    /**
     * Get user's hotels (relationship)
     */
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel', 'user_id');
    }
    
    /**
     * Get user's invitations sent (relationship)
     */
    public function invitesSent()
    {
        return $this->hasMany('App\Models\Invite', 'invited_by');
    }
    
    /**
     * Get reseller's customers (relationship)
     */
    public function customers()
    {
        if ($this->reseller_id == 0) {
            return $this->hasMany('App\Models\User', 'reseller_id');
        }
        return [];
    }
    
    /**
     * Get user's reseller (relationship)
     */
    public function reseller()
    {
        if ($this->reseller_id > 0) {
            return $this->belongsTo('App\Models\User', 'reseller_id');
        }
        return null;
    }
}
