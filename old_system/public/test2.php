<?php
$url = 'https://www.booking.com/hotel/tr/senatus.tr.html?selected_currency=USD&checkin=2024-10-01&checkout=2024-10-02';
$curl = curl_init($url);

    // Proxy ayarları
    curl_setopt($curl, CURLOPT_PROXY, 'http://p.webshare.io:80');
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, 'fbpzdsgj-rotate:dinjdivjy8rf');

    // Diğer cURL ayarları
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // Sonuçları değişkene atamak için bu ayarı kullanıyoruz.
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // Yönlendirmeleri takip et
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

    echo $html_content = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'cURL Hata Kodu: ' . curl_errno($curl) . '<br>';
        echo 'cURL Hatası: ' . curl_error($curl);
    }

    curl_close($curl);