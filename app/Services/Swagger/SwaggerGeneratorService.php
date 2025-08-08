<?php

declare(strict_types=1);

namespace App\Services\Swagger;

use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;

class SwaggerGeneratorService
{
    /**
     * Tự động tạo tài liệu Swagger từ các route
     */
    public function generateFromRoutes(): array
    {
        $routes = Route::getRoutes();
        $documentation = [];

        foreach ($routes as $route) {
            if ($this->isApiRoute($route)) {
                $controller = $route->getController();
                $method = $route->getActionMethod();
                
                if ($controller && $method) {
                    $doc = $this->generateMethodDocumentation($controller, $method, $route);
                    if ($doc) {
                        $documentation[] = $doc;
                    }
                }
            }
        }

        return $documentation;
    }

    /**
     * Kiểm tra xem route có phải là route API không
     */
    private function isApiRoute($route): bool
    {
        $uri = $route->uri();
        return str_starts_with($uri, 'api/');
    }

    /**
     * Tạo tài liệu cho một phương thức
     */
    private function generateMethodDocumentation($controller, string $method, $route): ?array
    {
        $reflection = new ReflectionClass($controller);
        $methodReflection = $reflection->getMethod($method);
        
        $docComment = $methodReflection->getDocComment();
        if (!$docComment) {
            return null;
        }

        // Phân tích tài liệu phương thức
        $httpMethod = $this->getHttpMethod($route);
        $path = '/api/' . $route->uri();
        $summary = $this->extractSummary($docComment);
        $description = $this->extractDescription($docComment);
        
        return [
            'method' => $httpMethod,
            'path' => $path,
            'summary' => $summary,
            'description' => $description,
            'tags' => [$this->extractTag($controller)],
            'parameters' => $this->extractParameters($methodReflection),
            'responses' => $this->generateDefaultResponses()
        ];
    }

    /**
     * Lấy phương thức HTTP từ route
     */
    private function getHttpMethod($route): string
    {
        $methods = $route->methods();
        return strtolower($methods[0] ?? 'GET');
    }

    /**
     * Lấy tóm tắt từ doc comment
     */
    private function extractSummary(string $docComment): string
    {
        preg_match('/\*\s*(.+)/', $docComment, $matches);
        return $matches[1] ?? 'API Endpoint';
    }

    /**
     * Lấy mô tả từ doc comment
     */
    private function extractDescription(string $docComment): string
    {
        $lines = explode("\n", $docComment);
        $description = '';
        
        foreach ($lines as $line) {
            if (str_contains($line, '@param') || str_contains($line, '@return')) {
                break;
            }
            $description .= trim($line, " *\t") . ' ';
        }
        
        return trim($description);
    }

    /**
     * Lấy tag từ tên class controller
     */
    private function extractTag($controller): string
    {
        $className = get_class($controller);
        $parts = explode('\\', $className);
        $controllerName = end($parts);
        
        return str_replace('Controller', '', $controllerName);
    }

    /**
     * Lấy tham số từ phương thức reflection
     */
    private function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                'required' => !$param->isOptional()
            ];
        }
        
        return $parameters;
    }

    /**
     * Tạo các phản hồi mặc định
     */
    private function generateDefaultResponses(): array
    {
        return [
            '200' => [
                'description' => 'Thành công',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean', 'example' => true],
                                'message' => ['type' => 'string'],
                                'data' => ['type' => 'object']
                            ]
                        ]
                    ]
                ]
            ],
            '401' => [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean', 'example' => false],
                                'message' => ['type' => 'string', 'example' => 'Unauthorized']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
