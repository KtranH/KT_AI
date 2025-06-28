<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => ['required', 'string'],
        ];
    }
}
