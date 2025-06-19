<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FeatureRepositoryInterface
{
    // Lấy danh sách feature
    public function getFeatures();

    // Lấy thông tin feature theo ID
    public function getFeatureById($id);
    
    // Cập nhật số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id);

    // Giảm số lượng ảnh khi người dùng xóa
    public function decreaseSumImg(int $featureId);
}
