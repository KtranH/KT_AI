<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Validation\Rule;

class Verify2FARequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'challenge_id' => [
                'required',
                'string',
            ],
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
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.integer' => 'ID người dùng phải là số nguyên.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'challenge_id.required' => 'Challenge ID là bắt buộc.',
            'challenge_id.string' => 'Challenge ID phải là chuỗi.',
            'code.required' => 'Mã xác thực là bắt buộc.',
            'code.string' => 'Mã xác thực phải là chuỗi.',
            'code.size' => 'Mã xác thực phải có 6 ký tự.',
            'code.regex' => 'Mã xác thực chỉ được chứa số.',
        ];
    }
}
