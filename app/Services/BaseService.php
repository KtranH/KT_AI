<?php

namespace App\Services;

use App\Traits\ExceptionHandlerTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

abstract class BaseService
{
    use ExceptionHandlerTrait;
    /**
     * Thực hiện transaction với error handling tối ưu
     * @deprecated Sử dụng executeInTransactionSafely từ ExceptionHandlerTrait
     */
    protected function executeInTransaction(callable $callback)
    {
        return $this->executeInTransactionSafely($callback, 'BaseService transaction');
    }
    
    /**
     * Cache với default TTL
     */
    protected function remember(string $key, callable $callback, int $ttlMinutes = 5)
    {
        return Cache::remember($key, now()->addMinutes($ttlMinutes), $callback);
    }
    
    /**
     * Validate ID
     * @param mixed $id ID
     * @return bool Kết quả
     */
    protected function validateId($id): bool
    {
        return is_numeric($id) && $id > 0;
    }
    
    /**
     * Validate email format
     * @param string $email Email
     * @return bool Kết quả
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Log action với context
     * @param string $action Hành động
     * @param array $context Context
     * @return void
     */
    protected function logAction(string $action, array $context = []): void
    {
        Log::info($action, array_merge([
            'service' => static::class,
            'timestamp' => now()->toISOString()
        ], $context));
    }
    
    /**
     * Clear cache by tags hoặc pattern
     * @param string $pattern Pattern
     * @return void
     */
    protected function clearCachePattern(string $pattern): void
    {
        try {
            // Nếu cache driver hỗ trợ tags, sử dụng tags
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags($pattern)->flush();
            } else {
                // Fallback: clear toàn bộ cache
                Cache::flush();
                Log::warning('Cache pattern clearing not supported, cleared all cache instead');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache pattern: ' . $pattern, ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Bulk operations với batch processing
     * @param array $items Danh sách items
     * @param callable $processor Hàm xử lý
     * @param int $batchSize Kích thước batch
     * @return array Danh sách kết quả
     */
    protected function processBatch(array $items, callable $processor, int $batchSize = 100): array
    {
        $results = [];
        $chunks = array_chunk($items, $batchSize);
        
        foreach ($chunks as $chunk) {
            $batchResults = $processor($chunk);
            $results = array_merge($results, $batchResults);
        }
        
        return $results;
    }
} 