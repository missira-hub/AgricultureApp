import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'

const app = createApp(App)

app.use(createPinia()) // ✅ register Pinia
app.use(router)

app.mount('#app')
import axios from 'axios';

const token = localStorage.getItem('token');

axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:8000'; // Your backend URL


if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
