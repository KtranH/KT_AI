<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($this->showEmail(), $this->email),
            'avatar' => $this->avatar ?? config('app.default_avatar'),
            'role' => $this->role,
            'stats' => [
                'likes' => $this->sum_like,
                'images' => $this->sum_img,
                'followers' => $this->sum_follower,
                'following' => $this->sum_following,
            ],
            'verified_at' => $this->email_verified_at ? $this->email_verified_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Xác định xem có hiển thị email hay không
     * 
     * @return bool
     */
    protected function showEmail(): bool
    {
        // Chỉ hiển thị email khi là chính người dùng đó hoặc là admin
        return Auth::check() && 
               (Auth::id() === $this->id || Auth::user()->role === 'admin');
    }
} 