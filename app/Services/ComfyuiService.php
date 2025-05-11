<?php

namespace App\Services;

use App\Repositories\ImageJobsRepository;
use App\Services\R2Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ComfyuiService
{
    public $max_time = 3000;
    public $imageJobsRepository;
    public $r2Service;
    public function __construct(ImageJobsRepository $imageJobsRepository, R2Service $r2Service)
    {
        $this->imageJobsRepository = $imageJobsRepository;
        $this->r2Service = $r2Service;
    }
    public function generateImage($prompt, $number_out)
    {
        try {
            // Kiểm tra người dùng đang có 5 tiến trình đang hoạt động không
            if ($this->imageJobsRepository->check5Jobs(Auth::user()->id)) {
                return false;
            }
            // Logic để gọi API ComfyUI
            $time = Carbon::now();
            $result_image = null;
            $url = env('COMFYUI_URL') . '/prompt';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'prompt' => $prompt,
            ]);
            $prompt_id = $response->json('prompt_id');
            // Nếu thành công, tạo tiến trình
            if ($response->successful()) {
                $imageJob = $this->imageJobsRepository->createImageJob(Auth::user()->id, $prompt_id);
            }
            // Cập nhật tiến trình
            $this->imageJobsRepository->updateImageJob($imageJob->id, 'processing');
            // Kiểm tra tiến trình
            while (true) {
                $history_respone = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->get(env('COMFYUI_URL') . '/api/history/' . $prompt_id);
                if ($history_respone->successful()) {
                    $this->imageJobsRepository->updateImageJob($imageJob->id, 'done');
                    $result_image = $this->uploadToR2($prompt_id, $number_out, $history_respone->json());
                    $this->imageJobsRepository->updateResultPathImageJob($imageJob->id, $result_image);
                    break;
                }
                else if (Carbon::now()->diffInMilliseconds($time) > $this->max_time) {
                    $this->imageJobsRepository->updateFailedImageJob($imageJob->id, 'Lỗi khi tạo ảnh, quá thời gian tạo ảnh');
                    break;
                }
                sleep(2);
            }
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            $this->imageJobsRepository->updateFailedImageJob($prompt_id, 'Lỗi khi tạo ảnh, quá thời gian tạo ảnh');
            return false;
        }
    }
    public function uploadToR2($prompt_id, $number_out, $data)
    {

        $filename = $data[$prompt_id]['outputs'][$number_out]['images'][0]['filename'];
        $imageUrl = 'http://127.0.0.1:8188/api/view?filename=' . $filename;
        // Tải ảnh từ API về (tạm lưu)
        $tempPath = storage_path("app/tmp/{$filename}");
        $imageContent = file_get_contents($imageUrl);
        file_put_contents($tempPath, $imageContent);

        // Upload ảnh lên R2
        $r2Path = "uploads/generate_image/user/" . Auth::user()->email . '/' . $filename;
        $this->r2Service->upload($r2Path, $tempPath, 'public');

        // Xóa file tạm
        unlink($tempPath);
        return $this->r2Service->getUrlR2() . "/" . $r2Path;
    }
}
