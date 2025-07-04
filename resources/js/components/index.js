// Main Components Index - Export tất cả components từ các modules

// Base Components
export * from './base/index.js'

// Layout Components  
export * from './layouts/index.js'

// UI Components
export * from './ui/index.js'

// Feature Components
export * from './features/auth/index.js'
export * from './features/images/index.js'
export * from './features/comments/index.js'
export * from './features/dashboard/index.js'
export * from './features/user-profile/index.js'

// Helper function để đăng ký components toàn cục
export const registerGlobalComponents = (app) => {
}; 