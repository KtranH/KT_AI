<?php

namespace App\Http\Requests\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JobImageRequest extends FormRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules()
    {
        return [
            'prompt' => 'required|string|max:1000',
            'width' => 'required|integer|min:64|max:2048',
            'height' => 'required|integer|min:64|max:2048',
            'seed' => 'required|numeric',
            'style' => 'nullable|string',
            'feature_id' => 'required|exists:ai_features,id',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'secondary_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Validation bổ sung để kiểm tra credits
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();
            
            if (!$user) {
                $validator->errors()->add('auth', 'Bạn cần đăng nhập để thực hiện hành động này.');
                return;
            }

            if (!$user->is_verified) {
                $validator->errors()->add('verification', 'Bạn cần xác minh email để tạo ảnh.');
                return;
            }

            if ($user->remaining_credits < 1) {
                $validator->errors()->add('credits', 'Bạn không có đủ credits để tạo ảnh. Credits hiện tại: ' . $user->remaining_credits);
            }
        });
    }
}
