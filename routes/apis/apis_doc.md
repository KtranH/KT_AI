# API Routes Structure

Cấu trúc API routes đã được tổ chức lại để dễ quản lý và bảo trì hơn.

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

## Phân loại routes

### Public Routes (Không cần xác thực)
- `GET /api/load_features` - Lấy danh sách tính năng
- `GET /api/load_features/{id}` - Lấy thông tin tính năng theo ID
- `GET /api/get_images_information/{id}` - Lấy thông tin hình ảnh
- `GET /api/get_images_by_feature/{id}` - Lấy hình ảnh theo tính năng
- `GET /api/get_likes_information/{id}` - Lấy thông tin like
- `GET /api/turnstile/config` - Cấu hình Turnstile
- `POST /api/login` - Đăng nhập
- `POST /api/register` - Đăng ký
- `POST /api/forgot-password` - Quên mật khẩu
- `GET /api/google/url` - Google OAuth URL
- `GET /api/google/callback` - Google OAuth callback

### Protected Routes (Cần xác thực)
Tất cả routes khác đều yêu cầu xác thực thông qua middleware `auth:sanctum`.

## Lợi ích

1. **Tổ chức rõ ràng**: Mỗi module có file routes riêng
2. **Dễ bảo trì**: Dễ dàng tìm và sửa routes
3. **Mở rộng**: Dễ dàng thêm routes mới cho từng module
4. **Tái sử dụng**: Có thể include các file routes vào các context khác nhau
5. **Phân quyền**: Dễ dàng áp dụng middleware cho từng nhóm routes

## Thêm routes mới

Để thêm routes mới:

1. Tạo file mới trong `routes/apis/` hoặc thêm vào file phù hợp
2. Include file trong `routes/api.php`
3. Đảm bảo áp dụng middleware phù hợp (public hoặc protected) 