<?php

namespace App\Observers;

use App\Models\Interaction;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class InteractionObserver
{
    /**
     * Xử lý sự kiện "deleting" của Interaction 
     * @param Interaction $interaction Interaction
     * @return void
     */
    public function deleting(Interaction $interaction): void
    {
        try {
            // Xóa thông báo like liên quan đến interaction này
            $deletedCount = DatabaseNotification::where('data->interaction_id', $interaction->id)->delete();
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa thông báo cho interaction ID: {$interaction->id} - " . $e->getMessage());
        }
    }

    /**
     * Xử lý sự kiện "deleted" của Interaction
     * @param Interaction $interaction Interaction
     * @return void
     */
    public function deleted(Interaction $interaction): void
    {
        Log::info("Interaction ID: {$interaction->id} đã được xóa hoàn toàn");
    }
} 