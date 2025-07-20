<?php

return [
    'paths' => ['api/*', 'auth/google/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    
    // === PRODUCTION SETTINGS (Uncomment for production) ===
    // 'allowed_origins' => [
    //     'http://127.0.0.1:8000',
    //     'http://localhost:8000',
    //     'https://yourdomain.com',
    // ],
    
    // === DEVELOPMENT SETTINGS (Comment out for production) ===
    'allowed_origins' => ['*'],
    
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    
    // === QUAN TRỌNG: Bật credentials để hỗ trợ CSRF + Sanctum ===
    'supports_credentials' => true,
]; 