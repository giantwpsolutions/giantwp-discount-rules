import { createApp } from 'vue'
import './style.css'
import './tailwindcss.css'
import App from './App.vue'
import router from './router/router';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css'




// Import translation functions from wp.i18n
const { __, _x, _n, _nx } = wp.i18n;

// Create the Vue app
const app = createApp(App);

// Register translation functions globally
app.config.globalProperties.__ = __;
app.config.globalProperties._x = _x;
app.config.globalProperties._n = _n;
app.config.globalProperties._nx = _nx;

app.use(router);
app.use(ElementPlus);
app.mount('#aio-woodiscount-dashboard')

