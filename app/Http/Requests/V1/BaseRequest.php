<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Override trong child class nếu cần authorization logic
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     * Customized để trả về JSON response format chuẩn cho API V1
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        $response = new JsonResponse([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $errors,
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ]
        ], 422);

        $response->header('X-API-Version', 'v1');

        throw new HttpResponseException($response);
    }

    /**
     * Common validation rules có thể sử dụng trong child classes
     */
    protected function commonRules(): array
    {
        return [
            'email' => ['email:rfc,dns', 'max:255'],
            'password' => ['string', 'min:8', 'max:255'],
            'name' => ['string', 'max:255'],
            'url' => ['url', 'max:500'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB
            'file' => ['file', 'max:51200'], // 50MB
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'required' => 'Trường :attribute là bắt buộc.',
            'string' => 'Trường :attribute phải là chuỗi.',
            'email' => 'Trường :attribute phải là email hợp lệ.',
            'min' => 'Trường :attribute phải có ít nhất :min ký tự.',
            'max' => 'Trường :attribute không được vượt quá :max ký tự.',
            'unique' => 'Trường :attribute đã tồn tại.',
            'confirmed' => 'Xác nhận :attribute không khớp.',
            'image' => 'Trường :attribute phải là hình ảnh.',
            'mimes' => 'Trường :attribute phải có định dạng: :values.',
            'url' => 'Trường :attribute phải là URL hợp lệ.',
            'integer' => 'Trường :attribute phải là số nguyên.',
            'numeric' => 'Trường :attribute phải là số.',
            'boolean' => 'Trường :attribute phải là true hoặc false.',
            'in' => 'Trường :attribute không hợp lệ.',
        ];
    }

    /**
     * Helper method để merge rules với common rules
     */
    protected function mergeCommonRules(array $specificRules): array
    {
        $commonRules = $this->commonRules();
        
        // Merge và override common rules với specific rules
        foreach ($specificRules as $field => $rules) {
            if (isset($commonRules[$field])) {
                // Nếu field tồn tại trong common rules, merge với specific rules
                $specificRules[$field] = array_merge($commonRules[$field], (array)$rules);
            }
        }

        return $specificRules;
    }

    /**
     * Helper method để sanitize input data
     */
    protected function sanitizeInput(array $fields = []): array
    {
        $data = $this->all();
        
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim(strip_tags($data[$field]));
            }
        }

        return $data;
    }

    /**
     * Helper method để check file upload
     */
    protected function hasFileUpload(string $field): bool
    {
        return $this->hasFile($field) && $this->file($field)->isValid();
    }
} 