<?php

namespace App\Exceptions;

/**
 * Exception cho các lỗi business logic
 */
class BusinessException extends AppException
{
    protected int $statusCode = 422;
    protected bool $shouldLog = false; // Business logic errors không cần log chi tiết
    
    // Các constant cho mã lỗi business
    public const INSUFFICIENT_CREDITS = 1001;
    public const INVALID_PERMISSIONS = 1002;
    public const RESOURCE_NOT_FOUND = 1003;
    public const DUPLICATE_ACTION = 1004;
    public const QUOTA_EXCEEDED = 1005;
    
    public static function insufficientCredits(string $action = ''): self
    {
        return new self(
            "Không đủ credits để thực hiện" . ($action ? " $action" : ''),
            ['action' => $action],
            self::INSUFFICIENT_CREDITS
        );
    }
    
    public static function invalidPermissions(string $resource = ''): self
    {
        return new self(
            "Không có quyền truy cập" . ($resource ? " $resource" : ''),
            ['resource' => $resource],
            self::INVALID_PERMISSIONS
        );
    }
    
    public static function resourceNotFound(string $resource, $id = null): self
    {
        return new self(
            "Không tìm thấy $resource" . ($id ? " với ID: $id" : ''),
            ['resource' => $resource, 'id' => $id],
            self::RESOURCE_NOT_FOUND
        );
    }
    
    public static function duplicateAction(string $action): self
    {
        return new self(
            "Hành động '$action' đã được thực hiện trước đó",
            ['action' => $action],
            self::DUPLICATE_ACTION
        );
    }
    
    public static function quotaExceeded(string $quota, int $limit): self
    {
        return new self(
            "Vượt quá giới hạn $quota ($limit)",
            ['quota' => $quota, 'limit' => $limit],
            self::QUOTA_EXCEEDED
        );
    }
} 