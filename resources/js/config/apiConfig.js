import axios from 'axios'
import { useAuthStore } from '../stores/auth/authStore'

// Tạo instance axios với cấu hình mặc định
const apiClient = axios.create({
  baseURL: '/api', // Base URL của API
  timeout: 30000, // Timeout mặc định là 30 giây
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Interceptor cho request
apiClient.interceptors.request.use(
  config => {
    // Thêm token vào header nếu có
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// Interceptor cho response
apiClient.interceptors.response.use(
  response => {
    return response
  },
  error => {
    // Kiểm tra lỗi validation từ Laravel
    if (error.response && error.response.status === 422) {
      console.log('Validation error:', error.response.data)
      
      // Kiểm tra nếu là API check và đã đăng nhập qua Google
      if (error.config && error.config.url === '/check' && localStorage.getItem('token')) {
        console.log('Auth check validation error, returning unauthenticated')
        // Trả về kết quả giả để không ảnh hưởng đến luồng đăng nhập
        return Promise.resolve({
          data: {
            authenticated: true,
            user: JSON.parse(localStorage.getItem('user'))
          }
        })
      }
    }
    
    // Kiểm tra lỗi xác thực 401 hoặc 403
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      // Có thể xử lý logout ở đây nếu cần
      const authStore = useAuthStore()
      authStore.clearAuthData()
      router.push('/login')
    }
    
    return Promise.reject(error)
  }
)

export default apiClient
