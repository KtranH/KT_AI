<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AIFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeatureController extends Controller
{
    //
    public function load_feature()
    {
        try {
            Log::info('Bắt đầu tải danh sách chức năng');
            
            $features = AIFeature::where('status_feature', 'active')
                ->orderBy('id')            
                ->get();

            Log::info('Đã tải ' . $features->count() . ' chức năng');

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
}
