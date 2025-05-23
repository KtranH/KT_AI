<?php

namespace App\Repositories;

use App\Models\ImageJob;
class ImageJobsRepository
{
    public function check5Jobs($userId)
    {
        return ImageJob::where('user_id', $userId)->where('status', '!=', 'done')->count() >= 5;
    }
    public function createImageJob($userId, $prompt)
    {
        return ImageJob::create([
            'user_id' => $userId,
            'prompt' => $prompt,
            'status' => 'pending',
        ]);
    }
    public function updateImageJob($id, $status)
    {
        return ImageJob::where('id', $id)->update([
            'status' => $status,
        ]);
    }
    public function updateResultPathImageJob($id, $resultPath)
    {
        return ImageJob::where('id', $id)->update([
            'result_path' => $resultPath,
        ]);
    }
    public function updateFailedImageJob($id, $errorMessage)
    {
        return ImageJob::where('id', $id)->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
    public function getImageJobs()
    {
        return ImageJob::all();
    }
}
