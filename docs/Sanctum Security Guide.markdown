# Hướng Dẫn Bảo Mật Sử Dụng Laravel Sanctum Trong Dự Án KT_AI

## Giới Thiệu

Laravel Sanctum là một giải pháp xác thực nhẹ nhàng được phát triển bởi Laravel, cung cấp nhiều phương thức xác thực khác nhau cho các ứng dụng SPA (Single Page Applications), ứng dụng di động cũng như API đơn giản dựa trên token. Trong dự án KT_AI, Sanctum được sử dụng như một lớp bảo mật chính để xác thực người dùng và bảo vệ các endpoint API.

Tài liệu này sẽ giải thích chi tiết về cách Laravel Sanctum được triển khai và sử dụng trong dự án KT_AI, cũng như các phương pháp tốt nhất để đảm bảo an ninh cho ứng dụng.

## Kiến Trúc Bảo Mật

### Các Thành Phần Chính

1. **Sanctum**: Cung cấp hệ thống xác thực token và cookie-based cho SPA
2. **Personal Access Tokens**: Token API được lưu trữ trong cơ sở dữ liệu cho xác thực API
3. **CSRF Protection**: Bảo vệ chống tấn công Cross-Site Request Forgery
4. **Stateful Authentication**: Xác thực dựa trên cookie cho SPA
5. **TurnStile Service**: Tích hợp Cloudflare Turnstile để bảo vệ khỏi botnet và tấn công tự động

### Luồng Xác Thực

1. **Đăng ký/Đăng nhập**: Người dùng cung cấp thông tin xác thực
2. **Kiểm tra và xác minh**: Hệ thống kiểm tra thông tin với cơ sở dữ liệu
3. **Tạo token**: Tạo token khi xác thực thành công
4. **Truy cập API**: Sử dụng token để truy cập các tài nguyên được bảo vệ
5. **Đăng xuất**: Hủy token khi người dùng đăng xuất

## Cấu Hình Sanctum

### Cấu Hình Cơ Bản (config/sanctum.php)

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),

'guard' => ['web'],

'expiration' => 10080, // 7 ngày (7 * 24 * 60 = 10080 phút)

'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

'middleware' => [
    'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

### Cấu Hình Guard API (config/auth.php)

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

### Cấu Hình CORS (config/cors.php)

```php
'paths' => ['api/*', 'auth/google/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://127.0.0.1:8000'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Triển Khai Sanctum Trong Dự Án

### Model User Và HasApiTokens

Trong Model User (app/Models/User.php), trait HasApiTokens được sử dụng để cung cấp các phương thức làm việc với Sanctum tokens:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // ...
}
```

### Migration Cho Personal Access Tokens

Sanctum yêu cầu một bảng `personal_access_tokens` để lưu trữ các API token:

```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```

### Middleware Xác Thực

KT_AI sử dụng middleware tùy chỉnh (app/Http/Middleware/AuthenticateMiddleware.php) để xác thực người dùng:

```php
public function handle(Request $request, Closure $next): Response
{
    // Kiểm tra xác thực
    if (!Auth::check()) {
        // Kiểm tra token từ API request
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            
            if (!$token) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Token không hợp lệ hoặc đã hết hạn.',
                        'status' => 401
                    ], 401);
                }
                return redirect()->route('login');
            }
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => 401
            ], 401);
        }
        return redirect()->route('login');
    }

    return $next($request);
}
```

## Xác Thực Người Dùng

### Đăng Ký Người Dùng

Khi người dùng đăng ký, quá trình sau đây được thực hiện trong AuthService:

```php
public function register(Request $request)
{
    $signUpRequest = new SignUpRequest();
    $request->validate($signUpRequest->rules());

    // Xác thực Turnstile
    $turnstileResponse = $this->turnStileService->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());

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
        'message' => 'Đã gửi mã xác thực'],
        200);
}
```

### Đăng Nhập Và Tạo Token

Khi người dùng đăng nhập, KT_AI sẽ tạo token Sanctum với thời hạn phù hợp dựa trên việc người dùng có chọn "nhớ đăng nhập" hay không:

```php
public function login(Request $request)
{
    // Validate login request và Turnstile...
    
    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => ['Thông tin đăng nhập không chính xác.'],
        ]);
    }
    
    // Kiểm tra xác thực email...
    
    // Tạo token Sanctum với thời hạn phù hợp với lựa chọn remember_me
    $tokenName = 'auth_token';

    if ($request->boolean('remember')) {
        // Nếu người dùng chọn remember_me, token sẽ hết hạn sau 7 ngày 
        $tokenName = 'auth_token_remember';
    } else {
        // Nếu không chọn remember_me, token sẽ hết hạn khi đóng trình duyệt
        $tokenName = 'auth_token_session';
    }

    $token = $user->createToken($tokenName)->plainTextToken;

    return response()->json([
        'message' => 'Đăng nhập thành công',
        'user' => $user,
        'token' => $token,
        'remember' => $request->boolean('remember')
    ]);
}
```

### Đăng Xuất Và Thu Hồi Token

Khi người dùng đăng xuất, tất cả các token được thu hồi để đảm bảo an toàn:

```php
public function logout(Request $request)
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

        return response()->json([
            'message' => 'Đã đăng xuất thành công',
            'csrf_token' => $token
        ])->cookie('XSRF-TOKEN', $token, 120, null, null, null, false);

    } catch (\Exception $e) {
        report($e);
        return response()->json(['message' => 'Đã xảy ra lỗi khi đăng xuất: ' . $e->getMessage()], 500);
    }
}
```

## Bảo Vệ Routes Và Endpoint API

### Route Bảo Vệ Bởi Sanctum

Dự án KT_AI sử dụng middleware `auth:sanctum` để bảo vệ các routes yêu cầu xác thực:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Like Routes
    Route::get('/check_liked/{id}', [LikeController::class, 'checkLiked']);
    Route::post('/like_post/{id}', [LikeController::class, 'likePost']);
    Route::post('/unlike_post/{id}', [LikeController::class, 'unlikePost']);
    
    // Comment Routes
    Route::get('/images/{imageId}/comments', [CommentController::class, 'getComments']);
    Route::post('/comments', [CommentController::class, 'store']);
    // ... và nhiều routes khác
});
```

### API Routes

Routes API cũng được bảo vệ bởi Sanctum:

```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

### Broadcast Authentication Channels

Các kênh broadcasting cũng được bảo vệ bằng Sanctum:

```php
// Thêm route cho xác thực kênh broadcast
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);
```

## Biện Pháp Bảo Mật Bổ Sung

### 1. Xác Thực Turnstile (Cloudflare)

Dự án KT_AI sử dụng Cloudflare Turnstile để bảo vệ các form đăng ký, đăng nhập khỏi các tấn công tự động:

```php
// Xác thực Turnstile
$turnstileResponse = $this->turnStileService->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());

if (!$turnstileResponse['success']) {
    throw ValidationException::withMessages([
        'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
    ]);
}
```

### 2. Xác Minh Email

Người dùng phải xác minh email trước khi có thể đăng nhập:

```php
if (!$user->is_verified) {
    Auth::logout();
    return response()->json([
        'message' => 'Vui lòng xác thực email trước khi đăng nhập.',
        'needs_verification' => true,
        'email' => $user->email
    ], 403);
}
```

### 3. CSRF Protection

Sanctum đã tích hợp bảo vệ CSRF mặc định cho SPA:

```php
'middleware' => [
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

### 4. Token Expiration

Token có thời gian sống được định cấu hình:

```php
'expiration' => 10080, // 7 ngày (7 * 24 * 60 = 10080 phút)
```

## Sử Dụng Sanctum Trong Frontend

### Cấu Hình Axios

Để sử dụng Sanctum token trong các yêu cầu API từ frontend, axios cần được cấu hình để gửi `X-XSRF-TOKEN` cookie và token bearer:

```javascript
// Cấu hình Axios
axios.defaults.withCredentials = true; // Cho phép gửi cookies với request
axios.interceptors.request.use(config => {
    // Thêm token vào header nếu có
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
});
```

### Lưu Trữ Token

Sau khi đăng nhập thành công, token được lưu trữ trong localStorage hoặc sessionStorage tùy thuộc vào tùy chọn "nhớ đăng nhập":

```javascript
// Trong store auth
const login = async (credentials) => {
    try {
        const response = await axios.post('/api/login', credentials);
        const { token, user, remember } = response.data;
        
        // Lưu token vào localStorage nếu remember = true, ngược lại vào sessionStorage
        if (remember) {
            localStorage.setItem('auth_token', token);
        } else {
            sessionStorage.setItem('auth_token', token);
        }
        
        // Cập nhật state
        state.token = token;
        state.user = user;
        state.isAuthenticated = true;
        
        return response;
    } catch (error) {
        // Xử lý lỗi
        return Promise.reject(error);
    }
}
```

### Xử Lý Đăng Xuất

Khi đăng xuất, token cần được xóa khỏi localStorage/sessionStorage:

```javascript
const logout = async () => {
    try {
        await axios.post('/api/logout');
        
        // Xóa token
        localStorage.removeItem('auth_token');
        sessionStorage.removeItem('auth_token');
        
        // Reset state
        state.token = null;
        state.user = null;
        state.isAuthenticated = false;
        
    } catch (error) {
        console.error('Lỗi khi đăng xuất:', error);
    }
}
```

## Các Tình Huống Sử Dụng Sanctum

### 1. Xác Thực API Từ Ứng Dụng SPA

```javascript
// Login và nhận token
const response = await axios.post('/api/login', { email, password });
const token = response.data.token;

// Sử dụng token cho các request API tiếp theo
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
const userData = await axios.get('/api/user');
```

### 2. Bảo Vệ Route API Cần Xác Thực

```php
Route::middleware('auth:sanctum')->get('/api/user-profile', function (Request $request) {
    return $request->user();
});
```

### 3. Tạo Token Với Khả Năng Cụ Thể

```php
// Tạo token chỉ có thể đọc (không thể thực hiện thao tác ghi)
$token = $user->createToken('read-only-token', ['read'])->plainTextToken;

// Kiểm tra khả năng trong controller
if (!$request->user()->tokenCan('write')) {
    return response()->json(['error' => 'Không có quyền thực hiện thao tác này'], 403);
}
```

## Khởi Chạy Sanctum

Laravel Sanctum không yêu cầu khởi chạy riêng. Miễn là bạn đã cài đặt package và đã chạy migration, nó sẽ hoạt động. Tuy nhiên, có một vài bước quan trọng:

### 1. Cài Đặt Sanctum Via Composer
```bash
composer require laravel/sanctum
```

### 2. Chạy Migrations
```bash
php artisan migrate
```

### 3. Đăng Ký Middleware
Đảm bảo `EnsureFrontendRequestsAreStateful` middleware được đăng ký trong `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Thực Hành Tốt Nhất

### Bảo Mật Token
1. **Xóa token khi đăng xuất**: Đảm bảo tất cả các token đều bị xóa khi người dùng đăng xuất
2. **Sử dụng HTTPS**: Luôn sử dụng HTTPS để truyền tải token
3. **Thiết lập thời gian hết hạn**: Cấu hình thời gian hết hạn phù hợp cho token
4. **Không lưu trữ token trong localStorage** khi làm việc với dữ liệu nhạy cảm (nên dùng HttpOnly Cookies)

### Bảo Mật API
1. **Rate Limiting**: Giới hạn số lượng request API trong một khoảng thời gian
2. **Kiểm soát quyền truy cập**: Kiểm tra quyền hạn của token trước khi cho phép truy cập tài nguyên
3. **Validate Input**: Luôn xác thực đầu vào từ request
4. **Kiểm soát phạm vi token**: Sử dụng token abilities để giới hạn quyền truy cập

### Bảo Mật Chung
1. **Sử dụng TLS/SSL**: Luôn sử dụng HTTPS cho mọi giao tiếp
2. **CORS Configuration**: Cấu hình CORS đúng cách để chỉ cho phép các domain được phép
3. **Logging và Giám sát**: Ghi nhật ký và giám sát các hoạt động xác thực
4. **Xác thực đa yếu tố (MFA)**: Triển khai xác thực đa yếu tố cho tài khoản quan trọng

## Kết Luận

Laravel Sanctum cung cấp một giải pháp xác thực linh hoạt và mạnh mẽ cho dự án KT_AI, cho phép bảo vệ hiệu quả các API và SPA. Với khả năng xác thực token-based và stateful sessions, Sanctum đáp ứng các yêu cầu bảo mật khác nhau của ứng dụng.

Để tiếp tục nâng cao bảo mật, có thể xem xét triển khai:
1. Xác thực đa yếu tố (MFA) cho các tài khoản quan trọng
2. Hệ thống phát hiện và phòng chống xâm nhập
3. Giám sát và phân tích hành vi đăng nhập bất thường
4. Kiểm tra bảo mật và đánh giá lỗ hổng định kỳ