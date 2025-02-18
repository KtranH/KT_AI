import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

// Components
import Home from '../components/Home.vue'
import Login from '../components/auth/Login.vue'
import Register from '../components/auth/Register.vue'
import Dashboard from '../components/Dashboard.vue'

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
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/api/google/callback',
    name: 'google-callback',
    component: {
      template: '<div>Đang xử lý đăng nhập...</div>',
      created() {
        const code = this.$route.query.code
        if (code) {
          this.$store.dispatch('auth/handleGoogleCallback', code)
            .then(() => {
              this.$router.push('/dashboard')
            })
            .catch(error => {
              console.error('Google callback error:', error)
              this.$router.push('/login')
            })
        } else {
          this.$router.push('/login')
        }
      }
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  // Kiểm tra xem route có yêu cầu auth không
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Gọi API kiểm tra auth status
    try {
      const response = await axios.get('/api/check', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      if (!response.data.authenticated) {
        next({
          path: '/login',
          query: { redirect: to.fullPath }
        })
      } else {
        next()
      }
    } catch (error) {
      next({
        path: '/login',
        query: { redirect: to.fullPath }
      })
    }
  } else if (to.matched.some(record => record.meta.guest)) {
    // Kiểm tra guest routes
    try {
      const response = await axios.get('/api/check', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      if (response.data.authenticated) {
        next('/dashboard')
      } else {
        next()
      }
    } catch (error) {
      next()
    }
  } else {
    next()
  }
})

export default router 