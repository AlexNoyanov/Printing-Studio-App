<template>
  <div id="app">
    <nav v-if="isAuthenticated" class="navbar">
      <div class="nav-container">
        <div class="logo-section">
          <img :src="logoImage" alt="Logo" class="logo-image" />
        </div>
        <h1 class="app-title">{{ appTitle }}</h1>
        <div class="nav-links">
          <router-link to="/orders" v-if="userRole === 'user'">My Orders</router-link>
          <router-link to="/create-order" v-if="userRole === 'user'">Create Order</router-link>
          <router-link to="/your-filaments" v-if="userRole === 'user' && hasUserFilaments">Your Filaments</router-link>
          <router-link to="/dashboard" v-if="userRole === 'printer'">Dashboard</router-link>
          <router-link to="/filaments" v-if="userRole === 'printer' && hasPrinterFilaments">Filaments</router-link>
          <button @click="logout" class="logout-btn">{{ currentUsername ? `${currentUsername}, Logout` : 'Logout' }}</button>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed, watch, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from './utils/storage'
import logoImage from './logos/Logo-black.png'

const router = useRouter()
const hasUserFilaments = ref(false)
const hasPrinterFilaments = ref(false)

const isAuthenticated = computed(() => {
  return localStorage.getItem('currentUser') !== null
})

const userRole = computed(() => {
  const user = localStorage.getItem('currentUser')
  if (!user) return null
  try {
    const userData = JSON.parse(user)
    return userData.role || 'user'
  } catch {
    return 'user'
  }
})

const currentUsername = computed(() => {
  const user = localStorage.getItem('currentUser')
  if (!user) return null
  try {
    const userData = JSON.parse(user)
    return userData.username || null
  } catch {
    return null
  }
})

const appTitle = computed(() => {
  if (userRole.value === 'printer') {
    return '3D Printing Studio'
  } else if (userRole.value === 'user') {
    return 'Order 3D Print'
  }
  return '3D Printing App'
})

// Check if user has filaments (only for clients)
const checkUserFilaments = async () => {
  if (userRole.value !== 'user') {
    hasUserFilaments.value = false
    return
  }

  try {
    const userStr = localStorage.getItem('currentUser')
    if (!userStr) {
      hasUserFilaments.value = false
      return
    }
    const userData = JSON.parse(userStr)
    const filaments = await storage.getUserFilaments(userData.id)
    hasUserFilaments.value = filaments.length > 0
  } catch (e) {
    console.error('Error checking user filaments:', e)
    hasUserFilaments.value = false
  }
}

// Check if printer has materials/colors (filaments)
const checkPrinterFilaments = async () => {
  if (userRole.value !== 'printer') {
    hasPrinterFilaments.value = false
    return
  }

  try {
    const userStr = localStorage.getItem('currentUser')
    if (!userStr) {
      hasPrinterFilaments.value = false
      return
    }
    const userData = JSON.parse(userStr)
    
    // Check both materials and colors (for backward compatibility)
    const [materials, colors] = await Promise.all([
      storage.getMaterials(userData.id),
      storage.getColors(userData.id)
    ])
    
    hasPrinterFilaments.value = materials.length > 0 || colors.length > 0
  } catch (e) {
    console.error('Error checking printer filaments:', e)
    hasPrinterFilaments.value = false
  }
}

// Update document title based on user role
watch([appTitle, isAuthenticated], ([title, authenticated]) => {
  if (authenticated) {
    document.title = title
    checkUserFilaments()
    checkPrinterFilaments()
  } else {
    document.title = '3D Printing Studio'
    hasUserFilaments.value = false
    hasPrinterFilaments.value = false
  }
}, { immediate: true })

// Also check when route changes (in case filaments were just assigned/created)
watch(() => router.currentRoute.value.path, () => {
  if (isAuthenticated.value) {
    if (userRole.value === 'user') {
      checkUserFilaments()
    } else if (userRole.value === 'printer') {
      checkPrinterFilaments()
    }
  }
})

onMounted(() => {
  if (isAuthenticated.value) {
    if (userRole.value === 'user') {
      checkUserFilaments()
    } else if (userRole.value === 'printer') {
      checkPrinterFilaments()
    }
  }
})

const logout = () => {
  localStorage.removeItem('currentUser')
  router.push('/login')
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: #1a1a1a;
  min-height: 100vh;
  color: #e0e0e0;
}

#app {
  min-height: 100vh;
  background: #1a1a1a;
}

.navbar {
  background: #000000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
  padding: 1rem 2rem;
  margin-bottom: 2rem;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 1rem;
  position: relative;
}

.logo-section {
  display: flex;
  align-items: center;
  justify-self: start;
}

.logo-image {
  height: 90px;
  width: auto;
  object-fit: contain;
}

.app-title {
  color: #87CEEB;
  font-size: 1.5rem;
  margin: 0;
  text-align: center;
  justify-self: center;
}

.nav-links {
  justify-self: end;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.nav-links a {
  text-decoration: none;
  color: #a0d4e8;
  font-weight: 500;
  transition: all 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
  white-space: nowrap;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  color: #87CEEB;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6), 0 0 15px rgba(135, 206, 235, 0.4);
}

.logout-btn {
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.3s;
  white-space: nowrap;
  font-size: 0.9rem;
}

.logout-btn:hover {
  background: #6bb6d6;
}

/* Responsive Design */
@media (max-width: 968px) {
  .navbar {
    padding: 1rem;
  }

  .nav-container {
    grid-template-columns: auto 1fr;
    gap: 0.5rem;
  }

  .app-title {
    font-size: 1.2rem;
    justify-self: start;
    margin-left: 1rem;
  }

  .nav-links {
    grid-column: 1 / -1;
    justify-self: stretch;
    justify-content: center;
    gap: 1rem;
    margin-top: 0.5rem;
  }

  .nav-links a {
    font-size: 0.9rem;
  }

  .logout-btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
  }
}

@media (max-width: 640px) {
  .navbar {
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  .nav-container {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .logo-section {
    justify-self: center;
  }

  .logo-image {
    height: 60px;
  }

  .app-title {
    font-size: 1rem;
    justify-self: center;
    margin-left: 0;
  }

  .nav-links {
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
  }

  .nav-links a,
  .logout-btn {
    width: 100%;
    text-align: center;
    padding: 0.5rem;
  }
}
</style>

