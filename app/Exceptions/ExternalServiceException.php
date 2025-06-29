<?php

namespace App\Exceptions;

/**
 * Exception cho các lỗi từ dịch vụ bên ngoài
 */
class ExternalServiceException extends AppException
{
    protected int $statusCode = 503;
    protected bool $shouldLog = true;
    
    // Các constant cho mã lỗi external service
    public const COMFYUI_ERROR = 2001;
    public const STORAGE_ERROR = 2002;
    public const EMAIL_SERVICE_ERROR = 2003;
    public const PAYMENT_SERVICE_ERROR = 2004;
    public const CACHE_SERVICE_ERROR = 2005;
    
    public static function comfyUIError(string $message, array $context = []): self
    {
        return new self(
            "Lỗi từ ComfyUI: $message",
            array_merge($context, ['service' => 'ComfyUI']),
            self::COMFYUI_ERROR
        );
    }
    
    public static function storageError(string $message, array $context = []): self
    {
        return new self(
            "Lỗi lưu trữ: $message",
            array_merge($context, ['service' => 'Storage']),
            self::STORAGE_ERROR
        );
    }
    
    public static function emailServiceError(string $message, array $context = []): self
    {
        return new self(
            "Lỗi gửi email: $message",
            array_merge($context, ['service' => 'Email']),
            self::EMAIL_SERVICE_ERROR
        );
    }
    
    public static function cacheServiceError(string $message, array $context = []): self
    {
        return new self(
            "Lỗi cache: $message",
            array_merge($context, ['service' => 'Cache']),
            self::CACHE_SERVICE_ERROR
        );
    }
} 