<template>
  <div class="container">
    <div class="page-header">
      <h1>Create Printing Order</h1>
    </div>
    <div class="order-form-card">
      <form @submit.prevent="handleSubmit" class="order-form">
        <div class="form-group">
          <label>Model Links *</label>
          <div class="links-container">
            <div
              v-for="(link, index) in modelLinks"
              :key="index"
              class="link-input-group"
            >
              <input
                :id="`modelLink-${index}`"
                v-model="modelLinks[index].url"
                type="url"
                :required="index === 0"
                :placeholder="`https://example.com/model${index + 1}.stl`"
                class="link-input"
              />
              <input
                :id="`modelCopies-${index}`"
                v-model.number="modelLinks[index].copies"
                type="number"
                min="1"
                max="100"
                :required="index === 0"
                placeholder="Copies"
                class="copies-input"
                title="Number of copies"
              />
              <button
                v-if="modelLinks.length > 1"
                type="button"
                @click="removeLink(index)"
                class="remove-link-btn"
                title="Remove link"
              >
                ×
              </button>
            </div>
            <button
              type="button"
              @click="addLink"
              class="add-link-btn"
              title="Add another link"
            >
              + Add Link
            </button>
          </div>
          <small>Add one or more links to your 3D model files. Specify number of copies for each model.</small>
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
              <span
                class="color-preview"
                :style="{ backgroundColor: color.hex }"
              ></span>
              <span class="color-name">{{ color.name }}</span>
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
const modelLinks = ref([{ url: '', copies: 1 }])
const selectedColors = ref([])
const comment = ref('')
const error = ref('')
const success = ref('')

const addLink = () => {
  modelLinks.value.push({ url: '', copies: 1 })
}

const removeLink = (index) => {
  if (modelLinks.value.length > 1) {
    modelLinks.value.splice(index, 1)
  }
}

// Available filaments from database
const availableColors = ref([])

const loadColors = async () => {
  try {
    // Load filaments (materials) from database - these are what customers can select
    // Try materials first (new unified system)
    const materials = await storage.getMaterials()
    if (materials && materials.length > 0) {
      availableColors.value = materials.map(material => ({
        id: material.id,
        name: material.name,
        hex: material.color || '#ffffff',
        materialType: material.materialType
      }))
    } else {
      // Fallback to colors for backward compatibility
      const colors = await storage.getColors()
      availableColors.value = colors.map(color => ({
        id: color.id,
        name: color.name,
        hex: color.value || color.hex || '#ffffff'
      }))
    }
  } catch (e) {
    console.error('Error loading filaments/colors:', e)
    // Fallback to empty array if API fails
    availableColors.value = []
  }
}

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  if (selectedColors.value.length === 0) {
    error.value = 'Please select at least one color'
    return
  }
  
  if (availableColors.value.length === 0) {
    error.value = 'No colors available. Please contact the printer owner.'
    return
  }
  
  // Filter out empty links and validate copies
  const validLinks = modelLinks.value
    .filter(link => link.url && link.url.trim() !== '')
    .map(link => ({
      url: link.url.trim(),
      copies: Math.max(1, parseInt(link.copies) || 1) // Ensure at least 1 copy
    }))
  
  if (validLinks.length === 0) {
    error.value = 'Please add at least one model link'
    return
  }
  
  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    router.push('/login')
    return
  }
  
  try {
    const newOrder = {
      id: Date.now().toString(),
      userId: user.id,
      userName: user.username,
      modelLink: validLinks[0].url, // Backward compatibility
      modelLinks: validLinks.map(l => l.url), // Array of URLs for backward compatibility
      modelLinksWithCopies: validLinks, // New: array with copies
      colors: selectedColors.value.map(id => {
        const color = availableColors.value.find(c => c.id === id)
        return color ? color.name : id
      }),
      comment: comment.value,
      status: 'Created',
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    }
    
    await storage.createOrder(newOrder)
    
    success.value = 'Order created successfully!'
    
    setTimeout(() => {
      router.push('/orders')
    }, 1500)
  } catch (e) {
    error.value = 'Failed to create order. Please try again.'
    console.error('Create order error:', e)
  }
}

onMounted(() => {
  loadColors()
})
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
  color: #87CEEB;
  font-size: 2.5rem;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5), 0 0 20px rgba(135, 206, 235, 0.3), 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 50%, #4da6c2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
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
  color: #a0d4e8;
  font-weight: 600;
  font-size: 1.1rem;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
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
  color: #b8dce8;
}

.form-group input[type="url"]:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #87CEEB;
}

.links-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.link-input-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.link-input {
  flex: 1;
}

.copies-input {
  width: 80px;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #b8dce8;
  font-family: inherit;
  text-align: center;
}

.copies-input:focus {
  outline: none;
  border-color: #87CEEB;
}

.remove-link-btn {
  background: #e74c3c;
  color: white;
  border: none;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 5px;
  font-size: 1.5rem;
  cursor: pointer;
  transition: background 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.remove-link-btn:hover {
  background: #c0392b;
}

.add-link-btn {
  background: #27ae60;
  color: white;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
  align-self: flex-start;
}

.add-link-btn:hover {
  background: #229954;
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
  gap: 0.75rem;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s;
  background: #1a1a1a;
  color: #e0e0e0;
  min-height: 60px;
  position: relative;
}

.color-option:hover {
  border-color: #87CEEB;
  background: #2a2a2a;
}

.color-option.selected {
  border-color: #87CEEB;
  background: #1a3a4a;
  box-shadow: 0 0 15px rgba(135, 206, 235, 0.3);
}

.color-option.selected::before {
  content: '✓';
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 24px;
  height: 24px;
  background: #87CEEB;
  color: #000;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  box-shadow: 0 0 10px rgba(135, 206, 235, 0.5);
}

.color-option input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.color-name {
  flex: 1;
  font-weight: 500;
}

.color-preview {
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  max-width: 40px;
  max-height: 40px;
  border-radius: 50%;
  border: 2px solid #ddd;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  display: block;
  aspect-ratio: 1 / 1;
}

.loading-colors {
  padding: 2rem;
  text-align: center;
  color: #999;
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

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .order-form-card {
    padding: 1.5rem;
  }

  .colors-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.75rem;
  }

  .color-option {
    padding: 0.5rem;
    min-height: 50px;
    gap: 0.5rem;
  }

  .color-preview {
    width: 30px;
    height: 30px;
    min-width: 30px;
    min-height: 30px;
    max-width: 30px;
    max-height: 30px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button,
  .form-actions a {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }

  .order-form-card {
    padding: 1rem;
  }

  .colors-grid {
    grid-template-columns: 1fr 1fr;
  }

  .color-name {
    font-size: 0.85rem;
  }
}
</style>

