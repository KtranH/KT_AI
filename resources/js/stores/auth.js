import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import router from '../router'

// Reactive state
const user = ref(JSON.parse(localStorage.getItem('user')) || null)
const token = ref(localStorage.getItem('token') || null)
const isAuthenticated = computed(() => !!user.value)

export const useAuthStore = () => {
  
  const saveAuthData = (userData, userToken) => {
    user.value = userData
    token.value = userToken
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', userToken)
  }

  const clearAuthData = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  const checkAuth = async () => {
    if (!token.value || user.value) return

    try {
      const response = await axios.get('/api/check', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      if (response.data.authenticated) {
        saveAuthData(response.data.user, token.value)
      } else {
        clearAuthData()
      }
    } catch (error) {
      clearAuthData()
      console.error('Error checking auth:', error)
    }
  }

  const login = async (credentials) => {
    try {
      const response = await axios.post('/api/login', credentials)
      if (response.data.needs_verification) {
        return response.data
      }
      saveAuthData(response.data.user, response.data.token)
      router.push(router.currentRoute.value.query.redirect || '/dashboard')
      return response.data
    } catch (error) {
      throw error
    }
  }

  const logout = async () => {
    try {
      await axios.post('/api/logout', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      clearAuthData()
      router.push('/login')
    } catch (error) {
      console.error('Logout error:', error)
    }
  }

  onMounted(() => {
    checkAuth() // Chỉ gọi API nếu chưa có user
  })

  return {
    user,
    token,
    isAuthenticated,
    checkAuth,
    login,
    logout
  }
}
