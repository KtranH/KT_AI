<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUserProfile() {
        try {
            $user = $this->userService->getUserProfile();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi tải thông tin người dùng: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng'
            ], 500);
        }
    }

    public function getUserById($id) {
        try {
            // Xác thực ID
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID không hợp lệ'
                ], 400);
            }

            $user = $this->userService->getUserById($id);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng'
            ], 500);
        }
    }

    public function getUserByEmail($email) {
        try {
            // Xác thực email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email không hợp lệ'
                ], 400);
            }

            $user = $this->userService->getUserByEmail($email);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin người dùng'
            ], 500);
        }
    }

    public function updateName(Request $request) {
        try {
            // Xác thực dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:30',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra xác thực người dùng
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            $user = $this->userService->updateName($request);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông tin người dùng'
            ], 500);
        }
    }

    public function updateAvatar(Request $request) {
        try {
            // Xác thực file
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra xác thực người dùng
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            $result = $this->userService->updateAvatar($request);
            return $result;
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật avatar'
            ], 500);
        }
    }

    public function updateCoverImage(Request $request) {
        try {
            // Xác thực file
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra xác thực người dùng
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            $result = $this->userService->updateCoverImage($request);
            return $result;
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật ảnh bìa'
            ], 500);
        }
    }

    public function updatePassword(Request $request) {
        try {
            // Kiểm tra xác thực người dùng
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            $result = $this->userService->updatePassword($request);
            return $result;
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật mật khẩu'
            ], 500);
        }
    }

    public function checkPassword(Request $request) {
        try {
            // Xác thực dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra xác thực người dùng
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            $result = $this->userService->checkPassword($request);
            return $result;
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra mật khẩu'
            ], 500);
        }
    }
}
