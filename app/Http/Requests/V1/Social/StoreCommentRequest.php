<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Social;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class StoreCommentRequest extends BaseRequest
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
            'image_id' => ['required', 'integer', 'exists:images,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'origin_comment' => ['nullable', 'integer', 'exists:comments,id'],
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
            'image_id.required' => 'Vui lòng chọn ảnh để bình luận.',
            'image_id.exists' => 'Ảnh không tồn tại.',
            'parent_id.exists' => 'Bình luận cha không tồn tại.',
            'origin_comment.exists' => 'Bình luận gốc không tồn tại.',
        ]);
    }
} 