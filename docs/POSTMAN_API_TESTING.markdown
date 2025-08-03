# Hướng dẫn Test API với Postman - Laravel Sanctum

## Tổng quan
Hướng dẫn này sẽ giúp bạn cấu hình và sử dụng Postman để test các API được bảo vệ bởi Laravel Sanctum.

## Bước 1: Cấu hình Postman Environment

### Tạo Environment mới
1. Mở Postman
2. Click vào "Environments" ở sidebar
3. Click "Create Environment"
4. Đặt tên: `Laravel API Testing`
5. Thêm các variables sau:

```
Variable Name    | Initial Value              | Current Value
base_url        | http://localhost:8000/api   | http://localhost:8000/api
token           |                            |
user_id         |                            |
```

## Bước 2: Login và lấy Token

### Request Login
- **Method**: POST
- **URL**: `{{base_url}}/auth/api-login`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body** (raw JSON):
  ```json
  {
    "email": "your-email@example.com",
    "password": "your-password",
    "remember": false
  }
  ```

### Response mẫu thành công:
```json
{
    "success": true,
    "message": "Đăng nhập thành công",
    "data": {
        "user": {
            "id": 1,
            "name": "User Name",
            "email": "user@example.com",
            // ... other user data
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer",
        "expires_in": 604800
    }
}
```

### Script để tự động lưu token (Tests tab):
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.data.token) {
        pm.environment.set("token", response.data.token);
        pm.environment.set("user_id", response.data.user.id);
        console.log("Token saved to environment:", response.data.token);
    }
}
```

## Bước 3: Test API được bảo vệ

### Cấu hình Authorization cho các request khác
1. Chọn tab "Authorization"
2. Type: `Bearer Token`
3. Token: `{{token}}`

Hoặc thêm vào Headers:
```
Authorization: Bearer {{token}}
```

### Ví dụ các API test:

#### 1. Lấy thông tin user hiện tại
- **Method**: GET
- **URL**: `{{base_url}}/auth/check`
- **Headers**:
  ```
  Authorization: Bearer {{token}}
  Accept: application/json
  ```

#### 2. Logout
- **Method**: POST
- **URL**: `{{base_url}}/auth/logout`
- **Headers**:
  ```
  Authorization: Bearer {{token}}
  Content-Type: application/json
  Accept: application/json
  ```

#### 3. Test API khác (ví dụ: lấy danh sách ảnh)
- **Method**: GET
- **URL**: `{{base_url}}/images`
- **Headers**:
  ```
  Authorization: Bearer {{token}}
  Accept: application/json
  ```

## Bước 4: Xử lý lỗi Token hết hạn

### Script kiểm tra token hết hạn (Tests tab):
```javascript
if (pm.response.code === 401) {
    const response = pm.response.json();
    if (response.message && response.message.includes('Unauthenticated')) {
        console.log("Token expired, need to login again");
        pm.environment.unset("token");
        pm.environment.unset("user_id");
    }
}
```

## Bước 5: Collection Scripts

### Pre-request Script cho toàn bộ Collection:
```javascript
// Kiểm tra nếu request cần authentication và chưa có token
const needsAuth = pm.request.headers.has("Authorization") || 
                  pm.request.auth && pm.request.auth.type === "bearer";

if (needsAuth && !pm.environment.get("token")) {
    console.warn("This request needs authentication but no token found. Please login first.");
}
```

## Các lưu ý quan trọng

### 1. Cấu hình CORS
Đảm bảo file `config/cors.php` đã được cấu hình đúng:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // Hoặc specific domains
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

### 2. Middleware API
Đảm bảo các route API sử dụng middleware `auth:sanctum`:
```php
Route::middleware(['auth:sanctum'])->group(function () {
    // Protected routes here
});
```

### 3. Headers bắt buộc
Luôn include các headers sau trong requests:
```
Accept: application/json
Content-Type: application/json (cho POST/PUT requests)
```

### 4. Environment Variables
Sử dụng Environment Variables để dễ dàng chuyển đổi giữa các môi trường:
- `base_url`: URL của API
- `token`: Bearer token
- `user_id`: ID của user hiện tại

## Troubleshooting

### Lỗi 419 (CSRF Token Mismatch)
- Đảm bảo sử dụng route `/api-login` thay vì `/login`
- Thêm header `Accept: application/json`

### Lỗi 401 (Unauthenticated)
- Kiểm tra token có đúng format không
- Kiểm tra token còn hạn không (mặc định 7 ngày)
- Đảm bảo header Authorization đúng format: `Bearer {token}`

### Lỗi 422 (Validation Error)
- Kiểm tra format dữ liệu gửi lên
- Đảm bảo email và password đúng định dạng

### Token không hoạt động
1. Kiểm tra cấu hình Sanctum
2. Xóa cache: `php artisan cache:clear`
3. Restart Laravel development server

## Example Collection Structure

```
📁 Laravel API Testing
├── 📁 Auth
│   ├── 🔵 POST Login (API)
│   ├── 🟢 GET Check Status
│   └── 🔴 POST Logout
├── 📁 Images
│   ├── 🟢 GET List Images
│   ├── 🔵 POST Create Image
│   └── 🟡 PUT Update Image
└── 📁 Users
    ├── 🟢 GET Profile
    └── 🟡 PUT Update Profile
```

## Kết luận

Với cấu hình trên, bạn có thể dễ dàng test tất cả các API của Laravel application với Sanctum authentication thông qua Postman. Nhớ luôn kiểm tra token và xử lý các trường hợp lỗi một cách phù hợp. 