<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Validation\Rule;

class RecoveryCodeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_id' => [
                'required_without:user_id',
                'string',
            ],
            'user_id' => [
                'required_without:challenge_id',
                'integer',
                'exists:users,id',
            ],
            'code' => [
                'required',
                'string',
                'min:10',
                'max:10',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'challenge_id.required_without' => 'Challenge ID hoặc ID người dùng là bắt buộc.',
            'challenge_id.string' => 'Challenge ID phải là chuỗi.',
            'user_id.required_without' => 'ID người dùng hoặc Challenge ID là bắt buộc.',
            'user_id.integer' => 'ID người dùng phải là số nguyên.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'code.required' => 'Mã khôi phục là bắt buộc.',
            'code.string' => 'Mã khôi phục phải là chuỗi.',
            'code.min' => 'Mã khôi phục phải có 10 ký tự.',
            'code.max' => 'Mã khôi phục phải có 10 ký tự.',
        ];
    }
}
