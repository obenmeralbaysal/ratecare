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
            $this->addOdamaxPlatform($response, $hotel, $currency, $checkIn, $checkOut, $adult);
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
                    case 'odamax':
                        $response["prices"]["odamax"] = $this->getOdamaxPrice($hotel, $currency, $startDate, $endDate);
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
                    $this->addPlatformToResponse($response, 'sabeeapp', 'SabeeApp', $price, $hotel['sabee_url']);
                }
                break;
                
            case "reseliva":
                if ($hotel['reseliva_is_active'] && !empty($hotel['reseliva_hotel_id'])) {
                    $price = $this->getReselivaPrice($hotel, $currency, $checkIn, $checkOut);
                    $url = $this->getReselivaUrl($hotel, $currency, $checkIn, $checkOut);
                    $this->addPlatformToResponse($response, 'reseliva', 'Reseliva', $price, $url);
                }
                break;
                
            case "hotelrunner":
                if ($hotel['is_hotelrunner_active'] && !empty($hotel['hotelrunner_url'])) {
                    $result = $this->getHotelRunnerPrice($hotel, $currency, $checkIn, $checkOut);
                    if ($result !== "NA") {
                        $this->addPlatformToResponse($response, 'hotelrunner', 'HotelRunner', $result['price'], $result['url']);
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
        if ($hotel['booking_is_active'] && !empty($hotel['booking_url'])) {
            $this->logMessage("Booking.com: Processing hotel " . $hotel['name'] . " with URL " . $hotel['booking_url'], 'INFO');
            
            $price = $this->getBookingPrice($hotel, $currency, $checkIn, $checkOut);
            
            // Only add to response if price is valid
            if ($price !== "NA") {
                $url = $this->getBookingUrl($hotel, $currency, $checkIn, $checkOut);
                $this->addPlatformToResponse($response, 'booking', 'Booking.com', $price, $url);
                $this->logMessage("Booking.com: Added to response with price " . $price, 'INFO');
            } else {
                $this->logMessage("Booking.com: Price not available for " . $hotel['name'], 'WARNING');
            }
        } else {
            $this->logMessage("Booking.com: Skipped - not active or no URL for " . $hotel['name'], 'INFO');
        }
    }
    
    /**
     * Add Hotels.com platform to response
     */
    private function addHotelsPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['hotels_is_active'] && !empty($hotel['hotels_url'])) {
            $price = $this->getHotelsPrice($hotel, $currency, $checkIn, $checkOut);
            $this->addPlatformToResponse($response, 'hotels', 'Hotels.com', $price, $hotel['hotels_url']);
        }
    }
    
    /**
     * Add TatilSepeti platform to response
     */
    private function addTatilSepetiPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['tatilsepeti_is_active'] && !empty($hotel['tatilsepeti_url'])) {
            $price = $this->getTatilSepetiPrice($hotel, $currency, $checkIn, $checkOut);
            $this->addPlatformToResponse($response, 'tatilsepeti', 'Tatil Sepeti', $price, $hotel['tatilsepeti_url']);
        }
    }
    
    /**
     * Add Odamax platform to response
     */
    private function addOdamaxPlatform(&$response, $hotel, $currency, $checkIn, $checkOut, $adult)
    {
        if ($hotel['odamax_is_active'] && !empty($hotel['odamax_url'])) {
            $price = $this->getOdamaxPrice($hotel, $currency, $checkIn, $checkOut);
            $url = $this->getOdamaxUrl($hotel, $currency, $checkIn, $checkOut);
            $this->addPlatformToResponse($response, 'odamax', 'Odamax', $price, $url);
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
                $this->addPlatformToResponse($response, 'otelz', 'OtelZ', $price, $url);
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
            $result = $this->getEtsturPrice($hotel, $currency, $checkIn, $checkOut);
            if ($result !== "NA") {
                $this->addPlatformToResponse($response, 'etstur', 'ETSTur', $result['price'], $result['url']);
            }
        }
    }
    
    /**
     * Add platform data to response
     */
    private function addPlatformToResponse(&$response, $name, $displayName, $price, $url)
    {
        $status = ($price == "NA" || $price == "" || $price === null) ? "failed" : "success";
        
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
        // Mock implementation - would call actual HotelRunner API
        return [
            'price' => rand(780, 1450) . ".00",
            'url' => $hotel['hotelrunner_url']
        ];
    }
    
    private function getBookingPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getBookingPriceReal($hotel['booking_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getBookingUrl($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would generate booking URL with dates
        return $hotel['booking_url'] . "?checkin=" . $checkIn . "&checkout=" . $checkOut;
    }
    
    private function getHotelsPrice($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would call Hotels.com API
        return rand(820, 1550) . ".00";
    }
    
    private function getTatilSepetiPrice($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would call TatilSepeti API
        return rand(790, 1480) . ".00";
    }
    
    private function getOdamaxPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getOdamaxPriceReal($hotel['odamax_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getOdamaxUrl($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would generate Odamax URL
        return $hotel['odamax_url'] . "?checkin=" . $checkIn . "&checkout=" . $checkOut;
    }
    
    private function getOtelzPrice($hotel, $currency, $checkIn, $checkOut)
    {
        return $this->getOtelZPriceReal($hotel['otelz_url'], $currency, $checkIn, $checkOut);
    }
    
    private function getOtelzUrl($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would generate OtelZ URL
        return "https://otelz.com/hotel/" . $hotel['otelz_url'];
    }
    
    private function getEtsturPrice($hotel, $currency, $checkIn, $checkOut)
    {
        // Mock implementation - would call ETSTur API
        return [
            'price' => rand(770, 1420) . ".00",
            'url' => "https://etstur.com/hotel/" . $hotel['etstur_hotel_id']
        ];
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
            'odamax' => $this->getOdamaxPrice($hotel, $currency, $startDate, $endDate),
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
            curl_setopt($ch, CURLOPT_POST, 0);
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
     * Get Booking.com price
     */
    private function getBookingPriceReal($url, $currency, $checkinDate, $checkoutDate)
    {
        $this->logMessage("Booking.com API: Starting price request for URL {$url}, currency {$currency}, dates {$checkinDate} to {$checkoutDate}", 'INFO');
        
        if (substr($url, -8) != ".tr.html") {
            $url = str_replace(".html", ".tr.html", $url);
        }

        $search_url = $url . "?selected_currency=" . $currency . "&checkin=" . $checkinDate . "&checkout=" . $checkoutDate;
        $this->logMessage("Booking.com: Fetching URL - " . $search_url, 'INFO');
        
        $html = $this->getHTML($search_url, 30, 2);
        
        if (!$html) {
            $this->logMessage("Booking.com: Failed to fetch HTML content", 'ERROR');
            return "NA";
        }

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

        // Try primary price pattern
        $price = $this->search('"b_price":"' . $currency_symbol, '"', $html);
        
        if ($price != []) {
            $finalPrice = round(preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($price[0]))));
            $this->logMessage("Booking.com: Found primary price - " . $finalPrice, 'INFO');
            return $finalPrice;
        }
        
        // Try alternative price pattern (like old system)
        $alternativePrice = $this->search("tarihlerinizde", "gibi", $html);
        
        if ($alternativePrice != []) {
            switch ($currency) {
                case "EUR":
                    $alternativePrice = $this->search("tarihlerinizde \xE2\x82\xAc", "gibi", $html);
                    break;
                case "USD":
                    $alternativePrice = $this->search("tarihlerinizde US$", "gibi", $html);
                    break;
                default:
                    $alternativePrice = $this->search("tarihlerinizde TL", "gibi", $html);
            }
            
            if ($alternativePrice != []) {
                $finalPrice = round(preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($alternativePrice[0]))));
                $this->logMessage("Booking.com: Found alternative price - " . $finalPrice, 'INFO');
                return $finalPrice;
            }
        }
        
        $this->logMessage("Booking.com: No price found in HTML content", 'WARNING');
        return "NA";
    }
    
    /**
     * Get Reseliva price via API
     */
    private function getReselivaPriceApi($hotelID, $currency, $startDate, $endDate)
    {
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

        if ($decodedResponse && $decodedResponse->num_hotels > 0) {
            $roomTypes = $decodedResponse->hotels[0]->room_types;
            $prices = [];

            foreach ($roomTypes as $roomType) {
                $prices[] = [
                    "price" => round($roomType->final_price),
                    "url" => $roomType->url,
                ];
            }

            $minPrice = min($prices);
            return $minPrice;
        }

        return "NA";
    }
    
    /**
     * Get Sabee Rooms Price
     */
    private function getSabeeRoomsPrice($sabeeHotelId, $currency, $startdate, $enddate)
    {
        // This would require SabeeClient integration
        $sabeeApiKey = env('SABEE_API_KEY');
        if (!$sabeeApiKey) {
            return "NA";
        }
        
        // TODO: Implement SabeeClient with $sabeeApiKey
        // For now return mock data
        return rand(800, 1500) . ".00";
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

        $data = [
            "detail_request" => [
                "facility_reference" => $facilityID,
                "start_date" => $startDate,
                "end_date" => $endDate,
                "party" => [["adults" => 2, "children" => []]],
                "lang" => "tr",
                "currency" => $currency,
                "price_formatter" => ["decimal_digit_number" => 2],
                "user_country" => "TR",
                "device_type" => "Desktop",
                "request_type" => "Strict",
                "web_hook_url" => "",
                "partner_id" => (int)env('OTELZ_PARTNER_ID', 1316)
            ]
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
            $this->logMessage("OtelZ API HTTP error: " . $httpCode . " - Response: " . substr($response, 0, 200), 'ERROR');
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
                    $this->logMessage("OtelZ API: Found price {$price} for facility {$facilityID}", 'INFO');
                    return round($price);
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
                    $this->logMessage("OtelZ API: Found minimum price {$minPrice} from room types for facility {$facilityID}", 'INFO');
                    return round($minPrice);
                }
            }
        }

        $this->logMessage("OtelZ API: Unexpected response structure for facility {$facilityID} - " . substr($response, 0, 300), 'ERROR');
        return "NA";
    }
    
    /**
     * Get Odamax Price
     */
    private function getOdamaxPriceReal($url, $currency, $startDate, $endDate)
    {
        $startDate = date("d.m.Y", strtotime($startDate));
        $endDate = date("d.m.Y", strtotime($endDate));

        if (strpos($url, 'kucukoteller') !== false) {
            $search_url = "$url&check_in=$startDate&check_out=$endDate&adult_1=2&type=HOTEL&currency=$currency";
        } else {
            $search_url = "$url?check_in=$startDate&check_out=$endDate&adult_1=2&type=HOTEL&currency=$currency";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $search_url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $html = curl_exec($ch);
        $html = str_replace(["\n", "\r", "\n"], ' ', $html);
        
        $tryPrice = $this->search('<i class="integers ">', '</i>', $html);
        if ($tryPrice) {
            $tryPrice = str_replace(",", "", trim($tryPrice[0]));
            $tryPrice = str_replace(".", "", $tryPrice);
            return round($tryPrice);
        }

        return "NA";
    }
}
