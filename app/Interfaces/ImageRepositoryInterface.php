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
    public function getImages(int $id): array;
    
    // Lấy danh sách hình ảnh theo feature
    public function getImagesByFeature(int $featureId, int $perPage = 5): array;
        
    // Lấy danh sách hình ảnh đã thích
    public function getImagesLiked(): Collection;

    //Lấy danh sách hình ảnh đã tải lên
    public function getImagesUploaded(): Collection;
    
    // Phân trang các hình ảnh đã thích
    public function getImagesLikedPaginated($id = null, int $perPage = 5, int $page = 1): LengthAwarePaginator;

    // Phân trang các hình ảnh đã tải lên
    public function getImagesUploadedPaginated($id = null, int $perPage = 5, int $page = 1): LengthAwarePaginator;

    // Lưu trữ hình ảnh tải lên
    public function storeImage($uploadedPaths, $user, $data): bool;
    
    // Cập nhật thông tin hình ảnh
    public function updateImage(Image $image, string $title, string $prompt): bool;
    
    // Xóa hình ảnh
    public function deleteImage(Image $image): bool;
    
    // Tăng số lượng ảnh cho mục Feature
    public function increaseSumImg(int $featureId): void;
    
    // Tăng số lượng ảnh cho user
    public function increaseSumImgUser(int $userId): void;
    
    // Giảm số lượng ảnh cho mục Feature
    public function decreaseSumImg(int $featureId): void;
    
    // Giảm số lượng ảnh cho user
    public function decreaseSumImgUser(int $userId): void;

    // Tăng số lượng like
    public function incrementSumLike(int $imageId): void;
    
    // Giảm số lượng like
    public function decrementSumLike(int $imageId): void;
} 