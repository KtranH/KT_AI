<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Common;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class ResponseResource extends BaseResource
{
    protected bool $success;
    protected string $message;
    protected $data;
    protected ?array $meta;
    protected ?array $errors;

    /**
     * Tạo instance resource mới
     * 
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param array|null $meta
     * @param array|null $errors
     */
    public function __construct(bool $success, string $message, $data = null, ?array $meta = null, ?array $errors = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->meta = $meta;
        $this->errors = $errors;
        
        // Call parent constructor with null resource
        parent::__construct(null);
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        // Add data if provided
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        // Add meta if provided
        if ($this->meta !== null) {
            $response['meta'] = array_merge($response['meta'] ?? [], $this->meta);
        }

        // Add errors if provided (for error responses)
        if ($this->errors !== null) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }

    /**
     * Tạo success response
     */
    public static function success(string $message, $data = null, ?array $meta = null): self
    {
        return new static(true, $message, $data, $meta);
    }

    /**
     * Tạo error response
     */
    public static function error(string $message, ?array $errors = null, ?array $meta = null): self
    {
        return new static(false, $message, null, $meta, $errors);
    }
} 