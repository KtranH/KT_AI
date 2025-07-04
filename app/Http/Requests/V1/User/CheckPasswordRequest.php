<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\User;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class CheckPasswordRequest extends BaseRequest
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
            'current_password' => 'required|string',
        ];
    }

    /**
     * Lỗi xác thực
     * @return array Lỗi xác thực
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Mật khẩu hiện tại là bắt buộc',
            'current_password.string' => 'Mật khẩu phải là chuỗi ký tự',
        ];
    }
} 