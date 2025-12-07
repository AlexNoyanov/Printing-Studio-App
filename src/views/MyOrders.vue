<template>
  <div class="container">
    <div class="page-header">
      <h1>My Orders</h1>
      <router-link to="/create-order" class="create-btn">+ Create New Order</router-link>
    </div>
    
    <div v-if="orders.length === 0" class="empty-state">
      <p>You haven't created any orders yet.</p>
      <router-link to="/create-order" class="create-btn">Create Your First Order</router-link>
    </div>
    
    <div v-else class="orders-list">
      <div
        v-for="order in sortedOrders"
        :key="order.id"
        class="order-card"
      >
        <div class="order-header">
          <h3>Order #{{ order.id.slice(-6) }}</h3>
          <span class="status-badge" :class="getStatusClass(order.status)">
            {{ order.status }}
          </span>
        </div>
        
        <div class="order-details">
          <div class="detail-item">
            <strong>Model Links:</strong>
            <div class="links-list">
              <a
                v-for="(link, index) in getOrderLinks(order)"
                :key="index"
                :href="link"
                target="_blank"
                rel="noopener noreferrer"
                class="model-link"
              >
                {{ link }}
              </a>
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storage } from '../utils/storage'

const orders = ref([])

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
  if (!user) return
  
  try {
    const allOrders = await storage.getOrders(user.id)
    orders.value = allOrders
  } catch (e) {
    console.error('Error loading orders:', e)
  }
}

const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => {
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

onMounted(() => {
  loadOrders()
  // Refresh orders every 5 seconds to catch updates from printer
  setInterval(loadOrders, 5000)
})
</script>

<style scoped>
.container {
  max-width: 1000px;
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

.create-btn {
  background: #87CEEB;
  color: #000;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.create-btn:hover {
  background: #6bb6d6;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
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
  margin-bottom: 1.5rem;
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
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #3a3a3a;
}

.order-header h3 {
  color: #a0d4e8;
  font-size: 1.5rem;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
}

.status-created {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-reviewed {
  background: rgba(135, 206, 235, 0.2);
  color: #87CEEB;
  border: 1px solid rgba(135, 206, 235, 0.3);
}

.status-printing {
  background: rgba(100, 181, 246, 0.2);
  color: #64b5f6;
  border: 1px solid rgba(100, 181, 246, 0.3);
}

.status-printed {
  background: rgba(81, 207, 102, 0.2);
  color: #51cf66;
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.status-delivery {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-done {
  background: rgba(81, 207, 102, 0.2);
  color: #51cf66;
  border: 1px solid rgba(81, 207, 102, 0.3);
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

