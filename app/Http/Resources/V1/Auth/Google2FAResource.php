<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Auth;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class Google2FAResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'is_enabled' => $this->resource['is_enabled'] ?? false,
            'recovery_codes' => $this->resource['recovery_codes'] ?? [],
            'created_at' => $this->resource['created_at'] ?? null,
            'updated_at' => $this->resource['updated_at'] ?? null,
        ];
    }
}
