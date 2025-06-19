# API Structure Synchronization Guide

## 📋 Tổng quan

Tài liệu này mô tả việc đồng bộ hóa hoàn toàn cấu trúc API giữa Laravel routes (PHP) và JavaScript API services để đảm bảo tính nhất quán và dễ bảo trì.

## 🔧 Vấn đề đã được sửa

### 1. **Auth API Reorganization**
**Trước:**
- `profileAPI` chứa các endpoint authentication (forgot password, reset password)
- `verificationAPI` tách biệt cho resend verification
- Thiếu endpoint `sendPasswordChangeVerification`

**Sau:**
```javascript
export const authAPI = {
  // Basic Auth
  login, logout, check, register,
  
  // Email Verification
  verifyEmail, resendVerification,
  
  // Password Reset
  forgotPassword, verifyResetCode, resetPassword,
  
  // Password Change Verification
  sendPasswordChangeVerification
}
```

### 2. **API Category Restructuring**

#### **Google OAuth API**
```javascript
export const googleAPI = {
  getAuthUrl: () => '/google/url',
  callback: (code) => `/google/callback?code=${code}`
}
```

#### **Profile/User API**
```javascript
export const profileAPI = {
  // User Info
  getUserById,
  
  // Profile Updates
  updateAvatar, updateCoverImage, updateName, updatePassword,
  
  // Utilities
  checkPassword, checkCredits
}
```

#### **Features API**
```javascript
export const featuresAPI = {
  getAll: () => '/load_features',
  getById: (id) => `/load_features/${id}`
}
```

#### **Images API**
```javascript
export const imageAPI = {
  // Public Routes
  getImages, getImagesByFeature,
  
  // Protected Routes
  getImagesUploaded, getImagesLiked, checkForNewImages,
  
  // Image Management
  delete, update
}
```

#### **Likes API**
```javascript
export const likeAPI = {
  // Public Routes
  getLikesByID,
  
  // Protected Routes
  checkLiked, likePost, unlikePost
}
```

#### **Comments API**
```javascript
export const commentAPI = {
  getComments, createComment, createReply,
  updateComment, deleteComment, toggleLike
}
```

#### **Notifications API**
```javascript
export const notificationAPI = {
  getNotifications, markAsRead, markAllAsRead, getUnreadCount
}
```

#### **Image Jobs API (ComfyUI)**
```javascript
export const imageJobsAPI = {
  createJob, getActiveJobs, getCompletedJobs, getFailedJobs,
  checkJobStatus, cancelJob, retryJob
}
```

#### **Statistics API**
```javascript
export const statisticsAPI = {
  getStatistics: () => '/statistics'
}
```

#### **Proxy API**
```javascript
export const proxyAPI = {
  getR2Image, downloadR2Image, downloadFromR2Storage
}
```

### 3. **Duplicate Removal**

**Đã loại bỏ:**
- Duplicate `likePost`/`unlikePost` trong `imageAPI`
- Duplicate `getLikes` trong `imageAPI` (moved to `likeAPI`)
- Redundant `toggleCommentLike` trong `likeAPI` (available as `commentAPI.toggleLike`)

### 4. **Backward Compatibility**

Để đảm bảo code cũ vẫn hoạt động:
```javascript
// Aliases for backward compatibility
export const verificationAPI = {
  resendCode: (email) => authAPI.resendVerification(email)
}

export const comfyuiAPI = imageJobsAPI
```

## 📁 File Structure Mapping

### PHP Routes → JavaScript Services

```
routes/api.php
├── /apis/auth.php          → authAPI, googleAPI, turnstileAPI
├── /apis/users.php         → profileAPI
├── /apis/features.php      → featuresAPI
├── /apis/images.php        → imageAPI, imageUploadAPI
├── /apis/likes.php         → likeAPI
├── /apis/comments.php      → commentAPI
├── /apis/notifications.php → notificationAPI
├── /apis/statistics.php    → statisticsAPI
├── /apis/image-jobs.php    → imageJobsAPI
└── /apis/proxy.php         → proxyAPI
```

## ✅ Benefits

1. **Perfect Sync**: Mỗi PHP route đều có corresponding JavaScript function
2. **Clear Organization**: API được nhóm theo chức năng logic
3. **No Duplication**: Mỗi endpoint chỉ xuất hiện một lần
4. **Type Safety**: Cấu trúc rõ ràng giúp TypeScript integration tốt hơn
5. **Maintainability**: Dễ maintain và debug
6. **Backward Compatibility**: Code cũ vẫn hoạt động

## 🔄 Migration Guide

### For Developers

1. **Import Changes:**
```javascript
// Old
import { verificationAPI } from '@/services/api'
// New
import { authAPI } from '@/services/api'

// Old
import { comfyuiAPI } from '@/services/api'
// New  
import { imageJobsAPI } from '@/services/api'
```

2. **Function Name Changes:**
```javascript
// Old
verificationAPI.resendCode(email)
// New
authAPI.resendVerification(email)

// Old
comfyuiAPI.getActiveJobs()
// New
imageJobsAPI.getActiveJobs()
```

### For New Features

1. **Always check** `routes/apis/` cho PHP routes trước
2. **Thêm endpoint** vào đúng API category trong `api.js`
3. **Update documentation** này khi có thay đổi
4. **Test** cả PHP route và JS function

## 🚀 Best Practices

1. **Naming Convention**: Giữ tên JavaScript function giống với PHP route action
2. **Parameter Consistency**: Parameters phải match giữa JS và PHP
3. **Error Handling**: Đồng nhất error response structure
4. **Documentation**: Update tài liệu khi có thay đổi API
5. **Testing**: Test integration giữa frontend và backend

## 📝 Notes

- File này được tạo sau khi đồng bộ hóa hoàn toàn API structure
- Mọi thay đổi API sau này phải tuân theo cấu trúc này
- Backward compatibility sẽ được maintain trong ít nhất 2 versions 