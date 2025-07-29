import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
// Home Pages
import Home from '../pages/public/Home.vue'
// Auth Forms
import Login from '../pages/auth/Login.vue'
import Register from '../pages/auth/Register.vue'
import VerifyEmail from '../pages/auth/VerifyEmail.vue'
import ForgotPassword from '../pages/auth/ForgotPassword.vue'
// Dashboard Pages
import Dashboard from '../pages/dashboard/Dashboard.vue'
// Features Pages
import Features from '../pages/ai-features/Features.vue'
import Features_V2 from '../pages/ai-features/Features_V2.vue'
// Image Pages
import CreateImage from '../pages/ai-features/GenImage.vue'
import Detail from '../pages/social/ImageDetail.vue'
// Error Pages
import Error404 from '../pages/errors/error404.vue'
// Settings Pages
import Settings from '../pages/user/Settings.vue'
// Upload Pages
import Upload from '../pages/social/Upload.vue'
// User routes
//import Profile from '../pages/user/ProfilePage.vue'
import Notifications from '../pages/social/Notification.vue'
// Test Pages (Development only)
// import TestLayout from '../pages/test/TestLayout.vue'
// import Test1 from '../pages/test/Test1.vue'
// import Test2 from '../pages/test/Test2.vue'
// import Test3 from '../pages/test/Test3.vue'
// import Test4 from '../pages/test/Test4.vue'
// Statistics Pages
import StatisticsPage from '../pages/user/StatisticsPage.vue';
// Image Jobs Management
import ImageJobsManager from '../pages/ai-features/ImageJobsManager.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true, title: 'KT_AI - Đăng nhập' }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true, title: 'KT_AI - Đăng ký' }
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: VerifyEmail,
    meta: { guest: true, title: 'KT_AI - Xác thực email' }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { guest: true, title: 'KT_AI - Quên mật khẩu' }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, title: 'KT_AI - Trang chủ', keepAlive: true }
  },
  {
    path: '/dashboard/:id',
    name: 'dashboard-user',
    component: Dashboard,
    meta: { requiresAuth: true, title: 'KT_AI - Trang chủ người dùng', keepAlive: true }
  },
  {
    path: '/features',
    name: 'features',
    component: Features,
    meta: { requiresAuth: true, title: 'KT_AI - Tính năng', keepAlive: true }
  },
  {
    path: '/features-v2',
    name: 'features-v2',
    component: Features_V2,
    meta: { requiresAuth: true, title: 'KT_AI - Tính năng', keepAlive: true }
  },
  {
    path: '/createimage/:encodedID',
    name: 'createimage',
    component: CreateImage,
    meta: { requiresAuth: true, title: 'KT_AI - Tạo ảnh' }
  },
  {
    path: '/image/detail/:encodedID',
    name: 'detail',
    component: Detail,
    meta: { requiresAuth: true, title: 'KT_AI - Chi tiết ảnh' }
  },
  {
    path: '/settings',
    name: 'settings',
    component: Settings,
    meta: { requiresAuth: true, title: 'KT_AI - Cài đặt' }
  },
  {
    path: '/error/404',
    name: 'error-404',
    component: Error404,
    meta: { title: 'KT_AI - Lỗi 404' }
  },
  {
    path: '/upload',
    name: 'upload',
    component: Upload,
    meta: { requiresAuth: true, title: 'KT_AI - Tải lên' }
  },
  /*{
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true }
  },*/
  {
    path: '/notifications',
    name: 'notifications',
    component: Notifications,
    meta: { requiresAuth: true, title: 'KT_AI - Thông báo' }
  },
  {
    path: '/statistics',
    name: 'Statistics',
    component: StatisticsPage,
    meta: { requiresAuth: true }
  },
  // Quản lý tiến trình tạo ảnh
  {
    path: '/image-jobs',
    name: 'image-jobs',
    component: ImageJobsManager,
    meta: { requiresAuth: true, title: 'KT_AI - Quản lý tiến trình tạo ảnh' }
  },
  // Test routes (Development only)
  /*
  {
    path: '/test',
    name: 'test',
    component: TestLayout,
    meta: { title: 'KT_AI - Test' },
    children: [
      {
        path: '1',
        name: 'test-1',
        component: Test1,
        meta: { title: 'KT_AI - Test 1' }
      },
      {
        path: '2',
        name: 'test-2',
        component: Test2,
        meta: { title: 'KT_AI - Test 2' }
      },
      {
        path: '3',
        name: 'test-3',
        component: Test3,
        meta: { title: 'KT_AI - Test 3' }
      },
      {
        path: '4',
        name: 'test-4',
        component: Test4,
        meta: { title: 'KT_AI - Test 4' }
      }
    ]
  },
  */
  {
    path: '/:pathMatch(.*)*',
    redirect: '/error/404'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  document.title = to.meta.title || 'KT_AI - Sáng tạo ảnh với AI';
  const auth = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isGuestRoute = to.matched.some(record => record.meta.guest)
  
  // Nếu đang logout, cho phép truy cập login page
  if (to.path === '/login' && auth.isLoggingOut) {
    next()
    return
  }
  
  if (isGuestRoute) {
    // Nếu đã đăng nhập và cố gắng truy cập guest route, chuyển hướng đến dashboard
    try {
      const isAuthenticated = await auth.checkAuth()
      if (isAuthenticated && to.path !== '/') {
        next({ path: '/dashboard' })
        return
      }
    } catch (error) {
      console.warn('Auth check failed for guest route:', error)
    }
    next()
    return
  }
  
  if (requiresAuth) {
    try {
      const isAuthenticated = await auth.checkAuth()
      
      if (!isAuthenticated) {
        next({ 
          path: '/login', 
          query: { redirect: to.fullPath } 
        })
      } else {
        next()
      }
    } catch (error) {
      console.error('Auth check failed:', error)
      next({ 
        path: '/login', 
        query: { redirect: to.fullPath } 
      })
    }
  } else {
    next()
  }
})

router.afterEach(() => {
  // Đảm bảo không delay re-render
  window.dispatchEvent(new Event('router-changed'))
})

export default router