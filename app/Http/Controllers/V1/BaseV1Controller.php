<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Constants\ResponseCode;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;

abstract class BaseV1Controller extends Controller
{
    /**
     * API version cho response headers
     */
    protected string $apiVersion = 'v1';
    
    /**
     * Thêm version header vào response
     * 
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function withVersionHeader($response)
    {
        return $response->header('X-API-Version', $this->apiVersion);
    }
    
    /**
     * Tạo response thành công với version header
     * 
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponseV1($data = null, ?string $message = null, int $statusCode = 200)
    {
        $response = $this->successResponse($data, $message, $statusCode);
        return $this->withVersionHeader($response);
    }
    
    /**
     * Tạo error response với version header
     * 
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponseV1(string $message, int $statusCode = 400, $errors = null)
    {
        $response = $this->errorResponse($message, $statusCode, $errors);
        return $this->withVersionHeader($response);
    }
    
    /**
     * Thực thi service method với version header
     * 
     * @param callable $serviceMethod
     * @param string|null $successMessage
     * @param string $context
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeServiceMethodV1(callable $serviceMethod, ?string $successMessage = null, string $context = '')
    {
        $response = $this->executeServiceMethod($serviceMethod, $successMessage, $context);
        return $this->withVersionHeader($response);
    }
} 