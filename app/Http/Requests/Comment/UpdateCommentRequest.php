<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Policies\CommentPolicy;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Khởi tạo đối tượng
     * @param CommentPolicy $policy Quyền hạn
     */
    public function __construct(private readonly CommentPolicy $policy)
    {
    }

    /**
     * Kiểm tra xem người dùng có quyền thực hiện yêu cầu hay không
     * @return bool Kết quả kiểm tra
     */
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        return $this->policy->update(Auth::user(), $comment);
    }

    /**
     * Quy tắc xác thực
     * @return array Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|max:1000'
        ];
    }
} 