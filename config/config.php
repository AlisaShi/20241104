<?php
return [
    'app' => [
        'name' => 'Food Delivery',
        'env' => 'development',
        'url' => 'http://localhost:8000',
        'timezone' => 'UTC',
        'debug' => true
    ],
    'session' => [
        'lifetime' => 120,
        'secure' => false,
        'httponly' => true
    ],
    'upload' => [
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
        'path' => dirname(__DIR__) . '/public/uploads/'
    ]
];
