# 🧪 **Hướng dẫn Test Session Authentication**

## 📋 **Mục tiêu test**

Kiểm tra xem hệ thống session + Bearer token authentication có hoạt động đúng không:
1. **Web login**: Tạo session, không có token
2. **Mobile login**: Tạo Bearer token
3. **Google OAuth**: Tạo session (không có token)
4. **Protected endpoints**: Hoạt động với cả session và token

## 🚀 **Chuẩn bị**

### **1. Khởi động server**
```bash
php artisan serve
# hoặc
php artisan serve --host=0.0.0.0 --port=8000
```

### **2. Kiểm tra cấu hình**
```bash
# Kiểm tra session driver
php artisan tinker
>>> config('session.driver')
>>> config('sanctum.stateful')

# Kiểm tra storage permissions
ls -la storage/framework/sessions/
```

## 🧪 **Test 1: Web Session Authentication**

### **Bước 1: Lấy CSRF token**
```bash
curl -c cookies.txt -b cookies.txt \
  http://localhost:8000/sanctum/csrf-cookie
```

**Kết quả mong đợi:**
- Cookie `XSRF-TOKEN` được set
- Status 204 (No Content)

### **Bước 2: Login với credentials**
```bash
curl -c cookies.txt -b cookies.txt \
  -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: [token_from_step_1]" \
  -d '{
    "email": "test@example.com",
    "password": "password",
    "cf-turnstile-response": "test"
  }'
```

**Kết quả mong đợi:**
```json
{
  "success": true,
  "message": "Đăng nhập thành công (session cookie)",
  "user": {...},
  "auth": {
    "token": null,
    "token_type": "Session",
    "remember": false,
    "expires_in": null
  }
}
```

**Kiểm tra cookies:**
- `laravel_session` được set
- `XSRF-TOKEN` được cập nhật

### **Bước 3: Gọi protected endpoint**
```bash
curl -c cookies.txt -b cookies.txt \
  http://localhost:8000/api/user/1
```

**Kết quả mong đợi:**
- Status 200
- User data được trả về
- Không cần Authorization header

## 🧪 **Test 2: Mobile Bearer Token Authentication**

### **Bước 1: Login để nhận token**
```bash
curl -X POST http://localhost:8000/api/api-login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

**Kết quả mong đợi:**
```json
{
  "success": true,
  "message": "API đăng nhập thành công",
  "user": {...},
  "auth": {
    "token": "1|abc123def456...",
    "token_type": "Bearer",
    "remember": false,
    "expires_in": 604800
  }
}
```

### **Bước 2: Gọi protected endpoint với token**
```bash
curl -H "Authorization: Bearer 1|abc123def456..." \
  http://localhost:8000/api/user/1
```

**Kết quả mong đợi:**
- Status 200
- User data được trả về

## 🧪 **Test 3: Google OAuth Session**

### **Bước 1: Lấy Google OAuth URL**
```bash
curl http://localhost:8000/api/auth/google/url
```

### **Bước 2: Mở popup và đăng nhập**
1. Mở URL từ step 1 trong browser
2. Đăng nhập Google
3. Popup sẽ đóng và gửi message về parent window

### **Bước 3: Kiểm tra session**
```bash
# Kiểm tra session files
ls -la storage/framework/sessions/

# Hoặc kiểm tra database
php artisan tinker
>>> DB::table('sessions')->get();
```

## 🧪 **Test 4: Logout và Session Cleanup**

### **Bước 1: Logout từ web session**
```bash
curl -c cookies.txt -b cookies.txt \
  -X POST http://localhost:8000/api/logout \
  -H "X-XSRF-TOKEN: [current_csrf_token]"
```

**Kết quả mong đợi:**
```json
{
  "success": true,
  "message": "Đăng xuất thành công",
  "data": {
    "csrf_token": "new_csrf_token_here"
  }
}
```

### **Bước 2: Kiểm tra session đã bị xóa**
```bash
# Kiểm tra session files
ls -la storage/framework/sessions/

# Kiểm tra cookies
cat cookies.txt
```

## 🔍 **Debug và Troubleshooting**

### **Session không được tạo**
```bash
# Kiểm tra storage permissions
chmod -R 775 storage/framework/sessions/

# Kiểm tra session configuration
php artisan tinker
>>> config('session')
>>> config('sanctum')
```

### **CSRF token mismatch**
```bash
# Kiểm tra cookies
cat cookies.txt

# Kiểm tra CORS
php artisan tinker
>>> config('cors')
```

### **Cookie không được gửi**
```bash
# Kiểm tra domain
php artisan tinker
>>> config('session.domain')
>>> config('sanctum.stateful')

# Test với curl verbose
curl -v -c cookies.txt -b cookies.txt \
  http://localhost:8000/sanctum/csrf-cookie
```

## 📱 **Test Frontend (Vue.js)**

### **1. Khởi động frontend**
```bash
cd resources/js
npm run dev
# hoặc
npm run build && npm run preview
```

### **2. Test login flow**
1. Mở browser DevTools > Application > Cookies
2. Gọi `GET /sanctum/csrf-cookie`
3. Kiểm tra cookie `XSRF-TOKEN` được set
4. Login với credentials
5. Kiểm tra cookie `laravel_session` được set
6. Gọi protected endpoint

### **3. Test Google OAuth**
1. Click "Login with Google"
2. Popup mở và đăng nhập
3. Kiểm tra session được tạo
4. Redirect đến dashboard

## ✅ **Checklist hoàn thành**

- [ ] Web session authentication hoạt động
- [ ] Mobile Bearer token authentication hoạt động  
- [ ] Google OAuth tạo session đúng cách
- [ ] Protected endpoints hoạt động với cả 2 phương thức
- [ ] Logout xóa session và token
- [ ] CSRF protection hoạt động
- [ ] Frontend xử lý session đúng cách

## 🚨 **Lỗi thường gặp**

### **419 CSRF Token Mismatch**
- Gọi `/sanctum/csrf-cookie` trước khi login
- Đảm bảo `X-XSRF-TOKEN` header được gửi đúng

### **401 Unauthenticated**
- Kiểm tra session/token có hợp lệ không
- Kiểm tra middleware `auth:sanctum`

### **Cookie không được set**
- Kiểm tra `SANCTUM_STATEFUL_DOMAINS`
- Kiểm tra `CORS_SUPPORTS_CREDENTIALS=true`
- Kiểm tra domain configuration
