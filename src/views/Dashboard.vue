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
        >
          <div class="order-header">
            <div>
              <h3>Order #{{ order.id.slice(-6) }}</h3>
              <p class="user-name">User: {{ order.userName }}</p>
            </div>
            <div class="status-control">
              <label>Status:</label>
              <select
                :value="order.status"
                @change="updateStatus(order.id, $event.target.value)"
                class="status-select"
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
          
          <div class="order-details">
            <div class="detail-item">
              <strong>Model Links:</strong>
              <div class="links-list">
                <div
                  v-for="(linkData, index) in getOrderLinksWithCopies(order)"
                  :key="linkData.id || index"
                  class="link-item"
                >
                  <label class="printed-checkbox">
                    <input
                      type="checkbox"
                      :checked="linkData.printed || false"
                      @change="togglePrinted(order.id, linkData.id || index, $event.target.checked)"
                      class="printed-checkbox-input"
                    />
                    <span class="checkbox-label">Printed</span>
                  </label>
                  <a
                    :href="linkData.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="model-link"
                  >
                    {{ linkData.url }}
                  </a>
                  <span class="copies-badge" v-if="linkData.copies > 1">
                    ×{{ linkData.copies }} copies
                  </span>
                </div>
              </div>
            </div>
            
            <div class="detail-item">
              <strong>Colors:</strong>
              <div class="colors-list">
                <span
                  v-for="color in order.colors"
                  :key="color"
                  class="color-badge"
                >
                  {{ color }}
                </span>
              </div>
            </div>
            
            <div v-if="order.comment" class="detail-item">
              <strong>Comment:</strong>
              <p>{{ order.comment }}</p>
            </div>
            
            <div class="detail-item">
              <strong>Created:</strong>
              <span>{{ formatDate(order.createdAt) }}</span>
            </div>
            
            <div v-if="order.updatedAt !== order.createdAt" class="detail-item">
              <strong>Last Updated:</strong>
              <span>{{ formatDate(order.updatedAt) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { storage } from '../utils/storage'

const orders = ref([])
const selectedUser = ref('')
const selectedStatus = ref('')
const isLoading = ref(false)
let refreshInterval = null

const statuses = ['Created', 'Reviewed', 'Printing', 'Printed', 'Delivery', 'Done']

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

onMounted(() => {
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
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  transition: transform 0.3s, box-shadow 0.3s;
  border: 1px solid #3a3a3a;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #3a3a3a;
  gap: 1rem;
  flex-wrap: wrap;
}

.order-header h3 {
  color: #a0d4e8;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
}

.user-name {
  color: #87CEEB;
  font-weight: 500;
  margin: 0;
}

.status-control {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 200px;
}

.status-control label {
  color: #87CEEB;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-select {
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 2px solid #87CEEB;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  background-color: #1a1a1a;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2387CEEB' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px;
  color: #b8dce8;
  transition: background-color 0.3s;
  box-shadow: 0 0 5px rgba(135, 206, 235, 0.2);
  appearance: none;
}

.status-select:hover {
  background-color: #2a2a2a;
}

.order-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item strong {
  color: #87CEEB;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item a {
  color: #87CEEB;
  text-decoration: none;
  word-break: break-all;
}

.detail-item a:hover {
  text-decoration: underline;
}

.links-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.model-link {
  display: block;
  padding: 0.5rem;
  background: rgba(135, 206, 235, 0.1);
  border-radius: 5px;
  border: 1px solid rgba(135, 206, 235, 0.2);
  transition: background 0.3s;
}

.model-link:hover {
  background: rgba(135, 206, 235, 0.2);
}

.link-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.copies-badge {
  background: rgba(135, 206, 235, 0.3);
  color: #87CEEB;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid rgba(135, 206, 235, 0.5);
}

.printed-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
}

.printed-checkbox-input {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #87CEEB;
}

.checkbox-label {
  color: #a0d4e8;
  font-size: 0.9rem;
  font-weight: 500;
}

.detail-item p {
  color: #999;
  margin: 0;
}

.detail-item span {
  color: #b8dce8;
  text-shadow: 0 0 3px rgba(135, 206, 235, 0.2);
}

.colors-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.color-badge {
  background: rgba(135, 206, 235, 0.2);
  color: #87CEEB;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.9rem;
  font-weight: 500;
  border: 1px solid rgba(135, 206, 235, 0.3);
}
</style>

