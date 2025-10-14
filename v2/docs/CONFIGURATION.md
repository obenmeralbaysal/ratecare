# Hotel DigiLab Configuration Guide

## Overview

This guide covers all configuration options available in Hotel DigiLab v2. Configuration is managed through environment variables (.env file) and configuration files in the `config/` directory.

## Environment Configuration (.env)

### Application Settings

```env
# Application Identity
APP_NAME="Hotel DigiLab"
APP_ENV=production                    # development, testing, production
APP_DEBUG=false                       # true for development only
APP_URL=https://your-domain.com
APP_TIMEZONE=UTC                      # PHP timezone identifier

# Security
APP_KEY=your-32-character-secret-key  # Generate with: openssl rand -hex 32
HASH_ALGO=sha256                      # Hashing algorithm
SESSION_LIFETIME=120                  # Session timeout in minutes
CSRF_TOKEN_NAME=_token               # CSRF token field name
```

### Database Configuration

```env
# Primary Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hotel_digilab
DB_USERNAME=hoteldigilab_user
DB_PASSWORD=secure_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Connection Pool Settings
DB_MAX_CONNECTIONS=100
DB_TIMEOUT=30
DB_RETRY_ATTEMPTS=3
```

### Cache Configuration

```env
# Cache Driver Options: file, redis, memcached, array
CACHE_DRIVER=file
CACHE_PREFIX=hotel_digilab_
CACHE_DEFAULT_TTL=3600               # Default TTL in seconds

# Redis Configuration (if using Redis)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0

# Memcached Configuration (if using Memcached)
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
```

### Mail Configuration

```env
# Mail Driver Options: mail, smtp, sendmail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls                  # tls, ssl, or null
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Hotel DigiLab"

# SMTP Authentication
MAIL_AUTH=true
MAIL_TIMEOUT=30
```

### File Upload Configuration

```env
# Upload Settings
UPLOAD_MAX_SIZE=10485760             # 10MB in bytes
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx
UPLOAD_PATH=storage/uploads
UPLOAD_PUBLIC_PATH=public/uploads

# Image Processing
IMAGE_MAX_WIDTH=1920
IMAGE_MAX_HEIGHT=1080
IMAGE_QUALITY=85
IMAGE_WATERMARK_ENABLED=false
```

### API Configuration

```env
# API Settings
API_RATE_LIMIT=60                    # Requests per minute
API_RATE_LIMIT_WINDOW=60            # Window in seconds
API_VERSION=v1
API_TIMEOUT=30

# API Authentication
API_TOKEN_EXPIRY=3600               # Token expiry in seconds
API_REFRESH_TOKEN_EXPIRY=604800     # Refresh token expiry (7 days)
```

### Widget Configuration

```env
# Widget Settings
WIDGET_CACHE_TTL=1800               # Widget cache TTL in seconds
WIDGET_TRACKING_ENABLED=true
WIDGET_DEFAULT_THEME=default
WIDGET_MAX_PER_USER=50

# Widget Security
WIDGET_ALLOWED_DOMAINS=*            # Comma-separated domains or * for all
WIDGET_IFRAME_SANDBOX=true
```

### External Services

```env
# Booking APIs
BOOKING_API_URL=https://api.booking.com
BOOKING_API_KEY=your_booking_api_key
EXPEDIA_API_URL=https://api.expedia.com
EXPEDIA_API_KEY=your_expedia_api_key

# Rate Update Settings
RATE_UPDATE_INTERVAL=3600           # Update interval in seconds
RATE_UPDATE_TIMEOUT=120             # Timeout for rate updates
RATE_COMPARISON_ENABLED=true
```

### Logging Configuration

```env
# Logging Settings
LOG_LEVEL=info                      # debug, info, warning, error, critical
LOG_FILE=storage/logs/app.log
LOG_MAX_FILES=30
LOG_MAX_SIZE=10485760              # 10MB

# Specific Log Channels
LOG_API_ENABLED=true
LOG_SECURITY_ENABLED=true
LOG_PERFORMANCE_ENABLED=false
```

### Performance Settings

```env
# Performance Options
ENABLE_QUERY_LOG=false              # Enable for debugging only
ENABLE_PROFILING=false              # Enable for development only
MEMORY_LIMIT=256M
MAX_EXECUTION_TIME=300

# Optimization
ENABLE_GZIP=true
ENABLE_BROWSER_CACHE=true
STATIC_CACHE_TTL=2592000           # 30 days
```

### Security Settings

```env
# Security Options
SECURITY_HEADERS_ENABLED=true
XSS_PROTECTION_ENABLED=true
SQL_INJECTION_PROTECTION_ENABLED=true
RATE_LIMITING_ENABLED=true

# Content Security Policy
CSP_ENABLED=true
CSP_REPORT_ONLY=false
CSP_REPORT_URI=/security/csp-report
```

### Maintenance Mode

```env
# Maintenance
MAINTENANCE_MODE=false
MAINTENANCE_MESSAGE="System is under maintenance. Please try again later."
MAINTENANCE_ALLOWED_IPS=127.0.0.1,::1
MAINTENANCE_RETRY_AFTER=3600        # Retry-After header in seconds
```

### Localization

```env
# Language Settings
DEFAULT_LOCALE=en
FALLBACK_LOCALE=en
AVAILABLE_LOCALES=en,tr,de,fr,es
AUTO_DETECT_LOCALE=true
```

## Configuration Files

### Application Config (config/app.php)

```php
<?php
return [
    'name' => env('APP_NAME', 'Hotel DigiLab'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    
    // Custom application settings
    'features' => [
        'widget_analytics' => true,
        'rate_comparison' => true,
        'multi_language' => true,
        'api_access' => true,
    ],
    
    'limits' => [
        'max_hotels_per_user' => 10,
        'max_widgets_per_hotel' => 20,
        'max_api_requests_per_minute' => 60,
    ]
];
```

### Database Config (config/database.php)

```php
<?php
return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ]
    ],
    
    'query_log' => env('ENABLE_QUERY_LOG', false),
    'slow_query_threshold' => 1000, // milliseconds
];
```

### Mail Config (config/mail.php)

```php
<?php
return [
    'default' => env('MAIL_DRIVER', 'mail'),
    
    'drivers' => [
        'smtp' => [
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'auth' => env('MAIL_AUTH', true),
            'timeout' => env('MAIL_TIMEOUT', 30),
        ],
        
        'mail' => [
            'sendmail_path' => '/usr/sbin/sendmail -bs',
        ]
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME'),
    ],
    
    'templates' => [
        'welcome' => 'emails/welcome',
        'password_reset' => 'emails/password_reset',
        'widget_report' => 'emails/widget_report',
    ]
];
```

### Cache Config (config/cache.php)

```php
<?php
return [
    'default' => env('CACHE_DRIVER', 'file'),
    
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/cache',
            'prefix' => env('CACHE_PREFIX', 'hotel_digilab_'),
        ],
        
        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => env('REDIS_DATABASE', 0),
            'prefix' => env('CACHE_PREFIX', 'hotel_digilab_'),
        ]
    ],
    
    'ttl' => [
        'default' => env('CACHE_DEFAULT_TTL', 3600),
        'widgets' => env('WIDGET_CACHE_TTL', 1800),
        'rates' => 900, // 15 minutes
        'hotels' => 7200, // 2 hours
    ]
];
```

## Environment-Specific Configurations

### Development Environment

```env
# .env.development
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Use local database
DB_HOST=localhost
DB_DATABASE=hotel_digilab_dev

# File cache for development
CACHE_DRIVER=file

# Log everything in development
LOG_LEVEL=debug
ENABLE_QUERY_LOG=true
ENABLE_PROFILING=true

# Disable security features for easier testing
RATE_LIMITING_ENABLED=false
MAINTENANCE_MODE=false
```

### Testing Environment

```env
# .env.testing
APP_ENV=testing
APP_DEBUG=true
APP_URL=http://localhost

# Use test database
DB_DATABASE=hotel_digilab_test

# Use array cache (in-memory)
CACHE_DRIVER=array

# Disable external services
MAIL_DRIVER=log
RATE_UPDATE_INTERVAL=0
WIDGET_TRACKING_ENABLED=false
```

### Production Environment

```env
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Production database with connection pooling
DB_HOST=db.your-domain.com
DB_MAX_CONNECTIONS=100

# Redis for production caching
CACHE_DRIVER=redis
REDIS_HOST=redis.your-domain.com

# SMTP for production emails
MAIL_DRIVER=smtp
MAIL_HOST=smtp.your-domain.com

# Enable all security features
SECURITY_HEADERS_ENABLED=true
RATE_LIMITING_ENABLED=true
XSS_PROTECTION_ENABLED=true
SQL_INJECTION_PROTECTION_ENABLED=true

# Production logging
LOG_LEVEL=warning
LOG_MAX_FILES=90
```

## Advanced Configuration

### Custom Configuration Files

Create custom configuration files in the `config/` directory:

```php
// config/custom.php
<?php
return [
    'feature_flags' => [
        'new_dashboard' => env('FEATURE_NEW_DASHBOARD', false),
        'advanced_analytics' => env('FEATURE_ADVANCED_ANALYTICS', true),
        'beta_api' => env('FEATURE_BETA_API', false),
    ],
    
    'integrations' => [
        'google_analytics' => [
            'enabled' => env('GA_ENABLED', false),
            'tracking_id' => env('GA_TRACKING_ID'),
        ],
        
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
        ]
    ]
];
```

### Dynamic Configuration

Load configuration dynamically based on conditions:

```php
// config/dynamic.php
<?php
$config = [
    'base_setting' => 'default_value'
];

// Override based on environment
if (env('APP_ENV') === 'production') {
    $config['performance_mode'] = true;
    $config['debug_toolbar'] = false;
} else {
    $config['performance_mode'] = false;
    $config['debug_toolbar'] = true;
}

// Override based on server
if (gethostname() === 'server1') {
    $config['special_feature'] = true;
}

return $config;
```

## Configuration Validation

### Environment Validation Script

```php
// scripts/validate_config.php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$requiredVars = [
    'APP_NAME',
    'APP_ENV',
    'APP_URL',
    'APP_KEY',
    'DB_HOST',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD'
];

$errors = [];

foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        $errors[] = "Missing required environment variable: {$var}";
    }
}

// Validate APP_KEY length
if (strlen($_ENV['APP_KEY'] ?? '') < 32) {
    $errors[] = "APP_KEY must be at least 32 characters long";
}

// Validate database connection
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    echo "✓ Database connection successful\n";
} catch (PDOException $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

if (empty($errors)) {
    echo "✓ All configuration checks passed\n";
    exit(0);
} else {
    echo "✗ Configuration errors found:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    exit(1);
}
```

## Configuration Best Practices

### 1. Security
- Never commit `.env` files to version control
- Use strong, unique keys for production
- Rotate secrets regularly
- Use environment-specific configurations

### 2. Performance
- Use Redis/Memcached for production caching
- Enable OPcache in production
- Set appropriate cache TTL values
- Monitor and tune database settings

### 3. Monitoring
- Enable appropriate logging levels
- Set up log rotation
- Monitor disk space for logs
- Use centralized logging in production

### 4. Backup
- Backup configuration files
- Document custom configurations
- Test configuration changes in staging
- Have rollback procedures ready

## Troubleshooting Configuration Issues

### Common Problems

1. **Database Connection Issues**
   ```bash
   # Test database connection
   php scripts/validate_config.php
   ```

2. **Cache Issues**
   ```bash
   # Clear cache
   php scripts/clear_cache.php
   ```

3. **Permission Issues**
   ```bash
   # Fix file permissions
   chmod 600 .env
   chmod -R 777 storage/
   ```

4. **Mail Configuration Issues**
   ```bash
   # Test mail configuration
   php scripts/test_mail.php
   ```

### Configuration Debugging

Enable debug mode temporarily to diagnose issues:

```env
APP_DEBUG=true
LOG_LEVEL=debug
ENABLE_QUERY_LOG=true
```

Remember to disable debug mode in production!

## Support

For configuration support:
- Documentation: https://docs.hoteldigilab.com/configuration
- Email: support@hoteldigilab.com
- Community Forum: https://community.hoteldigilab.com
