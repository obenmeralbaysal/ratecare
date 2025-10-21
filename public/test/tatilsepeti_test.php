<?php
/**
 * TatilSepeti API Test
 * Test standalone function to get prices from TatilSepeti
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
 * Search function for HTML parsing
 */
function search($start, $end, $html)
{
    $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/s';
    preg_match_all($pattern, $html, $matches);
    return isset($matches[1]) ? $matches[1] : [];
}

/**
 * TatilSepeti Price Request Function (Copied from ApiController)
 */
function getTatilSepetiPrice($url, $currency, $startDate, $endDate)
{
    echo "<h3>🏖️ Fetching TatilSepeti Price...</h3>";
    
    if (empty($url)) {
        echo "<div class='error'>❌ Empty URL provided</div>";
        return "NA";
    }
    
    try {
        $checkIn = date("d.m.Y", strtotime($startDate));
        $checkOut = date("d.m.Y", strtotime($endDate));
        
        echo "<div class='info'>📅 Check-in: {$checkIn}, Check-out: {$checkOut}</div>";
        
        // Build URL with parameters in TatilSepeti format
        $searchParams = "oda:2;tarih:{$checkIn},{$checkOut}";
        $finalUrl = $url . "?ara=" . urlencode($searchParams);
        
        echo "<div class='info'>🔗 Final URL: {$finalUrl}</div>";
        echo "<div class='info'>📝 Search Parameters: {$searchParams}</div>";
        
        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $finalUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Add proxy settings (same as Booking.com)
        curl_setopt($ch, CURLOPT_PROXY, 'brd.superproxy.io:22225');
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'brd-customer-hl_e5f2315f-zone-datacenter_proxy1-country-nl:uvmqoi66peju');
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        
        echo "<div class='info'>🌐 Using proxy: brd.superproxy.io:22225</div>";
        
        echo "<div class='info'>🌐 Making GET request to: {$finalUrl}</div>";
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if (!$result || $httpCode !== 200) {
            echo "<div class='error'>❌ HTTP error {$httpCode} or empty response</div>";
            if ($curlError) {
                echo "<div class='error'>cURL Error: {$curlError}</div>";
            }
            return "NA";
        }
        
        echo "<div class='success'>✅ Response received (" . strlen($result) . " bytes)</div>";
        
        // Search for price in response - look for Prices--List div and H4 inside
        echo "<div class='info'>🔍 Searching for price in Prices--List div...</div>";
        
        // First find the Prices--List div
        $pricesListDiv = search('<div class="Prices--List">', '</div>', $result);
        
        if (!empty($pricesListDiv)) {
            echo "<div class='info'>✅ Found Prices--List div</div>";
            
            // Search for H4 tag inside the Prices--List div
            $h4Price = search('<h4', '</h4>', $pricesListDiv[0]);
            
            if (!empty($h4Price)) {
                echo "<div class='info'>✅ Found H4 tag in Prices--List</div>";
                
                // Extract price from H4 content
                $priceText = strip_tags($h4Price[0]);
                $price = str_replace(['.', ',', ' ', 'TL', '₺'], '', trim($priceText));
                $price = preg_replace('/[^0-9]/', '', $price);
                
                if (is_numeric($price) && $price > 0) {
                    echo "<div class='success'>💰 Found price in H4: {$price} {$currency}</div>";
                    echo "<div class='info'>📄 H4 content: " . htmlspecialchars($h4Price[0]) . "</div>";
                    return $price;
                }
            } else {
                echo "<div class='warning'>⚠️ No H4 tag found in Prices--List div</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ No Prices--List div found</div>";
            
            // Fallback: try old pattern
            echo "<div class='info'>🔍 Trying fallback pattern...</div>";
            $tryPrice = search('<span class="Prices--Price">', '<small class=\'price-currency\'>', $result);
            
            if (!empty($tryPrice)) {
                $price = str_replace(['.', ',', ' '], '', trim($tryPrice[0]));
                $price = preg_replace('/[^0-9]/', '', $price);
                
                if (is_numeric($price) && $price > 0) {
                    echo "<div class='success'>💰 Found price with fallback pattern: {$price} {$currency}</div>";
                    return $price;
                }
            }
        }
        
        echo "<div class='warning'>⚠️ No valid price found in response</div>";
        
        // Show part of response for debugging
        echo "<h4>📄 Response Sample (first 500 chars):</h4>";
        echo "<pre>" . htmlspecialchars(substr($result, 0, 500)) . "...</pre>";
        
        return "NA";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        return "NA";
    }
}

// Handle form submission
$result = null;
$url = null;
$currency = 'TRY';
$startDate = null;
$endDate = null;

if ($_POST && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    $currency = trim($_POST['currency']) ?: 'TRY';
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);
    
    if (empty($url)) {
        $result = ['error' => 'TatilSepeti URL is required'];
    } elseif (empty($startDate) || empty($endDate)) {
        $result = ['error' => 'Start date and end date are required'];
    } else {
        $price = getTatilSepetiPrice($url, $currency, $startDate, $endDate);
        $result = [
            'success' => true,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'dates' => "{$startDate} to {$endDate}"
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TatilSepeti Price Test</title>
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
        input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="date"]:focus, select:focus {
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
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
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
        .result-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .result-info h3 {
            margin-top: 0;
            color: #007cba;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏖️ TatilSepeti Price Test</h1>
        
        <div class="info">
            <strong>📋 Test Purpose:</strong> This tool tests TatilSepeti price scraping by making POST requests with date parameters.
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

        <form method="POST">
            <div class="form-group">
                <label for="url">🏖️ TatilSepeti Hotel URL:</label>
                <input 
                    type="text" 
                    id="url" 
                    name="url" 
                    value="<?= htmlspecialchars($url ?? '') ?>" 
                    placeholder="https://www.tatilsepeti.com/bademli-konak"
                    required
                >
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">📅 Check-in Date:</label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        value="<?= htmlspecialchars($startDate ?? date('Y-m-d', strtotime('+1 day'))) ?>" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="end_date">📅 Check-out Date:</label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="<?= htmlspecialchars($endDate ?? date('Y-m-d', strtotime('+3 days'))) ?>" 
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="currency">💱 Currency:</label>
                <select id="currency" name="currency">
                    <option value="TRY" <?= ($currency ?? 'TRY') === 'TRY' ? 'selected' : '' ?>>TRY (Turkish Lira)</option>
                    <option value="EUR" <?= ($currency ?? 'TRY') === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                    <option value="USD" <?= ($currency ?? 'TRY') === 'USD' ? 'selected' : '' ?>>USD (US Dollar)</option>
                </select>
            </div>
            
            <button type="submit">
                🔍 Test TatilSepeti Price
            </button>
        </form>

        <?php if ($result): ?>
            <hr style="margin: 30px 0;">
            
            <?php if (isset($result['error'])): ?>
                <div class="error">
                    ❌ <strong>Error:</strong> <?= htmlspecialchars($result['error']) ?>
                </div>
                
            <?php elseif (isset($result['success'])): ?>
                <div class="result-info">
                    <h3>🏖️ TatilSepeti Test Results</h3>
                    <p><strong>URL:</strong> <?= htmlspecialchars($result['url']) ?></p>
                    <p><strong>Dates:</strong> <?= htmlspecialchars($result['dates']) ?></p>
                    <p><strong>Currency:</strong> <?= htmlspecialchars($result['currency']) ?></p>
                    
                    <?php if ($result['price'] !== "NA"): ?>
                        <div class="success">
                            💰 <strong>Price Found:</strong> <?= htmlspecialchars($result['price']) ?> <?= htmlspecialchars($result['currency']) ?>
                        </div>
                    <?php else: ?>
                        <div class="warning">
                            ⚠️ <strong>No Price Found</strong> - Check the response details above
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            <strong>🔧 API Details:</strong><br>
            • Method: GET<br>
            • URL Format: {base_url}?ara=oda:2;tarih:{checkin},{checkout}<br>
            • Example: https://www.tatilsepeti.com/bademli-konak?ara=oda:2;tarih:22.10.2025,25.10.2025<br>
            • Headers: User-Agent, Accept, Accept-Language<br>
            • Proxy: brd.superproxy.io:22225 (Netherlands datacenter)<br>
            • Price Pattern: &lt;div class="Prices--List"&gt;...&lt;h4&gt;price&lt;/h4&gt;...&lt;/div&gt;<br>
            • Timeout: 30 seconds
        </div>
    </div>
</body>
</html>
