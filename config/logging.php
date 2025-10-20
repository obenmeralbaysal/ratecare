<?php

return [
    'default' => 'file',
    
    'channels' => [
        'file' => [
            'driver' => 'file',
            'path' => $_ENV['LOG_FILE'] ?? __DIR__ . '/../storage/logs/app.log',
            'level' => $_ENV['LOG_LEVEL'] ?? 'info',
            'max_files' => (int) ($_ENV['LOG_MAX_FILES'] ?? 30),
        ],
        
        'error' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/logs/error.log',
            'level' => 'error',
            'max_files' => 30,
        ],
        
        'api' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/logs/api.log',
            'level' => 'info',
            'max_files' => 30,
        ],
        
        'widget' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/logs/widget.log',
            'level' => 'info',
            'max_files' => 30,
        ]
    ],
    
    'levels' => [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7,
    ]
];
