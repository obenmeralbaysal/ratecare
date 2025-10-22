<?php
/**
 * Setup RateCare Directory Permissions
 * Creates storage directories and sets proper permissions
 */

echo "============================================\n";
echo "RateCare Permissions Setup\n";
echo "============================================\n\n";

// Get project root
$projectRoot = __DIR__;
echo "Project root: {$projectRoot}\n\n";

// Directories to create
$directories = [
    'storage/logs',
    'storage/cache',
    'storage/views',
    'cache',
    'database/migrations'
];

echo "Creating directories...\n";
foreach ($directories as $dir) {
    $fullPath = $projectRoot . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0777, true)) {
            echo "  ✓ Created: {$dir}\n";
        } else {
            echo "  ✗ Failed to create: {$dir}\n";
        }
    } else {
        echo "  ✓ Already exists: {$dir}\n";
    }
    
    // Set permissions
    if (chmod($fullPath, 0777)) {
        echo "    → Permissions set to 777\n";
    } else {
        echo "    → Warning: Could not set permissions\n";
    }
}

echo "\n";

// Test log write
echo "Testing log write...\n";
$testLogFile = $projectRoot . '/storage/logs/test.log';
$testContent = "Test log entry - " . date('Y-m-d H:i:s') . "\n";

if (file_put_contents($testLogFile, $testContent, FILE_APPEND)) {
    echo "  ✓ Log write successful: {$testLogFile}\n";
    echo "  ✓ Content written: " . strlen($testContent) . " bytes\n";
} else {
    echo "  ✗ Log write failed\n";
    echo "  → Please check directory permissions manually\n";
}

echo "\n";

// Check PHP user
echo "PHP Process Information:\n";
echo "  User: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()) . "\n";
echo "  UID: " . (function_exists('posix_geteuid') ? posix_geteuid() : 'N/A') . "\n";

echo "\n";

// Check directory permissions
echo "Directory Permissions:\n";
foreach ($directories as $dir) {
    $fullPath = $projectRoot . '/' . $dir;
    if (file_exists($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $writable = is_writable($fullPath) ? '✓ Writable' : '✗ Not writable';
        echo "  {$dir}: {$perms} ({$writable})\n";
    }
}

echo "\n";
echo "============================================\n";
echo "✓ Permissions setup complete!\n";
echo "============================================\n\n";

echo "If you still have permission issues:\n";
echo "1. Run: chmod -R 777 storage cache\n";
echo "2. Or: chown -R www-data:www-data storage cache\n";
echo "3. Check SELinux: setenforce 0 (temporarily)\n\n";
