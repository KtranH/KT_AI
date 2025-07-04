<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\User;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCoverImageRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Lỗi xác thực
     * @return array Lỗi xác thực
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Vui lòng chọn file hình ảnh',
            'image.image' => 'File phải là hình ảnh',
            'image.mimes' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, JPG)',
            'image.max' => 'Kích thước file không được vượt quá 2MB',
        ];
    }
} 