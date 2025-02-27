import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

// Components
import Home from '../components/Home.vue'
import Login from '../components/auth/Login.vue'
import Register from '../components/auth/Register.vue'
import Dashboard from '../components/Dashboard.vue'
import VerifyEmail from '../components/auth/VerifyEmail.vue'
import Features from '../components/Features.vue'
import CreateImage from '../components/features/CreateImage.vue'

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
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true }
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: VerifyEmail,
    meta: { guest: true }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/features',
    name: 'features',
    component: Features,
    meta: { requiresAuth: true }
  },
  {
    path: '/create_image',
    name: 'create_image',
    component: CreateImage,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  try {
    const { data } = await axios.get('/api/check')
    const isAuthenticated = data.authenticated

    if (to.meta.requiresAuth && !isAuthenticated) {
      next({ path: '/login', query: { redirect: to.fullPath } })
    } else if (to.meta.guest && isAuthenticated) {
      next('/dashboard')
    } else {
      next()
    }
  } catch (error) {
    console.error('Auth check failed:', error)
    if (to.meta.requiresAuth) {
      next({ path: '/login', query: { redirect: to.fullPath } })
    } else {
      next()
    }
  }
})

router.afterEach(() => {
  // Đảm bảo không delay re-render
  window.dispatchEvent(new Event('router-changed'))
})

export default router 