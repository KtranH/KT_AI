<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Social;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyResource extends BaseResource
{
    /**
     * Chuyển đổi reply thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin reply
     */
    public function toArray(Request $request): array
    {
        $userId = Auth::id();
        $listLike = $this->resource->list_like ?? [];

        return [
            'id' => $this->id,
            'username' => $this->user->name,
            'userid' => $this->user_id,
            'avatar' => $this->user->avatar_url,
            'text' => $this->content,
            'time' => $this->created_at->diffForHumans(),
            'likes' => $this->sum_like,
            'isLiked' => in_array($userId, $listLike),
            'isOwner' => $userId === $this->user_id,
            'parent_id' => $this->parent_id,
            'origin_comment' => $this->origin_comment,
            'reply_to' => $this->when($this->parent && $this->parent->user, function() {
                return [
                    'id' => $this->parent->user->id,
                    'name' => $this->parent->user->name
                ];
            }),
            'origin_comment_info' => $this->when($this->originComment && $this->originComment->user, function() {
                return [
                    'id' => $this->originComment->id,
                    'user_id' => $this->originComment->user->id,
                    'username' => $this->originComment->user->name
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
} 