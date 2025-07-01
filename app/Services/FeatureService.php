<?php

namespace App\Services;
use App\Interfaces\FeatureRepositoryInterface;
use App\Services\BaseService;
use App\Http\Resources\AIFeatureResource;

class FeatureService extends BaseService
{
    protected FeatureRepositoryInterface $featureRepository;
    public function __construct(FeatureRepositoryInterface $featureRepository)
    {
        $this->featureRepository = $featureRepository;
    }
    public function getFeatures()
    {
        return $this->executeWithExceptionHandling(function() {
            $features = $this->featureRepository->getFeatures();
            return AIFeatureResource::collection($features);
        }, "Getting features");
    }
    public function getFeatureById($id)
    {
        return $this->executeWithExceptionHandling(function() use ($id) {
            $feature = $this->featureRepository->getFeatureById($id);
            return new AIFeatureResource($feature);
        }, "Getting feature by ID: {$id}");
    }
    public function increaseSumImg($id)
    {
        return $this->executeInTransactionSafely(function() use ($id) {
            $this->featureRepository->increaseSumImg($id);
        }, "Increasing sum image for feature ID: {$id}");
    }
    public function decreaseSumImg(int $featureId)
    {
        return $this->executeInTransactionSafely(function() use ($featureId) {
            $this->featureRepository->decreaseSumImg($featureId);
        }, "Decreasing sum image for feature ID: {$featureId}");
    }
}
