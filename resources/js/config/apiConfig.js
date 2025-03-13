import axios from 'axios'

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
    // Xử lý response thành công
    return response 
  },
  error => {
    // Xử lý các lỗi phổ biến
    if (error.response) {
      // Lỗi server trả về với status code
      const statusCode = error.response.status
      
      // Xử lý lỗi 401 - Unauthorized
      if (statusCode === 401) {
        // Nếu token hết hạn, đăng xuất người dùng
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        localStorage.removeItem('remember')
        sessionStorage.removeItem('token')
        sessionStorage.removeItem('user')
        
        // Chuyển hướng đến trang đăng nhập
        window.location.href = '/login'
      }
      
      // Thêm các xử lý lỗi khác nếu cần
    }
    
    return Promise.reject(error)
  }
)

export default apiClient
