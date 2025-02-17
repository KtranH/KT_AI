import './bootstrap';
import { createApp } from 'vue'
import App from './components/App.vue'
import VueFullPage from 'vue-fullpage.js'
import router from './router'
import AOS from 'aos'
import 'aos/dist/aos.css'

const app = createApp(App)

// Use plugins
app.use(router)
app.use(VueFullPage)

// Mount app
app.mount("#app")

// Initialize AOS
AOS.init(
    {
        duration: 800,
        deplay: 500,
        once: false,
        offset: 150,
        easing: 'ease-in-sine',
    }
)

