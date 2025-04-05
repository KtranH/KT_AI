<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReplyResource;

class CommentResource extends JsonResource
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
            'replies' => ReplyResource::collection($this->whenLoaded('replies')),
            'reply_count' => $this->whenLoaded('replies', function() {
                return $this->replies->count();
            }, 0),
            'created_at' => $this->created_at,
        ];
    }
} 