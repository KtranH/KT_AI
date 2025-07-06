<?php

namespace App\Repositories;

use App\Interfaces\FeatureRepositoryInterface;
use App\Models\AIFeature;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FeatureRepository implements FeatureRepositoryInterface
{
    /**
     * Lấy danh sách feature
     *
     * @return Collection
     */
    public function getFeatures(): Collection
    {
        $features = AIFeature::where('status_feature', 'active')->get();
        return $features;
    }

    /**
     * Lấy thông tin feature theo ID
     *
     * @param int $id ID của feature
     * @return AIFeature|null
     */
    public function getFeatureById(int $id): ?AIFeature
    {
        return AIFeature::where('id', $id)->first();
    }

    /**
     * Cập nhật số lượng ảnh khi người dùng tải lên
     *
     * @param int $id ID của feature
     * @return void
     */
    public function increaseSumImg(int $id): void
    {
        $feature = AIFeature::find($id);
        if ($feature) {
            $feature->sum_img = $feature->sum_img + 1;
            $feature->save();
        }
    }

    /**
     * Giảm số lượng ảnh khi người dùng xóa
     *
     * @param int $featureId ID của feature
     * @return void
     */
    public function decreaseSumImg(int $featureId): void
    {
        $feature = AIFeature::find($featureId);
        if ($feature && $feature->sum_img > 0) {
            $feature->sum_img = $feature->sum_img - 1;
            $feature->save();
        }
    }
}
