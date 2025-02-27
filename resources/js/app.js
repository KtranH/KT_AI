import './bootstrap';
import { createApp } from 'vue'
import App from './components/App.vue'
import VueFullPage from 'vue-fullpage.js'
import router from './router'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { createPinia } from 'pinia';

// Cấu hình Axios interceptor để tự động thêm token
axios.interceptors.request.use(config => {
    // Lấy token từ localStorage
    const token = localStorage.getItem('token')
    
    // Nếu token tồn tại, thêm vào header Authorization
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
}, error => {
    return Promise.reject(error)
})

// Tạo chỉ một Vue app instance duy nhất
const app = createApp(App)
// Tạo pinia 
const pinia = createPinia()

// Use plugins
app.use(router)
app.use(VueFullPage)

// Khởi tạo AOS với cấu hình mặc định
AOS.init({
    duration: 300,
    delay: 100,
    once: true,
    offset:150,
    easing: 'ease-in-sine',
})

// Thêm sự kiện để refresh AOS khi chuyển trang
router.afterEach(() => {
    setTimeout(() => {
        AOS.refresh()
    }, 100)
})

// Đảm bảo app chỉ được mount một lần
app.mount("#app")
// Sử dụng pinia
app.use(pinia);