<?php

namespace App\Controllers\Customer\Rate;

use App\Controllers\BaseController;
use App\Models\Rate;
use App\Models\Hotel;
use App\Models\RateComparison;
use App\Models\Currency;
use Core\Auth;
use Core\Authorization;

/**
 * Customer Rate Controller
 */
class RateController extends BaseController
{
    private $auth;
    private $authz;
    private $rateModel;
    private $hotelModel;
    private $rateComparisonModel;
    private $currencyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        $this->rateModel = new Rate();
        $this->hotelModel = new Hotel();
        $this->rateComparisonModel = new RateComparison();
        $this->currencyModel = new Currency();
    }
    
    /**
     * List rates for user's hotels
     */
    public function index()
    {
        $userId = $this->auth->id();
        $search = $this->input('search', '');
        $hotelId = $this->input('hotel_id');
        $source = $this->input('source');
        $currency = $this->input('currency');
        
        $filters = ['user_id' => $userId];
        
        if ($hotelId) {
            $filters['hotel_id'] = $hotelId;
        }
        
        if ($source) {
            $filters['source'] = $source;
        }
        
        if ($currency) {
            $filters['currency'] = $currency;
        }
        
        $rates = $this->rateModel->search($search, $filters);
        
        // Get user's hotels for filter
        $hotels = $this->hotelModel->getByUser($userId);
        
        // Get available sources and currencies
        $sources = $this->rateModel->db->select("SELECT DISTINCT source FROM rates WHERE source IS NOT NULL ORDER BY source");
        $currencies = $this->currencyModel->getActive();
        
        return $this->view('customer.rates.index', [
            'title' => 'Rate Management',
            'rates' => $rates,
            'hotels' => $hotels,
            'sources' => $sources,
            'currencies' => $currencies,
            'search' => $search,
            'filters' => [
                'hotel_id' => $hotelId,
                'source' => $source,
                'currency' => $currency
            ]
        ]);
    }
    
    /**
     * Show rate comparison for hotel
     */
    public function compare()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $checkIn = $this->input('check_in', date('Y-m-d', strtotime('+1 day')));
        $checkOut = $this->input('check_out', date('Y-m-d', strtotime('+2 days')));
        $adults = (int) $this->input('adults', 2);
        $children = (int) $this->input('children', 0);
        
        // Get user's hotels
        $hotels = $this->hotelModel->getByUser($userId);
        
        $comparison = null;
        $selectedHotel = null;
        
        if ($hotelId) {
            // Check if user owns the hotel
            if (!$this->authz->canManageHotels($hotelId)) {
                return $this->back()->with('error', 'Access denied');
            }
            
            $selectedHotel = $this->hotelModel->find($hotelId);
            
            // Get or create comparison
            $comparison = $this->getOrCreateComparison($hotelId, $checkIn, $checkOut, $adults, $children);
        }
        
        return $this->view('customer.rates.compare', [
            'title' => 'Rate Comparison',
            'hotels' => $hotels,
            'selected_hotel' => $selectedHotel,
            'comparison' => $comparison,
            'search_params' => [
                'hotel_id' => $hotelId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $adults,
                'children' => $children
            ]
        ]);
    }
    
    /**
     * Get rate statistics
     */
    public function statistics()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $period = $this->input('period', '30');
        
        $hotels = $this->hotelModel->getByUser($userId);
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $stats = [];
        
        if ($hotelId) {
            $stats = $this->rateModel->getStats($hotelId);
        } else {
            // Get stats for all user's hotels
            foreach ($hotels as $hotel) {
                $hotelStats = $this->rateModel->getStats($hotel['id']);
                $hotelStats['hotel_name'] = $hotel['name'];
                $stats[] = $hotelStats;
            }
        }
        
        return $this->json([
            'stats' => $stats,
            'period' => $period
        ]);
    }
    
    /**
     * Export rates data
     */
    public function export()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $format = $this->input('format', 'csv');
        $dateFrom = $this->input('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $this->input('date_to', date('Y-m-d'));
        
        $filters = ['user_id' => $userId];
        
        if ($hotelId) {
            if (!$this->authz->canManageHotels($hotelId)) {
                return $this->json(['error' => 'Access denied'], 403);
            }
            $filters['hotel_id'] = $hotelId;
        }
        
        $filters['check_in'] = $dateFrom;
        $filters['check_out'] = $dateTo;
        
        $rates = $this->rateModel->search('', $filters);
        
        if ($format === 'csv') {
            return $this->exportCsv($rates, 'rates_' . date('Y-m-d') . '.csv');
        } else {
            return $this->json($rates);
        }
    }
    
    /**
     * Get or create rate comparison
     */
    private function getOrCreateComparison($hotelId, $checkIn, $checkOut, $adults, $children)
    {
        // Try to get cached comparison
        $cached = $this->rateComparisonModel->getCached($hotelId, $checkIn, $checkOut, $adults, $children);
        
        if ($cached) {
            $cached['comparison_data'] = json_decode($cached['comparison_data'], true);
            return $cached;
        }
        
        // Create new comparison
        $comparisonData = $this->fetchRateComparison($hotelId, $checkIn, $checkOut, $adults, $children);
        
        if (!empty($comparisonData)) {
            $comparisonId = $this->rateComparisonModel->storeComparison(
                $hotelId, 
                $checkIn, 
                $checkOut, 
                $comparisonData,
                ['adults' => $adults, 'children' => $children]
            );
            
            return $this->rateComparisonModel->getWithData($comparisonId);
        }
        
        return null;
    }
    
    /**
     * Fetch rate comparison from different sources
     */
    private function fetchRateComparison($hotelId, $checkIn, $checkOut, $adults, $children)
    {
        $rates = $this->rateModel->compareRates($hotelId, $checkIn, $checkOut, $adults, $children);
        
        $comparisonData = [];
        
        foreach ($rates as $rate) {
            $source = $rate['source'];
            
            if (!isset($comparisonData[$source])) {
                $comparisonData[$source] = [
                    'source' => $source,
                    'price' => $rate['min_price'],
                    'currency' => $rate['currency'],
                    'rate_count' => $rate['rate_count'],
                    'rates' => []
                ];
            }
            
            // Get detailed rates for this source
            $detailedRates = $this->rateModel->getByHotel($hotelId, [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'source' => $source
            ]);
            
            $comparisonData[$source]['rates'] = $detailedRates;
        }
        
        return $comparisonData;
    }
    
    /**
     * Refresh rate comparison
     */
    public function refreshComparison()
    {
        $hotelId = $this->input('hotel_id');
        $checkIn = $this->input('check_in');
        $checkOut = $this->input('check_out');
        $adults = (int) $this->input('adults', 2);
        $children = (int) $this->input('children', 0);
        
        if (!$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        // Clear old cached comparison
        $this->rateComparisonModel->db->delete(
            "DELETE FROM rate_comparisons WHERE hotel_id = ? AND check_in = ? AND check_out = ? AND adults = ? AND children = ?",
            [$hotelId, $checkIn, $checkOut, $adults, $children]
        );
        
        // Create new comparison
        $comparison = $this->getOrCreateComparison($hotelId, $checkIn, $checkOut, $adults, $children);
        
        return $this->json([
            'success' => true,
            'comparison' => $comparison
        ]);
    }
    
    /**
     * Get rate trends
     */
    public function trends()
    {
        $userId = $this->auth->id();
        $hotelId = $this->input('hotel_id');
        $period = $this->input('period', '30');
        
        if ($hotelId && !$this->authz->canManageHotels($hotelId)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $sql = "SELECT 
                    DATE(r.created_at) as date,
                    AVG(r.price) as avg_price,
                    MIN(r.price) as min_price,
                    MAX(r.price) as max_price,
                    COUNT(*) as rate_count,
                    r.currency
                FROM rates r
                JOIN hotels h ON r.hotel_id = h.id
                WHERE h.user_id = ? 
                AND DATE(r.created_at) BETWEEN ? AND ?";
        
        $params = [$userId, $dateFrom, $dateTo];
        
        if ($hotelId) {
            $sql .= " AND r.hotel_id = ?";
            $params[] = $hotelId;
        }
        
        $sql .= " GROUP BY DATE(r.created_at), r.currency ORDER BY date ASC";
        
        $trends = $this->rateModel->raw($sql, $params);
        
        return $this->json([
            'trends' => $trends,
            'period' => $period
        ]);
    }
    
    /**
     * Export data as CSV
     */
    private function exportCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Hotel', 'Room Type', 'Check In', 'Check Out', 'Adults', 'Children',
            'Price', 'Currency', 'Source', 'Breakfast', 'Free Cancellation', 'Created At'
        ]);
        
        // CSV data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['hotel_name'] ?? '',
                $row['room_type'],
                $row['check_in'],
                $row['check_out'],
                $row['adults'],
                $row['children'],
                $row['price'],
                $row['currency'],
                $row['source'],
                $row['breakfast_included'] ? 'Yes' : 'No',
                $row['free_cancellation'] ? 'Yes' : 'No',
                $row['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
