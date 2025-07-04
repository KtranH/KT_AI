<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Content;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateImageRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     */
    public function authorize(): bool
    {
        $image = $this->route('image');
        return $image && $image->user_id === Auth::id();
    }

    /**
     * Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'prompt' => ['nullable', 'string', 'max:1000'],
            'privacy_status' => ['nullable', 'string', 'in:public,private'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'title.required' => 'Tiêu đề ảnh là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 100 ký tự.',
            'prompt.max' => 'Prompt không được vượt quá 1000 ký tự.',
            'privacy_status.in' => 'Trạng thái riêng tư phải là public hoặc private.',
        ]);
    }
} 