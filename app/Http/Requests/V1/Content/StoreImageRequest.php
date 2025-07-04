<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Content;

use App\Http\Requests\V1\BaseRequest;

class StoreImageRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'privacy_status' => ['nullable', 'string', 'in:public,private'],
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB per image
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'title.required' => 'Tiêu đề ảnh là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 100 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
            'privacy_status.in' => 'Trạng thái riêng tư phải là public hoặc private.',
            'images.required' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.array' => 'Dữ liệu ảnh không hợp lệ.',
            'images.min' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.max' => 'Không được upload quá 10 ảnh cùng lúc.',
            'images.*.image' => 'File phải là ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Kích thước ảnh không được vượt quá 10MB.',
        ]);
    }
} 