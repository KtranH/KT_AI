<?php

namespace App\Interfaces;
use App\Models\Image;
use App\Models\Interaction;
use Illuminate\Support\Collection;

interface LikeRepositoryInterface
{        
    /**
     * Kiểm tra xem người dùng đã thích hình ảnh hay chưa
     * @param int $imageId ID của hình ảnh
     * @return bool Kết quả kiểm tra
     */
    public function checkLiked(int $imageId): bool;
    
    /**
     * Lấy danh sách người dùng đã thích hình ảnh
     * @param int $imageId ID của hình ảnh
     * @param int $limit Số lượng người dùng đã thích
     * @return Collection Danh sách người dùng đã thích
     */
    public function getLikes(int $imageId, int $limit = 3): Collection;

    /**
     * Kiểm tra xem hình ảnh có tồn tại hay không
     * @param int $imageId ID của hình ảnh
     * @return Image Hình ảnh
     */
    public function checkImageExist(int $imageId): Image;

    /**
     * Kiểm tra xem tương tác có tồn tại hay không
     * @param int $imageId ID của hình ảnh
     * @return ?Interaction Tương tác
     */
    public function checkInteraction(int $imageId): ?Interaction;
    
    /**
     * Thêm tương tác
     * @param int $imageID ID của hình ảnh
     * @param int $userID ID của người dùng
     * @return Interaction Tương tác
     */
    public function store(int $imageID, int $userID): Interaction;
    
    /**
     * Xóa tương tác
     * @param Interaction $interaction Tương tác
     * @return void
     */
    public function delete(Interaction $interaction): void;
}
