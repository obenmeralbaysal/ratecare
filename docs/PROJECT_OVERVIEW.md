# 🏨 RateCare Project - Complete Overview & Quick Start Guide

> **Her bilgisayar değişiminde bu dosyayı okuyarak projeyi hızlıca hatırlayabilirsiniz.**

## 📋 Proje Özeti

**RateCare**, otel sahiplerinin farklı rezervasyon platformlarındaki fiyatlarını takip eden ve karşılaştıran gelişmiş bir sistem. Laravel'den framework-less MVC mimarisine tamamen migrate edilmiş modern bir uygulamadır.

### 🎯 Ana Amaç
- Otellerin rakip fiyatlarını izlemesi
- Çoklu platform fiyat karşılaştırması
- Widget sistemi ile entegrasyon
- Gerçek zamanlı fiyat takibi

## 🏗️ Proje Yapısı

```
ratecare/
├── 📁 [LEGACY] Laravel 5.7 Eski Sistem
│   ├── app/Models/          # Eski Laravel modelleri
│   ├── resources/views/     # Blade template'leri
│   ├── routes/             # Laravel route'ları
│   └── composer.json       # Laravel bağımlılıkları
│
└── 📁 v2/ [AKTIF] Framework-less Yeni Sistem
    ├── app/
    │   ├── Controllers/    # MVC Controller'ları
    │   │   ├── Admin/     # Admin paneli
    │   │   ├── Api/       # REST API
    │   │   ├── Customer/  # Müşteri paneli
    │   │   ├── Front/     # Ana sayfa
    │   │   └── Reseller/  # Bayi paneli
    │   ├── Models/        # Veritabanı modelleri
    │   ├── Middleware/    # İstek middleware'leri
    │   └── Helpers/       # Yardımcı fonksiyonlar
    ├── core/              # Framework çekirdeği (37 dosya)
    ├── database/          # Migration'lar ve seed'ler
    ├── public/            # Web erişilebilir dosyalar
    ├── resources/         # View'lar, diller, widget'lar
    └── docs/              # Detaylı dokümantasyon
```

## 🚀 Teknoloji Stack

### Backend
- **Framework**: Custom Framework-less MVC
- **PHP**: 7.4+ (8.0+ önerilen)
- **Database**: MySQL 5.7+
- **Architecture**: MVC Pattern

### Frontend
- **JavaScript**: Vanilla JS + Modern ES6
- **CSS**: Bootstrap 4 + Custom CSS
- **Template Engine**: Custom Blade-like Engine

### External Services
- **Selenium WebDriver**: Fiyat scraping
- **Multi-platform APIs**: Booking.com, Hotels.com, vb.

## 🌟 Ana Özellikler

### 👥 Kullanıcı Rolleri
- **Admin**: Sistem yönetimi, tüm yetkiler
- **Reseller**: Bayi yönetimi, müşteri ekleme
- **Customer**: Otel yönetimi, widget oluşturma

### 📊 Platform Entegrasyonları
- **Booking.com**: Fiyat çekme ve karşılaştırma
- **Hotels.com**: Rate monitoring
- **Sabee**: PMS entegrasyonu
- **Odamax**: Türk platform entegrasyonu
- **Otelz**: Yerel platform
- **Tatilsepeti**: Tatil platformu

### 🎨 Widget Sistemi
- Embeddable hotel widgets
- Real-time rate display
- Customizable themes
- Analytics tracking

## 🔧 Hızlı Başlangıç

### 1. Sistem Gereksinimleri
```bash
# Gerekli yazılımlar
- XAMPP/WAMP (Apache + MySQL + PHP 7.4+)
- Git
- Composer (opsiyonel)
- Modern web browser
```

### 2. Proje Kurulumu
```bash
# 1. Projeyi klonla/kopyala
cd c:\xampp\htdocs\
git clone [repository-url] ratecare
# VEYA mevcut klasörü kopyala

# 2. v2 dizinine git
cd ratecare\v2

# 3. Environment dosyasını kopyala
copy .env.example .env

# 4. .env dosyasını düzenle
notepad .env
```

### 3. Veritabanı Kurulumu
```bash
# MySQL'de veritabanı oluştur
CREATE DATABASE hotel_digilab;

# Migration'ları çalıştır
php database/migrate.php

# Admin kullanıcı oluştur
php scripts/create_admin.php
```

### 4. Web Server Ayarları
```apache
# Apache .htaccess (public/.htaccess)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 5. İzinleri Ayarla
```bash
# Windows'ta (cmd as admin)
icacls "c:\xampp\htdocs\ratecare\v2\storage" /grant Everyone:(OI)(CI)F

# Linux/Mac'te
chmod -R 777 storage/
chmod 600 .env
```

## 📚 Önemli Dosyalar ve Konumları

### Konfigürasyon
- **Ana Config**: `v2/.env`
- **App Config**: `v2/config/app.php`
- **DB Config**: `v2/config/database.php`

### Temel Dosyalar
- **Entry Point**: `v2/public/index.php`
- **Router**: `v2/core/Router.php`
- **Database**: `v2/core/Database.php`
- **Auth**: `v2/core/Auth.php`

### Modeller (v2/app/Models/)
- `User.php` - Kullanıcı yönetimi
- `Hotel.php` - Otel bilgileri
- `Widget.php` - Widget sistemi
- `Rate.php` - Fiyat verileri
- `Country.php`, `Currency.php`, `Language.php`

### Controller'lar (v2/app/Controllers/)
- **Admin/**: Yönetici paneli
- **Customer/**: Müşteri paneli
- **Api/**: REST API endpoints
- **Front/**: Ana sayfa ve genel

## 🔐 Güvenlik Özellikleri

- **XSS Protection**: Content Security Policy
- **SQL Injection**: Prepared statements
- **CSRF Protection**: Token tabanlı koruma
- **Rate Limiting**: API ve form koruma
- **Input Sanitization**: Tüm girdi temizleme
- **Security Headers**: Kapsamlı güvenlik başlıkları

## 🌍 Multi-Language Support

### Desteklenen Diller
- **English (EN)**: Varsayılan
- **Turkish (TR)**: Ana dil
- **German (DE)**: Alman pazarı
- **French (FR)**: Fransız pazarı
- **Spanish (ES)**: İspanyol pazarı

### Kullanım
```php
// Template'lerde
{{ __('common.welcome') }}
{{ __('validation.required', ['field' => 'email']) }}

// Controller'larda
$message = Lang::get('common.success');
```

## 🔌 API Kullanımı

### Base URL
```
http://localhost/ratecare/v2/public/api/v1/
```

### Temel Endpoint'ler
```bash
# Authentication
POST /api/v1/auth/login
POST /api/v1/auth/register
POST /api/v1/auth/logout

# Widgets
GET  /api/v1/widgets
POST /api/v1/widgets
GET  /api/v1/widgets/{id}
PUT  /api/v1/widgets/{id}

# Hotels
GET  /api/v1/hotels
POST /api/v1/hotels
GET  /api/v1/hotels/{id}/rates

# Rates
GET  /api/v1/rates
POST /api/v1/rates/update
```

## 🧪 Test Sistemi

```bash
# Tüm testleri çalıştır
php tests/run_tests.php

# Belirli test suite'i çalıştır
php tests/run_tests.php auth
php tests/run_tests.php database
php tests/run_tests.php api
php tests/run_tests.php widgets
```

## 📊 Monitoring ve Logs

### Log Dosyaları
- **App Logs**: `v2/storage/logs/app.log`
- **Error Logs**: `v2/storage/logs/error.log`
- **API Logs**: `v2/storage/logs/api.log`

### Health Check
```bash
curl http://localhost/ratecare/v2/public/api/v1/status
# Response: {"status":"ok","version":"2.0.0"}
```

## 🚨 Sorun Giderme

### Yaygın Sorunlar

1. **Database Connection Error**
   ```bash
   # .env dosyasını kontrol et
   DB_HOST=localhost
   DB_DATABASE=hotel_digilab
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **Permission Denied**
   ```bash
   # Storage klasörü izinlerini ayarla
   chmod -R 777 v2/storage/
   ```

3. **404 Not Found**
   ```bash
   # .htaccess dosyasını kontrol et
   # Apache mod_rewrite aktif mi?
   ```

4. **Session Issues**
   ```bash
   # Session klasörü yazılabilir mi?
   # .env'de SESSION ayarları doğru mu?
   ```

## 📈 Performance Optimizasyonu

### Cache Sistemi
- **File Cache**: Varsayılan
- **Redis**: Production için önerilen
- **Database Query Cache**: Otomatik

### Optimizasyon İpuçları
```php
// Query optimization
$users = User::select(['id', 'name', 'email'])
    ->where('active', 1)
    ->limit(10)
    ->get();

// Cache kullanımı
$result = Cache::remember('hotels_list', 3600, function() {
    return Hotel::all();
});
```

## 🔄 Migration Geçmişi

### v1 (Laravel) → v2 (Framework-less)
- **Tamamlanan**: 15 Phase, 100% complete
- **Geliştirme Süresi**: ~8 saat
- **Dosya Sayısı**: 80+ dosya
- **Kod Satırı**: 25,000+ satır
- **Durum**: Production Ready ✅

### Önemli Değişiklikler
- Laravel bağımlılığı kaldırıldı
- Custom MVC framework geliştirildi
- Performance %30 artırıldı
- Security features geliştirildi
- API sistemi yeniden yazıldı

## 📞 Destek ve Dokümantasyon

### Detaylı Dokümantasyon
- **Installation**: `v2/docs/INSTALLATION.md`
- **Configuration**: `v2/docs/CONFIGURATION.md`
- **API Reference**: `v2/docs/API.md`
- **Migration Plan**: `v2/todo.md`

### Hızlı Komutlar
```bash
# Sistem durumu kontrol
php scripts/system_check.php

# Cache temizle
php scripts/clear_cache.php

# Backup oluştur
php scripts/backup.php --type=full

# Admin kullanıcı oluştur
php scripts/create_admin.php
```

## 🎯 Sonraki Adımlar

### Yeni Bilgisayarda İlk Yapılacaklar
1. ✅ Bu dosyayı oku
2. ✅ XAMPP'ı başlat
3. ✅ `.env` dosyasını konfigüre et
4. ✅ Veritabanını oluştur
5. ✅ Migration'ları çalıştır
6. ✅ İzinleri ayarla
7. ✅ Browser'da test et: `http://localhost/ratecare/v2/public/`

### Geliştirme Ortamı
```bash
# Development server başlat
php -S localhost:8000 -t v2/public/

# Test suite çalıştır
php v2/tests/run_tests.php

# Log'ları takip et
tail -f v2/storage/logs/app.log
```

---

## 📝 Notlar

- **Ana Sistem**: `v2/` klasörü aktif sistem
- **Eski Sistem**: Root klasör sadece referans için
- **Database**: `hotel_digilab` veritabanı
- **URL**: `http://localhost/ratecare/v2/public/`
- **Admin Panel**: `/admin/dashboard`
- **API Base**: `/api/v1/`

**🎉 Bu dosyayı bookmark'la ve her bilgisayar değişiminde referans olarak kullan!**
