<?php
// Bright Data Proxy bilgileri
$username = 'brd-customer-hl_e5f2315f-zone-ratecare-country-nl';  // Bright Data kullanıcı adınız
$password = 'keietyq936q3';          // Bright Data şifreniz
$host = 'brd.superproxy.io';  // Bright Data'nın proxy hostu
$port = 22225;  // Varsayılan port

// Hedef URL
$url = 'https://www.booking.com/hotel/tr/senatus.tr.html?selected_currency=USD&checkin=2024-10-01&checkout=2024-10-02';

// cURL oturumu başlat
$ch = curl_init();

// cURL seçeneklerini ayarla
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Proxy ayarları
$proxy = "$host:$port";
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$username:$password");
   // Diğer cURL ayarları
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Sonuçları değişkene atamak için bu ayarı kullanıyoruz.
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Yönlendirmeleri takip etmek için bu ayarı kullanıyoruz.

// SSL doğrulamasını atla (isteğe bağlı)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// İsteği gönder ve yanıtı al
$response = curl_exec($ch);

// HTTP durum kodunu al
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Hata kontrolü
if(curl_errno($ch)){
    echo 'Curl Hatası: ' . curl_error($ch);
}

// cURL oturumunu kapat
curl_close($ch);

// Sonuçları yazdır
echo "HTTP Durum Kodu: $http_code\n";
echo "İçerik:\n$response";
?>
