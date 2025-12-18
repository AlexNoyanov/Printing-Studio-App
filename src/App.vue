<template>
  <div id="app">
    <nav v-if="isAuthenticated" class="navbar">
      <div class="nav-container">
        <div class="nav-left">
          <div class="logo-section">
            <img :src="logoImage" alt="Logo" class="logo-image" />
          </div>
          <h1 class="app-title">{{ appTitle }}</h1>
        </div>

        <button
          type="button"
          class="nav-toggle"
          @click="toggleNav"
          aria-label="Toggle navigation"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        <div :class="['nav-links', { 'nav-links--open': isNavOpen }]">
          <router-link to="/orders" v-if="userRole === 'user'" @click="closeNav">
            My Orders
          </router-link>
          <router-link to="/create-order" v-if="userRole === 'user'" @click="closeNav">
            Create Order
          </router-link>
          <router-link
            to="/your-filaments"
            v-if="userRole === 'user' && hasUserFilaments"
            @click="closeNav"
          >
            Your Filaments
          </router-link>
          <router-link to="/dashboard" v-if="userRole === 'printer'" @click="closeNav">
            Dashboard
          </router-link>
          <router-link
            to="/filaments"
            v-if="userRole === 'printer' && hasPrinterFilaments"
            @click="closeNav"
          >
            Filaments
          </router-link>
          <router-link to="/shop" @click="closeNav">
            Shop
          </router-link>
          <button @click="logout" class="logout-btn">
            {{ currentUsername ? `${currentUsername}, Logout` : 'Logout' }}
          </button>
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
const isNavOpen = ref(false)

const toggleNav = () => {
  isNavOpen.value = !isNavOpen.value
}

const closeNav = () => {
  isNavOpen.value = false
}

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
    isNavOpen.value = false
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
  closeNav()
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
  position: sticky;
  top: 0;
  z-index: 20;
  padding: 0.75rem 1.5rem;
  background: rgba(3, 7, 18, 0.92);
  backdrop-filter: blur(18px);
  border-bottom: 1px solid rgba(148, 163, 184, 0.28);
  margin-bottom: 1.5rem;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  position: relative;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  min-width: 0;
}

.logo-section {
  display: flex;
  align-items: center;
}

.logo-image {
  height: 40px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 0 10px rgba(148, 163, 184, 0.35));
}

.app-title {
  color: #e5e7eb;
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-toggle {
  display: none;
  width: 40px;
  height: 40px;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.4);
  background: radial-gradient(circle at top left, rgba(148, 163, 184, 0.16), transparent 55%);
  padding: 0;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.nav-toggle span {
  display: block;
  width: 18px;
  height: 2px;
  border-radius: 999px;
  background: #e5e7eb;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.nav-toggle span + span {
  margin-top: 4px;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-links a {
  text-decoration: none;
  color: #e5e7eb;
  font-weight: 500;
  font-size: 0.9rem;
  padding: 0.25rem 0;
  border-radius: 999px;
  transition: color 0.2s ease, background-color 0.2s ease;
  white-space: nowrap;
}

.nav-links a:hover {
  background: rgba(148, 163, 184, 0.14);
  color: #e5e7eb;
}

.nav-links a.router-link-active {
  color: #38bdf8;
}

.logout-btn {
  background: transparent;
  color: #e5e7eb;
  border: 1px solid rgba(148, 163, 184, 0.6);
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.85rem;
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
  white-space: nowrap;
}

.logout-btn:hover {
  background: rgba(248, 250, 252, 0.06);
  border-color: rgba(248, 250, 252, 0.8);
  color: #f9fafb;
}

/* Responsive Design */
@media (max-width: 900px) {
  .app-title {
    font-size: 0.95rem;
  }
}

@media (max-width: 768px) {
  .navbar {
    padding: 0.6rem 1rem;
    margin-bottom: 1rem;
  }

  .nav-toggle {
    display: inline-flex;
  }

  .nav-links {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.6rem;
    padding: 0.75rem 0.9rem;
    border-radius: 0.9rem;
    background: rgba(15, 23, 42, 0.98);
    border: 1px solid rgba(148, 163, 184, 0.4);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.85);
    flex-direction: column;
    align-items: stretch;
    gap: 0.4rem;
    min-width: 220px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-8px);
    transition: opacity 0.18s ease, transform 0.18s ease;
  }

  .nav-links--open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }

  .nav-links a,
  .logout-btn {
    width: 100%;
    text-align: left;
    padding: 0.5rem 0.75rem;
    border-radius: 0.6rem;
  }

  .nav-links a:hover {
    background: rgba(148, 163, 184, 0.16);
  }
}
</style>

