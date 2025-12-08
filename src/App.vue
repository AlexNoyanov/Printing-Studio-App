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
          <router-link to="/dashboard" v-if="userRole === 'printer'">Dashboard</router-link>
          <router-link to="/filaments" v-if="userRole === 'printer'">Filaments</router-link>
          <button @click="logout" class="logout-btn">{{ currentUsername ? `${currentUsername}, Logout` : 'Logout' }}</button>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import logoImage from './logos/Logo-black.png'

const router = useRouter()

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

// Update document title based on user role
watch([appTitle, isAuthenticated], ([title, authenticated]) => {
  if (authenticated) {
    document.title = title
  } else {
    document.title = '3D Printing Studio'
  }
}, { immediate: true })

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
}

.nav-links a {
  text-decoration: none;
  color: #a0d4e8;
  font-weight: 500;
  transition: all 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
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
}

.logout-btn:hover {
  background: #6bb6d6;
}
</style>

