<?php

function getETSTurPrice($hotelID, $currency, $startDate, $endDate)
{
  $postData = [
    "hotelId" => $hotelID,
    "checkIn" => $startDate,
    "checkOut" => $endDate,
    "adults" => 2,
    "currency" => $currency,
  ];
  $postData = json_encode($postData);

  $ch = curl_init('https://mapi.etstur.com/api/kucukoteller/availability');
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // İsteği bir kez gönder ve yanıtı sakla
  $response_text = curl_exec($ch);
  

  print_r($response_text);
  
  if (empty($response_text)) {
    return ['price' => "", 'url' => ""];
  }

  $response = json_decode($response_text);
  
  // Hata kontrolü ekleyelim
  if (json_last_error() !== JSON_ERROR_NONE || !isset($response->totalRate)) {
    return ['price' => "", 'url' => ""];
  }

  return ['price' => round($response->totalRate), 'url' => $response->deeplink];
}

// Fonksiyonu test et
$result = getETSTurPrice("ARSMZN", "TRY", "2025-05-20", "2025-05-22");

// Sonuçları görüntüle
echo "<pre>";
echo "Fiyat: " . $result['price'] . " TL\n";
echo "URL: " . $result['url'];
echo "</pre>";
