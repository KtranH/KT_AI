<?php

namespace App\Interfaces;

use App\Models\AIFeature;
use Illuminate\Support\Collection;

interface FeatureRepositoryInterface
{
    /**
    * Lấy danh sách feature
    * @return Collection Danh sách feature
    */
    public function getFeatures(): Collection;

    /**
    * Lấy thông tin feature theo ID
    * @param int $id ID của feature
    * @return AIFeature|null Feature
    */
    public function getFeatureById(int $id): ?AIFeature;
    
    /**
    * Cập nhật số lượng ảnh khi người dùng tải lên
    * @param int $id ID của feature
    * @return void
    */
    public function increaseSumImg(int $id): void;

    /**
    * Giảm số lượng ảnh khi người dùng xóa
    * @param int $featureId ID của feature
    * @return void
    */
    public function decreaseSumImg(int $featureId): void;
}
