<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FeatureService;

class FeatureController extends Controller
{
    protected FeatureService $featureService;
    public function __construct(FeatureService $featureService) {
        $this->featureService = $featureService;
    }
    public function getFeatures()
    {
        try {            
            $features = $this->featureService->getFeatures();
            return response()->json([
                'success' => true,
                'data' => $features,
                'total' => $features->count()
            ], 200, [
                'Content-Type' => 'application/json'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu chức năng',
                'error' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }
    public function getFeatureById($id)
    {
        try {
            $feature = $this->featureService->getFeatureById($id);
            if ($feature) {
                return response()->json([
                    'success' => true,
                    'data' => $feature
                ], 200, [
                    'Content-Type' => 'application/json'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chức năng'
                ], 404, [
                    'Content-Type' => 'application/json'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu chức năng',
                'error' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }
}
