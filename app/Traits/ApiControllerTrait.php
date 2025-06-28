<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait ApiControllerTrait
{
    /**
     * Execute service method với comprehensive error handling
     */
    protected function executeServiceWithErrorHandling(callable $serviceMethod, ?string $successMessage = null, string $errorMessage = 'Có lỗi xảy ra'): JsonResponse
    {
        try {
            $result = $serviceMethod();
            return $this->successResponse($result, $successMessage);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Không tìm thấy dữ liệu', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, $errorMessage);
        }
    }
    
    /**
     * Validate multiple IDs
     */
    protected function validateIds(array $ids): bool
    {
        return collect($ids)->every(fn($id) => $this->isValidId($id));
    }
    
    /**
     * Get paginated response format
     */
    protected function paginatedResponse($data, ?string $message = null): JsonResponse
    {
        if (method_exists($data, 'toArray')) {
            $paginationData = $data->toArray();
            
            return $this->successResponse([
                'data' => $paginationData['data'],
                'pagination' => [
                    'current_page' => $paginationData['current_page'],
                    'last_page' => $paginationData['last_page'],
                    'per_page' => $paginationData['per_page'],
                    'total' => $paginationData['total'],
                    'from' => $paginationData['from'],
                    'to' => $paginationData['to']
                ]
            ], $message);
        }
        
        return $this->successResponse($data, $message);
    }
    
    /**
     * Bulk operation response
     */
    protected function bulkOperationResponse(array $results, string $operation): JsonResponse
    {
        $successful = collect($results)->where('status', 'success')->count();
        $failed = collect($results)->where('status', 'error')->count();
        
        return $this->successResponse([
            'summary' => [
                'total' => count($results),
                'successful' => $successful,
                'failed' => $failed
            ],
            'details' => $results
        ], "Bulk {$operation} completed: {$successful} successful, {$failed} failed");
    }
    
    /**
     * Cache response với headers
     */
    protected function cachedResponse($data, ?string $message = null, int $cacheMinutes = 5): JsonResponse
    {
        $response = $this->successResponse($data, $message);
        
        return $response->header('Cache-Control', "public, max-age=" . ($cacheMinutes * 60))
                      ->header('Expires', now()->addMinutes($cacheMinutes)->toRfc2822String());
    }
    
    /**
     * Rate limited response
     */
    protected function rateLimitedResponse(string $message = 'Too many requests'): JsonResponse
    {
        return $this->errorResponse($message, 429)
                   ->header('Retry-After', 60);
    }
    
    /**
     * Validation helper
     */
    protected function validateRequest(array $rules, array $data): array
    {
        $validator = validator($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Execute service method nếu có quyền
     */
    protected function executeWithPermission(string $permission, callable $serviceMethod, ?string $successMessage = null): JsonResponse
    {
        if (!Auth::check() || !Gate::allows($permission)) {
            return $this->errorResponse('Không có quyền thực hiện hành động này', 403);
        }
        
        return $this->executeServiceWithErrorHandling($serviceMethod, $successMessage);
    }
} 