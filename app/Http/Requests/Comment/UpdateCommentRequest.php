<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        return Auth::id() === $comment->user_id;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:1000'
        ];
    }
} 