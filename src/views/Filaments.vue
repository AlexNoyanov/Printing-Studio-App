<template>
  <div class="container">
    <div class="page-header">
      <h1>Filament Details</h1>
      <router-link to="/colors" class="back-link">← Back to Colors</router-link>
    </div>

    <div v-if="loading" class="loading">
      <p>Loading filament...</p>
    </div>

    <div v-else-if="!filament" class="not-found">
      <p>Filament not found</p>
      <router-link to="/colors" class="back-btn">Back to Colors</router-link>
    </div>

    <div v-else class="filament-details">
      <!-- Filament Spool with Unfolded Line -->
      <div class="spool-container">
        <div class="spool-wrapper">
          <div class="spool">
            <div class="spool-body">
              <div class="spool-logo">
                <div class="logo-outer-rim"></div>
                <div class="logo-hub"></div>
                <div class="logo-spoke" v-for="i in 5" :key="i" :style="{ transform: `rotate(${i * 72}deg)` }"></div>
              </div>
              <div class="spool-center"></div>
              <div class="spool-windings">
                <div
                  v-for="i in 8"
                  :key="i"
                  class="winding"
                  :style="{ 
                    backgroundColor: filament.value,
                    borderColor: adjustBrightness(filament.value, -20)
                  }"
                ></div>
              </div>
            </div>
          </div>
          <!-- Filament line extending from top of spool to right edge -->
          <div class="filament-line-wrapper">
            <div class="filament-start" :style="{ backgroundColor: filament.value }">
              <div class="filament-start-glow" :style="{ boxShadow: `0 0 10px ${filament.value}` }"></div>
            </div>
            <div class="filament-line-top" :style="{ backgroundColor: filament.value }">
              <div 
                class="line-glow-top" 
                :style="{ 
                  boxShadow: `0 0 20px ${filament.value}`,
                  '--filament-color': filament.value
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filament Info -->
      <div class="filament-info-card">
        <h2>{{ filament.name }}</h2>
        <div class="info-grid">
          <div class="info-item">
            <strong>Color:</strong>
            <div class="color-display">
              <div
                class="color-box"
                :style="{ backgroundColor: filament.value }"
              ></div>
              <span>{{ filament.value.toUpperCase() }}</span>
            </div>
          </div>
          <div v-if="filament.filamentLink" class="info-item shop-link-item">
            <strong>Shop Link:</strong>
            <a
              :href="filament.filamentLink"
              target="_blank"
              rel="noopener noreferrer"
              class="shop-link-btn"
            >
              <span class="shop-icon">🛒</span>
              <span>Visit Shop</span>
              <span class="external-icon">↗</span>
            </a>
            <a
              :href="filament.filamentLink"
              target="_blank"
              rel="noopener noreferrer"
              class="shop-link-text"
            >
              {{ filament.filamentLink }}
            </a>
          </div>
          <div v-else class="info-item">
            <strong>Shop Link:</strong>
            <span class="no-link">No shop link provided</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { storage } from '../utils/storage'

const route = useRoute()
const filament = ref(null)
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

const adjustBrightness = (hex, percent) => {
  const num = parseInt(hex.replace('#', ''), 16)
  const r = Math.min(255, Math.max(0, (num >> 16) + percent))
  const g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + percent))
  const b = Math.min(255, Math.max(0, (num & 0x0000FF) + percent))
  return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')
}

const loadFilament = async () => {
  loading.value = true
  const colorId = route.params.id
  const user = getCurrentUser()

  if (!user || !colorId) {
    loading.value = false
    return
  }

  try {
    const allColors = await storage.getColors(user.id)
    const foundColor = allColors.find(c => c.id === colorId)

    if (foundColor) {
      filament.value = foundColor
    }
  } catch (e) {
    console.error('Error loading filament:', e)
  }

  loading.value = false
}

onMounted(() => {
  loadFilament()
})
</script>

<style scoped>
.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.page-header h1 {
  color: #87CEEB;
  font-size: 2.5rem;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5), 0 0 20px rgba(135, 206, 235, 0.3), 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 50%, #4da6c2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.back-link {
  color: #a0d4e8;
  text-decoration: none;
  font-size: 1rem;
  transition: all 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
}

.back-link:hover {
  color: #87CEEB;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6);
}

.loading,
.not-found {
  text-align: center;
  padding: 3rem;
  color: #999;
  font-size: 1.2rem;
}

.back-btn {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: #87CEEB;
  color: #000;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 600;
  transition: background 0.3s;
}

.back-btn:hover {
  background: #6bb6d6;
}

.filament-details {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Spool Design */
.spool-container {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #2a2a2a;
  border-radius: 10px;
  border: 1px solid #3a3a3a;
}

.spool-wrapper {
  position: relative;
  width: 100%;
  display: flex;
  align-items: flex-start;
}

.spool {
  position: relative;
  width: 200px;
  height: 200px;
  flex-shrink: 0;
  z-index: 2;
}

.filament-line-wrapper {
  position: absolute;
  top: 30px;
  left: 100px;
  right: 2rem;
  height: 10px;
  display: flex;
  align-items: center;
  z-index: 3;
}

.filament-start {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 0 10px currentColor;
  position: relative;
}

.filament-start-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 50%;
  opacity: 0.8;
}

.spool-body {
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #3a3a3a 0%, #2a2a2a 100%);
  border-radius: 50%;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
}

.spool-logo {
  position: absolute;
  width: 140px;
  height: 140px;
  border-radius: 50%;
  z-index: 5;
  animation: logoRotate 2s linear forwards;
}

.logo-outer-rim {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 8px solid #000;
  box-sizing: border-box;
  z-index: 10;
}

.logo-hub {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 20px;
  height: 20px;
  background: #fff;
  border-radius: 50%;
  z-index: 11;
}

.logo-spoke {
  position: absolute;
  top: 50%;
  left: 50%;
  transform-origin: left center;
  width: 50px;
  height: 10px;
  background: #000;
  border-radius: 5px;
  margin-top: -5px;
  z-index: 11;
}

@keyframes logoRotate {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.spool-center {
  width: 60px;
  height: 60px;
  background: #1a1a1a;
  border-radius: 50%;
  border: 3px solid #4a4a4a;
  z-index: 2;
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.8);
}

.spool-windings {
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.winding {
  position: absolute;
  border-radius: 50%;
  border: 3px solid;
  opacity: 0.8;
}

.winding:nth-child(1) {
  width: 70px;
  height: 70px;
  top: 65px;
  left: 65px;
}

.winding:nth-child(2) {
  width: 80px;
  height: 80px;
  top: 60px;
  left: 60px;
}

.winding:nth-child(3) {
  width: 90px;
  height: 90px;
  top: 55px;
  left: 55px;
}

.winding:nth-child(4) {
  width: 100px;
  height: 100px;
  top: 50px;
  left: 50px;
}

.winding:nth-child(5) {
  width: 110px;
  height: 110px;
  top: 45px;
  left: 45px;
}

.winding:nth-child(6) {
  width: 120px;
  height: 120px;
  top: 40px;
  left: 40px;
}

.winding:nth-child(7) {
  width: 130px;
  height: 130px;
  top: 35px;
  left: 35px;
}

.winding:nth-child(8) {
  width: 140px;
  height: 140px;
  top: 30px;
  left: 30px;
}

/* Filament Info Card */
.filament-info-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.filament-info-card h2 {
  color: #87CEEB;
  font-size: 2rem;
  margin-bottom: 1.5rem;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5);
}

.info-grid {
  display: grid;
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item strong {
  color: #87CEEB;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.color-display {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.color-box {
  width: 50px;
  height: 50px;
  border-radius: 5px;
  border: 2px solid #3a3a3a;
}

.shop-link-item {
  margin-top: 1rem;
}

.shop-link-btn {
  width: 180px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #87CEEB;
  color: #000;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 600;
  transition: all 0.3s;
  margin-bottom: 0.5rem;
  box-shadow: 0 0 10px rgba(135, 206, 235, 0.3);
}

.shop-link-btn:hover {
  background: #6bb6d6;
  box-shadow: 0 0 20px rgba(135, 206, 235, 0.6);
  transform: translateY(-2px);
}

.shop-icon {
  font-size: 1.2rem;
}

.external-icon {
  font-size: 0.9rem;
  opacity: 0.8;
}

.shop-link-text {
  display: block;
  color: #87CEEB;
  text-decoration: none;
  word-break: break-all;
  font-size: 0.9rem;
  transition: all 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
  margin-top: 0.5rem;
}

.shop-link-text:hover {
  color: #6bb6d6;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6);
  text-decoration: underline;
}

.no-link {
  color: #999;
  font-style: italic;
}

/* Filament Line Next to Spool */
.filament-line-top {
  flex: 1;
  min-width: 0;
  height: 10px;
  border-radius: 5px;
  position: relative;
  margin-left: 6px;
  left: -16px;
  width: 0;
  opacity: 0;
  animation: lineGrow 2s ease-out forwards;
  overflow: hidden;
  box-shadow: 0 0 15px currentColor;
  transform-origin: left center;
}

.line-glow-top {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 5px;
  opacity: 0.6;
  background: var(--filament-color);
  box-shadow: 0 0 20px var(--filament-color);
}

@keyframes lineGrow {
  0% {
    width: 0;
    opacity: 0;
    box-shadow: 0 0 0px currentColor;
    transform: scaleX(0);
  }
  5% {
    opacity: 1;
  }
  100% {
    width: 100%;
    opacity: 1;
    box-shadow: 0 0 15px currentColor;
    transform: scaleX(1);
  }
}

@keyframes filamentFlow {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}
</style>

