<?php

namespace App\Traits;

use App\Exceptions\AppException;
use App\Exceptions\BusinessException;
use App\Exceptions\ExternalServiceException;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Trait để xử lý exception một cách tập trung và nhất quán
 */
trait ExceptionHandlerTrait
{
    /**
     * Thực thi một callback với xử lý exception tập trung
     * 
     * @param callable $callback
     * @param string $context Mô tả ngữ cảnh để logging
     * @return mixed
     * @throws AppException
     */
    protected function executeWithExceptionHandling(callable $callback, string $context = '')
    {
        try {
            return $callback();
        } catch (AppException $e) {
            // Re-throw custom exceptions để controller hoặc caller khác xử lý
            throw $e;
        } catch (Throwable $e) {
            // Log exception gốc
            $this->logException($e, $context);
            
            // Chuyển đổi thành custom exception
            throw $this->convertToAppException($e, $context);
        }
    }
    
    /**
     * Thực thi callback và trả về JsonResponse (dành cho Controller)
     * 
     * @param callable $callback
     * @param string $context
     * @param string|null $successMessage
     * @return JsonResponse
     */
    protected function executeAndRespond(callable $callback, string $context = '', ?string $successMessage = null): JsonResponse
    {
        try {
            $result = $this->executeWithExceptionHandling($callback, $context);
            
            return $this->successResponse($result, $successMessage);
        } catch (AppException $e) {
            // Log nếu cần
            if ($e->shouldLog()) {
                $this->logException($e, $context);
            }
            
            return $e->render();
        } catch (Throwable $e) {
            // Fallback cho các exception không mong muốn
            $this->logException($e, $context);
            
            return $this->errorResponse(
                config('app.debug') ? $e->getMessage() : 'Có lỗi xảy ra, vui lòng thử lại sau',
                500
            );
        }
    }
    
    /**
     * Thực thi trong transaction với exception handling
     * 
     * @param callable $callback
     * @param string $context
     * @return mixed
     * @throws AppException
     */
    protected function executeInTransactionSafely(callable $callback, string $context = '')
    {
        try {
            \DB::beginTransaction();
            
            $result = $this->executeWithExceptionHandling($callback, $context);
            
            \DB::commit();
            return $result;
        } catch (Throwable $e) {
            \DB::rollBack();
            
            // Log transaction rollback
            $this->logException($e, "Transaction rolled back: $context");
            
            // Re-throw để caller xử lý
            throw $e instanceof AppException ? $e : $this->convertToAppException($e, $context);
        }
    }
    
    /**
     * Log exception với context đầy đủ
     * 
     * @param Throwable $exception
     * @param string $context
     */
    protected function logException(Throwable $exception, string $context = ''): void
    {
        $logContext = [
            'class' => static::class,
            'context' => $context,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        // Thêm context từ custom exception nếu có
        if ($exception instanceof AppException) {
            $logContext = array_merge($logContext, $exception->getContext());
        }
        
        // Determine log level
        $level = $this->getLogLevel($exception);
        
        Log::$level($exception->getMessage(), $logContext);
    }
    
    /**
     * Chuyển đổi exception thành AppException
     * 
     * @param Throwable $exception
     * @param string $context
     * @return AppException
     */
    protected function convertToAppException(Throwable $exception, string $context = ''): AppException
    {
        // Phân loại exception dựa trên type hoặc message
        $message = $exception->getMessage();
        $exceptionContext = ['original_exception' => get_class($exception), 'context' => $context];
        
        // Database exceptions
        if ($exception instanceof \Illuminate\Database\QueryException) {
            return new ExternalServiceException(
                'Lỗi cơ sở dữ liệu',
                $exceptionContext,
                ExternalServiceException::STORAGE_ERROR
            );
        }
        
        // Validation exceptions
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return new ValidationException(
                'Dữ liệu không hợp lệ',
                $exception->errors(),
                $exceptionContext
            );
        }
        
        // Authorization exceptions
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return BusinessException::invalidPermissions($context);
        }
        
        // HTTP Client exceptions (for API calls)
        if ($exception instanceof \Illuminate\Http\Client\RequestException) {
            return ExternalServiceException::comfyUIError($message, $exceptionContext);
        }
        
        // File/Storage exceptions
        if (str_contains($message, 'file') || str_contains($message, 'storage') || 
            $exception instanceof \Illuminate\Contracts\Filesystem\FileNotFoundException) {
            return ExternalServiceException::storageError($message, $exceptionContext);
        }
        
        // Cache exceptions
        if (str_contains($message, 'cache') || str_contains($message, 'redis')) {
            return ExternalServiceException::cacheServiceError($message, $exceptionContext);
        }
        
        // Default: Business exception
        return new BusinessException(
            'Có lỗi xảy ra trong quá trình xử lý',
            $exceptionContext
        );
    }
    
    /**
     * Xác định log level dựa trên exception type
     * 
     * @param Throwable $exception
     * @return string
     */
    protected function getLogLevel(Throwable $exception): string
    {
        if ($exception instanceof BusinessException) {
            return 'info';
        }
        
        if ($exception instanceof ValidationException) {
            return 'debug';
        }
        
        if ($exception instanceof ExternalServiceException) {
            return 'warning';
        }
        
        return 'error';
    }
} 