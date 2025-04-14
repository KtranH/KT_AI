<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:30',
            'prompt' => 'required|string|max:255',
        ];
    }
}
