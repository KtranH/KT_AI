<?php

namespace App\Repositories;

use App\Interfaces\FeatureRepositoryInterface;
use App\Models\AIFeature;

class FeatureRepository implements FeatureRepositoryInterface
{
    public function getFeatures()
    {
        return AIFeature::where('status_feature', 'active')->get();
    }
    public function getFeatureById($id)
    {
        return AIFeature::where('id', $id)->first();
    }
    public function increaseSumImg($id)
    {
        $feature = AIFeature::find($id);
        $feature->sum_img = $feature->sum_img + 1;
        $feature->save();
    }
    public function decreaseSumImg(int $featureId)
    {
        $feature = AIFeature::find($featureId);
        $feature->sum_img = $feature->sum_img - 1;
        $feature->save();
    }
}
