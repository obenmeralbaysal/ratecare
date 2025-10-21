<?php
/**
 * Sabee API Hotel Inventory Test
 * Test standalone function to get hotel inventory from Sabee API
 */

// Simple env function
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

// Load .env file manually if needed
$possibleEnvPaths = [
    __DIR__ . '/../../.env',
    __DIR__ . '/../../../.env',
    '/var/www/html/ratecare/.env',
    '/Applications/XAMPP/xamppfiles/htdocs/ratecare/.env'
];

$envFileFound = null;
foreach ($possibleEnvPaths as $envFile) {
    if (file_exists($envFile)) {
        $envFileFound = $envFile;
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value, '"\'');
            }
        }
        break; // Stop after finding first .env file
    }
}

/**
 * Sabee API Request Function (Copied from ApiController)
 */
function sabeeRequest($endpoint, $parameters, $sabeeApiKey)
{
    try {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $sabeeApiKey,
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.sabee.app/v1/{$endpoint}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo "<div class='error'>Sabee API: HTTP error {$httpCode} for endpoint {$endpoint}</div>";
            if ($curlError) {
                echo "<div class='error'>cURL Error: {$curlError}</div>";
            }
            return null;
        }
        
        return json_decode($response);
        
    } catch (Exception $e) {
        echo "<div class='error'>Sabee API: Request error - " . $e->getMessage() . "</div>";
        return null;
    }
}

/**
 * Get Sabee Hotel Inventory
 */
function getSabeeInventory($sabeeApiKey)
{
    echo "<h3>🏨 Fetching Sabee Hotel Inventory...</h3>";
    
    $response = sabeeRequest('hotel/inventory', [], $sabeeApiKey);
    
    if (!$response || !isset($response->success) || !$response->success) {
        echo "<div class='error'>❌ Failed to get hotel inventory</div>";
        if ($response) {
            echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
        }
        return null;
    }
    
    echo "<div class='success'>✅ Hotel inventory fetched successfully</div>";
    return $response->data->hotels;
}

/**
 * Find Hotel by ID
 */
function findHotelById($hotels, $hotelId)
{
    foreach ($hotels as $hotel) {
        if ($hotel->hotel_id == $hotelId) {
            return $hotel;
        }
    }
    return null;
}

// Handle form submission
$result = null;
$hotelId = null;
$sabeeApiKey = env('SABEE_API_KEY');

if ($_POST && isset($_POST['hotel_id'])) {
    $hotelId = trim($_POST['hotel_id']);
    
    if (empty($sabeeApiKey)) {
        $result = ['error' => 'SABEE_API_KEY not configured in environment'];
    } elseif (empty($hotelId)) {
        $result = ['error' => 'Hotel ID is required'];
    } else {
        $hotels = getSabeeInventory($sabeeApiKey);
        
        if ($hotels) {
            $hotel = findHotelById($hotels, $hotelId);
            
            if ($hotel) {
                $result = [
                    'success' => true,
                    'hotel' => $hotel,
                    'total_hotels' => count($hotels)
                ];
            } else {
                $result = [
                    'error' => "Hotel with ID '{$hotelId}' not found in inventory",
                    'available_hotels' => array_map(function($h) {
                        return [
                            'hotel_id' => $h->hotel_id,
                            'name' => $h->name ?? 'N/A'
                        ];
                    }, array_slice($hotels, 0, 10)) // Show first 10 hotels
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabee Hotel Inventory Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #007cba;
            outline: none;
        }
        button {
            background-color: #007cba;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #005a87;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #bee5eb;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #e9ecef;
            font-size: 14px;
        }
        .hotel-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .hotel-info h3 {
            margin-top: 0;
            color: #007cba;
        }
        .room-type {
            background-color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
            border-left: 4px solid #007cba;
        }
        .available-hotels {
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Sabee Hotel Inventory Test</h1>
        
        <div class="info">
            <strong>📋 Test Purpose:</strong> This tool tests the Sabee API hotel inventory endpoint and searches for a specific hotel by ID.
            <br><br>
            <strong>🔧 Environment:</strong> 
            <?php if ($envFileFound): ?>
                ✅ .env file loaded from: <code><?= htmlspecialchars($envFileFound) ?></code>
            <?php else: ?>
                ⚠️ No .env file found. Checked paths:
                <ul style="margin: 5px 0;">
                    <?php foreach ($possibleEnvPaths as $path): ?>
                        <li><code><?= htmlspecialchars($path) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!$sabeeApiKey): ?>
            <div class="error">
                ⚠️ <strong>SABEE_API_KEY not configured!</strong><br>
                Please set the SABEE_API_KEY in your .env file.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="hotel_id">🏨 Hotel ID:</label>
                <input 
                    type="text" 
                    id="hotel_id" 
                    name="hotel_id" 
                    value="<?= htmlspecialchars($hotelId ?? '') ?>" 
                    placeholder="Enter Sabee Hotel ID (e.g., 12345)"
                    required
                >
            </div>
            
            <button type="submit" <?= !$sabeeApiKey ? 'disabled' : '' ?>>
                🔍 Test Hotel Inventory
            </button>
        </form>

        <?php if ($result): ?>
            <hr style="margin: 30px 0;">
            
            <?php if (isset($result['error'])): ?>
                <div class="error">
                    ❌ <strong>Error:</strong> <?= htmlspecialchars($result['error']) ?>
                </div>
                
                <?php if (isset($result['available_hotels'])): ?>
                    <div class="info">
                        <strong>📋 Available Hotels (first 10):</strong>
                    </div>
                    <div class="available-hotels">
                        <?php foreach ($result['available_hotels'] as $hotel): ?>
                            <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                                <strong>ID:</strong> <?= htmlspecialchars($hotel['hotel_id']) ?> - 
                                <strong>Name:</strong> <?= htmlspecialchars($hotel['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif (isset($result['success'])): ?>
                <div class="success">
                    ✅ <strong>Hotel Found!</strong> (Total hotels in inventory: <?= $result['total_hotels'] ?>)
                </div>
                
                <div class="hotel-info">
                    <h3>🏨 Hotel Information</h3>
                    <p><strong>Hotel ID:</strong> <?= htmlspecialchars($result['hotel']->hotel_id) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($result['hotel']->name ?? 'N/A') ?></p>
                    
                    <?php if (isset($result['hotel']->room_types) && is_array($result['hotel']->room_types)): ?>
                        <h4>🛏️ Room Types (<?= count($result['hotel']->room_types) ?>):</h4>
                        <?php foreach ($result['hotel']->room_types as $roomType): ?>
                            <div class="room-type">
                                <strong>Room ID:</strong> <?= htmlspecialchars($roomType->room_id ?? 'N/A') ?><br>
                                <strong>Name:</strong> <?= htmlspecialchars($roomType->name ?? 'N/A') ?><br>
                                <?php if (isset($roomType->capacity)): ?>
                                    <strong>Capacity:</strong> <?= htmlspecialchars($roomType->capacity) ?><br>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><em>No room types available</em></p>
                    <?php endif; ?>
                    
                    <h4>📄 Full Hotel Data:</h4>
                    <pre><?= json_encode($result['hotel'], JSON_PRETTY_PRINT) ?></pre>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            <strong>🔧 API Details:</strong><br>
            • Endpoint: https://api.sabee.app/v1/hotel/inventory<br>
            • Method: POST<br>
            • Authentication: Bearer Token<br>
            • Timeout: 30 seconds
        </div>
    </div>
</body>
</html>
