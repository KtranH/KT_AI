<?php

namespace App\Http\Requests\Reply;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $isReply = $this->route('comment') !== null;
        
        $rules = [
            'content' => 'required|string|max:1000',
        ];
        
        if (!$isReply) {
            $rules['parent_id'] = 'required|exists:comments,id';
        }
        
        return $rules;
    }
}
