# Google OAuth Setup Guide

## 🚨 Vấn đề đã sửa

Lỗi 404 khi đăng nhập Google đã được khắc phục bằng cách:

1. ✅ **Thêm web route** cho Google callback trong `routes/web.php`:
```php
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'handleCallback']);
```

2. ✅ **Loại bỏ duplicate route** trong `routes/apis/auth.php`

3. ✅ **Cấu trúc route đúng**:
   - `/api/google/url` → Lấy URL đăng nhập Google
   - `/auth/google/callback` → Google redirect về đây sau khi user đăng nhập

## 🔧 Cấu hình cần thiết

### 1. Google Console Configuration

Trong [Google Console](https://console.developers.google.com/):

1. **Authorized JavaScript origins:**
```
http://127.0.0.1:8000
http://localhost:8000
```

2. **Authorized redirect URIs:**
```
http://127.0.0.1:8000/auth/google/callback
http://localhost:8000/auth/google/callback
```

### 2. Environment Variables (.env)

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

### 3. Kiểm tra cấu hình

```bash
# Kiểm tra routes
php artisan route:list --grep=google

# Kiểm tra config
php artisan config:cache
php artisan config:clear
```

## 🔄 Luồng hoạt động

1. **User click "Đăng nhập với Google"**
2. **Frontend gọi** `/api/google/url`
3. **Backend trả về** Google OAuth URL
4. **Popup mở** Google OAuth page
5. **User đăng nhập** trên Google
6. **Google redirect** về `/auth/google/callback`
7. **Backend xử lý** callback và tạo user/token
8. **Popup post message** về parent window
9. **Frontend nhận** message và đăng nhập user

## ✅ Test

1. **Kiểm tra route tồn tại:**
```bash
curl http://127.0.0.1:8000/auth/google/callback
# Nên trả về lỗi từ Google (không phải 404)
```

2. **Kiểm tra API endpoint:**
```bash
curl http://127.0.0.1:8000/api/google/url
# Nên trả về JSON với Google OAuth URL
```

## 🚨 Lưu ý quan trọng

1. **URL phải chính xác**: `http://127.0.0.1:8000/auth/google/callback`
2. **Không được dùng `localhost`** nếu Google Console cấu hình `127.0.0.1`
3. **Clear cache** sau khi thay đổi config
4. **Google Console** và `.env` phải khớp nhau hoàn toàn

## 🔧 Troubleshooting

### Lỗi 404 
- ✅ Đã sửa: Thêm route trong `web.php`

### Lỗi redirect_uri_mismatch
- Kiểm tra Google Console có đúng URL không
- Kiểm tra `.env` có đúng GOOGLE_REDIRECT_URI không

### Lỗi popup blocked
- Cho phép popup trong browser
- Sử dụng HTTPS trong production

## 📝 Files đã được cập nhật

1. `routes/web.php` - Thêm Google callback route
2. `routes/apis/auth.php` - Xóa duplicate route 
3. `doc/Google_OAuth_Setup.md` - File hướng dẫn này

Bây giờ tính năng đăng nhập Google sẽ hoạt động bình thường! 