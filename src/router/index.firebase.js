import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import CreateOrder from '../views/CreateOrder.vue'
import MyOrders from '../views/MyOrders.vue'
import Dashboard from '../views/Dashboard.vue'
import FilamentsList from '../views/FilamentsList.vue'
import Filaments from '../views/Filaments.vue'
import YourFilaments from '../views/YourFilaments.vue'
import Shop from '../views/Shop.vue'
import ClientLogin from '../views/ClientLogin.vue'
import ClientRegister from '../views/ClientRegister.vue'
import ClientHome from '../views/ClientHome.vue'
import ClientCreateOrder from '../views/ClientCreateOrder.vue'
import ClientMyOrders from '../views/ClientMyOrders.vue'

const routes = [
  {
    path: '/',
    redirect: '/client-login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/client-login',
    name: 'ClientLogin',
    component: ClientLogin
  },
  {
    path: '/client-register',
    name: 'ClientRegister',
    component: ClientRegister
  },
  {
    path: '/client-home',
    name: 'ClientHome',
    component: ClientHome,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/client-create-order',
    name: 'ClientCreateOrder',
    component: ClientCreateOrder,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/create-order',
    name: 'CreateOrder',
    component: CreateOrder,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/orders',
    name: 'MyOrders',
    component: ClientMyOrders,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/your-filaments',
    name: 'YourFilaments',
    component: YourFilaments,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, requiresRole: 'printer' }
  },
  {
    path: '/filaments',
    name: 'FilamentsList',
    component: FilamentsList,
    meta: { requiresAuth: true, requiresRole: 'printer' }
  },
  {
    path: '/filaments/:id',
    name: 'Filaments',
    component: Filaments,
    meta: { requiresAuth: true, requiresRole: 'printer' }
  },
  {
    path: '/shop',
    name: 'Shop',
    component: Shop,
    meta: { requiresAuth: true }
  }
]

// Firebase version - uses root path
const router = createRouter({
  history: createWebHistory('/'),
  routes
})

router.beforeEach((to, from, next) => {
  const currentUser = localStorage.getItem('currentUser')
  
  if (to.meta.requiresAuth && !currentUser) {
    // Redirect to client login for client pages, regular login for others
    if (to.path.startsWith('/client-')) {
      next('/client-login')
    } else {
      next('/login')
    }
    return
  }
  
  if (to.meta.requiresAuth && currentUser) {
    try {
      const userData = JSON.parse(currentUser)
      const requiredRole = to.meta.requiresRole
      
      if (requiredRole && userData.role !== requiredRole) {
        // Redirect based on role
        if (userData.role === 'user') {
          // Redirect to client home for client pages
          if (to.path.startsWith('/client-')) {
            next('/client-home')
          } else {
            next('/orders')
          }
        } else if (userData.role === 'printer') {
          next('/dashboard')
        } else {
          next('/client-login')
        }
        return
      }
    } catch (e) {
      next('/client-login')
      return
    }
  }
  
  next()
})

export default router
