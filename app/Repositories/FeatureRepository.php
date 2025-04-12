<?php

namespace App\Repositories;

use App\Interfaces\FeatureRepositoryInterface;
use App\Models\AIFeature;

class FeatureRepository implements FeatureRepositoryInterface
{
    // Lấy danh sách feature
    public function getFeatures()
    {
        return AIFeature::where('status_feature', 'active')->get();
    }
    // Lấy thông tin feature theo ID
    public function getFeatureById($id)
    {
        return AIFeature::where('id', $id)->first();
    }
    // Cập nhật số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id)
    {
        $feature = AIFeature::find($id);
        $feature->sum_img = $feature->sum_img + 1;
        $feature->save();
    }
    // Giảm số lượng ảnh khi người dùng xóa ảnh
    public function decreaseSumImg(int $featureId)
    {
        $feature = AIFeature::find($featureId);
        $feature->sum_img = $feature->sum_img - 1;
        $feature->save();
    }
}
