<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;

class LoginRequest extends BaseRequest
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
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'cf-turnstile-response' => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'cf-turnstile-response.required' => 'Vui lòng xác minh Turnstile.',
        ]);
    }
}
