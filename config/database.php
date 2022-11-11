<?php

return [
    'connection' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'php-education'),
        'username' => env('DB_USERNAME', 'php-education'),
        'password' => env('DB_PASSWORD', 'php-education')
    ]
];
