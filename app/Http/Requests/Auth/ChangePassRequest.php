<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePassRequest extends FormRequest
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
            'current_password' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                'string',
                /*Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->mixedCase()
                    ->uncompromised()*/
            ],
            'verification_code' => 'required|string|size:6',
        ];
    }
}