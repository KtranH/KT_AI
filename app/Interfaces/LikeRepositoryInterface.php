<?php

namespace App\Interfaces;
use Illuminate\Support\Collection;

interface LikeRepositoryInterface
{
    // Thích một hình ảnh
    public function likePost(int $imageId): void;
    
    // Bỏ thích một hình ảnh
    public function unlikePost(int $imageId): void;
    
    // Kiểm tra xem người dùng đã thích hình ảnh hay chưa
    public function checkLiked(int $imageId): bool;
    
    // Lấy danh sách người dùng đã thích hình ảnh
    public function getLikes(int $imageId, int $limit = 3): Collection;
}
