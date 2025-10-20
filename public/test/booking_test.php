<?php
/**
 * Booking.com API Test - Old System Implementation
 * Standalone test file for Booking.com price scraping
 */

// Test parameters
$bookingUrl = "https://www.booking.com/hotel/fr/port-royal-paris1.tr.html";
$currency = "TRY";
$checkinDate = "2025-10-22";
$checkoutDate = "2025-10-24";

echo "<h1>Booking.com Price Test</h1>";
echo "<p><strong>Hotel URL:</strong> {$bookingUrl}</p>";
echo "<p><strong>Currency:</strong> {$currency}</p>";
echo "<p><strong>Check-in:</strong> {$checkinDate}</p>";
echo "<p><strong>Check-out:</strong> {$checkoutDate}</p>";
echo "<hr>";

/**
 * Helper function: Get HTML content from URL
 */
function getHTML($url, $timeout, $type = 0) 
{
    $header = [];
    $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
    $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
    $header[0] = "Cache-Control: max-age=0";
    $header[] = "Connection: keep-alive";
    $header[] = "Keep-Alive: 300";
    $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $header[] = "Pragma: "; // browsers keep this blank.

    $ch = curl_init($url); // initialize curl with given url
    curl_setopt(
        $ch,
        CURLOPT_USERAGENT,
        "Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0"
    ); // set useragent
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // write the response to a variable
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects if any
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // max. seconds to execute
    curl_setopt($ch, CURLOPT_FAILONERROR, 1); // stop when it encounters an error
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

    if ($type == 2) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt(
            $ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0'
        );
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_PROXY, 'brd.superproxy.io:22225');
        curl_setopt(
            $ch,
            CURLOPT_PROXYUSERPWD,
            'brd-customer-hl_e5f2315f-zone-datacenter_proxy1-country-nl:uvmqoi66peju'
        );
    }

    return @curl_exec($ch);
}

/**
 * Helper function: Search for text between two strings
 */
function search($begin, $end, $text) 
{
    @preg_match_all(
        '/' . preg_quote($begin, '/') . '(.*?)' . preg_quote($end, '/') . '/i',
        $text,
        $m
    );

    return @$m[1];
}

/**
 * Get Booking URL with parameters
 */
function getBookingUrl($url, $currency, $checkinDate, $checkoutDate) 
{
    if (substr($url, -8) != ".tr.html") {
        $url = str_replace(".html", ".tr.html", $url);
    }

    $search_url = $url . "?selected_currency=" . $currency . "&checkin=" . $checkinDate . "&checkout=" . $checkoutDate;
    
    return $search_url;
}

/**
 * Test Booking.com Price Scraping
 */
function testBookingPrice($url, $currency, $checkinDate, $checkoutDate) 
{
    echo "<h2>Testing Booking.com Price Scraping</h2>";
    
    // Ensure URL ends with .tr.html
    if (substr($url, -8) != ".tr.html") {
        $url = str_replace(".html", ".tr.html", $url);
        echo "<p><strong>URL corrected to:</strong> {$url}</p>";
    }

    // Build search URL
    $search_url = getBookingUrl($url, $currency, $checkinDate, $checkoutDate);
    echo "<p><strong>Search URL:</strong> {$search_url}</p>";

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
    
    echo "<p><strong>Currency Symbol:</strong> {$currency_symbol}</p>";

    echo "<h3>Fetching HTML Content...</h3>";
    
    // Test different methods
    $methods = [
        ['name' => 'Standard', 'type' => 0, 'timeout' => 30],
        ['name' => 'With Proxy', 'type' => 2, 'timeout' => 30],
    ];
    
    foreach ($methods as $method) {
        echo "<h4>Method: {$method['name']}</h4>";
        
        $html = getHTML($search_url, $method['timeout'], $method['type']);
        
        if (!$html) {
            echo "<p style='color: red;'>❌ Failed to fetch HTML content</p>";
            continue;
        }
        
        echo "<p style='color: green;'>✅ HTML content fetched (" . strlen($html) . " bytes)</p>";
        
        // Show first 500 chars of HTML for debugging
        echo "<h5>HTML Preview (first 500 chars):</h5>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: scroll;'>" . 
             htmlspecialchars(substr($html, 0, 500)) . "...</pre>";
        
        // Search for primary price pattern
        echo "<h5>Searching for Primary Price Pattern:</h5>";
        $price_pattern = '"b_price":"' . $currency_symbol;
        echo "<p><strong>Pattern:</strong> {$price_pattern}</p>";
        
        $price = search($price_pattern, '"', $html);
        
        if (!empty($price)) {
            echo "<p style='color: green;'>✅ Primary price found: " . implode(', ', $price) . "</p>";
            
            $cleanPrice = round(
                preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($price[0])))
            );
            echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Final Price: {$cleanPrice} {$currency}</p>";
            return $cleanPrice;
        } else {
            echo "<p style='color: orange;'>⚠️ Primary price pattern not found</p>";
        }
        
        // Search for alternative price pattern
        echo "<h5>Searching for Alternative Price Pattern:</h5>";
        $alt_pattern = "tarihlerinizde";
        echo "<p><strong>Pattern:</strong> {$alt_pattern}</p>";
        
        $alternativePrice = search($alt_pattern, "gibi", $html);
        
        if (!empty($alternativePrice)) {
            echo "<p style='color: green;'>✅ Alternative price context found: " . implode(', ', $alternativePrice) . "</p>";
            
            // Search for specific currency in alternative price
            switch ($currency) {
                case "EUR":
                    $currencyPrice = search("tarihlerinizde €", "gibi", $html);
                    break;
                case "USD":
                    $currencyPrice = search("tarihlerinizde US$", "gibi", $html);
                    break;
                default:
                    $currencyPrice = search("tarihlerinizde TL", "gibi", $html);
            }
            
            if (!empty($currencyPrice)) {
                echo "<p style='color: green;'>✅ Currency-specific price found: " . implode(', ', $currencyPrice) . "</p>";
                
                $cleanAltPrice = round(
                    preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($currencyPrice[0])))
                );
                echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Alternative Price: {$cleanAltPrice} {$currency}</p>";
                return $cleanAltPrice;
            } else {
                echo "<p style='color: orange;'>⚠️ Currency-specific price not found in alternative pattern</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Alternative price pattern not found</p>";
        }
        
        // Search for common price patterns in HTML
        echo "<h5>Searching for Common Price Patterns:</h5>";
        $common_patterns = [
            'data-price="',
            'price":',
            'amount":',
            '"price_breakdown"',
            'total_price',
            'price_for_display'
        ];
        
        foreach ($common_patterns as $pattern) {
            $matches = search($pattern, '"', $html);
            if (!empty($matches)) {
                echo "<p><strong>{$pattern}:</strong> " . implode(', ', array_slice($matches, 0, 3)) . "</p>";
            }
        }
        
        echo "<hr>";
    }
    
    echo "<p style='color: red;'>❌ No price found with any method</p>";
    return "NA";
}

/**
 * Test different Booking.com URLs
 */
function testMultipleBookingUrls($currency, $checkinDate, $checkoutDate) 
{
    echo "<h2>Testing Multiple Booking.com URLs</h2>";
    
    $testUrls = [
        "https://www.booking.com/hotel/fr/port-royal-paris1.tr.html",
        "https://www.booking.com/hotel/tr/hilton-istanbul-bosphorus.html",
        "https://www.booking.com/hotel/tr/four-seasons-hotel-istanbul-at-sultanahmet.html"
    ];
    
    foreach ($testUrls as $url) {
        echo "<h3>Testing: " . basename($url) . "</h3>";
        $result = testBookingPrice($url, $currency, $checkinDate, $checkoutDate);
        echo "<p><strong>Result:</strong> {$result}</p>";
        echo "<hr>";
    }
}

// Run tests
echo "<div style='font-family: Arial, sans-serif; margin: 20px;'>";

// Test 1: Single URL
echo "<h1 style='color: #2c3e50;'>🏨 Test 1: Single Hotel Test</h1>";
$singleResult = testBookingPrice($bookingUrl, $currency, $checkinDate, $checkoutDate);

echo "<hr style='border: 2px solid #34495e; margin: 30px 0;'>";

// Test 2: Multiple URLs
echo "<h1 style='color: #2c3e50;'>🏨 Test 2: Multiple Hotels Test</h1>";
testMultipleBookingUrls($currency, $checkinDate, $checkoutDate);

echo "<hr style='border: 2px solid #34495e; margin: 30px 0;'>";

// Test Summary
echo "<h1 style='color: #2c3e50;'>📊 Test Summary</h1>";
echo "<div style='background: #ecf0f1; padding: 20px; border-radius: 8px;'>";
echo "<h3>Results:</h3>";
echo "<ul style='font-size: 16px;'>";
echo "<li><strong>Single Hotel Test:</strong> " . ($singleResult !== "NA" ? "<span style='color: green;'>✅ SUCCESS - Price: {$singleResult} {$currency}</span>" : "<span style='color: red;'>❌ FAILED</span>") . "</li>";
echo "</ul>";

echo "<h3>Method Information:</h3>";
echo "<ul>";
echo "<li><strong>Scraping Method:</strong> HTML parsing with regex patterns</li>";
echo "<li><strong>Primary Pattern:</strong> \"b_price\":\"{currency_symbol}</li>";
echo "<li><strong>Alternative Pattern:</strong> \"tarihlerinizde {currency_symbol}...gibi\"</li>";
echo "<li><strong>Proxy Support:</strong> Available for blocked requests</li>";
echo "</ul>";

echo "<h3>Common Issues:</h3>";
echo "<ul>";
echo "<li><strong>Bot Detection:</strong> Booking.com may block automated requests</li>";
echo "<li><strong>Dynamic Content:</strong> Prices may be loaded via JavaScript</li>";
echo "<li><strong>Rate Limiting:</strong> Too many requests may trigger blocks</li>";
echo "<li><strong>Currency Formatting:</strong> Different formats for different currencies</li>";
echo "</ul>";

echo "</div>";

echo "</div>";
?>
