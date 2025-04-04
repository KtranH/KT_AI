<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ReplyResource extends JsonResource
{
    public function toArray($request): array
    {
        $userId = Auth::id();
        $listLike = $this->list_like ?? [];
        
        return [
            'id' => $this->id,
            'username' => $this->user->name,
            'avatar' => $this->user->avatar_url,
            'text' => $this->content,
            'time' => $this->created_at->diffForHumans(),
            'likes' => $this->sum_like,
            'isLiked' => in_array($userId, $listLike),
            'isOwner' => $userId === $this->user_id,
            'showAllReplies' => true,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->when($this->parent, function() {
                return $this->parent->user->name ?? null;
            }, null),
            'nested_replies' => $this->when($this->nested_replies, function() {
                return ReplyResource::collection($this->nested_replies);
            }, []),
        ];
    }
}
