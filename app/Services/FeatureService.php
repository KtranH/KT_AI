<?php

namespace App\Services;
use App\Interfaces\FeatureRepositoryInterface;
use App\Services\BaseService;
use App\Http\Resources\AIFeatureResource;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class FeatureService extends BaseService
{
    protected FeatureRepositoryInterface $featureRepository;
    protected CacheService $cacheService;
    public function __construct(FeatureRepositoryInterface $featureRepository, CacheService $cacheService)
    {
        $this->featureRepository = $featureRepository;
        $this->cacheService = $cacheService;
    }
    /**
     * Lấy danh sách feature
     */
    public function getFeatures(): mixed
    {
        return $this->executeWithExceptionHandling(function() {
            $features = $this->cacheService->getFeatures();
            if ($features) {
                return AIFeatureResource::collection($features);
            }
            $features = $this->featureRepository->getFeatures();
            $this->cacheService->cacheFeatures($features);
            return AIFeatureResource::collection($features);
        }, "Getting features");
    }
    /**
     * Lấy feature theo ID
     */
    public function getFeatureById(int $id): mixed
    {
        return $this->executeWithExceptionHandling(function() use ($id) {
            $feature = $this->cacheService->getFeature($id);
            if ($feature) {
                return new AIFeatureResource($feature);
            }
            $feature = $this->featureRepository->getFeatureById($id);
            $this->cacheService->cacheFeature($feature);
            return new AIFeatureResource($feature);
        }, "Getting feature by ID: {$id}");
    }
    /**
     * Tăng số lượng ảnh của feature
     */
    public function increaseSumImg(int $id): mixed
    {
        return $this->executeInTransactionSafely(function() use ($id) {
            $this->featureRepository->increaseSumImg($id);
        }, "Increasing sum image for feature ID: {$id}");
    }
    /**
     * Giảm số lượng ảnh của feature
     */
    public function decreaseSumImg(int $featureId): mixed
    {
        return $this->executeInTransactionSafely(function() use ($featureId) {
            $this->featureRepository->decreaseSumImg($featureId);
        }, "Decreasing sum image for feature ID: {$featureId}");
    }
}
