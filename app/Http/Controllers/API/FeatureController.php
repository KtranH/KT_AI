<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Interfaces\FeatureRepositoryInterface;

class FeatureController extends Controller
{
    public function __construct(private readonly FeatureRepositoryInterface $featureRepository) {}
    //
    public function getFeatures()
    {
        try {            
            $features = $this->featureRepository->getFeatures();
            return response()->json([
                'success' => true,
                'data' => $features,
                'total' => $features->count()
            ], 200, [
                'Content-Type' => 'application/json'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải chức năng: ' . $e->getMessage());
            
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
            $feature = $this->featureRepository->getFeatureById($id);
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
