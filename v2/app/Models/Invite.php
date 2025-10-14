<?php

namespace App\Models;

use Core\Hash;

/**
 * Invite Model
 */
class Invite extends BaseModel
{
    protected $table = 'invites';
    protected $fillable = [
        'email', 'code', 'reseller_id', 'invited_by', 'accepted', 'accepted_at'
    ];
    
    /**
     * Create new invitation
     */
    public function createInvitation($email, $resellerId, $invitedBy)
    {
        $code = Hash::inviteCode();
        
        return $this->create([
            'email' => $email,
            'code' => $code,
            'reseller_id' => $resellerId,
            'invited_by' => $invitedBy,
            'accepted' => 0
        ]);
    }
    
    /**
     * Find invitation by code
     */
    public function findByCode($code)
    {
        return $this->whereFirst('code', $code);
    }
    
    /**
     * Find pending invitation by email
     */
    public function findPendingByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND accepted = 0";
        return $this->db->selectOne($sql, [$email]);
    }
    
    /**
     * Accept invitation
     */
    public function acceptInvitation($inviteId)
    {
        return $this->update($inviteId, [
            'accepted' => 1,
            'accepted_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get invitations by reseller
     */
    public function getByReseller($resellerId)
    {
        return $this->where('reseller_id', $resellerId);
    }
    
    /**
     * Get pending invitations
     */
    public function getPending()
    {
        return $this->where('accepted', 0);
    }
    
    /**
     * Get accepted invitations
     */
    public function getAccepted()
    {
        return $this->where('accepted', 1);
    }
    
    /**
     * Delete invitation
     */
    public function deleteInvitation($inviteId)
    {
        return $this->delete($inviteId);
    }
    
    /**
     * Clean expired invitations (older than 7 days)
     */
    public function cleanExpired()
    {
        $sql = "DELETE FROM {$this->table} WHERE accepted = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        return $this->db->query($sql);
    }
    
    /**
     * Get invitation statistics
     */
    public function getStats($resellerId = null)
    {
        $whereClause = $resellerId ? "WHERE reseller_id = ?" : "";
        $params = $resellerId ? [$resellerId] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN accepted = 1 THEN 1 ELSE 0 END) as accepted,
                    SUM(CASE WHEN accepted = 0 THEN 1 ELSE 0 END) as pending
                FROM {$this->table} {$whereClause}";
        
        return $this->db->selectOne($sql, $params);
    }
}
