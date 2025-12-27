<template>
  <div class="client-orders">
    <div class="container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Мои заказы</h1>
          <p class="subtitle">Отслеживайте статус ваших заказов на 3D-печать</p>
        </div>
        <router-link to="/create-order" class="create-order-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"></path>
          </svg>
          Создать заказ
        </router-link>
      </div>

      <!-- Empty State -->
      <div v-if="orders.length === 0 && !isLoading" class="empty-state">
        <div class="empty-icon">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
          </svg>
        </div>
        <h2>У вас пока нет заказов</h2>
        <p>Создайте свой первый заказ на 3D-печать прямо сейчас</p>
        <router-link to="/create-order" class="cta-button">
          Создать первый заказ
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Загрузка заказов...</p>
      </div>

      <!-- Orders List -->
      <div v-else-if="orders.length > 0" class="orders-grid">
        <div
          v-for="order in sortedOrders"
          :key="order.id"
          class="order-card"
          :class="`status-${order.status.toLowerCase()}`"
        >
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-id-section">
              <h3 class="order-number">Заказ #{{ order.id.slice(-6) }}</h3>
              <span class="order-date">{{ formatDate(order.createdAt) }}</span>
            </div>
            <span class="status-badge" :class="getStatusClass(order.status)">
              {{ getStatusText(order.status) }}
            </span>
          </div>

          <!-- Order Content -->
          <div class="order-content">
            <!-- Model Links -->
            <div class="order-section">
              <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                Модели
              </div>
              <div class="models-list">
                <div
                  v-for="(linkData, index) in getOrderLinksWithCopies(order)"
                  :key="linkData.id || index"
                  class="model-item"
                  :class="{ 'model-printed': linkData.printed }"
                >
                  <div class="model-info">
                    <a
                      :href="linkData.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="model-link"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                      </svg>
                      <span class="link-text">{{ getShortUrl(linkData.url) }}</span>
                    </a>
                    <div class="model-meta">
                      <span v-if="linkData.copies > 1" class="copies-info">
                        ×{{ linkData.copies }} {{ getCopiesText(linkData.copies) }}
                      </span>
                      <span v-if="linkData.printed" class="printed-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Напечатано
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Colors -->
            <div v-if="order.colors && order.colors.length > 0" class="order-section">
              <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                  <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                  <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                  <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                  <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                </svg>
                Цвета
              </div>
              <div class="colors-list">
                <span
                  v-for="color in order.colors"
                  :key="color"
                  class="color-tag"
                >
                  {{ color }}
                </span>
              </div>
            </div>

            <!-- Comment -->
            <div v-if="order.comment" class="order-section">
              <div class="section-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Комментарий
              </div>
              <p class="comment-text">{{ order.comment }}</p>
            </div>

            <!-- Updated Info -->
            <div v-if="order.updatedAt !== order.createdAt" class="order-footer">
              <span class="updated-info">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                  <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                Обновлено: {{ formatDate(order.updatedAt) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'

const router = useRouter()
const orders = ref([])
const isLoading = ref(true)
let refreshInterval = null

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const loadOrders = async () => {
  const user = getCurrentUser()
  if (!user) {
      router.push('/login')
    return
  }
  
  isLoading.value = true
  try {
    const allOrders = await storage.getOrders(user.id)
    orders.value = allOrders
  } catch (e) {
    console.error('Error loading orders:', e)
  } finally {
    isLoading.value = false
  }
}

const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => {
    // Show active orders first
    const aIsDone = a.status === 'Done'
    const bIsDone = b.status === 'Done'
    
    if (aIsDone && !bIsDone) return 1
    if (!aIsDone && bIsDone) return -1
    
    // Then sort by date (newest first)
    return new Date(b.createdAt) - new Date(a.createdAt)
  })
})

const getStatusClass = (status) => {
  const statusMap = {
    'Created': 'status-created',
    'Reviewed': 'status-reviewed',
    'Printing': 'status-printing',
    'Printed': 'status-printed',
    'Delivery': 'status-delivery',
    'Done': 'status-done'
  }
  return statusMap[status] || 'status-default'
}

const getStatusText = (status) => {
  const statusMap = {
    'Created': 'Создан',
    'Reviewed': 'На проверке',
    'Printing': 'Печатается',
    'Printed': 'Напечатан',
    'Delivery': 'Доставка',
    'Done': 'Готов'
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrderLinksWithCopies = (order) => {
  if (order.modelLinksWithCopies && Array.isArray(order.modelLinksWithCopies) && order.modelLinksWithCopies.length > 0) {
    return order.modelLinksWithCopies.filter(link => link && link.url && link.url.trim() !== '')
  }
  const links = []
  if (order.modelLinks && Array.isArray(order.modelLinks)) {
    links.push(...order.modelLinks.filter(link => link && link.trim() !== ''))
  } else if (order.modelLink && order.modelLink.trim() !== '') {
    links.push(order.modelLink)
  }
  return links.map(url => ({ url, copies: 1 }))
}

const getShortUrl = (url) => {
  try {
    const urlObj = new URL(url)
    return urlObj.hostname + urlObj.pathname.substring(0, 30) + (urlObj.pathname.length > 30 ? '...' : '')
  } catch {
    return url.length > 50 ? url.substring(0, 50) + '...' : url
  }
}

const getCopiesText = (copies) => {
  if (copies === 1) return 'копия'
  if (copies >= 2 && copies <= 4) return 'копии'
  return 'копий'
}

onMounted(() => {
  loadOrders()
  // Refresh orders every 10 seconds
  refreshInterval = setInterval(loadOrders, 10000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
.client-orders {
  min-height: 100vh;
  background: linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 3rem;
  gap: 2rem;
  flex-wrap: wrap;
}

.header-content {
  flex: 1;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.subtitle {
  font-size: 1.1rem;
  color: #a0d4e8;
  margin: 0;
  opacity: 0.9;
}

.create-order-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
  white-space: nowrap;
}

.create-order-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(135, 206, 235, 0.4);
}

.empty-state {
  background: rgba(26, 26, 42, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 4rem 2rem;
  text-align: center;
  border: 1px solid rgba(135, 206, 235, 0.2);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.empty-icon {
  color: #87CEEB;
  margin-bottom: 2rem;
  opacity: 0.5;
}

.empty-state h2 {
  font-size: 1.75rem;
  color: #fff;
  margin: 0 0 1rem;
}

.empty-state p {
  font-size: 1.1rem;
  color: #a0d4e8;
  margin: 0 0 2rem;
}

.cta-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
}

.cta-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(135, 206, 235, 0.4);
}

.loading-state {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(135, 206, 235, 0.2);
  border-top-color: #87CEEB;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #a0d4e8;
  font-size: 1.1rem;
}

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
}

.order-card {
  background: rgba(26, 26, 42, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(135, 206, 235, 0.2);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
}

.order-card:hover {
  transform: translateY(-4px);
  border-color: rgba(135, 206, 235, 0.4);
  box-shadow: 0 15px 50px rgba(135, 206, 235, 0.2);
}

.order-card.status-done {
  opacity: 0.7;
  border-color: rgba(81, 207, 102, 0.3);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(135, 206, 235, 0.2);
}

.order-id-section {
  flex: 1;
}

.order-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #87CEEB;
  margin: 0 0 0.5rem;
}

.order-date {
  font-size: 0.9rem;
  color: #a0d4e8;
  opacity: 0.8;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  white-space: nowrap;
}

.status-created {
  background: rgba(255, 193, 7, 0.15);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-reviewed {
  background: rgba(135, 206, 235, 0.15);
  color: #87CEEB;
  border: 1px solid rgba(135, 206, 235, 0.3);
}

.status-printing {
  background: rgba(100, 181, 246, 0.15);
  color: #64b5f6;
  border: 1px solid rgba(100, 181, 246, 0.3);
}

.status-printed {
  background: rgba(81, 207, 102, 0.15);
  color: #51cf66;
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.status-delivery {
  background: rgba(255, 193, 7, 0.15);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-done {
  background: rgba(81, 207, 102, 0.15);
  color: #51cf66;
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.order-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.section-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #87CEEB;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.models-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.model-item {
  background: rgba(10, 10, 10, 0.4);
  border-radius: 8px;
  padding: 0.75rem;
  border: 1px solid rgba(135, 206, 235, 0.1);
  transition: all 0.3s ease;
}

.model-item:hover {
  background: rgba(10, 10, 10, 0.6);
  border-color: rgba(135, 206, 235, 0.3);
}

.model-item.model-printed {
  opacity: 0.6;
  background: rgba(81, 207, 102, 0.1);
  border-color: rgba(81, 207, 102, 0.2);
}

.model-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.model-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #87CEEB;
  text-decoration: none;
  font-size: 0.95rem;
  transition: color 0.3s;
}

.model-link:hover {
  color: #a0d4e8;
}

.link-text {
  word-break: break-all;
}

.model-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.copies-info {
  font-size: 0.85rem;
  color: #a0d4e8;
  opacity: 0.8;
}

.printed-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.85rem;
  color: #51cf66;
  font-weight: 500;
}

.colors-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.color-tag {
  background: rgba(135, 206, 235, 0.15);
  color: #87CEEB;
  padding: 0.4rem 0.875rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
  border: 1px solid rgba(135, 206, 235, 0.3);
}

.comment-text {
  color: #b8dce8;
  line-height: 1.6;
  margin: 0;
  padding: 1rem;
  background: rgba(10, 10, 10, 0.4);
  border-radius: 8px;
  border-left: 3px solid rgba(135, 206, 235, 0.4);
}

.order-footer {
  padding-top: 1rem;
  border-top: 1px solid rgba(135, 206, 235, 0.1);
  margin-top: 0.5rem;
}

.updated-info {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #94a3b8;
}

@media (max-width: 968px) {
  .orders-grid {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
  }

  .create-order-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 640px) {
  .container {
    padding: 0 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .order-card {
    padding: 1.5rem;
  }

  .order-header {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>

