<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    //
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly R2StorageService $r2StorageService) {}

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
}
