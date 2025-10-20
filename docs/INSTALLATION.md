# Hotel DigiLab Installation Guide

## System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher (8.0+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum (1GB+ recommended)
- **Storage**: 1GB free disk space

### PHP Extensions Required
- `pdo_mysql`
- `mbstring`
- `json`
- `curl`
- `gd` or `imagick`
- `zip`
- `xml`
- `openssl`

### Optional Extensions
- `redis` (for Redis caching)
- `memcached` (for Memcached support)
- `opcache` (for performance)

## Installation Methods

### Method 1: Manual Installation

#### Step 1: Download and Extract
```bash
# Download the latest release
wget https://github.com/hoteldigilab/v2/archive/main.zip

# Extract files
unzip main.zip
mv hoteldigilab-v2-main /var/www/html/hoteldigilab
cd /var/www/html/hoteldigilab
```

#### Step 2: Set Permissions
```bash
# Set proper permissions
chmod -R 755 .
chmod -R 777 storage/
chmod 600 .env.example

# Set ownership (adjust user/group as needed)
chown -R www-data:www-data .
```

#### Step 3: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env

# Edit configuration
nano .env
```

#### Step 4: Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE hotel_digilab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hoteldigilab'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON hotel_digilab.* TO 'hoteldigilab'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php database/migrate.php
```

#### Step 5: Web Server Configuration

**Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/hoteldigilab/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Hide sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
}
```

### Method 2: Docker Installation

#### Step 1: Docker Compose Setup
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: hotel_digilab
      MYSQL_USER: hoteldigilab
      MYSQL_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

#### Step 2: Dockerfile
```dockerfile
FROM php:8.0-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/
COPY .env.example /var/www/html/.env

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage

EXPOSE 80
```

#### Step 3: Run Docker
```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php database/migrate.php

# Create admin user
docker-compose exec app php scripts/create_admin.php
```

## Configuration

### Environment Variables (.env)

```env
# Application
APP_NAME="Hotel DigiLab"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=your-generated-32-character-key

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hotel_digilab
DB_USERNAME=hoteldigilab
DB_PASSWORD=secure_password

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Hotel DigiLab"

# Cache
CACHE_DRIVER=file
CACHE_PREFIX=hotel_digilab

# API
API_RATE_LIMIT=60
API_VERSION=v1

# Security
SESSION_LIFETIME=120
CSRF_TOKEN_NAME=_token
```

### Database Migration

```bash
# Run all migrations
php database/migrate.php

# Run specific migration
php database/migrate.php --file=001_create_users_table.sql

# Rollback last migration
php database/migrate.php --rollback

# Check migration status
php database/migrate.php --status
```

### Create Admin User

```bash
# Interactive admin creation
php scripts/create_admin.php

# Or with parameters
php scripts/create_admin.php \
  --name="Admin User" \
  --email="admin@your-domain.com" \
  --password="secure_password"
```

## Post-Installation Setup

### 1. SSL Certificate (Production)
```bash
# Using Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

### 2. Cron Jobs
```bash
# Edit crontab
crontab -e

# Add these lines
# Rate updates every hour
0 * * * * /usr/bin/php /var/www/html/hoteldigilab/scripts/update_rates.php

# Log cleanup daily
0 2 * * * /usr/bin/php /var/www/html/hoteldigilab/scripts/cleanup_logs.php

# Cache cleanup weekly
0 3 * * 0 /usr/bin/php /var/www/html/hoteldigilab/scripts/clear_cache.php
```

### 3. Log Rotation
```bash
# Create logrotate config
sudo nano /etc/logrotate.d/hoteldigilab

# Add configuration
/var/www/html/hoteldigilab/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    copytruncate
}
```

### 4. Monitoring Setup
```bash
# Install monitoring tools
sudo apt install htop iotop

# Setup log monitoring
tail -f storage/logs/app.log
tail -f storage/logs/error.log
```

## Verification

### 1. System Check
```bash
# Run system diagnostics
php scripts/system_check.php
```

### 2. Test API
```bash
# Test API endpoint
curl -X GET "http://your-domain.com/api/v1/status"

# Expected response:
# {"status":"ok","version":"1.0.0","timestamp":1640995200}
```

### 3. Test Widget Rendering
```bash
# Test widget endpoint
curl -X GET "http://your-domain.com/api/v1/widgets/1/render"
```

### 4. Run Tests
```bash
# Run all tests
php tests/run_tests.php

# Run specific test suite
php tests/run_tests.php auth
php tests/run_tests.php database
php tests/run_tests.php api
```

## Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html/hoteldigilab
sudo chmod -R 755 /var/www/html/hoteldigilab
sudo chmod -R 777 /var/www/html/hoteldigilab/storage
```

#### 2. Database Connection Issues
```bash
# Test database connection
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=hotel_digilab', 'username', 'password');
echo 'Database connection successful';
"
```

#### 3. Apache/Nginx Issues
```bash
# Check Apache status
sudo systemctl status apache2
sudo apache2ctl configtest

# Check Nginx status
sudo systemctl status nginx
sudo nginx -t
```

#### 4. PHP Issues
```bash
# Check PHP version and extensions
php -v
php -m | grep -E "(pdo_mysql|mbstring|json|curl|gd)"

# Check PHP-FPM (if using Nginx)
sudo systemctl status php8.0-fpm
```

### Log Files
- Application logs: `storage/logs/app.log`
- Error logs: `storage/logs/error.log`
- Security logs: `storage/logs/security.log`
- API logs: `storage/logs/api.log`

### Performance Optimization

#### 1. Enable OPcache
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

#### 2. Configure MySQL
```ini
; my.cnf
[mysqld]
innodb_buffer_pool_size=1G
innodb_log_file_size=256M
query_cache_size=64M
query_cache_type=1
```

#### 3. Enable Gzip Compression
```apache
# Apache
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
</Location>
```

```nginx
# Nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript;
```

## Security Checklist

- [ ] Change default passwords
- [ ] Enable HTTPS/SSL
- [ ] Configure security headers
- [ ] Set proper file permissions
- [ ] Enable firewall
- [ ] Configure fail2ban
- [ ] Regular security updates
- [ ] Monitor logs
- [ ] Backup database regularly
- [ ] Test disaster recovery

## Support

For installation support:
- Documentation: https://docs.hoteldigilab.com
- Email: support@hoteldigilab.com
- Community: https://community.hoteldigilab.com
