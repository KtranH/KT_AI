<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Swagger\SwaggerGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSwaggerDocs extends Command
{
    /**
     * Tên và signature của lệnh console.
     *
     * @var string
     */
    protected $signature = 'swagger:generate {--force : Force regenerate}';

    /**
     * Mô tả của lệnh console.
     *
     * @var string
     */
    protected $description = 'Auto-generate Swagger documentation from routes and controllers';

    /**
     * Thực thi lệnh console.
     */
    public function handle(SwaggerGeneratorService $swaggerService): int
    {
        $this->info('🔄 Generating Swagger documentation...');

        try {
            // Generate documentation from routes
            $docs = $swaggerService->generateFromRoutes();
            
            // Generate OpenAPI spec
            $openApiSpec = $this->generateOpenApiSpec($docs);
            
            // Save to file
            $this->saveDocumentation($openApiSpec);
            
            $this->info('✅ Swagger documentation generated successfully!');
            $this->info('📖 Access at: http://localhost:8000/api/documentation');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error generating documentation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Tạo specification OpenAPI
     */
    private function generateOpenApiSpec(array $docs): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'KT AI API Documentation',
                'description' => 'API documentation cho hệ thống KT AI',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'KT AI Team',
                    'email' => 'admin@ktai.com'
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT'
                ]
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'API Server'
                ]
            ],
            'security' => [
                [
                    'bearerAuth' => []
                ]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ]
            ],
            'paths' => $this->generatePaths($docs)
        ];
    }

    /**
     * Tạo paths từ tài liệu
     */
    private function generatePaths(array $docs): array
    {
        $paths = [];
        
        foreach ($docs as $doc) {
            $path = $doc['path'];
            $method = $doc['method'];
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = [
                'summary' => $doc['summary'],
                'description' => $doc['description'],
                'tags' => $doc['tags'],
                'parameters' => $this->formatParameters($doc['parameters']),
                'responses' => $doc['responses']
            ];
        }
        
        return $paths;
    }

    /**
     * Định dạng tham số cho OpenAPI
     */
    private function formatParameters(array $parameters): array
    {
        $formatted = [];
        
        foreach ($parameters as $param) {
            $formatted[] = [
                'name' => $param['name'],
                'in' => 'query',
                'required' => $param['required'],
                'schema' => [
                    'type' => $this->mapPhpTypeToOpenApi($param['type'])
                ]
            ];
        }
        
        return $formatted;
    }

    /**
     * Ánh xạ kiểu PHP sang kiểu OpenAPI
     */
    private function mapPhpTypeToOpenApi(string $phpType): string
    {
        return match ($phpType) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'string' => 'string',
            'array' => 'array',
            default => 'string'
        };
    }

    /**
     * Lưu tài liệu vào file
     */
    private function saveDocumentation(array $spec): void
    {
        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $path = storage_path('api-docs/api-docs.json');
        
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $json);
        
        $this->info("📄 Documentation saved to: {$path}");
    }
}
