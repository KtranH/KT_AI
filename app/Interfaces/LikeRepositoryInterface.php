<?php

namespace App\Interfaces;
use Illuminate\Support\Collection;

interface LikeRepositoryInterface
{        
    // Kiểm tra xem người dùng đã thích hình ảnh hay chưa
    public function checkLiked(int $imageId): bool;
    
    // Lấy danh sách người dùng đã thích hình ảnh
    public function getLikes(int $imageId, int $limit = 3): Collection;

    // Kiểm tra xem hình ảnh có tồn tại hay không
    public function checkImageExist(int $imageId);

    // Kiểm tra xem tương tác có tồn tại hay không
    public function checkInteraction(int $imageId);
}
