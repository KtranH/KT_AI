import { ref, computed } from 'vue'
import axios from 'axios'
import router from '../router'

// Tạo một reactive state duy nhất
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
      console.error('Error checking auth:', error)
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
      const response = await axios.post('/api/logout')
      user.value = null
      localStorage.removeItem('token')
      router.push('/login')
      return { success: true }
    } catch (error) {
      console.error('Lỗi khi đăng xuất:', error)
      return { success: false, error }
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
  
        const { success, token, user: userData } = event.data;
  
        if (success) {
          localStorage.setItem('token', token);
          user.value = userData; // Cập nhật dữ liệu người dùng
          console.log('Đăng nhập thành công:', userData);
          
          // Sử dụng router.push thay vì window.location.reload
          router.push('/dashboard');
          // KHÔNG làm điều này để tránh render component hai lần
          // window.location.reload(); 
        } else {
          console.error('Đăng nhập thất bại:', event.data.message);
        }
      }, { once: true });
    } catch (error) {
      console.error('Lỗi trong quá trình đăng nhập:', error);
    }
  }
  
  // Thêm hàm để tải lại dữ liệu người dùng
  const refreshUserData = async () => {
    return await checkAuth()
  }
  
  return {
    user,
    isAuthenticated,
    checkAuth,
    login,
    logout,
    handleLoginByGoogle,
    refreshUserData
  }
} 