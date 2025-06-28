<?php

namespace App\Policies;

use App\Models\Image;
use App\Models\User;

class ImagePolicy
{
    /**
     * Kiểm tra xem người dùng có quyền xem tất cả ảnh hay không
     * @param User $user Người dùng
     * @return bool Kết quả kiểm tra
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Kiểm tra xem người dùng có quyền xem ảnh hay không
     * @param User $user Người dùng
     * @param Image $image Ảnh
     * @return bool Kết quả kiểm tra
     */
    public function view(User $user, Image $image): bool
    {
        return false;
    }

    /**
     * Kiểm tra xem người dùng có quyền tạo ảnh hay không
     * @param User $user Người dùng
     * @return bool Kết quả kiểm tra
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Kiểm tra xem người dùng có quyền cập nhật ảnh hay không
     * @param User $user Người dùng
     * @param Image $image Ảnh
     * @return bool Kết quả kiểm tra
     */
    public function update(User $user, Image $image): bool
    {
        return $user->id === $image->user_id;
    }

    /**
     * Kiểm tra xem người dùng có quyền xóa ảnh hay không
     * @param User $user Người dùng
     * @param Image $image Ảnh
     * @return bool Kết quả kiểm tra
     */
    public function delete(User $user, Image $image): bool
    {
        return $user->id === $image->user_id;
    }

    /**
     * Kiểm tra xem người dùng có quyền khôi phục ảnh hay không
     * @param User $user Người dùng
     * @param Image $image Ảnh
     * @return bool Kết quả kiểm tra
     */
    public function restore(User $user, Image $image): bool
    {
        return false;
    }

    /**
     * Kiểm tra xem người dùng có quyền xóa ảnh vĩnh viễn hay không
     * @param User $user Người dùng
     * @param Image $image Ảnh
     * @return bool Kết quả kiểm tra
     */
    public function forceDelete(User $user, Image $image): bool
    {
        return false;
    }
}
