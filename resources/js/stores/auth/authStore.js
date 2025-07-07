import { ref, computed } from 'vue'
import router from '../../router'
import { authAPI, googleAPI, profileAPI } from '../../services/api'
import { useImageStore } from '@/stores/user/imagesStore'
import { useLikeStore } from '@/stores/user/likeStore'
import { refreshCsrfToken } from '../../config/apiConfig'

// Import biến toàn cục từ useNotifications
import '@/composables/core/useNotifications'

// Reactive state
const parseUserData = () => {
  try {
    const userData = JSON.parse(localStorage.getItem('user') || 'null');
    if (userData && !userData.id) {
      console.warn('User data không có ID', userData);
      return null;
    }
    return userData;
  } catch (e) {
    console.error('Lỗi khi parse user data:', e);
    localStorage.removeItem('user');
    return null;
  }
};

const user = ref(parseUserData());
const token = ref(localStorage.getItem('token') || null);
const isRemembered = ref(localStorage.getItem('remember') === 'true');
const isAuthenticated = computed(() => !!user.value && !!user.value.id);

export const useAuthStore = () => {
  const storeImage = useImageStore()
  const storeLike = useLikeStore()
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

      // Thêm timeout cho API call để tránh bị treo
      const timeoutPromise = new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Auth check timeout')), 5000)
      )

      const response = await Promise.race([
        authAPI.check(),
        timeoutPromise
      ])

      // Cấu trúc mới: {success: true, data: {authenticated: true, user: {...}}, message: null}
      if (response.data && response.data.success && response.data.data) {
        const authData = response.data.data
        if (authData.authenticated) {
          if (isRemembered.value) {
            saveAuthData(authData.user, token.value, true)
          } else {
            saveAuthData(authData.user, token.value, false)
          }
          return true
        } else {
          clearAuthData()
          return false
        }
      } else {
        clearAuthData()
        return false
      }
    } catch (error) {
      if (error.message === 'Auth check timeout') {
        console.warn('Auth check timed out - proceeding without authentication check')
      } else {
        console.error('Error checking auth:', error)
      }
      
      // Nếu có lỗi network hoặc timeout, vẫn giữ token cục bộ một thời gian ngắn
      // thay vì xóa ngay lập tức, trừ khi là lỗi 401/403
      if (error.response && (error.response.status === 401 || error.response.status === 403)) {
        clearAuthData()
      }
      
      return false
    }
  }

  // Hàm khởi tạo để thay thế onMounted
  const initializeAuth = async () => {
    // Luôn lấy CSRF token mới khi khởi động ứng dụng
    await refreshCsrfToken()

    // Kiểm tra trạng thái đăng nhập
    await checkAuth()
  }

  const login = async (credentials) => {
    try {
      // Lấy CSRF token mới trước khi đăng nhập
      await refreshCsrfToken()

      const response = await authAPI.login(credentials)
      
      // Cấu trúc mới: {success: true, data: {user: {...}, token: "...", remember: true}, message: "Đăng nhập thành công"}
      if (response.data && response.data.success && response.data.data) {
        const loginData = response.data.data
        
        // Kiểm tra nếu cần verification
        if (loginData.needs_verification) {
          return loginData
        }
        
        saveAuthData(loginData.user, loginData.token, loginData.remember)
        
        setTimeout(() => {
          router.push(router.currentRoute.value.query.redirect || '/dashboard')
        }, 100)
        
        return loginData
      } else {
        if (response.data.needs_verification) {
          return response.data
        }
        saveAuthData(response.data.user, response.data.token, response.data.remember)
        
        setTimeout(() => {
          router.push(router.currentRoute.value.query.redirect || '/dashboard')
        }, 100)
        
        return response.data
      }
    } catch (error) {
      throw error
    }
  }

  const logout = async () => {
    try {
      // Lấy CSRF token mới trước khi đăng xuất
      await refreshCsrfToken()

      // Dọn dẹp kết nối Echo trước khi đăng xuất để tránh thông báo không cần thiết
      if (window.Echo) {
        // Dọn dẹp biến toàn cục từ useNotifications nếu tồn tại
        if (typeof globalEchoConnection !== 'undefined' && globalEchoConnection) {
          globalEchoConnection.stopListening('notification')
          globalEchoConnection = null
          
          if (typeof connectedUserId !== 'undefined') {
            connectedUserId = null
          }
          
          if (typeof isListeningToNotifications !== 'undefined') {
            isListeningToNotifications = false
          }
        }
        
        // Ngắt kết nối tất cả các kênh
        window.Echo.disconnect()
      }

      const response = await authAPI.logout()
      storeImage.clearImages()
      storeImage.clearImagesCreatedByUser()
      storeLike.clearLikes()
      clearAuthData()

      // Cấu trúc mới: {success: true, data: {csrf_token: "...", logged_out: true}, message: "Đăng xuất thành công"}
      let csrfToken = null
      if (response.data && response.data.success && response.data.data) {
        csrfToken = response.data.data.csrf_token
      } else if (response.data && response.data.csrf_token) {
        // Fallback cho response structure cũ
        csrfToken = response.data.csrf_token
      }

      // Cập nhật CSRF token mới từ phản hồi
      if (csrfToken) {
        document.cookie = `XSRF-TOKEN=${csrfToken}; path=/`
      }

      // Gọi refreshCsrfToken một lần nữa sau khi đăng xuất để đảm bảo token mới nhất
      await refreshCsrfToken()

      // Sử dụng router.replace thay vì router.push để tránh lịch sử điều hướng không cần thiết
      router.replace('/login')

    } catch (error) {
      console.error('Logout error:', error)

      // Xử lý lỗi CSRF token mismatch (419)
      if (error.response && error.response.status === 419) {
        // Vẫn xóa dữ liệu người dùng
        storeImage.clearImages()
        storeImage.clearImagesCreatedByUser()
        storeLike.clearLikes()
        clearAuthData()

        // Sử dụng router để điều hướng thay vì window.location.reload
        router.replace('/login')
        return
      }

      // Vẫn phải cập nhật CSRF token ngay cả khi có lỗi khác
      await refreshCsrfToken()
      
      // Luôn chuyển hướng về trang đăng nhập sau khi đăng xuất
      router.replace('/login')
    }
  }

  const handleLoginByGoogle = async (onPopupClosed) => {
    try {
      const response = await googleAPI.getAuthUrl()
      const googleAuthUrl = response.data.data.url

      const popup = window.open(googleAuthUrl, 'Google Login', 'width=500,height=600')

      // Lắng nghe phản hồi từ popup
      const messageHandler = (event) => {
        if (event.origin !== window.location.origin) return

        const { success, user, auth, message } = event.data

        if (success && user && auth && auth.token) {
          // Thử đóng popup, nếu không được thì bỏ qua lỗi
          try {
            popup.close()
          } catch (error) {
            console.log('Không thể đóng popup (Cross-Origin-Opener-Policy):', error)
            // Popup sẽ tự đóng hoặc user đóng thủ công
          }
          
          // Lưu thông tin đăng nhập với cấu trúc đúng
          saveAuthData(user, auth.token, auth.remember || true)

          // Sử dụng router.push() thay vì window.location.reload()
          setTimeout(() => {
            router.push('/dashboard')
          }, 100)
        } else {
          console.error('Đăng nhập Google thất bại:', message || event.data.message || 'Lỗi không xác định')
        }
        
        // Xóa event listener
        window.removeEventListener('message', messageHandler)
      }

      window.addEventListener('message', messageHandler)

      // Xử lý trường hợp popup bị đóng thủ công
      const checkClosed = setInterval(() => {
        if (popup.closed) {
          clearInterval(checkClosed)
          window.removeEventListener('message', messageHandler)
          if(typeof onPopupClosed === 'function') {
            onPopupClosed()
          }
        }
      }, 1000)

    } catch (error) {
      console.error('Lỗi trong quá trình đăng nhập Google:', error)
      if(typeof onPopupClosed === 'function') {
        onPopupClosed()
      }
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
    initializeAuth
  }
}