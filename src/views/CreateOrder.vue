<template>
  <div class="container">
    <div class="page-header">
      <h1>Create Printing Order</h1>
    </div>
    <div class="order-form-card">
      <form @submit.prevent="handleSubmit" class="order-form">
        <div class="form-group">
          <label for="modelLink">Model Link *</label>
          <input
            id="modelLink"
            v-model="modelLink"
            type="url"
            required
            placeholder="https://example.com/model.stl"
          />
          <small>Link to your 3D model file</small>
        </div>
        
        <div class="form-group">
          <label>Select Colors *</label>
          <div class="colors-grid">
            <label
              v-for="color in availableColors"
              :key="color.id"
              class="color-option"
              :class="{ selected: selectedColors.includes(color.id) }"
            >
              <input
                type="checkbox"
                :value="color.id"
                v-model="selectedColors"
              />
              <span class="color-name">{{ color.name }}</span>
              <span
                class="color-preview"
                :style="{ backgroundColor: color.hex }"
              ></span>
            </label>
          </div>
          <small v-if="selectedColors.length === 0" class="error-text">
            Please select at least one color
          </small>
        </div>
        
        <div class="form-group">
          <label for="comment">Comment</label>
          <textarea
            id="comment"
            v-model="comment"
            rows="4"
            placeholder="Any additional notes or requirements..."
          ></textarea>
        </div>
        
        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="success" class="success-message">{{ success }}</div>
        
        <div class="form-actions">
          <button type="submit" class="submit-btn" :disabled="selectedColors.length === 0">
            Create Order
          </button>
          <router-link to="/orders" class="cancel-btn">Cancel</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'

const router = useRouter()
const modelLink = ref('')
const selectedColors = ref([])
const comment = ref('')
const error = ref('')
const success = ref('')

// Available material colors
const availableColors = ref([
  { id: 'red', name: 'Red', hex: '#e74c3c' },
  { id: 'blue', name: 'Blue', hex: '#3498db' },
  { id: 'green', name: 'Green', hex: '#27ae60' },
  { id: 'yellow', name: 'Yellow', hex: '#f1c40f' },
  { id: 'black', name: 'Black', hex: '#2c3e50' },
  { id: 'white', name: 'White', hex: '#ecf0f1' },
  { id: 'orange', name: 'Orange', hex: '#e67e22' },
  { id: 'purple', name: 'Purple', hex: '#9b59b6' },
  { id: 'pink', name: 'Pink', hex: '#e91e63' },
  { id: 'gray', name: 'Gray', hex: '#95a5a6' }
])

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const handleSubmit = () => {
  error.value = ''
  success.value = ''
  
  if (selectedColors.value.length === 0) {
    error.value = 'Please select at least one color'
    return
  }
  
  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    router.push('/login')
    return
  }
  
  const orders = storage.getOrders()
  const newOrder = {
    id: Date.now().toString(),
    userId: user.id,
    userName: user.username,
    modelLink: modelLink.value,
    colors: selectedColors.value.map(id => {
      const color = availableColors.value.find(c => c.id === id)
      return color ? color.name : id
    }),
    comment: comment.value,
    status: 'Created',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString()
  }
  
  orders.push(newOrder)
  storage.saveOrders(orders)
  
  success.value = 'Order created successfully!'
  
  setTimeout(() => {
    router.push('/orders')
  }, 1500)
}
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: white;
  font-size: 2.5rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.order-form-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.order-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 2rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.75rem;
  color: #e0e0e0;
  font-weight: 600;
  font-size: 1.1rem;
}

.form-group input[type="url"],
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  font-family: inherit;
  background: #1a1a1a;
  color: #e0e0e0;
}

.form-group input[type="url"]:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #87CEEB;
}

.form-group input[type="url"]::placeholder,
.form-group textarea::placeholder {
  color: #666;
}

.form-group small {
  display: block;
  margin-top: 0.5rem;
  color: #999;
  font-size: 0.9rem;
}

.error-text {
  color: #e74c3c !important;
}

.colors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 0.5rem;
}

.color-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s;
  background: #1a1a1a;
  color: #e0e0e0;
}

.color-option:hover {
  border-color: #87CEEB;
  background: #2a2a2a;
}

.color-option.selected {
  border-color: #87CEEB;
  background: #1a3a4a;
}

.color-option input[type="checkbox"] {
  cursor: pointer;
}

.color-name {
  flex: 1;
  font-weight: 500;
}

.color-preview {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: 2px solid #ddd;
}

.error-message {
  color: #ff6b6b;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: rgba(255, 107, 107, 0.1);
  border-radius: 5px;
  font-size: 0.9rem;
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.success-message {
  color: #51cf66;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: rgba(81, 207, 102, 0.1);
  border-radius: 5px;
  font-size: 0.9rem;
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.submit-btn {
  flex: 1;
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.75rem;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.submit-btn:hover:not(:disabled) {
  background: #6bb6d6;
}

.submit-btn:disabled {
  background: #3a3a3a;
  color: #666;
  cursor: not-allowed;
}

.cancel-btn {
  flex: 1;
  background: #3a3a3a;
  color: #e0e0e0;
  border: none;
  padding: 0.75rem;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
  text-decoration: none;
  text-align: center;
  display: inline-block;
}

.cancel-btn:hover {
  background: #4a4a4a;
}
</style>

