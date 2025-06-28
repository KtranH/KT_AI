<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules(): array
    {
        $isReply = $this->route('comment') !== null;

        $rules = [
            'content' => 'required|string|max:150',
        ];

        if (!$isReply) {
            $rules['image_id'] = 'required|exists:images,id';
            $rules['parent_id'] = 'nullable|exists:comments,id';
            $rules['origin_comment'] = 'nullable|exists:comments,id';
        }

        return $rules;
    }
}