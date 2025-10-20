#!/bin/bash

# Hotel DigiLab Deployment Script
# Usage: ./scripts/deploy.sh [environment] [version]

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENVIRONMENT=${1:-production}
VERSION=${2:-latest}
BACKUP_DIR="/var/backups/hoteldigilab"
LOG_FILE="/var/log/hoteldigilab-deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as root
check_permissions() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root for security reasons"
    fi
}

# Validate environment
validate_environment() {
    case $ENVIRONMENT in
        development|staging|production)
            log "Deploying to $ENVIRONMENT environment"
            ;;
        *)
            error "Invalid environment: $ENVIRONMENT. Use: development, staging, or production"
            ;;
    esac
}

# Check system requirements
check_requirements() {
    log "Checking system requirements..."
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        error "PHP is not installed"
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$PHP_VERSION 7.4" | awk '{print ($1 < $2)}') -eq 1 ]]; then
        error "PHP 7.4 or higher is required. Current version: $PHP_VERSION"
    fi
    
    # Check required PHP extensions
    REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "json" "curl" "gd")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "$ext"; then
            error "Required PHP extension missing: $ext"
        fi
    done
    
    # Check MySQL
    if ! command -v mysql &> /dev/null; then
        warning "MySQL client not found. Database operations may fail."
    fi
    
    # Check web server
    if ! systemctl is-active --quiet apache2 && ! systemctl is-active --quiet nginx; then
        warning "No active web server detected (Apache or Nginx)"
    fi
    
    log "System requirements check completed"
}

# Create backup
create_backup() {
    log "Creating backup..."
    
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_NAME="hoteldigilab_backup_${TIMESTAMP}"
    
    # Create backup directory
    sudo mkdir -p "$BACKUP_DIR"
    
    # Backup application files
    if [[ -d "$PROJECT_DIR" ]]; then
        log "Backing up application files..."
        sudo tar -czf "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" \
            --exclude="storage/cache/*" \
            --exclude="storage/logs/*" \
            --exclude="node_modules" \
            --exclude=".git" \
            -C "$(dirname "$PROJECT_DIR")" \
            "$(basename "$PROJECT_DIR")"
    fi
    
    # Backup database
    if [[ -f "$PROJECT_DIR/.env" ]]; then
        log "Backing up database..."
        source "$PROJECT_DIR/.env"
        
        if [[ -n "$DB_DATABASE" ]]; then
            mysqldump -h"${DB_HOST:-localhost}" \
                     -u"$DB_USERNAME" \
                     -p"$DB_PASSWORD" \
                     "$DB_DATABASE" | \
            gzip > "$BACKUP_DIR/${BACKUP_NAME}_database.sql.gz"
        fi
    fi
    
    log "Backup created: $BACKUP_NAME"
    echo "$BACKUP_NAME" > "$PROJECT_DIR/.last_backup"
}

# Download and extract new version
deploy_code() {
    log "Deploying code version: $VERSION"
    
    if [[ "$VERSION" == "latest" ]]; then
        # For production, you might want to download from a release
        log "Using current code (latest)"
    else
        # Download specific version
        TEMP_DIR=$(mktemp -d)
        cd "$TEMP_DIR"
        
        # Example: Download from Git tag
        git clone --depth 1 --branch "$VERSION" \
            https://github.com/hoteldigilab/v2.git \
            hoteldigilab-new
        
        # Replace current installation
        rsync -av --delete \
            --exclude=".env" \
            --exclude="storage/logs/*" \
            --exclude="storage/cache/*" \
            --exclude="storage/uploads/*" \
            hoteldigilab-new/ "$PROJECT_DIR/"
        
        rm -rf "$TEMP_DIR"
    fi
    
    log "Code deployment completed"
}

# Install/update dependencies
install_dependencies() {
    log "Installing dependencies..."
    
    cd "$PROJECT_DIR"
    
    # If using Composer (for future PHP dependencies)
    if [[ -f "composer.json" ]]; then
        if command -v composer &> /dev/null; then
            composer install --no-dev --optimize-autoloader
        else
            warning "Composer not found, skipping PHP dependencies"
        fi
    fi
    
    # If using npm (for frontend assets)
    if [[ -f "package.json" ]]; then
        if command -v npm &> /dev/null; then
            npm ci --production
            npm run build
        else
            warning "npm not found, skipping frontend build"
        fi
    fi
    
    log "Dependencies installation completed"
}

# Set proper permissions
set_permissions() {
    log "Setting file permissions..."
    
    cd "$PROJECT_DIR"
    
    # Set general permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # Set executable permissions for scripts
    chmod +x scripts/*.sh
    chmod +x scripts/*.php
    
    # Set writable permissions for storage
    chmod -R 777 storage/
    
    # Secure sensitive files
    if [[ -f ".env" ]]; then
        chmod 600 .env
    fi
    
    # Set ownership
    WEB_USER=$(ps aux | grep -E '(apache|nginx|httpd)' | grep -v root | head -1 | awk '{print $1}')
    if [[ -n "$WEB_USER" ]]; then
        sudo chown -R "$WEB_USER:$WEB_USER" .
    fi
    
    log "Permissions set successfully"
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    cd "$PROJECT_DIR"
    
    if [[ -f "database/migrate.php" ]]; then
        php database/migrate.php
        log "Database migrations completed"
    else
        warning "Migration script not found"
    fi
}

# Clear caches
clear_caches() {
    log "Clearing caches..."
    
    cd "$PROJECT_DIR"
    
    # Clear application cache
    if [[ -f "scripts/clear_cache.php" ]]; then
        php scripts/clear_cache.php
    fi
    
    # Clear web server cache
    if systemctl is-active --quiet apache2; then
        sudo systemctl reload apache2
    fi
    
    if systemctl is-active --quiet nginx; then
        sudo systemctl reload nginx
    fi
    
    # Clear PHP OPcache
    if command -v php &> /dev/null; then
        php -r "if (function_exists('opcache_reset')) opcache_reset();"
    fi
    
    log "Caches cleared"
}

# Run tests
run_tests() {
    if [[ "$ENVIRONMENT" != "production" ]]; then
        log "Running tests..."
        
        cd "$PROJECT_DIR"
        
        if [[ -f "tests/run_tests.php" ]]; then
            php tests/run_tests.php
            log "Tests completed successfully"
        else
            warning "Test runner not found"
        fi
    else
        log "Skipping tests in production environment"
    fi
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Check if application responds
    if command -v curl &> /dev/null; then
        APP_URL=$(grep "APP_URL" "$PROJECT_DIR/.env" | cut -d'=' -f2 | tr -d '"')
        
        if [[ -n "$APP_URL" ]]; then
            HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/api/v1/status" || echo "000")
            
            if [[ "$HTTP_CODE" == "200" ]]; then
                log "Health check passed - Application is responding"
            else
                error "Health check failed - HTTP $HTTP_CODE"
            fi
        fi
    fi
    
    # Check database connection
    if [[ -f "scripts/test_db.php" ]]; then
        php scripts/test_db.php
    fi
    
    log "Health check completed"
}

# Send notifications
send_notifications() {
    log "Sending deployment notifications..."
    
    # Example: Send to Slack, email, etc.
    DEPLOY_MESSAGE="🚀 Hotel DigiLab deployed successfully to $ENVIRONMENT (version: $VERSION)"
    
    # If webhook URL is configured
    if [[ -n "${SLACK_WEBHOOK_URL:-}" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"$DEPLOY_MESSAGE\"}" \
            "$SLACK_WEBHOOK_URL" || warning "Failed to send Slack notification"
    fi
    
    # Log deployment
    echo "$(date): Deployed version $VERSION to $ENVIRONMENT" >> /var/log/hoteldigilab-deployments.log
    
    log "Notifications sent"
}

# Rollback function
rollback() {
    log "Rolling back deployment..."
    
    if [[ -f "$PROJECT_DIR/.last_backup" ]]; then
        BACKUP_NAME=$(cat "$PROJECT_DIR/.last_backup")
        
        # Restore files
        if [[ -f "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" ]]; then
            log "Restoring application files..."
            sudo tar -xzf "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" \
                -C "$(dirname "$PROJECT_DIR")"
        fi
        
        # Restore database
        if [[ -f "$BACKUP_DIR/${BACKUP_NAME}_database.sql.gz" ]]; then
            log "Restoring database..."
            source "$PROJECT_DIR/.env"
            
            zcat "$BACKUP_DIR/${BACKUP_NAME}_database.sql.gz" | \
            mysql -h"${DB_HOST:-localhost}" \
                  -u"$DB_USERNAME" \
                  -p"$DB_PASSWORD" \
                  "$DB_DATABASE"
        fi
        
        set_permissions
        clear_caches
        
        log "Rollback completed"
    else
        error "No backup found for rollback"
    fi
}

# Main deployment function
main() {
    log "Starting deployment to $ENVIRONMENT environment"
    
    check_permissions
    validate_environment
    check_requirements
    
    # Handle rollback
    if [[ "${3:-}" == "--rollback" ]]; then
        rollback
        exit 0
    fi
    
    create_backup
    deploy_code
    install_dependencies
    set_permissions
    run_migrations
    clear_caches
    
    # Run tests in non-production environments
    if [[ "$ENVIRONMENT" != "production" ]]; then
        run_tests
    fi
    
    health_check
    send_notifications
    
    log "Deployment completed successfully!"
    log "Version: $VERSION"
    log "Environment: $ENVIRONMENT"
    log "Backup: $(cat "$PROJECT_DIR/.last_backup" 2>/dev/null || echo 'N/A')"
}

# Handle script arguments
case "${1:-}" in
    --rollback)
        rollback
        ;;
    --help|-h)
        echo "Usage: $0 [environment] [version] [--rollback]"
        echo "Environments: development, staging, production"
        echo "Version: latest, v1.0.0, etc."
        echo "Options:"
        echo "  --rollback    Rollback to last backup"
        echo "  --help        Show this help"
        exit 0
        ;;
    *)
        main "$@"
        ;;
esac
