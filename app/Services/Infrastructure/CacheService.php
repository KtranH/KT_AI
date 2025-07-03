<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\AIFeature;

class CacheService
{
    // Cache keys constants
    const IMAGE_COMMENTS_COUNT = 'image_comments_count_%d';
    const USER_LAST_IMAGE_CHECK = 'user_%d_last_image_check';
    const USER_PROFILE = 'user_profile_%d';
    const IMAGE_LIKES_COUNT = 'image_likes_count_%d';
    const FEATURES = 'features';
    const FEATURE = 'feature_%d';
    // Default TTL in minutes
    const DEFAULT_TTL = 5;
    const LONG_TTL = 60;
    const SHORT_TTL = 1;
    
    /**
     * Cache comment count cho image
     */
    public function cacheImageCommentCount(int $imageId, int $count): void
    {
        $key = sprintf(self::IMAGE_COMMENTS_COUNT, $imageId);
        Cache::put($key, $count, now()->addMinutes(self::DEFAULT_TTL));
    }
    
    /**
     * Lấy comment count từ cache
     */
    public function getImageCommentCount(int $imageId): ?int
    {
        $key = sprintf(self::IMAGE_COMMENTS_COUNT, $imageId);
        return Cache::get($key);
    }
    
    /**
     * Clear cache khi có comment mới
     */
    public function clearImageCommentCache(int $imageId): void
    {
        $key = sprintf(self::IMAGE_COMMENTS_COUNT, $imageId);
        Cache::forget($key);
    }
    /**
     * Cache cho 1 feature
     */
    public function cacheFeature(AIFeature $feature): void
    {
        $key = sprintf(self::FEATURE, $feature->id);
        Cache::put($key, $feature, now()->addMinutes(self::DEFAULT_TTL));
    }
    /**
     * Cache cho nhiều features
     */
    public function cacheFeatures(Collection $features): void
    {
        Cache::put(self::FEATURES, $features, now()->addMinutes(self::LONG_TTL));
    }
    /**
     * Get feature from cache
     */
    public function getFeature(int $id): ?AIFeature
    {
        $key = sprintf(self::FEATURE, $id);
        return Cache::get($key) ?? null;
    }
    /**
     * Get features from cache
     */
    public function getFeatures()
    {
        return Cache::get(self::FEATURES);
    }
    /**
     * Clear features cache
     */
    public function clearFeatures(): void
    {
        Cache::forget(self::FEATURES);
    }

    /**
     * Cache user profile
     */
    public function cacheUserProfile(int $userId, $profile): void
    {
        $key = sprintf(self::USER_PROFILE, $userId);
        Cache::put($key, $profile, now()->addMinutes(self::LONG_TTL));
    }
    
    /**
     * Clear user-related cache
     */
    public function clearUserCache(int $userId): void
    {
        $patterns = [
            sprintf(self::USER_PROFILE, $userId),
            sprintf(self::USER_LAST_IMAGE_CHECK, $userId),
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
    
    /**
     * Bulk clear cache cho multiple users
     */
    public function bulkClearUserCache(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->clearUserCache($userId);
        }
    }
    
    /**
     * Cache với automatic tags
     */
    public function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL, array $tags = [])
    {
        try {
            if (!empty($tags) && method_exists(Cache::getStore(), 'tags')) {
                return Cache::tags($tags)->remember($key, now()->addMinutes($ttl), $callback);
            }
            
            return Cache::remember($key, now()->addMinutes($ttl), $callback);
        } catch (\Exception $e) {
            Log::warning('Cache operation failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            // Fallback: execute callback directly
            return $callback();
        }
    }
    
    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): void
    {
        try {
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags($tags)->flush();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate cache by tags', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
        }
    }
} 