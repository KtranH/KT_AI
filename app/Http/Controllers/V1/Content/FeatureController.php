<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Content;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Services\Business\FeatureService;
use Illuminate\Http\JsonResponse;

class FeatureController extends BaseV1Controller
{
    protected FeatureService $featureService;
    
    public function __construct(FeatureService $featureService) 
    {
        $this->featureService = $featureService;
    }

    /**
     * Lấy tất cả các AI features
     * 
     * @return JsonResponse
     */
    public function getFeatures(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->featureService->getFeatures(),
            null,
            ErrorMessages::FEATURE_LOAD_ERROR
        );
    }

    /**
     * Lấy một AI feature theo ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getFeatureById(int $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->featureService->getFeatureById($id),
            null,
            ErrorMessages::FEATURE_LOAD_ERROR
        );
    }
} 