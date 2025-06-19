<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use App\Services\R2StorageService;
use App\Services\MailService;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Requests\Auth\ChangePassRequest;


class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected R2StorageService $r2StorageService;
    protected MailService $mailService;
    public function __construct(UserRepositoryInterface $userRepository, R2StorageService $r2StorageService, MailService $mailService) {
        $this->userRepository = $userRepository;
        $this->r2StorageService = $r2StorageService;
        $this->mailService = $mailService;
    }
    public function getUserProfile()
    {
        return $this->userRepository->getUser();
    }
    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }
    public function getUserByEmail($email)
    {
        return $this->userRepository->getUserByEmail($email);
    }
    public function checkCredits()
    {
        return $this->userRepository->checkCredits();
    }
    public function checkPassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'string']
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

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
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $exception->errors()
            ], 422);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi kiểm tra mật khẩu: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra mật khẩu'
            ], 500);
        }
    }
    public function updateName(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            // Xác thực dữ liệu
            $name = $request->input('name');
            if (empty($name) || strlen($name) > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên không hợp lệ'
                ], 422);
            }

            // Cập nhật tên trực tiếp
            $user->name = $name;
            
            // Cập nhật hoạt động
            $activities = json_decode($user->activities, true) ?? [];
            $activities[] = [
                'action' => "Cập nhật tên tài khoản thành {$name}",
                'timestamp' => now()
            ];
            $user->activities = json_encode($activities);

            // Sử dụng repository để lưu
            $updatedUser = $this->userRepository->updateName($activities, $user);

            return response()->json([
                'success' => true,
                'data' => $updatedUser
            ]);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi cập nhật tên người dùng: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật tên người dùng'
            ], 500);
        }
    }
    public function updatePassword(Request $request)
    {
        $changePassRequest = new ChangePassRequest();
        try {
            $request->validate($changePassRequest->rules());

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }
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
            $user->password = Hash::make($password);
            $activities = json_decode($user->activities, true) ?? [];
            $activities[] = [
                'action' => 'Cập nhật mật khẩu',
                'timestamp' => now()
            ];

            // Sửa lại cách gọi phương thức để phù hợp với triển khai trong repository
            $user = $this->userRepository->updatePassword($activities, $user);

            // Xóa mã xác thực sau khi đã sử dụng
            Redis::del("password_change_code:{$user->email}");
            Redis::del("password_change_last_sent:{$user->email}");

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công',
                'data' => $user
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $exception->errors()
            ], 422);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi cập nhật mật khẩu: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật mật khẩu'
            ], 500);
        }
    }
    public function updateAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn file hình ảnh'
                ], 422);
            }

            $file = $request->file('image');
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không hợp lệ'
                ], 422);
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, JPG)'
                ], 422);
            }

            // Validate file size (max 2MB)
            if ($file->getSize() > 2 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kích thước file không được vượt quá 2MB'
                ], 422);
            }

            // Tạo tên file duy nhất
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "avatars/user/" . $user->email . '/' . $fileName;
            
            // Upload file lên R2
            $this->r2StorageService->upload($path, $file);

            // Lưu URL mới vào database
            $fullPath = $this->r2StorageService->getUrlR2() . "/" . $path;

            // Cập nhật hoạt động
            $activities = json_decode($user->activities, true) ?? [];
            $activities[] = [
                'action' => "Cập nhật avatar",
                'timestamp' => now()
            ];
            $user->activities = json_encode($activities);
            $user = $this->userRepository->updateAvatar($fullPath);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi cập nhật avatar: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật avatar'
            ], 500);
        }
    }

    public function updateCoverImage(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy file hình ảnh'
                ], 422);
            }

            $file = $request->file('image');
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không hợp lệ'
                ], 422);
            }

            $path = "cover_images/user/" . $user->email . '/' . $file->getClientOriginalName();
            $this->r2StorageService->upload($path, $file);

            // Lưu URL mới vào database
            $fullPath = $this->r2StorageService->getUrlR2() . "/" . $path;

            // Cập nhật hoạt động
            $activities = json_decode($user->activities, true) ?? [];
            $activities[] = [
                'action' => "Cập nhật ảnh bìa",
                'timestamp' => now()
            ];
            $user->activities = json_encode($activities);
            $user = $this->userRepository->updateCoverImage($fullPath);

            // Có thể xóa ảnh bìa cũ nếu cần
            // $this->r2StorageService->delete($user->cover_image_url);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi cập nhật ảnh bìa: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật ảnh bìa'
            ], 500);
        }
    }
}
