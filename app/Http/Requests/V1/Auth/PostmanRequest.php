<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseRequest;

class PostmanRequest extends BaseRequest
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
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ];
    }
}
