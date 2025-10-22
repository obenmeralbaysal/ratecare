#!/bin/bash
# Setup RateCare Directory Permissions
# Makes storage directories writable

echo "============================================"
echo "RateCare Permissions Setup"
echo "============================================"
echo ""

# Get script directory (project root)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "Project root: $SCRIPT_DIR"
echo ""

# Create directories if they don't exist
echo "Creating directories..."
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/views
mkdir -p cache
echo "✓ Directories created"
echo ""

# Set permissions
echo "Setting permissions..."

# Storage directories - writable by web server
chmod -R 777 storage/logs
chmod -R 777 storage/cache
chmod -R 777 storage/views
chmod -R 777 cache

echo "✓ Permissions set to 777"
echo ""

# Check current user and web server user
CURRENT_USER=$(whoami)
echo "Current user: $CURRENT_USER"

# Try to detect web server user
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    WEB_USER="$CURRENT_USER"
fi

echo "Web server user: $WEB_USER"
echo ""

# Optionally change ownership (requires sudo)
read -p "Do you want to change ownership to $WEB_USER? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Changing ownership (requires sudo)..."
    sudo chown -R $WEB_USER:$WEB_USER storage/
    sudo chown -R $WEB_USER:$WEB_USER cache/
    echo "✓ Ownership changed"
fi

echo ""
echo "============================================"
echo "✓ Permissions setup complete!"
echo "============================================"
echo ""
echo "Test log write:"
echo "Test" > storage/logs/test.log && echo "✓ Log write successful" || echo "✗ Log write failed"
echo ""
