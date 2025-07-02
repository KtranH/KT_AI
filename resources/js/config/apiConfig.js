import axios from 'axios'
import { useAuthStore } from '../stores/auth/authStore'

// Hàm lấy CSRF token mới từ server
export const refreshCsrfToken = async () => {
  try {
    // Gọi đến route csrf của Laravel để lấy token mới
    const response = await axios.get('/sanctum/csrf-cookie', {
      withCredentials: true // Quan trọng để cookies được lưu
    })
    
    // Lấy token từ cookie để cập nhật header
    const cookies = document.cookie.split(';')
    const xsrfCookie = cookies.find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))
    
    if (xsrfCookie) {
      const token = decodeURIComponent(xsrfCookie.trim().substring('XSRF-TOKEN='.length))
      axios.defaults.headers.common['X-XSRF-TOKEN'] = token
      apiClient.defaults.headers.common['X-XSRF-TOKEN'] = token
      console.log('CSRF token đã được cập nhật thành công')
      return token
    } else {
      console.error('Không tìm thấy XSRF-TOKEN cookie sau khi refresh')
      return null
    }
  } catch (error) {
    console.error('Lỗi khi cập nhật CSRF token:', error)
    return null
  }
}

// Hàm để lấy CSRF token từ cookie
export const getCsrfTokenFromCookie = () => {
  const cookies = document.cookie.split(';')
  const xsrfCookie = cookies.find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))
  
  if (xsrfCookie) {
    return decodeURIComponent(xsrfCookie.trim().substring('XSRF-TOKEN='.length))
  }
  
  return null
}

// Tạo instance axios với cấu hình mặc định
const apiClient = axios.create({
  baseURL: '/api', // Base URL của API
  timeout: 30000, // Timeout mặc định là 30 giây
  withCredentials: true, // Cookies được gửi trong mọi request
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Interceptor cho request
apiClient.interceptors.request.use(
  config => {
    // Kiểm tra có token không
    let token = null;
    
    // Kiểm tra trong local storage
    const storedToken = localStorage.getItem('token');
    if (storedToken) {
      token = storedToken;
    }
    
    // Kiểm tra trong session storage nếu không có trong local storage
    if (!token) {
      const sessionToken = sessionStorage.getItem('token');
      if (sessionToken) {
        token = sessionToken;
      }
    }
    
    // Thêm token vào header nếu có
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Thêm CSRF token từ cookie vào header nếu có
    const csrfToken = getCsrfTokenFromCookie();
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = csrfToken;
    }
    
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Interceptor cho response
apiClient.interceptors.response.use(
  response => {
    return response;
  },
  async error => {
    console.error('API Error:', error.response ? error.response.status : error.message);
    
    // Xử lý lỗi CSRF token mismatch
    if (error.response && error.response.status === 419) {
      console.log('CSRF token mismatch. Đang làm mới token...');
      
      // Lưu lại request gốc
      const originalRequest = error.config;
      
      // Đảm bảo không lặp vô hạn
      if (!originalRequest._retry) {
        originalRequest._retry = true;
        
        try {
          // Lấy CSRF token mới
          const newToken = await refreshCsrfToken();
          
          if (newToken) {
            // Cập nhật header với token mới
            originalRequest.headers['X-XSRF-TOKEN'] = newToken;
            
            // Thử lại request ban đầu với token mới
            return apiClient(originalRequest);
          }
        } catch (refreshError) {
          console.error('Lỗi khi làm mới CSRF token:', refreshError);
          return Promise.reject(error);
        }
      }
    }
    
    // Kiểm tra lỗi validation từ Laravel
    if (error.response && error.response.status === 422) {
      console.log('Validation error:', error.response.data);
      
      // Kiểm tra nếu là API check và đã đăng nhập qua Google
      if (error.config && error.config.url === '/check' && (localStorage.getItem('token') || sessionStorage.getItem('token'))) {
        console.log('Auth check validation error, đã đăng nhập qua Google');
        return Promise.resolve({
          data: {
            authenticated: true,
            user: JSON.parse(localStorage.getItem('user') || sessionStorage.getItem('user') || '{}')
          }
        });
      }
    }
    
    // Kiểm tra lỗi xác thực 401 hoặc 403
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      console.log('Lỗi xác thực:', error.response.status);
      
      // Xử lý đăng xuất bằng cách xóa dữ liệu người dùng
      try {
        // Fallback nếu không thể sử dụng authStore
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        
        // Chỉ thử dùng authStore nếu gọi api bắt buộc đăng nhập
        if (!error.config.url.includes('notifications') && !error.config.url.includes('/user')) {
          try {
            const auth = useAuthStore();
            if (auth && typeof auth.clearAuthData === 'function') {
              auth.clearAuthData();
              console.log('Đã xóa dữ liệu xác thực bằng authStore');
            }
          } catch (authError) {
            console.log('Không thể sử dụng authStore:', authError);
          }
        }
      } catch (e) {
        console.error('Lỗi khi xử lý clearAuthData:', e);
      }
      
      return Promise.reject(error);
    }
    
    return Promise.reject(error);
  }
);

export default apiClient
