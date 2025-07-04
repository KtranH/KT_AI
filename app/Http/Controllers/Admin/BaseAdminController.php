<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;

abstract class BaseAdminController extends Controller
{
    /**
     * Constructor - middleware admin auth có thể được thêm ở đây
     */
    public function __construct()
    {
        // Có thể thêm middleware admin auth ở đây
        // $this->middleware('admin.auth');
    }
    
    /**
     * Kiểm tra quyền admin
     * 
     * @return bool
     */
    protected function hasAdminPermission(): bool
    {
        // Implement logic kiểm tra quyền admin
        return true; // Placeholder
    }
    
    /**
     * Validate admin permission và trả về error response nếu không có quyền
     * 
     * @return \Illuminate\Http\JsonResponse|null
     */
    protected function validateAdminPermission()
    {
        if (!$this->hasAdminPermission()) {
            return $this->errorResponse(ErrorMessages::PERMISSION_DENIED, 403);
        }
        
        return null;
    }
    
    /**
     * Admin log action for audit trail
     * 
     * @param string $action
     * @param array $data
     * @return void
     */
    protected function logAdminAction(string $action, array $data = []): void
    {
        // Implement admin action logging
        \Log::info("Admin Action: {$action}", $data);
    }
} 