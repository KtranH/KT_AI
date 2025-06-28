<?php

namespace App\Http\Requests\Image;
use Illuminate\Foundation\Http\FormRequest;

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
}
