<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Social;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCommentRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     */
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        
        // Chỉ cho phép user sở hữu comment được update
        return $comment && $comment->user_id === Auth::id();
    }

    /**
     * Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'content.required' => 'Nội dung bình luận là bắt buộc.',
            'content.min' => 'Bình luận phải có ít nhất 1 ký tự.',
            'content.max' => 'Bình luận không được vượt quá 1000 ký tự.',
        ]);
    }
} 