<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Services\R2StorageService;
use App\Services\MailService;
use App\Auth\MailController;
use App\Http\Requests\Auth\ChangePassRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{
    //
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly R2StorageService $r2StorageService,
        private readonly MailService $mailService
    ) {}

    // Lấy thông tin người dùng hiện tại
    public function getUserProfile() {
        try {
            $user = $this->userRepository->getUser();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Lấy thông tin người dùng bằng ID
    public function getUserById($id) {
        try {
            $user = $this->userRepository->getUserById($id);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Lấy thông tin người dùng bằng email
    public function getUserByEmail($email) {
        try {
            $user = $this->userRepository->getUserByEmail($email);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Cập nhật tên user
    public function updateName(Request $request) {
        try {
            $user = $this->userRepository->updateName($request);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông tin người dùng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Cập nhật avatar
    public function updateAvatar(Request $request) {
        try {
            $path = "avatars/user/" . Auth::user()->email . '/' . $request->file('image')->getClientOriginalName();
            $this->r2StorageService->upload($path, $request->file('image'));
            $user = $this->userRepository->updateAvatar($this->r2StorageService->getUrlR2() . "/" . $path);
            //$this->r2StorageService->delete($user->avatar_url);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Cập nhật cover image
    public function updateCoverImage(Request $request) {
        try {
            $path = "cover_images/user/" . Auth::user()->email . '/' . $request->file('image')->getClientOriginalName();
            $this->r2StorageService->upload($path, $request->file('image'));
            $user = $this->userRepository->updateCoverImage($this->r2StorageService->getUrlR2() . "/" . $path);
            //$this->r2StorageService->delete($user->cover_image_url);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật cover image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Cập nhật mật khẩu với xác thực mã
    public function updatePassword(Request $request) {
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

            if ($request->verification_code !== $storedCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã xác thực không đúng'
                ], 400);
            }

            // Cập nhật mật khẩu
            $user = $this->userRepository->updatePassword($request);

            // Xóa mã xác thực sau khi đã sử dụng
            Redis::del("password_change_code:{$user->email}");
            Redis::del("password_change_last_sent:{$user->email}");

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công',
                'data' => $user
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật mật khẩu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Kiểm tra mật khẩu hiện tại
    public function checkPassword(Request $request) {
        try
        {
            $request->validate([
                'current_password' => ['required', 'string']
            ]);

            if (!$this->userRepository->checkPassword($request->current_password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không đúng'
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu hiện tại đúng'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra mật khẩu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
