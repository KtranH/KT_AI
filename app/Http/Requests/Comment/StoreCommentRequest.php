<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'image_id' => 'required|exists:images,id',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ];
    }
} 