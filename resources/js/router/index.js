import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

// Components
// Home Pages
import Home from '../pages/Home.vue'
// Auth Forms
import Login from '../components/auth/LoginForm.vue'
import Register from '../components/auth/RegisterForm.vue'
import VerifyEmail from '../components/auth/VerifyEmailForm.vue'
// Dashboard Pages
import Dashboard from '../pages/Dashboard.vue'
// Features Pages
import Features from '../pages/Features.vue'
// Image Pages
import CreateImage from '../pages/GenImage.vue'
import Detail from '../pages/ImageDetail.vue'

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
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, title: 'KT_AI - Trang chủ' }
  },
  {
    path: '/features',
    name: 'features',
    component: Features,
    meta: { requiresAuth: true, title: 'KT_AI - Tính năng' }
  },
  {
    path: '/createimage/:encodedID',
    name: 'createimage',
    component: CreateImage,
    meta: { requiresAuth: true, title: 'KT_AI - Tạo ảnh' }
  },
  {
    path: '/image/detail',
    name: 'detail',
    component: Detail,
    meta: { requiresAuth: true, title: 'KT_AI - Chi tiết ảnh' }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  document.title = to.meta.title || 'KT_AI - Sáng tạo ảnh với AI';
  const auth = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  
  if (requiresAuth) {
    const isAuthenticated = await auth.checkAuth()
    
    if (!isAuthenticated) {
      next({ 
        path: '/login', 
        query: { redirect: to.fullPath } 
      })
    } else {
      next()
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