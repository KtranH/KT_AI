<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends BaseRequest
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
            'name' => 'required|string|max:30',
            'email' => 'required|email:dns|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->mixedCase()
                    ->uncompromised()
            ],
            'cf-turnstile-response' => ['required', 'string']
        ];
    }
}
