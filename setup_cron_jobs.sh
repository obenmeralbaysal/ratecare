#!/bin/bash
# Setup RateCare Cron Jobs
# This script helps setup cron jobs for background tasks

echo "============================================"
echo "RateCare Background Jobs - Cron Setup"
echo "============================================"
echo ""

# Get project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "Project root: $SCRIPT_DIR"
echo ""

# Cron job definitions
echo "Recommended Cron Jobs:"
echo ""
echo "# RateCare Background Jobs"
echo "# Aggregate statistics daily at 2:00 AM"
echo "0 2 * * * php $SCRIPT_DIR/jobs/aggregate_statistics.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1"
echo ""
echo "# Cleanup expired cache daily at 3:00 AM"
echo "0 3 * * * php $SCRIPT_DIR/jobs/cleanup_expired_cache.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1"
echo ""
echo "# Warm cache daily at 6:00 AM"
echo "0 6 * * * php $SCRIPT_DIR/jobs/warm_cache.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1"
echo ""

# Save to temp file
TEMP_CRON="/tmp/ratecare_cron_jobs.txt"
cat > $TEMP_CRON << EOF
# RateCare Background Jobs - Auto-generated
# Aggregate statistics daily at 2:00 AM
0 2 * * * php $SCRIPT_DIR/jobs/aggregate_statistics.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1

# Cleanup expired cache daily at 3:00 AM
0 3 * * * php $SCRIPT_DIR/jobs/cleanup_expired_cache.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1

# Warm cache daily at 6:00 AM
0 6 * * * php $SCRIPT_DIR/jobs/warm_cache.php >> $SCRIPT_DIR/storage/logs/cron.log 2>&1
EOF

echo "============================================"
echo "Installation Options:"
echo "============================================"
echo ""
echo "1. AUTOMATIC (Recommended):"
echo "   Adds jobs to current user's crontab"
echo ""
echo "2. MANUAL:"
echo "   Copy jobs manually with: crontab -e"
echo ""

read -p "Install automatically? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Installing cron jobs..."
    
    # Backup existing crontab
    crontab -l > /tmp/ratecare_crontab_backup_$(date +%Y%m%d_%H%M%S).txt 2>/dev/null || true
    
    # Add new jobs (avoid duplicates)
    (crontab -l 2>/dev/null | grep -v "RateCare Background Jobs"; cat $TEMP_CRON) | crontab -
    
    echo "✓ Cron jobs installed successfully!"
    echo ""
    echo "Verify with: crontab -l"
else
    echo "Manual installation instructions:"
    echo "1. Run: crontab -e"
    echo "2. Add these lines:"
    echo ""
    cat $TEMP_CRON
    echo ""
    echo "3. Save and exit"
fi

echo ""
echo "============================================"
echo "Testing Jobs Manually:"
echo "============================================"
echo ""
echo "Test aggregation:"
echo "  php $SCRIPT_DIR/jobs/aggregate_statistics.php"
echo ""
echo "Test cleanup:"
echo "  php $SCRIPT_DIR/jobs/cleanup_expired_cache.php"
echo ""
echo "Test warming:"
echo "  php $SCRIPT_DIR/jobs/warm_cache.php"
echo ""

# Make job scripts executable
chmod +x $SCRIPT_DIR/jobs/*.php

echo "✓ Job scripts are now executable"
echo ""
echo "============================================"
echo "Done!"
echo "============================================"
