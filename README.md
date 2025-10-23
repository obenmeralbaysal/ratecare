# Hotel DigiLab v2 🏨

A modern, framework-less hotel management and widget system for rate comparison and booking integrations.

## 🚀 Features

- **Multi-Role System**: Admin, Reseller, Customer roles with granular permissions
- **Widget System**: Embeddable hotel search, rates, and booking widgets
- **Rate Comparison**: Real-time rate comparison across multiple booking platforms
- **RESTful API**: Comprehensive API for external integrations
- **Multi-Language**: Support for multiple languages (EN, TR, DE, FR, ES)
- **Security First**: Built-in XSS, SQL injection protection, rate limiting
- **Performance Optimized**: Query optimization, caching, CDN ready
- **Mobile Responsive**: Modern, mobile-first UI design

## 📋 Requirements

- **PHP**: 7.4+ (8.0+ recommended)
- **MySQL**: 5.7+ (8.0+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum (1GB+ recommended)
- **Storage**: 1GB free disk space

## 🔧 Quick Start

### 1. Installation

```bash
# Clone repository
git clone https://github.com/hoteldigilab/v2.git
cd v2

# Copy environment file
cp .env.example .env

# Configure database and settings
nano .env

# Run database migrations
php database/migrate.php

# Create admin user
php scripts/create_admin.php
```

### 2. Web Server Configuration

**Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 3. Set Permissions

```bash
chmod -R 755 .
chmod -R 777 storage/
chmod 600 .env
chown -R www-data:www-data .
```

## 📚 Documentation

- [Installation Guide](docs/INSTALLATION.md) - Complete installation instructions
- [Configuration Guide](docs/CONFIGURATION.md) - Environment and configuration options
- [API Documentation](docs/API.md) - RESTful API reference
- [Deployment Guide](scripts/deploy.sh) - Production deployment scripts

## 🏗️ Architecture

### Directory Structure
```
v2/
├── app/
│   ├── Controllers/     # MVC Controllers (Admin, API, Customer, Front, Reseller)
│   ├── Models/         # Database Models (User, Hotel, Widget, Rate, etc.)
│   ├── Middleware/     # Request middleware
│   ├── Libraries/      # Custom libraries
│   └── Helpers/        # Helper functions
├── config/             # Configuration files
├── core/               # Core framework classes
├── database/           # Database migrations and seeds
├── docs/              # Documentation
├── public/            # Web accessible files
├── resources/         # Views, languages, widgets
├── routes/            # Route definitions
├── scripts/           # CLI scripts and utilities
├── storage/           # Logs, cache, uploads
└── tests/             # Test suites
```

### Core Components

- **Router**: PSR-7 compatible routing system
- **Database**: PDO-based query builder with relationships
- **View Engine**: Blade-like template system with layouts
- **Authentication**: Session-based auth with role management
- **API System**: RESTful API with rate limiting and authentication
- **Widget System**: Embeddable widgets with tracking and analytics
- **Security**: XSS, CSRF, SQL injection protection
- **Caching**: File/Redis-based caching system

## 🔌 API Usage

### Authentication
```bash
curl -X POST /api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### Create Widget
```bash
curl -X POST /api/v1/widgets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Hotel Search","type":"search","hotel_id":1}'
```

### Get Widget HTML
```bash
curl /api/v1/widgets/1/render
```

## 🎨 Widget Integration

### Embed Widget
```html
<iframe src="https://your-domain.com/widgets/1/embed" 
        width="100%" height="400" frameborder="0">
</iframe>
```

### JavaScript Integration
```javascript
// Load widget dynamically
fetch('/api/v1/widgets/1/render')
  .then(response => response.json())
  .then(data => {
    document.getElementById('widget-container').innerHTML = data.html;
  });
```

## 🧪 Testing

```bash
# Run all tests
php tests/run_tests.php

# Run specific test suite
php tests/run_tests.php auth
php tests/run_tests.php database
php tests/run_tests.php api
```

## 🚀 Deployment

### Automated Deployment
```bash
# Deploy to production
./scripts/deploy.sh production v1.0.0

# Deploy to staging
./scripts/deploy.sh staging latest

# Rollback if needed
./scripts/deploy.sh production --rollback
```

### Manual Deployment
```bash
# Backup current installation
php scripts/backup.php --type=full --compress

# Update code
git pull origin main

# Run migrations
php database/migrate.php

# Clear caches
php scripts/clear_cache.php

# Set permissions
chmod -R 777 storage/
```

## 📊 Monitoring

### Health Check
```bash
curl /api/v1/status
# Response: {"status":"ok","version":"1.0.0"}
```

### System Statistics
```bash
# Get system health
curl -H "Authorization: Bearer ADMIN_TOKEN" /api/v1/admin/health

# Get performance stats
php scripts/system_check.php
```

### Log Monitoring
```bash
# Monitor application logs
tail -f storage/logs/app.log

# Monitor error logs
tail -f storage/logs/error.log

# Monitor API logs
tail -f storage/logs/api.log
```

## 🔒 Security Features

- **Input Sanitization**: All user inputs are sanitized and validated
- **XSS Protection**: Content Security Policy and output encoding
- **SQL Injection**: Prepared statements and input validation
- **CSRF Protection**: Token-based CSRF protection
- **Rate Limiting**: API and form submission rate limiting
- **Security Headers**: Comprehensive security headers
- **Session Security**: Secure session configuration
- **File Upload Security**: Type validation and secure storage

## 🌍 Multi-Language Support

```php
// In templates
{{ __('common.welcome') }}
{{ __('validation.required', ['field' => 'email']) }}

// In controllers
$message = Lang::get('common.success');
$errors = Lang::get('validation.email');
```

Supported languages: English, Turkish, German, French, Spanish

## 📈 Performance Optimization

- **Query Optimization**: Automatic query analysis and optimization suggestions
- **Caching**: Multi-layer caching (file, Redis, browser)
- **Asset Optimization**: Minified CSS/JS, image optimization
- **Database Indexing**: Optimized database indexes
- **CDN Ready**: Static asset CDN support
- **Gzip Compression**: Automatic content compression

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME="Hotel DigiLab"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_DATABASE=hotel_digilab
DB_USERNAME=username
DB_PASSWORD=password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
```

See [Configuration Guide](docs/CONFIGURATION.md) for complete options.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup
```bash
# Clone repository
git clone https://github.com/hoteldigilab/v2.git
cd v2

# Copy development environment
cp .env.example .env.development

# Set up development database
php database/migrate.php --env=development

# Run development server
php -S localhost:8000 -t public/
```

## 📝 Changelog

### v2.0.0 (2024-01-01)
- Complete framework-less rewrite
- Modern MVC architecture
- RESTful API system
- Enhanced security features
- Performance optimizations
- Multi-language support
- Comprehensive testing suite

### v1.0.0 (2023-06-01)
- Initial Laravel-based version
- Basic hotel management
- Widget system
- Rate comparison

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: https://docs.hoteldigilab.com
- **Email**: support@hoteldigilab.com
- **Issues**: https://github.com/hoteldigilab/v2/issues
- **Community**: https://community.hoteldigilab.com

## 🙏 Acknowledgments

- Built with modern PHP best practices
- Inspired by Laravel's elegant syntax
- Security practices from OWASP guidelines
- Performance optimizations from industry standards

---

**Hotel DigiLab v2** - Empowering hotels with modern technology 🚀
