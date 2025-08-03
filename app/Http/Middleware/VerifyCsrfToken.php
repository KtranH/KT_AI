<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Các URI cần bỏ qua CSRF
     * 
     * CHỈ BỎ QUA CHO NHỮNG ENDPOINTS THỰC SỰ PUBLIC
     * TẤT CẢ ENDPOINTS KHÁC SẼ CÓ DOUBLE PROTECTION (CSRF + SANCTUM)
     * 
     * @var array<int, string>
     */
    protected $except = [
        // === PUBLIC READ-ONLY ENDPOINTS ===
        'api/turnstile/config',           // Cấu hình Turnstile (cần trước khi đăng nhập)
        'api/load_features',              // Lấy danh sách features (public)
        'api/load_features/*',            // Lấy feature theo ID (public)
        'api/get_images_information/*',   // Lấy thông tin hình ảnh (public)
        'api/get_images_by_feature/*',    // Lấy hình ảnh theo feature (public)
        'api/get_likes_information/*',    // Lấy thông tin like (public)
        'api/check',                      // Check auth status (có thể gọi khi chưa đăng nhập)
        'api/api-login',                  // API Login
        
        // === GOOGLE OAUTH CALLBACKS ===
        'api/google/url',                 // Google OAuth URL
        'api/google/callback',            // Google OAuth callback
        
        // === CSRF + SANCTUM PROTECTION CHO TẤT CẢ ENDPOINTS KHÁC ===
        // Tất cả auth endpoints sẽ yêu cầu CSRF:
        // - api/login
        // - api/register
        // - api/forgot-password
        // - api/verify-email
        // - api/resend-verification
        // - api/verify-reset-code
        // - api/reset-password
        // - api/logout (protected)
        // 
        // Tất cả protected endpoints sẽ yêu cầu cả CSRF + Sanctum:
        // - api/user/*
        // - api/images/*
        // - api/comments/*
        // - api/likes/*
        // - api/notifications/*
        // - api/statistics
    ];
}
