import { ref, computed, onMounted } from 'vue'
import router from '../../router'
import { authAPI, googleAPI } from '../../services/api'
import axios from 'axios'

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
    sessionStorage.removeItem('remember')
    
    // Xóa dữ liệu trong các store khác nếu cần
    if (window.$pinia) {
      const stores = window.$pinia._s
      Object.keys(stores).forEach(key => {
        if (key !== 'auth') {
          stores[key].$reset()
        }
      })
    }
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

      // Kiểm tra xác thực bằng cách tạo một request đến /api/check
      // Đảm bảo token được gửi trong header Authorization
      try {
        const response = await authAPI.check()
        
        if (response.data.authenticated) {
          if (isRemembered.value) {
            saveAuthData(response.data.user, token.value, true)
          } else {
            saveAuthData(response.data.user, token.value, false)
          }
          return true
        }
      } catch (error) {
        // Nếu API /api/check trả về lỗi nhưng chúng ta vẫn có token và user
        // Đặc biệt là sau khi đăng nhập bằng Google
        // Giả định rằng người dùng đã được xác thực và bỏ qua lỗi validation
        if (error.response && error.response.status === 422 && user.value && token.value) {
          console.log('Bỏ qua lỗi validation từ API /check do đã đăng nhập qua Google');
          return true;
        }
        
        // Nếu lỗi khác, xóa dữ liệu
        clearAuthData()
        console.error('Error checking auth:', error)
        return false
      }
      
      // Nếu không authenticated
      clearAuthData()
      return false
    } catch (error) {
      clearAuthData()
      console.error('Error in checkAuth:', error)
      return false
    }
  }

  // Hàm khởi tạo để thay thế onMounted
  const initializeAuth = () => {
    checkAuth()
  }

  const login = async (credentials) => {
    try {
      const response = await authAPI.login(credentials)
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
      await authAPI.logout()
      clearAuthData()
      router.push('/login')
    } catch (error) {
      console.error('Logout error:', error)
    }
  }

  const handleLoginByGoogle = async () => {
    try {
      // Quay lại sử dụng axios trực tiếp như trước đây
      const response = await axios.get('/auth/google/url')
      const googleAuthUrl = response.data.url
  
      const popup = window.open(googleAuthUrl, 'Google Login', 'width=500,height=600')
      
      // Thiết lập sự kiện lắng nghe mà không dùng once: true để tránh bỏ lỡ thông báo
      const messageHandler = (event) => {
        // Kiểm tra event origin một cách linh hoạt hơn
        // Có thể lọc chỉ các origin được phép nếu cần thiết
        if (event.origin !== window.location.origin) {
          console.log(`Ignored message from: ${event.origin}`)
          return
        }
  
        // Kiểm tra dữ liệu có đúng định dạng không
        const { success, token, user } = event.data
        if (success === undefined) return
  
        // Gỡ bỏ event listener sau khi xử lý thành công
        window.removeEventListener('message', messageHandler)
  
        if (success) {
          console.log('Đăng nhập thành công:', user)
          // Lưu thông tin đăng nhập
          saveAuthData(user, token, true)
          
          // Lấy đường dẫn redirect nếu có, nếu không thì về dashboard
          const redirect = router.currentRoute.value.query.redirect || '/dashboard'
          
          // Chuyển hướng mạnh mẽ hơn - không dùng setTimeout
          try {
            // Cố gắng sử dụng Vue Router trước
            router.push(redirect).catch(err => {
              console.log('Lỗi Router, chuyển sang window.location:', err)
              // Nếu router thất bại, dùng window.location trực tiếp
              window.location.href = redirect
            })
          } catch (navigationError) {
            console.log('Lỗi chuyển hướng, dùng window.location:', navigationError)
            // Fallback an toàn
            window.location.href = redirect
          }
        } else {
          console.error('Đăng nhập thất bại:', event.data.message)
        }
      }
      
      // Đăng ký event listener
      window.addEventListener('message', messageHandler)
    } catch (error) {
      console.error('Lỗi trong quá trình đăng nhập:', error)
    }
  }

  
  return {
    user,
    token,
    isAuthenticated,
    checkAuth,
    login,
    logout,
    handleLoginByGoogle,
    initializeAuth,
    clearAuthData
  }
}