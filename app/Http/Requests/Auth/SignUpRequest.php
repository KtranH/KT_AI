<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Policies\UserPolicy;

class SignUpRequest extends FormRequest
{
    public function __construct(private readonly UserPolicy $policy)
    {
    }
    public function authorize(): bool
    {
        return true;
    }

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
