<?php

namespace Core;

/**
 * Authorization and Role Management System
 */
class Authorization
{
    private static $instance = null;
    private $auth;
    
    private function __construct()
    {
        $this->auth = Auth::getInstance();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($permission, $resource = null)
    {
        return $this->auth->can($permission, $resource);
    }
    
    /**
     * Require permission or throw exception
     */
    public function requirePermission($permission, $resource = null)
    {
        if (!$this->hasPermission($permission, $resource)) {
            throw new \Exception("Access denied. Required permission: {$permission}");
        }
        
        return true;
    }
    
    /**
     * Check if user can access admin area
     */
    public function canAccessAdmin()
    {
        return $this->auth->isAdmin();
    }
    
    /**
     * Check if user can access reseller area
     */
    public function canAccessReseller()
    {
        return $this->auth->isAdmin() || $this->auth->isReseller();
    }
    
    /**
     * Check if user can access customer area
     */
    public function canAccessCustomer()
    {
        return $this->auth->check(); // Any authenticated user
    }
    
    /**
     * Check if user can manage other users
     */
    public function canManageUsers($targetUserId = null)
    {
        if ($this->auth->isAdmin()) {
            return true;
        }
        
        if ($this->auth->isReseller() && $targetUserId) {
            // Resellers can only manage their customers
            $db = Database::getInstance();
            $targetUser = $db->selectOne("SELECT * FROM users WHERE id = ?", [$targetUserId]);
            
            if ($targetUser) {
                return $targetUser['reseller_id'] == $this->auth->getResellerId();
            }
        }
        
        return false;
    }
    
    /**
     * Check if user can view resource
     */
    public function canView($resource, $resourceId = null)
    {
        switch ($resource) {
            case 'users':
                return $this->canManageUsers($resourceId);
                
            case 'hotels':
                return $this->canManageHotels($resourceId);
                
            case 'widgets':
                return $this->canManageWidgets($resourceId);
                
            case 'statistics':
                return $this->auth->check();
                
            default:
                return false;
        }
    }
    
    /**
     * Check if user can edit resource
     */
    public function canEdit($resource, $resourceId = null)
    {
        return $this->canView($resource, $resourceId);
    }
    
    /**
     * Check if user can delete resource
     */
    public function canDelete($resource, $resourceId = null)
    {
        return $this->canView($resource, $resourceId);
    }
    
    /**
     * Check if user can manage hotels
     */
    public function canManageHotels($hotelId = null)
    {
        if ($this->auth->isAdmin()) {
            return true;
        }
        
        if ($hotelId && ($this->auth->isReseller() || $this->auth->isCustomer())) {
            $db = Database::getInstance();
            $hotel = $db->selectOne("SELECT * FROM hotels WHERE id = ?", [$hotelId]);
            
            if ($hotel) {
                $hotelUser = $db->selectOne("SELECT * FROM users WHERE id = ?", [$hotel['user_id']]);
                
                if ($this->auth->isReseller()) {
                    return $hotelUser['reseller_id'] == $this->auth->getResellerId();
                }
                
                if ($this->auth->isCustomer()) {
                    return $hotel['user_id'] == $this->auth->id();
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if user can manage widgets
     */
    public function canManageWidgets($widgetId = null)
    {
        if ($this->auth->isAdmin()) {
            return true;
        }
        
        if ($widgetId && ($this->auth->isReseller() || $this->auth->isCustomer())) {
            $db = Database::getInstance();
            $widget = $db->selectOne("
                SELECT w.*, h.user_id, u.reseller_id 
                FROM widgets w 
                JOIN hotels h ON w.hotel_id = h.id 
                JOIN users u ON h.user_id = u.id 
                WHERE w.id = ?
            ", [$widgetId]);
            
            if ($widget) {
                if ($this->auth->isReseller()) {
                    return $widget['reseller_id'] == $this->auth->getResellerId();
                }
                
                if ($this->auth->isCustomer()) {
                    return $widget['user_id'] == $this->auth->id();
                }
            }
        }
        
        return false;
    }
    
    /**
     * Filter query based on user permissions
     */
    public function filterQuery($baseQuery, $resource)
    {
        if ($this->auth->isAdmin()) {
            return $baseQuery; // Admins see everything
        }
        
        switch ($resource) {
            case 'users':
                if ($this->auth->isReseller()) {
                    return $baseQuery . " AND reseller_id = " . $this->auth->getResellerId();
                }
                break;
                
            case 'hotels':
                if ($this->auth->isReseller()) {
                    return $baseQuery . " AND user_id IN (SELECT id FROM users WHERE reseller_id = " . $this->auth->getResellerId() . ")";
                } elseif ($this->auth->isCustomer()) {
                    return $baseQuery . " AND user_id = " . $this->auth->id();
                }
                break;
                
            case 'widgets':
                if ($this->auth->isReseller()) {
                    return $baseQuery . " AND hotel_id IN (SELECT id FROM hotels WHERE user_id IN (SELECT id FROM users WHERE reseller_id = " . $this->auth->getResellerId() . "))";
                } elseif ($this->auth->isCustomer()) {
                    return $baseQuery . " AND hotel_id IN (SELECT id FROM hotels WHERE user_id = " . $this->auth->id() . ")";
                }
                break;
        }
        
        return $baseQuery;
    }
    
    /**
     * Get allowed actions for resource
     */
    public function getAllowedActions($resource, $resourceId = null)
    {
        $actions = [];
        
        if ($this->canView($resource, $resourceId)) {
            $actions[] = 'view';
        }
        
        if ($this->canEdit($resource, $resourceId)) {
            $actions[] = 'edit';
        }
        
        if ($this->canDelete($resource, $resourceId)) {
            $actions[] = 'delete';
        }
        
        return $actions;
    }
}
