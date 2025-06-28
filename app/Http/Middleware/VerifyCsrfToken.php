<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Các URI cần bỏ qua CSRF
     * @var array<int, string>
     */
    protected $except = [
        'api/*'  // Bỏ qua CSRF cho tất cả các route API
    ];
}
