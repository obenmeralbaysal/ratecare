<?php

namespace App\Controllers\Customer\Hotel;

use App\Controllers\BaseController;
use App\Models\Hotel;
use App\Models\Widget;
use App\Models\Rate;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use Core\Auth;
use Core\Authorization;

/**
 * Customer Hotel Controller
 */
class HotelController extends BaseController
{
    private $auth;
    private $authz;
    private $hotelModel;
    private $widgetModel;
    private $rateModel;
    private $countryModel;
    private $currencyModel;
    private $languageModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        $this->hotelModel = new Hotel();
        $this->widgetModel = new Widget();
        $this->rateModel = new Rate();
        $this->countryModel = new Country();
        $this->currencyModel = new Currency();
        $this->languageModel = new Language();
    }
    
    /**
     * List user's hotels
     */
    public function index()
    {
        $userId = $this->auth->id();
        $search = $this->input('search', '');
        $country = $this->input('country');
        $city = $this->input('city');
        $isActive = $this->input('is_active');
        
        $filters = ['user_id' => $userId];
        
        if ($country) {
            $filters['country'] = $country;
        }
        
        if ($city) {
            $filters['city'] = $city;
        }
        
        if ($isActive !== null) {
            $filters['is_active'] = $isActive;
        }
        
        $hotels = $this->hotelModel->search($search, $filters);
        
        // Get filter options
        $countries = $this->countryModel->getActive();
        $cities = $this->hotelModel->db->select(
            "SELECT DISTINCT city FROM hotels WHERE user_id = ? AND city IS NOT NULL ORDER BY city",
            [$userId]
        );
        
        return $this->view('customer.hotels.index', [
            'title' => 'My Hotels',
            'hotels' => $hotels,
            'countries' => $countries,
            'cities' => $cities,
            'search' => $search,
            'filters' => [
                'country' => $country,
                'city' => $city,
                'is_active' => $isActive
            ]
        ]);
    }
    
    /**
     * Show create hotel form
     */
    public function create()
    {
        $countries = $this->countryModel->getDropdownList();
        $currencies = $this->currencyModel->getDropdownList();
        $languages = $this->languageModel->getDropdownList();
        
        return $this->view('customer.hotels.create', [
            'title' => 'Create Hotel',
            'countries' => $countries,
            'currencies' => $currencies,
            'languages' => $languages
        ]);
    }
    
    /**
     * Store new hotel
     */
    public function store()
    {
        try {
            $this->validate([
                'name' => 'required|min:3',
                'city' => 'required',
                'country' => 'required',
                'currency' => 'required',
                'language' => 'required'
            ]);
            
            $hotelId = $this->hotelModel->createHotel([
                'user_id' => $this->auth->id(),
                'name' => $this->input('name'),
                'address' => $this->input('address'),
                'city' => $this->input('city'),
                'country' => $this->input('country'),
                'phone' => $this->input('phone'),
                'email' => $this->input('email'),
                'website' => $this->input('website'),
                'star_rating' => (int) $this->input('star_rating', 0),
                'description' => $this->input('description'),
                'currency' => $this->input('currency'),
                'language' => $this->input('language'),
                'is_active' => 1
            ]);
            
            return $this->redirect('/customer/hotels')->with('success', 'Hotel created successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Show hotel details
     */
    public function show($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->redirect('/customer/hotels')->with('error', 'Access denied');
        }
        
        $hotel = $this->hotelModel->find($id);
        
        if (!$hotel) {
            return $this->redirect('/customer/hotels')->with('error', 'Hotel not found');
        }
        
        // Get hotel statistics
        $stats = $this->hotelModel->getStats($id);
        
        // Get recent widgets
        $widgets = $this->widgetModel->getByHotel($id);
        
        // Get recent rates
        $rates = $this->rateModel->getByHotel($id, [
            'check_in' => date('Y-m-d')
        ]);
        $rates = array_slice($rates, 0, 10); // Limit to 10 recent rates
        
        return $this->view('customer.hotels.show', [
            'title' => 'Hotel Details',
            'hotel' => $hotel,
            'stats' => $stats,
            'widgets' => $widgets,
            'rates' => $rates
        ]);
    }
    
    /**
     * Show edit hotel form
     */
    public function edit($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->redirect('/customer/hotels')->with('error', 'Access denied');
        }
        
        $hotel = $this->hotelModel->find($id);
        
        if (!$hotel) {
            return $this->redirect('/customer/hotels')->with('error', 'Hotel not found');
        }
        
        $countries = $this->countryModel->getDropdownList();
        $currencies = $this->currencyModel->getDropdownList();
        $languages = $this->languageModel->getDropdownList();
        
        return $this->view('customer.hotels.edit', [
            'title' => 'Edit Hotel',
            'hotel' => $hotel,
            'countries' => $countries,
            'currencies' => $currencies,
            'languages' => $languages
        ]);
    }
    
    /**
     * Update hotel
     */
    public function update($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->redirect('/customer/hotels')->with('error', 'Access denied');
        }
        
        try {
            $this->validate([
                'name' => 'required|min:3',
                'city' => 'required',
                'country' => 'required',
                'currency' => 'required',
                'language' => 'required'
            ]);
            
            $this->hotelModel->update($id, [
                'name' => $this->input('name'),
                'address' => $this->input('address'),
                'city' => $this->input('city'),
                'country' => $this->input('country'),
                'phone' => $this->input('phone'),
                'email' => $this->input('email'),
                'website' => $this->input('website'),
                'star_rating' => (int) $this->input('star_rating', 0),
                'description' => $this->input('description'),
                'currency' => $this->input('currency'),
                'language' => $this->input('language')
            ]);
            
            return $this->redirect('/customer/hotels')->with('success', 'Hotel updated successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Delete hotel
     */
    public function destroy($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->redirect('/customer/hotels')->with('error', 'Access denied');
        }
        
        try {
            // Check if hotel has widgets
            $widgetCount = $this->widgetModel->db->selectOne(
                "SELECT COUNT(*) as count FROM widgets WHERE hotel_id = ?",
                [$id]
            )['count'];
            
            if ($widgetCount > 0) {
                return $this->redirect('/customer/hotels')
                    ->with('error', 'Cannot delete hotel with existing widgets. Please delete widgets first.');
            }
            
            $this->hotelModel->delete($id);
            return $this->redirect('/customer/hotels')->with('success', 'Hotel deleted successfully');
            
        } catch (\Exception $e) {
            return $this->redirect('/customer/hotels')->with('error', 'Error deleting hotel: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle hotel status
     */
    public function toggle($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $hotel = $this->hotelModel->find($id);
        
        if (!$hotel) {
            return $this->json(['error' => 'Hotel not found'], 404);
        }
        
        $newStatus = $hotel['is_active'] ? 0 : 1;
        $this->hotelModel->update($id, ['is_active' => $newStatus]);
        
        return $this->json([
            'success' => true,
            'status' => $newStatus,
            'message' => $newStatus ? 'Hotel activated' : 'Hotel deactivated'
        ]);
    }
    
    /**
     * Get hotel statistics
     */
    public function statistics($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $period = $this->input('period', '30');
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        // Get basic stats
        $stats = $this->hotelModel->getStats($id);
        
        // Get rate statistics
        $rateStats = $this->rateModel->getStats($id);
        
        // Get widget performance
        $widgetStats = $this->widgetModel->getStats(['hotel_id' => $id]);
        
        return $this->json([
            'hotel_stats' => $stats,
            'rate_stats' => $rateStats,
            'widget_stats' => $widgetStats,
            'period' => $period
        ]);
    }
    
    /**
     * Export hotel data
     */
    public function export($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $format = $this->input('format', 'csv');
        
        $hotel = $this->hotelModel->find($id);
        $widgets = $this->widgetModel->getByHotel($id);
        $rates = $this->rateModel->getByHotel($id);
        
        $data = [
            'hotel' => $hotel,
            'widgets' => $widgets,
            'rates' => $rates
        ];
        
        if ($format === 'csv') {
            return $this->exportCsv($data, 'hotel_' . $hotel['code'] . '_' . date('Y-m-d') . '.csv');
        } else {
            return $this->json($data);
        }
    }
    
    /**
     * Get cities for country (AJAX)
     */
    public function getCities()
    {
        $country = $this->input('country');
        
        if (!$country) {
            return $this->json(['cities' => []]);
        }
        
        $cities = $this->hotelModel->db->select(
            "SELECT DISTINCT city FROM hotels WHERE country = ? AND city IS NOT NULL ORDER BY city",
            [$country]
        );
        
        return $this->json([
            'cities' => array_column($cities, 'city')
        ]);
    }
    
    /**
     * Duplicate hotel
     */
    public function duplicate($id)
    {
        if (!$this->authz->canManageHotels($id)) {
            return $this->redirect('/customer/hotels')->with('error', 'Access denied');
        }
        
        $hotel = $this->hotelModel->find($id);
        
        if (!$hotel) {
            return $this->redirect('/customer/hotels')->with('error', 'Hotel not found');
        }
        
        try {
            // Create duplicate hotel
            $newHotelData = $hotel;
            unset($newHotelData['id']);
            unset($newHotelData['code']);
            unset($newHotelData['created_at']);
            unset($newHotelData['updated_at']);
            
            $newHotelData['name'] = $hotel['name'] . ' (Copy)';
            
            $newHotelId = $this->hotelModel->createHotel($newHotelData);
            
            return $this->redirect('/customer/hotels')->with('success', 'Hotel duplicated successfully');
            
        } catch (\Exception $e) {
            return $this->redirect('/customer/hotels')->with('error', 'Error duplicating hotel: ' . $e->getMessage());
        }
    }
    
    /**
     * Export data as CSV
     */
    private function exportCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Hotel information
        fputcsv($output, ['Hotel Information']);
        fputcsv($output, ['Name', 'Code', 'City', 'Country', 'Star Rating', 'Currency', 'Language', 'Status']);
        fputcsv($output, [
            $data['hotel']['name'],
            $data['hotel']['code'],
            $data['hotel']['city'],
            $data['hotel']['country'],
            $data['hotel']['star_rating'],
            $data['hotel']['currency'],
            $data['hotel']['language'],
            $data['hotel']['is_active'] ? 'Active' : 'Inactive'
        ]);
        
        fputcsv($output, []); // Empty row
        
        // Widgets
        fputcsv($output, ['Widgets']);
        fputcsv($output, ['Name', 'Code', 'Type', 'Status', 'Created At']);
        foreach ($data['widgets'] as $widget) {
            fputcsv($output, [
                $widget['name'],
                $widget['code'],
                $widget['type'],
                $widget['is_active'] ? 'Active' : 'Inactive',
                $widget['created_at']
            ]);
        }
        
        fputcsv($output, []); // Empty row
        
        // Rates
        fputcsv($output, ['Rates']);
        fputcsv($output, ['Room Type', 'Check In', 'Check Out', 'Price', 'Currency', 'Source']);
        foreach ($data['rates'] as $rate) {
            fputcsv($output, [
                $rate['room_type'],
                $rate['check_in'],
                $rate['check_out'],
                $rate['price'],
                $rate['currency'],
                $rate['source']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
