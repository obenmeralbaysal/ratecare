<?php

namespace App\Controllers\Customer\Widget;

use App\Controllers\BaseController;
use App\Models\Widget;
use App\Models\Hotel;
use App\Models\Statistic;
use Core\Auth;
use Core\Authorization;

/**
 * Customer Widget Controller
 */
class WidgetController extends BaseController
{
    private $auth;
    private $authz;
    private $widgetModel;
    private $hotelModel;
    private $statisticModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->auth = Auth::getInstance();
        $this->authz = Authorization::getInstance();
        $this->widgetModel = new Widget();
        $this->hotelModel = new Hotel();
        $this->statisticModel = new Statistic();
    }
    
    /**
     * List user's widgets
     */
    public function index()
    {
        $userId = $this->auth->id();
        $search = $this->input('search', '');
        $hotelId = $this->input('hotel_id');
        $type = $this->input('type');
        
        $filters = ['user_id' => $userId];
        
        if ($hotelId) {
            $filters['hotel_id'] = $hotelId;
        }
        
        if ($type) {
            $filters['type'] = $type;
        }
        
        $widgets = $this->widgetModel->search($search, $filters);
        
        // Get user's hotels for filter dropdown
        $hotels = $this->hotelModel->getByUser($userId);
        
        return $this->view('customer.widgets.index', [
            'title' => 'My Widgets',
            'widgets' => $widgets,
            'hotels' => $hotels,
            'search' => $search,
            'filters' => [
                'hotel_id' => $hotelId,
                'type' => $type
            ]
        ]);
    }
    
    /**
     * Show create widget form
     */
    public function create()
    {
        $userId = $this->auth->id();
        $hotels = $this->hotelModel->getByUser($userId);
        
        if (empty($hotels)) {
            return $this->redirect('/customer/hotels/create')
                ->with('error', 'Please create a hotel first before creating widgets');
        }
        
        return $this->view('customer.widgets.create', [
            'title' => 'Create Widget',
            'hotels' => $hotels
        ]);
    }
    
    /**
     * Store new widget
     */
    public function store()
    {
        try {
            $this->validate([
                'hotel_id' => 'required|numeric',
                'name' => 'required|min:3',
                'type' => 'required'
            ]);
            
            $hotelId = $this->input('hotel_id');
            
            // Check if user owns the hotel
            if (!$this->authz->canManageHotels($hotelId)) {
                return $this->back()->with('error', 'Access denied');
            }
            
            $settings = [
                'rate_limit' => (int) $this->input('rate_limit', 10),
                'show_currency' => (bool) $this->input('show_currency', true),
                'show_dates' => (bool) $this->input('show_dates', true),
                'theme' => $this->input('theme', 'default')
            ];
            
            $styleSettings = [
                'width' => $this->input('width', '100%'),
                'height' => $this->input('height', 'auto'),
                'background_color' => $this->input('background_color', '#ffffff'),
                'text_color' => $this->input('text_color', '#333333'),
                'border_radius' => $this->input('border_radius', '5px')
            ];
            
            $widgetId = $this->widgetModel->createWidget([
                'hotel_id' => $hotelId,
                'name' => $this->input('name'),
                'type' => $this->input('type'),
                'settings' => $settings,
                'style_settings' => $styleSettings,
                'is_active' => 1
            ]);
            
            return $this->redirect('/customer/widgets')->with('success', 'Widget created successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Show widget details
     */
    public function show($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->redirect('/customer/widgets')->with('error', 'Access denied');
        }
        
        $widget = $this->widgetModel->getWithSettings($id);
        
        if (!$widget) {
            return $this->redirect('/customer/widgets')->with('error', 'Widget not found');
        }
        
        $hotel = $this->hotelModel->find($widget['hotel_id']);
        
        // Get widget statistics
        $stats = $this->statisticModel->getByWidget($id, [
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d')
        ]);
        
        // Get embed code
        $embedCode = $this->generateEmbedCode($widget['code']);
        
        return $this->view('customer.widgets.show', [
            'title' => 'Widget Details',
            'widget' => $widget,
            'hotel' => $hotel,
            'stats' => $stats,
            'embed_code' => $embedCode
        ]);
    }
    
    /**
     * Show edit widget form
     */
    public function edit($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->redirect('/customer/widgets')->with('error', 'Access denied');
        }
        
        $widget = $this->widgetModel->getWithSettings($id);
        
        if (!$widget) {
            return $this->redirect('/customer/widgets')->with('error', 'Widget not found');
        }
        
        $hotel = $this->hotelModel->find($widget['hotel_id']);
        $userId = $this->auth->id();
        $hotels = $this->hotelModel->getByUser($userId);
        
        return $this->view('customer.widgets.edit', [
            'title' => 'Edit Widget',
            'widget' => $widget,
            'hotel' => $hotel,
            'hotels' => $hotels
        ]);
    }
    
    /**
     * Update widget
     */
    public function update($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->redirect('/customer/widgets')->with('error', 'Access denied');
        }
        
        try {
            $this->validate([
                'name' => 'required|min:3',
                'type' => 'required'
            ]);
            
            $settings = [
                'rate_limit' => (int) $this->input('rate_limit', 10),
                'show_currency' => (bool) $this->input('show_currency', true),
                'show_dates' => (bool) $this->input('show_dates', true),
                'theme' => $this->input('theme', 'default')
            ];
            
            $styleSettings = [
                'width' => $this->input('width', '100%'),
                'height' => $this->input('height', 'auto'),
                'background_color' => $this->input('background_color', '#ffffff'),
                'text_color' => $this->input('text_color', '#333333'),
                'border_radius' => $this->input('border_radius', '5px')
            ];
            
            $this->widgetModel->update($id, [
                'name' => $this->input('name'),
                'type' => $this->input('type'),
                'settings' => json_encode($settings),
                'style_settings' => json_encode($styleSettings)
            ]);
            
            return $this->redirect('/customer/widgets')->with('success', 'Widget updated successfully');
            
        } catch (\Exception $e) {
            return $this->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Delete widget
     */
    public function destroy($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->redirect('/customer/widgets')->with('error', 'Access denied');
        }
        
        try {
            $this->widgetModel->delete($id);
            return $this->redirect('/customer/widgets')->with('success', 'Widget deleted successfully');
        } catch (\Exception $e) {
            return $this->redirect('/customer/widgets')->with('error', 'Error deleting widget: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle widget status
     */
    public function toggle($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $widget = $this->widgetModel->find($id);
        
        if (!$widget) {
            return $this->json(['error' => 'Widget not found'], 404);
        }
        
        $newStatus = $widget['is_active'] ? 0 : 1;
        $this->widgetModel->update($id, ['is_active' => $newStatus]);
        
        return $this->json([
            'success' => true,
            'status' => $newStatus,
            'message' => $newStatus ? 'Widget activated' : 'Widget deactivated'
        ]);
    }
    
    /**
     * Get widget statistics
     */
    public function statistics($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $period = $this->input('period', '30');
        $dateFrom = date('Y-m-d', strtotime("-{$period} days"));
        $dateTo = date('Y-m-d');
        
        $stats = $this->statisticModel->getByWidget($id, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        // Get aggregated data
        $widget = $this->widgetModel->find($id);
        $aggregated = $this->statisticModel->getAggregated($widget['hotel_id'], 'views', 'daily', [
            'widget_id' => $id,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        return $this->json([
            'stats' => $stats,
            'aggregated' => $aggregated,
            'period' => $period
        ]);
    }
    
    /**
     * Generate embed code
     */
    private function generateEmbedCode($widgetCode)
    {
        $embedUrl = url("widget/embed/{$widgetCode}");
        
        return [
            'iframe' => "<iframe src=\"{$embedUrl}\" width=\"100%\" height=\"400\" frameborder=\"0\"></iframe>",
            'javascript' => "<script src=\"" . url('assets/js/widget-embed.js') . "\" data-widget=\"{$widgetCode}\"></script>",
            'url' => $embedUrl
        ];
    }
    
    /**
     * Preview widget
     */
    public function preview($id)
    {
        if (!$this->authz->canManageWidgets($id)) {
            return $this->redirect('/customer/widgets')->with('error', 'Access denied');
        }
        
        $widget = $this->widgetModel->getWithSettings($id);
        
        if (!$widget) {
            return $this->redirect('/customer/widgets')->with('error', 'Widget not found');
        }
        
        return $this->redirect("/widget/embed/{$widget['code']}");
    }
}
