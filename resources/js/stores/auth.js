import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import router from '../router'

// Reactive state
const user = ref(JSON.parse(localStorage.getItem('user')) || null)
const token = ref(localStorage.getItem('token') || null)
const isRemembered = ref(localStorage.getItem('remember') === 'true')
const isAuthenticated = computed(() => !!user.value)

export const useAuthStore = () => {
  
  const saveAuthData = (userData, userToken, remember = false) => {
    user.value = userData
    token.value = userToken
    isRemembered.value = remember
    
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', userToken)
    localStorage.setItem('remember', remember)
    
    // Nếu không remember, dữ liệu sẽ bị xóa khi đóng trình duyệt
    if (!remember) {
      sessionStorage.setItem('user', JSON.stringify(userData))
      sessionStorage.setItem('token', userToken)
    }
  }

  const clearAuthData = () => {
    user.value = null
    token.value = null
    isRemembered.value = false
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('remember')
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('token')
  }

  const checkAuth = async () => {
    try {
      // Nếu không remember, kiểm tra trong sessionStorage
      if (!isRemembered.value) {
        const sessionToken = sessionStorage.getItem('token')
        const sessionUser = sessionStorage.getItem('user')
        
        if (!sessionToken || !sessionUser) {
          clearAuthData()
          return false
        }
        
        token.value = sessionToken
        user.value = JSON.parse(sessionUser)
      }
      
      if (!token.value || !user.value) return false

      const response = await axios.get('/api/check', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      
      if (response.data.authenticated) {
        if (isRemembered.value) {
          saveAuthData(response.data.user, token.value, true)
        } else {
          saveAuthData(response.data.user, token.value, false)
        }
        return true
      } else {
        clearAuthData()
        return false
      }
    } catch (error) {
      clearAuthData()
      console.error('Error checking auth:', error)
      return false
    }
  }

  const login = async (credentials) => {
    try {
      const response = await axios.post('/api/login', credentials)
      if (response.data.needs_verification) {
        return response.data
      }
      saveAuthData(response.data.user, response.data.token, response.data.remember)
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

  const handleLoginByGoogle = async () => {
    try {
      const response = await axios.get('/auth/google/url');
      const googleAuthUrl = response.data.url;
  
      const popup = window.open(googleAuthUrl, 'Google Login', 'width=500,height=600');
  
      // Lắng nghe phản hồi từ popup
      window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return;
  
        const { success, token, user } = event.data;
  
        if (success) {
          console.log('Đăng nhập thành công:', user);
          // Lưu thông tin đăng nhập trước, sau đó mới chuyển hướng
          saveAuthData(user, token, true);
          
          // Sử dụng router.push() thay vì window.location.reload()
          router.push('/dashboard');
        } else {
          console.error('Đăng nhập thất bại:', event.data.message);
        }
      }, { once: true });
    } catch (error) {
      console.error('Lỗi trong quá trình đăng nhập:', error);
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
    logout,
    handleLoginByGoogle
  }
}
