import { ref, computed, nextTick } from 'vue'
import router from '../../router'
import { authAPI, googleAPI, profileAPI } from '../../services/api'
import { useImageStore } from '@/stores/user/imagesStore'
import { useLikeStore } from '@/stores/user/likeStore'
import { refreshCsrfToken } from '../../config/apiConfig'

// Import biến toàn cục từ useNotifications
import '@/composables/features/social/useNotifications'

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
const isLoggingOut = ref(false);
const isAuthenticated = computed(() => {
  // Web (session cookie): không cần token; Mobile: có token
  const hasValidUser = !!user.value && !!user.value.id && typeof user.value.id === 'number'
  const isMobileClient = typeof navigator !== 'undefined' && /Mobile|Android|iP(ad|hone)/i.test(navigator.userAgent)
  const hasToken = !!token.value
  return hasValidUser && !isAuthLoading.value && !isLoggingOut.value && (isMobileClient ? hasToken : true)
});
// Loading 
const isAuthLoading = ref(false)

export const useAuthStore = () => {
  const storeImage = useImageStore()
  const storeLike = useLikeStore()
  const saveAuthData = (userData, userToken, remember = false) => {
    user.value = userData
    token.value = userToken || null
    isRemembered.value = remember

    localStorage.setItem('user', JSON.stringify(userData))
    if (userToken) {
      localStorage.setItem('token', userToken)
    } else {
      localStorage.removeItem('token')
    }
    localStorage.setItem('remember', remember)

    // Nếu không remember, dữ liệu sẽ bị xóa khi đóng trình duyệt
    if (!remember) {
      sessionStorage.setItem('user', JSON.stringify(userData))
      if (userToken) {
        sessionStorage.setItem('token', userToken)
      } else {
        sessionStorage.removeItem('token')
      }
    }
  }

  const clearAuthData = () => {
    console.log('🐛 Before clearAuthData:', {
      user: user.value?.email,
      token: !!token.value,
      isRemembered: isRemembered.value
    })
    
    user.value = null
    token.value = null
    isRemembered.value = false
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('remember')
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('token')
    sessionStorage.removeItem('remember')
    
    console.log('🐛 After clearAuthData:', {
      user: user.value,
      token: token.value,
      isRemembered: isRemembered.value,
      isAuthenticated: isAuthenticated.value
    })

    // Force trigger reactivity để components update ngay lập tức
    nextTick(() => {
      // Components sẽ re-render sau khi DOM update cycle hoàn tất
      console.log('🐛 NextTick triggered - Components should update now')
    })

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
    isAuthLoading.value = true;
    try {
      if (isLoggingOut.value) {
        isAuthLoading.value = false;
        return false
      }

      // Nếu không remember, kiểm tra trong sessionStorage
      if (!isRemembered.value) {
        const sessionUser = sessionStorage.getItem('user')

        if (!sessionUser) {
          clearAuthData()
          isAuthLoading.value = false;
          return false
        }

        // Session authentication: token có thể null (dùng session cookie)
        const sessionToken = sessionStorage.getItem('token')
        token.value = sessionToken || null
        user.value = JSON.parse(sessionUser)
      }

      if (!user.value) {
        isAuthLoading.value = false;
        return false
      }

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
          isAuthLoading.value = false;
          return true
        } else {
          clearAuthData()
          isAuthLoading.value = false;
          return false
        }
      } else {
        clearAuthData()
        isAuthLoading.value = false;
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
      isAuthLoading.value = false;
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
      console.log('🔐 Starting login with double protection (CSRF + Sanctum)...')
      
      // Lấy CSRF token mới trước khi đăng nhập
      await refreshCsrfToken()

      const isMobileClient = typeof navigator !== 'undefined' && /Mobile|Android|iP(ad|hone)/i.test(navigator.userAgent)
      const response = isMobileClient ? await authAPI.mobileLogin(credentials) : await authAPI.login(credentials)
      
      console.log('✅ Login successful with double protection')
      
      // Cấu trúc mới: {success: true, data: {user: {...}, token: "...", remember: true}, message: "Đăng nhập thành công"}
      if (response.data && response.data.success && response.data.data) {
        const loginData = response.data.data
        
        // Kiểm tra nếu cần verification
        if (loginData.needs_verification) {
          return loginData
        }
        
        // Web: có thể không có token (session cookie). Mobile: có token
        saveAuthData(loginData.user, loginData.token || null, !!loginData.remember)
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
      console.error('❌ Login failed with double protection:', error.response?.status, error.message)
      throw error
    }
  }

  const logout = async () => {
    try {
      console.log('🔐 Starting logout with double protection (CSRF + Sanctum)...')
      
      // Set flag để tránh conflict với router guard
      isLoggingOut.value = true
      
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
      
      // Reset logout flag SAU khi đã clear xong data
      isLoggingOut.value = false

      // Sử dụng router.replace để giữ tính chất SPA
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
        isLoggingOut.value = false // Reset logout flag

        // Sử dụng router.replace để giữ tính chất SPA
        router.replace('/login')
        return
      }

      await refreshCsrfToken()
      isLoggingOut.value = false
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
        // Kiểm tra origin để đảm bảo bảo mật
        const allowedOrigins = [
          window.location.origin,
          'http://localhost:8000',
          'http://127.0.0.1:8000'
        ]
        if (!allowedOrigins.includes(event.origin)) return

        const { success, user, auth, message } = event.data

        if (success && user) {
          // Thử đóng popup một cách an toàn
          try {
            // Kiểm tra xem popup vẫn còn mở và có thể truy cập
            if (popup && !popup.closed && popup.close) {
              popup.close()
            }
          } catch (error) {}
          // Google OAuth giờ dùng session (không có token)
          // auth.token có thể null hoặc undefined cho web session
          const token = auth?.token || null
          const remember = auth?.remember || true
          
          // Lưu thông tin đăng nhập (có thể không có token cho web)
          saveAuthData(user, token, remember)

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
    isAuthLoading,
    isRemembered,
    isLoggingOut,
    checkAuth,
    login,
    logout,
    handleLoginByGoogle,
    initializeAuth
  }
}