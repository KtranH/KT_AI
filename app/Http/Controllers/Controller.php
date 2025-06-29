<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Traits\ExceptionHandlerTrait;

abstract class Controller
{
    use ApiResponseTrait, ExceptionHandlerTrait;
    
    /**
     * Thực thi phương thức dịch vụ với xử lý lỗi đồng nhất
     * 
     * @param callable $serviceMethod
     * @param string|null $successMessage
     * @param string $context Mô tả ngữ cảnh để logging
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeServiceMethod(callable $serviceMethod, ?string $successMessage = null, string $context = '')
    {
        return $this->executeAndRespond($serviceMethod, $context, $successMessage);
    }
    
    /**
     * Thực thi phương thức dịch vụ đã trả về JsonResponse
     * Dùng cho các dịch vụ trả về phản hồi định dạng
     * 
     * @param callable $serviceMethod
     * @param string $context Mô tả ngữ cảnh để logging
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeServiceJsonMethod(callable $serviceMethod, string $context = '')
    {
        return $this->executeAndRespond($serviceMethod, $context);
    }
    
    /**
     * Kiểm tra ID có phải là số hay không
     * 
     * @param mixed $id
     * @return bool
     */
    protected function isValidId($id): bool
    {
        return is_numeric($id) && $id > 0;
    }
    
    /**
     * Kiểm tra định dạng email
     * 
     * @param string $email
     * @return bool
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
