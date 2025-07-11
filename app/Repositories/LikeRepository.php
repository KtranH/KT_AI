<?php

namespace App\Repositories;

use App\Interfaces\LikeRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


class LikeRepository implements LikeRepositoryInterface
{
    /**
     * Kiểm tra xem user đã thích ảnh hay chưa
     * @param int $imageId ID của ảnh
     * @return bool Kết quả
     */
    public function checkLiked(int $imageId): bool
    {
        return Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->exists();
    }

    /**
     * Lấy danh sách người thích ảnh
     * @param int $imageId ID của ảnh
     * @param int $limit Số lượng người thích
     * @return Collection Danh sách người thích
     */
    public function getLikes(int $imageId, int $limit = 3): Collection
    {
        return Interaction::with('user')
            ->where('image_id', $imageId)
            ->where('type_interaction', 'like')
            ->take($limit)
            ->get();
    }

    /**
     * Kiểm tra xem ảnh có tồn tại hay không
     * @param int $imageId ID của ảnh
     * @return Image ảnh
     */
    public function checkImageExist(int $imageId): Image
    {
        return Image::findOrFail($imageId);
    }

    /**
     * Kiểm tra xem user đã thích ảnh hay chưa
     * @param int $imageId ID của ảnh
     * @return Interaction Interaction
     */
    public function checkInteraction(int $imageId): ?Interaction
    {
        return Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->first();
    }

    /**
     * Lưu thông tin thích ảnh
     * @param int $imageID ID của ảnh
     * @param int $userID ID của user
     * @return Interaction Interaction
     */
    public function store($imageID, $userID): Interaction
    {
        $interaction = new Interaction();
        $interaction->image_id = $imageID;
        $interaction->user_id = $userID;
        $interaction->status_interaction = 'active';
        $interaction->type_interaction = 'like';
        $interaction->save();
        return $interaction;
    }

    /**
     * Xóa thông tin thích ảnh
     * @param Interaction $interaction Interaction
     * @return void
     */
    public function delete(Interaction $interaction): void
    {
        if ($interaction) {
            $interaction->delete();
        }
    }
}