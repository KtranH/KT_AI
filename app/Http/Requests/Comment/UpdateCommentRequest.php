<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Policies\CommentPolicy;

class UpdateCommentRequest extends FormRequest
{
    public function __construct(private readonly CommentPolicy $policy)
    {
    }
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        return $this->policy->update(Auth::user(), $comment);
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:1000'
        ];
    }
} 