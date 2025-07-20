import axios from 'axios'
import { useAuthStore } from '../stores/auth/authStore'

// === CSRF TOKEN MANAGEMENT ===

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
      console.log('✅ CSRF token đã được cập nhật thành công')
      return token
    } else {
      console.error('❌ Không tìm thấy XSRF-TOKEN cookie sau khi refresh')
      return null
    }
  } catch (error) {
    console.error('❌ Lỗi khi cập nhật CSRF token:', error)
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

// === API CLIENT CONFIGURATION ===

// Tạo instance axios với cấu hình cho DOUBLE PROTECTION (CSRF + Sanctum)
const apiClient = axios.create({
  baseURL: '/api', // Base URL của API
  timeout: 30000, // Timeout mặc định là 30 giây
  withCredentials: true, // QUAN TRỌNG: Cookies được gửi trong mọi request cho CSRF
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// === REQUEST INTERCEPTOR - DOUBLE PROTECTION ===
apiClient.interceptors.request.use(
  config => {
    console.log(`🔐 Double Protection Request: ${config.method?.toUpperCase()} ${config.url}`)
    
    // === 1. SANCTUM TOKEN PROTECTION ===
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
    
    // Thêm Bearer token vào header nếu có
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      console.log('🔑 Sanctum Bearer token added to request')
    }
    
    // === 2. CSRF TOKEN PROTECTION ===
    const csrfToken = getCsrfTokenFromCookie();
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = csrfToken;
      console.log('🛡️ CSRF token added to request')
    } else {
      console.warn('⚠️ No CSRF token found - this may cause 419 errors for protected endpoints')
    }
    
    return config;
  },
  error => {
    console.error('❌ Request interceptor error:', error)
    return Promise.reject(error);
  }
);

// === RESPONSE INTERCEPTOR - ERROR HANDLING ===
apiClient.interceptors.response.use(
  response => {
    console.log(`✅ Double Protection Response: ${response.status} ${response.config.url}`)
    return response;
  },
  async error => {
    const status = error.response?.status
    const url = error.config?.url
    console.error(`❌ API Error: ${status} ${url}`, error.message);
    
    // === 1. XỬ LÝ LỖI CSRF TOKEN MISMATCH (419) ===
    if (status === 419) {
      console.log('🛡️ CSRF token mismatch detected. Attempting to refresh...');
      
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
            console.log('🔄 Retrying request with new CSRF token...')
            return apiClient(originalRequest);
          } else {
            console.error('❌ Failed to refresh CSRF token')
          }
        } catch (refreshError) {
          console.error('❌ Error refreshing CSRF token:', refreshError);
        }
      } else {
        console.error('❌ Already retried CSRF token refresh - giving up')
      }
    }
    
    // === 2. XỬ LÝ LỖI VALIDATION (422) ===
    if (status === 422) {
      console.log('📝 Validation error:', error.response.data);
      
      // Xử lý đặc biệt cho API check khi đã đăng nhập qua Google
      if (url === '/check' && (localStorage.getItem('token') || sessionStorage.getItem('token'))) {
        console.log('🔍 Auth check validation error for Google login - returning cached user data');
        return Promise.resolve({
          data: {
            authenticated: true,
            user: JSON.parse(localStorage.getItem('user') || sessionStorage.getItem('user') || '{}')
          }
        });
      }
    }
    
    // === 3. XỬ LÝ LỖI SANCTUM AUTHENTICATION (401/403) ===
    if (status === 401 || status === 403) {
      console.log(`🔑 Sanctum authentication error: ${status}`);
      
      // Xử lý đăng xuất bằng cách xóa dữ liệu người dùng
      try {
        // Fallback nếu không thể sử dụng authStore
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        
        // Chỉ thử dùng authStore nếu gọi api bắt buộc đăng nhập
        if (!url?.includes('notifications') && !url?.includes('/user')) {
          try {
            const auth = useAuthStore();
            if (auth && typeof auth.clearAuthData === 'function') {
              auth.clearAuthData();
              console.log('🧹 Auth data cleared via authStore');
            }
          } catch (authError) {
            console.log('⚠️ Cannot use authStore:', authError);
          }
        }
      } catch (e) {
        console.error('❌ Error handling clearAuthData:', e);
      }
      
      return Promise.reject(error);
    }
    
    return Promise.reject(error);
  }
);

export default apiClient
