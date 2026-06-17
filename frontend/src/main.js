import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Keep Bootstrap, but remove FontAwesome and Tabler imports from here
import 'bootstrap/dist/css/bootstrap.min.css'

createApp(App)
    .use(router)
    .mount('#app')