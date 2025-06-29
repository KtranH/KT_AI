<?php

namespace App\Services;
use App\Interfaces\FeatureRepositoryInterface;
use App\Services\BaseService;

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
            return $this->featureRepository->getFeatures();
        }, "Getting features");
    }
    public function getFeatureById($id)
    {
        return $this->executeWithExceptionHandling(function() use ($id) {
            return $this->featureRepository->getFeatureById($id);
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
