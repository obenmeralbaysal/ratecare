<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Hotel;
use App\Models\User;

/**
 * Admin Hotels Controller
 */
class HotelsController extends BaseController
{
    private $hotelModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize database connection
        try {
            $db = \Core\Database::getInstance();
            $db->connect([
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'hoteldigilab_new'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4')
            ]);
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
        }
        
        $this->hotelModel = new Hotel();
        $this->userModel = new User();
    }
    
    /**
     * Edit hotel for specific user
     */
    public function edit($userId)
    {
        try {
            // Get user info
            $user = $this->getUserById($userId);
            if (!$user) {
                return $this->redirect('/admin/users')->with('error', 'User not found');
            }
            
            // Get user's hotel
            $hotel = $this->getHotelByUserId($userId);
            
            echo $this->view('admin.hotels.edit', [
                'title' => 'Edit Property - ' . $user['namesurname'],
                'user' => $user,
                'hotel' => $hotel
            ]);
            
        } catch (\Exception $e) {
            error_log("Hotel edit error: " . $e->getMessage());
            return $this->redirect('/admin/users')->with('error', 'Error loading hotel data');
        }
    }
    
    /**
     * Update hotel information
     */
    public function update($hotelId)
    {
        try {
            // Validate input
            $this->validate([
                'name' => 'required|min:2',
                'web_url' => 'url',
                'opening_language' => 'required|in:auto,native,english',
                'default_ibe' => 'in:sabeeapp,reseliva,hotelrunner'
            ]);
            
            $data = [
                'name' => $this->input('name'),
                'web_url' => $this->input('web_url'),
                'opening_language' => $this->input('opening_language'),
                'default_ibe' => $this->input('default_ibe'),
                'sabee_hotel_id' => $this->input('sabee_hotel_id'),
                'sabee_url' => $this->input('sabee_url'),
                'sabee_is_active' => $this->input('sabee_is_active') ? 1 : 0,
                'reseliva_hotel_id' => $this->input('reseliva_hotel_id'),
                'reseliva_is_active' => $this->input('reseliva_is_active') ? 1 : 0,
                'hotelrunner_url' => $this->input('hotelrunner_url'),
                'is_hotelrunner_active' => $this->input('is_hotelrunner_active') ? 1 : 0,
                'booking_url' => $this->input('booking_url'),
                'booking_is_active' => $this->input('booking_is_active') ? 1 : 0,
                'hotels_url' => $this->input('hotels_url'),
                'hotels_is_active' => $this->input('hotels_is_active') ? 1 : 0,
                'tatilsepeti_url' => $this->input('tatilsepeti_url'),
                'tatilsepeti_is_active' => $this->input('tatilsepeti_is_active') ? 1 : 0,
                'odamax_url' => $this->input('odamax_url'),
                'odamax_is_active' => $this->input('odamax_is_active') ? 1 : 0,
                'otelz_url' => $this->input('otelz_url'),
                'otelz_is_active' => $this->input('otelz_is_active') ? 1 : 0,
                'etstur_hotel_id' => $this->input('etstur_hotel_id'),
                'is_etstur_active' => $this->input('is_etstur_active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update hotel
            $this->updateHotel($hotelId, $data);
            
            return $this->redirect('/admin/users')->with('success', 'Hotel information updated successfully');
            
        } catch (\Exception $e) {
            error_log("Hotel update error: " . $e->getMessage());
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Get user by ID
     */
    private function getUserById($userId)
    {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $result = $this->userModel->raw($sql, [$userId]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get hotel by user ID
     */
    private function getHotelByUserId($userId)
    {
        try {
            $sql = "SELECT * FROM hotels WHERE user_id = ? LIMIT 1";
            $result = $this->hotelModel->raw($sql, [$userId]);
            return $result[0] ?? $this->getDefaultHotelData();
        } catch (\Exception $e) {
            error_log("Get hotel error: " . $e->getMessage());
            return $this->getDefaultHotelData();
        }
    }
    
    /**
     * Update hotel data
     */
    private function updateHotel($hotelId, $data)
    {
        try {
            if ($hotelId && $hotelId != 'new') {
                // Update existing hotel
                $sql = "UPDATE hotels SET ";
                $setParts = [];
                $params = [];
                
                foreach ($data as $key => $value) {
                    $setParts[] = "$key = ?";
                    $params[] = $value;
                }
                
                $sql .= implode(', ', $setParts) . " WHERE id = ?";
                $params[] = $hotelId;
                
                return $this->hotelModel->raw($sql, $params);
            } else {
                // Create new hotel - would need user_id
                // For now, just return success
                return true;
            }
        } catch (\Exception $e) {
            error_log("Update hotel error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get default hotel data structure
     */
    private function getDefaultHotelData()
    {
        return [
            'id' => null,
            'name' => '',
            'web_url' => '',
            'opening_language' => 'auto',
            'default_ibe' => 'sabeeapp',
            'sabee_hotel_id' => '',
            'sabee_url' => '',
            'sabee_is_active' => 0,
            'reseliva_hotel_id' => '',
            'reseliva_is_active' => 0,
            'hotelrunner_url' => '',
            'is_hotelrunner_active' => 0,
            'booking_url' => '',
            'booking_is_active' => 0,
            'hotels_url' => '',
            'hotels_is_active' => 0,
            'tatilsepeti_url' => '',
            'tatilsepeti_is_active' => 0,
            'odamax_url' => '',
            'odamax_is_active' => 0,
            'otelz_url' => '',
            'otelz_is_active' => 0,
            'etstur_hotel_id' => '',
            'is_etstur_active' => 0
        ];
    }
}
