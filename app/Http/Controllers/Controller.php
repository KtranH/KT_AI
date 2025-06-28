<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;

abstract class Controller
{
    use ApiResponseTrait;
    
    /**
     * Thực thi phương thức dịch vụ với xử lý lỗi đồng nhất
     * 
     * @param callable $serviceMethod
     * @param string|null $successMessage
     * @param string $errorMessage
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeServiceMethod(callable $serviceMethod, ?string $successMessage = null, string $errorMessage = 'Có lỗi xảy ra')
    {
        try {
            $result = $serviceMethod();
            return $this->successResponse($result, $successMessage);
        } catch (\Exception $e) {
            return $this->handleException($e, $errorMessage);
        }
    }
    
    /**
     * Thực thi phương thức dịch vụ đã trả về JsonResponse
     * Dùng cho các dịch vụ trả về phản hồi định dạng
     * 
     * @param callable $serviceMethod
     * @param string $errorMessage
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeServiceJsonMethod(callable $serviceMethod, string $errorMessage = 'Có lỗi xảy ra')
    {
        try {
            return $serviceMethod(); // Return directly without wrapping
        } catch (\Exception $e) {
            return $this->handleException($e, $errorMessage);
        }
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
