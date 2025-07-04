<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\User;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateNameRequest extends BaseRequest
{
    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:30',
        ];
    }

    /**
     * Lỗi xác thực
     * @return array Lỗi xác thực
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên là bắt buộc',
            'name.string' => 'Tên phải là chuỗi ký tự',
            'name.max' => 'Tên không được vượt quá 30 ký tự',
        ];
    }
} 