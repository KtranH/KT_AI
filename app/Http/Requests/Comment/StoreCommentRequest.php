<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $isReply = $this->route('comment') !== null;
        
        $rules = [
            'content' => 'required|string|max:1000',
        ];
        
        if (!$isReply) {
            $rules['image_id'] = 'required|exists:images,id';
            $rules['parent_id'] = 'nullable|exists:comments,id';
        }
        
        return $rules;
    }
} 