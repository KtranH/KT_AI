<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FeatureRepositoryInterface
{
    // Lấy danh sách feature
    public function getFeatures(): Collection;

    // Lấy thông tin feature theo ID
    public function getFeatureById($id);
    
    // Cập nhật số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id);
}
