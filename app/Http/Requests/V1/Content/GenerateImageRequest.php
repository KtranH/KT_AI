<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Content;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;

class GenerateImageRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:10', 'max:1000'],
            'width' => ['required', 'integer', 'min:512', 'max:2048'],
            'height' => ['required', 'integer', 'min:512', 'max:2048'],
            'seed' => ['required', 'integer', 'min:0'],
            'style' => ['nullable', 'string', 'max:100'],
            'feature_id' => ['required', 'integer', 'exists:ai_features,id'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'secondary_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'prompt.required' => 'Prompt mô tả ảnh là bắt buộc.',
            'prompt.min' => 'Prompt phải có ít nhất 10 ký tự.',
            'prompt.max' => 'Prompt không được vượt quá 1000 ký tự.',
            'width.required' => 'Chiều rộng ảnh là bắt buộc.',
            'width.integer' => 'Chiều rộng phải là số nguyên.',
            'width.min' => 'Chiều rộng tối thiểu là 512px.',
            'width.max' => 'Chiều rộng tối đa là 2048px.',
            'height.required' => 'Chiều cao ảnh là bắt buộc.',
            'height.integer' => 'Chiều cao phải là số nguyên.',
            'height.min' => 'Chiều cao tối thiểu là 512px.',
            'height.max' => 'Chiều cao tối đa là 2048px.',
            'seed.required' => 'Seed là bắt buộc.',
            'seed.integer' => 'Seed phải là số nguyên.',
            'seed.min' => 'Seed phải lớn hơn hoặc bằng 0.',
            'feature_id.required' => 'Vui lòng chọn AI feature.',
            'feature_id.exists' => 'AI feature không tồn tại.',
            'main_image.image' => 'File ảnh chính phải là hình ảnh.',
            'main_image.mimes' => 'Ảnh chính phải có định dạng: jpeg, png, jpg, webp.',
            'main_image.max' => 'Kích thước ảnh chính không được vượt quá 10MB.',
            'secondary_image.image' => 'File ảnh phụ phải là hình ảnh.',
            'secondary_image.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, webp.',
            'secondary_image.max' => 'Kích thước ảnh phụ không được vượt quá 10MB.',
        ]);
    }

    /**
     * Validation bổ sung để kiểm tra user requirements
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = Auth::user();
            
            if (!$user) {
                $validator->errors()->add('auth', 'Bạn cần đăng nhập để thực hiện hành động này.');
                return;
            }

            if (!$user->is_verified) {
                $validator->errors()->add('verification', 'Bạn cần xác minh email để tạo ảnh.');
                return;
            }

            if (($user->remaining_credits ?? 0) < 1) {
                $validator->errors()->add('credits', 'Bạn không có đủ credits để tạo ảnh. Credits hiện tại: ' . ($user->remaining_credits ?? 0));
            }
        });
    }
} 