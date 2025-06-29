<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Base exception class cho ứng dụng
 */
abstract class AppException extends Exception
{
    protected int $statusCode = 500;
    protected array $context = [];
    protected bool $shouldLog = true;
    
    public function __construct(string $message = "", array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    /**
     * Trả về status code HTTP
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Trả về context để logging
     */
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * Có nên log exception này không
     */
    public function shouldLog(): bool
    {
        return $this->shouldLog;
    }
    
    /**
     * Render exception thành JSON response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->getCode()
        ], $this->getStatusCode());
    }
} 