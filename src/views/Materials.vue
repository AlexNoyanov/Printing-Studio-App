<template>
  <div class="container">
    <div class="page-header">
      <h1>Material Management</h1>
    </div>

    <div class="materials-content">
      <!-- Add/Edit Material Form -->
      <div class="material-form-card">
        <h2>{{ editingMaterial ? 'Edit Material' : 'Add New Material' }}</h2>
        <form @submit.prevent="handleSubmit" class="material-form">
          <div class="form-group">
            <label for="materialName">Material Name *</label>
            <input
              id="materialName"
              v-model="materialForm.name"
              type="text"
              required
              placeholder="e.g., Red PLA, Blue PETG"
              maxlength="100"
            />
          </div>

          <div class="form-group">
            <label for="materialColor">Color *</label>
            <div class="color-picker-wrapper">
              <input
                id="materialColor"
                v-model="materialForm.color"
                type="color"
                required
                class="color-picker"
              />
              <input
                v-model="materialForm.color"
                type="text"
                placeholder="#000000"
                pattern="^#[0-9A-Fa-f]{6}$"
                class="color-input"
                @input="validateColor"
              />
            </div>
            <small>Pick a color or enter hex value (e.g., #FF5733)</small>
          </div>

          <div class="form-group">
            <label for="materialType">Material Type *</label>
            <select
              id="materialType"
              v-model="materialForm.materialType"
              required
              class="material-select"
            >
              <option value="">Select material type</option>
              <option value="PLA">PLA</option>
              <option value="PETG">PETG</option>
              <option value="ABS">ABS</option>
              <option value="TPU">TPU</option>
              <option value="ASA">ASA</option>
              <option value="PC">PC (Polycarbonate)</option>
              <option value="Nylon">Nylon</option>
              <option value="Wood">Wood</option>
              <option value="Metal">Metal</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="shopLink">Shop Link</label>
            <input
              id="shopLink"
              v-model="materialForm.shopLink"
              type="url"
              placeholder="https://example.com/filament"
            />
            <small>Link to the shop where this filament was purchased</small>
          </div>

          <div class="material-preview">
            <div
              class="preview-box"
              :style="{ backgroundColor: materialForm.color || '#000000' }"
            ></div>
            <div class="preview-info">
              <span class="preview-text">{{ materialForm.name || 'Preview' }}</span>
              <span class="preview-type">{{ materialForm.materialType || 'Material Type' }}</span>
            </div>
          </div>

          <div v-if="error" class="error-message">{{ error }}</div>
          <div v-if="success" class="success-message">{{ success }}</div>

          <div class="form-actions">
            <button type="submit" class="submit-btn">
              {{ editingMaterial ? 'Update Material' : 'Add Material' }}
            </button>
            <button
              v-if="editingMaterial"
              type="button"
              @click="cancelEdit"
              class="cancel-btn"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Materials List -->
      <div class="materials-list-card">
        <h2>Your Materials ({{ materials.length }})</h2>
        <div v-if="materials.length === 0" class="empty-materials">
          <p>No materials added yet. Add your first material above!</p>
        </div>
        <div v-else class="materials-grid">
          <div
            v-for="material in materials"
            :key="material.id"
            class="material-item"
          >
            <div
              class="material-swatch"
              :style="{ backgroundColor: material.color }"
            ></div>
            <div class="material-info">
              <h3>{{ material.name }}</h3>
              <p class="material-type">{{ material.materialType }}</p>
              <p class="material-color">{{ material.color.toUpperCase() }}</p>
              <a
                v-if="material.shopLink"
                :href="material.shopLink"
                target="_blank"
                rel="noopener noreferrer"
                class="shop-link"
              >
                Shop Link →
              </a>
            </div>
            <div class="material-actions">
              <button
                @click="editMaterial(material)"
                class="edit-btn"
                title="Edit material"
              >
                ✏️
              </button>
              <button
                @click="deleteMaterial(material.id)"
                class="delete-btn"
                title="Delete material"
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

const materials = ref([])
const materialForm = ref({
  name: '',
  color: '#000000',
  materialType: '',
  shopLink: ''
})
const editingMaterial = ref(null)
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

const loadMaterials = async () => {
  const user = getCurrentUser()
  if (!user) return
  
  try {
    const allMaterials = await storage.getMaterials(user.id)
    materials.value = allMaterials
  } catch (e) {
    console.error('Error loading materials:', e)
    error.value = 'Failed to load materials. Please try again.'
  }
}

const validateColor = () => {
  const hexPattern = /^#[0-9A-Fa-f]{6}$/
  if (materialForm.value.color && !hexPattern.test(materialForm.value.color)) {
    // Auto-fix: add # if missing
    if (!materialForm.value.color.startsWith('#')) {
      materialForm.value.color = '#' + materialForm.value.color
    }
  }
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  if (!materialForm.value.name || !materialForm.value.color || !materialForm.value.materialType) {
    error.value = 'Please fill in all required fields'
    return
  }
  
  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    return
  }
  
  try {
    if (editingMaterial.value) {
      // Update existing material
      await storage.updateMaterial(editingMaterial.value.id, {
        name: materialForm.value.name,
        color: materialForm.value.color,
        materialType: materialForm.value.materialType,
        shopLink: materialForm.value.shopLink
      })
      success.value = 'Material updated successfully!'
    } else {
      // Create new material
      const newMaterial = {
        id: 'mat_' + Date.now().toString(),
        userId: user.id,
        name: materialForm.value.name,
        color: materialForm.value.color,
        materialType: materialForm.value.materialType,
        shopLink: materialForm.value.shopLink
      }
      await storage.createMaterial(newMaterial)
      success.value = 'Material added successfully!'
    }
    
    // Reset form
    materialForm.value = {
      name: '',
      color: '#000000',
      materialType: '',
      shopLink: ''
    }
    editingMaterial.value = null
    
    // Reload materials
    await loadMaterials()
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      success.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error saving material:', e)
    error.value = 'Failed to save material. Please try again.'
  }
}

const editMaterial = (material) => {
  editingMaterial.value = material
  materialForm.value = {
    name: material.name,
    color: material.color,
    materialType: material.materialType,
    shopLink: material.shopLink || ''
  }
  // Scroll to form
  document.querySelector('.material-form-card')?.scrollIntoView({ behavior: 'smooth' })
}

const cancelEdit = () => {
  editingMaterial.value = null
  materialForm.value = {
    name: '',
    color: '#000000',
    materialType: '',
    shopLink: ''
  }
  error.value = ''
  success.value = ''
}

const deleteMaterial = async (materialId) => {
  if (!confirm('Are you sure you want to delete this material?')) {
    return
  }
  
  try {
    await storage.deleteMaterial(materialId)
    success.value = 'Material deleted successfully!'
    await loadMaterials()
    
    setTimeout(() => {
      success.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error deleting material:', e)
    error.value = 'Failed to delete material. Please try again.'
  }
}

onMounted(() => {
  loadMaterials()
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
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5), 0 0 20px rgba(135, 206, 235, 0.3), 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 50%, #4da6c2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.materials-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .materials-content {
    grid-template-columns: 1fr;
  }
}

.material-form-card,
.materials-list-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.material-form-card h2,
.materials-list-card h2 {
  color: #a0d4e8;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #a0d4e8;
  font-weight: 600;
}

.form-group input[type="text"],
.form-group input[type="url"],
.material-select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #b8dce8;
  font-family: inherit;
}

.form-group input:focus,
.material-select:focus {
  outline: none;
  border-color: #87CEEB;
}

.color-picker-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.color-picker {
  width: 60px;
  height: 40px;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  cursor: pointer;
}

.color-input {
  flex: 1;
}

.form-group small {
  display: block;
  margin-top: 0.5rem;
  color: #999;
  font-size: 0.9rem;
}

.material-preview {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #1a1a1a;
  border-radius: 5px;
  margin-bottom: 1.5rem;
}

.preview-box {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 2px solid #3a3a3a;
  flex-shrink: 0;
}

.preview-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.preview-text {
  color: #b8dce8;
  font-weight: 600;
}

.preview-type {
  color: #87CEEB;
  font-size: 0.9rem;
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

.empty-materials {
  text-align: center;
  padding: 3rem;
  color: #999;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.material-item {
  background: #1a1a1a;
  border-radius: 10px;
  padding: 1.5rem;
  border: 2px solid #3a3a3a;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.material-item:hover {
  border-color: #87CEEB;
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(135, 206, 235, 0.2);
}

.material-swatch {
  width: 100%;
  height: 80px;
  border-radius: 10px;
  border: 2px solid #3a3a3a;
}

.material-info {
  flex: 1;
}

.material-info h3 {
  color: #a0d4e8;
  margin-bottom: 0.5rem;
  font-size: 1.2rem;
}

.material-type {
  color: #87CEEB;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.material-color {
  color: #999;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.shop-link {
  color: #87CEEB;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s;
}

.shop-link:hover {
  color: #6bb6d6;
  text-decoration: underline;
}

.material-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.edit-btn,
.delete-btn {
  background: transparent;
  border: 1px solid #3a3a3a;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s;
}

.edit-btn:hover {
  background: rgba(135, 206, 235, 0.2);
  border-color: #87CEEB;
}

.delete-btn:hover {
  background: rgba(255, 107, 107, 0.2);
  border-color: #ff6b6b;
}
</style>

