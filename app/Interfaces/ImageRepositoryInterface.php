<?php

namespace App\Interfaces;

use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ImageRepositoryInterface
{
    /**
     * Tìm hình ảnh theo ID
     * @param int $id ID của hình ảnh
     * @return array Danh sách hình ảnh
     */
    public function getImages(int $id): array;
    
    /**
     * Lấy danh sách hình ảnh theo feature
     * @param int $featureId ID của feature
     * @param int $perPage Số lượng hình ảnh trên mỗi trang
     * @param string $sortBy Cách sắp xếp (newest, oldest, most_liked)
     * @return array Danh sách hình ảnh
     */
    public function getImagesByFeature(int $featureId, int $perPage = 10, string $sortBy = 'newest'): array;
        
    /**
     * Phân trang các hình ảnh đã thích
     * @param int $id ID của hình ảnh
     * @param int $perPage Số lượng hình ảnh trên mỗi trang
     * @param int $page Trang hiện tại
     * @return LengthAwarePaginator Phân trang các hình ảnh đã thích
     */
    public function getImagesLikedPaginated($id = null, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    /**
     * Phân trang các hình ảnh đã tải lên
     * @param int $id ID của hình ảnh
     * @param int $perPage Số lượng hình ảnh trên mỗi trang
     * @param int $page Trang hiện tại
     * @return LengthAwarePaginator Phân trang các hình ảnh đã tải lên
     */
    public function getImagesUploadedPaginated($id = null, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    /**
     * Phân trang các hình ảnh do người dùng tạo
     * @param int $id ID của người dùng
     * @param int $perPage Số lượng hình ảnh trên mỗi trang
     * @param int $page Trang hiện tại
     * @return LengthAwarePaginator Phân trang các hình ảnh đã tạo
     */
    public function getImagesCreatedByUserPaginated($id = null, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    /**
     * Lưu trữ hình ảnh tải lên
     * @param array $uploadedPaths Đường dẫn của hình ảnh
     * @param User $user Người dùng
     * @param array $data Dữ liệu của hình ảnh
     * @return bool Kết quả lưu trữ
     */
    public function storeImage($uploadedPaths, $user, $data): bool;

    /**
     * Cập nhật thông tin hình ảnh
     * @param Image $image Hình ảnh
     * @param string $title Tiêu đề của hình ảnh
     * @param string $prompt Prompt của hình ảnh
     * @return bool Kết quả cập nhật
     */
    public function updateImage(Image $image, string $title, string $prompt): bool;

    /**
     * Xóa hình ảnh
     * @param Image $image Hình ảnh
     * @return bool Kết quả xóa
     */
    public function deleteImage(Image $image): bool;
    
    /**
     * Tăng số lượng ảnh cho mục Feature
     * @param int $featureId ID của feature
     * @return void
     */
    public function increaseSumImg(int $featureId): void;
    
    /**
     * Tăng số lượng ảnh cho user
     * @param int $userId ID của user
     * @return void
     */
    public function increaseSumImgUser(int $userId): void;
    
    /**
     * Giảm số lượng ảnh cho mục Feature
     * @param int $featureId ID của feature
     * @return void
     */
    public function decreaseSumImg(int $featureId): void;
    
    /**
     * Giảm số lượng ảnh cho user
     * @param int $userId ID của user
     * @return void
     */
    public function decreaseSumImgUser(int $userId): void;

    /**
     * Tăng số lượng like
     * @param int $imageId ID của hình ảnh
     * @return void
     */
    public function incrementSumLike(int $imageId): void;
    
    /**
     * Giảm số lượng like
     * @param int $imageId ID của hình ảnh
     * @return void
     */
    public function decrementSumLike(int $imageId): void;

    /**
     * Kiểm tra xem có hình ảnh mới kể từ timestamp
     */
    public function hasNewImagesForUser(int $userId, int $timestampAfter): bool;
} 