import './bootstrap';
import { createApp } from 'vue'
import App from './components/App.vue'
import VueFullPage from 'vue-fullpage.js'

const app = createApp(App)
app.use(VueFullPage)
app.mount("#app")
