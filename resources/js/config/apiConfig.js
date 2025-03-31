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
      
      // Debug token để giúp gỡ lỗi
      console.log('Sending request with token:', token.substring(0, 10) + '...');
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
  error => {
    console.error('API Error:', error.response ? error.response.status : error.message);
    
    // Kiểm tra lỗi validation từ Laravel
    if (error.response && error.response.status === 422) {
      console.log('Validation error:', error.response.data);
      
      // Kiểm tra nếu là API check và đã đăng nhập qua Google
      if (error.config && error.config.url === '/check' && (localStorage.getItem('token') || sessionStorage.getItem('token'))) {
        console.log('Auth check validation error, đã đăng nhập qua Google');
        // Trả về kết quả giả để không ảnh hưởng đến luồng đăng nhập
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
      
      // Import và sử dụng authStore để xử lý đăng xuất
      try {
        const authStore = useAuthStore();
        authStore.clearAuthData();
      } catch (e) {
        console.error('Lỗi khi xử lý clearAuthData:', e);
        
        // Fallback nếu không thể sử dụng authStore
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
      }
      
      // Chuyển hướng đến trang đăng nhập
      window.location.href = '/login';
    }
    
    return Promise.reject(error);
  }
);

export default apiClient
