import App from './App.vue'
import VueFullPage from 'vue-fullpage.js'
import router from './router'
import AOS from 'aos'
import piniaPersist from 'pinia-plugin-persistedstate'
import VueSweetalert2 from 'vue-sweetalert2'
import './bootstrap'
import 'fullpage.js/dist/fullpage.css'
import 'aos/dist/aos.css'
import 'sweetalert2/dist/sweetalert2.min.css'
import { createPinia } from 'pinia'
import { registerGlobalComponents } from './components/common'
import { clickOutside } from './directives/clickOutside'
import { createApp } from 'vue'
import PrimeVue from 'primevue/config';

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

// Use pinia
const pinia = createPinia()
pinia.use(piniaPersist);
app.use(pinia)

// Use plugins
app.use(router)
app.use(VueFullPage, {
  licenseKey: 'OPEN-SOURCE-GPLV3-LICENSE',
  scrollOverflow: true,
  css3: true,
  credits: { enabled: false }
})
app.use(VueSweetalert2)
// Use PrimeVue
app.use(PrimeVue);

// Đăng ký các component toàn cục
registerGlobalComponents(app);

// Đăng ký các directives toàn cục
app.directive('click-outside', clickOutside);

// Khởi tạo AOS với cấu hình mặc định
AOS.init({
    duration: 300,
    delay: 50,
    once: false,
    offset: 20,
    easing: 'ease-in-out',
    disable: 'mobile'
})

// Thêm sự kiện để refresh AOS khi chuyển trang
router.afterEach(() => {
    setTimeout(() => {
        AOS.refresh()
    }, 100)
})


// Đảm bảo app chỉ được mount một lần
if (!document.getElementById('app')._vue_app_) {
  app.mount('#app')
  document.getElementById('app')._vue_app_ = true
}