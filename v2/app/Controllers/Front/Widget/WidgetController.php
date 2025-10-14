<?php

namespace App\Controllers\Front\Widget;

use App\Controllers\BaseController;
use App\Models\Widget;
use App\Models\Hotel;
use App\Models\Rate;
use App\Models\Statistic;

/**
 * Public Widget Controller
 * Handles public widget display and interactions
 */
class WidgetController extends BaseController
{
    private $widgetModel;
    private $hotelModel;
    private $rateModel;
    private $statisticModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->widgetModel = new Widget();
        $this->hotelModel = new Hotel();
        $this->rateModel = new Rate();
        $this->statisticModel = new Statistic();
    }
    
    /**
     * Display widget by code
     */
    public function show($code)
    {
        $widget = $this->widgetModel->findByCode($code);
        
        if (!$widget || !$widget['is_active']) {
            return $this->json(['error' => 'Widget not found'], 404);
        }
        
        $hotel = $this->hotelModel->find($widget['hotel_id']);
        
        if (!$hotel || !$hotel['is_active']) {
            return $this->json(['error' => 'Hotel not found'], 404);
        }
        
        // Record view statistic
        $this->recordView($widget['id'], $hotel['id']);
        
        // Get widget data based on type
        $widgetData = $this->getWidgetData($widget, $hotel);
        
        return $this->json([
            'widget' => $widget,
            'hotel' => $hotel,
            'data' => $widgetData
        ]);
    }
    
    /**
     * Get widget embed code
     */
    public function embed($code)
    {
        $widget = $this->widgetModel->findByCode($code);
        
        if (!$widget || !$widget['is_active']) {
            return $this->view('front.widget.error', [
                'message' => 'Widget not found'
            ]);
        }
        
        $hotel = $this->hotelModel->find($widget['hotel_id']);
        
        if (!$hotel || !$hotel['is_active']) {
            return $this->view('front.widget.error', [
                'message' => 'Hotel not found'
            ]);
        }
        
        // Record view
        $this->recordView($widget['id'], $hotel['id']);
        
        // Get widget settings
        $settings = json_decode($widget['settings'], true) ?: [];
        $styleSettings = json_decode($widget['style_settings'], true) ?: [];
        
        // Get widget data
        $widgetData = $this->getWidgetData($widget, $hotel);
        
        return $this->view("front.widget.{$widget['type']}", [
            'widget' => $widget,
            'hotel' => $hotel,
            'settings' => $settings,
            'styleSettings' => $styleSettings,
            'data' => $widgetData
        ]);
    }
    
    /**
     * Handle widget click tracking
     */
    public function click($code)
    {
        $widget = $this->widgetModel->findByCode($code);
        
        if (!$widget) {
            return $this->json(['error' => 'Widget not found'], 404);
        }
        
        // Record click statistic
        $this->statisticModel->record(
            $widget['hotel_id'],
            'clicks',
            'widget_click',
            1,
            ['widget_id' => $widget['id']]
        );
        
        // Get redirect URL from request
        $redirectUrl = $this->input('url');
        
        if ($redirectUrl) {
            return $this->redirect($redirectUrl);
        }
        
        return $this->json(['success' => true]);
    }
    
    /**
     * Search rates for widget
     */
    public function searchRates($code)
    {
        $widget = $this->widgetModel->findByCode($code);
        
        if (!$widget || $widget['type'] !== 'rates') {
            return $this->json(['error' => 'Invalid widget'], 400);
        }
        
        $checkIn = $this->input('check_in');
        $checkOut = $this->input('check_out');
        $adults = (int) $this->input('adults', 2);
        $children = (int) $this->input('children', 0);
        
        if (!$checkIn || !$checkOut) {
            return $this->json(['error' => 'Check-in and check-out dates required'], 400);
        }
        
        // Get rates
        $rates = $this->rateModel->getByHotel($widget['hotel_id'], [
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ]);
        
        // Filter by adults/children if specified
        if ($adults || $children) {
            $rates = array_filter($rates, function($rate) use ($adults, $children) {
                return $rate['adults'] >= $adults && $rate['children'] >= $children;
            });
        }
        
        // Record search statistic
        $this->statisticModel->record(
            $widget['hotel_id'],
            'searches',
            'rate_search',
            1,
            ['widget_id' => $widget['id']]
        );
        
        return $this->json([
            'rates' => array_values($rates),
            'search_params' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $adults,
                'children' => $children
            ]
        ]);
    }
    
    /**
     * Get widget configuration for embedding
     */
    public function config($code)
    {
        $widget = $this->widgetModel->findByCode($code);
        
        if (!$widget || !$widget['is_active']) {
            return $this->json(['error' => 'Widget not found'], 404);
        }
        
        $hotel = $this->hotelModel->find($widget['hotel_id']);
        
        $config = [
            'widget_id' => $widget['id'],
            'widget_code' => $widget['code'],
            'widget_type' => $widget['type'],
            'hotel_name' => $hotel['name'],
            'settings' => json_decode($widget['settings'], true) ?: [],
            'style_settings' => json_decode($widget['style_settings'], true) ?: [],
            'embed_url' => url("widget/embed/{$code}"),
            'api_url' => url("widget/api/{$code}")
        ];
        
        return $this->json($config);
    }
    
    /**
     * Get widget data based on type
     */
    private function getWidgetData($widget, $hotel)
    {
        switch ($widget['type']) {
            case 'rates':
                return $this->getRatesData($widget, $hotel);
                
            case 'booking':
                return $this->getBookingData($widget, $hotel);
                
            case 'availability':
                return $this->getAvailabilityData($widget, $hotel);
                
            default:
                return [];
        }
    }
    
    /**
     * Get rates widget data
     */
    private function getRatesData($widget, $hotel)
    {
        $settings = json_decode($widget['settings'], true) ?: [];
        $limit = $settings['rate_limit'] ?? 10;
        
        $rates = $this->rateModel->getLowestRates($hotel['id'], $limit);
        
        return [
            'rates' => $rates,
            'currency' => $hotel['currency'],
            'total_rates' => count($rates)
        ];
    }
    
    /**
     * Get booking widget data
     */
    private function getBookingData($widget, $hotel)
    {
        $settings = json_decode($widget['settings'], true) ?: [];
        
        return [
            'hotel_info' => [
                'name' => $hotel['name'],
                'address' => $hotel['address'],
                'city' => $hotel['city'],
                'country' => $hotel['country'],
                'star_rating' => $hotel['star_rating']
            ],
            'booking_settings' => $settings,
            'currency' => $hotel['currency']
        ];
    }
    
    /**
     * Get availability widget data
     */
    private function getAvailabilityData($widget, $hotel)
    {
        // This would typically connect to a booking system API
        // For now, return basic hotel info
        return [
            'hotel_info' => [
                'name' => $hotel['name'],
                'city' => $hotel['city'],
                'country' => $hotel['country']
            ],
            'available' => true // Placeholder
        ];
    }
    
    /**
     * Record widget view
     */
    private function recordView($widgetId, $hotelId)
    {
        $this->statisticModel->record(
            $hotelId,
            'views',
            'widget_view',
            1,
            ['widget_id' => $widgetId]
        );
    }
}
