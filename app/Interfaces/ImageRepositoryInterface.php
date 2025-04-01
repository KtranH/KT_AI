<?php

namespace App\Interfaces;

use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ImageRepositoryInterface
{
    // Tìm hình ảnh theo ID
    public function getImages(int $id): ?Image;
    
    // Lấy danh sách hình ảnh theo feature
    public function getImagesByFeature(int $featureId, int $perPage = 5): LengthAwarePaginator;
    
    // Lấy danh sách hình ảnh của người dùng
    public function getImagesCreatedByUser(): Collection;
    
    // Lưu trữ hình ảnh tải lên
    public function storeImage($uploadedPaths, $user, $data): bool;
    
    // Tăng số lượng ảnh cho mục Feature
    public function increaseSumImg(int $featureId): void;
    
    // Tăng số lượng ảnh cho user
    public function increaseSumImgUser(int $userId): void;
    
    // Giảm số lượng ảnh cho mục Feature
    public function decreaseSumImg(int $featureId): void;
    
    // Giảm số lượng ảnh cho user
    public function decreaseSumImgUser(int $userId): void;
} 