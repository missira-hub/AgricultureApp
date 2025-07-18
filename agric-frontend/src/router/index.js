import { createRouter, createWebHistory } from 'vue-router'

import Home from '../views/Homepage.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ConsumerDashboard from '../views/consumer/ConsumerDashboard.vue'
import ProductList from '../views/consumer/ProductList.vue'
import CartView from '../views/consumer/cartview.vue'
import Messages from '../views/consumer/Messages.vue'

import About from '../views/About.vue'
import Services from '../views/Services.vue'
import Projects from '../views/Projects.vue'
import News from '../views/News.vue'
import Shop from '../views/Shop.vue'
import Contact from '../views/Contact.vue'

// ✅ Import Farmer Dashboard
import FarmerDashboard from '../views/farmer/FarmerDashboard.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/about', component: About },
  { path: '/services', component: Services },
  { path: '/projects', component: Projects },
  { path: '/news', component: News },
  { path: '/shop', component: Shop },
  { path: '/contact', component: Contact },

  // Auth and dashboards
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/consumer/dashboard', component: ConsumerDashboard },
  { path: '/farmer/dashboard', component: FarmerDashboard }, // ✅ Added Farmer Dashboard

  // Product list and cart
  { path: '/products', component: ProductList },
  { path: '/consumer/cart', component: CartView },

  // Messages
  { path: '/consumer/messages', component: Messages },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
