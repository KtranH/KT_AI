import './bootstrap';
import { createApp } from 'vue'
import App from './components/App.vue'
import VueFullPage from 'vue-fullpage.js'
import router from './router'

const app = createApp(App)

// Use plugins
app.use(router)
app.use(VueFullPage)

// Mount app
app.mount("#app")
