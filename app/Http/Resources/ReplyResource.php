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
            'parent_id' => $this->parent_id,
            'reply_to' => $this->when($this->parent && $this->parent->user, function() {
                return [
                    'id' => $this->parent->user->id,
                    'name' => $this->parent->user->name
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
