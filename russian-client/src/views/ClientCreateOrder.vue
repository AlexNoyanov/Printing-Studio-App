<template>
  <div class="client-create-order">
    <div class="container">
      <!-- Header -->
      <div class="page-header">
        <h1>Создать новый заказ</h1>
        <p>Заполните форму ниже, чтобы отправить заказ на 3D-печать</p>
      </div>

      <!-- Form Card -->
      <div class="form-card">
        <form @submit.prevent="handleSubmit" class="order-form">
          <!-- Model Links Section -->
          <div class="form-section">
            <div class="section-header">
              <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
              </svg>
              <div>
                <h2>Ссылки на модели</h2>
                <p class="section-description">Добавьте ссылки на ваши 3D-модели (STL, OBJ и другие форматы)</p>
              </div>
            </div>

            <div class="links-container">
              <div
                v-for="(link, index) in modelLinks"
                :key="index"
                class="link-input-group"
              >
                <div class="link-input-wrapper">
                  <input
                    :id="`modelLink-${index}`"
                    v-model="modelLinks[index].url"
                    type="url"
                    :required="index === 0"
                    :placeholder="`https://example.com/model${index + 1}.stl`"
                    class="link-input"
                  />
                  <div class="copies-wrapper">
                    <label :for="`modelCopies-${index}`" class="copies-label">Копий:</label>
                    <input
                      :id="`modelCopies-${index}`"
                      v-model.number="modelLinks[index].copies"
                      type="number"
                      min="1"
                      max="100"
                      :required="index === 0"
                      class="copies-input"
                    />
                  </div>
                </div>
                <button
                  v-if="modelLinks.length > 1"
                  type="button"
                  @click="removeLink(index)"
                  class="remove-link-btn"
                  title="Удалить ссылку"
                >
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
              <button
                type="button"
                @click="addLink"
                class="add-link-btn"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Добавить еще одну ссылку
              </button>
            </div>
          </div>

          <!-- Colors Section -->
          <div class="form-section">
            <div class="section-header">
              <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
              </svg>
              <div>
                <h2>Выберите цвета</h2>
                <p class="section-description">Выберите один или несколько цветов для печати</p>
              </div>
            </div>

            <div v-if="availableColors.length === 0" class="loading-colors">
              <p>Загрузка доступных цветов...</p>
            </div>

            <div v-else class="colors-grid">
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
                  class="color-checkbox"
                />
                <div class="color-preview-wrapper">
                  <span
                    class="color-preview"
                    :style="{ backgroundColor: color.hex }"
                  ></span>
                  <div class="color-info">
                    <span class="color-name">{{ color.name }}</span>
                    <span v-if="color.materialType" class="color-type">{{ color.materialType }}</span>
                  </div>
                </div>
                <div class="checkmark">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
              </label>
            </div>

            <div v-if="selectedColors.length === 0 && availableColors.length > 0" class="error-hint">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              Пожалуйста, выберите хотя бы один цвет
            </div>
          </div>

          <!-- Comment Section -->
          <div class="form-section">
            <div class="section-header">
              <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
              <div>
                <h2>Комментарий (необязательно)</h2>
                <p class="section-description">Дополнительные требования или пожелания к заказу</p>
              </div>
            </div>
            <textarea
              id="comment"
              v-model="comment"
              rows="5"
              placeholder="Например: Толщина слоя 0.2мм, заполнение 20%, поддержки не нужны..."
              class="comment-input"
            ></textarea>
          </div>

          <!-- Messages -->
          <div v-if="error" class="error-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            {{ error }}
          </div>
          <div v-if="success" class="success-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ success }}
          </div>

          <!-- Actions -->
          <div class="form-actions">
            <button type="submit" class="submit-btn" :disabled="selectedColors.length === 0 || isLoading">
              <span v-if="!isLoading">Создать заказ</span>
              <span v-else class="loading-spinner"></span>
            </button>
            <router-link to="/home" class="cancel-btn">Отмена</router-link>
          </div>
        </form>
      </div>
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
const isLoading = ref(false)
const availableColors = ref([])

const addLink = () => {
  modelLinks.value.push({ url: '', copies: 1 })
}

const removeLink = (index) => {
  if (modelLinks.value.length > 1) {
    modelLinks.value.splice(index, 1)
  }
}

const loadColors = async () => {
  try {
    const materials = await storage.getMaterials()
    if (materials && materials.length > 0) {
      availableColors.value = materials.map(material => ({
        id: material.id,
        name: material.name,
        hex: material.color || '#ffffff',
        materialType: material.materialType
      }))
    } else {
      const colors = await storage.getColors()
      availableColors.value = colors.map(color => ({
        id: color.id,
        name: color.name,
        hex: color.value || color.hex || '#ffffff'
      }))
    }
  } catch (e) {
    console.error('Error loading filaments/colors:', e)
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
  isLoading.value = true
  
  if (selectedColors.value.length === 0) {
    error.value = 'Пожалуйста, выберите хотя бы один цвет'
    isLoading.value = false
    return
  }
  
  if (availableColors.value.length === 0) {
    error.value = 'Нет доступных цветов. Пожалуйста, свяжитесь с владельцем принтера.'
    isLoading.value = false
    return
  }
  
  const validLinks = modelLinks.value
    .filter(link => link.url && link.url.trim() !== '')
    .map(link => ({
      url: link.url.trim(),
      copies: Math.max(1, parseInt(link.copies) || 1)
    }))
  
  if (validLinks.length === 0) {
    error.value = 'Пожалуйста, добавьте хотя бы одну ссылку на модель'
    isLoading.value = false
    return
  }
  
  const user = getCurrentUser()
  if (!user) {
    error.value = 'Пользователь не найден. Пожалуйста, войдите снова.'
    router.push('/login')
    isLoading.value = false
    return
  }
  
  try {
    const newOrder = {
      id: Date.now().toString(),
      userId: user.id,
      userName: user.username,
      modelLink: validLinks[0].url,
      modelLinks: validLinks.map(l => l.url),
      modelLinksWithCopies: validLinks,
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
    
    success.value = 'Заказ успешно создан!'
    
    setTimeout(() => {
      router.push('/orders')
    }, 1500)
  } catch (e) {
    error.value = 'Не удалось создать заказ. Пожалуйста, попробуйте еще раз.'
    console.error('Create order error:', e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadColors()
})
</script>

<style scoped>
.client-create-order {
  min-height: 100vh;
  background: linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
  padding: 2rem 0;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 1rem;
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-header p {
  font-size: 1.1rem;
  color: #a0d4e8;
  margin: 0;
}

.form-card {
  background: rgba(26, 26, 42, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  border: 1px solid rgba(135, 206, 235, 0.2);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.order-form {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.section-icon {
  color: #87CEEB;
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.25rem;
}

.section-description {
  font-size: 0.9rem;
  color: #a0d4e8;
  margin: 0;
  opacity: 0.8;
}

.links-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.link-input-group {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.link-input-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.link-input {
  width: 100%;
  padding: 1rem 1.25rem;
  background: rgba(10, 10, 10, 0.6);
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
  font-family: inherit;
}

.link-input:focus {
  outline: none;
  border-color: #87CEEB;
  background: rgba(10, 10, 10, 0.8);
  box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.1);
}

.link-input::placeholder {
  color: #666;
}

.copies-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.copies-label {
  color: #a0d4e8;
  font-size: 0.9rem;
  font-weight: 500;
}

.copies-input {
  width: 80px;
  padding: 0.5rem;
  background: rgba(10, 10, 10, 0.6);
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 8px;
  color: #fff;
  font-size: 1rem;
  text-align: center;
  font-family: inherit;
}

.copies-input:focus {
  outline: none;
  border-color: #87CEEB;
}

.remove-link-btn {
  background: rgba(255, 107, 107, 0.1);
  border: 2px solid rgba(255, 107, 107, 0.3);
  color: #ff6b6b;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 0;
}

.remove-link-btn:hover {
  background: rgba(255, 107, 107, 0.2);
  border-color: rgba(255, 107, 107, 0.5);
}

.add-link-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(135, 206, 235, 0.1);
  border: 2px dashed rgba(135, 206, 235, 0.3);
  border-radius: 12px;
  color: #87CEEB;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.add-link-btn:hover {
  background: rgba(135, 206, 235, 0.2);
  border-color: rgba(135, 206, 235, 0.5);
}

.loading-colors {
  padding: 2rem;
  text-align: center;
  color: #a0d4e8;
}

.colors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}

.color-option {
  position: relative;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(10, 10, 10, 0.6);
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 70px;
}

.color-option:hover {
  border-color: rgba(135, 206, 235, 0.4);
  background: rgba(10, 10, 10, 0.8);
  transform: translateY(-2px);
}

.color-option.selected {
  border-color: #87CEEB;
  background: rgba(135, 206, 235, 0.15);
  box-shadow: 0 0 20px rgba(135, 206, 235, 0.3);
}

.color-checkbox {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.color-preview-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.color-preview {
  width: 50px;
  height: 50px;
  min-width: 50px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  flex-shrink: 0;
}

.color-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.color-name {
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
}

.color-type {
  color: #a0d4e8;
  font-size: 0.8rem;
  opacity: 0.8;
}

.checkmark {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 28px;
  height: 28px;
  background: #87CEEB;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #000;
  opacity: 0;
  transform: scale(0);
  transition: all 0.3s ease;
}

.color-option.selected .checkmark {
  opacity: 1;
  transform: scale(1);
}

.error-hint {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 12px;
  color: #ff6b6b;
  font-size: 0.9rem;
}

.comment-input {
  width: 100%;
  padding: 1rem 1.25rem;
  background: rgba(10, 10, 10, 0.6);
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  transition: all 0.3s ease;
}

.comment-input:focus {
  outline: none;
  border-color: #87CEEB;
  background: rgba(10, 10, 10, 0.8);
  box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.1);
}

.comment-input::placeholder {
  color: #666;
}

.error-message,
.success-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.95rem;
}

.error-message {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  color: #ff6b6b;
}

.success-message {
  background: rgba(81, 207, 102, 0.1);
  border: 1px solid rgba(81, 207, 102, 0.3);
  color: #51cf66;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.submit-btn {
  flex: 1;
  padding: 1.25rem;
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(135, 206, 235, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-btn {
  flex: 1;
  padding: 1.25rem;
  background: rgba(58, 58, 58, 0.6);
  color: #e0e0e0;
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: none;
  text-align: center;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: rgba(58, 58, 58, 0.8);
  border-color: rgba(135, 206, 235, 0.4);
}

.loading-spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(0, 0, 0, 0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 968px) {
  .form-card {
    padding: 2rem;
  }
  
  .colors-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }

  .form-card {
    padding: 1.5rem;
  }

  .page-header h1 {
    font-size: 1.75rem;
  }
  
  .form-group label {
    font-size: 0.95rem;
  }
  
  .form-group input,
  .form-group textarea,
  .form-group select {
    font-size: 0.95rem;
  }

  .colors-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
  }

  .form-actions {
    flex-direction: column;
    gap: 1rem;
  }
  
  .form-actions button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 0.875rem;
  }

  .form-card {
    padding: 1.25rem;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1.25rem;
  }
  
  .form-group label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
  }
  
  .form-group input,
  .form-group textarea,
  .form-group select {
    padding: 0.8rem;
    font-size: 0.9rem;
  }
  
  .form-group textarea {
    min-height: 100px;
  }

  .colors-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.875rem;
  }
  
  .color-option {
    padding: 1rem;
  }
  
  .color-preview {
    width: 50px;
    height: 50px;
  }
  
  .form-actions button {
    padding: 0.875rem;
    font-size: 0.95rem;
  }
}
</style>

