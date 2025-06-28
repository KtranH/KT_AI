<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponseTrait
{
    /**
     * Trả về response thành công
     *
     * @param mixed|null $data
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Trả về response lỗi
     *
     * @param string $message
     * @param int $code
     * @param mixed|null $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 500, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $errors,
        ], $code);
    }

    /**
     * Xử lý exception và trả về response lỗi
     *
     * @param \Exception $exception
     * @param string $defaultMessage
     * @param array $context
     * @return JsonResponse
     */
    protected function handleException(\Exception $exception, string $defaultMessage, array $context = []): JsonResponse
    {
        // Thêm trace cho context
        $context['trace'] = $exception->getTraceAsString();
        
        // Log lỗi
        Log::error($exception->getMessage(), $context);
        
        // Trả về response lỗi
        $errorMessage = config('app.debug') ? $exception->getMessage() : $defaultMessage;
        
        return $this->errorResponse($defaultMessage, 500, $errorMessage);
    }
} 