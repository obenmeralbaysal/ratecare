# API Cache & Statistics Implementation TODO List

## 📋 Genel Bakış
API isteklerini cache'leyerek performans artırımı ve detaylı istatistik toplama sistemi.

---

## 🎯 PHASE 1: Database Yapısı

### 1.1 Settings Tablosu Kontrolü
- [ ] `settings` tablosunda `caching-time` kaydı var mı kontrol et
- [ ] Yoksa, default value ile ekle (örn: 30 dakika)
```sql
INSERT INTO settings (key, value, description) 
VALUES ('caching-time', '30', 'API cache süresi (dakika)');
```

### 1.2 Cache Tablosu Oluştur
- [ ] `api_cache` tablosu oluştur
```sql
CREATE TABLE api_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    widget_code VARCHAR(50) NOT NULL,
    parameters JSON NOT NULL,
    response_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_cache_key (cache_key),
    INDEX idx_widget_code (widget_code),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 1.3 İstatistik Tablosu Oluştur
- [ ] `api_statistics` tablosu oluştur
```sql
CREATE TABLE api_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_code VARCHAR(50) NOT NULL,
    request_date DATE NOT NULL,
    request_time TIME NOT NULL,
    parameters JSON NOT NULL,
    cache_hit_type ENUM('full', 'partial', 'miss') NOT NULL DEFAULT 'miss',
    cached_platforms JSON NULL COMMENT 'Cache den okunan platformlar',
    requested_platforms JSON NULL COMMENT 'API ye istek atılan platformlar',
    updated_platforms JSON NULL COMMENT 'Cache e eklenen/güncellenen platformlar',
    response_time_ms INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_widget_code (widget_code),
    INDEX idx_request_date (request_date),
    INDEX idx_cache_hit_type (cache_hit_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 1.4 Özet İstatistik Tablosu (Opsiyonel - Performance için)
- [ ] `api_statistics_summary` tablosu oluştur
```sql
CREATE TABLE api_statistics_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_requests INT DEFAULT 0,
    cache_full_hits INT DEFAULT 0,
    cache_partial_hits INT DEFAULT 0,
    cache_misses INT DEFAULT 0,
    total_platforms_requested INT DEFAULT 0,
    total_platforms_from_cache INT DEFAULT 0,
    channels_usage JSON NULL COMMENT 'Her platform kaç kez istendi',
    avg_response_time_ms INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔧 PHASE 2: Backend - Cache System

### 2.1 Cache Helper Class Oluştur
- [ ] `/app/Helpers/ApiCache.php` dosyası oluştur
- [ ] Fonksiyonlar:
  - `getCacheTime()` - Settings'den cache süresini oku
  - `generateCacheKey($widgetCode, $params)` - Unique cache key oluştur
  - `get($cacheKey)` - Cache'den oku
  - `set($cacheKey, $data, $widgetCode, $params)` - Cache'e yaz
  - `clear($widgetCode = null)` - Cache temizle
  - `isExpired($cacheKey)` - Expire kontrolü
  - `cleanExpired()` - Expired cache'leri temizle

### 2.2 Statistics Helper Class Oluştur
- [ ] `/app/Helpers/ApiStatistics.php` dosyası oluştur
- [ ] Fonksiyonlar:
  - `logRequest($widgetCode, $params, $cacheHit, $channels, $responseTime)`
  - `getCacheHitRate($startDate, $endDate)`
  - `getChannelUsage($startDate, $endDate)`
  - `getTotalRequests($startDate, $endDate)`
  - `getDailySummary($date)`
  - `updateDailySummary($date)`

### 2.3 ApiController'a Cache Entegrasyonu
- [ ] `ApiController.php` dosyasını güncelle
- [ ] `getRequest()` metoduna cache logic ekle:
```php
// Pseudo code:
1. Generate cache key from parameters
2. Check if cache exists and not expired
3. If cache hit:
   - Read cached response
   - Check for failed/NA platforms
   - If all platforms OK:
     * Return cached response (FULL CACHE HIT)
     * Log cache hit to statistics
   - If some platforms failed/NA:
     * Request ONLY missing platforms (PARTIAL CACHE HIT)
     * Merge new results with cached data
     * Update cache with merged data
     * Log partial cache hit + requested channels
4. If cache miss:
   - Process request normally (ALL PLATFORMS)
   - Track which channels were requested
   - Save response to cache
   - Log cache miss + channels to statistics
5. Return response
```

### 2.4 Partial Cache Update Strategy (İNOVATİF ÖZELLİK!)
- [ ] Cache'den okunan verinin platform durumunu kontrol et
- [ ] Platform durumları:
  - `success` - Fiyat var ve valid
  - `failed` - Status = "failed"
  - `NA` - Price = "NA"
  - `missing` - Platform cache'de yok

- [ ] Eksik/başarısız platformları tespit et:
```php
function getMissingPlatforms($cachedResponse, $hotel) {
    $missing = [];
    $allPlatforms = ['booking', 'hotels', 'etstur', 'sabeeapp', 'otelz', 'tatilsepeti', 'reseliva'];
    
    foreach ($allPlatforms as $platform) {
        // Hotel'de platform aktif mi?
        if (!isPlatformActive($hotel, $platform)) continue;
        
        // Cache'de var mı?
        $platformData = findPlatformInCache($cachedResponse, $platform);
        
        if (!$platformData || 
            $platformData['status'] === 'failed' || 
            $platformData['price'] === 'NA') {
            $missing[] = $platform;
        }
    }
    
    return $missing;
}
```

- [ ] Sadece eksik platformlar için istek at:
```php
$missingPlatforms = getMissingPlatforms($cachedData, $hotel);

if (!empty($missingPlatforms)) {
    // Selective platform requests
    foreach ($missingPlatforms as $platform) {
        $newPrice = requestPlatform($platform, $hotel, $params);
        
        if ($newPrice !== "NA" && $newPrice['status'] === 'success') {
            // Success! Update cache
            updateCachedPlatform($cacheKey, $platform, $newPrice);
        }
    }
    
    // Statistics'e partial cache hit olarak kaydet
    logPartialCacheHit($widgetCode, $missingPlatforms);
}
```

- [ ] Cache güncelleme stratejisi:
  - Yeni başarılı platform verisi gelirse → Cache'e EKLE/GÜNCELLE
  - Yine failed/NA ise → Cache'i değiştirme (eski veri kalsın)
  - Expire time'ı UZAT (partial update başarılı olursa)

- [ ] Cache Helper'a yeni fonksiyon ekle:
  - `updatePlatformInCache($cacheKey, $platform, $data)` - Tek platform güncelle
  - `mergePlatformData($existing, $new)` - Platform verilerini merge et
  - `extendCacheExpiry($cacheKey, $additionalMinutes)` - TTL uzat

### 2.5 Platform Tracking Sistemi
- [ ] Her platform çağrısını track et
- [ ] `$requestedChannels` array'ine ekle:
  - `booking`, `hotels`, `etstur`, `sabeeapp`, `otelz`, `tatilsepeti`, `reseliva`
- [ ] Response'a kanal bilgisi ekle (debug için)
- [ ] Cache durumunu response'a ekle:
```json
{
  "cache_info": {
    "hit_type": "partial",  // "full", "partial", "miss"
    "cached_platforms": ["booking", "etstur", "sabeeapp"],
    "requested_platforms": ["hotels", "otelz"],
    "updated_platforms": ["hotels"]
  }
}
```

---

## 🎨 PHASE 3: Frontend - Dashboard Widgets

### 3.1 Dashboard Card Oluştur
- [ ] `/public/views/dashboard/index.php` dosyasını bul/güncelle
- [ ] Cache Statistics Card ekle:
```html
<!-- Cache Performance Card -->
<div class="col-lg-3 col-md-6">
    <div class="card stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted">Cache Hit Rate</h6>
                    <h3 class="mb-0" id="cacheHitRate">--%</h3>
                </div>
                <div class="stats-icon bg-success">
                    <i class="fas fa-bolt"></i>
                </div>
            </div>
            <small class="text-muted">Son 24 saat</small>
        </div>
    </div>
</div>

<!-- Total Requests Card -->
<div class="col-lg-3 col-md-6">
    <div class="card stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted">Total Requests</h6>
                    <h3 class="mb-0" id="totalRequests">--</h3>
                </div>
                <div class="stats-icon bg-info">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <small class="text-muted">Son 24 saat</small>
        </div>
    </div>
</div>

<!-- Most Used Channel Card -->
<div class="col-lg-3 col-md-6">
    <div class="card stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted">En Çok Kullanılan</h6>
                    <h3 class="mb-0" id="topChannel">--</h3>
                </div>
                <div class="stats-icon bg-warning">
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <small class="text-muted">Platform</small>
        </div>
    </div>
</div>
```

### 3.2 Cache Statistics Sayfası Oluştur
- [ ] `/public/views/statistics/cache.php` dosyası oluştur
- [ ] Sayfada gösterilecekler:
  1. **Özet Kartlar** (bugün):
     - Toplam istek
     - Cache hit rate
     - Ortalama response time
     - Cache miss sayısı
  
  2. **Grafikler**:
     - Cache hit/miss grafiği (son 7 gün)
     - Kanal kullanım dağılımı (pie chart)
     - Saatlik istek grafiği
     - Response time grafiği
  
  3. **Detaylı Tablo**:
     - Tarih/Saat
     - Widget Code
     - Cache Hit/Miss
     - Kullanılan Kanallar
     - Response Time
     - Filtreler: Tarih, Widget, Cache Hit/Miss

### 3.3 Menüye Ekle
- [ ] Ana menüye "Cache Statistics" link ekle
- [ ] Sidebar'a ekle (Statistics altında)

---

## 🔌 PHASE 4: API Endpoints

### 4.1 Statistics API Endpoints
- [ ] `GET /api/statistics/cache/summary` - Özet istatistikler
```json
{
  "today": {
    "total_requests": 1250,
    "cache_hits": 875,
    "cache_misses": 375,
    "cache_hit_rate": 70.0,
    "avg_response_time_ms": 145
  },
  "last_7_days": {...}
}
```

- [ ] `GET /api/statistics/cache/channels` - Kanal kullanımı
```json
{
  "channels": {
    "booking": 245,
    "etstur": 189,
    "sabeeapp": 167,
    ...
  }
}
```

- [ ] `GET /api/statistics/cache/history?start=...&end=...` - Tarih aralığı
- [ ] `GET /api/statistics/cache/widget/{code}` - Belirli widget

### 4.2 Cache Management Endpoints
- [ ] `POST /api/cache/clear` - Tüm cache temizle (admin only)
- [ ] `POST /api/cache/clear/{widgetCode}` - Belirli widget cache temizle
- [ ] `GET /api/cache/status` - Cache durumu

---

## 📊 PHASE 5: Background Jobs (Opsiyonel)

### 5.1 Cache Cleanup Job
- [ ] Expired cache'leri temizle (günlük)
- [ ] Cron job: `0 2 * * * php /path/cleanup_cache.php`

### 5.2 Statistics Aggregation Job
- [ ] Günlük özet istatistikleri hesapla
- [ ] `api_statistics_summary` tablosunu güncelle
- [ ] Cron job: `0 1 * * * php /path/aggregate_stats.php`

---

## ✅ PHASE 6: Testing & Optimization

### 6.1 Unit Tests
- [ ] Cache key generation testi
- [ ] Cache expiry testi
- [ ] Statistics logging testi

### 6.2 Integration Tests
- [ ] **Full cache hit scenario:** Tüm platformlar cache'de ve valid
- [ ] **Partial cache hit scenario:** Bazı platformlar failed/NA, sadece onlar request atılmalı
- [ ] **Cache miss scenario:** Hiç cache yok, tüm platformlara istek
- [ ] **Platform merge scenario:** Eski cache + yeni platform = merged cache
- [ ] **Multiple requests aynı anda:** Race condition testi

### 6.3 Performance Tests
- [ ] Load testing (1000 req/min)
- [ ] Cache hit rate measurement
- [ ] Database query optimization

### 6.4 Index Optimization
- [ ] Cache tablosu index'lerini kontrol et
- [ ] Statistics query performance ölç
- [ ] Slow query log analizi

---

## 🎯 PHASE 7: Documentation

### 7.1 Teknik Dokümantasyon
- [ ] Cache sistemi mimarisi
- [ ] API endpoint dokümantasyonu
- [ ] Database schema dokümantasyonu

### 7.2 Kullanıcı Dokümantasyonu
- [ ] Dashboard kullanım kılavuzu
- [ ] Cache temizleme prosedürü
- [ ] İstatistik raporları yorumlama

---

## 📝 Implementation Notes

### Cache Key Format
```
api_cache:{widgetCode}:{currency}:{checkin}:{checkout}:{adult}:{child}:{infant}
Örnek: api_cache:WIDGET123:EUR:2025-10-27:2025-10-28:2:0:0
```

### Channels Array Format
```json
{
  "channels_requested": [
    "booking",
    "etstur",
    "sabeeapp",
    "otelz"
  ],
  "channels_success": [
    "booking",
    "etstur",
    "sabeeapp"
  ],
  "channels_failed": [
    "otelz"
  ]
}
```

### Response Time Calculation
```php
$startTime = microtime(true);
// ... API processing ...
$endTime = microtime(true);
$responseTimeMs = round(($endTime - $startTime) * 1000);
```

### Partial Cache Hit Example (İNOVATİF!)

#### Scenario:
Cache'de 4 platformdan 2'si failed/NA, sadece onları yeniden dene.

#### Step 1: Cache'den Oku
```json
{
  "platforms": [
    {"name": "booking", "status": "success", "price": 4320},
    {"name": "etstur", "status": "failed", "price": "NA"},
    {"name": "sabeeapp", "status": "success", "price": 4500},
    {"name": "otelz", "status": "failed", "price": "NA"}
  ]
}
```

#### Step 2: Eksik Platformları Tespit Et
```php
$missingPlatforms = ['etstur', 'otelz']; // status=failed veya price=NA
```

#### Step 3: Sadece Eksik Platformlara İstek At
```php
// Etstur'a istek at
$etsturPrice = getEtsturPrice(...); // Success! 13800 TRY

// OtelZ'e istek at  
$otelzPrice = getOtelzPrice(...); // Yine failed :(
```

#### Step 4: Başarılı Olanları Cache'e Merge Et
```json
{
  "platforms": [
    {"name": "booking", "status": "success", "price": 4320},      // Cache'den
    {"name": "etstur", "status": "success", "price": 13800},      // YENİ - API'den
    {"name": "sabeeapp", "status": "success", "price": 4500},     // Cache'den
    {"name": "otelz", "status": "failed", "price": "NA"}          // Hala failed
  ],
  "cache_info": {
    "hit_type": "partial",
    "cached_platforms": ["booking", "sabeeapp"],
    "requested_platforms": ["etstur", "otelz"],
    "updated_platforms": ["etstur"],
    "failed_platforms": ["otelz"]
  }
}
```

#### Step 5: Statistics'e Kaydet
```sql
INSERT INTO api_statistics (
    widget_code, 
    cache_hit_type, 
    cached_platforms, 
    requested_platforms,
    updated_platforms
) VALUES (
    'WIDGET123',
    'partial',
    '["booking","sabeeapp"]',
    '["etstur","otelz"]',
    '["etstur"]'
);
```

#### Performans Kazancı:
- **Eski yöntem:** 4 platform = 4 API call = ~2000ms
- **Partial cache:** 2 cache hit + 2 API call = ~1000ms
- **⚡ %50 daha hızlı!**

---

## 🚀 Implementation Priority

### High Priority (İlk yapılacaklar) - ✅ TAMAMLANDI
1. ✅ Database tabloları oluştur (cache_hit_type ENUM field'ı ile)
2. ✅ Cache Helper class (updatePlatformInCache, mergePlatformData)
3. ✅ **Partial Cache Update Strategy** (İNOVATİF ÖZELLİK!)
   - ✅ Eksik platformları tespit et
   - ✅ Sadece onlara istek at
   - ✅ Cache'e merge et
4. ✅ ApiController cache entegrasyonu (full/partial/miss logic)
5. ✅ Statistics logging (cache_hit_type ile)

### Medium Priority - ✅ TAMAMLANDI
6. ✅ Statistics API endpoints (partial hit metriklerini dahil et)
7. ✅ Dashboard cards (Full/Partial/Miss breakdown)
8. ⏺️ Statistics sayfası (Partial hit grafiği ekle) - OPSIYONEL

### Low Priority (İsteğe bağlı)
8. ⏺️ Detaylı grafikler
9. ⏺️ Background jobs
10. ⏺️ Advanced analytics

---

## 📅 Estimated Timeline

- **PHASE 1 (Database):** 2 saat
  - Enhanced schema with cache_hit_type ENUM
  - Additional JSON fields for platform tracking
  
- **PHASE 2 (Backend):** 12 saat ⬆️ (+4 saat)
  - Basic cache system: 4 saat
  - **Partial cache update strategy:** 4 saat (İNOVATİF!)
  - ApiController integration: 3 saat
  - Statistics helper: 1 saat
  
- **PHASE 3 (Frontend):** 8 saat ⬆️ (+2 saat)
  - Dashboard cards with partial hit breakdown: 4 saat
  - Statistics page with partial hit charts: 4 saat
  
- **PHASE 4 (API):** 5 saat ⬆️ (+1 saat)
  - Enhanced endpoints with partial hit metrics: 5 saat
  
- **PHASE 5 (Jobs):** 3 saat
- **PHASE 6 (Testing):** 5 saat ⬆️ (+1 saat)
  - Partial cache update scenarios: +1 saat
  
- **PHASE 7 (Docs):** 2 saat

**Toplam:** ~37 saat (+8 saat for innovative partial cache feature)

---

## 🔍 Monitoring & Alerts

### Metrikler
- **Total cache hit rate (full + partial) < %60** ise uyarı
- **Partial hit rate > %30** ise uyarı (platform API'lerinde problem var demektir)
- **Platform success rate < %80** ise uyarı (o platformu kontrol et)
- Response time > 500ms ise uyarı
- Cache size > 10GB ise temizlik gerekli
- **Aynı platform 5+ kez üst üste failed** ise circuit breaker devreye gir

### Logging
- Cache hit/miss log level: DEBUG
- **Partial cache update log level: INFO** (hangi platformlar güncellendi)
- API errors log level: ERROR
- Statistics updates log level: INFO
- **Platform circuit breaker aktivasyonu: WARNING**

---

## ⚙️ Configuration

### Environment Variables
```env
# Cache Settings
CACHE_ENABLED=true
CACHE_DEFAULT_TTL=30  # dakika (fallback)
CACHE_MAX_SIZE=10GB
PARTIAL_CACHE_ENABLED=true  # Partial cache update özelliği
PARTIAL_CACHE_TTL_EXTENSION=10  # Partial update'te cache TTL'i kaç dakika uzatsın

# Platform Retry & Circuit Breaker
PLATFORM_MAX_RETRIES=3  # Bir platformu max kaç kez dene
PLATFORM_CIRCUIT_BREAKER_THRESHOLD=5  # Kaç failed sonrası circuit breaker
PLATFORM_CIRCUIT_BREAKER_TIMEOUT=3600  # Circuit breaker süresi (saniye)

# Statistics
STATS_ENABLED=true
STATS_RETENTION_DAYS=90

# Performance
CACHE_CLEANUP_INTERVAL=daily
STATS_AGGREGATION_INTERVAL=daily
PARTIAL_CACHE_LOCK_TIMEOUT=2  # Race condition için lock timeout (saniye)
```

---

## 🎓 Best Practices

1. **Cache Invalidation:** Widget settings değiştiğinde ilgili cache'i temizle
2. **Partial Cache Strategy:** 
   - Sadece failed/NA platformları yeniden dene
   - Başarılı olanları cache'e merge et
   - Failed kalanları olduğu gibi bırak (sonraki istekte tekrar dene)
   - Cache TTL'i partial update'te 5-10 dakika uzat
3. **Statistics:** Batch insert kullan (her request'te insert yerine queue'a ekle)
4. **Indexes:** Sık sorgulan alanlara index ekle
5. **Cleanup:** Old data için retention policy uygula (90 gün)
6. **Monitoring:** 
   - Cache hit rate (full + partial) sürekli izle
   - Partial hit rate yüksekse (>30%) → Platform API'lerinde problem var demektir
   - Platform bazlı success rate'i track et
7. **Performance:**
   - Partial update için max retry sayısı belirle (örn: 3 kez failed olursa artık deneme)
   - Çok sık fail olan platformları geçici disable et (circuit breaker pattern)

---

## 🐛 Known Issues & Solutions

### Issue 1: Cache Stampede
**Problem:** Aynı anda çok istek gelince cache expire olur, hepsi DB'ye gider
**Solution:** Mutex/Lock mechanism ile ilk isteği bekle

### Issue 2: Memory Overflow
**Problem:** Cache çok büyüyünce memory problemi
**Solution:** Max size limit + LRU eviction policy

### Issue 3: Stale Data
**Problem:** Hotel fiyatları değişti ama cache'de eski
**Solution:** TTL'i makul tut (30 dk) + manual invalidation endpoint

### Issue 4: Partial Cache Race Condition
**Problem:** İki request aynı anda gelir, ikisi de aynı eksik platformu request eder
**Solution:** 
- Lock mechanism (Redis SETNX veya DB row lock)
- İlk istek platformu güncelliyorsa, ikinci istek 100ms bekleyip cache'i yeniden kontrol etsin
- Timeout: 2 saniye (platformdan yanıt gelene kadar)

### Issue 5: Platform Always Failing
**Problem:** Bir platform sürekli failed dönüyor, her seferinde deneniyor
**Solution:**
- Platform failure count'u track et
- 5+ failed ise, o platform için 1 saat "circuit breaker" aç
- Circuit breaker açıkken o platformu skip et
- 1 saat sonra tekrar dene (half-open state)

---

Bu TODO list'i takip ederek tüm sistemi adım adım implement edebilirsiniz!
