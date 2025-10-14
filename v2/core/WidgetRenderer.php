<?php

namespace Core;

/**
 * Widget Rendering System
 */
class WidgetRenderer
{
    private $widget;
    private $hotel;
    private $settings;
    private $theme = 'default';
    private $language = 'en';
    
    public function __construct($widget, $hotel = null)
    {
        $this->widget = $widget;
        $this->hotel = $hotel;
        $this->loadSettings();
    }
    
    /**
     * Load widget settings
     */
    private function loadSettings()
    {
        $this->settings = json_decode($this->widget['settings'] ?? '{}', true);
        $this->theme = $this->settings['theme'] ?? 'default';
        $this->language = $this->settings['language'] ?? 'en';
    }
    
    /**
     * Render widget HTML
     */
    public function render()
    {
        $widgetType = $this->widget['type'];
        
        switch ($widgetType) {
            case 'search':
                return $this->renderSearchWidget();
            case 'rates':
                return $this->renderRatesWidget();
            case 'booking':
                return $this->renderBookingWidget();
            case 'comparison':
                return $this->renderComparisonWidget();
            default:
                return $this->renderDefaultWidget();
        }
    }
    
    /**
     * Render search widget
     */
    private function renderSearchWidget()
    {
        $data = [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language
        ];
        
        return $this->renderTemplate('search', $data);
    }
    
    /**
     * Render rates widget
     */
    private function renderRatesWidget()
    {
        // Get rates for hotel
        $rates = $this->getRatesForWidget();
        
        $data = [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'rates' => $rates,
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language
        ];
        
        return $this->renderTemplate('rates', $data);
    }
    
    /**
     * Render booking widget
     */
    private function renderBookingWidget()
    {
        $data = [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language,
            'booking_url' => $this->generateBookingUrl()
        ];
        
        return $this->renderTemplate('booking', $data);
    }
    
    /**
     * Render comparison widget
     */
    private function renderComparisonWidget()
    {
        $comparison = $this->getComparisonData();
        
        $data = [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'comparison' => $comparison,
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language
        ];
        
        return $this->renderTemplate('comparison', $data);
    }
    
    /**
     * Render default widget
     */
    private function renderDefaultWidget()
    {
        $data = [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language
        ];
        
        return $this->renderTemplate('default', $data);
    }
    
    /**
     * Render widget template
     */
    private function renderTemplate($type, $data)
    {
        $templatePath = __DIR__ . "/../resources/widgets/{$this->theme}/{$type}.php";
        
        if (!file_exists($templatePath)) {
            $templatePath = __DIR__ . "/../resources/widgets/default/{$type}.php";
        }
        
        if (!file_exists($templatePath)) {
            return $this->renderFallbackWidget($data);
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include template
        include $templatePath;
        
        // Get output and clean buffer
        $html = ob_get_clean();
        
        // Add widget tracking
        $html = $this->addTracking($html);
        
        return $html;
    }
    
    /**
     * Render fallback widget
     */
    private function renderFallbackWidget($data)
    {
        $widget = $data['widget'];
        $hotel = $data['hotel'];
        
        $html = '<div class="hotel-widget" data-widget-id="' . $widget['id'] . '">';
        $html .= '<div class="widget-header">';
        $html .= '<h3>' . htmlspecialchars($widget['name']) . '</h3>';
        $html .= '</div>';
        $html .= '<div class="widget-content">';
        
        if ($hotel) {
            $html .= '<h4>' . htmlspecialchars($hotel['name']) . '</h4>';
            $html .= '<p>' . htmlspecialchars($hotel['city'] . ', ' . $hotel['country']) . '</p>';
        }
        
        $html .= '<p>Widget content will be displayed here.</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $this->addTracking($html);
    }
    
    /**
     * Add tracking code to widget
     */
    private function addTracking($html)
    {
        $trackingCode = $this->generateTrackingCode();
        
        // Add tracking script before closing div
        $html = str_replace('</div>', $trackingCode . '</div>', $html);
        
        return $html;
    }
    
    /**
     * Generate tracking JavaScript
     */
    private function generateTrackingCode()
    {
        $widgetId = $this->widget['id'];
        $trackingUrl = url('/api/v1/widgets/' . $widgetId . '/track');
        
        return "
        <script>
        (function() {
            var widgetId = {$widgetId};
            var trackingUrl = '{$trackingUrl}';
            
            // Track widget view
            fetch(trackingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    event: 'view',
                    timestamp: Date.now(),
                    url: window.location.href,
                    referrer: document.referrer
                })
            });
            
            // Track widget clicks
            document.addEventListener('click', function(e) {
                if (e.target.closest('[data-widget-id=\"' + widgetId + '\"]')) {
                    fetch(trackingUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            event: 'click',
                            timestamp: Date.now(),
                            element: e.target.tagName,
                            url: window.location.href
                        })
                    });
                }
            });
        })();
        </script>";
    }
    
    /**
     * Get rates for widget
     */
    private function getRatesForWidget()
    {
        if (!$this->hotel) {
            return [];
        }
        
        $db = Database::getInstance();
        
        $checkIn = date('Y-m-d', strtotime('+1 day'));
        $checkOut = date('Y-m-d', strtotime('+2 days'));
        
        // Override with widget settings if available
        if (isset($this->settings['check_in'])) {
            $checkIn = $this->settings['check_in'];
        }
        if (isset($this->settings['check_out'])) {
            $checkOut = $this->settings['check_out'];
        }
        
        $rates = $db->select(
            "SELECT * FROM rates 
             WHERE hotel_id = ? 
             AND check_in >= ? 
             AND check_out <= ?
             ORDER BY price ASC
             LIMIT 10",
            [$this->hotel['id'], $checkIn, $checkOut]
        );
        
        return $rates;
    }
    
    /**
     * Get comparison data
     */
    private function getComparisonData()
    {
        if (!$this->hotel) {
            return [];
        }
        
        $db = Database::getInstance();
        
        $comparison = $db->selectOne(
            "SELECT * FROM rate_comparisons 
             WHERE hotel_id = ? 
             ORDER BY created_at DESC 
             LIMIT 1",
            [$this->hotel['id']]
        );
        
        if ($comparison) {
            return json_decode($comparison['comparison_data'], true);
        }
        
        return [];
    }
    
    /**
     * Generate booking URL
     */
    private function generateBookingUrl()
    {
        if (!$this->hotel) {
            return '#';
        }
        
        $baseUrl = $this->settings['booking_url'] ?? 'https://booking.com';
        $hotelCode = $this->hotel['code'] ?? $this->hotel['id'];
        
        $params = [
            'hotel_id' => $hotelCode,
            'checkin' => date('Y-m-d', strtotime('+1 day')),
            'checkout' => date('Y-m-d', strtotime('+2 days')),
            'adults' => 2,
            'children' => 0
        ];
        
        // Override with widget settings
        if (isset($this->settings['check_in'])) {
            $params['checkin'] = $this->settings['check_in'];
        }
        if (isset($this->settings['check_out'])) {
            $params['checkout'] = $this->settings['check_out'];
        }
        if (isset($this->settings['adults'])) {
            $params['adults'] = $this->settings['adults'];
        }
        if (isset($this->settings['children'])) {
            $params['children'] = $this->settings['children'];
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }
    
    /**
     * Render widget as JSON (for API)
     */
    public function renderJson()
    {
        return [
            'widget' => $this->widget,
            'hotel' => $this->hotel,
            'html' => $this->render(),
            'settings' => $this->settings,
            'theme' => $this->theme,
            'language' => $this->language
        ];
    }
    
    /**
     * Get widget CSS
     */
    public function getCSS()
    {
        $cssPath = __DIR__ . "/../resources/widgets/{$this->theme}/style.css";
        
        if (!file_exists($cssPath)) {
            $cssPath = __DIR__ . "/../resources/widgets/default/style.css";
        }
        
        if (file_exists($cssPath)) {
            return file_get_contents($cssPath);
        }
        
        return $this->getDefaultCSS();
    }
    
    /**
     * Get default CSS
     */
    private function getDefaultCSS()
    {
        return "
        .hotel-widget {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            font-family: Arial, sans-serif;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .widget-header h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
        }
        
        .widget-content {
            color: #666;
        }
        
        .widget-content h4 {
            margin: 0 0 10px 0;
            color: #444;
        }
        
        .rate-item {
            padding: 10px;
            border: 1px solid #eee;
            margin: 5px 0;
            border-radius: 4px;
        }
        
        .rate-price {
            font-weight: bold;
            color: #007bff;
            font-size: 16px;
        }
        
        .booking-button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .booking-button:hover {
            background: #0056b3;
        }
        ";
    }
    
    /**
     * Static method to render widget by ID
     */
    public static function renderById($widgetId)
    {
        $db = Database::getInstance();
        
        $widget = $db->selectOne(
            "SELECT w.*, h.* FROM widgets w 
             LEFT JOIN hotels h ON w.hotel_id = h.id 
             WHERE w.id = ? AND w.is_active = 1",
            [$widgetId]
        );
        
        if (!$widget) {
            return '<div class="widget-error">Widget not found</div>';
        }
        
        $renderer = new self($widget);
        return $renderer->render();
    }
    
    /**
     * Static method to render widget embed code
     */
    public static function getEmbedCode($widgetId)
    {
        $embedUrl = url("/widgets/{$widgetId}/embed");
        
        return '<iframe src="' . $embedUrl . '" width="100%" height="400" frameborder="0"></iframe>';
    }
}
