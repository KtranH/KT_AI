import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Hàm để cập nhật CSRF token
function updateCsrfToken() {
  const tokenMeta = document.querySelector('meta[name="csrf-token"]')
  if (tokenMeta) {
    const token = tokenMeta.getAttribute('content')
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }
  }
}

// Cập nhật token ban đầu
updateCsrfToken()

// Xử lý lỗi CSRF token mismatch
axios.interceptors.response.use(
  response => response,
  error => {
    // Nếu token hết hạn hoặc không hợp lệ
    if (error.response && error.response.status === 419) {
      // Reload trang hiện tại để làm mới token
      window.location.reload()
    }
    return Promise.reject(error)
  }
)

// Thêm event listener để cập nhật token khi DOM thay đổi
const observer = new MutationObserver(mutations => {
  mutations.forEach(mutation => {
    if (mutation.target.tagName === 'HEAD') {
      updateCsrfToken()
    }
  })
})

// Theo dõi thay đổi trong head để cập nhật token
observer.observe(document.head, {
  childList: true,
  subtree: true
})

// Cấu hình Laravel Echo và Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Enable debug khi developing
Pusher.logToConsole = import.meta.env.DEV;

window.Pusher = Pusher;

// Lấy CSRF token từ thẻ meta
const getCsrfToken = () => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
};

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
    // Xử lý lỗi kết nối
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                // Lấy CSRF token mới nhất mỗi khi xác thực
                options.auth.headers['X-CSRF-TOKEN'] = getCsrfToken();
                
                axios.post('/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                }, {
                    headers: options.auth.headers
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    console.error('Laravel Echo xác thực kênh thất bại:', error);
                    callback(true, error);
                });
            }
        };
    }
});
