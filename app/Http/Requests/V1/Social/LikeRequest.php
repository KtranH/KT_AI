<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Social;

use App\Http\Requests\V1\BaseRequest;
use Illuminate\Support\Facades\Auth;

class LikeRequest extends BaseRequest
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
            'likeable_type' => ['required', 'string', 'in:comment,image'],
            'likeable_id' => ['required', 'integer'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'likeable_type.required' => 'Loại đối tượng like là bắt buộc.',
            'likeable_type.in' => 'Loại đối tượng like chỉ có thể là comment hoặc image.',
            'likeable_id.required' => 'ID đối tượng like là bắt buộc.',
            'likeable_id.integer' => 'ID đối tượng like phải là số nguyên.',
        ]);
    }
} 