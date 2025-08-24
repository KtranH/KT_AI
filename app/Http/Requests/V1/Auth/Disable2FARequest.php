<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Validation\Rule;

class Disable2FARequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã xác thực là bắt buộc.',
            'code.string' => 'Mã xác thực phải là chuỗi.',
            'code.size' => 'Mã xác thực phải có 6 ký tự.',
            'code.regex' => 'Mã xác thực chỉ được chứa số.',
        ];
    }
}
