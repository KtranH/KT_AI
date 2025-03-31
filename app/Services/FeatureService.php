<?php

namespace App\Services;

use App\Models\AIFeature;

class FeatureService
{
    public function getFeatures()
    {
        return Feature::all();
    }
    // Cập nhật số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id)
    {
        $feature = AIFeature::find($id);
        $feature->sum_img = $feature->sum_img + 1;
        $feature->save();
    }
}
