import { createRouter, createWebHistory } from 'vue-router'
import Landing from '../views/Landing.vue'
import ClientLogin from '../views/ClientLogin.vue'
import ClientRegister from '../views/ClientRegister.vue'
import ClientHome from '../views/ClientHome.vue'
import ClientCreateOrder from '../views/ClientCreateOrder.vue'
import ClientMyOrders from '../views/ClientMyOrders.vue'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: Landing
  },
  {
    path: '/login',
    name: 'ClientLogin',
    component: ClientLogin
  },
  {
    path: '/register',
    name: 'ClientRegister',
    component: ClientRegister
  },
  {
    path: '/home',
    name: 'ClientHome',
    component: ClientHome,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/orders',
    name: 'ClientMyOrders',
    component: ClientMyOrders,
    meta: { requiresAuth: true, requiresRole: 'user' }
  },
  {
    path: '/create-order',
    name: 'ClientCreateOrder',
    component: ClientCreateOrder,
    meta: { requiresAuth: true, requiresRole: 'user' }
  }
]

const router = createRouter({
  history: createWebHistory('/'),
  routes
})

router.beforeEach((to, from, next) => {
  const currentUser = localStorage.getItem('currentUser')
  
  if (to.meta.requiresAuth && !currentUser) {
    next('/login')
    return
  }
  
  if (to.meta.requiresAuth && currentUser) {
    try {
      const userData = JSON.parse(currentUser)
      const requiredRole = to.meta.requiresRole
      
      if (requiredRole && userData.role !== requiredRole) {
        if (userData.role === 'user') {
          next('/home')
        } else {
          next('/login')
        }
        return
      }
    } catch (e) {
      next('/login')
      return
    }
  }
  
  next()
})

export default router

