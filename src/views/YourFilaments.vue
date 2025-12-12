<template>
  <div class="container">
    <div class="page-header">
      <h1>Your Filaments</h1>
      <p class="subtitle">Manage your filament spools</p>
    </div>

    <div v-if="loading" class="loading">
      <p>Loading your filaments...</p>
    </div>

    <div v-else-if="userFilaments.length === 0" class="empty-filaments">
      <p>You don't have any filament spools yet.</p>
      <p class="hint">Contact your printer owner to assign filaments to you.</p>
    </div>

    <div v-else class="filaments-grid">
      <div
        v-for="filament in userFilaments"
        :key="filament.id"
        class="filament-card"
      >
        <div
          class="filament-swatch"
          :style="{ backgroundColor: filament.color || '#000000' }"
        ></div>
        <div class="filament-info">
          <h3>{{ filament.name }}</h3>
          <p class="filament-type">{{ filament.materialType }}</p>
          <p class="filament-color">{{ filament.color ? filament.color.toUpperCase() : 'N/A' }}</p>
          <div class="quantity-badge">
            <span class="quantity-label">Spools:</span>
            <span class="quantity-value">{{ filament.quantity }}</span>
          </div>
          <a
            v-if="filament.shopLink"
            :href="filament.shopLink"
            target="_blank"
            rel="noopener noreferrer"
            class="shop-link"
          >
            Shop Link →
          </a>
        </div>
        <router-link
          :to="`/filaments/${filament.materialId}`"
          class="view-details-btn"
        >
          View Details
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { storage } from '../utils/storage'

const userFilaments = ref([])
const loading = ref(true)

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const loadUserFilaments = async () => {
  loading.value = true
  const user = getCurrentUser()
  
  if (!user) {
    loading.value = false
    return
  }

  try {
    userFilaments.value = await storage.getUserFilaments(user.id)
  } catch (e) {
    console.error('Error loading user filaments:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadUserFilaments()
})
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  color: #87CEEB;
  font-size: 2.5rem;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5), 0 0 20px rgba(135, 206, 235, 0.3), 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 50%, #4da6c2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #999;
  font-size: 1.1rem;
}

.loading,
.empty-filaments {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
}

.empty-filaments p {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.hint {
  color: #87CEEB;
  font-style: italic;
  font-size: 0.95rem;
}

.filaments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.filament-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 1.5rem;
  border: 2px solid #3a3a3a;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filament-card:hover {
  border-color: #87CEEB;
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(135, 206, 235, 0.2);
}

.filament-swatch {
  width: 100%;
  height: 120px;
  border-radius: 10px;
  border: 2px solid #3a3a3a;
  margin-bottom: 0.5rem;
}

.filament-info {
  flex: 1;
}

.filament-info h3 {
  color: #a0d4e8;
  margin-bottom: 0.5rem;
  font-size: 1.3rem;
}

.filament-type {
  color: #87CEEB;
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 1rem;
}

.filament-color {
  color: #999;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.quantity-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(135, 206, 235, 0.1);
  border: 1px solid rgba(135, 206, 235, 0.3);
  border-radius: 5px;
  margin-bottom: 1rem;
  width: fit-content;
}

.quantity-label {
  color: #87CEEB;
  font-size: 0.9rem;
}

.quantity-value {
  color: #a0d4e8;
  font-weight: 700;
  font-size: 1.2rem;
}

.shop-link {
  color: #87CEEB;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s;
  display: inline-block;
  margin-bottom: 1rem;
}

.shop-link:hover {
  color: #6bb6d6;
  text-decoration: underline;
}

.view-details-btn {
  display: block;
  text-align: center;
  padding: 0.75rem;
  background: #87CEEB;
  color: #000;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 600;
  transition: background 0.3s;
  margin-top: auto;
}

.view-details-btn:hover {
  background: #6bb6d6;
}

@media (max-width: 768px) {
  .filaments-grid {
    grid-template-columns: 1fr;
  }
}
</style>

