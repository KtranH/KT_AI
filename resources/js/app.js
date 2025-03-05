import './bootstrap';
import { createApp } from 'vue'
import App from './App.vue'
import VueFullPage from 'vue-fullpage.js'
import 'fullpage.js/dist/fullpage.css'
import router from './router'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { createPinia } from 'pinia';
import piniaPersist from 'pinia-plugin-persistedstate';

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
pinia.use(piniaPersist);

// Use plugins
app.use(router)
app.use(VueFullPage, {
  licenseKey: 'OPEN-SOURCE-GPLV3-LICENSE',
  scrollOverflow: true,
  css3: true,
  credits: { enabled: false }
})

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