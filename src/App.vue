<template>
  <div id="app">
    <!-- Language selector - always visible -->
    <div class="language-selector-container">
      <select 
        :value="currentLanguage" 
        @change="handleLanguageChange" 
        class="language-selector"
      >
        <option value="en">🇬🇧 English</option>
        <option value="ru">🇷🇺 Русский</option>
      </select>
    </div>
    
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
          <router-link to="/shop">Shop</router-link>
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
import { getLanguage, setUserLanguage, getBrowserLanguage, getUserLanguage, getSavedLanguage, setCurrentLanguage, initCurrentLanguage } from './utils/i18n'
import logoImage from './logos/logo-black-small.png'

const router = useRouter()
const hasUserFilaments = ref(false)
const hasPrinterFilaments = ref(false)

// Initialize language system
initCurrentLanguage()

// Get initial language
const getInitialLanguage = () => {
  const savedLang = getSavedLanguage()
  if (savedLang && (savedLang === 'en' || savedLang === 'ru')) {
    return savedLang
  }
  
  const userLang = getUserLanguage()
  if (userLang && (userLang === 'en' || userLang === 'ru')) {
    return userLang
  }
  
  const browserLang = getBrowserLanguage()
  return browserLang === 'ru' ? 'ru' : 'en'
}

const currentLanguage = ref(getInitialLanguage())

// Load user language preference on mount and when authentication changes
const loadLanguagePreference = () => {
  // Check saved language first (persists after logout)
  const savedLang = getSavedLanguage()
  if (savedLang && (savedLang === 'en' || savedLang === 'ru')) {
    currentLanguage.value = savedLang
    return
  }
  
  // Then check user account language
  const userLang = getUserLanguage()
  if (userLang && (userLang === 'en' || userLang === 'ru')) {
    currentLanguage.value = userLang
    setUserLanguage(userLang)
    return
  }
  
  // Fallback to browser language
  const browserLang = getBrowserLanguage()
  currentLanguage.value = browserLang === 'ru' ? 'ru' : 'en'
  setUserLanguage(currentLanguage.value)
}

// Handle language change
const handleLanguageChange = async (event) => {
  const newLanguage = event?.target?.value || currentLanguage.value
  console.log('Language changed to:', newLanguage)
  
  // Update the reactive ref
  currentLanguage.value = newLanguage
  
  // Update the language state immediately
  setCurrentLanguage(newLanguage)
  setUserLanguage(newLanguage)
  
  // If user is logged in, update language in database
  if (isAuthenticated.value) {
    try {
      const userStr = localStorage.getItem('currentUser')
      if (userStr) {
        const userData = JSON.parse(userStr)
        
        // Update user language via API
        const API_BASE = import.meta.env.VITE_API_BASE || (() => {
          if (window.location.hostname.includes('web.app') || window.location.hostname.includes('firebaseapp.com')) {
            return 'https://noyanov.com/Apps/Printing/api'
          }
          return import.meta.env.DEV ? 'https://noyanov.com/Apps/Printing/api' : '/Apps/Printing/api'
        })()
        
        try {
          const response = await fetch(`${API_BASE}/users.php?id=${userData.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ language: newLanguage })
          })
          if (!response.ok) {
            console.warn('Failed to update language in database, but continuing')
          }
        } catch (e) {
          console.error('Failed to update language in database:', e)
          // Continue anyway - language is saved in localStorage
        }
        
        // Update local storage
        userData.language = newLanguage
        localStorage.setItem('currentUser', JSON.stringify(userData))
      }
    } catch (e) {
      console.error('Error updating user language:', e)
      // Still update local storage even if API call fails
      const userStr = localStorage.getItem('currentUser')
      if (userStr) {
        const userData = JSON.parse(userStr)
        userData.language = newLanguage
        localStorage.setItem('currentUser', JSON.stringify(userData))
      }
    }
  }
  
  // Reload page to apply language changes to all components
  // Use a small delay to ensure state is saved
  setTimeout(() => {
    window.location.reload()
  }, 50)
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
    loadLanguagePreference()
  } else {
    document.title = '3D Printing Studio'
    hasUserFilaments.value = false
    hasPrinterFilaments.value = false
    loadLanguagePreference()
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
  loadLanguagePreference()
  
  if (isAuthenticated.value) {
    if (userRole.value === 'user') {
      checkUserFilaments()
    } else if (userRole.value === 'printer') {
      checkPrinterFilaments()
    }
  }
})

const logout = () => {
  // Keep language preference even after logout (it's stored separately)
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

.language-selector-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  padding: 0.5rem;
  border: 1px solid rgba(135, 206, 235, 0.3);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.language-selector {
  background: #1a1a1a;
  color: #b8dce8;
  border: 1px solid rgba(135, 206, 235, 0.3);
  border-radius: 6px;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  outline: none;
}

.language-selector:hover {
  border-color: rgba(135, 206, 235, 0.6);
  background: #2a2a2a;
  box-shadow: 0 0 10px rgba(135, 206, 235, 0.2);
}

.language-selector:focus {
  border-color: #87CEEB;
  box-shadow: 0 0 15px rgba(135, 206, 235, 0.4);
}

.language-selector option {
  background: #1a1a1a;
  color: #b8dce8;
  padding: 0.5rem;
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
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6), 0 0 40px rgba(135, 206, 235, 0.1);
  padding: 0.875rem 2rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid rgba(135, 206, 235, 0.2);
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
}

.logo-section {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.logo-image {
  height: 34px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.logo-image:hover {
  transform: scale(1.05);
}

.app-title {
  display: none; /* Hide title for cleaner, more modern look */
}

.nav-links {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
  margin-left: auto;
}

.nav-links a {
  text-decoration: none;
  color: #a0d4e8;
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  white-space: nowrap;
  position: relative;
  background: transparent;
  border: 1px solid transparent;
}

.nav-links a::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 8px;
  padding: 1px;
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.3), rgba(135, 206, 235, 0.1));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.nav-links a:hover {
  color: #87CEEB;
  background: rgba(135, 206, 235, 0.1);
  border-color: rgba(135, 206, 235, 0.3);
  text-shadow: 0 0 8px rgba(135, 206, 235, 0.5);
  transform: translateY(-1px);
}

.nav-links a:hover::before {
  opacity: 1;
}

.nav-links a.router-link-active {
  color: #87CEEB;
  background: rgba(135, 206, 235, 0.15);
  border-color: rgba(135, 206, 235, 0.4);
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6);
  box-shadow: 0 0 15px rgba(135, 206, 235, 0.2);
}

.nav-links a.router-link-active::before {
  opacity: 1;
}

.logout-btn {
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  border: none;
  padding: 0.625rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  white-space: nowrap;
  box-shadow: 0 4px 15px rgba(135, 206, 235, 0.3);
  position: relative;
  overflow: hidden;
}

.logout-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.logout-btn:hover {
  background: linear-gradient(135deg, #6bb6d6 0%, #87CEEB 100%);
  box-shadow: 0 6px 20px rgba(135, 206, 235, 0.5);
  transform: translateY(-2px);
}

.logout-btn:hover::before {
  width: 300px;
  height: 300px;
}

.logout-btn:active {
  transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 968px) {
  .navbar {
    padding: 0.75rem 1.5rem;
  }

  .nav-container {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .logo-image {
    height: 30px;
  }

  .nav-links {
    flex: 1;
    justify-content: flex-end;
    gap: 0.4rem;
  }

  .nav-links a {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
  }

  .logout-btn {
    font-size: 0.85rem;
    padding: 0.5rem 1.25rem;
  }
}

@media (max-width: 640px) {
  .navbar {
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
  }

  .nav-container {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .logo-section {
    justify-content: center;
  }

  .logo-image {
    height: 26px;
  }

  .nav-links {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.4rem;
    width: 100%;
  }

  .nav-links a {
    font-size: 0.8rem;
    padding: 0.5rem 0.875rem;
    flex: 0 1 auto;
  }

  .logout-btn {
    width: 100%;
    text-align: center;
    padding: 0.625rem 1rem;
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  .navbar {
    padding: 0.625rem 0.75rem;
  }

  .logo-image {
    height: 24px;
  }

  .nav-links {
    gap: 0.3rem;
  }

  .nav-links a {
    font-size: 0.75rem;
    padding: 0.45rem 0.75rem;
  }

  .logout-btn {
    padding: 0.55rem 0.875rem;
    font-size: 0.8rem;
  }
}
</style>

