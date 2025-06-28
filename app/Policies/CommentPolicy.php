<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Kiểm tra xem người dùng có quyền cập nhật comment hay không
     * @param User $user Người dùng
     * @param Comment $comment Comment
     * @return bool Kết quả kiểm tra
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Kiểm tra xem người dùng có quyền xóa comment hay không
     * @param User $user Người dùng
     * @param Comment $comment Comment
     * @return bool Kết quả kiểm tra
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
} 