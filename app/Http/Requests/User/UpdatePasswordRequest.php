<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
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
            'password' => [
                'required',
                'confirmed',
                'string',
            ],
            'verification_code' => 'required|string|size:6',
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
            'password.required' => 'Mật khẩu mới là bắt buộc',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'verification_code.required' => 'Mã xác thực là bắt buộc',
            'verification_code.size' => 'Mã xác thực phải có 6 ký tự',
        ];
    }
} 