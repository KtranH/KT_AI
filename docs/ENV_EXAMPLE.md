# 🔐 **File .env.example cho Sanctum Session + Bearer Token**

## 📋 **Hướng dẫn sử dụng**

1. Copy nội dung dưới đây
2. Tạo file `.env` trong thư mục gốc của dự án
3. Paste và điều chỉnh các giá trị phù hợp với môi trường của bạn

## ⚙️ **Nội dung file .env**

```env
# === LARAVEL APP CONFIGURATION ===
APP_NAME="KT_AI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# === DATABASE CONFIGURATION ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kt_ai
DB_USERNAME=root
DB_PASSWORD=

# === SESSION CONFIGURATION (QUAN TRỌNG CHO WEB AUTH) ===
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.localhost
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# === SANCTUM CONFIGURATION ===
# Các domain được coi là "stateful" (web SPA) - sẽ nhận session cookies
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:3000,localhost:5173,127.0.0.1:5173

# Prefix cho token (tùy chọn)
SANCTUM_TOKEN_PREFIX=

# === CORS CONFIGURATION ===
# Bật credentials để hỗ trợ session cookies
CORS_SUPPORTS_CREDENTIALS=true

# === MAIL CONFIGURATION ===
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# === REDIS CONFIGURATION (TÙY CHỌN) ===
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# === QUEUE CONFIGURATION ===
QUEUE_CONNECTION=sync

# === CACHE CONFIGURATION ===
CACHE_DRIVER=file

# === LOG CONFIGURATION ===
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# === BROADCASTING CONFIGURATION ===
BROADCAST_DRIVER=log

# === EXTERNAL SERVICES ===
# Turnstile (Cloudflare)
TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# R2 Storage (Cloudflare)
R2_ACCESS_KEY_ID=your_r2_access_key
R2_SECRET_ACCESS_KEY=your_r2_secret_key
R2_DEFAULT_REGION=auto
R2_BUCKET=your_bucket_name
R2_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com

# ComfyUI API
COMFYUI_API_URL=http://localhost:8188
COMFYUI_API_USERNAME=
COMFYUI_API_PASSWORD=
```

## 🔧 **Các bước cấu hình**

### **1. Tạo APP_KEY**
```bash
php artisan key:generate
```

### **2. Cấu hình database**
```bash
# Tạo database
mysql -u root -p
CREATE DATABASE kt_ai;

# Chạy migrations
php artisan migrate

# Tạo sessions table (nếu dùng SESSION_DRIVER=database)
php artisan session:table
php artisan migrate
```

### **3. Cấu hình Google OAuth**
1. Vào [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project có sẵn
3. Enable Google+ API
4. Tạo OAuth 2.0 credentials
5. Thêm redirect URI: `http://localhost:8000/api/auth/google/callback`
6. Copy `Client ID` và `Client Secret` vào `.env`

### **4. Cấu hình Turnstile (Cloudflare)**
1. Vào [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Chọn domain của bạn
3. Vào Security > Turnstile
4. Tạo site key mới
5. Copy `Site Key` và `Secret Key` vào `.env`

## 🚀 **Kiểm tra cấu hình**

### **1. Kiểm tra session**
```bash
php artisan tinker
>>> config('session.driver')
>>> config('session.lifetime')
>>> config('session.domain')
```

### **2. Kiểm tra Sanctum**
```bash
php artisan tinker
>>> config('sanctum.stateful')
>>> config('sanctum.guard')
```

### **3. Kiểm tra CORS**
```bash
php artisan tinker
>>> config('cors.supports_credentials')
>>> config('cors.allowed_origins')
```

## 🔒 **Production Settings**

### **HTTPS Production**
```env
APP_URL=https://yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_DOMAIN=.yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com,app.yourdomain.com
```

### **Security Production**
```env
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=error
SESSION_HTTP_ONLY=true
```

## ❓ **Troubleshooting**

### **Session không hoạt động**
1. Kiểm tra `SESSION_DRIVER` có đúng không
2. Kiểm tra `storage/framework/sessions/` có quyền write không
3. Kiểm tra `SANCTUM_STATEFUL_DOMAINS` có đúng domain không

### **CSRF token mismatch**
1. Kiểm tra `CORS_SUPPORTS_CREDENTIALS=true`
2. Kiểm tra domain trong `SESSION_DOMAIN`
3. Đảm bảo frontend gọi `/sanctum/csrf-cookie` trước khi login

### **Google OAuth không hoạt động**
1. Kiểm tra `GOOGLE_CLIENT_ID` và `GOOGLE_CLIENT_SECRET`
2. Kiểm tra `GOOGLE_REDIRECT_URI` có đúng không
3. Kiểm tra Google+ API đã được enable chưa
