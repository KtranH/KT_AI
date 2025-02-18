import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Hàm để cập nhật CSRF token
const updateCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    } else {
        console.error('CSRF token not found');
    }
};

// Cập nhật token ban đầu
updateCsrfToken();

// Xử lý lỗi CSRF token mismatch
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && (error.response.status === 419 || error.response.status === 401)) {
            // Nếu token hết hạn hoặc không hợp lệ
            console.error('CSRF token mismatch or session expired');
            
            // Reload trang hiện tại để làm mới token
            window.location.reload();
            
            return Promise.reject(new Error('Phiên làm việc đã hết hạn, vui lòng thử lại'));
        }
        return Promise.reject(error);
    }
);

// Thêm event listener để cập nhật token khi DOM thay đổi
const observer = new MutationObserver(() => {
    updateCsrfToken();
});

// Theo dõi thay đổi trong head để cập nhật token
observer.observe(document.head, {
    childList: true,
    subtree: true
});
