<?php

namespace App\Http\Requests\Reply;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
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
