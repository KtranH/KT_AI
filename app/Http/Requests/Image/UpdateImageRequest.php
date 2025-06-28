<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:30',
            'prompt' => 'string|max:255',
        ];
    }
}
