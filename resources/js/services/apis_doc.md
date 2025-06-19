# API Services Structure

## 📋 Tổng quan

Cấu trúc API frontend đã được tổ chức lại thành các module riêng biệt, phù hợp với cấu trúc backend trong `/routes/apis/`.

## 📁 Cấu trúc thư mục

```
services/
├── index.js            # Main export file
├── api.js              # Legacy file (deprecated)
├── auth.js             # Authentication APIs
├── users.js            # User/Profile management
├── features.js         # AI Features APIs
├── images.js           # Image management & upload
├── likes.js            # Like/Unlike functionality
├── comments.js         # Comments & replies
├── notifications.js    # Notifications management
├── image-jobs.js       # ComfyUI job management
├── statistics.js       # Statistics APIs
├── proxy.js            # Proxy & download services
└── generic.js          # Generic API utilities
```

## 🎯 Mapping với Backend

| Frontend Module | Backend Route | Mô tả |
|---|---|---|
| `auth.js` | `/apis/auth.php` | Authentication, OAuth, Turnstile |
| `users.js` | `/apis/users.php` | User profile, avatar, settings |
| `features.js` | `/apis/features.php` | AI features management |
| `images.js` | `/apis/images.php` | Image CRUD, upload |
| `likes.js` | `/apis/likes.php` | Like/unlike functionality |
| `comments.js` | `/apis/comments.php` | Comments & replies |
| `notifications.js` | `/apis/notifications.php` | Push notifications |
| `image-jobs.js` | `/apis/image-jobs.php` | ComfyUI job management |
| `statistics.js` | `/apis/statistics.php` | App statistics |
| `proxy.js` | `/apis/proxy.php` | R2 storage proxy |

## 🚀 Cách sử dụng

### Import APIs mới (Recommended)

```javascript
// Import từng module cụ thể
import { authAPI, googleAPI } from '@/services/auth'
import { imageAPI, imageUploadAPI } from '@/services/images'
import { commentAPI } from '@/services/comments'
import { likeAPI } from '@/services/likes'

// Sử dụng
await authAPI.login(credentials)
await imageAPI.getImages(id)
await commentAPI.createComment(data)
```

### Import từ index.js (Alternative)

```javascript
// Import tất cả từ index
import { 
  authAPI, 
  imageAPI, 
  commentAPI,
  likeAPI 
} from '@/services'

// Sử dụng
await authAPI.login(credentials)
await imageAPI.getImages(id)
```

### Legacy import (Deprecated nhưng vẫn hoạt động)

```javascript
// ⚠️ Deprecated - sẽ bị loại bỏ trong tương lai
import { authAPI, imageAPI } from '@/services/api'
```

## ✅ Lợi ích

1. **🎯 Modular**: Mỗi domain có file riêng, dễ tìm và quản lý
2. **🔄 Sync với Backend**: Cấu trúc giống hệt backend
3. **👥 Team Friendly**: Nhiều dev có thể làm việc song song
4. **📦 Tree Shaking**: Bundle chỉ import những gì cần thiết
5. **🔧 Maintainable**: Dễ debug và maintain
6. **🔄 Backward Compatible**: Code cũ vẫn hoạt động

## 🔄 Migration Guide

### Từ Legacy API

```javascript
// ❌ Cũ
import { authAPI } from '@/services/api'

// ✅ Mới
import { authAPI } from '@/services/auth'
```

### Từ Default Import

```javascript
// ❌ Cũ
import api from '@/services'
api.authAPI.login()

// ✅ Mới
import { authAPI } from '@/services/auth'
authAPI.login()
```

## 📝 Naming Convention

- **File names**: Sử dụng kebab-case (`image-jobs.js`)
- **Export names**: Sử dụng camelCase (`imageJobsAPI`)
- **Function names**: Sử dụng camelCase (`getUserById`)

## 🛠️ Development

### Thêm API mới

1. Tạo hoặc cập nhật file module phù hợp
2. Export API object với naming convention
3. Cập nhật `index.js` nếu cần
4. Test import/export hoạt động đúng

### Best Practices

- ✅ Luôn group APIs theo domain logic
- ✅ Sử dụng JSDoc để document functions
- ✅ Handle errors properly trong API calls
- ✅ Consistent parameter naming với backend
- ✅ Sử dụng TypeScript interfaces khi có thể

## 🚀 Performance

- **Tree Shaking**: Chỉ bundle APIs được sử dụng
- **Lazy Loading**: Có thể lazy load từng module
- **Code Splitting**: Dễ dàng split theo route/feature

## 🔮 Roadmap

- [ ] TypeScript definitions cho tất cả APIs
- [ ] Auto-generated API documentation
- [ ] Mock services cho testing
- [ ] API response caching
- [ ] Request interceptors cho error handling 