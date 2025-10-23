# RateCare Background Jobs & Circuit Breaker

## 🎯 Genel Bakış

PHASE 5-6 implementasyonu: Otomatik bakım işleri ve platform koruma sistemi.

---

## 🔄 Background Jobs

### 1. **Statistics Aggregation** (`aggregate_statistics.php`)

**Çalışma Zamanı:** Her gece 02:00  
**Süre:** ~10-30 saniye

**Ne Yapar:**
- Dünkü tüm API isteklerini toplar
- `api_statistics_summary` tablosuna yazar
- Eski detaylı kayıtları siler (retention setting'e göre)
- Database'i optimize eder

**Faydası:**
```
Önce: 1,000,000 satır detaylı kayıt
Sonra: 30 gün summary = 30 satır
Performans: %99 daha az veri, %95 daha hızlı query
```

**Settings:**
- `cache-statistics-retention-days`: Detay kayıtlarını kaç gün sakla (default: 30)

---

### 2. **Expired Cache Cleanup** (`cleanup_expired_cache.php`)

**Çalışma Zamanı:** Her gece 03:00  
**Süre:** ~5-15 saniye

**Ne Yapar:**
- `expires_at < NOW()` olan cache kayıtlarını siler
- Cache tablosunu optimize eder

**Faydası:**
```
Cache tablosu: 10GB → 2GB
Query speed: 500ms → 50ms
```

**Settings:**
- `cache-cleanup-enabled`: Cleanup aktif mi? (default: 1)

---

### 3. **Cache Warming** (`warm_cache.php`)

**Çalışma Zamanı:** Her sabah 06:00  
**Süre:** ~1-3 dakika

**Ne Yapar:**
- Son 7 günün en popüler widget'larını bulur
- Her birine API isteği atar
- Cache'i önceden doldurur

**Faydası:**
```
İlk kullanıcı isteği: 2000ms → 5ms
Sabah trafiği: %90 daha hızlı
```

**Settings:**
- `cache-warming-enabled`: Warming aktif mi? (default: 0, manuel aktif et)
- `cache-warming-widget-count`: Kaç widget warm edilsin? (default: 10)

---

## ⚡ Circuit Breaker Pattern

### Nasıl Çalışır?

**3 Durum (State):**

1. **CLOSED** 🟢 (Normal)
   - Platform çalışıyor
   - İstekler normal atılıyor

2. **OPEN** 🔴 (Devre Dışı)
   - Platform X kez failed oldu
   - İstekler ATİLMIYOR (direkt skip)
   - Timeout süresince bekle

3. **HALF_OPEN** 🟡 (Test)
   - Timeout geçti
   - Test istekleri at
   - Başarılı → CLOSED
   - Başarısız → OPEN

### Örnek Senaryo:

```
09:00:00 - Booking.com request 1: ❌ FAILED
09:00:05 - Booking.com request 2: ❌ FAILED
09:00:10 - Booking.com request 3: ❌ FAILED
09:00:15 - Booking.com request 4: ❌ FAILED
09:00:20 - Booking.com request 5: ❌ FAILED

>> Circuit OPENED! 🚨

09:00:25 - Booking.com request 6: ⏭️ SKIPPED (circuit open)
09:00:30 - Booking.com request 7: ⏭️ SKIPPED
09:00:35 - Booking.com request 8: ⏭️ SKIPPED
...
09:10:20 - (10 dakika geçti) Circuit → HALF_OPEN 🟡

09:10:21 - Booking.com request: 🧪 TEST
09:10:22 - Result: ✅ SUCCESS

>> Circuit CLOSED! ✅ (Normal çalışmaya devam)
```

### Settings:

- `circuit-breaker-enabled`: Aktif mi? (default: 1)
- `circuit-breaker-failure-threshold`: Kaç fail sonrası aç? (default: 5)
- `circuit-breaker-timeout-seconds`: Kaç saniye bekle? (default: 600 = 10 dakika)
- `circuit-breaker-half-open-requests`: Kaç test isteği? (default: 3)
- `circuit-breaker-platforms`: Hangi platformlar? (default: tümü)

---

## 📊 Monitoring

### API Endpoints:

**Circuit Breaker Durumu:**
```bash
GET /api/v1/circuit-breaker/status
```

Response:
```json
{
  "status": "success",
  "data": {
    "total_platforms": 7,
    "closed": 6,
    "open": 1,
    "half_open": 0,
    "total_requests": 1250,
    "total_failures": 45,
    "platforms": [
      {
        "platform": "booking",
        "state": "open",
        "failure_count": 5,
        "success_rate": 96.2
      }
    ]
  }
}
```

**Circuit Reset:**
```bash
POST /api/v1/circuit-breaker/reset
{
  "platform": "booking"  // optional, tümü için boş bırak
}
```

---

## 🚀 Kurulum

### 1. Migration Çalıştır:

```bash
cd /var/www/html/ratecare_test
php run_phase5_migration.php
```

### 2. Cron Jobs Kur:

```bash
chmod +x setup_cron_jobs.sh
./setup_cron_jobs.sh
```

Veya manuel:
```bash
crontab -e
```

```cron
# RateCare Background Jobs
0 2 * * * php /var/www/html/ratecare_test/jobs/aggregate_statistics.php >> /var/www/html/ratecare_test/storage/logs/cron.log 2>&1
0 3 * * * php /var/www/html/ratecare_test/jobs/cleanup_expired_cache.php >> /var/www/html/ratecare_test/storage/logs/cron.log 2>&1
0 6 * * * php /var/www/html/ratecare_test/jobs/warm_cache.php >> /var/www/html/ratecare_test/storage/logs/cron.log 2>&1
```

### 3. Test Et:

```bash
# Test aggregation
php jobs/aggregate_statistics.php

# Test cleanup
php jobs/cleanup_expired_cache.php

# Test warming
php jobs/warm_cache.php

# Test circuit breaker
curl https://test.ratecare.net/api/v1/circuit-breaker/status
```

---

## 📁 Dosya Yapısı

```
ratecare/
├── jobs/
│   ├── aggregate_statistics.php    ← Daily aggregation
│   ├── cleanup_expired_cache.php   ← Cache cleanup
│   └── warm_cache.php              ← Cache warming
│
├── app/Helpers/
│   └── CircuitBreaker.php          ← Circuit breaker logic
│
├── database/migrations/
│   └── 002_add_cache_settings.sql  ← Settings + table
│
├── storage/logs/
│   ├── cron.log                    ← Cron job outputs
│   ├── aggregate.log               ← Aggregation logs
│   ├── cleanup.log                 ← Cleanup logs
│   ├── warming.log                 ← Warming logs
│   └── circuit-breaker.log         ← Circuit breaker events
│
├── run_phase5_migration.php        ← Run migration
└── setup_cron_jobs.sh              ← Setup cron
```

---

## 🎛️ Settings Yönetimi

Settings tablosundan kontrol edilebilir:

### Cache Settings:
```sql
UPDATE settings SET value = '60' WHERE `key` = 'cache-statistics-retention-days';
UPDATE settings SET value = '0' WHERE `key` = 'cache-cleanup-enabled';
UPDATE settings SET value = '1' WHERE `key` = 'cache-warming-enabled';
UPDATE settings SET value = '20' WHERE `key` = 'cache-warming-widget-count';
```

### Circuit Breaker Settings:
```sql
UPDATE settings SET value = '0' WHERE `key` = 'circuit-breaker-enabled';
UPDATE settings SET value = '3' WHERE `key` = 'circuit-breaker-failure-threshold';
UPDATE settings SET value = '300' WHERE `key` = 'circuit-breaker-timeout-seconds';
```

---

## 📈 Beklenen Performans Kazançları

| Özellik | Önce | Sonra | Kazanç |
|---------|------|-------|--------|
| Database Boyutu | 10GB | 2GB | %80 ↓ |
| Query Hızı | 500ms | 50ms | %90 ↑ |
| Failed Platform Timeout | 5s | 0s (skip) | %100 ↑ |
| Sabah İlk İstek | 2s | 5ms | %99 ↑ |
| Sunucu CPU | %80 | %40 | %50 ↓ |

---

## 🔍 Troubleshooting

### Cron Jobs Çalışmıyor?

```bash
# Crontab kontrolü
crontab -l

# Log kontrolü
tail -f storage/logs/cron.log

# Manuel test
php jobs/aggregate_statistics.php
```

### Circuit Breaker Açılmıyor?

```bash
# Durumu kontrol et
curl https://test.ratecare.net/api/v1/circuit-breaker/status

# Log kontrolü
tail -f storage/logs/circuit-breaker.log

# Manuel reset
curl -X POST https://test.ratecare.net/api/v1/circuit-breaker/reset
```

### Cache Warming Çalışmıyor?

```bash
# Settings kontrolü
SELECT * FROM settings WHERE `key` = 'cache-warming-enabled';

# Manuel çalıştır
php jobs/warm_cache.php

# Log kontrolü
tail -f storage/logs/warming.log
```

---

## ✅ Sistem Hazır!

**Background jobs ve circuit breaker başarıyla kuruldu!** 🎉

**Monitoring:**
- Dashboard: https://test.ratecare.net/admin/dashboard
- Statistics: https://test.ratecare.net/admin/cache/statistics
- Circuit Breaker API: https://test.ratecare.net/api/v1/circuit-breaker/status

**Loglar:**
- `storage/logs/cron.log` - Cron outputs
- `storage/logs/circuit-breaker.log` - Circuit events
- `storage/logs/aggregate.log` - Aggregation
- `storage/logs/cleanup.log` - Cleanup
- `storage/logs/warming.log` - Warming
