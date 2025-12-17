<template>
  <div class="container">
    <div class="page-header">
      <h1>Filament Management</h1>
    </div>

    <div class="filaments-content">
      <!-- Add/Edit Filament Form -->
      <div class="filament-form-card">
        <div class="form-tabs">
          <button
            type="button"
            :class="['tab-btn', { active: activeTab === 'filament' }]"
            @click="activeTab = 'filament'"
          >
            {{ editingFilament ? 'Edit Filament' : 'Add New Filament' }}
          </button>
          <button
            type="button"
            :class="['tab-btn', { active: activeTab === 'materials' }]"
            @click="activeTab = 'materials'"
          >
            Manage Material Types
          </button>
        </div>

        <!-- Filament Form Tab -->
        <form v-if="activeTab === 'filament'" @submit.prevent="handleSubmit" class="filament-form">
          <div class="form-group">
            <label for="filamentName">Filament Name *</label>
            <input
              id="filamentName"
              v-model="filamentForm.name"
              type="text"
              required
              placeholder="e.g., Red PLA, Blue PETG"
              maxlength="100"
            />
          </div>

          <div class="form-group">
            <label for="filamentColor">Color *</label>
            <div class="color-picker-wrapper">
              <input
                id="filamentColor"
                v-model="filamentForm.color"
                type="color"
                required
                class="color-picker"
              />
              <input
                v-model="filamentForm.color"
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
              v-model="filamentForm.materialType"
              required
              class="material-select"
            >
              <option value="">Select material type</option>
              <option
                v-for="type in materialTypes"
                :key="type.id"
                :value="type.name"
              >
                {{ type.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="shopLink">Shop Link</label>
            <input
              id="shopLink"
              v-model="filamentForm.shopLink"
              type="url"
              placeholder="https://example.com/filament"
            />
            <small>Link to the shop where this filament was purchased</small>
          </div>

          <div class="filament-preview">
            <div
              class="preview-box"
              :style="{ backgroundColor: filamentForm.color || '#000000' }"
            ></div>
            <div class="preview-info">
              <span class="preview-text">{{ filamentForm.name || 'Preview' }}</span>
              <span class="preview-type">{{ filamentForm.materialType || 'Material Type' }}</span>
            </div>
          </div>

          <div v-if="error" class="error-message">{{ error }}</div>
          <div v-if="success" class="success-message">{{ success }}</div>

          <div class="form-actions">
            <button type="submit" class="submit-btn">
              {{ editingFilament ? 'Update Filament' : 'Add Filament' }}
            </button>
            <button
              v-if="editingFilament"
              type="button"
              @click="cancelEdit"
              class="cancel-btn"
            >
              Cancel
            </button>
          </div>
        </form>

        <!-- Material Types Management Tab -->
        <div v-if="activeTab === 'materials'" class="material-types-management">
          <h3>Manage Material Types</h3>
          
          <!-- Add New Material Type -->
          <div class="add-material-type-form">
            <div class="form-group">
              <label for="newMaterialType">Add New Material Type</label>
              <div class="add-type-input-group">
                <input
                  id="newMaterialType"
                  v-model="newMaterialType"
                  type="text"
                  placeholder="e.g., PLA+, Carbon Fiber"
                  maxlength="50"
                  class="material-type-input"
                  @keyup.enter="addMaterialType"
                />
                <button
                  type="button"
                  @click="addMaterialType"
                  class="add-type-btn"
                  :disabled="!newMaterialType.trim() || addingMaterialType"
                >
                  {{ addingMaterialType ? 'Adding...' : 'Add' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Material Types List -->
          <div class="material-types-list">
            <h4>Available Material Types ({{ materialTypes.length }})</h4>
            <div v-if="materialTypes.length === 0" class="empty-materials">
              <p>No material types yet. Add your first one above!</p>
            </div>
            <div v-else class="materials-grid">
              <div
                v-for="type in materialTypes"
                :key="type.id"
                class="material-type-item"
              >
                <span class="material-type-name">{{ type.name }}</span>
                <button
                  type="button"
                  @click="deleteMaterialType(type.id)"
                  class="delete-type-btn"
                  :disabled="deletingMaterialType === type.id"
                  title="Delete material type"
                >
                  {{ deletingMaterialType === type.id ? '...' : '🗑️' }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="materialTypeError" class="error-message">{{ materialTypeError }}</div>
          <div v-if="materialTypeSuccess" class="success-message">{{ materialTypeSuccess }}</div>
        </div>
      </div>

      <!-- Assign Filament to Client -->
      <div class="assign-filament-card">
        <h2>Assign Filament to Client</h2>
        <form @submit.prevent="assignFilamentToClient" class="assign-form">
          <div class="form-group">
            <label for="clientSelect">Select Client *</label>
            <select
              id="clientSelect"
              v-model="assignForm.clientId"
              required
              class="client-select"
            >
              <option value="">Choose a client...</option>
              <option
                v-for="client in clients"
                :key="client.id"
                :value="client.id"
              >
                {{ client.username }} ({{ client.email }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="filamentSelect">Select Filament *</label>
            <select
              id="filamentSelect"
              v-model="assignForm.materialId"
              required
              class="filament-select"
            >
              <option value="">Choose a filament...</option>
              <option
                v-for="filament in filaments"
                :key="filament.id"
                :value="filament.id"
              >
                {{ filament.name }} ({{ filament.materialType }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="quantity">Quantity (Spools) *</label>
            <input
              id="quantity"
              v-model.number="assignForm.quantity"
              type="number"
              min="1"
              required
              class="quantity-input"
              placeholder="1"
            />
            <small>Number of spools to assign to this client</small>
          </div>

          <div v-if="assignError" class="error-message">{{ assignError }}</div>
          <div v-if="assignSuccess" class="success-message">{{ assignSuccess }}</div>

          <div class="form-actions">
            <button type="submit" class="submit-btn" :disabled="assigning">
              {{ assigning ? 'Assigning...' : 'Assign Filament' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Filaments List -->
      <div class="filaments-list-card">
        <div class="list-header">
          <h2>Your Filaments ({{ filaments.length }})</h2>
        </div>
        <div v-if="migrationMessage" :class="migrationMessageClass" class="migration-message" style="white-space: pre-line;">
          {{ migrationMessage }}
        </div>
        <div v-if="filaments.length === 0" class="empty-filaments">
          <p>No filaments added yet. Add your first filament above!</p>
          <p v-if="hasColors" class="migration-hint">
            You have existing colors. Click "Migrate Colors to Filaments" to convert them.
          </p>
        </div>
        <div v-else class="filaments-grid">
          <router-link
            v-for="filament in filaments"
            :key="filament.id"
            :to="`/filaments/${filament.id}`"
            class="filament-item"
          >
            <div
              class="filament-swatch"
              :style="{ backgroundColor: filament.color || '#000000' }"
            ></div>
            <div class="filament-info">
              <h3>{{ filament.name }}</h3>
              <p class="filament-type">{{ filament.materialType }}</p>
              <p class="filament-color">{{ filament.color ? filament.color.toUpperCase() : 'N/A' }}</p>
              <a
                v-if="filament.shopLink"
                :href="filament.shopLink"
                target="_blank"
                rel="noopener noreferrer"
                class="shop-link"
                @click.stop
              >
                Shop Link →
              </a>
            </div>
            <div class="filament-actions" @click.stop>
              <button
                @click.prevent="editFilament(filament)"
                class="edit-btn"
                title="Edit filament"
              >
                ✏️
              </button>
              <button
                @click.prevent="deleteFilament(filament.id)"
                class="delete-btn"
                title="Delete filament"
              >
                🗑️
              </button>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'

const router = useRouter()
const filaments = ref([])
const filamentForm = ref({
  name: '',
  color: '#000000',
  materialType: '',
  shopLink: ''
})
const editingFilament = ref(null)
const error = ref('')
const success = ref('')
const migrating = ref(false)
const migrationMessage = ref('')
const migrationMessageClass = ref('')
const hasColors = ref(false)
const activeTab = ref('filament')
const materialTypes = ref([])
const newMaterialType = ref('')
const addingMaterialType = ref(false)
const deletingMaterialType = ref(null)
const materialTypeError = ref('')
const materialTypeSuccess = ref('')
const clients = ref([])
const assignForm = ref({
  clientId: '',
  materialId: '',
  quantity: 1
})
const assigning = ref(false)
const assignError = ref('')
const assignSuccess = ref('')

const getCurrentUser = () => {
  const userStr = localStorage.getItem('currentUser')
  if (!userStr) return null
  try {
    return JSON.parse(userStr)
  } catch {
    return null
  }
}

const loadFilaments = async () => {
  const user = getCurrentUser()
  if (!user) return
  
  try {
    const allFilaments = await storage.getMaterials(user.id)
    filaments.value = allFilaments
    
    // Check if user has colors that can be migrated
    try {
      const colors = await storage.getColors(user.id)
      hasColors.value = colors.length > 0
    } catch (e) {
      // Ignore error, just don't show migration hint
      hasColors.value = false
    }
  } catch (e) {
    console.error('Error loading filaments:', e)
    error.value = 'Failed to load filaments. Please try again.'
  }
}

const migrateColors = async () => {
  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    return
  }
  
  if (!confirm('This will convert ALL existing colors from ALL users to filaments with default material type "PLA". Continue?')) {
    return
  }
  
  migrating.value = true
  migrationMessage.value = ''
  error.value = ''
  success.value = ''
  
  try {
    // Call migration without userId to migrate all users' colors
    const result = await storage.migrateColorsToFilaments(null)
    
    if (result.success) {
      let message = result.message
      if (result.users && result.users.length > 0) {
        const userDetails = result.users.map(u => `${u.username}: ${u.migrated} migrated, ${u.skipped} skipped`).join('; ')
        message += `\n\nPer user: ${userDetails}`
      }
      migrationMessage.value = message
      migrationMessageClass.value = 'success'
      
      // Reload filaments
      await loadFilaments()
      
      // Clear message after 8 seconds (longer for multi-user info)
      setTimeout(() => {
        migrationMessage.value = ''
      }, 8000)
    } else {
      migrationMessage.value = result.message || 'Migration completed with warnings'
      migrationMessageClass.value = 'info'
    }
  } catch (e) {
    console.error('Error migrating colors:', e)
    migrationMessage.value = 'Failed to migrate colors: ' + (e.message || 'Unknown error')
    migrationMessageClass.value = 'error'
  } finally {
    migrating.value = false
  }
}

const validateColor = () => {
  const hexPattern = /^#[0-9A-Fa-f]{6}$/
  if (filamentForm.value.color && !hexPattern.test(filamentForm.value.color)) {
    // Auto-fix: add # if missing
    if (!filamentForm.value.color.startsWith('#')) {
      filamentForm.value.color = '#' + filamentForm.value.color
    }
  }
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  if (!filamentForm.value.name || !filamentForm.value.color || !filamentForm.value.materialType) {
    error.value = 'Please fill in all required fields'
    return
  }
  
  const user = getCurrentUser()
  if (!user) {
    error.value = 'User not found. Please login again.'
    return
  }
  
  try {
    if (editingFilament.value) {
      // Update existing filament
      await storage.updateMaterial(editingFilament.value.id, {
        name: filamentForm.value.name,
        color: filamentForm.value.color,
        materialType: filamentForm.value.materialType,
        shopLink: filamentForm.value.shopLink
      })
      success.value = 'Filament updated successfully!'
    } else {
      // Create new filament
      const newFilament = {
        id: 'filament_' + Date.now().toString(),
        userId: user.id,
        name: filamentForm.value.name,
        color: filamentForm.value.color,
        materialType: filamentForm.value.materialType,
        shopLink: filamentForm.value.shopLink
      }
      await storage.createMaterial(newFilament)
      success.value = 'Filament added successfully!'
    }
    
    // Reset form
    filamentForm.value = {
      name: '',
      color: '#000000',
      materialType: '',
      shopLink: ''
    }
    editingFilament.value = null
    
    // Reload filaments
    await loadFilaments()
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      success.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error saving filament:', e)
    error.value = 'Failed to save filament. Please try again.'
  }
}

const editFilament = (filament) => {
  editingFilament.value = filament
  filamentForm.value = {
    name: filament.name,
    color: filament.color || '#000000',
    materialType: filament.materialType || '',
    shopLink: filament.shopLink || ''
  }
  // Scroll to form
  document.querySelector('.filament-form-card')?.scrollIntoView({ behavior: 'smooth' })
}

const cancelEdit = () => {
  editingFilament.value = null
  filamentForm.value = {
    name: '',
    color: '#000000',
    materialType: '',
    shopLink: ''
  }
  error.value = ''
  success.value = ''
}

const deleteFilament = async (filamentId) => {
  if (!confirm('Are you sure you want to delete this filament?')) {
    return
  }
  
  try {
    await storage.deleteMaterial(filamentId)
    success.value = 'Filament deleted successfully!'
    await loadFilaments()
    
    setTimeout(() => {
      success.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error deleting filament:', e)
    error.value = 'Failed to delete filament. Please try again.'
  }
}

const loadMaterialTypes = async () => {
  try {
    materialTypes.value = await storage.getMaterialTypes()
  } catch (e) {
    console.error('Error loading material types:', e)
    materialTypeError.value = 'Failed to load material types. Please try again.'
  }
}

const addMaterialType = async () => {
  const name = newMaterialType.value.trim()
  if (!name) return

  addingMaterialType.value = true
  materialTypeError.value = ''
  materialTypeSuccess.value = ''

  try {
    await storage.createMaterialType(name)
    materialTypeSuccess.value = `Material type "${name}" added successfully!`
    newMaterialType.value = ''
    await loadMaterialTypes()
    
    setTimeout(() => {
      materialTypeSuccess.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error adding material type:', e)
    const errorMsg = e.message || 'Failed to add material type'
    if (errorMsg.includes('already exists')) {
      materialTypeError.value = `Material type "${name}" already exists!`
    } else {
      materialTypeError.value = errorMsg
    }
  } finally {
    addingMaterialType.value = false
  }
}

const deleteMaterialType = async (typeId) => {
  const type = materialTypes.value.find(t => t.id === typeId)
  if (!type) return

  if (!confirm(`Are you sure you want to delete "${type.name}"? This cannot be undone if any filaments are using this type.`)) {
    return
  }

  deletingMaterialType.value = typeId
  materialTypeError.value = ''
  materialTypeSuccess.value = ''

  try {
    await storage.deleteMaterialType(typeId)
    materialTypeSuccess.value = `Material type "${type.name}" deleted successfully!`
    await loadMaterialTypes()
    
    // If the deleted type was selected in the form, clear it
    if (filamentForm.value.materialType === type.name) {
      filamentForm.value.materialType = ''
    }
    
    setTimeout(() => {
      materialTypeSuccess.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error deleting material type:', e)
    const errorMsg = e.message || 'Failed to delete material type'
    if (errorMsg.includes('using it')) {
      materialTypeError.value = `Cannot delete "${type.name}": ${errorMsg}`
    } else {
      materialTypeError.value = errorMsg
    }
  } finally {
    deletingMaterialType.value = null
  }
}

const loadClients = async () => {
  try {
    const allUsers = await storage.getUsers()
    // Filter to only show clients (role = 'user')
    clients.value = allUsers.filter(u => u.role === 'user')
  } catch (e) {
    console.error('Error loading clients:', e)
  }
}

const assignFilamentToClient = async () => {
  assignError.value = ''
  assignSuccess.value = ''

  if (!assignForm.value.clientId || !assignForm.value.materialId || !assignForm.value.quantity) {
    assignError.value = 'Please fill in all fields'
    return
  }

  assigning.value = true

  try {
    await storage.assignFilamentToUser(
      assignForm.value.clientId,
      assignForm.value.materialId,
      assignForm.value.quantity
    )
    assignSuccess.value = `Filament assigned successfully!`
    
    // Reset form
    assignForm.value = {
      clientId: '',
      materialId: '',
      quantity: 1
    }
    
    setTimeout(() => {
      assignSuccess.value = ''
    }, 3000)
  } catch (e) {
    console.error('Error assigning filament:', e)
    assignError.value = e.message || 'Failed to assign filament. Please try again.'
  } finally {
    assigning.value = false
  }
}

onMounted(() => {
  loadFilaments()
  loadMaterialTypes()
  loadClients()
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

.filaments-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .filaments-content {
    grid-template-columns: 1fr;
  }
}

.filament-form-card,
.filaments-list-card,
.assign-filament-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
}

.assign-filament-card {
  margin-top: 2rem;
}

.assign-filament-card h2 {
  color: #a0d4e8;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

.assign-form {
  display: flex;
  flex-direction: column;
}

.client-select,
.filament-select,
.quantity-input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #b8dce8;
  font-family: inherit;
}

.client-select:focus,
.filament-select:focus,
.quantity-input:focus {
  outline: none;
  border-color: #87CEEB;
}

.filament-form-card h2,
.filaments-list-card h2 {
  color: #a0d4e8;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

.form-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #3a3a3a;
}

.tab-btn {
  background: transparent;
  border: none;
  color: #999;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all 0.3s;
}

.tab-btn:hover {
  color: #a0d4e8;
}

.tab-btn.active {
  color: #87CEEB;
  border-bottom-color: #87CEEB;
}

.material-types-management {
  padding-top: 1rem;
}

.material-types-management h3 {
  color: #a0d4e8;
  margin-bottom: 1.5rem;
  font-size: 1.3rem;
}

.material-types-management h4 {
  color: #87CEEB;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.add-material-type-form {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #3a3a3a;
}

.add-type-input-group {
  display: flex;
  gap: 0.5rem;
}

.material-type-input {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  background: #1a1a1a;
  color: #b8dce8;
  font-family: inherit;
}

.material-type-input:focus {
  outline: none;
  border-color: #87CEEB;
}

.add-type-btn {
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
  white-space: nowrap;
}

.add-type-btn:hover:not(:disabled) {
  background: #6bb6d6;
}

.add-type-btn:disabled {
  background: #3a3a3a;
  color: #666;
  cursor: not-allowed;
}

.material-types-list {
  margin-top: 1.5rem;
}

.empty-materials {
  text-align: center;
  padding: 2rem;
  color: #999;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.material-type-item {
  background: #1a1a1a;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.material-type-item:hover {
  border-color: #87CEEB;
}

.material-type-name {
  color: #b8dce8;
  font-weight: 600;
  flex: 1;
}

.delete-type-btn {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  padding: 0.5rem;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
  min-width: 2.5rem;
}

.delete-type-btn:hover:not(:disabled) {
  background: rgba(255, 107, 107, 0.2);
  border-color: #ff6b6b;
}

.delete-type-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.migrate-btn {
  background: #f39c12;
  color: #000;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.migrate-btn:hover:not(:disabled) {
  background: #e67e22;
}

.migrate-btn:disabled {
  background: #3a3a3a;
  color: #666;
  cursor: not-allowed;
}

.migration-message {
  margin-bottom: 1rem;
  padding: 0.75rem;
  border-radius: 5px;
  font-size: 0.9rem;
}

.migration-message.success {
  color: #51cf66;
  background: rgba(81, 207, 102, 0.1);
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.migration-message.info {
  color: #87CEEB;
  background: rgba(135, 206, 235, 0.1);
  border: 1px solid rgba(135, 206, 235, 0.3);
}

.migration-message.error {
  color: #ff6b6b;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.migration-hint {
  color: #87CEEB;
  font-size: 0.9rem;
  margin-top: 1rem;
  font-style: italic;
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

.filament-preview {
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

.empty-filaments {
  text-align: center;
  padding: 3rem;
  color: #999;
}

.filaments-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .filaments-grid {
    grid-template-columns: 1fr;
  }
}

.filament-item {
  background: #1a1a1a;
  border-radius: 10px;
  padding: 1.5rem;
  border: 2px solid #3a3a3a;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  position: relative;
}

.filament-item:hover {
  border-color: #87CEEB;
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(135, 206, 235, 0.2);
}

.filament-swatch {
  width: 100%;
  height: 80px;
  border-radius: 10px;
  border: 2px solid #3a3a3a;
}

.filament-info {
  flex: 1;
}

.filament-info h3 {
  color: #a0d4e8;
  margin-bottom: 0.5rem;
  font-size: 1.2rem;
}

.filament-type {
  color: #87CEEB;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.filament-color {
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

.filament-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.edit-btn,
.delete-btn {
  background: rgba(42, 42, 42, 0.8);
  border: 1px solid #3a3a3a;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s;
  backdrop-filter: blur(5px);
}

.edit-btn:hover {
  background: rgba(135, 206, 235, 0.2);
  border-color: #87CEEB;
}

.delete-btn:hover {
  background: rgba(255, 107, 107, 0.2);
  border-color: #ff6b6b;
}

/* Responsive Design */
@media (max-width: 968px) {
  .filaments-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .filament-form-card,
  .filaments-list-card,
  .assign-filament-card {
    padding: 1.5rem;
  }

  .filaments-grid {
    grid-template-columns: 1fr;
  }

  .form-tabs {
    flex-wrap: wrap;
  }

  .tab-btn {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
  }

  .materials-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }

  .filament-form-card,
  .filaments-list-card,
  .assign-filament-card {
    padding: 1rem;
  }

  .color-picker-wrapper {
    flex-direction: column;
  }

  .add-type-input-group {
    flex-direction: column;
  }

  .add-type-btn {
    width: 100%;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>

