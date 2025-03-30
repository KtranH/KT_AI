<?php

namespace App\Http\Requests\Auth;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf-turnstile-response' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là chuỗi.',
            'name.max' => 'Họ và tên không được vượt quá 30 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.string' => 'Email phải là chuỗi.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là chuỗi.',
            'password.confirmed' => 'Mật khẩu không khớp.',
            'cf-turnstile-response.required' => 'Vui lòng xác thực Turnstile.',
            'cf-turnstile-response.string' => 'Turnstile phải là chuỗi.',
        ];
    }
}