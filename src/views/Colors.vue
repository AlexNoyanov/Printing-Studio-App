<template>
  <div class="container">
    <div class="page-header">
      <h1>Color Management</h1>
    </div>

    <div class="colors-content">
      <!-- Add/Edit Color Form -->
      <div class="color-form-card">
        <h2>{{ editingColor ? 'Edit Color' : 'Add New Color' }}</h2>
        <form @submit.prevent="handleSubmit" class="color-form">
          <div class="form-group">
            <label for="colorName">Color Name *</label>
            <input
              id="colorName"
              v-model="colorForm.name"
              type="text"
              required
              placeholder="Enter color name"
              maxlength="50"
            />
          </div>

          <div class="form-group">
            <label for="colorValue">Color Value *</label>
            <div class="color-picker-wrapper">
              <input
                id="colorValue"
                v-model="colorForm.value"
                type="color"
                required
                class="color-picker"
              />
              <input
                v-model="colorForm.value"
                type="text"
                placeholder="#000000"
                pattern="^#[0-9A-Fa-f]{6}$"
                class="color-input"
                @input="validateColor"
              />
            </div>
            <small>Pick a color or enter hex value (e.g., #FF5733)</small>
          </div>

          <div class="color-preview">
            <div
              class="preview-box"
              :style="{ backgroundColor: colorForm.value || '#000000' }"
            ></div>
            <span class="preview-text">{{ colorForm.name || 'Preview' }}</span>
          </div>

          <div v-if="error" class="error-message">{{ error }}</div>
          <div v-if="success" class="success-message">{{ success }}</div>

          <div class="form-actions">
            <button type="submit" class="submit-btn">
              {{ editingColor ? 'Update Color' : 'Add Color' }}
            </button>
            <button
              v-if="editingColor"
              type="button"
              @click="cancelEdit"
              class="cancel-btn"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Colors List -->
      <div class="colors-list-card">
        <h2>Your Colors ({{ colors.length }})</h2>
        <div v-if="colors.length === 0" class="empty-colors">
          <p>No colors added yet. Add your first color above!</p>
        </div>
        <div v-else class="colors-grid">
          <div
            v-for="color in colors"
            :key="color.id"
            class="color-item"
          >
            <div
              class="color-swatch"
              :style="{ backgroundColor: color.value }"
            ></div>
            <div class="color-info">
              <h3>{{ color.name }}</h3>
              <p class="color-hex">{{ color.value.toUpperCase() }}</p>
            </div>
            <div class="color-actions">
              <button
                @click="editColor(color)"
                class="edit-btn"
                title="Edit color"
              >
                ✏️
              </button>
              <button
                @click="deleteColor(color.id)"
                class="delete-btn"
                title="Delete color"
              >
                🗑️
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { storage } from '../utils/storage'

const colors = ref([])
const colorForm = ref({
  name: '',
  value: '#000000'
})
const editingColor = ref(null)
const error = ref('')
const success = ref('')

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const loadColors = () => {
  const user = getCurrentUser()
  if (!user) return

  const allColors = storage.getColors()
  colors.value = allColors.filter(c => c.userId === user.id)
}

const validateColor = (event) => {
  const value = event.target.value
  if (value.startsWith('#')) {
    colorForm.value.value = value
  } else if (value.length > 0) {
    colorForm.value.value = '#' + value
  }
}

const handleSubmit = () => {
  error.value = ''
  success.value = ''

  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    return
  }

  if (!colorForm.value.name.trim()) {
    error.value = 'Please enter a color name'
    return
  }

  if (!/^#[0-9A-Fa-f]{6}$/.test(colorForm.value.value)) {
    error.value = 'Please enter a valid hex color (e.g., #FF5733)'
    return
  }

  const allColors = storage.getColors()

  if (editingColor.value) {
    // Update existing color
    const index = allColors.findIndex(c => c.id === editingColor.value.id)
    if (index !== -1) {
      allColors[index] = {
        ...allColors[index],
        name: colorForm.value.name.trim(),
        value: colorForm.value.value.toUpperCase()
      }
      storage.saveColors(allColors)
      success.value = 'Color updated successfully!'
    }
  } else {
    // Add new color
    // Check if color name already exists for this user
    const existingColor = allColors.find(
      c => c.userId === user.id && c.name.toLowerCase() === colorForm.value.name.trim().toLowerCase()
    )
    if (existingColor) {
      error.value = 'A color with this name already exists'
      return
    }

    const newColor = {
      id: Date.now().toString(),
      userId: user.id,
      name: colorForm.value.name.trim(),
      value: colorForm.value.value.toUpperCase()
    }
    allColors.push(newColor)
    storage.saveColors(allColors)
    success.value = 'Color added successfully!'
  }

  loadColors()
  resetForm()

  setTimeout(() => {
    success.value = ''
    error.value = ''
  }, 3000)
}

const editColor = (color) => {
  editingColor.value = color
  colorForm.value = {
    name: color.name,
    value: color.value
  }
  // Scroll to form
  document.querySelector('.color-form-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const cancelEdit = () => {
  editingColor.value = null
  resetForm()
}

const resetForm = () => {
  colorForm.value = {
    name: '',
    value: '#000000'
  }
  editingColor.value = null
}

const deleteColor = (colorId) => {
  if (!confirm('Are you sure you want to delete this color?')) {
    return
  }

  const allColors = storage.getColors()
  const filteredColors = allColors.filter(c => c.id !== colorId)
  storage.saveColors(filteredColors)
  loadColors()
  success.value = 'Color deleted successfully!'
  setTimeout(() => {
    success.value = ''
  }, 3000)
}

onMounted(() => {
  loadColors()
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
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #87CEEB;
  font-size: 2.5rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.colors-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .colors-content {
    grid-template-columns: 1fr;
  }
}

.color-form-card,
.colors-list-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.color-form-card h2,
.colors-list-card h2 {
  color: #87CEEB;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

.color-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #e0e0e0;
  font-weight: 500;
}

.form-group input[type="text"] {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #e0e0e0;
  transition: border-color 0.3s;
}

.form-group input[type="text"]:focus {
  outline: none;
  border-color: #87CEEB;
}

.form-group input[type="text"]::placeholder {
  color: #666;
}

.color-picker-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.color-picker {
  width: 80px;
  height: 50px;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  cursor: pointer;
  background: none;
}

.color-input {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #e0e0e0;
  font-family: monospace;
  text-transform: uppercase;
}

.color-input:focus {
  outline: none;
  border-color: #87CEEB;
}

.form-group small {
  display: block;
  margin-top: 0.5rem;
  color: #999;
  font-size: 0.9rem;
}

.color-preview {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #1a1a1a;
  border-radius: 5px;
  margin-bottom: 1.5rem;
  border: 1px solid #3a3a3a;
}

.preview-box {
  width: 60px;
  height: 60px;
  border-radius: 5px;
  border: 2px solid #3a3a3a;
}

.preview-text {
  color: #e0e0e0;
  font-weight: 500;
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

.submit-btn:hover {
  background: #6bb6d6;
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
}

.cancel-btn:hover {
  background: #4a4a4a;
}

.empty-colors {
  text-align: center;
  padding: 2rem;
  color: #999;
}

.colors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.color-item {
  background: #1a1a1a;
  border: 1px solid #3a3a3a;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  transition: transform 0.3s, box-shadow 0.3s;
}

.color-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.color-swatch {
  width: 100%;
  height: 80px;
  border-radius: 5px;
  border: 2px solid #3a3a3a;
}

.color-info {
  flex: 1;
}

.color-info h3 {
  color: #e0e0e0;
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.color-hex {
  color: #999;
  font-size: 0.85rem;
  font-family: monospace;
}

.color-actions {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn {
  flex: 1;
  background: #3a3a3a;
  border: none;
  padding: 0.5rem;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s;
}

.edit-btn:hover {
  background: #4a4a4a;
}

.delete-btn:hover {
  background: #ff6b6b;
}
</style>

