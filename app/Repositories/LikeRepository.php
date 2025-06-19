<?php

namespace App\Repositories;

use App\Interfaces\LikeRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


class LikeRepository implements LikeRepositoryInterface
{
    public function checkLiked(int $imageId): bool
    {
        return Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->exists();
    }
    public function getLikes(int $imageId, int $limit = 3): Collection
    {
        return Interaction::with('user')
            ->where('image_id', $imageId)
            ->where('type_interaction', 'like')
            ->take($limit)
            ->get();
    }
    public function checkImageExist(int $imageId): Image
    {
        return Image::findOrFail($imageId);
    }
    public function checkInteraction(int $imageId): ?Interaction
    {
        return Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->first();
    }
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
    public function delete(Interaction $interaction): void
    {
        if ($interaction) {
            $interaction->delete();
        }
    }
}