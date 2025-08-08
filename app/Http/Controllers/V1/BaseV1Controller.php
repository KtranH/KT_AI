<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="KT AI API Documentation",
 *     description="API documentation cho hệ thống KT AI",
 *     @OA\Contact(
 *         email="admin@ktai.com",
 *         name="KT AI Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
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
     * Thực thi service method với tài liệu tự động
     */
    protected function executeServiceMethodV1(callable $serviceMethod, ?string $successMessage = null, ?string $errorMessage = null): JsonResponse
    {
        try {
            $result = $serviceMethod();
            
            $response = [
                'success' => true,
                'message' => $successMessage ?? 'Thành công',
                'data' => $result
            ];
            
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => $errorMessage ?? $e->getMessage(),
                'data' => null
            ];
            
            return response()->json($response, 500);
        }
    }

    /**
     * Auto-generate Swagger documentation for CRUD operations
     */
    protected function autoDocumentCrud(string $resource, string $model, array $fields = []): void
    {
        // Phương thức này sẽ được sử dụng để tự động tạo tài liệu Swagger cho các hoạt động CRUD
        // Thực hiện sẽ được thêm dựa trên phân tích route
    }
} 