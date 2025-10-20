<?php
/**
 * OtelZ Hotel Data API Test
 * Test file for OtelZ Hotel Data endpoint
 */

// Test parameters
$partner_id = 1316;
$facility_reference = 9518; // Test facility ID
$lang = "tr";
$country = "TR";
$city_reference = null; // Optional
$district_reference = null; // Optional

echo "<h1>OtelZ Hotel Data API Test</h1>";
echo "<p><strong>Partner ID:</strong> {$partner_id}</p>";
echo "<p><strong>Facility Reference:</strong> {$facility_reference}</p>";
echo "<p><strong>Language:</strong> {$lang}</p>";
echo "<p><strong>Country:</strong> {$country}</p>";
echo "<hr>";

/**
 * Test OtelZ Hotel Data API
 */
function testOtelZHotelDataAPI($partner_id, $facility_reference = null, $lang = "tr", $country = "TR", $city_reference = null, $district_reference = null) 
{
    echo "<h2>Testing OtelZ Hotel Data API</h2>";
    
    // Prepare request data
    $data = [
        "partner_id" => (int)$partner_id,
        "lang" => $lang,
        "country" => $country
    ];
    
    // Add optional parameters
    if ($facility_reference) {
        $data["facility_reference"] = (int)$facility_reference;
    }
    
    if ($city_reference) {
        $data["city_reference"] = (int)$city_reference;
    }
    
    if ($district_reference) {
        $data["district_reference"] = (int)$district_reference;
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo "<h3>Request JSON:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>{$json}</pre>";

    // Credentials
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    echo "<p><strong>Username:</strong> {$username}</p>";

    // Headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$username:$passwd"),
    ];

    echo "<h3>Headers:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    foreach ($headers as $header) {
        if (strpos($header, 'Authorization') !== false) {
            echo "Authorization: Basic [HIDDEN]\n";
        } else {
            echo $header . "\n";
        }
    }
    echo "</pre>";

    // Test different possible endpoints
    $endpoints = [
        'https://fullconnect.otelz.com/data/hotel',
        'https://fullconnect.otelz.com/v2/data/hotel',
        'https://fullconnect.otelz.com/hotel/data',
        'https://fullconnect.otelz.com/data/facility'
    ];

    foreach ($endpoints as $endpoint) {
        echo "<h3>Testing Endpoint: {$endpoint}</h3>";
        
        // Make API call
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        echo "<p>🔄 Making API call...</p>";

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Check for cURL errors
        if ($curlError) {
            echo "<p style='color: red;'>❌ cURL Error: {$curlError}</p>";
            continue;
        }

        echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

        // Check HTTP status
        if ($httpCode === 200) {
            echo "<p style='color: green;'>✅ HTTP 200 OK</p>";
            
            // Parse JSON response
            $result = json_decode($response);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<p style='color: red;'>❌ JSON Decode Error: " . json_last_error_msg() . "</p>";
                echo "<h4>Raw Response:</h4>";
                echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
                continue;
            }

            echo "<p style='color: green;'>✅ JSON parsed successfully</p>";

            // Show response summary
            if (isset($result->hotels) && is_array($result->hotels)) {
                echo "<p style='color: green; font-weight: bold;'>✅ Found " . count($result->hotels) . " hotels</p>";
                
                // Show first hotel details
                if (count($result->hotels) > 0) {
                    $hotel = $result->hotels[0];
                    echo "<h4>First Hotel Details:</h4>";
                    echo "<ul>";
                    if (isset($hotel->name)) echo "<li><strong>Name:</strong> {$hotel->name}</li>";
                    if (isset($hotel->facility_reference)) echo "<li><strong>Facility ID:</strong> {$hotel->facility_reference}</li>";
                    if (isset($hotel->address)) echo "<li><strong>Address:</strong> {$hotel->address}</li>";
                    if (isset($hotel->city)) echo "<li><strong>City:</strong> {$hotel->city}</li>";
                    if (isset($hotel->district)) echo "<li><strong>District:</strong> {$hotel->district}</li>";
                    if (isset($hotel->latitude)) echo "<li><strong>Latitude:</strong> {$hotel->latitude}</li>";
                    if (isset($hotel->longitude)) echo "<li><strong>Longitude:</strong> {$hotel->longitude}</li>";
                    echo "</ul>";
                }
            } elseif (isset($result->hotel)) {
                echo "<p style='color: green; font-weight: bold;'>✅ Single hotel data found</p>";
                $hotel = $result->hotel;
                echo "<h4>Hotel Details:</h4>";
                echo "<ul>";
                if (isset($hotel->name)) echo "<li><strong>Name:</strong> {$hotel->name}</li>";
                if (isset($hotel->facility_reference)) echo "<li><strong>Facility ID:</strong> {$hotel->facility_reference}</li>";
                if (isset($hotel->address)) echo "<li><strong>Address:</strong> {$hotel->address}</li>";
                if (isset($hotel->city)) echo "<li><strong>City:</strong> {$hotel->city}</li>";
                if (isset($hotel->district)) echo "<li><strong>District:</strong> {$hotel->district}</li>";
                echo "</ul>";
            }

            // Show full response (truncated)
            echo "<h4>Full API Response (first 1000 chars):</h4>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: scroll;'>" . 
                 htmlspecialchars(substr(json_encode($result, JSON_PRETTY_PRINT), 0, 1000)) . "...</pre>";
            
            return $result;
            
        } elseif ($httpCode === 401) {
            echo "<p style='color: red;'>❌ HTTP 401 Unauthorized - Check credentials</p>";
        } elseif ($httpCode === 404) {
            echo "<p style='color: orange;'>⚠️ HTTP 404 Not Found - Wrong endpoint</p>";
        } else {
            echo "<p style='color: red;'>❌ HTTP Error: {$httpCode}</p>";
            echo "<h4>Response Body:</h4>";
            echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        }
        
        echo "<hr>";
    }

    return null;
}

/**
 * Test GET method as well
 */
function testOtelZHotelDataGET($partner_id, $facility_reference = null, $lang = "tr", $country = "TR") 
{
    echo "<h2>Testing OtelZ Hotel Data API (GET Method)</h2>";
    
    // Prepare query parameters
    $params = [
        "partner_id" => $partner_id,
        "lang" => $lang,
        "country" => $country
    ];
    
    if ($facility_reference) {
        $params["facility_reference"] = $facility_reference;
    }
    
    $queryString = http_build_query($params);
    
    // Credentials
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    // Test different possible endpoints with GET
    $endpoints = [
        'https://fullconnect.otelz.com/data/hotel_data',
        'https://fullconnect.otelz.com/v2/data/hotel_data'
    ];

    foreach ($endpoints as $endpoint) {
        $fullUrl = $endpoint . '?' . $queryString;
        echo "<h3>Testing GET: {$fullUrl}</h3>";
        
        // Make API call
        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$passwd");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

        if ($httpCode === 200) {
            echo "<p style='color: green;'>✅ GET method works!</p>";
            $result = json_decode($response);
            if ($result) {
                echo "<p style='color: green;'>✅ Valid JSON response</p>";
                return $result;
            }
        } else {
            echo "<p style='color: red;'>❌ GET method failed: {$httpCode}</p>";
        }
        
        echo "<hr>";
    }
}

// Run tests
echo "<div style='font-family: Arial, sans-serif; margin: 20px;'>";

// Test POST method
$postResult = testOtelZHotelDataAPI($partner_id, $facility_reference, $lang, $country, $city_reference, $district_reference);

echo "<hr style='border: 2px solid #333;'>";

// Test GET method
$getResult = testOtelZHotelDataGET($partner_id, $facility_reference, $lang, $country);

echo "<h2>Test Summary</h2>";
echo "<p><strong>POST Result:</strong> " . ($postResult ? "SUCCESS" : "FAILED") . "</p>";
echo "<p><strong>GET Result:</strong> " . ($getResult ? "SUCCESS" : "FAILED") . "</p>";

echo "</div>";
?>
