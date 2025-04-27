<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use App\Services\TurnStileService;
use App\Services\MailService;

class AuthService
{
    /**
     * The mail service instance.
     */
    protected MailService $mailService;

    /**
     * The user repository instance.
     */
    protected UserRepository $userRepository;

    /**
     * The turnstile service instance.
     */
    protected TurnStileService $turnStileService;

    /**
     * Create a new service instance.
     */
    public function __construct(
        UserRepository $userRepository,
        TurnStileService $turnStileService,
        MailService $mailService
    ) {
        $this->userRepository = $userRepository;
        $this->turnStileService = $turnStileService;
        $this->mailService = $mailService;
    }

    /**
     * Check authentication status.
     */
    public function checkStatus(): JsonResponse
    {
        $result = $this->userRepository->checkStatus();

        return response()->json($result);
    }

    /**
     * Register a new user.
     *
     * @param Request $request The request object with validated data
     * @param string $turnstileToken Turnstile verification token
     * @param string $clientIp Client IP address
     */
    public function register(Request $request, string $turnstileToken, string $clientIp): JsonResponse
    {
        // Xác thực Turnstile
        $turnstileResponse = $this->turnStileService->verifyTurnstile($turnstileToken, $clientIp);

        if (!$turnstileResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Xác thực không thành công. Vui lòng thử lại.'
            ], 400);
        }

        $user = $this->userRepository->store($request);

        if (!$this->mailService->sendMail($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi mã xác thực'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi mã xác thực'
        ], 200);
    }

    /**
     * Authenticate a user.
     *
     * @param Request $request The request object with login data
     */
    public function login(Request $request): JsonResponse
    {
        // Xác thực Turnstile
        $turnstileResponse = $this->turnStileService->verifyTurnstile(
            $request->input('cf-turnstile-response'),
            $request->ip()
        );

        if (!$turnstileResponse['success']) {
            throw ValidationException::withMessages([
                'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
            ]);
        }

        $remember = $request->boolean('remember');

        if (!Auth::attempt($request->only('email', 'password'), $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        // Lấy user mới nhất từ database
        $user = $this->userRepository->getUser();

        if (!$user->is_verified) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng xác thực email trước khi đăng nhập.',
                'needs_verification' => true,
                'email' => $user->email
            ], 403);
        }

        // Tạo token Sanctum với thời hạn phù hợp với lựa chọn remember_me
        // Nếu người dùng chọn remember_me, token sẽ hết hạn sau 7 ngày (được cấu hình trong sanctum.php)
        // Nếu không chọn remember_me, token sẽ hết hạn khi đóng trình duyệt (session->expire_on_close = true)
        $tokenName = $remember ? 'auth_token_remember' : 'auth_token_session';

        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
            'remember' => $remember
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user && method_exists($user, 'tokens')) {
                // Nếu dùng token-based, xoá toàn bộ token
                $user->tokens()->delete();
            }

            // Nếu dùng session-based (Google OAuth), logout session
            Auth::guard('web')->logout();

            // Đảm bảo regenerate session
            if ($request->session()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Xóa dữ liệu user trong session
            if ($request->session()) {
                $request->session()->forget('user');
            }

            // Lấy CSRF token mới
            $token = csrf_token();

            // Chuẩn bị response với CSRF token mới
            return response()->json([
                'success' => true,
                'message' => 'Đã đăng xuất thành công',
                'csrf_token' => $token
            ])->cookie('XSRF-TOKEN', $token, 120, null, null, null, false);

        } catch (\Exception $e) {
            report($e);
            throw $e;
        }
    }
}