<?php

namespace App\Observers;

use App\Models\Image;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class ImageObserver
{
    /**
     * Handle the Image "deleting" event.
     */
    public function deleting(Image $image): void
    {
        try {
            // Xóa tất cả thông báo liên quan đến hình ảnh này
            $deletedCount = DatabaseNotification::where('data->image_id', $image->id)->delete();
            
            Log::info("Đã xóa {$deletedCount} thông báo liên quan đến hình ảnh ID: {$image->id}");
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa thông báo cho hình ảnh ID: {$image->id} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Image "deleted" event.
     */
    public function deleted(Image $image): void
    {
        Log::info("Hình ảnh ID: {$image->id} đã được xóa hoàn toàn");
    }
} 