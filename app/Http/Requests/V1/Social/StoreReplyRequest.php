<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Social;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class StoreReplyRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id' => ['required', 'integer', 'exists:comments,id'],
            'origin_comment' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'content.required' => 'Nội dung phản hồi là bắt buộc.',
            'content.min' => 'Phản hồi phải có ít nhất 1 ký tự.',
            'content.max' => 'Phản hồi không được vượt quá 1000 ký tự.',
            'parent_id.required' => 'ID bình luận cha là bắt buộc.',
            'parent_id.exists' => 'Bình luận cha không tồn tại.',
            'origin_comment.exists' => 'Bình luận gốc không tồn tại.',
        ]);
    }
} 