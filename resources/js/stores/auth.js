import { ref, computed } from 'vue'
import axios from 'axios'
import router from '../router'

const user = ref(null)
const isAuthenticated = computed(() => !!user.value)

export const useAuthStore = () => {
  const checkAuth = async () => {
    try {
      const response = await axios.get('/api/check')
      if (response.data.authenticated) {
        user.value = response.data.user
      } else {
        user.value = null
      }
      return response.data
    } catch (error) {
      user.value = null
      throw error
    }
  }

  const login = async (credentials) => {
    try {
      const response = await axios.post('/api/login', credentials)
      
      if (response.data.success) {
        user.value = response.data.user
        const redirect = router.currentRoute.value.query.redirect || '/dashboard'
        window.location.href = redirect
      }
      else
      {
        return response.data
      }
    } catch (error) {
      if (error.response?.status === 419) {
        // Lấy CSRF token mới và thử lại
        await axios.get('/sanctum/csrf-cookie')
        return login(credentials) // Gọi lại hàm login
      }
      throw error
    }
  }

  const logout = async () => {
    try {
      const response = await axios.post('/api/logout')
      user.value = null
      window.location.href = '/login'
    } catch (error) {
      if (error.response?.status === 419) {
        window.location.reload()
      } else {
        console.error('Lỗi khi đăng xuất:', error)
      }
    }
  }

  return {
    user,
    isAuthenticated,
    checkAuth,
    login,
    logout
  }
} 