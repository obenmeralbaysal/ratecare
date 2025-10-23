# Gerçek API Hata Takip Sistemi

## 🎯 Amaç

**Cache Miss ≠ API Hatası!**

- ❌ Cache miss: Sadece veri cache'de yok demek
- ✅ API hatası: Platform timeout, 404, 500, connection error vs.

Bu sistem **gerçek API hatalarını** takip eder.

---

## 📦 Kurulum

### 1️⃣ Migration Çalıştır

```bash
mysql -u root -p hotel_digilab < migrations/add_channel_error_tracking.sql
```

**Eklenecek Kolonlar:**
- `channel` - Platform adı (booking, hotels, sabee, etc.)
- `has_error` - API hatası var mı? (0/1)
- `error_platforms` - Hangi platformlar hata verdi (JSON)
- `error_message` - Hata detayı (TEXT)

### 2️⃣ API Logger Kullanımı

API request işlerken hataları kaydet:

```php
use App\Helpers\ApiStatistics;

$statistics = new ApiStatistics();

// API request sonrası
$hasError = false;
$errorPlatforms = [];
$errorMessage = null;

// Platform response'larını kontrol et
foreach ($platformResponses as $platform => $response) {
    if ($response['error'] || $response['status'] != 200) {
        $hasError = true;
        $errorPlatforms[] = $platform;
        $errorMessage .= "$platform: " . $response['error_message'] . "; ";
    }
}

// Log the request with error tracking
$statistics->logRequest(
    widgetCode: $widgetCode,
    params: $requestParams,
    cacheHitType: $cacheHitType,
    cachedPlatforms: $cachedPlatforms,
    requestedPlatforms: $requestedPlatforms,
    updatedPlatforms: $updatedPlatforms,
    responseTimeMs: $responseTime,
    
    // NEW: Error tracking parameters
    channel: 'booking',              // Ana kullanılan platform
    hasError: $hasError,             // Hata var mı?
    errorPlatforms: $errorPlatforms, // Hatalı platformlar
    errorMessage: trim($errorMessage)// Hata mesajı
);
```

---

## 🔍 Hata Tespiti Örnekleri

### Örnek 1: Timeout Hatası
```php
if ($response['timeout']) {
    $hasError = true;
    $errorPlatforms[] = 'booking';
    $errorMessage = "Booking API timeout after 5s";
}
```

### Örnek 2: HTTP Status Hatası
```php
if ($response['http_code'] >= 400) {
    $hasError = true;
    $errorPlatforms[] = 'hotels';
    $errorMessage = "Hotels.com returned " . $response['http_code'];
}
```

### Örnek 3: Empty Response
```php
if (empty($response['data'])) {
    $hasError = true;
    $errorPlatforms[] = 'sabee';
    $errorMessage = "Sabee returned empty data";
}
```

### Örnek 4: Connection Error
```php
if (!$response['connected']) {
    $hasError = true;
    $errorPlatforms[] = 'odamax';
    $errorMessage = "Cannot connect to Odamax API";
}
```

---

## 📊 Veri Kullanımı

### Dashboard - Most Stable Channel

```sql
SELECT channel, COUNT(*) as total, 
       SUM(has_error) as errors,
       (SUM(has_error) / COUNT(*)) * 100 as error_rate
FROM api_statistics
WHERE DATE(created_at) = CURDATE()
  AND channel IS NOT NULL
GROUP BY channel
HAVING total >= 10
ORDER BY error_rate ASC
LIMIT 1
```

**Sonuç:** En az hata veren = En stabil platform

### İstatistikler - Channel Error Rates

```sql
SELECT * FROM channel_error_statistics
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY error_rate DESC
```

**Gösterilen:**
- Total requests
- Error count
- Error rate %
- Success rate %
- Avg response time
- Status badge (critical/warning/healthy)

---

## 🎨 Frontend Görünümü

### Dashboard Card
```
┌──────────────────────────┐
│ ⭐ Booking              │
│ MOST STABLE CHANNEL      │
│ Least errors & reliable  │
└──────────────────────────┘
```

### Statistics Table
```
Channel | Total | Errors | Error Rate | Status
--------|-------|--------|------------|--------
Sabee   | 456   | 120    | 26.32%     | 🔴 Critical
Booking | 1234  | 150    | 12.15%     | ⚠️ Warning
Hotels  | 890   | 45     | 5.06%      | ✅ Healthy
```

**Status Levels:**
- 🔴 Critical: > 20% error rate
- ⚠️ Warning: > 10% error rate
- ✅ Healthy: ≤ 10% error rate

---

## 🔄 Backward Compatibility

Kod eski API ile de çalışır:

```php
// Yeni parametreler opsiyonel
$statistics->logRequest($code, $params, 'full');
// channel, hasError, vs. otomatik NULL/false olur
```

Migration öncesi:
- Query hata verir → catch bloğu devreye girer
- Fallback olarak eski veri kullanılır
- Sayfa çökmez, sadece log'a hata yazar

---

## ✅ Test Etme

### 1. Migration Sonrası
```sql
-- Kolonlar eklendi mi?
DESCRIBE api_statistics;

-- View oluştu mu?
SELECT * FROM channel_error_statistics LIMIT 5;
```

### 2. Gerçek Veri Kaydedildi mi?
```sql
-- Son kayıtları kontrol et
SELECT channel, has_error, error_platforms, error_message
FROM api_statistics
ORDER BY created_at DESC
LIMIT 10;
```

### 3. Dashboard Test
- Most Stable Channel: Gerçek veri gösteriyor mu?
- Error Rates Table: Tabloda veri var mı?
- Status badge'ler doğru mu?

---

## 📝 TODO

### Sonraki Adımlar:

1. **API Request Handler'ı Güncelle**
   - Her platform için error detection ekle
   - Timeout, HTTP errors, empty response kontrol et
   - `logRequest()` çağrısına hata parametrelerini ekle

2. **Alert System**
   - Error rate > 20% olunca email gönder
   - Kritik platformlar için Slack notification

3. **Historical Analysis**
   - Hangi saatlerde daha fazla hata var?
   - Hangi günler problematik?
   - Trend analizi

4. **Auto Recovery**
   - Hatalı platform'u geçici disable et
   - Alternatif platform'a yönlendir
   - 5 dk sonra tekrar dene

---

## 🐛 Troubleshooting

### "Column not found" Hatası
```
Migration henüz çalışmadı.
→ Çözüm: migrations/add_channel_error_tracking.sql çalıştır
```

### "No data in error table"
```
Logger henüz hata parametrelerini göndermiyor.
→ Çözüm: API handler'ı güncelle, hataları kaydet
```

### "Always showing N/A"
```
Channel kolonu NULL.
→ Çözüm: logRequest() çağrılarına channel parametresi ekle
```

---

**Created:** 2025-10-23  
**Version:** 1.0  
**Status:** ✅ Ready for Production
