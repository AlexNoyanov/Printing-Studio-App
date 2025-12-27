<template>
  <div id="app">
    <nav v-if="isAuthenticated" class="navbar">
      <div class="nav-container">
        <div class="logo-section">
          <img :src="logoImage" alt="Logo" class="logo-image" />
        </div>
        <div class="nav-links">
          <router-link to="/home" v-if="userRole === 'user'">Главная</router-link>
          <router-link to="/orders" v-if="userRole === 'user'">Мои заказы</router-link>
          <router-link to="/create-order" v-if="userRole === 'user'">Создать заказ</router-link>
          <button @click="logout" class="logout-btn">
            {{ currentUsername ? `${currentUsername}, Выход` : 'Выход' }}
          </button>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from './utils/storage'
import { setCurrentLanguage, setUserLanguage } from './utils/i18n'
import logoImage from './logos/logo-black-small.png'

const router = useRouter()

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
  background: #000000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 1000;
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
  color: #ffffff;
  font-weight: 500;
  transition: color 0.3s ease;
}

.nav-links a:hover,
.nav-links a.router-link-active {
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

