<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Thêm version header vào response
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ]
        ];
    }

    /**
     * Customize response wrapper
     *
     * @param Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     */
    public function withResponse(Request $request, $response): void
    {
        $response->header('X-API-Version', 'v1');
    }

    /**
     * Format timestamps theo chuẩn ISO 8601
     *
     * @param mixed $timestamp
     * @return string|null
     */
    protected function formatTimestamp($timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        return is_string($timestamp) 
            ? $timestamp 
            : $timestamp->toISOString();
    }

    /**
     * Format URL với domain đầy đủ
     *
     * @param string|null $path
     * @return string|null
     */
    protected function formatUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_starts_with($path, 'http') 
            ? $path 
            : asset($path);
    }

    /**
     * Conditional include data khi có giá trị
     *
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    protected function whenHasValue($value, $default = null)
    {
        return $this->when($value !== null, $value, $default);
    }
} 