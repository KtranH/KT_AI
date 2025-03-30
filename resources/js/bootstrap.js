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
