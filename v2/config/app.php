<?php

return [
    // Application
    'name' => $_ENV['APP_NAME'] ?? 'Hotel DigiLab',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    
    // Security
    'key' => $_ENV['APP_KEY'] ?? 'your-secret-key-here',
    'hash_algo' => $_ENV['HASH_ALGO'] ?? 'sha256',
    'session_lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
    'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? '_token',
    
    // File Upload
    'upload' => [
        'max_size' => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf'),
        'path' => $_ENV['UPLOAD_PATH'] ?? 'storage/uploads'
    ],
];
