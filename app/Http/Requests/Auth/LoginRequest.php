<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.string' => 'Email phải là chuỗi.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là chuỗi.',
            'cf-turnstile-response.required' => 'Vui lòng xác thực Turnstile.',
            'cf-turnstile-response.string' => 'Turnstile phải là chuỗi.',
        ];
    }
}
