<?php

return [
    'paths' => ['api/*', 'auth/google/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // Để test trên web
    //'allowed_origins' => ['http://127.0.0.1:8000'],
    // Để test postman
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    // Để test trên web
    //'supports_credentials' => true,
    // Để test postman
    'supports_credentials' => false,
]; 