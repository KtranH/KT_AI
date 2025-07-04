<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\User;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAvatarRequest extends BaseRequest
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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'], // 10MB
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'image.required' => 'Vui lòng chọn file hình ảnh',
            'image.image' => 'File phải là hình ảnh',
            'image.mimes' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, JPG, WEBP)',
            'image.max' => 'Kích thước file không được vượt quá 10MB',
        ]);
    }
} 