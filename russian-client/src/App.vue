<template>
  <div id="app">
    <nav class="navbar" :class="{ authenticated: isAuthenticated || isAuthPage }">
      <div class="nav-container">
        <div class="logo-section">
          <img :src="logoImage" alt="Logo" class="logo-image" />
        </div>
        <div class="nav-links">
          <template v-if="isAuthenticated">
            <router-link to="/home" v-if="userRole === 'user'">Главная</router-link>
            <router-link to="/orders" v-if="userRole === 'user'">Мои заказы</router-link>
            <router-link to="/create-order" v-if="userRole === 'user'">Создать заказ</router-link>
            <button @click="logout" class="logout-btn">
              {{ currentUsername ? `${currentUsername}, Выход` : 'Выход' }}
            </button>
          </template>
          <template v-else>
            <router-link to="/" class="nav-link">Главная</router-link>
            <router-link to="/login" class="login-btn">Войти</router-link>
            <router-link to="/register" class="register-btn">Регистрация</router-link>
          </template>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { storage } from './utils/storage'
import { setCurrentLanguage, setUserLanguage } from './utils/i18n'
import logoImage from './logos/p1s-printer.png'

const router = useRouter()
const route = useRoute()

// Force Russian language
setCurrentLanguage('ru')
setUserLanguage('ru')

// Authentication state
const currentUser = ref(null)

const isAuthenticated = computed(() => {
  return currentUser.value !== null
})

const userRole = computed(() => {
  return currentUser.value?.role || null
})

const currentUsername = computed(() => {
  return currentUser.value?.username || null
})

// Check if we're on login or register page - make header black
const isAuthPage = computed(() => {
  return route.path === '/login' || route.path === '/register'
})

// Load user from localStorage
const loadUser = () => {
  try {
    const userStr = localStorage.getItem('currentUser')
    if (userStr) {
      currentUser.value = JSON.parse(userStr)
    }
  } catch (e) {
    console.error('Error loading user:', e)
    currentUser.value = null
  }
}

// Logout
const logout = () => {
  localStorage.removeItem('currentUser')
  currentUser.value = null
  router.push('/')
}

// Watch for auth changes
onMounted(() => {
  loadUser()
  
  // Listen for storage changes (e.g., login from another tab)
  window.addEventListener('storage', (e) => {
    if (e.key === 'currentUser') {
      loadUser()
    }
  })
  
  // Also check on route changes
  router.afterEach(() => {
    loadUser()
  })
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  line-height: 1.6;
  color: #333;
  background: #f5f5f5;
}

#app {
  min-height: 100vh;
}

/* Navbar */
.navbar {
  background: #ffffff;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.navbar.authenticated {
  background: #000000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  border-bottom: none;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-image {
  height: 40px;
  width: auto;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-links a {
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  letter-spacing: 0.3px;
}

.navbar:not(.authenticated) .nav-links a {
  color: #2d3748;
  font-weight: 600;
}

.navbar:not(.authenticated) .nav-links a:hover,
.navbar:not(.authenticated) .nav-links a.router-link-active {
  color: #667eea;
}

.navbar.authenticated .nav-links a {
  color: #ffffff;
  font-weight: 500;
}

.navbar.authenticated .nav-links a:hover,
.navbar.authenticated .nav-links a.router-link-active {
  color: #667eea;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #333333;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #ffffff;
  transition: background 0.3s ease;
}

.logout-btn:hover {
  background: #555555;
}

.nav-link {
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  color: #2d3748;
  letter-spacing: 0.3px;
}

.navbar:not(.authenticated) .nav-link:hover,
.navbar:not(.authenticated) .nav-link.router-link-active {
  color: #667eea;
}

.login-btn {
  padding: 0.65rem 2rem;
  background: transparent;
  border: 2px solid #764ba2;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  color: #764ba2;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
  letter-spacing: 0.3px;
}

.login-btn:hover {
  background: rgba(118, 75, 162, 0.1);
  border-color: #8b5fb8;
  color: #8b5fb8;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(118, 75, 162, 0.25);
}

.register-btn {
  padding: 0.65rem 1.75rem;
  background: transparent;
  border: 2px solid rgba(102, 126, 234, 0.4);
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  color: #667eea;
  text-decoration: none;
  transition: all 0.3s ease;
  letter-spacing: 0.3px;
}

.navbar:not(.authenticated) .register-btn:hover {
  background: rgba(102, 126, 234, 0.05);
  border-color: rgba(102, 126, 234, 0.6);
  color: #667eea;
  transform: translateY(-2px);
}

.navbar.authenticated .register-btn {
  border-color: rgba(255, 255, 255, 0.3);
  color: #ffffff;
}

.navbar.authenticated .register-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.5);
}

.logo-image {
  height: 42px;
  width: auto;
  transition: transform 0.3s ease;
}

.navbar:not(.authenticated) .logo-image {
  filter: brightness(0.9);
}

.logo-section a:hover .logo-image {
  transform: scale(1.05);
}

/* Responsive */
@media (max-width: 768px) {
  .nav-container {
    padding: 1rem;
    flex-wrap: wrap;
  }
  
  .nav-links {
    gap: 1rem;
    font-size: 0.9rem;
  }
}
</style>

