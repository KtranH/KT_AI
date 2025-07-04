<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Social;

use App\Http\Resources\V1\BaseResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentResource extends BaseResource
{
    /**
     * Chuyển đổi comment thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin comment
     */
    public function toArray($request): array
    {
        $userId = Auth::id();
        $listLike = $this->list_like ?? [];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'username' => $this->user->name,
            'avatar' => $this->user->avatar_url,
            'text' => $this->content,
            'time' => $this->created_at->diffForHumans(),
            'likes' => $this->sum_like,
            'isLiked' => in_array($userId, $listLike),
            'isOwner' => $userId === $this->user_id,
            'replies' => ReplyResource::collection($this->whenLoaded('replies')),
            'reply_count' => $this->whenLoaded('replies', function() {
                return $this->replies->count();
            }, 0),
            'hasMoreReplies' => $this->whenLoaded('replies', function() {
                return count($this->replies) >= 3;
            }, false),
            'parent_id' => $this->parent_id,
            'origin_comment' => $this->origin_comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
} 