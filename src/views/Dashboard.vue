<template>
  <div class="container">
    <div class="page-header">
      <h1>Printer Dashboard</h1>
      <div class="stats">
        <div class="stat-item">
          <span class="stat-value">{{ totalOrders }}</span>
          <span class="stat-label">Total Orders</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ pendingOrders }}</span>
          <span class="stat-label">Pending</span>
        </div>
      </div>
    </div>
    
    <div v-if="orders.length === 0" class="empty-state">
      <p>No orders yet.</p>
    </div>
    
    <div v-else class="dashboard-content">
      <div class="filters">
        <select v-model="selectedUser" class="filter-select">
          <option value="">All Users</option>
          <option v-for="user in uniqueUsers" :key="user" :value="user">
            {{ user }}
          </option>
        </select>
        <select v-model="selectedStatus" class="filter-select">
          <option value="">All Statuses</option>
          <option v-for="status in statuses" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
      </div>
      
      <div class="orders-list">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="order-card"
          :class="{ 'order-done': order.status === 'Done' }"
        >
          <div class="order-header" @click="toggleOrderExpand(order.id)" :class="{ 'header-expanded': isExpanded(order.id) }">
            <div class="order-info">
              <div class="order-id-section">
                <div class="order-id-row">
                  <h3 class="order-id">Order #{{ order.id.slice(-6) }}</h3>
                  <svg 
                    class="expand-icon" 
                    :class="{ 'expanded': isExpanded(order.id) }"
                    width="20" 
                    height="20" 
                    viewBox="0 0 24 24" 
                    fill="none" 
                    stroke="currentColor" 
                    stroke-width="2"
                  >
                    <polyline points="6 9 12 15 18 9"></polyline>
                  </svg>
                </div>
                <div class="order-meta">
                  <span class="user-badge">
                    <svg class="user-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    {{ order.userName }}
                  </span>
                  <span class="date-badge">
                    <svg class="date-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="16" y1="2" x2="16" y2="6"></line>
                      <line x1="8" y1="2" x2="8" y2="6"></line>
                      <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    {{ formatDate(order.createdAt) }}
                  </span>
                </div>
              </div>
            </div>
            <div class="status-section" @click.stop>
              <label class="status-label">Status</label>
              <select
                :value="order.status"
                @change="updateStatus(order.id, $event.target.value)"
                class="status-select"
                :class="`status-${order.status.toLowerCase()}`"
              >
                <option
                  v-for="status in statuses"
                  :key="status"
                  :value="status"
                >
                  {{ status }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="order-body" :class="{ 'collapsed': !isExpanded(order.id) }">
            <div class="order-section">
              <div class="section-header">
                <svg class="section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                  <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                  <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span class="section-title">Model Links</span>
              </div>
              <div class="links-list">
                <div
                  v-for="(linkData, index) in getOrderLinksWithCopies(order)"
                  :key="linkData.id || index"
                  class="link-card"
                  :class="{ 'link-printed': linkData.printed }"
                >
                  <div class="link-header">
                    <label class="printed-checkbox">
                      <input
                        type="checkbox"
                        :checked="linkData.printed || false"
                        @change="togglePrinted(order.id, linkData.id || index, $event.target.checked)"
                        class="printed-checkbox-input"
                      />
                      <span class="checkbox-label">Printed</span>
                    </label>
                    <span class="copies-badge" v-if="linkData.copies > 1">
                      ×{{ linkData.copies }} copies
                    </span>
                  </div>
                  <a
                    :href="linkData.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="model-link"
                  >
                    <svg class="link-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                      <polyline points="15 3 21 3 21 9"></polyline>
                      <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span class="link-text">{{ linkData.url }}</span>
                    <svg class="external-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                      <polyline points="15 3 21 3 21 9"></polyline>
                      <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                  </a>
                </div>
              </div>
            </div>
            
            <div class="order-section" v-if="order.colors && order.colors.length > 0">
              <div class="section-header">
                <svg class="section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                  <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                  <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                  <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                  <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                </svg>
                <span class="section-title">Filaments</span>
              </div>
              <div class="colors-list">
                <router-link
                  v-for="filamentInfo in getOrderFilaments(order)"
                  :key="filamentInfo.id || filamentInfo.name"
                  :to="filamentInfo.id ? `/filaments/${filamentInfo.id}` : '#'"
                  class="color-badge filament-badge"
                  :style="{ backgroundColor: filamentInfo.color || filamentInfo.hex || '#ffffff' }"
                  @click.stop
                >
                  <span class="filament-name">{{ filamentInfo.name }}</span>
                  <span class="filament-type" v-if="filamentInfo.materialType">{{ filamentInfo.materialType }}</span>
                  <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M7 17L17 7"></path>
                    <path d="M7 7h10v10"></path>
                  </svg>
                </router-link>
              </div>
            </div>
            
            <div class="order-section" v-if="order.comment">
              <div class="section-header">
                <svg class="section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="section-title">Comment</span>
              </div>
              <p class="comment-text">{{ order.comment }}</p>
            </div>
            
            <div class="order-footer" v-if="order.updatedAt !== order.createdAt">
              <span class="updated-badge">
                <svg class="update-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                  <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
                Last updated: {{ formatDate(order.updatedAt) }}
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
const selectedUser = ref('')
const selectedStatus = ref('')
const isLoading = ref(false)
const expandedOrders = ref(new Set())
const allFilaments = ref([])
let refreshInterval = null

const statuses = ['Created', 'Reviewed', 'Printing', 'Printed', 'Delivery', 'Done']

const isExpanded = (orderId) => {
  return expandedOrders.value.has(orderId)
}

const toggleOrderExpand = (orderId) => {
  if (expandedOrders.value.has(orderId)) {
    expandedOrders.value.delete(orderId)
  } else {
    expandedOrders.value.add(orderId)
  }
}

const loadOrders = async () => {
  // Prevent concurrent requests
  if (isLoading.value) {
    return
  }
  
  isLoading.value = true
  try {
    const allOrders = await storage.getOrders()
    orders.value = allOrders
  } catch (e) {
    console.error('Error loading orders:', e)
  } finally {
    isLoading.value = false
  }
}

const uniqueUsers = computed(() => {
  const users = new Set(orders.value.map(o => o.userName))
  return Array.from(users).sort()
})

const filteredOrders = computed(() => {
  let filtered = [...orders.value]
  
  if (selectedUser.value) {
    filtered = filtered.filter(o => o.userName === selectedUser.value)
  }
  
  if (selectedStatus.value) {
    filtered = filtered.filter(o => o.status === selectedStatus.value)
  }
  
  return filtered.sort((a, b) => {
    return new Date(b.createdAt) - new Date(a.createdAt)
  })
})

const totalOrders = computed(() => orders.value.length)

const pendingOrders = computed(() => {
  return orders.value.filter(o => 
    ['Created', 'Reviewed', 'Printing', 'Printed', 'Delivery'].includes(o.status)
  ).length
})

const updateStatus = async (orderId, newStatus) => {
  try {
    await storage.updateOrder(orderId, {
      status: newStatus,
      updatedAt: new Date().toISOString()
    })
    await loadOrders()
  } catch (e) {
    console.error('Error updating order status:', e)
    alert('Failed to update order status. Please try again.')
  }
}

const togglePrinted = async (orderId, linkId, printed) => {
  try {
    await storage.updateLinkPrintedStatus(linkId, printed)
    // Reload orders to get updated status
    await loadOrders()
  } catch (e) {
    console.error('Error updating printed status:', e)
    alert('Failed to update printed status. Please try again.')
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrderLinks = (order) => {
  // Support both modelLinks (array) and modelLink (single) for backward compatibility
  if (order.modelLinks && Array.isArray(order.modelLinks) && order.modelLinks.length > 0) {
    return order.modelLinks.filter(link => link && link.trim() !== '')
  }
  if (order.modelLink && order.modelLink.trim() !== '') {
    return [order.modelLink]
  }
  return []
}

const getOrderLinksWithCopies = (order) => {
  // Support new format with copies
  if (order.modelLinksWithCopies && Array.isArray(order.modelLinksWithCopies) && order.modelLinksWithCopies.length > 0) {
    return order.modelLinksWithCopies.filter(link => link && link.url && link.url.trim() !== '')
  }
  // Fallback to old format
  const links = getOrderLinks(order)
  return links.map(url => ({ url, copies: 1 }))
}

const loadFilaments = async () => {
  try {
    // Try to load materials first (new unified system)
    const materials = await storage.getMaterials()
    if (materials && materials.length > 0) {
      allFilaments.value = materials.map(material => ({
        id: material.id,
        name: material.name,
        color: material.color,
        hex: material.color,
        materialType: material.materialType,
        shopLink: material.shopLink
      }))
    } else {
      // Fallback to colors for backward compatibility
      const colors = await storage.getColors()
      allFilaments.value = colors.map(color => ({
        id: color.id,
        name: color.name,
        color: color.value || color.hex,
        hex: color.value || color.hex,
        materialType: null
      }))
    }
  } catch (e) {
    console.error('Error loading filaments:', e)
    allFilaments.value = []
  }
}

const getOrderFilaments = (order) => {
  if (!order.colors || order.colors.length === 0) {
    return []
  }

  // Try to match order.colors with filaments
  // order.colors might be color names, IDs, or objects
  return order.colors.map(colorItem => {
    // Handle if colorItem is a string (color name)
    if (typeof colorItem === 'string') {
      // Try to find by name
      const filament = allFilaments.value.find(f => 
        f.name.toLowerCase() === colorItem.toLowerCase() ||
        f.id === colorItem
      )
      
      if (filament) {
        return filament
      }
      
      // Fallback: return basic info if not found
      return {
        id: null,
        name: colorItem,
        color: colorItem,
        hex: colorItem,
        materialType: null
      }
    }
    
    // Handle if colorItem is an object (should have id or name)
    if (typeof colorItem === 'object') {
      const filament = allFilaments.value.find(f => 
        f.id === colorItem.id || 
        f.name === colorItem.name ||
        (colorItem.id && f.id === colorItem.id)
      )
      
      if (filament) {
        return filament
      }
      
      return {
        id: colorItem.id || null,
        name: colorItem.name || colorItem,
        color: colorItem.color || colorItem.hex || colorItem.value || colorItem,
        hex: colorItem.color || colorItem.hex || colorItem.value || colorItem,
        materialType: colorItem.materialType || null
      }
    }
    
    return {
      id: null,
      name: String(colorItem),
      color: String(colorItem),
      hex: String(colorItem),
      materialType: null
    }
  })
}

onMounted(() => {
  loadFilaments()
  loadOrders()
  // Refresh orders every 30 seconds (reduced frequency for better performance)
  // Only refresh if page is visible (not in background tab)
  refreshInterval = setInterval(() => {
    if (!document.hidden) {
      loadOrders()
    }
  }, 30000) // 30 seconds instead of 3 seconds
})

onUnmounted(() => {
  // Clean up interval when component is destroyed
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
})
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
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

.stats {
  display: flex;
  gap: 2rem;
}

.stat-item {
  background: #2a2a2a;
  padding: 1rem 1.5rem;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #87CEEB;
}

.stat-label {
  display: block;
  font-size: 0.9rem;
  color: #999;
  margin-top: 0.25rem;
}

.empty-state {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.empty-state p {
  color: #999;
  font-size: 1.2rem;
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filters {
  display: flex;
  gap: 1rem;
  background: #2a2a2a;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.filter-select {
  flex: 1;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: border-color 0.3s;
  background-color: #1a1a1a;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2387CEEB' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px;
  color: #b8dce8;
  appearance: none;
}

.filter-select:focus {
  outline: none;
  border-color: #87CEEB;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-card {
  background: linear-gradient(135deg, rgba(42, 42, 42, 0.95) 0%, rgba(30, 30, 30, 0.98) 100%);
  border-radius: 16px;
  padding: 0;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(135, 206, 235, 0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(135, 206, 235, 0.15);
  overflow: hidden;
}

.order-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 48px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(135, 206, 235, 0.3);
  border-color: rgba(135, 206, 235, 0.3);
}

.order-card.order-done {
  opacity: 0.65;
  background: linear-gradient(135deg, rgba(30, 30, 30, 0.95) 0%, rgba(20, 20, 20, 0.98) 100%);
  border-color: rgba(107, 114, 128, 0.3);
  position: relative;
}

.order-card.order-done::before {
  content: '✓ Completed';
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem 1rem;
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 20px;
  color: #34d399;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  z-index: 10;
  pointer-events: none;
}

.order-card.order-done:hover {
  opacity: 0.8;
  transform: translateY(-2px);
}

.order-card.order-done .order-header {
  background: linear-gradient(135deg, rgba(20, 20, 20, 0.8) 0%, rgba(15, 15, 15, 0.9) 100%);
  border-bottom-color: rgba(107, 114, 128, 0.2);
}

.order-card.order-done .order-id {
  color: #6b7280;
  text-shadow: none;
  background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.order-card.order-done .user-badge,
.order-card.order-done .date-badge {
  background: rgba(107, 114, 128, 0.1);
  border-color: rgba(107, 114, 128, 0.2);
  color: #9ca3af;
}

.order-card.order-done .user-icon,
.order-card.order-done .date-icon {
  color: #6b7280;
}

.order-card.order-done .section-title,
.order-card.order-done .section-icon {
  color: #6b7280;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(26, 26, 26, 0.8) 0%, rgba(20, 20, 20, 0.9) 100%);
  border-bottom: 1px solid rgba(135, 206, 235, 0.2);
  gap: 1.5rem;
  flex-wrap: wrap;
  cursor: pointer;
  transition: background 0.3s ease;
  user-select: none;
}

.order-header:hover {
  background: linear-gradient(135deg, rgba(32, 32, 32, 0.9) 0%, rgba(25, 25, 25, 0.95) 100%);
}

.order-header.header-expanded {
  border-bottom-color: rgba(135, 206, 235, 0.3);
}

.order-info {
  flex: 1;
  min-width: 0;
}

.order-id-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.order-id-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.order-id {
  color: #87CEEB;
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.4);
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.5px;
}

.expand-icon {
  width: 20px;
  height: 20px;
  color: #87CEEB;
  transition: transform 0.3s ease;
  flex-shrink: 0;
}

.expand-icon.expanded {
  transform: rotate(180deg);
}

.order-meta {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.user-badge,
.date-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.875rem;
  background: rgba(135, 206, 235, 0.1);
  border: 1px solid rgba(135, 206, 235, 0.2);
  border-radius: 8px;
  color: #a0d4e8;
  font-size: 0.875rem;
  font-weight: 500;
}

.user-icon,
.date-icon {
  width: 14px;
  height: 14px;
  color: #87CEEB;
  flex-shrink: 0;
}

.status-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 180px;
  cursor: default;
}

.status-label {
  color: #87CEEB;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.status-select {
  padding: 0.875rem 2.75rem 0.875rem 1rem;
  border: 2px solid #87CEEB;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(20, 20, 20, 0.98) 100%);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 12 12'%3E%3Cpath fill='%2387CEEB' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.875rem center;
  background-size: 14px;
  color: #b8dce8;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(135, 206, 235, 0.2);
  appearance: none;
}

.status-select:hover {
  background: linear-gradient(135deg, rgba(42, 42, 42, 0.95) 0%, rgba(30, 30, 30, 0.98) 100%);
  box-shadow: 0 6px 16px rgba(135, 206, 235, 0.3);
  transform: translateY(-1px);
}

.status-select:focus {
  outline: none;
  border-color: #87CEEB;
  box-shadow: 0 0 0 3px rgba(135, 206, 235, 0.2);
}

.status-select.status-created {
  border-color: #94a3b8;
  color: #cbd5e1;
}

.status-select.status-reviewed {
  border-color: #60a5fa;
  color: #93c5fd;
}

.status-select.status-printing {
  border-color: #34d399;
  color: #6ee7b7;
  box-shadow: 0 4px 12px rgba(52, 211, 153, 0.3);
}

.status-select.status-printed {
  border-color: #34d399;
  color: #6ee7b7;
}

.status-select.status-delivery {
  border-color: #a78bfa;
  color: #c4b5fd;
}

.status-select.status-done {
  border-color: #10b981;
  color: #34d399;
}

.order-body {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  max-height: 2000px;
  overflow: hidden;
  transition: max-height 0.4s ease, padding 0.4s ease, opacity 0.3s ease;
  opacity: 1;
}

.order-body.collapsed {
  max-height: 0;
  padding: 0 2rem;
  opacity: 0;
  overflow: hidden;
}

.order-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.25rem;
}

.section-icon {
  width: 20px;
  height: 20px;
  color: #87CEEB;
  flex-shrink: 0;
}

.section-title {
  color: #87CEEB;
  font-size: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.links-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.link-card {
  background: rgba(26, 26, 26, 0.6);
  border: 1px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  padding: 1rem;
  transition: all 0.3s ease;
}

.link-card:hover {
  background: rgba(26, 26, 26, 0.8);
  border-color: rgba(135, 206, 235, 0.4);
  transform: translateX(4px);
}

.link-card.link-printed {
  background: rgba(52, 211, 153, 0.1);
  border-color: rgba(52, 211, 153, 0.3);
}

.link-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  gap: 1rem;
}

.model-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(135, 206, 235, 0.08);
  border: 1px solid rgba(135, 206, 235, 0.15);
  border-radius: 8px;
  color: #87CEEB;
  text-decoration: none;
  word-break: break-all;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.model-link:hover {
  background: rgba(135, 206, 235, 0.15);
  border-color: rgba(135, 206, 235, 0.3);
  transform: translateX(2px);
  color: #a0d4e8;
}

.link-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  opacity: 0.7;
}

.link-text {
  flex: 1;
  min-width: 0;
}

.external-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
  opacity: 0.6;
}

.copies-badge {
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.3) 0%, rgba(135, 206, 235, 0.2) 100%);
  color: #87CEEB;
  padding: 0.35rem 0.875rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid rgba(135, 206, 235, 0.4);
  white-space: nowrap;
}

.printed-checkbox {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  cursor: pointer;
  user-select: none;
}

.printed-checkbox-input {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #34d399;
  border-radius: 4px;
}

.checkbox-label {
  color: #a0d4e8;
  font-size: 0.875rem;
  font-weight: 500;
}

.comment-text {
  color: #b8dce8;
  margin: 0;
  padding: 1rem;
  background: rgba(26, 26, 26, 0.5);
  border-radius: 8px;
  border-left: 3px solid rgba(135, 206, 235, 0.4);
  line-height: 1.6;
  font-size: 0.95rem;
}

.colors-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.color-badge {
  color: #fff;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  border: 2px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.color-badge:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.filament-badge {
  display: inline-flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.25rem;
  padding: 0.75rem 1rem;
  text-decoration: none;
  cursor: pointer;
  position: relative;
  min-width: 120px;
}

.filament-badge:hover {
  transform: scale(1.08) translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
  border-color: rgba(255, 255, 255, 0.4);
}

.filament-name {
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1.2;
}

.filament-type {
  font-size: 0.7rem;
  font-weight: 500;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.15rem 0.5rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.link-arrow {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 14px;
  height: 14px;
  opacity: 0.8;
  transition: transform 0.2s ease;
}

.filament-badge:hover .link-arrow {
  transform: translate(2px, -2px);
  opacity: 1;
}

.order-footer {
  padding-top: 1rem;
  border-top: 1px solid rgba(135, 206, 235, 0.1);
  margin-top: 0.5rem;
}

.updated-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.8rem;
  font-weight: 500;
}

.update-icon {
  width: 14px;
  height: 14px;
  color: #94a3b8;
}

/* Responsive Design */
@media (max-width: 968px) {
  .container {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .stats {
    width: 100%;
    justify-content: space-around;
  }

  .filters {
    flex-direction: column;
  }

  .order-header {
    flex-direction: column;
    gap: 1rem;
  }

  .status-section {
    min-width: 100%;
  }

  .order-body {
    padding: 1.5rem;
  }
}

@media (max-width: 640px) {
  .container {
    padding: 0.75rem;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }

  .stats {
    flex-direction: column;
    gap: 1rem;
  }

  .stat-item {
    width: 100%;
  }

  .order-card {
    border-radius: 12px;
  }

  .order-header {
    padding: 1rem 1.25rem;
  }

  .order-body {
    padding: 1.25rem;
    gap: 1.25rem;
  }

  .order-id {
    font-size: 1.4rem;
  }

  .order-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>

