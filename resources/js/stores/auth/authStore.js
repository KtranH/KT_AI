import { ref, computed } from 'vue'
import router from '../../router'
import { authAPI } from '../../services/api'
import { useImageStore } from '@/stores/user/imagesStore'
import { useLikeStore } from '@/stores/user/likeStore'
import { refreshCsrfToken } from '../../config/apiConfig'

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

      const response = await authAPI.check()

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
      if (response.data.needs_verification) {
        return response.data
      }
      saveAuthData(response.data.user, response.data.token, response.data.remember)
      
      // Sử dụng router.push thay vì window.location để tránh reload trang
      // và thêm một khoảng thời gian ngắn để đảm bảo dữ liệu đã được lưu
      setTimeout(() => {
        router.push(router.currentRoute.value.query.redirect || '/dashboard')
      }, 100)
      
      return response.data
    } catch (error) {
      // Lỗi đã được xử lý tự động bởi interceptor, không cần xử lý thêm lỗi CSRF ở đây nữa
      throw error
    }
  }

  const logout = async () => {
    try {
      // Lấy CSRF token mới trước khi đăng xuất
      await refreshCsrfToken()

      const response = await authAPI.logout()
      storeImage.clearImages()
      storeImage.clearImagesCreatedByUser()
      storeLike.clearLikes()
      clearAuthData()

      // Cập nhật CSRF token mới từ phản hồi
      if (response.data && response.data.csrf_token) {
        document.cookie = `XSRF-TOKEN=${response.data.csrf_token}; path=/`
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

  const handleLoginByGoogle = async () => {
    try {
      const response = await axios.get('/auth/google/url')
      const googleAuthUrl = response.data.url

      const popup = window.open(googleAuthUrl, 'Google Login', 'width=500,height=600')

      // Lắng nghe phản hồi từ popup
      window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return

        const { success, token, user } = event.data

        if (success) {
          console.log('Đăng nhập thành công:', user)
          // Lưu thông tin đăng nhập trước, sau đó mới chuyển hướng
          saveAuthData(user, token, true)

          // Sử dụng router.push() thay vì window.location.reload()
          router.push('/dashboard')
        } else {
          console.error('Đăng nhập thất bại:', event.data.message)
        }
      }, { once: true })
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
    initializeAuth
  }
}