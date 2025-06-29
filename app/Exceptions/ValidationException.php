<?php

namespace App\Exceptions;

/**
 * Exception cho các lỗi validation tùy chỉnh
 */
class ValidationException extends AppException
{
    protected int $statusCode = 400;
    protected bool $shouldLog = false;
    protected array $errors = [];
    
    public function __construct(string $message, array $errors = [], array $context = [])
    {
        parent::__construct($message, $context);
        $this->errors = $errors;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors
        ], $this->getStatusCode());
    }
    
    public static function invalidInput(string $field, string $message): self
    {
        return new self(
            "Dữ liệu không hợp lệ",
            [$field => [$message]]
        );
    }
    
    public static function multipleErrors(array $errors): self
    {
        return new self(
            "Dữ liệu không hợp lệ",
            $errors
        );
    }
} 