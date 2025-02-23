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
      
      if (response.data.needs_verification) {
        return response.data
      }
      
      user.value = response.data.user
      localStorage.setItem('token', response.data.token) // Lưu token

      const redirect = router.currentRoute.value.query.redirect || '/dashboard'
      router.push(redirect)
      return response.data
    } catch (error) {
      if (error.response?.status === 403 && error.response?.data?.needs_verification) {
        return error.response.data
      }
      throw error
    }
  }

  const logout = async () => {
    try {
      await axios.post('/api/logout')
      user.value = null
      localStorage.removeItem('token')
      router.push('/login') 
    } catch (error) {
      console.error('Lỗi khi đăng xuất:', error)
    }
  }
  const handleLoginByGoogle = async () => {
    try {
      const response = await axios.get('/auth/google/url');
      const googleAuthUrl = response.data.url;
  
      const popup = window.open(googleAuthUrl, 'Google Login', 'width=500,height=600');
  
      // Lắng nghe phản hồi từ popup
      window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return; // Đảm bảo cùng origin
  
        const { success, token, user } = event.data;
  
        if (success) {
          localStorage.setItem('token', token);
          console.log('Đăng nhập thành công:', user);
          router.push('/dashboard');
          window.location.reload(); 
        } else {
          console.error('Đăng nhập thất bại:', event.data.message);
        }
      }, { once: true });
    } catch (error) {
      console.error('Lỗi trong quá trình đăng nhập:', error);
    }
  }
  return {
    user,
    isAuthenticated,
    checkAuth,
    login,
    logout,
    handleLoginByGoogle
  }
} 