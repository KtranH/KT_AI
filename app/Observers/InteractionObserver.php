<?php

namespace App\Observers;

use App\Models\Interaction;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class InteractionObserver
{
    /**
     * Handle the Interaction "deleting" event.
     */
    public function deleting(Interaction $interaction): void
    {
        try {
            // Xóa thông báo like liên quan đến interaction này
            $deletedCount = DatabaseNotification::where('data->interaction_id', $interaction->id)->delete();
            
            Log::info("Đã xóa {$deletedCount} thông báo liên quan đến interaction ID: {$interaction->id}");
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa thông báo cho interaction ID: {$interaction->id} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Interaction "deleted" event.
     */
    public function deleted(Interaction $interaction): void
    {
        Log::info("Interaction ID: {$interaction->id} đã được xóa hoàn toàn");
    }
} 