<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Widget;
use App\Models\Hotel;

/**
 * API Controller for Rate Comparison
 */
class ApiController extends BaseController
{
    private $widgetModel;
    private $hotelModel;
    
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
        
        $this->widgetModel = new Widget();
        $this->hotelModel = new Hotel();
    }
    
    /**
     * Get rate comparison for widget
     * GET /api/{widgetCode}?currency=TRY&checkin=2025-10-20&checkout=2025-10-21&adult=2&child=0&infant=0
     */
    public function getRequest($widgetCode)
    {
        try {
            // Log API request
            $this->logMessage("API Request started for widget: " . $widgetCode, 'INFO');
            
            // Get request parameters
            $currency = $this->input('currency', 'TRY');
            $checkin = $this->input('checkin', date('Y-m-d'));
            $checkout = $this->input('checkout', date('Y-m-d', strtotime('+1 day')));
            $adult = (int)$this->input('adult', 2);
            $child = (int)$this->input('child', 0);
            $infant = (int)$this->input('infant', 0);
            
            $this->logMessage("API Parameters - Currency: {$currency}, CheckIn: {$checkin}, CheckOut: {$checkout}", 'INFO');
            
            // Format dates
            $checkIn = date("Y-m-d", strtotime(str_replace('/', '-', $checkin)));
            $checkOut = date("Y-m-d", strtotime(str_replace('/', '-', $checkout)));
            
            // Find widget and hotel
            $widget = $this->getWidgetByCode($widgetCode);
            if (!$widget) {
                $this->logMessage("Widget not found: " . $widgetCode, 'ERROR');
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Widget not found'
                ], 404);
            }
            
            $this->logMessage("Widget found: " . $widgetCode . " for hotel ID: " . $widget['hotel_id'], 'INFO');
            
            $hotel = $this->getHotelById($widget['hotel_id']);
            if (!$hotel) {
                $this->logMessage("Hotel not found for ID: " . $widget['hotel_id'], 'ERROR');
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Hotel not found'
                ], 404);
            }
            
            $this->logMessage("Hotel found: " . $hotel['name'] . " (ID: " . $hotel['id'] . ")", 'INFO');
            
            // Initialize response
            $response = [
                "status" => "success",
                "data" => [
                    "platforms" => [],
                    "request_info" => [
                        "currency" => $currency,
                        "checkin" => $checkIn,
                        "checkout" => $checkOut,
                        "adult" => $adult,
                        "child" => $child,
                        "infant" => $infant,
                        "widget_code" => $widgetCode,
                        "hotel_name" => $hotel['name']
                    ]
                ],
            ];
            
            // Get prices from different platforms
            $this->addDefaultIbePlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            $this->addBookingPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            $this->addHotelsPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            $this->addTatilSepetiPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            $this->addOtelzPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            $this->addEtsturPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
            
            return $this->jsonResponse($response);
            
        } catch (\Exception $e) {
            // Log error without sensitive data
            $this->logMessage("API getRequest error for widget: " . $widgetCode . " - " . $e->getMessage(), 'ERROR');
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get specific platform prices
     * POST /api/price
     */
    public function getPrice()
    {
        try {
            $hotelCode = $this->input('hotelCode');
            $currency = $this->input('currency', 'TRY');
            $startDate = $this->input('startDate');
            $endDate = $this->input('endDate');
            $adults = $this->input('adults', 2);
            $types = $this->input('type', []);
            
            if (!is_array($types)) {
                $types = [$types];
            }
            
            $widget = $this->getWidgetByCode($hotelCode);
            if (!$widget) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Widget not found'
                ], 404);
            }
            
            $hotel = $this->getHotelById($widget['hotel_id']);
            if (!$hotel) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Hotel not found'
                ], 404);
            }
            
            $response = [
                "currency" => $currency,
                "checkin" => $startDate,
                "checkout" => $endDate,
                "adults" => $adults,
                "prices" => []
            ];
            
            // Get prices for requested platforms
            foreach ($types as $type) {
                switch ($type) {
                    case 'sabee':
                        $response["prices"]["sabee"] = $this->getSabeePrice($hotel, $currency, $startDate, $endDate);
                        break;
                    case 'booking':
                        $response["prices"]["booking"] = $this->getBookingPrice($hotel, $currency, $startDate, $endDate);
                        break;
                    case 'hotels':
                        $response["prices"]["hotels"] = $this->getHotelsPrice($hotel, $currency, $startDate, $endDate);
                        break;
                    case 'tatilsepeti':
                        $response["prices"]["tatilsepeti"] = $this->getTatilSepetiPrice($hotel, $currency, $startDate, $endDate);
                        break;
                    case 'otelz':
                        $response["prices"]["otelz"] = $this->getOtelzPrice($hotel, $currency, $startDate, $endDate);
                        break;
                    case 'min':
                        $response["prices"] = $this->getAllPricesWithMin($hotel, $currency, $startDate, $endDate);
                        break;
                }
            }
            
            return $this->jsonResponse($response);
            
        } catch (\Exception $e) {
            error_log("API getPrice error: " . $e->getMessage());
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Add default IBE platform to response
     */
    private function addDefaultIbePlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        $defaultIbe = $hotel['default_ibe'] ?? 'sabeeapp';
        
        switch ($defaultIbe) {
            case "sabeeapp":
                if ($hotel['sabee_is_active'] && !empty($hotel['sabee_hotel_id'])) {
                    $price = $this->getSabeePrice($hotel, $currency, $checkIn, $checkOut);
                    $this->addPlatformToResponse($response, 'sabeeapp', 'SabeeApp', $price, $hotel['sabee_url'], $currency);
                }
                break;
                
            case "reseliva":
                if ($hotel['reseliva_is_active'] && !empty($hotel['reseliva_hotel_id'])) {
                    $price = $this->getReselivaPrice($hotel, $currency, $checkIn, $checkOut);
                    $url = $this->getReselivaUrl($hotel, $currency, $checkIn, $checkOut);
                    $this->addPlatformToResponse($response, 'reseliva', 'Reseliva', $price, $url, $currency);
                }
                break;
                
            case "hotelrunner":
                if ($hotel['is_hotelrunner_active'] && !empty($hotel['hotelrunner_url'])) {
                    $result = $this->getHotelRunnerPrice($hotel, $currency, $checkIn, $checkOut);
                    if ($result !== "NA") {
                        $this->addPlatformToResponse($response, 'hotelrunner', 'HotelRunner', $result['price'], $result['url'], $currency);
                    }
                }
                break;
        }
    }
    
    /**
     * Add Booking.com platform to response
     */
    private function addBookingPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        $this->logMessage("Booking.com: Starting platform check for hotel " . $hotel['name'], 'DEBUG');
        $this->logMessage("Booking.com: booking_is_active = " . ($hotel['booking_is_active'] ? 'true' : 'false'), 'DEBUG');
        $this->logMessage("Booking.com: booking_url = " . ($hotel['booking_url'] ?? 'null'), 'DEBUG');
        
        if ($hotel['booking_is_active'] && !empty($hotel['booking_url'])) {
            $this->logMessage("Booking.com: Processing hotel " . $hotel['name'] . " with URL " . $hotel['booking_url'], 'INFO');
            
            $price = $this->getBookingPrice($hotel, $currency, $checkIn, $checkOut);
            $this->logMessage("Booking.com: Received price result: " . $price, 'DEBUG');
            
            // Only add to response if price is valid
            if ($price !== "NA") {
                $url = $this->getBookingUrl($hotel, $currency, $checkIn, $checkOut);
                $this->addPlatformToResponse($response, 'booking', 'Booking.com', $price, $url, $currency);
                $this->logMessage("Booking.com: Successfully added to response with price " . $price, 'INFO');
            } else {
                $this->logMessage("Booking.com: Price not available for " . $hotel['name'] . " - not adding to response", 'WARNING');
            }
        } else {
            $booking_active = $hotel['booking_is_active'] ?? 'not_set';
            $booking_url = $hotel['booking_url'] ?? 'not_set';
            $this->logMessage("Booking.com: Skipped for " . $hotel['name'] . " - Active: {$booking_active}, URL: {$booking_url}", 'WARNING');
        }
    }
    
    /**
     * Add Hotels.com platform to response
     */
    private function addHotelsPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['hotels_is_active'] && !empty($hotel['hotels_url'])) {
            $price = $this->getHotelsPrice($hotel, $currency, $checkIn, $checkOut);
            $this->addPlatformToResponse($response, 'hotels', 'Hotels.com', $price, $hotel['hotels_url'], $currency);
        }
    }
    
    /**
     * Add TatilSepeti platform to response
     */
    private function addTatilSepetiPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['tatilsepeti_is_active'] && !empty($hotel['tatilsepeti_url'])) {
            $price = $this->getTatilSepetiPrice($hotel, $currency, $checkIn, $checkOut);
            $this->addPlatformToResponse($response, 'tatilsepeti', 'Tatil Sepeti', $price, $hotel['tatilsepeti_url'], $currency);
        }
    }
    
    /**
     * Add OtelZ platform to response
     */
    private function addOtelzPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['otelz_is_active'] && !empty($hotel['otelz_url'])) {
            // Validate facility ID before making API call
            if (!is_numeric($hotel['otelz_url'])) {
                $this->logMessage("OtelZ: Skipping invalid facility ID - " . $hotel['otelz_url'], 'WARNING');
                return;
            }
            
            $price = $this->getOtelzPrice($hotel, $currency, $checkIn, $checkOut);
            
            // Only add to response if price is valid
            if ($price !== "NA") {
                $url = $this->getOtelzUrl($hotel, $currency, $checkIn, $checkOut);
                $this->addPlatformToResponse($response, 'otelz', 'OtelZ', $price, $url, $currency);
            } else {
                $this->logMessage("OtelZ: Price not available for facility ID " . $hotel['otelz_url'], 'INFO');
            }
        }
    }
    
    /**
     * Add ETSTur platform to response
     */
    private function addEtsturPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['is_etstur_active'] && !empty($hotel['etstur_hotel_id'])) {
            $this->logMessage("ETSTur: Processing hotel " . $hotel['name'] . " with hotel_id " . $hotel['etstur_hotel_id'], 'INFO');
            
            $result = $this->getEtsturPrice($hotel, $currency, $checkIn, $checkOut);
            
            if ($result !== "NA" && is_array($result)) {
                // result contains: ['price' => X, 'currency' => Y, 'url' => Z]
                $priceData = ['price' => $result['price'], 'currency' => $result['currency']];
                $url = $result['url'];
                
                $this->addPlatformToResponse($response, 'etstur', 'ETSTur', $priceData, $url, $currency);
                $this->logMessage("ETSTur: Successfully added to response with price " . $result['price'] . " " . $result['currency'], 'INFO');
            } else {
                $this->logMessage("ETSTur: Price not available for hotel " . $hotel['name'], 'WARNING');
            }
        } else {
            $etstur_active = $hotel['is_etstur_active'] ?? 'not_set';
            $etstur_id = $hotel['etstur_hotel_id'] ?? 'not_set';
            $this->logMessage("ETSTur: Skipped for " . $hotel['name'] . " - Active: {$etstur_active}, Hotel ID: {$etstur_id}", 'WARNING');
        }
    }
    
    /**
     * Add platform data to response
     * 
     * @param array $response Response array reference
     * @param string $name Platform name
     * @param string $displayName Platform display name
     * @param mixed $priceData Can be: numeric value (assumes TRY), "NA", or ['price' => X, 'currency' => 'EUR']
     * @param string $url Platform URL
     * @param string $targetCurrency Target currency for conversion
     */
    private function addPlatformToResponse(&$response, $name, $displayName, $priceData, $url, $targetCurrency = 'TRY')
    {
        // Handle both old format (numeric) and new format (array with currency)
        $price = null;
        $sourceCurrency = 'TRY'; // Default assume TRY
        
        if (is_array($priceData) && isset($priceData['price'])) {
            // New format: ['price' => X, 'currency' => 'EUR']
            $price = $priceData['price'];
            $sourceCurrency = $priceData['currency'] ?? 'TRY';
        } else {
            // Old format: just a number or "NA"
            $price = $priceData;
            $sourceCurrency = 'TRY';
        }
        
        $status = ($price == "NA" || $price == "" || $price === null) ? "failed" : "success";
        
        // Smart currency conversion: only convert if source and target currencies differ
        if ($status === "success" && $sourceCurrency !== $targetCurrency) {
            // First convert to TRY if not already
            if ($sourceCurrency !== 'TRY') {
                $price = $this->convertToTRY($price, $sourceCurrency);
                $sourceCurrency = 'TRY';
            }
            
            // Then convert from TRY to target currency
            if ($targetCurrency !== 'TRY') {
                $price = $this->convertFromTRY($price, $targetCurrency);
            }
            
            $this->logMessage("Platform {$name}: Smart conversion applied - Final price: {$price} {$targetCurrency}", 'DEBUG');
        } else if ($status === "success") {
            $this->logMessage("Platform {$name}: No conversion needed - Price already in {$targetCurrency}", 'DEBUG');
        }
        
        $data = [
            "status" => $status,
            "name" => $name,
            "displayName" => $displayName,
            "price" => $price,
            "url" => $url,
        ];
        
        $response["data"]["platforms"][] = $data;
    }
    
    /**
     * Get widget by code
     */
    private function getWidgetByCode($code)
    {
        try {
            $sql = "SELECT * FROM widgets WHERE code = ? AND type = 'main' LIMIT 1";
            $result = $this->widgetModel->raw($sql, [$code]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            error_log("Get widget error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get hotel by ID
     */
    private function getHotelById($hotelId)
    {
        try {
            $sql = "SELECT * FROM hotels WHERE id = ? LIMIT 1";
            $result = $this->hotelModel->raw($sql, [$hotelId]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            error_log("Get hotel error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Real price functions from old system helpers
     */
    private function getSabeePrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getSabeeRoomsPrice($hotel['sabee_hotel_id'], $currency, $checkIn, $checkOut);
    }
    
    private function getReselivaPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getReselivaPriceApi($hotel['reseliva_hotel_id'], $currency, $checkIn, $checkOut);
    }
    
    private function getReselivaUrl($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would generate actual Reseliva URL
        return "https://reseliva.com/hotel/" . $hotel['reseliva_hotel_id'];
    }
    
    private function getHotelRunnerPrice($hotel, $currency, $checkIn, $checkOut)
    {
        // HotelRunner returns "NA" in old system
        $this->logMessage("HotelRunner: Not implemented - returning NA", 'INFO');
        return "NA";
    }
    
    private function getBookingPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getBookingPriceReal($hotel['booking_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getBookingUrl($hotel, $currency, $checkIn, $checkOut)
    {
        $url = $hotel['booking_url'];
        
        // Ensure URL ends with .tr.html
        if (substr($url, -8) != ".tr.html") {
            $url = str_replace(".html", ".tr.html", $url);
            $this->logMessage("Booking.com URL: Corrected to .tr.html - " . $url, 'DEBUG');
        }
        
        // Add parameters including selected_currency
        $params = [
            'selected_currency' => $currency,
            'checkin' => $checkIn,
            'checkout' => $checkOut
        ];
        
        $queryString = http_build_query($params);
        $finalUrl = $url . "?" . $queryString;
        
        $this->logMessage("Booking.com URL: Generated final URL - " . $finalUrl, 'DEBUG');
        
        return $finalUrl;
    }
    
    private function getHotelsPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getHotelsPriceReal($hotel['hotels_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getTatilSepetiPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getTatilSepetiPriceReal($hotel['tatilsepeti_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getOtelzPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getOtelZPriceReal($hotel['otelz_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getOtelzUrl($hotel, $currency, $checkIn, $checkOut)
    {
        // OtelZ uses facility ID directly in API calls
        return "https://otelz.com/hotel/" . $hotel['otelz_url'];
    }
    
    private function getEtsturPrice($hotel, $currency, $checkIn, $checkOut)
    {
        $result = $this->getEtsturPriceReal($hotel['etstur_hotel_id'], $currency, $checkIn, $checkOut);
        
        if ($result !== "NA" && is_array($result) && isset($result['price'])) {
            // Generate Etstur URL (if they have a direct URL pattern)
            $url = "https://www.etstur.com/otel/" . $hotel['etstur_hotel_id'];
            
            // result already has ['price' => X, 'currency' => Y] format
            // Add URL to it
            $result['url'] = $url;
            
            return $result;
        }
        
        return "NA";
    }
    
    /**
     * Get all prices with minimum calculation
     */
    private function getAllPricesWithMin($hotel, $currency, $startDate, $endDate)
    {
        $prices = [
            'booking' => $this->getBookingPrice($hotel, $currency, $startDate, $endDate),
            'hotels' => $this->getHotelsPrice($hotel, $currency, $startDate, $endDate),
            'tatilsepeti' => $this->getTatilSepetiPrice($hotel, $currency, $startDate, $endDate),
            'reseliva' => $this->getReselivaPrice($hotel, $currency, $startDate, $endDate),
            'otelz' => $this->getOtelzPrice($hotel, $currency, $startDate, $endDate),
            'sabee' => $this->getSabeePrice($hotel, $currency, $startDate, $endDate),
        ];
        
        // Calculate minimum price
        $validPrices = array_filter($prices, function($price) {
            return !in_array($price, [0, "", "NA", null]);
        });
        
        $prices['min'] = !empty($validPrices) ? min($validPrices) : "NA";
        
        return $prices;
    }
    
    /**
     * Return JSON response
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    // ===== HELPER FUNCTIONS FROM OLD SYSTEM =====
    
    /**
     * Custom log function
     */
    private function logMessage($message, $level = 'INFO')
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also use error_log as fallback
        error_log($message);
    }
    
    /**
     * Validate API credentials
     */
    private function validateApiCredentials()
    {
        $missing = [];
        
        if (!env('RESELIVA_USERNAME')) $missing[] = 'RESELIVA_USERNAME';
        if (!env('RESELIVA_PASSWORD')) $missing[] = 'RESELIVA_PASSWORD';
        if (!env('OTELZ_USERNAME')) $missing[] = 'OTELZ_USERNAME';
        if (!env('OTELZ_PASSWORD')) $missing[] = 'OTELZ_PASSWORD';
        
        if (!empty($missing)) {
            $this->logMessage("Missing API credentials: " . implode(', ', $missing), 'ERROR');
        }
        
        return empty($missing);
    }
    
    /**
     * Get HTML content via cURL
     */
    private function getHTML($url, $timeout = 30, $type = 0)
    {
        $header = [];
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[0] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Pragma: ";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

        if ($type == 2) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0');
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_PROXY, 'brd.superproxy.io:22225');
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'brd-customer-hl_e5f2315f-zone-datacenter_proxy1-country-nl:uvmqoi66peju');
        }

        return @curl_exec($ch);
    }
    
    /**
     * Search text between delimiters
     */
    private function search($begin, $end, $text)
    {
        @preg_match_all('/' . preg_quote($begin, '/') . '(.*?)' . preg_quote($end, '/') . '/i', $text, $m);
        return @$m[1];
    }
    
    /**
     * Get cached currency exchange rates from TCMB
     */
    private function getCurrencyRates()
    {
        $cacheFile = __DIR__ . '/../../cache/currency_rates.json';
        $cacheDir = dirname($cacheFile);
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        // Check if cache file exists and is fresh (less than 24 hours old)
        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            $now = time();
            $cacheAge = $now - $cacheTime;
            
            // Cache is valid for 24 hours (86400 seconds)
            if ($cacheAge < 86400) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData && isset($cachedData['rates'])) {
                    $this->logMessage("Currency: Using cached rates (age: " . round($cacheAge/3600, 1) . " hours)", 'DEBUG');
                    return $cachedData['rates'];
                }
            } else {
                $this->logMessage("Currency: Cache expired (age: " . round($cacheAge/3600, 1) . " hours), fetching new rates", 'DEBUG');
            }
        } else {
            $this->logMessage("Currency: No cache file found, fetching rates from TCMB", 'DEBUG');
        }
        
        // Fetch fresh rates from TCMB
        $rates = $this->fetchFreshCurrencyRates();
        
        if ($rates) {
            // Save to cache
            $cacheData = [
                'timestamp' => time(),
                'date' => date('Y-m-d H:i:s'),
                'rates' => $rates
            ];
            
            file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));
            $this->logMessage("Currency: Rates cached successfully", 'DEBUG');
        }
        
        return $rates;
    }
    
    /**
     * Fetch fresh currency rates from TCMB
     */
    private function fetchFreshCurrencyRates()
    {
        try {
            $this->logMessage("Currency: Fetching fresh exchange rates from TCMB", 'INFO');
            
            $contextOptions = [
                'http' => [
                    'timeout' => 15,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ];
            
            $file = file_get_contents(
                'https://www.tcmb.gov.tr/kurlar/today.xml',
                false,
                stream_context_create($contextOptions)
            );
            
            if (!$file) {
                $this->logMessage("Currency: Failed to fetch TCMB XML", 'ERROR');
                return $this->getFallbackRates();
            }
            
            $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
            $currency_xml = simplexml_load_string($temp);
            
            if (!$currency_xml) {
                $this->logMessage("Currency: Failed to parse TCMB XML", 'ERROR');
                return $this->getFallbackRates();
            }
            
            $rates = [
                'eur_buying' => (float)$currency_xml->Currency[3]->BanknoteBuying,
                'usd_buying' => (float)$currency_xml->Currency[0]->BanknoteBuying,
                'usd_eur_cross' => (float)$currency_xml->Currency[3]->CrossRateOther,
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
            // Validate rates (should be reasonable values)
            if ($rates['eur_buying'] < 20 || $rates['eur_buying'] > 50 || 
                $rates['usd_buying'] < 20 || $rates['usd_buying'] > 50) {
                $this->logMessage("Currency: Invalid rates detected - EUR: {$rates['eur_buying']}, USD: {$rates['usd_buying']}", 'ERROR');
                return $this->getFallbackRates();
            }
            
            $this->logMessage("Currency: Fresh rates fetched - EUR: {$rates['eur_buying']}, USD: {$rates['usd_buying']}", 'INFO');
            
            return $rates;
            
        } catch (\Exception $e) {
            $this->logMessage("Currency: Error fetching fresh rates - " . $e->getMessage(), 'ERROR');
            return $this->getFallbackRates();
        }
    }
    
    /**
     * Get fallback currency rates when TCMB is unavailable
     */
    private function getFallbackRates()
    {
        $this->logMessage("Currency: Using fallback rates", 'WARNING');
        
        // Fallback to approximate rates (should be updated periodically)
        return [
            'eur_buying' => 34.50,
            'usd_buying' => 32.00,
            'usd_eur_cross' => 1.08,
            'last_updated' => 'fallback',
            'is_fallback' => true
        ];
    }
    
    /**
     * Convert price to TRY (Turkish Lira)
     */
    private function convertToTRY($price, $fromCurrency)
    {
        // If already TRY, return as is
        if ($fromCurrency === 'TRY' || $fromCurrency === 'TL') {
            return round($price);
        }
        
        // If price is not numeric or "NA", return as is
        if (!is_numeric($price) || $price === "NA") {
            return $price;
        }
        
        $rates = $this->getCurrencyRates();
        if (!$rates) {
            $this->logMessage("Currency: Cannot convert {$price} {$fromCurrency} to TRY - rates unavailable", 'WARNING');
            return $price; // Return original price if rates unavailable
        }
        
        $tryPrice = $price;
        
        $cacheInfo = isset($rates['is_fallback']) ? ' (fallback)' : ' (cached)';
        
        switch (strtoupper($fromCurrency)) {
            case 'EUR':
                $tryPrice = $price * $rates['eur_buying'];
                $this->logMessage("Currency: Converted {$price} EUR to {$tryPrice} TRY (rate: {$rates['eur_buying']}{$cacheInfo})", 'DEBUG');
                break;
                
            case 'USD':
                $tryPrice = $price * $rates['usd_buying'];
                $this->logMessage("Currency: Converted {$price} USD to {$tryPrice} TRY (rate: {$rates['usd_buying']}{$cacheInfo})", 'DEBUG');
                break;
                
            default:
                $this->logMessage("Currency: Unknown currency {$fromCurrency}, returning original price", 'WARNING');
                return $price;
        }
        
        return round($tryPrice);
    }
    
    /**
     * Convert price from TRY to target currency
     */
    private function convertFromTRY($tryPrice, $toCurrency)
    {
        // If target is TRY, return as is
        if ($toCurrency === 'TRY' || $toCurrency === 'TL') {
            return round($tryPrice);
        }
        
        // If price is not numeric or "NA", return as is
        if (!is_numeric($tryPrice) || $tryPrice === "NA") {
            return $tryPrice;
        }
        
        $rates = $this->getCurrencyRates();
        if (!$rates) {
            $this->logMessage("Currency: Cannot convert {$tryPrice} TRY to {$toCurrency} - rates unavailable", 'WARNING');
            return $tryPrice; // Return original price if rates unavailable
        }
        
        $convertedPrice = $tryPrice;
        
        $cacheInfo = isset($rates['is_fallback']) ? ' (fallback)' : ' (cached)';
        
        switch (strtoupper($toCurrency)) {
            case 'EUR':
                $convertedPrice = $tryPrice / $rates['eur_buying'];
                $this->logMessage("Currency: Converted {$tryPrice} TRY to {$convertedPrice} EUR (rate: {$rates['eur_buying']}{$cacheInfo})", 'DEBUG');
                break;
                
            case 'USD':
                $convertedPrice = $tryPrice / $rates['usd_buying'];
                $this->logMessage("Currency: Converted {$tryPrice} TRY to {$convertedPrice} USD (rate: {$rates['usd_buying']}{$cacheInfo})", 'DEBUG');
                break;
                
            default:
                $this->logMessage("Currency: Unknown target currency {$toCurrency}, returning TRY price", 'WARNING');
                return $tryPrice;
        }
        
        return round($convertedPrice, 2); // Round to 2 decimals for foreign currencies
    }
    
    /**
     * Get Booking.com price
     */
    private function getBookingPriceReal($url, $currency, $checkinDate, $checkoutDate)
    {
        $this->logMessage("Booking.com API: Starting price request for URL {$url}, currency {$currency}, dates {$checkinDate} to {$checkoutDate}", 'INFO');
        
        // DEBUG: Check if this is returning a mock value
        $this->logMessage("Booking.com DEBUG: This is the REAL implementation, not mock", 'INFO');
        
        // Ensure URL ends with .tr.html
        if (substr($url, -8) != ".tr.html") {
            $url = str_replace(".html", ".tr.html", $url);
            $this->logMessage("Booking.com: URL corrected to " . $url, 'DEBUG');
        }

        $search_url = $url . "?selected_currency=" . $currency . "&checkin=" . $checkinDate . "&checkout=" . $checkoutDate;
        $this->logMessage("Booking.com: Fetching URL - " . $search_url, 'INFO');
        
        // Get currency symbol
        switch ($currency) {
            case "EUR":
                $currency_symbol = "€";
                break;
            case "USD":
                $currency_symbol = "US$";
                break;
            default:
                $currency_symbol = "TL";
        }
        
        // Use only proxy method (type=2)
        $this->logMessage("Booking.com: Using proxy method only", 'DEBUG');
        
        $html = $this->getHTML($search_url, 30, 2);
        
        if (!$html) {
            $this->logMessage("Booking.com: Failed to fetch HTML with proxy method", 'ERROR');
            return "NA";
        }
        
        $this->logMessage("Booking.com: HTML fetched successfully (" . strlen($html) . " bytes) with proxy method", 'DEBUG');

        // Try primary price pattern
        $price_pattern = '"b_price":"' . $currency_symbol;
        $price = $this->search($price_pattern, '"', $html);
        
        if (!empty($price)) {
            $finalPrice = round(preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($price[0]))));
            $this->logMessage("Booking.com: Found primary price - " . $finalPrice . " " . $currency . " with proxy method", 'INFO');
            
            // Convert to TRY if needed
            $tryPrice = $this->convertToTRY($finalPrice, $currency);
            $this->logMessage("Booking.com: Final price after conversion - " . $tryPrice . " TRY", 'INFO');
            
            return $tryPrice;
        }
        
        $this->logMessage("Booking.com: Primary price pattern not found with proxy method", 'DEBUG');
        
        // Try alternative price pattern
        $alternativePrice = $this->search("tarihlerinizde", "gibi", $html);
        
        if (!empty($alternativePrice)) {
            $this->logMessage("Booking.com: Alternative price context found with proxy method", 'DEBUG');
            
            // Search for specific currency in alternative price
            switch ($currency) {
                case "EUR":
                    $currencyPrice = $this->search("tarihlerinizde €", "gibi", $html);
                    break;
                case "USD":
                    $currencyPrice = $this->search("tarihlerinizde US$", "gibi", $html);
                    break;
                default:
                    $currencyPrice = $this->search("tarihlerinizde TL", "gibi", $html);
            }
            
            if (!empty($currencyPrice)) {
                $finalPrice = round(preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($currencyPrice[0]))));
                $this->logMessage("Booking.com: Found alternative price - " . $finalPrice . " " . $currency . " with proxy method", 'INFO');
                
                // Convert to TRY if needed
                $tryPrice = $this->convertToTRY($finalPrice, $currency);
                $this->logMessage("Booking.com: Final alternative price after conversion - " . $tryPrice . " TRY", 'INFO');
                
                return $tryPrice;
            }
        }
        
        $this->logMessage("Booking.com: No price found with proxy method", 'WARNING');
        return "NA";
    }
    
    /**
     * Get Hotels.com price via GraphQL API
     */
    private function getHotelsPriceReal($url, $currency, $startDate, $endDate)
    {
        $this->logMessage("Hotels.com API: Starting price request for URL {$url}, currency {$currency}, dates {$startDate} to {$endDate}", 'INFO');
        
        if (empty($url)) {
            $this->logMessage("Hotels.com: Empty URL provided", 'ERROR');
            return "NA";
        }
        
        try {
            // Parse URL and update query parameters
            $pathInfo = parse_url($url);
            if (!array_key_exists("query", $pathInfo)) {
                $this->logMessage("Hotels.com: No query parameters in URL", 'ERROR');
                return "NA";
            }
            
            $queryString = $pathInfo["query"];
            parse_str($queryString, $queryArray);
            $queryArray["chkin"] = $startDate;
            $queryArray["chkout"] = $endDate;
            $newQueryStr = http_build_query($queryArray);
            $newUrl = "https://" . $pathInfo["host"] . $pathInfo["path"] . "?" . $newQueryStr;
            
            $this->logMessage("Hotels.com: Updated URL - " . $newUrl, 'DEBUG');
            
            // Extract property ID from URL for GraphQL query
            $propertyId = $this->extractHotelsPropertyId($url);
            if (!$propertyId) {
                $this->logMessage("Hotels.com: Could not extract property ID from URL", 'ERROR');
                return "NA";
            }
            
            // Prepare GraphQL query
            $graphqlQuery = $this->buildHotelsGraphQLQuery($propertyId, $startDate, $endDate);
            
            $headers = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: */*',
                'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
                'Accept-Encoding: gzip, deflate, br',
                'Content-Type: application/json',
                'Client-Info: shopping-pwa,743f46197f3b193f505182af7292a80dd14e979b,us-west-2',
                'X-Product-Line: lodging',
                'X-Page-Id: page.Hotels.Infosite.Information,H,30',
                'Origin: https://tr.hotels.com',
                'Referer: ' . $newUrl
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://tr.hotels.com/graphql');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $graphqlQuery);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (!$response || $httpCode !== 200) {
                $this->logMessage("Hotels.com: HTTP error {$httpCode} or empty response", 'ERROR');
                return "NA";
            }
            
            $decodedResponse = json_decode($response, true);
            
            if (!$decodedResponse || !isset($decodedResponse[0]["data"]["propertyOffers"]["stickyBar"])) {
                $this->logMessage("Hotels.com: Invalid response structure or no stickyBar", 'WARNING');
                return "NA";
            }
            
            if ($decodedResponse[0]["data"]["propertyOffers"]["stickyBar"] == null) {
                $this->logMessage("Hotels.com: No offers available (stickyBar is null)", 'INFO');
                return "NA";
            }
            
            // Extract price from response
            $priceData = $decodedResponse[0]["data"]["propertyOffers"]["stickyBar"]["price"];
            if (!isset($priceData["formattedDisplayPrice"])) {
                $this->logMessage("Hotels.com: No formatted display price found", 'WARNING');
                return "NA";
            }
            
            $price = $priceData["formattedDisplayPrice"];
            $price = trim(str_replace(["TL", "₺", "€", "$"], "", $price));
            $price = str_replace([".", ",", " "], "", $price);
            $price = preg_replace('/[^0-9]/', '', $price);
            
            if (!is_numeric($price) || $price <= 0) {
                $this->logMessage("Hotels.com: Invalid price extracted: " . $price, 'WARNING');
                return "NA";
            }
            
            $this->logMessage("Hotels.com: Found price {$price} TRY", 'INFO');
            
            // Convert to TRY if needed (Hotels.com usually returns TRY for Turkish site)
            $finalPrice = $this->convertToTRY($price, 'TRY');
            $this->logMessage("Hotels.com: Final price after conversion - {$finalPrice} TRY", 'INFO');
            
            return $finalPrice;
            
        } catch (\Exception $e) {
            $this->logMessage("Hotels.com: Error - " . $e->getMessage(), 'ERROR');
            return "NA";
        }
    }
    
    /**
     * Extract property ID from Hotels.com URL
     */
    private function extractHotelsPropertyId($url)
    {
        // Try to extract property ID from URL patterns like:
        // https://tr.hotels.com/ho534033/baldan-suites-hotel-restaurant-marmaris-turkiye/
        if (preg_match('/\/ho(\d+)\//', $url, $matches)) {
            return $matches[1];
        }
        
        // Try expediaPropertyId parameter
        if (preg_match('/expediaPropertyId=(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        $this->logMessage("Hotels.com: Could not extract property ID from URL: " . $url, 'WARNING');
        return null;
    }
    
    /**
     * Build GraphQL query for Hotels.com
     */
    private function buildHotelsGraphQLQuery($propertyId, $startDate, $endDate)
    {
        // Parse dates
        $checkIn = date_create($startDate);
        $checkOut = date_create($endDate);
        
        $query = [
            [
                "operationName" => "PropertyOffersQuery",
                "variables" => [
                    "propertyId" => $propertyId,
                    "searchCriteria" => [
                        "primary" => [
                            "dateRange" => [
                                "checkInDate" => [
                                    "day" => (int)$checkIn->format('j'),
                                    "month" => (int)$checkIn->format('n'),
                                    "year" => (int)$checkIn->format('Y')
                                ],
                                "checkOutDate" => [
                                    "day" => (int)$checkOut->format('j'),
                                    "month" => (int)$checkOut->format('n'),
                                    "year" => (int)$checkOut->format('Y')
                                ]
                            ],
                            "destination" => [
                                "regionName" => "Turkey",
                                "regionId" => "6054843"
                            ],
                            "rooms" => [
                                [
                                    "adults" => 2,
                                    "children" => []
                                ]
                            ]
                        ],
                        "secondary" => [
                            "counts" => [],
                            "booleans" => [],
                            "selections" => [
                                [
                                    "id" => "sort",
                                    "value" => "RECOMMENDED"
                                ],
                                [
                                    "id" => "privacyTrackingState",
                                    "value" => "CAN_NOT_TRACK"
                                ],
                                [
                                    "id" => "useRewards",
                                    "value" => "SHOP_WITHOUT_POINTS"
                                ]
                            ],
                            "ranges" => []
                        ]
                    ],
                    "context" => [
                        "siteId" => 300000028,
                        "locale" => "tr_TR",
                        "eapid" => 28,
                        "currency" => "TRY",
                        "device" => [
                            "type" => "DESKTOP"
                        ],
                        "identity" => [
                            "expUserId" => "-1",
                            "tuid" => "-1",
                            "authState" => "ANONYMOUS"
                        ],
                        "privacyTrackingState" => "CAN_TRACK"
                    ]
                ],
                "extensions" => [
                    "persistedQuery" => [
                        "version" => 1,
                        "sha256Hash" => "593bde3027fb0475dd6e126fd6ba54df87f2813b660a6f23b3b8ef8f2ec91a8a"
                    ]
                ]
            ]
        ];
        
        return json_encode($query);
    }
    
    /**
     * Get TatilSepeti price
     */
    private function getTatilSepetiPriceReal($url, $currency, $startDate, $endDate)
    {
        $this->logMessage("TatilSepeti API: Starting price request for URL {$url}, currency {$currency}, dates {$startDate} to {$endDate}", 'INFO');
        
        if (empty($url)) {
            $this->logMessage("TatilSepeti: Empty URL provided", 'ERROR');
            return "NA";
        }
        
        try {
            $checkIn = date("d.m.Y", strtotime($startDate));
            $checkOut = date("d.m.Y", strtotime($endDate));
            
            $postData = "Search=oda%3A2%3Btarih%3A" . urlencode($checkIn) . "%2C" . urlencode($checkOut) . "%3Bclick%3Atrue";
            
            $headers = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With: XMLHttpRequest',
                'Origin: https://www.tatilsepeti.com',
                'Referer: ' . $url
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (!$result || $httpCode !== 200) {
                $this->logMessage("TatilSepeti: HTTP error {$httpCode} or empty response", 'ERROR');
                return "NA";
            }
            
            // Parse JSON response (like old system)
            $decodedResult = json_decode($result, true);
            
            if (!$decodedResult) {
                $this->logMessage("TatilSepeti: Failed to parse JSON response", 'ERROR');
                return "NA";
            }
            
            if (!isset($decodedResult["roomList"])) {
                $this->logMessage("TatilSepeti: No roomList found in JSON response", 'WARNING');
                return "NA";
            }
            
            $roomList = $decodedResult["roomList"];
            $this->logMessage("TatilSepeti: Found roomList in JSON", 'INFO');
            
            // Check for availability errors (like old system)
            $availability = $this->search('<div class="alert', '--error', $roomList);
            if (in_array(0, $availability)) {
                $this->logMessage("TatilSepeti: Availability error found", 'WARNING');
                return "NA";
            }
            
            // Search for price in roomList (like old system)
            $tryPrice = $this->search('<span class="Prices--Price">', '<small class=\'price-currency\'>', $roomList);
            
            if (!empty($tryPrice)) {
                $price = str_replace(['.', ','], '', trim($tryPrice[0]));
                $price = preg_replace('/[^0-9]/', '', $price);
                
                if (is_numeric($price) && $price > 0) {
                    $finalPrice = $this->convertToTRY($price, 'TRY'); // TatilSepeti returns TRY
                    $this->logMessage("TatilSepeti: Found price {$price} TRY, converted to {$finalPrice} TRY", 'INFO');
                    return $finalPrice;
                }
            }
            
            $this->logMessage("TatilSepeti: No valid price found in roomList", 'WARNING');
            return "NA";
            
        } catch (\Exception $e) {
            $this->logMessage("TatilSepeti: Error - " . $e->getMessage(), 'ERROR');
            return "NA";
        }
    }
    
    /**
     * Get Reseliva price via API
     */
    private function getReselivaPriceApi($hotelID, $currency, $startDate, $endDate)
    {
        $this->logMessage("Reseliva API: Starting price request for hotel {$hotelID}, currency {$currency}, dates {$startDate} to {$endDate}", 'INFO');
        
        $username = env('RESELIVA_USERNAME', 'uKucukOteller');
        $passwd = env('RESELIVA_PASSWORD', '138Rs!5g8SD');

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode("$username:$passwd"),
        ];

        $data = [
            'api_version' => 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'hotels' => '[{"ta_id":1,"partner_id":' . $hotelID . '}]',
            'currency' => $currency,
            'user_country' => "TR",
            'device_type' => "d",
            'party' => '[{"adults":2, "children":[]}]',
            'source' => "kucukotellercomtr",
        ];

        $postData = http_build_query($data);

        $ch = curl_init('https://www.reseliva.com/siteBase/REST/kucukotellercomtr/service/hotel_availability');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $decodedResponse = json_decode($response);

        if ($decodedResponse && isset($decodedResponse->num_hotels) && $decodedResponse->num_hotels > 0) {
            $roomTypes = $decodedResponse->hotels[0]->room_types;
            $prices = [];

            foreach ($roomTypes as $roomType) {
                $price = round($roomType->final_price);
                $convertedPrice = $this->convertToTRY($price, $currency);
                
                $prices[] = [
                    "price" => $convertedPrice,
                    "url" => $roomType->url,
                ];
            }

            if (!empty($prices)) {
                $minPriceData = min($prices);
                $this->logMessage("Reseliva API: Found minimum price {$minPriceData['price']} TRY for hotel {$hotelID}", 'INFO');
                return $minPriceData;
            }
        }

        $this->logMessage("Reseliva API: No hotels or prices found for hotel {$hotelID}", 'WARNING');
        return "NA";
    }
    
    /**
     * Get Sabee Rooms Price
     */
    private function getSabeeRoomsPrice($sabeeHotelId, $currency, $startdate, $enddate)
    {
        $this->logMessage("Sabee API: Starting price request for hotel {$sabeeHotelId}, currency {$currency}, dates {$startdate} to {$enddate}", 'INFO');
        
        $sabeeApiKey = env('SABEE_API_KEY');
        if (!$sabeeApiKey) {
            $this->logMessage("Sabee API: API key not configured", 'WARNING');
            return "NA";
        }
        
        try {
            // Get room types for the hotel
            $roomTypes = $this->getSabeeRooms($sabeeHotelId, $sabeeApiKey);
            if (empty($roomTypes)) {
                $this->logMessage("Sabee API: No room types found for hotel {$sabeeHotelId}", 'WARNING');
                return "NA";
            }
            
            // Prepare rooms array for availability request
            $rooms = [];
            foreach ($roomTypes as $roomType) {
                $rooms[] = [
                    "room_id" => $roomType->room_id,
                    "guest_count" => ["adults" => 2]
                ];
            }
            
            // Request availability
            $parameters = [
                'hotel_id' => $sabeeHotelId,
                'start_date' => $startdate,
                'end_date' => $enddate,
                'rooms' => $rooms,
            ];
            
            $response = $this->sabeeRequest('booking/availability', $parameters, $sabeeApiKey);
            
            if ($response && isset($response->success) && $response->success && isset($response->data->room_rates)) {
                $prices = [];
                $pricesArray = array_column($response->data->room_rates, "prices");
                
                foreach ($pricesArray as $priceArray) {
                    if ($priceArray != null) {
                        foreach ($priceArray as $p) {
                            if ($p->rateplan_id == 0) {
                                continue;
                            }
                            $prices[] = $p;
                        }
                    }
                }
                
                if (empty($prices)) {
                    $this->logMessage("Sabee API: No valid prices found for hotel {$sabeeHotelId}", 'WARNING');
                    return "NA";
                }
                
                // Find minimum price
                $priceColumn = array_column($prices, 'amount');
                $minArray = $prices[array_search(min($priceColumn), $priceColumn)];
                
                $sabeeCurrency = $minArray->currency;
                $price = $minArray->amount;
                
                $this->logMessage("Sabee API: Found price {$price} {$sabeeCurrency} for hotel {$sabeeHotelId}", 'INFO');
                
                // Return price with currency info - no conversion needed!
                // The addPlatformToResponse will handle currency conversion smartly
                return [
                    'price' => round($price, 2),
                    'currency' => $sabeeCurrency
                ];
            } else {
                $this->logMessage("Sabee API: Invalid response or no room rates for hotel {$sabeeHotelId}", 'WARNING');
                return "NA";
            }
            
        } catch (\Exception $e) {
            $this->logMessage("Sabee API: Error - " . $e->getMessage(), 'ERROR');
            return "NA";
        }
    }
    
    /**
     * Get Sabee room types for hotel
     */
    private function getSabeeRooms($sabeeHotelId, $sabeeApiKey)
    {
        try {
            $response = $this->sabeeRequest('hotel/inventory', [], $sabeeApiKey);
            
            if (!$response || !isset($response->success) || !$response->success) {
                $this->logMessage("Sabee API: Failed to get hotel inventory", 'ERROR');
                return [];
            }
            
            $hotels = $response->data->hotels;
            $hotelIndex = array_search($sabeeHotelId, array_column($hotels, 'hotel_id'));
            
            if ($hotelIndex === false) {
                $this->logMessage("Sabee API: Hotel {$sabeeHotelId} not found in inventory", 'WARNING');
                return [];
            }
            
            return $hotels[$hotelIndex]->room_types;
            
        } catch (\Exception $e) {
            $this->logMessage("Sabee API: Error getting rooms - " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Make Sabee API request
     */
    private function sabeeRequest($endpoint, $parameters, $sabeeApiKey)
    {
        try {
            $headers = [
                'api_key: ' . $sabeeApiKey,
                'api_version: 1'
            ];
            
            // If parameters exist, use POST method for booking/availability
            $isPost = !empty($parameters) && $endpoint === 'booking/availability';
            
            if ($isPost) {
                $headers[] = 'Content-Type: application/json';
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.sabeeapp.com/connect/{$endpoint}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            
            if ($isPost) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                $this->logMessage("Sabee API: HTTP error {$httpCode} for endpoint {$endpoint}", 'ERROR');
                return null;
            }
            
            return json_decode($response);
            
        } catch (\Exception $e) {
            $this->logMessage("Sabee API: Request error - " . $e->getMessage(), 'ERROR');
            return null;
        }
    }
    
    /**
     * Get OtelZ Price
     */
    private function getOtelZPriceReal($otelzUrl, $currency, $startDate, $endDate)
    {
        $this->logMessage("OtelZ API: Starting price request for facility {$otelzUrl}, currency {$currency}, dates {$startDate} to {$endDate}", 'INFO');
        
        // Check if otelzUrl is numeric
        if (!is_numeric($otelzUrl)) {
            $this->logMessage("OtelZ API: Invalid facility ID - " . $otelzUrl, 'ERROR');
            return "NA";
        }

        $facilityID = (int)$otelzUrl;
        $username = env('OTELZ_USERNAME', 'kucukoteller');
        $passwd = env('OTELZ_PASSWORD', '4;q)Dx9#');
        
        // Check credentials
        if (!$username || !$passwd) {
            $this->logMessage("OtelZ API: Missing credentials", 'ERROR');
            return "NA";
        }
        
        $this->logMessage("OtelZ API: Using credentials - Username: " . $username . ", Partner ID: " . env('OTELZ_PARTNER_ID', 1316), 'DEBUG');

        // Use working format from test (simplified)
        $data = [
            "partner_id" => (int)env('OTELZ_PARTNER_ID', 1316),
            "facility_reference" => $facilityID,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "party" => [
                [
                    "adults" => 2,
                    "children" => [],
                ],
            ],
            "lang" => "tr",
            "user_country" => "TR",
        ];

        $json = json_encode($data);
        $this->logMessage("OtelZ API: Request JSON - " . $json, 'DEBUG');
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$username:$passwd"),
        ];

        $ch = curl_init('https://fullconnect.otelz.com/v1/detail/availability');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Check for cURL errors
        if ($curlError) {
            $this->logMessage("OtelZ API cURL error: " . $curlError, 'ERROR');
            return "NA";
        }

        // Check HTTP status
        if ($httpCode !== 200) {
            $this->logMessage("OtelZ API HTTP error: " . $httpCode . " - Response: " . substr($response, 0, 500), 'ERROR');
            
            // Special handling for 401 Unauthorized
            if ($httpCode === 401) {
                $this->logMessage("OtelZ API: Authentication failed - check credentials and partner_id", 'ERROR');
            }
            
            return "NA";
        }

        $result = json_decode($response);
        
        // Check JSON decode error
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logMessage("OtelZ API JSON decode error: " . json_last_error_msg(), 'ERROR');
            return "NA";
        }

        // Check for API errors
        if (isset($result->errors)) {
            $errorMessage = "OtelZ API errors for facility ID {$facilityID}: " . json_encode($result->errors);
            $this->logMessage($errorMessage, 'ERROR');
            
            // Check for specific error codes
            foreach ($result->errors as $error) {
                if ($error->code == 10002010) {
                    $this->logMessage("OtelZ API: Facility {$facilityID} not available or invalid", 'WARNING');
                }
            }
            
            return "NA";
        }

        // Check result structure
        if ($result && isset($result->detail_result)) {
            // Check if there are available rooms
            if (isset($result->detail_result->min_price)) {
                if (isset($result->detail_result->min_price->total_room) &&
                    $result->detail_result->min_price->total_room == 0) {
                    $this->logMessage("OtelZ API: No rooms available for facility {$facilityID}", 'INFO');
                    return "NA";
                }
                
                // Get minimum price
                if (isset($result->detail_result->min_price->net_total->amount)) {
                    $price = $result->detail_result->min_price->net_total->amount;
                    $this->logMessage("OtelZ API: Found price {$price} {$currency} for facility {$facilityID}", 'INFO');
                    
                    // Convert to TRY if needed
                    $tryPrice = $this->convertToTRY($price, $currency);
                    $this->logMessage("OtelZ API: Final price after conversion - {$tryPrice} TRY for facility {$facilityID}", 'INFO');
                    
                    return $tryPrice;
                }
            }
            
            // Alternative: Check room_types for prices
            if (isset($result->detail_result->room_types) && is_array($result->detail_result->room_types)) {
                $minPrice = null;
                
                foreach ($result->detail_result->room_types as $roomType) {
                    if (isset($roomType->room_prices) && is_array($roomType->room_prices)) {
                        foreach ($roomType->room_prices as $roomPrice) {
                            if (isset($roomPrice->net_total->amount)) {
                                $currentPrice = $roomPrice->net_total->amount;
                                if ($minPrice === null || $currentPrice < $minPrice) {
                                    $minPrice = $currentPrice;
                                }
                            }
                        }
                    }
                }
                
                if ($minPrice !== null) {
                    $this->logMessage("OtelZ API: Found minimum price {$minPrice} {$currency} from room types for facility {$facilityID}", 'INFO');
                    
                    // Convert to TRY if needed
                    $tryPrice = $this->convertToTRY($minPrice, $currency);
                    $this->logMessage("OtelZ API: Final minimum price after conversion - {$tryPrice} TRY for facility {$facilityID}", 'INFO');
                    
                    return $tryPrice;
                }
            }
        }

        $this->logMessage("OtelZ API: Unexpected response structure for facility {$facilityID} - " . substr($response, 0, 300), 'ERROR');
        return "NA";
    }
    
    /**
     * Get Etstur Price
     */
    private function getEtsturPriceReal($hotelId, $currency, $checkIn, $checkOut)
    {
        $this->logMessage("Etstur API: Starting price request for hotel {$hotelId}, currency {$currency}, dates {$checkIn} to {$checkOut}", 'INFO');
        
        if (empty($hotelId)) {
            $this->logMessage("Etstur API: Empty hotel ID provided", 'ERROR');
            return "NA";
        }
        
        try {
            // Prepare request data
            $data = [
                'hotelId' => $hotelId,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'adults' => 2,
                'currency' => $currency
            ];
            
            $json = json_encode($data);
            $this->logMessage("Etstur API: Request JSON - " . $json, 'DEBUG');
            
            // Prepare headers
            $headers = [
                'Content-Type: application/json'
            ];
            
            // Initialize cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://mapi.etstur.com/api/kucukoteller/availability',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($curlError) {
                $this->logMessage("Etstur API cURL error: " . $curlError, 'ERROR');
                return "NA";
            }
            
            // Check HTTP status
            if ($httpCode !== 200) {
                $this->logMessage("Etstur API HTTP error: " . $httpCode . " - Response: " . substr($response, 0, 500), 'ERROR');
                return "NA";
            }
            
            if (!$response) {
                $this->logMessage("Etstur API: Empty response", 'ERROR');
                return "NA";
            }
            
            $this->logMessage("Etstur API: Response received (" . strlen($response) . " bytes)", 'DEBUG');
            
            // Parse JSON response
            $result = json_decode($response, true);
            
            // Check JSON decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logMessage("Etstur API JSON decode error: " . json_last_error_msg(), 'ERROR');
                return "NA";
            }
            
            // Check for API errors
            if (isset($result['error']) || isset($result['errors'])) {
                $errorMessage = isset($result['error']) ? $result['error'] : json_encode($result['errors']);
                $this->logMessage("Etstur API error: " . $errorMessage, 'ERROR');
                return "NA";
            }
            
            // Extract price from response
            // The response structure may vary, so we need to check different possible locations
            $price = null;
            $responseCurrency = $currency;
            
            // Check for price in common response structures
            if (isset($result['price'])) {
                $price = $result['price'];
            } elseif (isset($result['data']['price'])) {
                $price = $result['data']['price'];
            } elseif (isset($result['minPrice'])) {
                $price = $result['minPrice'];
            } elseif (isset($result['data']['minPrice'])) {
                $price = $result['data']['minPrice'];
            } elseif (isset($result['rooms']) && is_array($result['rooms']) && count($result['rooms']) > 0) {
                // Find minimum price from rooms
                $prices = [];
                foreach ($result['rooms'] as $room) {
                    if (isset($room['price'])) {
                        $prices[] = $room['price'];
                    } elseif (isset($room['totalPrice'])) {
                        $prices[] = $room['totalPrice'];
                    }
                }
                if (!empty($prices)) {
                    $price = min($prices);
                }
            } elseif (isset($result['availability']) && is_array($result['availability'])) {
                // Check availability array
                $prices = [];
                foreach ($result['availability'] as $avail) {
                    if (isset($avail['price'])) {
                        $prices[] = $avail['price'];
                    }
                }
                if (!empty($prices)) {
                    $price = min($prices);
                }
            }
            
            // Check if currency is specified in response
            if (isset($result['currency'])) {
                $responseCurrency = $result['currency'];
            } elseif (isset($result['data']['currency'])) {
                $responseCurrency = $result['data']['currency'];
            }
            
            if ($price !== null && is_numeric($price) && $price > 0) {
                $this->logMessage("Etstur API: Found price {$price} {$responseCurrency} for hotel {$hotelId}", 'INFO');
                
                // Return price with currency info - no conversion needed!
                // The addPlatformToResponse will handle currency conversion smartly
                return [
                    'price' => round($price, 2),
                    'currency' => $responseCurrency
                ];
            }
            
            $this->logMessage("Etstur API: No valid price found in response for hotel {$hotelId}", 'WARNING');
            $this->logMessage("Etstur API: Response structure - " . substr($response, 0, 500), 'DEBUG');
            return "NA";
            
        } catch (\Exception $e) {
            $this->logMessage("Etstur API: Error - " . $e->getMessage(), 'ERROR');
            return "NA";
        }
    }
    
}
