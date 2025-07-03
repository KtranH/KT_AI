<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Services\Business\FeatureService;
use Illuminate\Http\JsonResponse;

class FeatureController extends Controller
{
    protected FeatureService $featureService;
    public function __construct(FeatureService $featureService) {
        $this->featureService = $featureService;
    }
    /**
     * Lấy tất cả các chức năng
     * 
     * @return JsonResponse
     */
    public function getFeatures(): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->featureService->getFeatures(),
            null,
            ErrorMessages::FEATURE_LOAD_ERROR
        );
    }
    /**
     * Lấy một chức năng theo ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getFeatureById($id): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->featureService->getFeatureById($id),
            null,
            ErrorMessages::FEATURE_LOAD_ERROR
        );
    }
}
