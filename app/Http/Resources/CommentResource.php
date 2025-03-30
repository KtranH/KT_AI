<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        $userId = Auth::id();
        $listLike = json_decode($this->list_like ?? '[]', true);
        
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
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
} 