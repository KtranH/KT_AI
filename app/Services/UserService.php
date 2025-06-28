<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Services\R2StorageService;
use App\Services\MailService;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Requests\User\UpdateNameRequest;
use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdateCoverImageRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\CheckPasswordRequest;

/**
 * Service class xử lý các chức năng liên quan đến User
 * Đã được tối ưu hóa để sử dụng Request classes và loại bỏ validation thủ công
 */
class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected R2StorageService $r2StorageService;
    protected MailService $mailService;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        R2StorageService $r2StorageService, 
        MailService $mailService
    ) {
        $this->userRepository = $userRepository;
        $this->r2StorageService = $r2StorageService;
        $this->mailService = $mailService;
    }

    /**
     * Lấy thông tin profile của user hiện tại
     * @return mixed
     */
    public function getUserProfile()
    {
        return $this->userRepository->getUser();
    }

    /**
     * Lấy thông tin user theo ID
     * @param mixed $id
     * @return mixed
     */
    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }

    /**
     * Lấy thông tin user theo email
     * @param mixed $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        return $this->userRepository->getUserByEmail($email);
    }

    /**
     * Kiểm tra số credits của user hiện tại
     * @return mixed
     */
    public function checkCredits()
    {
        return $this->userRepository->checkCredits();
    }

    /**
     * Kiểm tra mật khẩu hiện tại của user
     * @param CheckPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(CheckPasswordRequest $request)
    {
        $currentPassword = $request->input('current_password');
        
        if (!$this->userRepository->checkPassword($currentPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu hiện tại đúng'
        ]);
    }

    /**
     * Cập nhật tên của user
     * @param UpdateNameRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateName(UpdateNameRequest $request)
    {
        $user = Auth::user();
        $name = $request->input('name');
        
        // Cập nhật tên trực tiếp
        $user->name = $name;
        
        // Cập nhật hoạt động
        $activities = json_decode($user->activities, true) ?? [];
        $activities[] = [
            'action' => "Cập nhật tên tài khoản thành {$name}",
            'timestamp' => now()
        ];
        
        // Sử dụng repository để lưu
        $updatedUser = $this->userRepository->updateName($activities, $user);

        return response()->json([
            'success' => true,
            'data' => $updatedUser
        ]);
    }

    /**
     * Cập nhật mật khẩu của user
     * @param UpdatePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();
        
        // Kiểm tra mã xác thực
        $storedCode = Redis::get("password_change_code:{$user->email}");
        if (!$storedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực đã hết hạn hoặc không tồn tại'
            ], 400);
        }

        $verificationCode = $request->input('verification_code');
        if ($verificationCode !== $storedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không đúng'
            ], 400);
        }

        // Cập nhật mật khẩu
        $password = $request->input('password');
        $user->password = $password;
        $activities = json_decode($user->activities, true) ?? [];
        $activities[] = [
            'action' => 'Cập nhật mật khẩu',
            'timestamp' => now()
        ];

        // Cập nhật thông qua repository
        $user = $this->userRepository->updatePassword($activities, $user);

        // Xóa mã xác thực sau khi đã sử dụng
        Redis::del("password_change_code:{$user->email}");
        Redis::del("password_change_last_sent:{$user->email}");

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công',
            'data' => $user
        ]);
    }

    /**
     * Cập nhật avatar của user
     * @param UpdateAvatarRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $user = Auth::user();
        $file = $request->file('image');
        
        // Tạo tên file duy nhất
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = "avatars/user/" . $user->email . '/' . $fileName;
        
        // Upload file lên R2
        $this->r2StorageService->upload($path, $file);

        // Lưu URL mới vào database
        $fullPath = $this->r2StorageService->getUrlR2() . "/" . $path;

        // Cập nhật thông qua repository
        $user = $this->userRepository->updateAvatar($fullPath);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Cập nhật ảnh bìa của user
     * @param UpdateCoverImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCoverImage(UpdateCoverImageRequest $request)
    {
        $user = Auth::user();
        $file = $request->file('image');
        
        $path = "cover_images/user/" . $user->email . '/' . $file->getClientOriginalName();
        $this->r2StorageService->upload($path, $file);

        // Lưu URL mới vào database
        $fullPath = $this->r2StorageService->getUrlR2() . "/" . $path;

        // Cập nhật thông qua repository
        $user = $this->userRepository->updateCoverImage($fullPath);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
