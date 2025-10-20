<?php
/**
 * OtelZ Attribute API Test
 * Test file for OtelZ Detail Attribute endpoint
 */

// Test parameters
$facilityID = 14361; // Test facility ID (optional)
$partner_id = 1316;
$lang = "tr";

echo "<h1>OtelZ Attribute API Test</h1>";
echo "<p><strong>Partner ID:</strong> {$partner_id}</p>";
echo "<p><strong>Language:</strong> {$lang}</p>";
echo "<p><strong>Facility ID:</strong> {$facilityID} (optional)</p>";
echo "<hr>";


/**
 * OtelZ Search API - Option 2: Geolocation Filter
 */
function testOtelZSearchGeolocation($partner_id, $latitude, $longitude, $distance, $currency, $startDate, $endDate, $page_size = 20, $page_number = 1) 
{
    echo "<h2>Testing OtelZ Search API - Geolocation Filter</h2>";
    
    // Prepare request data (Search API Format)
    $data = [
        "partner_id" => (int)$partner_id,
        "filter" => [
            "type" => "Geolocation",
            "latitude" => $latitude,
            "longitude" => $longitude,
            "distance" => $distance
        ],
        "start_date" => $startDate,
        "end_date" => $endDate,
        "party" => [
            [
                "adults" => 2,
                "children" => []
            ]
        ],
        "currency" => $currency,
        "price_formatter" => [
            "decimal_digit_number" => 2
        ],
        "user_country" => "TR",
        "device_type" => "Desktop",
        "page_size" => (int)$page_size,
        "page_number" => (int)$page_number
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo "<h3>Request JSON (Geolocation):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>{$json}</pre>";

    return makeOtelZSearchAPICall($data, "Geolocation");
}

/**
 * Make OtelZ Search API Call
 */
function makeOtelZSearchAPICall($data, $filterType) 
{
    // Credentials
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    echo "<p><strong>Username:</strong> {$username}</p>";
    echo "<p><strong>Filter Type:</strong> {$filterType}</p>";

    // Headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$username:$passwd"),
    ];

    // API endpoint
    $endpoint = 'https://fullconnect.otelz.com/v2/search/availability';
    echo "<p><strong>Endpoint:</strong> {$endpoint}</p>";

    // Make API call
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    echo "<p>🔄 Making Search API call...</p>";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curlError) {
        echo "<p style='color: red;'>❌ cURL Error: {$curlError}</p>";
        return null;
    }

    echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

    // Check HTTP status
    if ($httpCode !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: {$httpCode}</p>";
        echo "<h3>Response Body:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
        return null;
    }

    echo "<p style='color: green;'>✅ HTTP 200 OK</p>";

    // Parse JSON response
    $result = json_decode($response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color: red;'>❌ JSON Decode Error: " . json_last_error_msg() . "</p>";
        echo "<h3>Raw Response:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
        return null;
    }

    echo "<p style='color: green;'>✅ JSON parsed successfully</p>";

    // Check for API errors
    if (isset($result->errors)) {
        echo "<p style='color: red;'>❌ API Errors:</p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . 
             htmlspecialchars(json_encode($result->errors, JSON_PRETTY_PRINT)) . "</pre>";
        return null;
    }

    // Analyze search results
    if (isset($result->search_result)) {
        $searchResult = $result->search_result;
        
        echo "<h3>Search Results Summary:</h3>";
        echo "<ul>";
        
        if (isset($searchResult->total_hotel_count)) {
            echo "<li><strong>Total Hotels:</strong> {$searchResult->total_hotel_count}</li>";
        }
        
        if (isset($searchResult->page_info)) {
            $pageInfo = $searchResult->page_info;
            echo "<li><strong>Page:</strong> {$pageInfo->page_number} / {$pageInfo->total_page}</li>";
            echo "<li><strong>Hotels per page:</strong> {$pageInfo->page_size}</li>";
        }
        
        if (isset($searchResult->hotels) && is_array($searchResult->hotels)) {
            echo "<li><strong>Hotels in this page:</strong> " . count($searchResult->hotels) . "</li>";
            
            // Show first few hotels
            echo "</ul>";
            echo "<h4>Hotels Found:</h4>";
            echo "<div style='max-height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;'>";
            
            foreach (array_slice($searchResult->hotels, 0, 5) as $index => $hotel) {
                echo "<div style='border-bottom: 1px solid #eee; padding: 10px 0;'>";
                echo "<h5>Hotel " . ($index + 1) . ":</h5>";
                echo "<ul>";
                
                if (isset($hotel->facility_reference)) {
                    echo "<li><strong>Facility ID:</strong> {$hotel->facility_reference}</li>";
                }
                if (isset($hotel->name)) {
                    echo "<li><strong>Name:</strong> {$hotel->name}</li>";
                }
                if (isset($hotel->address)) {
                    echo "<li><strong>Address:</strong> {$hotel->address}</li>";
                }
                if (isset($hotel->min_price)) {
                    $minPrice = $hotel->min_price;
                    if (isset($minPrice->net_total->amount)) {
                        echo "<li><strong>Min Price:</strong> {$minPrice->net_total->amount} {$minPrice->net_total->currency}</li>";
                    }
                }
                if (isset($hotel->distance)) {
                    echo "<li><strong>Distance:</strong> {$hotel->distance} km</li>";
                }
                
                echo "</ul>";
                echo "</div>";
            }
            
            if (count($searchResult->hotels) > 5) {
                echo "<p><em>... and " . (count($searchResult->hotels) - 5) . " more hotels</em></p>";
            }
            
            echo "</div>";
        }
        
        echo "</ul>";
    }

    // Show full response (truncated)
    echo "<h3>Full API Response (first 2000 chars):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: scroll;'>" . 
         htmlspecialchars(substr(json_encode($result, JSON_PRETTY_PRINT), 0, 2000)) . "...</pre>";

    return $result;
}

/**
 * OtelZ Attribute API Test
 */
function testOtelZAttributeAPI($facilityID, $partner_id, $lang = "tr") 
{
    echo "<h2>Testing OtelZ Attribute API</h2>";
    
    // Validate facility ID
    if ($facilityID && !ctype_digit((string)$facilityID)) {
        echo "<p style='color: red;'>❌ Invalid facility ID: {$facilityID}</p>";
        return "NA";
    }

    // Prepare request data (Attribute API Format)
    $data = [
        "partner_id" => (int)$partner_id,
        "lang" => $lang
    ];
    
    // Add facility_reference if provided
    if ($facilityID) {
        $data["facility_reference"] = (int)$facilityID;
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo "<h3>Request JSON (Attribute API):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>{$json}</pre>";

    // Credentials
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    echo "<p><strong>Username:</strong> {$username}</p>";
    echo "<p><strong>Partner ID:</strong> {$partner_id}</p>";
    echo "<p><strong>Language:</strong> {$lang}</p>";
    if ($facilityID) {
        echo "<p><strong>Facility ID:</strong> {$facilityID}</p>";
    } else {
        echo "<p><strong>Facility ID:</strong> All facilities</p>";
    }

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

    // API endpoint
    $endpoint = 'https://fullconnect.otelz.com/v1/detail/attribute';
    echo "<p><strong>Endpoint:</strong> {$endpoint}</p>";

    // Make API call
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    echo "<p>🔄 Making Attribute API call...</p>";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curlError) {
        echo "<p style='color: red;'>❌ cURL Error: {$curlError}</p>";
        return "NA";
    }

    echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

    // Check HTTP status
    if ($httpCode !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: {$httpCode}</p>";
        echo "<h3>Response Body:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
        return "NA";
    }

    echo "<p style='color: green;'>✅ HTTP 200 OK</p>";

    // Parse JSON response
    $result = json_decode($response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color: red;'>❌ JSON Decode Error: " . json_last_error_msg() . "</p>";
        echo "<h3>Raw Response:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
        return "NA";
    }

    echo "<p style='color: green;'>✅ JSON parsed successfully</p>";

    // Check for API errors
    if (isset($result->errors)) {
        echo "<p style='color: red;'>❌ API Errors:</p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . 
             htmlspecialchars(json_encode($result->errors, JSON_PRETTY_PRINT)) . "</pre>";
        return "NA";
    }

    // Analyze attribute results
    if (isset($result->attribute_result)) {
        $attributeResult = $result->attribute_result;
        
        echo "<h3>Attribute Results Summary:</h3>";
        echo "<ul>";
        
        if (isset($attributeResult->facilities) && is_array($attributeResult->facilities)) {
            echo "<li><strong>Facilities Found:</strong> " . count($attributeResult->facilities) . "</li>";
            
            // Show first facility's attributes
            if (count($attributeResult->facilities) > 0) {
                $facility = $attributeResult->facilities[0];
                echo "</ul>";
                echo "<h4>First Facility Attributes:</h4>";
                echo "<div style='max-height: 500px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;'>";
                
                if (isset($facility->facility_reference)) {
                    echo "<p><strong>Facility ID:</strong> {$facility->facility_reference}</p>";
                }
                if (isset($facility->name)) {
                    echo "<p><strong>Name:</strong> {$facility->name}</p>";
                }
                
                if (isset($facility->attribute_groups) && is_array($facility->attribute_groups)) {
                    echo "<h5>Attribute Groups (" . count($facility->attribute_groups) . "):</h5>";
                    
                    foreach ($facility->attribute_groups as $group) {
                        echo "<div style='border: 1px solid #eee; margin: 10px 0; padding: 10px; background: #f9f9f9;'>";
                        
                        if (isset($group->group_label)) {
                            echo "<h6 style='color: #2c3e50; margin: 0 0 10px 0;'>{$group->group_label}</h6>";
                        }
                        
                        if (isset($group->attributes) && is_array($group->attributes)) {
                            echo "<ul style='margin: 0; padding-left: 20px;'>";
                            foreach ($group->attributes as $attribute) {
                                if (isset($attribute->label)) {
                                    echo "<li>{$attribute->label}";
                                    if (isset($attribute->attribute_reference)) {
                                        echo " <small>(ID: {$attribute->attribute_reference})</small>";
                                    }
                                    echo "</li>";
                                }
                            }
                            echo "</ul>";
                        }
                        
                        echo "</div>";
                    }
                }
                
                echo "</div>";
            }
        } elseif (isset($attributeResult->attribute_groups)) {
            // Global attribute groups (not facility-specific)
            echo "<li><strong>Global Attribute Groups:</strong> " . count($attributeResult->attribute_groups) . "</li>";
            echo "</ul>";
            
            echo "<h4>Global Attribute Groups:</h4>";
            echo "<div style='max-height: 500px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;'>";
            
            foreach ($attributeResult->attribute_groups as $group) {
                echo "<div style='border: 1px solid #eee; margin: 10px 0; padding: 10px; background: #f9f9f9;'>";
                
                if (isset($group->group_label)) {
                    echo "<h6 style='color: #2c3e50; margin: 0 0 10px 0;'>{$group->group_label}</h6>";
                }
                if (isset($group->group_reference)) {
                    echo "<p><small>Group ID: {$group->group_reference}</small></p>";
                }
                
                if (isset($group->attributes) && is_array($group->attributes)) {
                    echo "<ul style='margin: 0; padding-left: 20px;'>";
                    foreach (array_slice($group->attributes, 0, 10) as $attribute) {
                        if (isset($attribute->label)) {
                            echo "<li>{$attribute->label}";
                            if (isset($attribute->attribute_reference)) {
                                echo " <small>(ID: {$attribute->attribute_reference})</small>";
                            }
                            echo "</li>";
                        }
                    }
                    if (count($group->attributes) > 10) {
                        echo "<li><em>... and " . (count($group->attributes) - 10) . " more attributes</em></li>";
                    }
                    echo "</ul>";
                }
                
                echo "</div>";
            }
            
            echo "</div>";
        }
        
        echo "</ul>";
    }

    // Show full response (truncated)
    echo "<h3>Full API Response (first 2000 chars):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: scroll;'>" . 
         htmlspecialchars(substr(json_encode($result, JSON_PRETTY_PRINT), 0, 2000)) . "...</pre>";

    return $result;
        //"request_type" => 1,
        //"web_hook_url" => "",
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo "<h3>Request JSON:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>{$json}</pre>";

    // Credentials
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    echo "<p><strong>Username:</strong> {$username}</p>";
    echo "<p><strong>Partner ID:</strong> 1316</p>";

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

    // Make API call
    $ch = curl_init('https://fullconnect.otelz.com/v1/detail/availability');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    echo "<p>🔄 Making API call to: <strong>https://fullconnect.otelz.com/v1/detail/availability</strong></p>";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curlError) {
        echo "<p style='color: red;'>❌ cURL Error: {$curlError}</p>";
        return "NA";
    }

    echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

    // Check HTTP status
    if ($httpCode !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: {$httpCode}</p>";
        echo "<h3>Response Body:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars($response) . "</pre>";
        return "NA";
    }

    echo "<p style='color: green;'>✅ HTTP 200 OK</p>";

    // Parse JSON response
    $result = json_decode($response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color: red;'>❌ JSON Decode Error: " . json_last_error_msg() . "</p>";
        echo "<h3>Raw Response:</h3>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars($response) . "</pre>";
        return "NA";
    }

    echo "<p style='color: green;'>✅ JSON parsed successfully</p>";

    // Show full response
    echo "<h3>Full API Response:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: scroll;'>" . 
         htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre>";

    // Check for API errors
    if (isset($result->errors)) {
        echo "<p style='color: red;'>❌ API Errors:</p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . 
             htmlspecialchars(json_encode($result->errors, JSON_PRETTY_PRINT)) . "</pre>";
        return "NA";
    }

    // Parse price (Old System Logic)
    if ($result && isset($result->detail_result)) {
        if (isset($result->detail_result->min_price)) {
            if ($result->detail_result->min_price->total_room == 0) {
                echo "<p style='color: orange;'>⚠️ No rooms available</p>";
                return "NA";
            }
            
            if (isset($result->detail_result->min_price->net_total->amount)) {
                $price = $result->detail_result->min_price->net_total->amount;
                $finalPrice = round($price);
                echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Price Found: {$finalPrice} {$currency}</p>";
                return $finalPrice;
            }
        }
    }

    echo "<p style='color: red;'>❌ No price found in response</p>";
    return "NA";
}

/**
 * Test with New System Format
 */
function testOtelZAPINewFormat($facilityID, $currency, $startDate, $endDate) 
{
    echo "<h2>Testing OtelZ API (New System Format)</h2>";
    
    // Prepare request data (New System Format)
    $data = [
        "detail_request" => [
            "facility_reference" => (int)$facilityID,
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
            "partner_id" => 1316
        ]
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo "<h3>Request JSON (New Format):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>{$json}</pre>";

    // Same API call logic as above...
    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$username:$passwd"),
    ];

    $ch = curl_init('https://fullconnect.otelz.com/detail/availability');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    echo "<p>🔄 Making API call with new format...</p>";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "<p><strong>HTTP Status Code:</strong> {$httpCode}</p>";

    if ($httpCode !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: {$httpCode}</p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999;'>" . htmlspecialchars($response) . "</pre>";
        return "NA";
    }

    echo "<p style='color: green;'>✅ New format also works!</p>";
    return "SUCCESS";
}

// Run tests
echo "<div style='font-family: Arial, sans-serif; margin: 20px;'>";

// Test 1: Attribute API with specific facility
echo "<h1 style='color: #2c3e50;'>🏷️ Test 1: Attribute API - Specific Facility</h1>";
$attributeResult1 = testOtelZAttributeAPI($facilityID, $partner_id, $lang);

echo "<hr style='border: 2px solid #34495e; margin: 30px 0;'>";

// Test 2: Attribute API without facility (all attributes)
echo "<h1 style='color: #2c3e50;'>🏷️ Test 2: Attribute API - All Attributes</h1>";
$attributeResult2 = testOtelZAttributeAPI(null, $partner_id, $lang);

echo "<hr style='border: 2px solid #34495e; margin: 30px 0;'>";

// Test Summary
echo "<h1 style='color: #2c3e50;'>📊 Test Summary</h1>";
echo "<div style='background: #ecf0f1; padding: 20px; border-radius: 8px;'>";
echo "<h3>Results:</h3>";
echo "<ul style='font-size: 16px;'>";
echo "<li><strong>Specific Facility Attributes:</strong> " . ($attributeResult1 ? "<span style='color: green;'>✅ SUCCESS</span>" : "<span style='color: red;'>❌ FAILED</span>") . "</li>";
echo "<li><strong>All Attributes:</strong> " . ($attributeResult2 ? "<span style='color: green;'>✅ SUCCESS</span>" : "<span style='color: red;'>❌ FAILED</span>") . "</li>";
echo "</ul>";

echo "<h3>API Endpoint Tested:</h3>";
echo "<ul>";
echo "<li><strong>Attribute API:</strong> <code>https://fullconnect.otelz.com/v1/detail/attribute</code></li>";
echo "</ul>";

echo "<h3>Parameters:</h3>";
echo "<ul>";
echo "<li><strong>partner_id:</strong> {$partner_id} (Required)</li>";
echo "<li><strong>lang:</strong> {$lang} (Optional, default: tr)</li>";
echo "<li><strong>facility_reference:</strong> {$facilityID} (Optional)</li>";
echo "</ul>";

echo "<h3>Key Information:</h3>";
echo "<ul>";
echo "<li><strong>With facility_reference:</strong> Returns attributes for specific hotel</li>";
echo "<li><strong>Without facility_reference:</strong> Returns all available attribute groups</li>";
echo "<li><strong>Response format:</strong> attribute_result with facilities or attribute_groups</li>";
echo "<li><strong>Attributes grouped by:</strong> Banyo Özellikleri, Genel Özellikler, etc.</li>";
echo "</ul>";

if ($attributeResult1 && isset($attributeResult1->attribute_result->facilities)) {
    $facilityCount = count($attributeResult1->attribute_result->facilities);
    echo "<p><strong>Specific Facility Result:</strong> Found {$facilityCount} facility with attributes</p>";
}

if ($attributeResult2 && isset($attributeResult2->attribute_result->attribute_groups)) {
    $groupCount = count($attributeResult2->attribute_result->attribute_groups);
    echo "<p><strong>All Attributes Result:</strong> Found {$groupCount} attribute groups</p>";
}

echo "</div>";

echo "</div>";
?>
