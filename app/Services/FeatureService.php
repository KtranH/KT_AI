<?php

namespace App\Services;
use App\Interfaces\FeatureRepositoryInterface;

class FeatureService
{
    protected FeatureRepositoryInterface $featureRepository;
    public function __construct(FeatureRepositoryInterface $featureRepository)
    {
        $this->featureRepository = $featureRepository;
    }
    public function getFeatures()
    {
        return $this->featureRepository->getFeatures();
    }
    public function getFeatureById($id)
    {
        return $this->featureRepository->getFeatureById($id);
    }
    public function increaseSumImg($id)
    {
        $this->featureRepository->increaseSumImg($id);
    }
    public function decreaseSumImg(int $featureId)
    {
        $this->featureRepository->decreaseSumImg($featureId);
    }
}
