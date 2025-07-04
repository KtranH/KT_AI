<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\BaseCollection;

class UserCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'data' => UserResource::collection($this->collection),
        ];
    }
} 