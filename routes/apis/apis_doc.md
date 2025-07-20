# API Routes Structure - Double Protection (CSRF + Sanctum)

Cấu trúc API routes đã được tổ chức lại để dễ quản lý và bảo trì hơn với **Double Protection Security**.

## 🔐 **Bảo mật 2 lớp (Double Protection)**

Project này sử dụng **Double Protection** cho bảo mật tối đa:

### **Lớp 1: CSRF Protection**
- Bảo vệ chống Cross-Site Request Forgery
- Yêu cầu header `X-XSRF-TOKEN` 
- Token được lấy từ `/sanctum/csrf-cookie`

### **Lớp 2: Sanctum Token Protection**  
- Bảo vệ chống Unauthorized Access
- Yêu cầu header `Authorization: Bearer {token}`
- Token được tạo sau khi đăng nhập thành công

### **Lớp 3: Turnstile Protection (Bonus)**
- Bảo vệ chống Bot Attacks
- Cloudflare Turnstile verification
- Yêu cầu `cf-turnstile-response` cho auth endpoints

## Cấu trúc thư mục

```
routes/
├── api.php              # File chính load tất cả API routes
├── web.php              # Web routes (SPA, broadcast, test routes)
└── apis/                # Thư mục chứa các file API routes riêng biệt
    ├── auth.php         # Routes xác thực (login, register, forgot password)
    ├── features.php     # Routes quản lý tính năng AI
    ├── images.php       # Routes quản lý hình ảnh
    ├── users.php        # Routes quản lý người dùng
    ├── comments.php     # Routes quản lý bình luận
    ├── likes.php        # Routes quản lý like
    ├── notifications.php # Routes quản lý thông báo
    ├── statistics.php   # Routes thống kê
    ├── image-jobs.php   # Routes quản lý job tạo hình ảnh
    └── proxy.php        # Routes proxy cho hình ảnh
```

## Cách hoạt động

1. Tất cả API routes được định nghĩa trong thư mục `routes/apis/`
2. File `routes/api.php` include tất cả các file trong thư mục `apis/`
3. `RouteServiceProvider` load `api.php` với prefix `api` và middleware `api`
4. Tất cả API endpoints sẽ có URL bắt đầu bằng `/api/`

## 🔐 **Phân loại routes theo mức bảo mật**

### **Public Routes (Không cần bảo mật)**
- `GET /api/turnstile/config` - Cấu hình Turnstile
- `GET /api/load_features` - Lấy danh sách tính năng
- `GET /api/load_features/{id}` - Lấy thông tin tính năng theo ID
- `GET /api/get_images_information/{id}` - Lấy thông tin hình ảnh
- `GET /api/get_images_by_feature/{id}` - Lấy hình ảnh theo tính năng
- `GET /api/get_likes_information/{id}` - Lấy thông tin like
- `GET /api/check` - Check auth status
- `GET /api/google/url` - Google OAuth URL
- `GET /api/google/callback` - Google OAuth callback

### **Auth Routes (CSRF + Turnstile Protection)**
- `POST /api/login` - 🔐 **CSRF + Turnstile**
- `POST /api/register` - 🔐 **CSRF + Turnstile**
- `POST /api/api-login` - 🔐 **CSRF + Turnstile** (API testing)
- `POST /api/forgot-password` - 🔐 **CSRF + Turnstile**
- `POST /api/verify-email` - 🔐 **CSRF**
- `POST /api/resend-verification` - 🔐 **CSRF**
- `POST /api/verify-reset-code` - 🔐 **CSRF**
- `POST /api/reset-password` - 🔐 **CSRF**

### **Protected Routes (CSRF + Sanctum Protection)**
- `POST /api/logout` - 🔐 **CSRF + Sanctum**
- `GET /api/user/{id}` - 🔐 **CSRF + Sanctum**
- `POST /api/update-avatar` - 🔐 **CSRF + Sanctum**
- `PATCH /api/update-name` - 🔐 **CSRF + Sanctum**
- `POST /api/upload_images/{featureId}` - 🔐 **CSRF + Sanctum**
- `DELETE /api/images/{image}` - 🔐 **CSRF + Sanctum**
- `POST /api/comments` - 🔐 **CSRF + Sanctum**
- `POST /api/likes` - 🔐 **CSRF + Sanctum**
- `GET /api/notifications` - 🔐 **CSRF + Sanctum**
- `GET /api/statistics` - 🔐 **CSRF + Sanctum**

## 🔧 **Request Headers cần thiết**

### **Cho Public Routes**
```javascript
{
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest'
}
```

### **Cho Auth Routes**
```javascript
{
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-XSRF-TOKEN': 'csrf_token_here'  // CSRF Protection
}
```

### **Cho Protected Routes**
```javascript
{
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-XSRF-TOKEN': 'csrf_token_here',      // CSRF Protection
  'Authorization': 'Bearer sanctum_token'  // Sanctum Protection
}
```

## 🚀 **Lợi ích của Double Protection**

1. **Bảo mật tối đa**: 2-3 lớp bảo vệ cho mỗi request
2. **Chống CSRF**: Ngăn chặn cross-site request forgery
3. **Chống Unauthorized**: Ngăn chặn truy cập trái phép
4. **Chống Bot**: Cloudflare Turnstile verification
5. **Audit Trail**: Log chi tiết mọi request
6. **Enterprise Ready**: Đáp ứng tiêu chuẩn bảo mật cao

## 🔍 **Monitoring & Debugging**

### **Response Headers**
```
X-Double-Protection: CSRF+Sanctum
```

### **Log Format**
```
🔐 Double Protection Status
- Method: POST
- URI: /api/login
- Protection: CSRF+Sanctum
- CSRF Present: true
- Sanctum Present: true
```

## Thêm routes mới

Để thêm routes mới với Double Protection:

1. Tạo file mới trong `routes/apis/` hoặc thêm vào file phù hợp
2. Include file trong `routes/api.php`
3. Đảm bảo áp dụng middleware phù hợp:
   - Public: Không middleware đặc biệt
   - Auth: CSRF + Turnstile protection
   - Protected: `auth:sanctum` + CSRF protection
4. Test với cả CSRF token và Sanctum token 